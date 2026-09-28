<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Verificar si el usuario está eliminado
        if ($user->eliminado) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta ha sido eliminada del sistema. Contacta al administrador.');
        }

        // Verificar si el usuario está inactivo/bloqueado
        if (!$user->estado_activo) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está bloqueada. Contacta al administrador para reactivarla.');
        }

        return $next($request);
    }
}