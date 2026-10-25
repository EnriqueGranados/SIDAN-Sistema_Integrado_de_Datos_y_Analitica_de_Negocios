<?php

namespace App\Http\Controllers;

use App\Models\AbonoTransferencia;
use App\Models\Orden;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Resúmenes y recibos NO FISCALES; sin modificar saldos ni pagos. */
class RecibosOrdenController extends Controller
{
    private function autorizacion(Request $request, Orden $orden, bool $admin = false): void
    {
        abort_if($admin && !$request->user(), 403);
        if ($admin) return; // Protegido por grupo admin y AccesoModulos.
        $propio = $request->user() && $orden->id_usuario !== null
            && (int) $orden->id_usuario === (int) $request->user()->id_usuario;
        $sesion = $request->session()->has('sidan.ordenes.'.$orden->id_orden);
        abort_unless($propio || $sesion, 403);
    }

    private function pdf(Orden $orden, ?AbonoTransferencia $abono = null)
    {
        abort_unless(class_exists(\Barryvdh\DomPDF\Facade\Pdf::class), 503,
            'Para descargar PDF instalá barryvdh/laravel-dompdf.');
        $orden->load(['actividad', 'detalles', 'pago.abonos']);
        $pago = $orden->pago;
        if ($abono) {
            abort_unless($pago && (int) $abono->id_pago === (int) $pago->id_pago && $abono->estado === 'confirmado', 404);
        }
        $centavos = fn ($v) => (int) round((float) $v * 100);
        $acreditado = 0;
        if ($pago && in_array($pago->proveedor, ['efectivo', 'transferencia'], true)) {
            $acreditado = $pago->abonos->where('estado', 'confirmado')->sum(fn ($a) => $centavos($a->monto_recibido));
        } elseif ($pago && in_array($pago->proveedor, ['wompi', 'promocion'], true)
            && $pago->estado === 'confirmado' && $orden->estado === 'pagada') {
            $acreditado = $centavos($orden->total);
        }
        $total = $centavos($orden->total);
        $tipo = $abono ? 'recibo' : 'orden';
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.recibo-orden', [
            'orden' => $orden, 'pago' => $pago, 'abono' => $abono, 'total' => $total,
            'acreditado' => $acreditado, 'saldo' => max(0, $total - $acreditado),
        ])->setPaper('a4');
        return $pdf->download('SIDAN-'.($tipo === 'recibo' ? 'abono-'.$abono->id_abono : 'orden-'.$orden->id_orden).'.pdf');
    }

    public function orden(Request $request, Orden $orden)
    {
        $this->autorizacion($request, $orden);
        return $this->pdf($orden);
    }
    public function adminOrden(Request $request, Orden $orden)
    {
        $this->autorizacion($request, $orden, true);
        return $this->pdf($orden);
    }
    public function abono(Request $request, Orden $orden, AbonoTransferencia $abono)
    {
        $this->autorizacion($request, $orden);
        return $this->pdf($orden, $abono);
    }
    public function adminAbono(Request $request, Orden $orden, AbonoTransferencia $abono)
    {
        $this->autorizacion($request, $orden, true);
        return $this->pdf($orden, $abono);
    }
}
