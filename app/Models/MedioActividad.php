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
        'id_item_actividad',
        'id_sesion',
        'tipo',
        'url',
        'texto_alternativo',
        'es_portada',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'id_item_actividad' => 'integer',
            'id_sesion' => 'integer',
            'es_portada' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(
            Actividad::class,
            'id_actividad',
            'id_actividad'
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            ItemActividad::class,
            'id_item_actividad',
            'id_item_actividad'
        );
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(
            SesionActividad::class,
            'id_sesion',
            'id_sesion'
        );
    }
}