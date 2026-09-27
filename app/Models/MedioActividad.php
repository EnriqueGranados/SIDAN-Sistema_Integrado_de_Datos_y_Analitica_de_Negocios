<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedioActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_medios_actividad';
    protected $primaryKey = 'id_medio';

    protected $fillable = [
        'id_actividad',
        'tipo',
        'url',
        'texto_alternativo',
        'es_portada',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'es_portada' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }
}
