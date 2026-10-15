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
                        ->where('expira_en', '>', now('UTC'));
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

    private function cantidadComprometida(
        string $columna,
        int $id
    ): int {
        $ahoraUtc = now('UTC');

        return (int) DB::table('tbl_detalles_orden as detalle')
            ->join(
                'tbl_ordenes as orden',
                'orden.id_orden',
                '=',
                'detalle.id_orden'
            )
            ->where($columna, $id)
            ->where(function ($query) use ($ahoraUtc) {
                $query->whereIn('orden.estado', [
                    'pagada',
                    'pagada_prueba',
                ])->orWhere(function ($query) use ($ahoraUtc) {
                    $query->where('orden.estado', 'pendiente_pago')
                        ->where(function ($query) use ($ahoraUtc) {
                            $query->whereNull('orden.expira_en')
                                ->orWhere('orden.expira_en', '>', $ahoraUtc);
                        });
                });
            })
            ->sum('detalle.cantidad');
    }
}