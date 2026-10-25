<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbonoTransferencia;
use App\Models\DetalleOrden;
use App\Models\Pago;
use App\Models\Orden;
use App\Services\AbonosProductosService;
use App\Services\GestionOrdenService;
use App\Services\RegularizacionVencidasService;
use App\Services\VencimientosOrdenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AbonosOrdenController extends Controller
{
    public function __construct(private readonly GestionOrdenService $gestion,
        private readonly AbonosProductosService $abonos,
        private readonly RegularizacionVencidasService $regularizacion) {}

    public function vencidas(Request $request, VencimientosOrdenService $vencimientos)
    {
        $datos = $request->validate([
            'vista' => ['nullable', 'in:abiertas,finalizadas'],
            'actividad' => ['nullable', 'integer', 'exists:tbl_actividades,id_actividad'],
        ]);
        $vencimientos->sincronizar();
        $actividad = (int) ($datos['actividad'] ?? 0);
        $vista = $datos['vista'] ?? 'abiertas';
        $base = Orden::query()
            ->whereHas('pago', fn ($q) => $q->whereIn('proveedor', ['efectivo', 'transferencia'])
                ->whereHas('abonos', fn ($a) => $a->where('estado', 'confirmado')->where('monto_recibido', '>', 0)))
            ->when($actividad, fn ($q) => $q->where('id_actividad', $actividad));
        $abiertas = (clone $base)->where('estado', 'vencida')
            ->where('cierre_financiero_estado', 'regularizacion')->count();
        $finalizadas = (clone $base)->where(function ($q) {
            $q->whereIn('cierre_financiero_estado', ['finalizada', 'regularizada', 'autorizado'])
                ->orWhere(fn ($pagada) => $pagada->where('estado', 'pagada')
                    ->whereNotNull('finalizada_en'));
        })->count();
        $consulta = clone $base;
        if ($vista === 'abiertas') {
            $consulta->where('estado', 'vencida')->where('cierre_financiero_estado', 'regularizacion');
        } else {
            $consulta->whereIn('cierre_financiero_estado', ['finalizada', 'regularizada', 'autorizado']);
        }
        $ordenes = $consulta->with(['actividad' => fn ($q) => $q->withTrashed(),
                'detalles', 'pago.abonos'])->orderByDesc('id_orden')->paginate(12)->withQueryString();
        return view('admin.pagos.vencidas', compact('ordenes', 'vista', 'actividad', 'abiertas', 'finalizadas'));
    }

    /** Abono presencial: nunca se dispara una transferencia nueva desde una vencida. */
    public function abonar(Request $request, Orden $orden)
    {
        $datos = $request->validate([
            'monto_recibido' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ]);
        $faltante = $this->gestion->ejecutar($orden, function (Orden $actual) use ($request, $datos) {
            return $this->regularizacion->registrarEfectivo(
                $actual, $this->gestion->centavos($datos['monto_recibido']),
                (int) $request->user()->id_usuario, $datos['observacion'] ?? null
            );
        });
        return redirect()->route('admin.ordenes.vencidas', ['vista' => $faltante ? 'abiertas' : 'finalizadas',
            'actividad' => $orden->id_actividad])->with('success', $faltante
                ? 'Efectivo acreditado. Saldo pendiente: $' . $this->gestion->dinero($faltante) . '.'
                : 'Orden completamente pagada. Sus productos pagados pueden entregarse.');
    }

    /** Únicamente concilia comprobantes HISTÓRICOS ya recibidos, nunca habilita transferencias posteriores. */
    public function conciliar(Request $request, Orden $orden, AbonoTransferencia $abono)
    {
        $datos = $request->validate([
            'accion' => ['required', 'in:aprobar,rechazar'],
            'monto_recibido' => ['required_if:accion,aprobar', 'nullable', 'numeric', 'decimal:0,2', 'gt:0'],
            'observacion' => ['nullable', 'string', 'max:1000'],
            'motivo' => ['required_if:accion,rechazar', 'nullable', 'string', 'min:5', 'max:1000'],
        ]);
        $this->gestion->ejecutar($orden, function (Orden $actual) use ($abono, $datos, $request) {
            if (!$this->regularizacion->vigente($actual)) {
                $this->gestion->error('El plazo de 30 días para regularizar esta orden finalizó.');
            }
            $pago = Pago::where('id_orden', $actual->id_orden)->lockForUpdate()->firstOrFail();
            $pendiente = AbonoTransferencia::whereKey($abono->id_abono)->lockForUpdate()->firstOrFail();
            if ($pago->proveedor !== 'transferencia' || (int)$pendiente->id_pago !== (int)$pago->id_pago
                || $pendiente->estado !== 'pendiente' || $pendiente->metodo !== 'transferencia') {
                $this->gestion->error('El comprobante histórico no está disponible para conciliación.');
            }
            if ($datos['accion'] === 'aprobar') {
                $limiteOriginal = $actual->limite_saldo_en ?? $actual->expira_en;
                if (!$pendiente->created_at || $pendiente->created_at->gt($limiteOriginal)) {
                    $this->gestion->error('Solo se pueden conciliar comprobantes enviados antes del vencimiento original.');
                }
                if (!$pendiente->comprobante_path || !Storage::disk('local')->exists($pendiente->comprobante_path)) {
                    $this->gestion->error('El archivo del comprobante no está disponible.');
                }
                $monto = $this->gestion->centavos($datos['monto_recibido']);
                $saldo = $this->gestion->centavos($actual->total) - $pago->montoAcreditadoCentavos();
                if ($monto <= 0 || $monto > $saldo) $this->gestion->error('Monto verificado fuera del saldo pendiente.');
                $actual->loadMissing('detalles');
                $anteriores = [];
                foreach ($actual->detalles as $detalle) {
                    $anteriores[$detalle->id_detalle_orden] =
                        $this->abonos->aplicado($detalle) >= $this->abonos->neto($detalle);
                }
                $pendiente->update(['estado' => 'confirmado',
                    'monto_recibido' => $this->gestion->dinero($monto),
                    'observacion_revision' => $datos['observacion'] ?? null,
                    'revisado_por' => $request->user()->id_usuario, 'revisado_en' => now('UTC')]);
                $this->abonos->distribuir($actual, $pendiente);
                $this->regularizacion->verificarStockTransicion($actual, $anteriores);
                if ($pago->montoAcreditadoCentavos() === $this->gestion->centavos($actual->total)) {
                    $this->regularizacion->completar($actual, $pago, (int)$request->user()->id_usuario);
                } else {
                    $pago->update(['estado' => 'parcial']);
                }
            } else {
                $pendiente->update(['estado' => 'rechazado',
                    'observacion_revision' => $datos['motivo'],
                    'revisado_por' => $request->user()->id_usuario, 'revisado_en' => now('UTC')]);
            }
        });
        return redirect()->route('admin.ordenes.vencidas', [
            'vista' => 'abiertas', 'actividad' => $orden->id_actividad,
        ])->with('success', 'Comprobante anterior conciliado. Los demás abonos fueron conservados.');
    }

    /** Finalización voluntaria anticipada, incluso si quedaron artículos sin pagar. */
    public function cerrar(Request $request, Orden $orden)
    {
        $datos = $request->validate([
            'motivo' => ['required', 'string', 'min:10', 'max:500'],
            'confirmacion' => ['accepted'],
        ]);
        $this->gestion->ejecutar($orden, function (Orden $actual) use ($datos, $request) {
            if (!$this->regularizacion->vigente($actual)) {
                throw ValidationException::withMessages(['orden' => 'La orden no está disponible para finalización voluntaria.']);
            }
            if ($actual->pago?->abonos()->where('estado', 'pendiente')->where('metodo', 'transferencia')->exists()) {
                throw ValidationException::withMessages(['orden' => 'Conciliá primero los comprobantes antiguos pendientes.']);
            }
            $this->regularizacion->finalizar($actual, (int)$request->user()->id_usuario, $datos['motivo']);
        });
        return redirect()->route('admin.ordenes.vencidas', ['vista' => 'finalizadas',
            'actividad' => $orden->id_actividad])->with('success',
                'Orden finalizada. Se conservaron los pagos y las entregas registradas.');
    }

    public function entregar(Request $request, Orden $orden, DetalleOrden $detalle)
    {
        $data=$request->validate(['observacion'=>['nullable','string','max:1000']]);
        $this->gestion->ejecutar($orden,function (Orden $actual) use ($detalle,$data,$request) {
            $d=DetalleOrden::whereKey($detalle->id_detalle_orden)->lockForUpdate()->firstOrFail();
            if ((int)$d->id_orden !== (int)$actual->id_orden || $actual->tipo_orden !== 'productos') {
                throw ValidationException::withMessages(['entrega'=>'Producto ajeno a la orden seleccionada.']);
            }
            // La entrega es por línea completa para evitar prorratear descuentos
            // entre cantidades de una misma línea y registrar ganancia duplicada.
            $cantidad=(int)$d->cantidad-(int)$d->cantidad_entregada;
            if ($cantidad<=0) throw ValidationException::withMessages(['entrega'=>'Este producto ya fue entregado.']);
            if ($actual->estado==='pagada' && $this->abonos->aplicado($d)===0) {
                // Wompi, promocionales y pagos históricos anteriores al libro de abonos.
                if ($actual->pago?->estado!=='confirmado') throw ValidationException::withMessages(['entrega'=>'Pago sin confirmar.']);
                // Producto completamente cubierto con pago confirmado; bypass seguro.
                $d->update(['cantidad_entregada'=>(int)$d->cantidad]);
                DB::table('tbl_entregas_detalle')->insert([
                    'id_detalle_orden'=>$d->id_detalle_orden,'cantidad'=>$cantidad,
                    'entregado_por'=>$request->user()->id_usuario,'observacion'=>$data['observacion']??null,
                    'created_at'=>now(),'updated_at'=>now(),
                ]);
                return;
            }
            $this->abonos->entregar($actual,$d,$cantidad,(int)$request->user()->id_usuario,$data['observacion']??null);
        });
        return back()->with('success','Producto entregado. El movimiento quedó registrado.');
    }
}
