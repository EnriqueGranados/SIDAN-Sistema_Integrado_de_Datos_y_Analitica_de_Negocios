<?php

namespace App\Http\Middleware;

use App\Support\AccesoModulos;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulo
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            AccesoModulos::permiteRuta(
                $request->user(),
                $request->route()?->getName()
            ),
            403,
            'Tu rol no tiene acceso a este módulo.'
        );

        return $next($request);
    }
}