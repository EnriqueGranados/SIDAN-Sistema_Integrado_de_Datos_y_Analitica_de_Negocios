<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\Pago;
use App\Services\GestionOrdenService;
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
                $this->gestion->registrarManual($actual, $datos['metodo'], $archivo);
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

        $archivo = $this->gestion->ejecutar(
            $orden,
            fn (Orden $actual) => $this->gestion->cancelar($actual)
        );

        if ($archivo) {
            Storage::disk('local')->delete($archivo);
        }

        $request->session()->forget('sidan.ordenes.' . $orden->id_orden);

        $destino = $request->user()?->rol?->nombre === 'usuario'
            ? 'user.ordenes.index'
            : 'welcome';

        return redirect()->route($destino)
            ->with('success', 'La orden fue cancelada y eliminada.');
    }

    public function index()
    {
        return view('admin.pagos.index', [
            'pagos' => Pago::with(['orden.actividad', 'orden.detalles'])
                ->orderByRaw("CASE WHEN estado = 'pendiente' THEN 0 ELSE 1 END")
                ->orderByDesc('id_pago')
                ->paginate(20),
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

    public function aprobar(Request $request, Pago $pago)
    {
        $orden = $pago->orden()->firstOrFail();

        $this->gestion->ejecutar($orden, function (Orden $actual) use ($pago, $request) {
            $actualPago = Pago::whereKey($pago->id_pago)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($actualPago->proveedor, ['transferencia', 'efectivo'], true)) {
                $this->gestion->error('Los pagos de Wompi se confirman mediante su webhook.');
            }

            if ($actualPago->proveedor === 'transferencia') {
                if (
                    !$actualPago->comprobante_path
                    || !Storage::disk('local')->exists($actualPago->comprobante_path)
                ) {
                    $this->gestion->error('No se encontró el comprobante de transferencia.');
                }
            }

            $this->gestion->confirmar(
                $actual,
                $actualPago,
                (int) $request->user()->id_usuario
            );
        });

        return back()->with('success', 'Pago aprobado. La orden quedó pagada.');
    }

    public function rechazar(Request $request, Pago $pago)
    {
        $datos = $request->validate([
            'motivo' => ['required', 'string', 'max:1000'],
        ]);

        $orden = $pago->orden()->firstOrFail();

        $this->gestion->ejecutar($orden, function (Orden $actual) use ($pago, $datos, $request) {
            $actualPago = Pago::whereKey($pago->id_pago)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                !in_array($actualPago->proveedor, ['transferencia', 'efectivo'], true)
                || $actualPago->estado !== 'pendiente'
                || $actual->estado !== 'pendiente_pago'
            ) {
                $this->gestion->error('Este pago ya no se puede rechazar.');
            }

            $actualPago->update([
                'estado' => 'rechazado',
                'revisado_por' => $request->user()->id_usuario,
                'revisado_en' => now(),
                'observacion_revision' => $datos['motivo'],
            ]);

            $actual->update([
                'estado' => 'vencida',
                'expira_en' => now(),
            ]);

            if (
                $actual->tipo_orden === 'inscripcion'
                && $actual->inscripcion?->estado === 'pendiente_pago'
            ) {
                $actual->inscripcion->update([
                    'estado' => 'vencida',
                    'expira_en' => now(),
                ]);
            }
        });

        return back()->with(
            'success',
            'Pago rechazado. La reserva fue liberada; el comprador puede crear otra orden.'
        );
    }
}