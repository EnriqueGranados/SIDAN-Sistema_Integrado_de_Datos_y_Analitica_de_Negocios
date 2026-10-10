<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inscripcion extends Model
{
    protected $table = 'tbl_inscripciones';
    protected $primaryKey = 'id_inscripcion';
    protected $guarded = ['id_inscripcion'];

    protected function casts(): array
    {
        return [
            'fecha_confirmacion' => 'datetime',
            'expira_en' => 'datetime',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(
            Actividad::class, 'id_actividad', 'id_actividad'
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class, 'id_usuario', 'id_usuario'
        );
    }

    public function orden(): HasOne
    {
        return $this->hasOne(
            Orden::class, 'id_inscripcion', 'id_inscripcion'
        );
    }
}
