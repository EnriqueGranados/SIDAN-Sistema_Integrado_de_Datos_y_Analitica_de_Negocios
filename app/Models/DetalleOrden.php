<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleOrden extends Model
{
    protected $table = 'tbl_detalles_orden';
    protected $primaryKey = 'id_detalle_orden';
    protected $guarded = ['id_detalle_orden'];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(
            Orden::class, 'id_orden', 'id_orden'
        );
    }
}
