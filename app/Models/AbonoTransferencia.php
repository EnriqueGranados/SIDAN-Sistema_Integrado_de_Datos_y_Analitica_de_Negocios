<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbonoTransferencia extends Model
{
    protected $table = 'tbl_abonos_transferencia';
    protected $primaryKey = 'id_abono';
    protected $guarded = ['id_abono'];

    // En PostgreSQL timestampTz exige conservar el desplazamiento horario.
    // Sin él, una hora local -06:00 podría interpretarse erróneamente como UTC.
    protected $dateFormat = 'Y-m-d H:i:sP';

    protected function casts(): array
    {
        return [
            'monto_recibido' => 'decimal:2',
            'revisado_en' => 'datetime',
        ];
    }

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class, 'id_pago', 'id_pago');
    }
}
