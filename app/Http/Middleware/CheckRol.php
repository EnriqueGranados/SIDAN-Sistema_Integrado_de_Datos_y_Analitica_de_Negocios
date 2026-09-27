<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRol
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $usuario = $request->user();

        if (!$usuario || !$usuario->rol) {
            abort(403);
        }

        if (!in_array($usuario->rol->nombre, $roles)) {
            abort(403);
        }

        return $next($request);
    }
}