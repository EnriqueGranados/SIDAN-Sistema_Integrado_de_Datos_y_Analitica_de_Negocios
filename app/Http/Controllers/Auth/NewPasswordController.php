<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'request' => $request,
        ]);
    }

    public function handleFromProfile(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'El enlace no es válido o ha expirado.');
        }

        if ($request->isMethod('post')) {
            return $this->storeFromProfile($request);
        }

        return $this->createFromProfile($request);
    }

    public function createFromProfile(Request $request): View
    {
        $intent = $request->query('intent');

        if (!in_array($intent, ['change', 'set'], true)) {
            abort(403, 'El enlace no es válido.');
        }

        $email = strtolower(trim(
            (string) $request->query('email')
        ));

        $user = User::where('correo', $email)->first();

        if (
            !$user ||
            $user->eliminado ||
            !$user->estado_activo
        ) {
            abort(403, 'El enlace no es válido.');
        }

        // Comprobar que el enlace todavía corresponde al estado de la cuenta.
        if (
            $intent === 'change' &&
            is_null($user->password_hash)
        ) {
            abort(403, 'El enlace ya no puede utilizarse.');
        }

        if (
            $intent === 'set' &&
            (
                !is_null($user->password_hash) ||
                !$user->googleAccount()->exists()
            )
        ) {
            abort(403, 'El enlace ya no puede utilizarse.');
        }

        return view('auth.profile-password', [
            'request' => $request,
            'intent' => $intent,
            'user' => $user,
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */

    // Maneja la solicitud de restablecimiento de contraseña.
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $email = strtolower(trim($request->input('email')));

        // Verificar si el usuario existe y tiene una cuenta válida para restablecer la contraseña.
        $existingUser = User::where('correo', $email)->first();

        if (!$existingUser || $existingUser->eliminado || !$existingUser->estado_activo || is_null($existingUser->password_hash)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'No fue posible restablecer la contraseña.',
                ]);
        }

        // Intentar restablecer la contraseña.
        $status = Password::reset(
            [
                'correo' => $email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
            function (User $user) use ($request) {
                $user->forceFill([
                    'password_hash' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Respuesta genérica para todos los casos.
        return $status === Password::PASSWORD_RESET
            ? redirect()
                ->route('login')
                ->with(
                    'status',
                    'Tu contraseña ha sido restablecida correctamente.'
                )
            : back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => __($status),
                ]);
    }

    public function storeFromProfile(Request $request): RedirectResponse
    {
        // Validar los datos de entrada.
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $email = strtolower(trim($request->input('email')));
        $intent = $request->query('intent');

        if (!in_array($intent, ['change', 'set'], true)) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'El enlace para gestionar tu contraseña no es válido.'
                );
        }

        // Verificar si el usuario existe y tiene una cuenta válida para restablecer la contraseña.
        $existingUser = User::where('correo', $email)->first();

        if (
            !$existingUser ||
            $existingUser->eliminado ||
            !$existingUser->estado_activo
        ) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'No fue posible actualizar la contraseña.'
                );
        }

        // Reglas para cada intención.
        // CHANGE: la cuenta necesariamente debe tener contraseña.
        if (
            $intent === 'change' &&
            is_null($existingUser->password_hash)
        ) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Este enlace ya no puede utilizarse.'
                );
        }

        // SET: solamente una cuenta que utiliza Google puede establecer su primera contraseña mediante este flujo.
        if (
            $intent === 'set' &&
            (
                !is_null($existingUser->password_hash) ||
                !$existingUser->googleAccount()->exists()
            )
        ) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Este enlace ya no puede utilizarse.'
                );
        }

        // Consumir el token mediante el broker.
        $status = Password::reset(
            [
                'correo' => $email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
            function (User $user) use ($request) {
                $user->forceFill([
                    'password_hash' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                event(new PasswordReset($user));
            }
        );

        
        // Tokken incorrecto.
        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors([
                    'email' => __($status),
                ]);
        }

        // Cerrar la sesión actual.
        if (auth()->check()) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()
            ->route('login')
            ->with(
                'status',
                $intent === 'set'
                    ? 'Tu contraseña de SIDAN ha sido establecida correctamente. Ya puedes iniciar sesión con correo y contraseña o continuar usando Google.'
                    : 'Tu contraseña ha sido cambiada correctamente. Inicia sesión nuevamente.'
            );
    }
}