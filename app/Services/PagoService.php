<?php

namespace App\Services;

use App\Models\IntentoPagoWompi;
use App\Models\Orden;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PagoService
{
    public function __construct(
        private readonly WompiService $wompi
    ) {}

    // Crear o recuperar un enlace para una orden pendiente.
    public function iniciar(Orden $orden): string
    {
        $preparado = DB::transaction(function () use ($orden) {
            $orden = Orden::whereKey($orden->id_orden)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $orden->estado !== 'pendiente_pago' ||
                ($orden->expira_en && $orden->expira_en->isPast())
            ) {
                throw ValidationException::withMessages([
                    'pago' => 'Esta orden ya no acepta pagos.',
                ]);
            }

            // Un pago lógico por orden en esta primera versión.
            $pago = Pago::firstOrCreate(
                ['id_orden' => $orden->id_orden],
                [
                    'monto' => $orden->total,
                    'proveedor' => 'wompi',
                    'estado' => 'pendiente',
                ]
            );

            // Reutilizar un enlace vigente cuando ya existe.
            $existente = IntentoPagoWompi::query()
                ->where('id_pago', $pago->id_pago)
                ->whereIn('estado', ['creando', 'pendiente'])
                ->orderByDesc('id_intento')
                ->first();

            if (
                $existente &&
                $existente->url_enlace &&
                (!$existente->expira_en || $existente->expira_en->isFuture())
            ) {
                return ['url' => $existente->url_enlace];
            }

            // Evitar generar varios enlaces simultáneamente.
            if (
                $existente &&
                $existente->estado === 'creando' &&
                $existente->created_at->gt(now()->subMinutes(2))
            ) {
                throw ValidationException::withMessages([
                    'pago' => 'Estamos preparando el enlace. Espera unos segundos.',
                ]);
            }

            if ($existente) {
                $existente->update(['estado' => 'fallido']);
            }

            $intento = IntentoPagoWompi::create([
                'id_pago' => $pago->id_pago,
                'referencia_comercio' =>
                    'SIDAN-ORD-' . $orden->id_orden . '-' .
                    Str::upper(Str::random(14)),
                'monto_solicitado' => $orden->total,
                'estado' => 'creando',
                'expira_en' => $orden->expira_en,
            ]);

            return ['intento' => $intento->id_intento];
        });

        if (isset($preparado['url'])) {
            return $preparado['url'];
        }

        $intento = IntentoPagoWompi::findOrFail(
            $preparado['intento']
        );

        $orden = Orden::with('actividad')
            ->findOrFail($orden->id_orden);

        try {
            $webhookUrl = config('services.wompi.webhook_url');

            if (
                !$webhookUrl ||
                !filter_var($webhookUrl, FILTER_VALIDATE_URL)
            ) {
                throw new RuntimeException(
                    'La URL del webhook no está configurada.'
                );
            }

            $respuesta = $this->wompi->crearEnlacePago([
                'identificadorEnlaceComercio' =>
                    $intento->referencia_comercio,

                'monto' => (float) $orden->total,

                'nombreProducto' =>
                    'Inscripción: ' . $orden->actividad->nombre,

                'configuracion' => [
                    'urlWebhook' => $webhookUrl,

                    'urlRedirect' => route(
                        'sidan.ordenes.show',
                        $orden->id_orden
                    ),

                    'esMontoEditable' => false,
                    'esCantidadEditable' => false,
                    'cantidadPorDefecto' => 1,
                    'notificarTransaccionCliente' => false,
                ],

                'vigencia' => [
                    'fechaFin' => $orden->expira_en
                        ?->toIso8601String(),
                ],

                'limitesDeUso' => [
                    'cantidadMaximaPagosExitosos' => 1,
                ],
            ]);

            if (
                !isset(
                    $respuesta['idEnlace'],
                    $respuesta['urlEnlace']
                )
            ) {
                throw new RuntimeException(
                    'Wompi no devolvió el enlace completo.'
                );
            }

            $intento->update([
                'id_enlace_wompi' => $respuesta['idEnlace'],
                'url_enlace' => $respuesta['urlEnlace'],
                'estado' => 'pendiente',
            ]);

            return $respuesta['urlEnlace'];

        } catch (Throwable $e) {
            // No borrar la operación: conservarla para diagnóstico.
            $intento->update(['estado' => 'fallido']);

            report($e);

            throw ValidationException::withMessages([
                'pago' => 'No se pudo generar el enlace de Wompi.',
            ]);
        }
    }

    // Se ejecuta solamente tras verificar la firma del webhook.
    public function confirmarWebhook(array $datos): void
    {
        if (
            ($datos['ResultadoTransaccion'] ?? null)
            !== 'ExitosaAprobada'
        ) {
            return;
        }

        $referencia =
            $datos['EnlacePago']['IdentificadorEnlaceComercio']
            ?? null;

        $idEnlace = $datos['EnlacePago']['Id'] ?? null;
        $idTransaccion = $datos['IdTransaccion'] ?? null;

        if (
            !is_string($referencia) ||
            !is_scalar($idEnlace) ||
            !is_string($idTransaccion) ||
            $idTransaccion === ''
        ) {
            throw new RuntimeException(
                'La notificación de Wompi está incompleta.'
            );
        }

        DB::transaction(function () use (
            $datos,
            $referencia,
            $idEnlace,
            $idTransaccion
        ) {
            $intento = IntentoPagoWompi::query()
                ->where('referencia_comercio', $referencia)
                ->lockForUpdate()
                ->firstOrFail();

            $pago = Pago::whereKey($intento->id_pago)
                ->lockForUpdate()
                ->firstOrFail();

            $orden = Orden::whereKey($pago->id_orden)
                ->lockForUpdate()
                ->firstOrFail();

            $inscripcion = $orden->inscripcion;

            // Validar que el enlace sea el generado por SIDAN.
            if (
                !$intento->id_enlace_wompi ||
                (string) $intento->id_enlace_wompi !== (string) $idEnlace
            ) {
                throw new RuntimeException(
                    'El identificador del enlace no coincide.'
                );
            }

            // Comparar cantidades monetarias usando centavos enteros.
            if (
                $this->centavos($datos['Monto'] ?? null)
                    !== $this->centavos($intento->monto_solicitado)
                ||
                $this->centavos($pago->monto)
                    !== $this->centavos($orden->total)
            ) {
                throw new RuntimeException(
                    'El monto recibido no coincide con la orden.'
                );
            }

            if (!is_bool($datos['EsProductiva'] ?? null)) {
                throw new RuntimeException(
                    'No es posible verificar el ambiente de Wompi.'
                );
            }

            $productiva = $datos['EsProductiva'];

            if (app()->environment('production') && !$productiva) {
                throw new RuntimeException(
                    'No se aceptan pagos de prueba en producción.'
                );
            }

            // Evitar asociar varias transacciones al mismo intento.
            if (
                $intento->id_transaccion_wompi !== null &&
                $intento->id_transaccion_wompi !== $idTransaccion
            ) {
                throw new RuntimeException(
                    'El enlace ya tiene otra transacción registrada.'
                );
            }

            // Webhook repetido: no volver a confirmar ni cobrar.
            if (in_array($intento->estado, [
                'aprobado',
                'aprobado_prueba',
            ], true)) {
                return;
            }

            $intento->update([
                'id_transaccion_wompi' => $idTransaccion,
                'estado' => $productiva
                    ? 'aprobado'
                    : 'aprobado_prueba',
                'es_productiva' => $productiva,
            ]);

            $pago->update([
                'estado' => $productiva
                    ? 'confirmado'
                    : 'confirmado_prueba',
                'fecha_confirmacion' => now(),
            ]);

            // Confirmar la inscripción solo si el cupo
            // provisional todavía estaba vigente.
            $puedeConfirmar =
                $orden->estado === 'pendiente_pago' &&
                $inscripcion !== null &&
                $inscripcion->estado === 'pendiente_pago' &&
                $orden->expira_en !== null &&
                !$orden->expira_en->isPast();

            if ($puedeConfirmar) {
                $orden->update([
                    'estado' => $productiva
                        ? 'pagada'
                        : 'pagada_prueba',
                ]);

                $inscripcion->update([
                    'estado' => $productiva
                        ? 'confirmada'
                        : 'confirmada_prueba',
                    'fecha_confirmacion' => now(),
                ]);
            } else {
                // Un pago tardío requiere conciliación.
                // Nunca apropiarse automáticamente de otro cupo.
                $orden->update([
                    'estado' => 'revision_cupo',
                ]);

                if ($inscripcion) {
                    $inscripcion->update([
                        'estado' => 'revision_cupo',
                    ]);
                }
            }

            // Un único recibo interno por pago confirmado.
            DB::table('tbl_comprobantes')->insertOrIgnore([
                'id_pago' => $pago->id_pago,
                'numero' => 'SIDAN-REC-' . $pago->id_pago,
                'tipo' => 'recibo_interno',
                'monto' => $pago->monto,
                'fecha_emision' => now(),
                'es_productiva' => $productiva,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, 3);
    }

    private function centavos(mixed $monto): int
    {
        if (
            !is_string($monto) &&
            !is_int($monto) &&
            !is_float($monto)
        ) {
            throw new RuntimeException('Monto inválido.');
        }

        $valor = (string) $monto;

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $valor)) {
            throw new RuntimeException(
                'Formato monetario inválido.'
            );
        }

        [$entero, $decimales] = array_pad(
            explode('.', $valor, 2),
            2,
            ''
        );

        return ((int) $entero * 100)
            + (int) str_pad($decimales, 2, '0');
    }
}
