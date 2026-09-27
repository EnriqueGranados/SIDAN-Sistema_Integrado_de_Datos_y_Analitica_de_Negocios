<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_items_actividad';
    protected $primaryKey = 'id_item_actividad';
    public $timestamps = false;

    protected $fillable = [
        'id_actividad',
        'nombre',
        'descripcion',
        'tipo',
        'precio',
        'costo_referencia',
        'stock_total',
        'venta_desde',
        'venta_hasta',
        'min_por_inscripcion',
        'max_por_inscripcion',
        'requiere_participante',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'costo_referencia' => 'decimal:2',
            'stock_total' => 'integer',
            'venta_desde' => 'datetime',
            'venta_hasta' => 'datetime',
            'min_por_inscripcion' => 'integer',
            'max_por_inscripcion' => 'integer',
            'requiere_participante' => 'boolean',
            'orden' => 'integer',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }

    public function variantes(): HasMany
    {
        return $this->hasMany(VarianteItem::class, 'id_item_actividad', 'id_item_actividad')
            ->orderBy('orden');
    }

    public function promociones(): BelongsToMany
    {
        return $this->belongsToMany(
            Promocion::class,
            'tbl_promociones_items',
            'id_item_actividad',
            'id_promocion'
        );
    }
}
