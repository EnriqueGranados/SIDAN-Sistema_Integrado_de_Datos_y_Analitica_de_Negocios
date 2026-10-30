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

    // En PostgreSQL timestampTz exige conservar el desplazamiento horario.
    // Sin él, una hora local -06:00 podría interpretarse erróneamente como UTC.
    protected $dateFormat = 'Y-m-d H:i:sP';

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'descuento_total' => 'decimal:2',
            'total' => 'decimal:2',
            'expira_en' => 'datetime',
            'limite_saldo_en' => 'datetime',
            'minimo_inicial' => 'decimal:2',
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
