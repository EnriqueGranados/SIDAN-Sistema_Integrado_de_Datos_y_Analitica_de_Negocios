<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Resumen histórico de caja, ventas entregadas y fondos por reconocer. */
class ResumenFinancieroPagosService
{
    public function calcular(?int $actividad = null): array
    {
        $filtrar = static fn ($query) => $query->when($actividad !== null && $actividad > 0,
            fn ($q) => $q->where('o.id_actividad', $actividad));

        // Un abono confirmado es la única fuente de dinero recibido en
        // efectivo/transferencia. NO sumar también tbl_pagos.monto.
        $manual = $filtrar(DB::table('tbl_abonos_transferencia as a')
            ->join('tbl_pagos as p', 'p.id_pago', '=', 'a.id_pago')
            ->join('tbl_ordenes as o', 'o.id_orden', '=', 'p.id_orden')
            ->whereIn('p.proveedor', ['efectivo', 'transferencia'])
            ->where('a.estado', 'confirmado'))
            ->sum('a.monto_recibido');

        // Wompi no usa el libro de abonos manuales: una fila por pago.
        $wompi = $filtrar(DB::table('tbl_pagos as p')
            ->join('tbl_ordenes as o', 'o.id_orden', '=', 'p.id_orden')
            ->where('p.proveedor', 'wompi')->where('p.estado', 'confirmado'))
            ->sum('p.monto');

        // Los artículos se reconocen al entregarse completamente. Los
        // servicios solo cuando SIDAN haya registrado su fecha financiera.
        $detalles = fn () => DB::table('tbl_detalles_orden as d')
            ->join('tbl_ordenes as o', 'o.id_orden', '=', 'd.id_orden')
            ->whereNotNull('d.fecha_confirmacion_financiera');
        $productos = $filtrar($detalles()->where('d.tipo_concepto', 'producto')
            ->whereColumn('d.cantidad_entregada', '>=', 'd.cantidad'))
            ->sum('d.venta_neta_confirmada');
        $servicios = $filtrar($detalles()->where('d.tipo_concepto', '<>', 'producto'))
            ->sum('d.venta_neta_confirmada');

        $cierres = fn () => DB::table('tbl_cierres_orden as c')
            ->join('tbl_ordenes as o', 'o.id_orden', '=', 'c.id_orden');
        $noEjercidos = $filtrar($cierres()->where('c.clasificacion', 'ingreso_no_ejercido'))
            ->sum('c.anticipo_sin_entrega');
        $porClasificar = $filtrar($cierres()->where('c.clasificacion', 'pendiente_clasificacion'))
            ->sum('c.anticipo_sin_entrega');

        $abonosPorPago = DB::table('tbl_abonos_transferencia')->select('id_pago')
            ->selectRaw("SUM(CASE WHEN estado='confirmado' THEN COALESCE(monto_recibido,0) ELSE 0 END) AS recibido")
            ->groupBy('id_pago');
        $pendientes = DB::table('tbl_ordenes as o')
            ->leftJoin('tbl_pagos as p', 'p.id_orden', '=', 'o.id_orden')
            ->leftJoinSub($abonosPorPago, 'a', 'a.id_pago', '=', 'p.id_pago')
            ->where('o.estado', 'pendiente_pago')
            ->where(fn ($q) => $q->whereNull('o.limite_saldo_en')->orWhere('o.limite_saldo_en', '>', now()))
            ->where(fn ($q) => $q->whereNull('o.expira_en')->orWhere('o.expira_en', '>', now()));
        $porCobrar = $filtrar($pendientes)->selectRaw("COALESCE(SUM(GREATEST(o.total - CASE
                WHEN p.proveedor IN ('efectivo','transferencia') THEN COALESCE(a.recibido,0)
                WHEN p.proveedor='wompi' AND p.estado='confirmado' THEN p.monto
                ELSE 0 END, 0)),0) AS importe")->value('importe');

        $vencidas = $filtrar(DB::table('tbl_ordenes as o')
            ->where('o.cierre_financiero_estado', 'regularizacion'))->count();

        $regularizacion = $filtrar(DB::table('tbl_ordenes as o')
            ->leftJoin('tbl_pagos as p', 'p.id_orden', '=', 'o.id_orden')
            ->leftJoinSub($abonosPorPago, 'a', 'a.id_pago', '=', 'p.id_pago')
            ->where('o.estado', 'vencida')->where('o.cierre_financiero_estado', 'regularizacion'))
            ->selectRaw('COALESCE(SUM(GREATEST(o.total - COALESCE(a.recibido,0),0)),0) AS importe')
            ->value('importe');

        $caja = $this->centavos($manual) + $this->centavos($wompi);
        $ventas = $this->centavos($productos) + $this->centavos($servicios);
        $otrosReconocidos = $this->centavos($noEjercidos);
        return [
            'recaudado' => $this->moneda($caja),
            'ventas_realizadas' => $this->moneda($ventas),
            'ventas_productos' => $this->moneda($this->centavos($productos)),
            'servicios_reconocidos' => $this->moneda($this->centavos($servicios)),
            'por_reconocer' => $this->moneda(max(0, $caja - $ventas - $otrosReconocidos)),
            'saldo_activo' => $this->moneda($this->centavos($porCobrar)),
            'saldo_regularizacion' => $this->moneda($this->centavos($regularizacion)),
            'no_ejercidos' => $this->moneda($otrosReconocidos),
            'retenidos_por_clasificar' => $this->moneda($this->centavos($porClasificar)),
            'vencidas_revision' => $vencidas,
            'descuadre' => $ventas + $otrosReconocidos > $caja,
        ];
    }

    private function centavos(mixed $monto): int
    {
        return (int) round((float) ($monto ?? 0) * 100);
    }

    private function moneda(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }
}
