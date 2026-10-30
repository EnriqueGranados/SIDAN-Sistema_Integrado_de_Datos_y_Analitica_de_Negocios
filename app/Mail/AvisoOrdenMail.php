<?php
namespace App\Mail;

use App\Models\Orden;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AvisoOrdenMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(public Orden $orden, public string $evento, public string $referencia = '') {}

    public function build(): self
    {
        $asuntos = [
            'creada' => 'Orden registrada',
            'abono' => 'Pago acreditado',
            'comprobante_recibido' => 'Comprobante pendiente de revisión',
            'pagada' => 'Orden pagada completamente',
            'vencimiento_proximo' => 'Recordatorio de pago pendiente',
            'vencida' => 'Tu orden venció',
            'regularizacion_por_finalizar' => 'Últimos días para regularizar tu orden',
            'finalizada' => 'Orden finalizada',
        ];
        $asunto = $asuntos[$this->evento] ?? 'Actualización de tu orden';
        $correo = $this->subject('SIDAN · '.$asunto.' #'.$this->orden->id_orden)
            ->view('emails.aviso-orden')->with(['tituloEvento' => $asunto]);
        // Enviar también un recibo interno PDF del abono confirmado cuando esté instalado Dompdf.
        if ($this->evento === 'abono' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $this->orden->loadMissing(['actividad','detalles','pago.abonos']);
            $pago = $this->orden->pago;
            $abono = str_starts_with($this->referencia, 'wompi-') ? null
                : $pago?->abonos?->firstWhere('id_abono', (int) $this->referencia);
            if (($abono && $abono->estado === 'confirmado') || ($pago && $pago->proveedor === 'wompi' && $pago->estado === 'confirmado')) {
                $centavos = fn ($valor) => (int) round((float) $valor * 100);
                $total = $centavos($this->orden->total);
                $acreditado = in_array($pago->proveedor, ['transferencia','efectivo'], true)
                    ? $pago->abonos->where('estado','confirmado')->sum(fn ($a) => $centavos($a->monto_recibido))
                    : $total;
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.recibo-orden', [
                    'orden'=>$this->orden,'pago'=>$pago,'abono'=>$abono,
                    'total'=>$total,'acreditado'=>$acreditado,'saldo'=>max(0,$total-$acreditado),
                ])->setPaper('a4')->output();
                $correo->attachData($pdf, 'SIDAN-recibo-'.$this->orden->id_orden.'.pdf', ['mime'=>'application/pdf']);
            }
        }
        return $correo;
    }
}
