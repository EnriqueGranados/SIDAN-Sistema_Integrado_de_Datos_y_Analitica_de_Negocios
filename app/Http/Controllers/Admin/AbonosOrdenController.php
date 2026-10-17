<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Services\AbonosProductosService;
use App\Services\GestionOrdenService;
use App\Services\VencimientosOrdenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AbonosOrdenController extends Controller
{
    public function __construct(private readonly GestionOrdenService $gestion,
        private readonly AbonosProductosService $abonos) {}

    public function vencidas(VencimientosOrdenService $vencimientos)
    {
        $vencimientos->sincronizar();
        return view('admin.pagos.vencidas', [
            'ordenes'=>Orden::with(['actividad','detalles','pago.abonos'])
                ->where('cierre_financiero_estado','pendiente_revision')
                ->orderBy('limite_saldo_en')->paginate(20),
        ]);
    }

    public function cerrar(Request $request, Orden $orden)
    {
        $data=$request->validate([
            'clasificacion'=>['required','in:pendiente_clasificacion,ingreso_no_ejercido'],
            'justificacion'=>['required','string','min:15','max:2000'],
            'declaracion'=>['accepted'],
        ]);
        $this->gestion->ejecutar($orden,function (Orden $actual) use ($data,$request) {
            if ($actual->estado!=='vencida' || $actual->cierre_financiero_estado!=='pendiente_revision') {
                throw ValidationException::withMessages(['cierre'=>'Esta orden no está pendiente de cierre financiero.']);
            }
            $pago=$actual->pago;
            if (!$pago || !in_array($pago->proveedor,['efectivo','transferencia'],true)
                || $pago->abonos()->where('estado','pendiente')->where('metodo','transferencia')->exists()) {
                throw ValidationException::withMessages(['cierre'=>'Conciliá todos los comprobantes antes del cierre.']);
            }
            // Cerrar únicamente solicitudes futuras vacías de efectivo; nunca
            // cambiar estados ni importes de abonos ya confirmados.
            $pago->abonos()->where('estado','pendiente')->where('metodo','efectivo')
                ->update(['estado'=>'rechazado',
                    'observacion_revision'=>'Cobro no realizado antes del vencimiento de la orden.',
                    'revisado_por'=>$request->user()->id_usuario,'revisado_en'=>now(),
                    'updated_at'=>now()]);
            $recibido=$pago->montoAcreditadoCentavos();
            if ($recibido<=0) throw ValidationException::withMessages(['cierre'=>'No existe dinero acreditado.']);
            $entregado=0;
            $hayPagadoSinEntregar=false;
            foreach($actual->detalles as $detalle) {
                $abonadoDetalle=$this->abonos->aplicado($detalle);
                if ($abonadoDetalle >= $this->abonos->neto($detalle)
                    && (int)$detalle->cantidad_entregada < (int)$detalle->cantidad) {
                    $hayPagadoSinEntregar=true;
                }
                if ((int)$detalle->cantidad_entregada>0) $entregado+=$this->abonos->neto($detalle);
            }
            $entregado=min($entregado,$recibido);
            if ($hayPagadoSinEntregar && $data['clasificacion']==='ingreso_no_ejercido') {
                throw ValidationException::withMessages(['cierre'=>
                    'Hay productos pagados pendientes de entrega. Clasificá su dinero como obligación pendiente.']);
            }
            DB::table('tbl_cierres_orden')->insert([
                'id_orden'=>$actual->id_orden,
                'autorizado_por'=>$request->user()->id_usuario,
                'dinero_recibido'=>$this->abonos->dinero($recibido),
                'importe_productos_entregados'=>$this->abonos->dinero($entregado),
                'anticipo_sin_entrega'=>$this->abonos->dinero(max(0,$recibido-$entregado)),
                'clasificacion'=>$data['clasificacion'],
                'justificacion'=>$data['justificacion'],
                'created_at'=>now(),'updated_at'=>now(),
            ]);
            $actual->update(['cierre_financiero_estado'=>'autorizado']);
            // No contabilizar el monto como un cobro nuevo: el efectivo ya está
            // incluido en los abonos verificados; ni marcar la orden como pagada.
        });
        return back()->with('success','Cierre financiero autorizado y registrado sin modificar los abonos.');
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
