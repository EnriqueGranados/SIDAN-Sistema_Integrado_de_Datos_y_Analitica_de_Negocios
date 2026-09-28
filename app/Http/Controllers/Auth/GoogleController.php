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
use App\Models\User;
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
                return redirect()->intended('/user/dashboard');
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

            $nuevoUsuario = Usuario::create([
                'id_informacion_personal' => $infoPersonal->id_informacion_personal,
                'id_rol'                  => 2, 
                'correo'                  => $googleUser->getEmail(),
                'password_hash'           => null, 
                'google_id'               => $googleUser->getId(),
                'imagen_perfil'             => $rutaImagen, 
            ]);

            Auth::login($nuevoUsuario);
            return redirect()->intended('/user/dashboard');
        }
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