<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'tbl_promociones';
    protected $primaryKey = 'id_promocion';
    public $timestamps = false;

    protected $fillable = [
        'id_actividad',
        'codigo',
        'nombre',
        'tipo_descuento',
        'valor',
        'vigente_desde',
        'vigente_hasta',
        'limite_usos',
        'limite_por_persona',
        'monto_minimo',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'vigente_desde' => 'datetime',
            'vigente_hasta' => 'datetime',
            'limite_usos' => 'integer',
            'limite_por_persona' => 'integer',
            'monto_minimo' => 'decimal:2',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por', 'id_usuario');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            ItemActividad::class,
            'tbl_promociones_items',
            'id_promocion',
            'id_item_actividad'
        );
    }
}
