<?php
namespace App\Console\Commands;

use App\Models\Orden;
use App\Services\AvisosOrdenService;
use Illuminate\Console\Command;

class ProgramarAvisosOrden extends Command
{
    protected $signature = 'sidan:avisos-orden';
    protected $description = 'Programa una sola vez los recordatorios de órdenes pendientes';

    public function handle(AvisosOrdenService $avisos): int
    {
        if (!config('sidan-avisos.habilitados')) {
            $this->warn('Los correos de órdenes están desactivados (SIDAN_MAIL_ORDENES=false).');
            return self::SUCCESS;
        }
        $ahora = now('UTC');
        $fin = $ahora->copy()->addHours((int) config('sidan-avisos.aviso_vencimiento_horas',48));
        Orden::query()->where('estado','pendiente_pago')
            ->whereBetween('limite_saldo_en', [$ahora, $fin])
            ->where('total','>',0)
            ->orderBy('id_orden')->chunkById(100, function ($ordenes) use ($avisos) {
                foreach($ordenes as $orden) $avisos->programar($orden, 'vencimiento_proximo');
            }, 'id_orden');
        $ventana = $ahora->copy()->addHours((int) config('sidan-avisos.aviso_regularizacion_horas',72));
        Orden::query()->where('estado','vencida')->where('cierre_financiero_estado','regularizacion')
            ->whereRaw("COALESCE(limite_saldo_en, expira_en) + INTERVAL '30 days' > ?",[$ahora])
            ->whereRaw("COALESCE(limite_saldo_en, expira_en) + INTERVAL '30 days' <= ?",[$ventana])
            ->orderBy('id_orden')->chunkById(100, function ($ordenes) use ($avisos) {
                foreach($ordenes as $orden) $avisos->programar($orden, 'regularizacion_por_finalizar');
            }, 'id_orden');
        $this->info('Avisos de vencimiento programados. Los duplicados se descartan en la cola.');
        return self::SUCCESS;
    }
}
