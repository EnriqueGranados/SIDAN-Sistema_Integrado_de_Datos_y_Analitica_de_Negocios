<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Orden extends Model
{
    protected $table = 'tbl_ordenes';
    protected $primaryKey = 'id_orden';
    protected $guarded = ['id_orden'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'descuento_total' => 'decimal:2',
            'total' => 'decimal:2',
            'expira_en' => 'datetime',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(
            Actividad::class, 'id_actividad', 'id_actividad'
        );
    }

    public function inscripcion(): BelongsTo
    {
        return $this->belongsTo(
            Inscripcion::class, 'id_inscripcion', 'id_inscripcion'
        );
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(
            DetalleOrden::class, 'id_orden', 'id_orden'
        );
    }

    public function pago(): HasOne
    {
        return $this->hasOne(
            Pago::class, 'id_orden', 'id_orden'
        );
    }
}
