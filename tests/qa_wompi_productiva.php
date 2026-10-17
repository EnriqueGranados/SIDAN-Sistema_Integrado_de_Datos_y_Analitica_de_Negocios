
<?php

/**
 * SIDAN | Prueba local reversible del flujo productivo Wompi.
 *
 * Ejecutar desde Tinker, nunca como webhook público:
 * require base_path('tests/qa_wompi_productiva.php');
 */

(static function (): void {
    // CAMBIAR ESTE ID por una orden NUEVA y vigente con intento Wompi pendiente.
    $idOrden = 77;

    if (!app()->environment(['local', 'testing'])) {
        throw new RuntimeException('Esta prueba solamente se permite con APP_ENV=local o testing.');
    }

    $db = \Illuminate\Support\Facades\DB::connection();

    if ($db->transactionLevel() !== 0) {
        throw new RuntimeException('Hay una transacción abierta. Cerrá Tinker y volvé a empezar.');
    }

    $intento = $db->table('tbl_intentos_pago_wompi as i')
        ->join('tbl_pagos as p', 'p.id_pago', '=', 'i.id_pago')
        ->join('tbl_ordenes as o', 'o.id_orden', '=', 'p.id_orden')
        ->where('o.id_orden', $idOrden)
        ->where('o.tipo_orden', 'productos')
        ->where('o.estado', 'pendiente_pago')
        ->where('p.proveedor', 'wompi')
        ->where('p.estado', 'pendiente')
        ->where('i.estado', 'pendiente')
        ->whereNotNull('i.id_enlace_wompi')
        ->select(
            'i.id_intento',
            'i.referencia_comercio',
            'i.id_enlace_wompi',
            'i.monto_solicitado',
            'o.expira_en'
        )
        ->orderByDesc('i.id_intento')
        ->first();

    if ($intento === null) {
        throw new RuntimeException('No hay un intento Wompi pendiente de una orden de productos con ese ID.');
    }

    if (
        $intento->expira_en === null ||
        \Carbon\Carbon::parse($intento->expira_en)->isPast()
    ) {
        throw new RuntimeException('La orden ya venció o no tiene vencimiento válido. Creá otra orden.');
    }

    echo "\n=== SIDAN: SIMULACION PRODUCTIVA DE WOMPI ===\n";
    echo "Entorno: " . app()->environment() . "\n";
    echo "Base de datos: " . $db->getDatabaseName() . "\n";
    echo "Orden: #{$idOrden} | Importe: \${$intento->monto_solicitado}\n";

    $db->beginTransaction();
    $revertida = false;

    try {
        // Invoca el servicio real de SIDAN, NO el endpoint HTTP de Wompi.
        app(\App\Services\PagoService::class)->confirmarWebhook([
            'ResultadoTransaccion' => 'ExitosaAprobada',
            'EnlacePago' => [
                'IdentificadorEnlaceComercio' => $intento->referencia_comercio,
                'Id' => $intento->id_enlace_wompi,
            ],
            'IdTransaccion' => 'QA-SIDAN-' . \Illuminate\Support\Str::random(12),
            'Monto' => (string) $intento->monto_solicitado,
            'EsProductiva' => true,
        ]);

        $filas = $db->table('tbl_ordenes as o')
            ->join('tbl_pagos as p', 'p.id_orden', '=', 'o.id_orden')
            ->join('tbl_intentos_pago_wompi as i', 'i.id_pago', '=', 'p.id_pago')
            ->join('tbl_detalles_orden as d', 'd.id_orden', '=', 'o.id_orden')
            ->where('o.id_orden', $idOrden)
            ->where('i.id_intento', $intento->id_intento)
            ->select(
                'o.id_orden',
                'o.estado as estado_orden',
                'p.estado as estado_pago',
                'i.estado as estado_intento',
                'i.es_productiva',
                'd.nombre_concepto',
                'd.cantidad',
                'd.precio_unitario',
                'd.descuento',
                'd.costo_unitario_historico',
                'd.venta_neta_confirmada',
                'd.costo_total_confirmado',
                'd.ganancia_bruta_confirmada',
                'd.fecha_confirmacion_financiera'
            )
            ->get();

        echo "\n=== RESULTADOS DENTRO DE LA TRANSACCION ===\n";
        dump($filas->toArray());

        $aprobada = $filas->isNotEmpty() && $filas->every(static function ($fila): bool {
            if (
                $fila->estado_orden !== 'pagada' ||
                $fila->estado_pago !== 'confirmado' ||
                $fila->estado_intento !== 'aprobado' ||
                !(bool) $fila->es_productiva ||
                $fila->costo_unitario_historico === null ||
                $fila->venta_neta_confirmada === null ||
                $fila->costo_total_confirmado === null ||
                $fila->ganancia_bruta_confirmada === null ||
                $fila->fecha_confirmacion_financiera === null
            ) {
                return false;
            }

            $gananciaCalculada = (float) $fila->venta_neta_confirmada - (float) $fila->costo_total_confirmado;
            return abs($gananciaCalculada - (float) $fila->ganancia_bruta_confirmada) < 0.005;
        });

        echo "\n";
        echo $aprobada
            ? "PRUEBA EXITOSA: Wompi productivo simulado y triggers correctos.\n"
            : "PRUEBA NO SUPERADA: revisar estados o montos.\n";
    } catch (\Throwable $e) {
        echo "ERROR EN LA PRUEBA: {$e->getMessage()}\n";
        throw $e;
    } finally {
        if ($db->transactionLevel() > 0) {
            $db->rollBack();
            $revertida = true;
        }
        echo $revertida
            ? "TRANSACCION REVERTIDA: ninguna venta simulada se guardo.\n"
            : "ADVERTENCIA: revisar el estado de la transaccion.\n";
    }

    echo "\n=== COMPROBACION DESPUES DEL ROLLBACK ===\n";
    dump([
        'estado_orden' => $db->table('tbl_ordenes')->where('id_orden', $idOrden)->value('estado'),
        'detalle' => $db->table('tbl_detalles_orden')
            ->where('id_orden', $idOrden)
            ->get([
                'costo_unitario_historico',
                'venta_neta_confirmada',
                'costo_total_confirmado',
                'ganancia_bruta_confirmada',
            ])->toArray(),
    ]);
})();
