<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntentoPagoWompi extends Model
{
    protected $table = 'tbl_intentos_pago_wompi';
    protected $primaryKey = 'id_intento';
    protected $guarded = ['id_intento'];

    protected function casts(): array
    {
        return [
            'monto_solicitado' => 'decimal:2',
            'es_productiva' => 'boolean',
            'expira_en' => 'datetime',
        ];
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(
            Pago::class, 'id_pago', 'id_pago'
        );
    }
}
