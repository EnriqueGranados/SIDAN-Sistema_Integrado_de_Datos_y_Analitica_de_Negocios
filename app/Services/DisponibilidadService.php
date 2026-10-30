<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DisponibilidadService
{
    public function cuposDisponibles(int $idActividad): ?int
    {
        $actividad = DB::table('tbl_actividades')
            ->where('id_actividad', $idActividad)
            ->first();

        if (!$actividad) {
            return 0;
        }

        if ($actividad->cupo_total === null) {
            return null;
        }

        $ocupados = DB::table('tbl_inscripciones')
            ->where('id_actividad', $idActividad)
            ->where(function ($query) {
                $query->whereIn('estado', [
                    'confirmada',
                    'confirmada_prueba',
                ])->orWhere(function ($query) {
                    $query->where('estado', 'pendiente_pago')
                        ->where(function ($reserva) {
                            $reserva->whereNull('expira_en')
                                ->orWhere('expira_en', '>', now('UTC'));
                        });
                });
            })
            ->count();

        return max(0, (int) $actividad->cupo_total - $ocupados);
    }

    public function stockDisponibleItem(int $idItem): ?int
    {
        $item = DB::table('tbl_items_actividad')
            ->where('id_item_actividad', $idItem)
            ->first();

        if (!$item) {
            return 0;
        }

        if ($item->stock_total === null) {
            return null;
        }

        $comprometido = $this->cantidadComprometida(
            'detalle.id_item_actividad',
            $idItem
        );

        return max(0, (int) $item->stock_total - $comprometido);
    }

    public function stockDisponibleVariante(int $idVariante): ?int
    {
        $variante = DB::table('tbl_variantes_item')
            ->where('id_variante_item', $idVariante)
            ->first();

        if (!$variante) {
            return 0;
        }

        if ($variante->stock_total === null) {
            return null;
        }

        $comprometido = $this->cantidadComprometida(
            'detalle.id_variante_item',
            $idVariante
        );

        return max(0, (int) $variante->stock_total - $comprometido);
    }

    /** Stock: una orden vencida libera solo unidades SIN pago completo y no entregadas. */
    public function cantidadComprometida(string $columna, int $id, ?int $inscripcion = null): int
    {
        $permitidas=['detalle.id_item_actividad','detalle.id_variante_item'];
        if (!in_array($columna,$permitidas,true)) throw new \InvalidArgumentException('Columna inválida');
        $sql=<<<'SQL'
            CASE
              WHEN orden.estado IN ('pagada','pagada_prueba') THEN detalle.cantidad
              WHEN orden.estado='pendiente_pago'
                   AND (orden.expira_en IS NULL OR orden.expira_en > ?) THEN detalle.cantidad
              WHEN (SELECT COALESCE(SUM(a.monto),0) FROM tbl_aplicaciones_abono a
                    WHERE a.id_detalle_orden=detalle.id_detalle_orden)
                    >= detalle.subtotal - COALESCE(detalle.descuento,0)
                   THEN detalle.cantidad
              ELSE COALESCE(detalle.cantidad_entregada,0)
            END
        SQL;
        $q=DB::table('tbl_detalles_orden as detalle')
            ->join('tbl_ordenes as orden','orden.id_orden','=','detalle.id_orden')
            ->where($columna,$id);
        if($inscripcion!==null) $q->where('orden.id_inscripcion',$inscripcion)->where('orden.tipo_orden','productos');
        return (int)$q->selectRaw("COALESCE(SUM({$sql}),0) AS comprometido",[now('UTC')])
            ->first()->comprometido;
    }
}
