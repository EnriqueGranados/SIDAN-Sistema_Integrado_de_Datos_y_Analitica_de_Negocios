<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Rol;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $usuario = Auth::user();

        $rol = Rol::find($usuario->id_rol);

        if ($rol && $rol->nombre === 'admin' || $rol->nombre === 'superadmin') {
            return redirect()->route('admin.dashboard')
                            ->with('success', 'Se ha iniciado sesión como administrador. Puedes gestionar el sistema desde aquí.');
        }
       
        return redirect()->route('user.dashboard')
                        ->with('success', 'Se ha iniciado sesión correctamente.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
