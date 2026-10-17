<?php

/**
 * SIDAN | QA REVERSIBLE del webhook productivo de Wompi.
 *
 * Uso en Tinker (APP_ENV=local o testing):
 *   $qaOrdenWompi = 38;  // cambiá al ID real
 *   $qaProbarEntrega = false; // true: también simula entregar productos dentro del rollback
 *   require base_path('tests/qa_wompi_productiva.php');
 *
 * Nunca llama a la API de Wompi ni realiza un cobro real.
 * La simulación usa PagoService::confirmarWebhook() y revierte cambios de BD.
 */

(static function (?int $idOrden, bool $probarEntrega): void {
    if (!app()->environment(['local', 'testing'])) {
        throw new RuntimeException('Solo ejecutar con APP_ENV=local o testing.');
    }

    $db = \Illuminate\Support\Facades\DB::connection();
    if ($db->transactionLevel() !== 0) {
        throw new RuntimeException('Ya existe una transacción abierta. Reiniciá Tinker.');
    }

    $candidatos = $db->table('tbl_intentos_pago_wompi as i')
        ->join('tbl_pagos as p', 'p.id_pago', '=', 'i.id_pago')
        ->join('tbl_ordenes as o', 'o.id_orden', '=', 'p.id_orden')
        ->where('o.tipo_orden', 'productos')
        ->where('o.estado', 'pendiente_pago')
        ->where('p.proveedor', 'wompi')
        ->where('p.estado', 'pendiente')
        ->where('i.estado', 'pendiente')
        ->whereNotNull('i.id_enlace_wompi')
        ->select(
            'i.id_intento', 'i.id_pago', 'i.referencia_comercio', 'i.id_enlace_wompi',
            'i.monto_solicitado', 'o.id_orden', 'o.total', 'o.estado as estado_orden',
            'o.expira_en', 'o.modalidad_pago'
        )
        ->orderByDesc('i.id_intento');

    if ($idOrden !== null) {
        $candidatos->where('o.id_orden', $idOrden);
    }

    $intento = $candidatos->first();

    if ($intento === null) {
        echo "\nNo se encontró un enlace Wompi PENDIENTE que cumpla las condiciones.\n";
        echo "Revisá: ID de orden, estado pendiente_pago, pago Wompi pendiente, "
           . "intento pendiente e id_enlace_wompi no nulo.\n";
        if ($idOrden !== null) {
            dump($db->table('tbl_ordenes as o')
                ->leftJoin('tbl_pagos as p', 'p.id_orden', '=', 'o.id_orden')
                ->leftJoin('tbl_intentos_pago_wompi as i', 'i.id_pago', '=', 'p.id_pago')
                ->where('o.id_orden', $idOrden)
                ->select('o.id_orden', 'o.estado as orden', 'o.expira_en',
                    'p.proveedor', 'p.estado as pago', 'i.estado as intento',
                    'i.id_enlace_wompi')->get()->toArray());
        }
        return;
    }

    $idOrden = (int) $intento->id_orden;
    if ($intento->expira_en === null || \Carbon\Carbon::parse($intento->expira_en)->isPast()) {
        echo "\nOrden #{$idOrden}: el plazo de pago ya venció; no se simulará confirmación normal.\n";
        return;
    }

    // Consulta de la versión anterior, conservando el detalle financiero por producto.
    // Se ejecuta en la misma transacción tanto antes como después de simular la entrega.
    $consultarDetalleFinanciero = static function () use ($db, $idOrden, $intento) {
        return $db->table('tbl_ordenes as o')
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
                'd.fecha_confirmacion_financiera',
                // Extras para interpretar la rentabilidad diferida:
                'd.id_detalle_orden',
                'd.tipo_concepto',
                'd.cantidad_entregada'
            )
            ->orderBy('d.id_detalle_orden')
            ->get();
    };

    $previo = [
        'orden' => $db->table('tbl_ordenes')->where('id_orden', $idOrden)->first(),
        'pago' => $db->table('tbl_pagos')->where('id_pago', $intento->id_pago)->first(),
        'intento' => $db->table('tbl_intentos_pago_wompi')->where('id_intento', $intento->id_intento)->first(),
        'detalles' => $db->table('tbl_detalles_orden')->where('id_orden', $idOrden)
            ->orderBy('id_detalle_orden')->get()->toArray(),
    ];

    echo "\n=== SIDAN QA WOMPI (REVERSIBLE) ===\n";
    echo "Entorno: " . app()->environment() . " | BD: {$db->getDatabaseName()}\n";
    echo "Orden: #{$idOrden} | Total: \${$intento->total} | Enlace: {$intento->id_enlace_wompi}\n";
    echo "No se llama a Wompi ni se procesa un cobro real.\n";

    $db->beginTransaction();
    $pruebaOk = false;
    try {
        app(\App\Services\PagoService::class)->confirmarWebhook([
            'ResultadoTransaccion' => 'ExitosaAprobada',
            'EnlacePago' => [
                'IdentificadorEnlaceComercio' => $intento->referencia_comercio,
                'Id' => $intento->id_enlace_wompi,
            ],
            'IdTransaccion' => 'QA-SIDAN-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(12)),
            'Monto' => (string) $intento->monto_solicitado,
            'EsProductiva' => true,
        ]);

        $orden = \App\Models\Orden::with(['pago', 'detalles'])->findOrFail($idOrden);
        $pago = $orden->pago;
        $intentoDespues = $db->table('tbl_intentos_pago_wompi')
            ->where('id_intento', $intento->id_intento)->first();

        $pruebaOk = $orden->estado === 'pagada'
            && $pago?->estado === 'confirmado'
            && $intentoDespues?->estado === 'aprobado'
            && (bool) $intentoDespues?->es_productiva;

        echo "\n=== ETAPA 1: WEBHOOK ===\n";
        dump([
            'estado_orden' => $orden->estado,
            'estado_pago' => $pago?->estado,
            'estado_intento' => $intentoDespues?->estado,
            'es_productiva' => (bool) $intentoDespues?->es_productiva,
            'resultado' => $pruebaOk ? 'CORRECTO' : 'FALLO',
        ]);

        echo "\n=== DESGLOSE FINANCIERO DESPUÉS DEL WEBHOOK ===\n";
        $filasAntesEntrega = $consultarDetalleFinanciero();
        dump($filasAntesEntrega->toArray());
        if ($filasAntesEntrega->isEmpty()) {
            $pruebaOk = false;
            echo "ERROR: la orden no tiene detalles consultables.\n";
        }
        // En esta etapa, los productos sin entregar tienen rentabilidad pendiente.
        // Una fila ya contabilizada debe tener importes consistentes.
        foreach ($filasAntesEntrega as $fila) {
            if ($fila->fecha_confirmacion_financiera === null) {
                continue;
            }
            if ($fila->venta_neta_confirmada === null ||
                $fila->costo_total_confirmado === null ||
                $fila->ganancia_bruta_confirmada === null ||
                abs((float) $fila->venta_neta_confirmada
                    - (float) $fila->costo_total_confirmado
                    - (float) $fila->ganancia_bruta_confirmada) >= 0.005) {
                $pruebaOk = false;
                echo "ERROR: importes financieros inconsistentes en {$fila->nombre_concepto}.\n";
            }
        }

        foreach ($orden->detalles as $detalle) {
            $esFisicoSinEntrega = $detalle->tipo_concepto === 'producto'
                && (int) $detalle->cantidad_entregada < (int) $detalle->cantidad;
            echo "  {$detalle->nombre_concepto}: "
                . ($esFisicoSinEntrega ? 'producto pendiente de entrega' : 'servicio/entregado')
                . ' | rentabilidad: ' . ($detalle->fecha_confirmacion_financiera === null ? 'pendiente' : 'registrada')
                . "\n";
            // Regla nueva: un producto físico NO reconoce rentabilidad hasta entregarse.
            if ($esFisicoSinEntrega && $detalle->fecha_confirmacion_financiera !== null) {
                $pruebaOk = false;
                echo "  ERROR: rentabilidad registrada antes de entregar el producto.\n";
            }
        }

        if ($probarEntrega && $pruebaOk) {
            echo "\n=== ETAPA 2: ENTREGA SIMULADA (SOLO EN ESTA TRANSACCIÓN) ===\n";
            $servicio = app(\App\Services\AbonosProductosService::class);
            foreach ($orden->detalles as $detalle) {
                if ($detalle->tipo_concepto !== 'producto') {
                    continue;
                }
                $cantidad = (int) $detalle->cantidad - (int) $detalle->cantidad_entregada;
                if ($cantidad < 1) {
                    continue;
                }
                // ID temporal únicamente para esta simulación reversible.
                $servicio->entregar(
                    $orden, $detalle, $cantidad, (int) $orden->id_usuario,
                    'QA reversible: entrega ficticia (se revierte al final)'
                );
                $detalle->refresh();
                $completo = (int) $detalle->cantidad_entregada === (int) $detalle->cantidad;
                $contabilizado = $detalle->fecha_confirmacion_financiera !== null;
                echo "  {$detalle->nombre_concepto}: "
                    . ($completo ? 'entregado' : 'entrega incompleta')
                    . ' | rentabilidad: ' . ($contabilizado ? 'registrada' : 'sin registrar') . "\n";
                if (!$completo || !$contabilizado) {
                    $pruebaOk = false;
                }
            }

            echo "\n=== DESGLOSE FINANCIERO DESPUÉS DE LA ENTREGA ===\n";
            $filasDespuesEntrega = $consultarDetalleFinanciero();
            dump($filasDespuesEntrega->toArray());

            foreach ($filasDespuesEntrega as $fila) {
                if ($fila->tipo_concepto !== 'producto') {
                    continue;
                }
                if ((int) $fila->cantidad_entregada !== (int) $fila->cantidad ||
                    $fila->costo_unitario_historico === null ||
                    $fila->venta_neta_confirmada === null ||
                    $fila->costo_total_confirmado === null ||
                    $fila->ganancia_bruta_confirmada === null ||
                    $fila->fecha_confirmacion_financiera === null ||
                    abs((float) $fila->venta_neta_confirmada
                        - (float) $fila->costo_total_confirmado
                        - (float) $fila->ganancia_bruta_confirmada) >= 0.005) {
                    $pruebaOk = false;
                    echo "ERROR: entrega/rentabilidad incompleta o inconsistente en {$fila->nombre_concepto}.\n";
                }
            }
        }

        echo "\nRESULTADO: " . ($pruebaOk ? 'PRUEBA EXITOSA' : 'PRUEBA NO SUPERADA') . "\n";
    } catch (\Throwable $e) {
        echo "ERROR EN QA: " . $e->getMessage() . "\n";
        throw $e;
    } finally {
        if ($db->transactionLevel() > 0) {
            $db->rollBack();
            echo "ROLLBACK EJECUTADO: no queda pago ni entrega simulada registrada.\n";
        } else {
            echo "ADVERTENCIA: comprobar estado de la transacción.\n";
        }
    }

    $posterior = [
        'orden' => $db->table('tbl_ordenes')->where('id_orden', $idOrden)->first(),
        'pago' => $db->table('tbl_pagos')->where('id_pago', $intento->id_pago)->first(),
        'intento' => $db->table('tbl_intentos_pago_wompi')->where('id_intento', $intento->id_intento)->first(),
        'detalles' => $db->table('tbl_detalles_orden')->where('id_orden', $idOrden)
            ->orderBy('id_detalle_orden')->get()->toArray(),
    ];
    echo "COMPROBACIÓN POSTERIOR: "
        . (serialize($previo) === serialize($posterior) ? 'estados idénticos' : 'REVISAR diferencias')
        . "\n";
})(isset($qaOrdenWompi) && $qaOrdenWompi !== null ? (int) $qaOrdenWompi : null,
    isset($qaProbarEntrega) ? (bool) $qaProbarEntrega : false);
