<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdenController extends Controller
{
    public function show(Request $request, Orden $orden): View
    {
        // El dueño autenticado puede consultar su orden.
        $propietario = $request->user()
            && $orden->id_usuario !== null
            && (int) $orden->id_usuario
                === (int) $request->user()->id_usuario;

        // Los visitantes solo pueden consultar operaciones
        // creadas durante su sesión.
        $sesion = $request->session()->has(
            'sidan.ordenes.' . $orden->id_orden
        );

        abort_unless($propietario || $sesion, 403);

        return view('ordenes.show', [
            'orden' => $orden->load(
                'actividad',
                'inscripcion',
                'detalles',
                'pago.intentos'
            ),
        ]);
    }
}
