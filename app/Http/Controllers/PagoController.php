<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Services\PagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    public function iniciar(
        Request $request,
        Orden $orden,
        PagoService $pagos
    ): RedirectResponse {
        $usuario = $request->user();

        $propietario = $usuario
            && $orden->id_usuario !== null
            && (int) $orden->id_usuario
                === (int) $usuario->id_usuario;

        $sesion = $request->session()->has(
            'sidan.ordenes.' . $orden->id_orden
        );

        abort_unless($propietario || $sesion, 403);

        abort_if(
            $usuario && ($usuario->eliminado || !$usuario->estado_activo),
            403
        );

        $url = $pagos->iniciar($orden);

        return redirect()->away($url);
    }
}
