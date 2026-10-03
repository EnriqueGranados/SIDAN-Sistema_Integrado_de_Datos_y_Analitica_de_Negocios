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
}