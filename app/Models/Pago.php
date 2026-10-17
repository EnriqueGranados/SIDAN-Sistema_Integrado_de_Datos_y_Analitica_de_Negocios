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

    // En PostgreSQL timestampTz exige conservar el desplazamiento horario.
    // Sin él, una hora local -06:00 podría interpretarse erróneamente como UTC.
    protected $dateFormat = 'Y-m-d H:i:sP';

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

    public function abonos(): HasMany
    {
        return $this->hasMany(AbonoTransferencia::class, 'id_pago', 'id_pago')
            ->orderBy('id_abono');
    }

    public function montoAcreditadoCentavos(): int
    {
        // No calcular importes monetarios con float.
        $suma = $this->abonos()->where('estado', 'confirmado')
            ->get(['monto_recibido'])
            ->sum(fn ($abono) => (int) round((float) $abono->monto_recibido * 100));

        return (int) $suma;
    }

    public function intentos(): HasMany
    {
        return $this->hasMany(
            IntentoPagoWompi::class, 'id_pago', 'id_pago'
        );
    }
}