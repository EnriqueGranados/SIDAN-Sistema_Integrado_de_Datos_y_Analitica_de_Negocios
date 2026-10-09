<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\GooglePasswordResetAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    // Maneja la solicitud de restablecimiento de contraseña.
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim($request->input('email')));

        $user = User::where('correo', $email)->first();

        // Respuesta genérica para todos los casos.
        $respuesta = 'Si existe una cuenta asociada a este correo, recibirás instrucciones para continuar.';

        // Correo inexistente.
        if (!$user) {
            return back()->with('status', $respuesta);
        }

        // Si el suario esta eliminado o bloqueado no enviamos recuperación y tampoco revelamos el estado de la cuenta.
        if ($user->eliminado || !$user->estado_activo) {
            return back()->with('status', $respuesta);
        }

        // Cuenta de solo Google, no tiene contraseña local.
        // Enviamos únicamente un aviso informativo y no generamos token de recuperación.
        if (is_null($user->password_hash)) {
            $user->notify(new GooglePasswordResetAttempt());

            return back()->with('status', $respuesta);
        }

        // Cuenta con contraseña local.
        Password::sendResetLink([
            'correo' => $email,
        ]);

        return back()->with('status', $respuesta);
    }
}