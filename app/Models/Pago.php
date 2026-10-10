<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends Model
{
    protected $table = 'tbl_pagos';
    protected $primaryKey = 'id_pago';
    protected $guarded = ['id_pago'];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_confirmacion' => 'datetime',
        ];
    }

    public function orden(): BelongsTo
    {
        return $this->belongsTo(
            Orden::class, 'id_orden', 'id_orden'
        );
    }

    public function intentos(): HasMany
    {
        return $this->hasMany(
            IntentoPagoWompi::class, 'id_pago', 'id_pago'
        );
    }
}
