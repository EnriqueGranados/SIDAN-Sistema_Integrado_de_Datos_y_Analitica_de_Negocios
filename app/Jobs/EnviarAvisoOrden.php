<?php

namespace App\Jobs;

use App\Mail\AvisoOrdenMail;
use App\Models\Orden;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Un evento por orden y referencia; no se factura ni se registra un pago aquí. */
class EnviarAvisoOrden implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(public int $ordenId, public string $evento, public string $referencia = '') {}

    public function handle(): void
    {
        if (!config('sidan-avisos.habilitados')) return;
        $orden = Orden::with(['actividad', 'pago.abonos'])->find($this->ordenId);
        if (!$orden || !filter_var($orden->correo_contacto, FILTER_VALIDATE_EMAIL)) return;
        // La restricción única asegura idempotencia, incluso con workers concurrentes.
        $insertados = DB::table('tbl_avisos_orden')->insertOrIgnore([
            'id_orden' => $this->ordenId, 'evento' => $this->evento,
            'referencia' => $this->referencia, 'created_at' => now('UTC'), 'updated_at' => now('UTC'),
        ]);
        if (!$insertados) return;
        try {
            Mail::to($orden->correo_contacto)->send(new AvisoOrdenMail($orden, $this->evento, $this->referencia));
            DB::table('tbl_avisos_orden')
                ->where('id_orden', $this->ordenId)->where('evento', $this->evento)->where('referencia', $this->referencia)
                ->update(['enviado_en' => now('UTC'), 'updated_at' => now('UTC')]);
        } catch (Throwable $e) {
            DB::table('tbl_avisos_orden')
                ->where('id_orden', $this->ordenId)->where('evento', $this->evento)->where('referencia', $this->referencia)
                ->whereNull('enviado_en')->delete();
            Log::warning('SIDAN: no se pudo enviar correo de orden', ['orden' => $this->ordenId, 'evento' => $this->evento, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
