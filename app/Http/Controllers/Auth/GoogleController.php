<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Models\Rol;
use App\Models\InformacionPersonal;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error al comunicarse con Google.');
        }

        $usuarioExistente = User::where('correo', $googleUser->getEmail())->first();

        if ($usuarioExistente) {
            if ($usuarioExistente->google_id !== null) {
                // Escenario 1: Ya está vinculado, inicia sesión directo
                Auth::login($usuarioExistente);
                return redirect()->intended('/user/dashboard')->with('success', 'Has iniciado sesión con tu cuenta de Google.');
            } else {
                // Escenario 2: El correo existe pero no tiene google_id.
                // Guardamos datos en sesión y pedimos confirmación.
                session([
                    'temp_google_id' => $googleUser->getId(),
                    'temp_email'     => $googleUser->getEmail(),
                    'temp_avatar'    => $googleUser->getAvatar()
                ]);

                return redirect()->route('vincular.cuenta');
            }
        } else {
            // Escenario 3: Usuario nuevo
            $partesNombre = explode(' ', $googleUser->getName(), 2);
            
            $infoPersonal = InformacionPersonal::create([
                'nombres'   => $partesNombre[0] ?? '',
                'apellidos' => $partesNombre[1] ?? '',
            ]);

            // 1. Obtener la URL de la imagen de Google
            $urlAvatar = $googleUser->getAvatar();
            $rutaImagen = null;

            if ($urlAvatar) {
                // 2. Descargar el contenido de la imagen
                $contenidoImagen = Http::get($urlAvatar)->body();
                
                // 3. Generar un nombre único (ej. google_a1b2c3d4.jpg)
                $nombreArchivo = 'perfiles/google_' . Str::random(10) . '.jpg';
                
                // 4. Guardar en storage/app/public/perfiles
                Storage::disk('public')->put($nombreArchivo, $contenidoImagen);
                
                $rutaImagen = $nombreArchivo;
            }
            
            $rolUsuario = Rol::where('nombre', 'usuario')->firstOrFail();

            $nuevoUsuario = User::create([
                'id_informacion_personal' => $infoPersonal->id_informacion_personal,
                'id_rol'                  => $rolUsuario->id_rol,
                'correo'                  => $googleUser->getEmail(),
                'password_hash'           => null, 
                'google_id'               => $googleUser->getId(),
                'imagen_perfil'           => $rutaImagen, 
            ]);

            Auth::login($nuevoUsuario);
            return redirect()->route('datos.personales')->with('success', 'Tu cuenta de Google se ha enlazado correctamente. Puedes completar tu perfil a continuación.');
        }
    }

    public function guardarDatosPersonales(Request $request)
    {
        $usuario = Auth::user();

        $request->validate([
            // Validamos excluyendo el ID actual por si en el futuro se usa para editar el perfil
            'documento' => ['nullable', 'string', 'regex:/^[0-9]{8}-[0-9]$/', 'unique:tbl_informacion_personal,documento,' . $usuario->id_informacion_personal . ',id_informacion_personal'],
            'telefono' => ['nullable', 'string', 'regex:/^\+[1-9][0-9]{6,14}$/', 'unique:tbl_informacion_personal,telefono,' . $usuario->id_informacion_personal . ',id_informacion_personal'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(10)->format('Y-m-d')],
            'genero' => ['nullable', 'string', 'max:10'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            
            // La contraseña es opcional, pero si la envían debe estar confirmada
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ],
        [
            'documento.unique' => 'Este DUI ya está registrado.',
            'documento.regex' => 'Ingresa un número de DUI válido.',

            'telefono.regex' => 'Ingresa un número de teléfono válido.',
            'telefono.unique' => 'Este número de teléfono ya está registrado.',

            'fecha_nacimiento.before_or_equal' => 'Debes tener al menos 10 años para registrarte.',
        ]);

        // 1. Actualizamos la tabla de información personal
        $infoPersonal = InformacionPersonal::where('id_informacion_personal', $usuario->id_informacion_personal)->first();
        
        if ($infoPersonal) {
            // Solo actualizamos los campos que el usuario decidió llenar
            $infoPersonal->update([
                'documento' => $request->documento,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'ubicacion' => $request->ubicacion,
            ]);
        }

        // 2. Si el usuario ingresó una contraseña alternativa, la guardamos
        if ($request->filled('password')) {
            $usuario->update([
                'password_hash' => Hash::make($request->password)
            ]);
        }

        // 3. Redirigimos al dashboard con un mensaje de éxito
        return redirect()->route('user.dashboard')->with('success', '¡Perfil actualizado con éxito!');
    }

    public function showLinkAccountForm()
    {
        if (!session()->has('temp_email')) {
            return redirect('/login');
        }

        return view('auth.vincular-cuenta');
    }

    public function linkAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $usuario = User::where('correo', session('temp_email'))->first();

        // Comprobamos la contraseña contra el campo password_hash.
        if (Hash::check($request->password, $usuario->password_hash)) {
            
            $usuario->google_id = session('temp_google_id');
            
            if (is_null($usuario->imagen_perfil)) {
                // 1. Obtener la URL de la imagen desde la variable de sesión
                $urlAvatar = session('temp_avatar');

                if ($urlAvatar) {
                    // 2. Descargar el contenido de la imagen
                    $contenidoImagen = Http::get($urlAvatar)->body();
                    
                    // 3. Generar un nombre único
                    $nombreArchivo = 'perfiles/google_' . Str::random(10) . '.jpg';
                    
                    // 4. Guardar en storage/app/public/perfiles
                    Storage::disk('public')->put($nombreArchivo, $contenidoImagen);
                    
                    // 5. Asignar la nueva ruta de la imagen al usuario directamente
                    $usuario->imagen_perfil = $nombreArchivo;
                }
            }
            
            $usuario->save();

            session()->forget(['temp_email', 'temp_google_id', 'temp_avatar']);
            Auth::login($usuario);

            return redirect()->intended('/user/dashboard')->with('success', 'Cuenta vinculada exitosamente.');
        }

        return back()->withErrors(['password' => 'La contraseña es incorrecta.']);
    }
}