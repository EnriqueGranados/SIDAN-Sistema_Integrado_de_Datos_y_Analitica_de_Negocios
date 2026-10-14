<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Inscripcion;
use App\Models\Orden;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ControlActividadController extends Controller
{
    private function actividades()
    {
        return Actividad::query()
            ->orderBy('nombre')
            ->get(['id_actividad', 'nombre']);
    }

    public function inscripciones(Request $request): View
    {
        $filtros = $request->validate([
            'actividad' => ['nullable', 'integer', 'min:1'],
            'estado' => ['nullable', 'string', 'max:50'],
            'buscar' => ['nullable', 'string', 'max:150'],
        ]);

        $consulta = Inscripcion::query()
            ->with(['actividad', 'usuario'])
            ->withCount('ordenes')
            ->orderByDesc('id_inscripcion');

        if (!empty($filtros['actividad'])) {
            $consulta->where(
                'id_actividad',
                $filtros['actividad']
            );
        }

        if (!empty($filtros['estado'])) {
            $consulta->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);

            $consulta->where(function ($query) use ($buscar) {
                $query->where(
                    'nombre_participante',
                    'ILIKE',
                    "%{$buscar}%"
                )->orWhere(
                        'correo_participante',
                        'ILIKE',
                        "%{$buscar}%"
                    );
            });
        }

        $inscripciones = $consulta
            ->paginate(20)
            ->withQueryString();

        $estados = Inscripcion::query()
            ->select('estado')
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        return view('admin.control.inscripciones', [
            'actividades' => $this->actividades(),
            'inscripciones' => $inscripciones,
            'estados' => $estados,
        ]);
    }

    public function ordenes(Request $request): View
    {
        $filtros = $request->validate([
            'actividad' => ['nullable', 'integer', 'min:1'],
            'estado' => ['nullable', 'string', 'max:50'],
            'tipo' => ['nullable', 'in:inscripcion,productos'],
            'buscar' => ['nullable', 'string', 'max:150'],
        ]);

        $consulta = Orden::query()
            ->with([
                'actividad',
                'inscripcion',
                'detalles',
                'pago',
            ])
            ->orderByDesc('id_orden');

        if (!empty($filtros['actividad'])) {
            $consulta->where(
                'id_actividad',
                $filtros['actividad']
            );
        }

        if (!empty($filtros['estado'])) {
            $consulta->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['tipo'])) {
            $consulta->where('tipo_orden', $filtros['tipo']);
        }

        if (!empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);

            $consulta->where(function ($query) use ($buscar) {
                $query->where(
                    'correo_contacto',
                    'ILIKE',
                    "%{$buscar}%"
                )->orWhereHas(
                        'inscripcion',
                        function ($subconsulta) use ($buscar) {
                            $subconsulta
                                ->where(
                                    'nombre_participante',
                                    'ILIKE',
                                    "%{$buscar}%"
                                );
                        }
                    );
            });
        }

        $ordenes = $consulta
            ->paginate(15)
            ->withQueryString();

        $estados = Orden::query()
            ->select('estado')
            ->distinct()
            ->orderBy('estado')
            ->pluck('estado');

        return view('admin.control.ordenes', [
            'actividades' => $this->actividades(),
            'ordenes' => $ordenes,
            'estados' => $estados,
        ]);
    }

    public function consolidado(Request $request): View
    {
        $filtros = $request->validate([
            'actividad' => ['nullable', 'integer', 'min:1'],
            'buscar' => ['nullable', 'string', 'max:150'],
        ]);

        // Solo compras de productos con orden pagada.
        // La configuración se agrupa como JSONB para separar
        // camisas de distinta talla, color o nombre.
        $base = DB::table('tbl_detalles_orden as detalle')
            ->join(
                'tbl_ordenes as orden',
                'orden.id_orden',
                '=',
                'detalle.id_orden'
            )
            ->join(
                'tbl_actividades as actividad',
                'actividad.id_actividad',
                '=',
                'orden.id_actividad'
            )
            ->where('orden.tipo_orden', 'productos')
            ->where('orden.estado', 'pagada')
            ->whereNotNull('detalle.id_item_actividad');

        if (!empty($filtros['actividad'])) {
            $base->where(
                'orden.id_actividad',
                (int) $filtros['actividad']
            );
        }

        if (!empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);

            $base->where(function ($query) use ($buscar) {
                $query->where(
                    'detalle.nombre_concepto',
                    'ILIKE',
                    '%' . $buscar . '%'
                )->orWhere(
                        'orden.correo_contacto',
                        'ILIKE',
                        '%' . $buscar . '%'
                    );
            });
        }

        // Totales por artículo, independientemente de
        // las personalizaciones individuales.
        $productos = (clone $base)
            ->select(
                'orden.id_actividad',
                'actividad.nombre as actividad_nombre',
                'detalle.id_item_actividad',
                'detalle.nombre_concepto'
            )
            ->selectRaw('SUM(detalle.cantidad) as unidades')
            ->selectRaw('SUM(detalle.subtotal) as importe')
            ->groupBy(
                'orden.id_actividad',
                'actividad.nombre',
                'detalle.id_item_actividad',
                'detalle.nombre_concepto'
            )
            ->orderBy('actividad.nombre')
            ->orderBy('detalle.nombre_concepto')
            ->get();

        // Desglose por combinación exacta de opciones.
        $variaciones = (clone $base)
            ->select(
                'orden.id_actividad',
                'detalle.id_item_actividad',
                'detalle.nombre_concepto',
                'detalle.configuracion'
            )
            ->selectRaw('SUM(detalle.cantidad) as unidades')
            ->selectRaw('SUM(detalle.subtotal) as importe')
            ->groupBy(
                'orden.id_actividad',
                'detalle.id_item_actividad',
                'detalle.nombre_concepto',
                'detalle.configuracion'
            )
            ->orderBy('detalle.nombre_concepto')
            ->get();

        // Registro individual para saber quién hizo el pedido.
        $pedidos = (clone $base)
            ->leftJoin(
                'tbl_inscripciones as inscripcion',
                'inscripcion.id_inscripcion',
                '=',
                'orden.id_inscripcion'
            )
            ->select(
                'orden.id_orden',
                'orden.id_actividad',
                'orden.correo_contacto',
                'orden.created_at as fecha_orden',
                'inscripcion.nombre_participante',
                'detalle.id_detalle_orden',
                'detalle.nombre_concepto',
                'detalle.configuracion',
                'detalle.cantidad',
                'detalle.precio_unitario',
                'detalle.subtotal'
            )
            ->orderByDesc('orden.id_orden')
            ->orderBy('detalle.id_detalle_orden')
            ->paginate(25)
            ->withQueryString();

        $actividades = Actividad::query()
            ->orderBy('nombre')
            ->get(['id_actividad', 'nombre']);

        return view('admin.control.consolidado', compact(
            'actividades',
            'productos',
            'variaciones',
            'pedidos'
        ));
    }
}