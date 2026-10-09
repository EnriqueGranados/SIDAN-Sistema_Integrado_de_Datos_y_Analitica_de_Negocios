<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesionActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_sesiones_actividad';
    protected $primaryKey = 'id_sesion';
    public $timestamps = false;

    protected $fillable = [
        'id_actividad',
        'id_espacio',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'ubicacion',
        'enlace_acceso',
        'cupo',
        'requiere_reserva',
        'obligatoria',
        'orden',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'id_espacio' => 'integer',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'cupo' => 'integer',
            'requiere_reserva' => 'boolean',
            'obligatoria' => 'boolean',
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

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(
            Espacio::class,
            'id_espacio',
            'id_espacio'
        );
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(
            RecursoActividad::class,
            'id_sesion',
            'id_sesion'
        );
    }
}