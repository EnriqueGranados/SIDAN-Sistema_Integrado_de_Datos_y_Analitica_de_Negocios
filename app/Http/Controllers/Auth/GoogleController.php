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
use App\Models\GoogleUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;

class GoogleController extends Controller
{   
    // Redirigir al usuario a la página de autenticación de Google.
    public function redirectToGoogle()
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            return redirect()
                ->route('password.force.edit')
                ->with(
                    'warning',
                    'Debes cambiar tu contraseña temporal antes de continuar.'
                );
        }

        session()->forget('google_oauth_intent');

        return Socialite::driver('google')->redirect();
    }

    // Manejar la respuesta de Google después de la autenticación.
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error al comunicarse con Google.');
        }

        $googleId = $googleUser->getId();
        $googleEmail = strtolower(trim($googleUser->getEmail()));

        $intent = session()->pull('google_oauth_intent');

        // Vincular la cuenta de Google con el perfil del usuario autenticado.
        if ($intent === 'link') {
            return $this->handleProfileGoogleLink(
                $googleId,
                $googleEmail
            );
        }

        // Escenario 1: Google ya está vinculado a un usuario en SIDAN.
        $googleAccount = GoogleUser::where('google_id', $googleId)->first();

        if ($googleAccount) {
            $usuario = $googleAccount->user;

            if (!$usuario || $usuario->eliminado || !$usuario->estado_activo) {
                return redirect()
                    ->route('login')
                    ->with('error', 'Esta cuenta no está disponible.');
            }

            Auth::login($usuario);
            request()->session()->regenerate();

            if ($usuario->must_change_password) {
                request()->session()->forget('url.intended');

                return redirect()
                    ->route('password.force.edit')
                    ->with(
                        'warning',
                        'Debes actualizar tu contraseña antes de continuar en SIDAN.'
                    );
            }

            return redirect()
                ->route('dashboard')
                ->with('success', 'Has iniciado sesión con tu cuenta de Google.');
        }

        // Escenario 2: El correo ya existe en SIDAN, pero Google no está vinculado.
        $usuarioExistente = User::where('correo', $googleEmail)->first();

        if ($usuarioExistente) {
            session([
                'temp_google_id' => $googleId,
                'temp_email' => $googleEmail,
                'temp_avatar' => $googleUser->getAvatar(),
            ]);

            return redirect()->route('vincular.cuenta');
        }

        // Escenario 3: Usuario nuevo, crear cuenta en SIDAN y vincular Google.
        $urlAvatar = $googleUser->getAvatar();
        $rutaImagen = null;

        if ($urlAvatar) {
            $respuestaAvatar = Http::get($urlAvatar);

            if ($respuestaAvatar->successful()) {
                $nombreArchivo = 'perfiles/google_' . Str::random(10) . '.jpg';

                Storage::disk('public')->put(
                    $nombreArchivo,
                    $respuestaAvatar->body()
                );

                $rutaImagen = $nombreArchivo;
            }
        }

        try {
            $nuevoUsuario = DB::transaction(function () use (
                $googleUser,
                $googleId,
                $googleEmail,
                $rutaImagen
            ) {
                $partesNombre = explode(' ', $googleUser->getName(), 2);

                $infoPersonal = InformacionPersonal::create([
                    'nombres' => $partesNombre[0] ?? '',
                    'apellidos' => $partesNombre[1] ?? '',
                ]);

                $rolUsuario = Rol::where('nombre', 'usuario')->firstOrFail();

                // Primero creamos el usuario en la tabla tbl_usuarios.
                $usuario = User::create([
                    'id_informacion_personal' => $infoPersonal->id_informacion_personal,
                    'id_rol' => $rolUsuario->id_rol,
                    'correo' => $googleEmail,
                    'password_hash' => null,
                    'imagen_perfil' => $rutaImagen,
                ]);

                // Después creamos la relación en la tabla tbl_google_usuarios.
                $usuario->googleAccount()->create([
                    'google_id' => $googleId,
                    'correo_google' => $googleEmail,
                ]);

                return $usuario;
            });
        } catch (\Exception $e) {
            // Si hubo un error, eliminamos la imagen descargada para no dejar archivos huérfanos.
            if ($rutaImagen) {
                Storage::disk('public')->delete($rutaImagen);
            }

            report($e);

            return redirect('/login')->with('error', 'No fue posible crear tu cuenta. Inténtalo nuevamente.');
        }

        Auth::login($nuevoUsuario);

        return redirect()
            ->route('datos.personales')->with('success', 'Tu cuenta de Google se ha enlazado correctamente. Puedes completar tu perfil a continuación.');
    }

    // Mostrar el formulario para completar los datos personales del usuario.
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

    // Mostrar el formulario para vincular la cuenta de Google con una cuenta existente en SIDAN.
    public function showLinkAccountForm()
    {
        $googleEmail = session('temp_email');

        if (!$googleEmail || !session('temp_google_id')) {
            return redirect()->route('login');
        }

        $usuario = User::where('correo', $googleEmail)->first();

        if (!$usuario) {
            session()->forget([
                'temp_email',
                'temp_google_id',
                'temp_avatar',
            ]);

            return redirect()
                ->route('login')
                ->with('error', 'No se encontró la cuenta que deseas vincular.');
        }

        if ($usuario->must_change_password) {
            session()->forget([
                'temp_email',
                'temp_google_id',
                'temp_avatar',
            ]);

            return redirect()
                ->route('login')
                ->with(
                    'warning',
                    'Primero debes iniciar sesión con tu contraseña temporal y cambiarla antes de vincular Google.'
                );
        }

        return view('auth.vincular-cuenta');
    }

    // Vincular la cuenta de Google con una cuenta existente en SIDAN.
    public function linkAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $googleId = session('temp_google_id');
        $googleEmail = session('temp_email');
        $googleAvatar = session('temp_avatar');

        // Validar que exista una vinculación pendiente.
        if (!$googleId || !$googleEmail) {
            return redirect()->route('login')->with('error', 'La solicitud de vinculación con Google ha expirado.');
        }

        // Buscamos el usuario por correo electrónico.
        $usuario = User::where('correo', $googleEmail)->first();

        if (!$usuario) {
            session()->forget([
                'temp_email',
                'temp_google_id',
                'temp_avatar',
            ]);

            return redirect()->route('login')->with('error', 'No fue posible encontrar la cuenta que deseas vincular.');
        }

        //Confirmar identidad mediante la contraseña SIDAN.
        if (is_null($usuario->password_hash) || !Hash::check($request->password, $usuario->password_hash)) 
        {
            return back()->withErrors(['password' => 'La contraseña es incorrecta.',]);
        }

        // Impedir vincular Google mientras exista un cambio obligatorio de contraseña pendiente.
        if ($usuario->must_change_password) {
            session()->forget([
                'temp_email',
                'temp_google_id',
                'temp_avatar',
            ]);

            return redirect()
                ->route('login')
                ->with(
                    'warning',
                    'Debes cambiar tu contraseña temporal antes de vincular tu cuenta de Google.'
                );
        }

        // Comprobar que la cuenta de Google no esté ya vinculada a otro usuario.
        $googleYaVinculado = GoogleUser::where('google_id', $googleId)->exists();

        if ($googleYaVinculado) {
            session()->forget([
                'temp_email',
                'temp_google_id',
                'temp_avatar',
            ]);

            return redirect()->route('login')->with('error', 'Esta cuenta de Google ya se encuentra vinculada a otro usuario.');
        }

        // Comprobar que el usuario SIDAN no tenga ya una cuenta de Google vinculada.
        if ($usuario->googleAccount()->exists()) {
            session()->forget([
                'temp_email',
                'temp_google_id',
                'temp_avatar',
            ]);

            return redirect()->route('login')->with('error', 'Esta cuenta de SIDAN ya tiene una cuenta de Google vinculada.');
        }

        $rutaImagen = null;

        // Intentar descargar el avatar de Google si el usuario no tiene imagen de perfil.
        if (is_null($usuario->imagen_perfil) && $googleAvatar) {
            try {
                $respuestaAvatar = Http::get($googleAvatar);

                if ($respuestaAvatar->successful()) {
                    $nombreArchivo = 'perfiles/google_' . Str::random(10) . '.jpg';

                    Storage::disk('public')->put(
                        $nombreArchivo,
                        $respuestaAvatar->body()
                    );

                    $rutaImagen = $nombreArchivo;
                }
            } catch (\Exception $e) {
                report($e);
            }
        }

        try {
            DB::transaction(function () use (
                $usuario,
                $googleId,
                $googleEmail,
                $rutaImagen
            ) {
                // Vincular la cuenta de Google al usuario existente.
                $usuario->googleAccount()->create([
                    'google_id' => $googleId,
                    'correo_google' => $googleEmail,
                ]);

                // Actualizar la imagen de perfil si se descargó una nueva.
                if ($rutaImagen) {
                    $usuario->imagen_perfil = $rutaImagen;
                    $usuario->save();
                }
            });
        } catch (\Exception $e) {
            if ($rutaImagen) {
                Storage::disk('public')->delete($rutaImagen);
            }

            report($e);

            return back()->withErrors(['password' => 'No fue posible vincular la cuenta de Google. Inténtalo nuevamente.',]);
        }

        session()->forget([
            'temp_email',
            'temp_google_id',
            'temp_avatar',
        ]);

        Auth::login($usuario);

        return redirect()->intended('/user/dashboard')->with('success', 'Cuenta de Google vinculada exitosamente.');
    }

    // Vincular la cuenta de Google desde el perfil del usuario autenticado.
    public function redirectToGoogleFromProfile()
    {
        $user = Auth::user();

        if ($user->must_change_password) {
            return redirect()
                ->route('password.force.edit')
                ->with(
                    'warning',
                    'Primero debes cambiar tu contraseña temporal.'
                );
        }

        // El usuario ya tiene una cuenta de Google vinculada.
        if ($user->googleAccount()->exists()) {
            return redirect()->route('profile.edit')->with('error', 'Ya tienes una cuenta de Google vinculada.');
        }

        // Indicamos que este OAuth no es para iniciar sesión, sino para vincular Google al usuario autenticado.
        session([
            'google_oauth_intent' => 'link',
        ]);

        return Socialite::driver('google')->redirect();
    }

    //Vincular la cuenta de Google con el perfil del usuario autenticado.
    private function handleProfileGoogleLink(string $googleId, string $googleEmail): RedirectResponse 
    {
        // El usuario debe seguir autenticado.
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Tu sesión ha expirado. Inicia sesión nuevamente.');
        }

        $user = Auth::user();

        if ($user->must_change_password) {
            return redirect()
                ->route('password.force.edit')
                ->with(
                    'warning',
                    'Primero debes cambiar tu contraseña temporal.'
                );
        }

        // El usuario SIDAN ya tiene Google vinculado.
        if ($user->googleAccount()->exists()) {
            return redirect()->route('profile.edit')->with('error', 'Ya tienes una cuenta de Google vinculada.');
        }

        // La cuenta Google seleccionada ya pertenece a otro usuario SIDAN.
        if (GoogleUser::where('google_id', $googleId)->exists()) {
            return redirect()->route('profile.edit')->with('error', 'Esta cuenta de Google ya se encuentra vinculada a otro usuario.');
        }

        // El correo de Google debe coincidir con el correo del usuario SIDAN.
        if (strtolower(trim($user->correo)) !== strtolower(trim($googleEmail))) 
        {
            return redirect()->route('profile.edit')->with('error', 'La cuenta de Google seleccionada no coincide con el correo electrónico de tu cuenta SIDAN.');
        }

        try {
            $user->googleAccount()->create([
                'google_id' => $googleId,
                'correo_google' => $googleEmail,
            ]);
        } catch (\Exception $e) {
            report($e);

            return redirect()->route('profile.edit')->with('error', 'No fue posible vincular la cuenta de Google. Inténtalo nuevamente.');
        }

        return redirect()->route('profile.edit')->with('success', 'Cuenta de Google vinculada exitosamente.');
    }
}