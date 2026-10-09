<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VarianteItem extends Model
{
    use HasFactory;

    protected $table = 'tbl_variantes_item';
    protected $primaryKey = 'id_variante_item';
    public $timestamps = false;

    protected $fillable = [
        'id_item_actividad',
        'sku',
        'nombre_variante',
        'precio',
        'costo_referencia',
        'stock_total',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'costo_referencia' => 'decimal:2',
            'stock_total' => 'integer',
            'orden' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemActividad::class, 'id_item_actividad', 'id_item_actividad');
    }
}
