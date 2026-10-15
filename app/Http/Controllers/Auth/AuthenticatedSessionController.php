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

        if ($usuario->must_change_password) {
            $request->session()->forget('url.intended');

            return redirect()
                ->route('password.force.edit')
                ->with(
                    'warning',
                    'Por seguridad, debes cambiar tu contraseña temporal antes de continuar.'
                );
        }

        $nombreRol = $usuario->rol?->nombre;

        $esAdministrador = in_array(
            $nombreRol,
            ['admin', 'superadmin'],
            true
        );

        $mensaje = $esAdministrador
            ? 'Se ha iniciado sesión como administrador. Puedes gestionar el sistema desde aquí.'
            : 'Se ha iniciado sesión correctamente.';

        if ($nombreRol === 'usuario') {
            $request->session()->forget('url.intended');

            return redirect()
                ->route('welcome')
                ->with('success', $mensaje);
        }

        $destinoPredeterminado = $esAdministrador
            ? route('admin.dashboard')
            : route('welcome');

        return redirect()
            ->intended($destinoPredeterminado)
            ->with('success', $mensaje);
    }
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}