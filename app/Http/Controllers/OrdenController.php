<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdenController extends Controller
{

    public function index(Request $request): View
    {
        $filtros = $request->validate([
            'actividad' => ['nullable', 'integer', 'min:1'],
            'estado' => ['nullable', 'string', 'max:50'],
            'tipo' => ['nullable', 'in:inscripcion,productos'],
        ]);

        $propias = Orden::query()
            ->where('id_usuario', $request->user()->id_usuario);

        $actividades = \App\Models\Actividad::query()
            ->whereIn('id_actividad', (clone $propias)->select('id_actividad'))
            ->orderBy('nombre')
            ->get(['id_actividad', 'nombre']);

        $estados = (clone $propias)
            ->select('estado')
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        $consulta = (clone $propias)
            ->with(['actividad', 'inscripcion', 'detalles', 'pago.abonos']);

        foreach ([
            'actividad' => 'id_actividad',
            'estado' => 'estado',
            'tipo' => 'tipo_orden',
        ] as $filtro => $columna) {
            if (!empty($filtros[$filtro])) {
                $consulta->where($columna, $filtros[$filtro]);
            }
        }

        return view('ordenes.index', [
            'actividades' => $actividades,
            'estados' => $estados,
            'ordenes' => $consulta
                ->orderByDesc('id_orden')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }
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
                'pago.intentos',
                'pago.abonos'
            ),
        ]);
    }
}