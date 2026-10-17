<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\AccesoModulos;
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

        $request->session()->forget('url.intended');

        if ($usuario->must_change_password) {
            return redirect()
                ->route('password.force.edit')
                ->with(
                    'warning',
                    'Por seguridad, debes cambiar tu contraseña temporal antes de continuar.'
                );
        }

        return redirect()
            ->route(AccesoModulos::inicio($usuario))
            ->with('success', 'Se ha iniciado sesión correctamente.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}