<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $usuario = Auth::user();

        // Cambio de contraseña.
        if ($usuario->must_change_password) {
            $request->session()->forget('url.intended');

            return redirect()->route('password.force.edit')->with('warning', 'Por seguridad, debes cambiar tu contraseña temporal antes de continuar.');
        }

        // Obtener el nombre del rol mediante la relación del modelo.
        $nombreRol = $usuario->rol?->nombre;

        // Determinar si es administrador.
        $esAdministrador = in_array(
            $nombreRol,
            ['admin', 'superadmin'],
            true
        );

        // Establecer el destino predeterminado.
        $destinoPredeterminado = $esAdministrador
            ? route('admin.dashboard')
            : route('user.dashboard');

        // Preparar el mensaje correspondiente.
        $mensaje = $esAdministrador
            ? 'Se ha iniciado sesión como administrador. Puedes gestionar el sistema desde aquí.'
            : 'Se ha iniciado sesión correctamente.';

        // Redirigir a la página solicitada anteriormente o al Dashboard correspondiente si no existe una.
        return redirect()->intended($destinoPredeterminado)->with('success', $mensaje);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
