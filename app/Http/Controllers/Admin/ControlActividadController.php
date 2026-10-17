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

    /**
     * Una sola fuente para los tres desgloses del consolidado.
     * Las órdenes pendientes, canceladas y vencidas nunca participan.
     */
    private function consultaConsolidado(array $filtros): \Illuminate\Database\Query\Builder
    {
        $base = DB::table('tbl_detalles_orden as detalle')
            ->join('tbl_ordenes as orden', 'orden.id_orden', '=', 'detalle.id_orden')
            ->join('tbl_actividades as actividad', 'actividad.id_actividad', '=', 'orden.id_actividad')
            ->leftJoin('tbl_inscripciones as inscripcion', 'inscripcion.id_inscripcion', '=', 'orden.id_inscripcion')
            ->where('orden.tipo_orden', 'productos')
            ->where('orden.estado', 'pagada')
            ->whereNotNull('detalle.id_item_actividad');

        if (!empty($filtros['actividad'])) {
            $base->where('orden.id_actividad', (int) $filtros['actividad']);
        }

        if (!empty($filtros['buscar'])) {
            $buscar = trim($filtros['buscar']);
            $base->where(function ($query) use ($buscar) {
                $query->where('detalle.nombre_concepto', 'ILIKE', "%{$buscar}%")
                    ->orWhere('orden.correo_contacto', 'ILIKE', "%{$buscar}%")
                    ->orWhere('inscripcion.nombre_participante', 'ILIKE', "%{$buscar}%");

                // Se admite tanto "34" como "#34" para localizar una orden.
                $numero = ltrim($buscar, '#');
                if ($numero !== '' && ctype_digit($numero)) {
                    $query->orWhere('orden.id_orden', (int) $numero);
                }
            });
        }

        return $base;
    }

    private function filtrosConsolidado(Request $request): array
    {
        return $request->validate([
            'actividad' => ['nullable', 'integer', 'min:1'],
            'buscar' => ['nullable', 'string', 'max:150'],
            'vista' => ['nullable', 'in:productos,variaciones,compradores'],
        ]);
    }

    private function productosConsolidados(\Illuminate\Database\Query\Builder $base)
    {
        return (clone $base)
            ->select(
                'orden.id_actividad',
                'actividad.nombre as actividad_nombre',
                'detalle.id_item_actividad',
                'detalle.nombre_concepto'
            )
            ->selectRaw('SUM(detalle.cantidad) AS unidades')
            // detalle.subtotal es bruto; detalle.descuento se descuenta de la línea.
            ->selectRaw('SUM(detalle.subtotal - COALESCE(detalle.descuento, 0)) AS importe')
            ->groupBy(
                'orden.id_actividad',
                'actividad.nombre',
                'detalle.id_item_actividad',
                'detalle.nombre_concepto'
            )
            ->orderBy('actividad.nombre')
            ->orderBy('detalle.nombre_concepto')
            ->get();
    }

    private function variacionesConsolidadas(\Illuminate\Database\Query\Builder $base)
    {
        return (clone $base)
            ->select(
                'orden.id_actividad',
                'actividad.nombre as actividad_nombre',
                'detalle.id_item_actividad',
                'detalle.nombre_concepto',
                'detalle.configuracion'
            )
            ->selectRaw('SUM(detalle.cantidad) AS unidades')
            ->selectRaw('SUM(detalle.subtotal - COALESCE(detalle.descuento, 0)) AS importe')
            ->groupBy(
                'orden.id_actividad',
                'actividad.nombre',
                'detalle.id_item_actividad',
                'detalle.nombre_concepto',
                'detalle.configuracion'
            )
            ->orderBy('actividad.nombre')
            ->orderBy('detalle.nombre_concepto')
            ->get();
    }

    /** Detalle de artículos, conservando las opciones originales de cada línea. */
    private function lineasConsolidadas(\Illuminate\Database\Query\Builder $base)
    {
        return (clone $base)
            ->select(
                'orden.id_orden',
                'detalle.id_detalle_orden',
                'detalle.nombre_concepto',
                'detalle.configuracion',
                'detalle.cantidad',
                'detalle.precio_unitario',
                'detalle.subtotal',
                'detalle.descuento'
            )
            ->orderByDesc('orden.id_orden')
            ->orderBy('detalle.id_detalle_orden');
    }

    public function consolidado(Request $request): View
    {
        $filtros = $this->filtrosConsolidado($request);
        $vista = $filtros['vista'] ?? 'productos';
        $base = $this->consultaConsolidado($filtros);

        $productos = $this->productosConsolidados($base);
        $variaciones = $vista === 'variaciones'
            ? $this->variacionesConsolidadas($base)
            : collect();

        $ordenes = null;
        $pedidosPorOrden = collect();

        if ($vista === 'compradores') {
            // Se pagina por ORDEN y no por línea, para no dividir una compra
            // entre dos páginas del consolidado.
            $ordenes = (clone $base)
                ->select(
                    'orden.id_orden',
                    'orden.correo_contacto',
                    'orden.created_at as fecha_orden',
                    'actividad.nombre as actividad_nombre',
                    'inscripcion.nombre_participante'
                )
                ->groupBy(
                    'orden.id_orden',
                    'orden.correo_contacto',
                    'orden.created_at',
                    'actividad.nombre',
                    'inscripcion.nombre_participante'
                )
                ->orderByDesc('orden.id_orden')
                ->paginate(15)
                ->withQueryString();

            $ids = $ordenes->getCollection()->pluck('id_orden')->all();
            if ($ids) {
                $pedidosPorOrden = $this->lineasConsolidadas($base)
                    ->whereIn('orden.id_orden', $ids)
                    ->get()
                    ->groupBy('id_orden');
            }
        }

        return view('admin.control.consolidado', [
            'actividades' => $this->actividades(),
            'productos' => $productos,
            'variaciones' => $variaciones,
            'ordenes' => $ordenes,
            'pedidosPorOrden' => $pedidosPorOrden,
            'vista' => $vista,
        ]);
    }

    /** Vista imprimible: usa los mismos filtros, pero sin paginación. */
    public function imprimirConsolidado(Request $request): View
    {
        $filtros = $this->filtrosConsolidado($request);
        $vista = $filtros['vista'] ?? 'productos';
        $base = $this->consultaConsolidado($filtros);

        $productos = $this->productosConsolidados($base);
        $variaciones = $vista === 'variaciones'
            ? $this->variacionesConsolidadas($base)
            : collect();

        $pedidosPorOrden = collect();
        $ordenes = collect();

        if ($vista === 'compradores') {
            $ordenes = (clone $base)
                ->select(
                    'orden.id_orden',
                    'orden.correo_contacto',
                    'orden.created_at as fecha_orden',
                    'actividad.nombre as actividad_nombre',
                    'inscripcion.nombre_participante'
                )
                ->groupBy(
                    'orden.id_orden',
                    'orden.correo_contacto',
                    'orden.created_at',
                    'actividad.nombre',
                    'inscripcion.nombre_participante'
                )
                ->orderByDesc('orden.id_orden')
                ->get();

            // El reporte de impresión contiene TODAS las órdenes filtradas.
            $pedidosPorOrden = $this->lineasConsolidadas($base)
                ->get()
                ->groupBy('id_orden');
        }

        $actividadSeleccionada = !empty($filtros['actividad'])
            ? Actividad::whereKey($filtros['actividad'])->value('nombre')
            : null;

        return view('admin.control.consolidado-imprimir', compact(
            'vista',
            'filtros',
            'productos',
            'variaciones',
            'ordenes',
            'pedidosPorOrden',
            'actividadSeleccionada'
        ));
    }
}