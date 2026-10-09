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
        $rol = $usuario?->rol?->nombre;

        $destinoPredeterminado = in_array($rol, ['admin', 'superadmin'], true)
            ? route('admin.dashboard')
            : route('user.dashboard');

        $mensaje = in_array($rol, ['admin', 'superadmin'], true)
            ? 'Se ha iniciado sesión como administrador.'
            : 'Se ha iniciado sesión correctamente.';

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
