<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Pago;
use App\Models\AbonoTransferencia;
use App\Services\GestionOrdenService;
use App\Services\ResumenFinancieroPagosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GestionOrdenController extends Controller
{
    public function __construct(
        private readonly GestionOrdenService $gestion
    ) {
    }

    private function autorizar(Request $request, Orden $orden): void
    {
        $usuario = $request->user();

        if ($usuario && ($usuario->eliminado || !$usuario->estado_activo)) {
            abort(403);
        }

        $permitido = $orden->id_usuario !== null
            ? $usuario && (int) $usuario->id_usuario === (int) $orden->id_usuario
            : $request->session()->has('sidan.ordenes.' . $orden->id_orden);

        abort_unless($permitido, 403);
    }

    public function promocion(Request $request, Orden $orden)
    {
        $this->autorizar($request, $orden);

        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:60'],
        ]);

        $this->gestion->ejecutar($orden, function (Orden $actual) use ($datos) {
            $this->gestion->aplicarCodigo($actual, $datos['codigo']);
        });

        return back()->with('success', 'Código aplicado. Los totales fueron actualizados.');
    }

    public function manual(Request $request, Orden $orden)
    {
        $this->autorizar($request, $orden);

        $datos = $request->validate([
            'metodo' => ['required', 'in:transferencia,efectivo'],
            'modalidad' => ['required', 'in:completo,anticipo'],
            'comprobante' => [
                'required_if:metodo,transferencia',
                'nullable',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:max_width=12000,max_height=12000',
            ],
        ]);

        if ($datos['metodo'] === 'transferencia') {
            foreach (['banco', 'titular', 'cuenta'] as $campo) {
                if (!filled(config('pagos.transferencia.' . $campo))) {
                    $this->gestion->error('La cuenta bancaria todavía no está configurada.');
                }
            }
        }

        $archivo = null;

        try {
            if ($datos['metodo'] === 'transferencia') {
                $archivo = $request->file('comprobante')
                    ->store('comprobantes-transferencias', 'local');

                if (!$archivo) {
                    $this->gestion->error('No se pudo guardar el comprobante. Intentá nuevamente.');
                }
            }

            $this->gestion->ejecutar($orden, function (Orden $actual) use ($datos, $archivo) {
                $this->gestion->registrarManual($actual, $datos['metodo'], $archivo, $datos['modalidad']);
            });
        } catch (Throwable $exception) {
            if ($archivo) {
                Storage::disk('local')->delete($archivo);
            }

            throw $exception;
        }

        return back()->with(
            'success',
            'Solicitud registrada. La orden seguirá pendiente hasta la aprobación del administrador.'
        );
    }

    public function complemento(Request $request, Orden $orden)
    {
        $this->autorizar($request, $orden);

        $request->validate([
            'comprobante' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
                'dimensions:max_width=12000,max_height=12000',
            ],
        ]);

        foreach (['banco', 'titular', 'cuenta'] as $campo) {
            if (!filled(config('pagos.transferencia.' . $campo))) {
                $this->gestion->error('La cuenta bancaria todavía no está configurada.');
            }
        }

        $archivo = $request->file('comprobante')
            ->store('comprobantes-transferencias', 'local');

        if (!$archivo) {
            $this->gestion->error('No se pudo guardar el comprobante.');
        }

        try {
            $this->gestion->ejecutar($orden, function (Orden $actual) use ($archivo) {
                $this->gestion->registrarComplemento($actual, $archivo);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($archivo);
            throw $exception;
        }

        return back()->with(
            'success',
            'Comprobante complementario enviado. Esperá la revisión administrativa.'
        );
    }

    public function gratuita(Request $request, Orden $orden)
    {
        $this->autorizar($request, $orden);

        $this->gestion->ejecutar($orden, function (Orden $actual) {
            $this->gestion->validarSinPago($actual);

            if ($this->gestion->centavos($actual->total) !== 0) {
                $this->gestion->error('La orden todavía tiene un monto pendiente.');
            }

            $pago = Pago::create([
                'id_orden' => $actual->id_orden,
                'proveedor' => 'promocion',
                'monto' => 0,
                'estado' => 'pendiente',
            ]);

            $this->gestion->confirmar($actual, $pago, null);
        });

        return back()->with('success', 'Orden confirmada sin cobro.');
    }

    public function cancelar(Request $request, Orden $orden)
    {
        $this->autorizar($request, $orden);
        $datos = $request->validate(['motivo' => ['nullable', 'string', 'max:300']]);
        $this->gestion->ejecutar($orden, fn (Orden $actual) =>
            $this->gestion->cancelar($actual, $request->user()?->id_usuario, $datos['motivo'] ?? null)
        );
        return redirect()->route($request->user()?->rol?->nombre === 'usuario'
            ? 'user.ordenes.index' : 'welcome')
            ->with('success', 'Orden cancelada. Se conserva en tu historial y no genera saldo por cobrar.');
    }

    public function cancelarAdministracion(Request $request, Orden $orden)
    {
        $datos = $request->validate(['motivo' => ['required', 'string', 'min:8', 'max:300']]);
        $this->gestion->ejecutar($orden, fn (Orden $actual) =>
            $this->gestion->cancelar($actual, $request->user()->id_usuario, $datos['motivo'])
        );
        return back()->with('success', 'Orden cancelada y conservada en el historial, sin saldo pendiente de cobro.');
    }

    public function index(Request $request, ResumenFinancieroPagosService $resumen)
    {
        $estadosPagos = Pago::query()
            ->select('estado')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('estado')
            ->orderBy('estado')
            ->pluck('total', 'estado');

        $metodosPagos = Pago::query()
            ->whereNotNull('proveedor')
            ->distinct()
            ->orderBy('proveedor')
            ->pluck('proveedor');

        // El historial debe incluir actividades finalizadas, archivadas y fuera de ventana.
        // No aplicar los filtros de venta presencial ni de visibilidad pública.
        $actividadesFiltro = \App\Models\Actividad::withTrashed()
            ->with(['categoria', 'portada'])
            ->orderBy('nombre')
            ->get([
                'id_actividad', 'id_categoria', 'nombre', 'slug',
                'visible_desde', 'visible_hasta', 'eliminado_en',
                'realizacion_desde', 'realizacion_hasta',
            ]);

        $filtros = $request->validate([
            'estado' => ['nullable', 'string', \Illuminate\Validation\Rule::in($estadosPagos->keys()->all())],
            'actividad' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('tbl_actividades', 'id_actividad')],
            'metodo' => ['nullable', 'string', \Illuminate\Validation\Rule::in($metodosPagos->all())],
            'situacion' => ['nullable', 'in:vencidas,revision_pendiente,saldo_pendiente'],
            'entrega' => ['nullable', 'in:pendientes,completadas'],
        ]);

        $estadoSeleccionado = $filtros['estado'] ?? '';
        $actividadSeleccionada = (int) ($filtros['actividad'] ?? 0);
        $metodoSeleccionado = $filtros['metodo'] ?? '';
        $situacionSeleccionada = $filtros['situacion'] ?? '';
        $entregaSeleccionada = $filtros['entrega'] ?? '';

        // Órdenes sin método de pago: también se pueden cancelar desde Pagos.
        // No forman parte de la paginación de tbl_pagos porque aún no existe un pago.
        $ordenesSinMetodo = Orden::query()
            ->with(['actividad' => fn ($q) => $q->withTrashed()])
            ->whereDoesntHave('pago')
            ->where('estado', 'pendiente_pago')
            ->when($actividadSeleccionada > 0, fn ($q) => $q->where('id_actividad', $actividadSeleccionada))
            ->orderByDesc('id_orden')
            ->paginate(12, ['*'], 'sin_metodo_page')->withQueryString();


        $consulta = Pago::query()
            ->with([
                'orden.actividad' => fn ($q) => $q->withTrashed(),
                'orden.detalles',
                'abonos',
            ])
            ->when($estadoSeleccionado !== '', fn ($q) => $q->where('estado', $estadoSeleccionado))
            ->when($metodoSeleccionado !== '', fn ($q) => $q->where('proveedor', $metodoSeleccionado))
            ->when($actividadSeleccionada > 0, fn ($q) => $q->whereHas('orden',
                fn ($orden) => $orden->where('id_actividad', $actividadSeleccionada)));

        if ($situacionSeleccionada !== 'vencidas') {
            $consulta->whereDoesntHave('orden', fn ($q) => $q->where('estado', 'vencida')
                ->where('cierre_financiero_estado', 'regularizacion'));
        }

        if ($situacionSeleccionada === 'vencidas') {
            $consulta->whereHas('orden', function ($orden) {
                $orden->where(function ($q) {
                    $q->where('estado', 'vencida')
                        ->orWhere(function ($pendiente) {
                            $pendiente->where('estado', 'pendiente_pago')
                                ->where(function ($vencimiento) {
                                    $vencimiento->where(function ($q) {
                                        $q->whereNotNull('limite_saldo_en')
                                            ->where('limite_saldo_en', '<=', now());
                                    })->orWhere(function ($q) {
                                        $q->whereNull('limite_saldo_en')
                                            ->whereNotNull('expira_en')
                                            ->where('expira_en', '<=', now());
                                    });
                                });
                        });
                });
            });
        } elseif ($situacionSeleccionada === 'revision_pendiente') {
            $consulta->whereIn('proveedor', ['efectivo', 'transferencia'])
                ->whereHas('abonos', fn ($abono) => $abono->where('estado', 'pendiente'));
        } elseif ($situacionSeleccionada === 'saldo_pendiente') {
            $consulta->whereHas('orden', fn ($orden) => $orden->where('estado', 'pendiente_pago'));
        }

        if ($entregaSeleccionada !== '') {
            // Se comprueba la cantidad entregada por detalle, no la fecha ni el estado global.
            $consulta->whereHas('orden', function ($orden) use ($entregaSeleccionada) {
                $orden->where('tipo_orden', 'productos')
                    ->whereHas('detalles', fn ($d) => $d->where('tipo_concepto', 'producto'));

                if ($entregaSeleccionada === 'pendientes') {
                    $orden->whereHas('detalles', fn ($d) => $d
                        ->where('tipo_concepto', 'producto')
                        ->whereColumn('cantidad_entregada', '<', 'cantidad'));
                } else {
                    $orden->whereDoesntHave('detalles', fn ($d) => $d
                        ->where('tipo_concepto', 'producto')
                        ->whereColumn('cantidad_entregada', '<', 'cantidad'));
                }
            });
        }

        return view('admin.pagos.index', [
            'estadosPagos' => $estadosPagos,
            'estadoSeleccionado' => $estadoSeleccionado,
            'metodosPagos' => $metodosPagos,
            'metodoSeleccionado' => $metodoSeleccionado,
            'actividadesFiltro' => $actividadesFiltro,
            'actividadSeleccionada' => $actividadSeleccionada,
            'situacionSeleccionada' => $situacionSeleccionada,
            'entregaSeleccionada' => $entregaSeleccionada,
            // Resumen de TODO el histórico; la actividad aplicada lo acota.
            // Estado/método son filtros de la lista, NO del libro de caja.
            'resumenFinanciero' => $resumen->calcular($actividadSeleccionada ?: null),
            'ordenesSinMetodo' => $ordenesSinMetodo,
            'hayFiltros' => collect($filtros)->contains(fn ($valor) => $valor !== null && $valor !== ''),
            'pagos' => $consulta
                ->orderByRaw("CASE WHEN estado IN ('pendiente', 'parcial') THEN 0 ELSE 1 END")
                ->orderByDesc('id_pago')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function comprobante(Pago $pago)
    {
        abort_unless(
            $pago->proveedor === 'transferencia'
            && $pago->comprobante_path
            && Storage::disk('local')->exists($pago->comprobante_path),
            404
        );

        return Storage::disk('local')->response(
            $pago->comprobante_path,
            null,
            [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function comprobanteAbono(AbonoTransferencia $abono)
    {
        abort_unless(
            $abono->pago?->proveedor === 'transferencia'
            && $abono->comprobante_path
            && Storage::disk('local')->exists($abono->comprobante_path),
            404
        );

        return Storage::disk('local')->response(
            $abono->comprobante_path,
            null,
            [
                'Cache-Control' => 'private, no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function aprobar(Request $request, Pago $pago)
    {
        $datos = $request->validate([
            'monto_recibido' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $orden = $pago->orden()->firstOrFail();
        $faltante = $this->gestion->ejecutar($orden, function (Orden $actual) use ($pago, $request, $datos) {
            $actualPago = Pago::whereKey($pago->id_pago)
                ->lockForUpdate()->firstOrFail();

            if (!in_array($actualPago->proveedor, ['transferencia', 'efectivo'], true)) {
                $this->gestion->error('Los pagos de Wompi se confirman mediante su webhook.');
            }

            $abono = $actualPago->abonos()->where('estado', 'pendiente')->lockForUpdate()->first();
            if (!$abono)
                $this->gestion->error('No existe abono pendiente para validar.');
            if (
                $actualPago->proveedor === 'transferencia' && (!$abono->comprobante_path
                    || !Storage::disk('local')->exists($abono->comprobante_path))
            ) {
                $this->gestion->error('No existe comprobante bancario pendiente y accesible.');
            }
            return $this->gestion->acreditarAbono(
                $actual,
                $actualPago,
                $abono,
                $this->gestion->centavos($datos['monto_recibido']),
                (int) $request->user()->id_usuario,
                $datos['observacion'] ?? null
            );
        });

        return back()->with('success', $faltante > 0
            ? 'Abono acreditado. Quedan $' . $this->gestion->dinero($faltante)
            . ' pendientes; el comprador ya puede enviar otro comprobante.'
            : 'Pago aprobado. La orden quedó pagada.');
    }

    public function rechazar(Request $request, Pago $pago)
    {
        $datos = $request->validate([
            'motivo' => ['required', 'string', 'max:1000'],
        ]);

        $orden = $pago->orden()->firstOrFail();
        $conservarOrden = $this->gestion->ejecutar(
            $orden,
            function (Orden $actual) use ($pago, $datos, $request) {
                $actualPago = Pago::whereKey($pago->id_pago)
                    ->lockForUpdate()->firstOrFail();

                if (
                    !in_array($actualPago->proveedor, ['transferencia', 'efectivo'], true)
                    || !in_array($actualPago->estado, ['pendiente', 'parcial'], true)
                    || $actual->estado !== 'pendiente_pago'
                ) {
                    $this->gestion->error('Este pago ya no se puede rechazar.');
                }

                if (in_array($actualPago->proveedor, ['transferencia', 'efectivo'], true)) {
                    $abono = $actualPago->abonos()
                        ->where('estado', 'pendiente')
                        ->lockForUpdate()->first();

                    if (!$abono) {
                        $this->gestion->error('No existe un comprobante pendiente para rechazar.');
                    }

                    $abono->update([
                        'estado' => 'rechazado',
                        'observacion_revision' => $datos['motivo'],
                        'revisado_por' => $request->user()->id_usuario,
                        'revisado_en' => now(),
                    ]);

                    // Un comprobante complementario rechazado NO borra
                    // el dinero que se acreditó previamente.
                    if ($actualPago->montoAcreditadoCentavos() > 0) {
                        // Reemplazar el intento de cobro en efectivo rechazado.
                        if ($actualPago->proveedor === 'efectivo') {
                            $actualPago->abonos()->create(['metodo' => 'efectivo', 'estado' => 'pendiente']);
                        }
                        $actualPago->update([
                            'estado' => 'parcial',
                            'observacion_revision' => null,
                            'revisado_por' => $request->user()->id_usuario,
                            'revisado_en' => now(),
                        ]);

                        return true;
                    }
                }

                $actualPago->update([
                    'estado' => 'rechazado',
                    'revisado_por' => $request->user()->id_usuario,
                    'revisado_en' => now(),
                    'observacion_revision' => $datos['motivo'],
                ]);

                // Sin dinero acreditado se libera la reserva como antes.
                // Efectivo rechazado: se cancela la solicitud.
                $estadoFinal = $actualPago->proveedor === 'efectivo'
                    ? 'cancelada' : 'vencida';

                $actual->update([
                    'estado' => $estadoFinal,
                    'expira_en' => now(),
                ]);

                if (
                    $actual->tipo_orden === 'inscripcion'
                    && $actual->inscripcion?->estado === 'pendiente_pago'
                ) {
                    $actual->inscripcion->update([
                        'estado' => $estadoFinal,
                        'expira_en' => now(),
                    ]);
                }

                return false;
            }
        );

        return back()->with('success', $conservarOrden
            ? 'Comprobante rechazado. Se conservan los abonos anteriores y la orden sigue disponible.'
            : ($pago->proveedor === 'efectivo'
                ? 'Orden cancelada por administración. La reserva fue liberada.'
                : 'Pago rechazado. La reserva fue liberada; el comprador puede crear otra orden.'));
    }
}