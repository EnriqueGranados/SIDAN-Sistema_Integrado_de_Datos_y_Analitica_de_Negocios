<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecursoActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_recursos_actividad';
    protected $primaryKey = 'id_recurso_actividad';

    protected $fillable = [
        'id_actividad',
        'id_sesion',
        'id_recurso',
        'cantidad',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'id_actividad' => 'integer',
            'id_sesion' => 'integer',
            'id_recurso' => 'integer',
            'cantidad' => 'integer',
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

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(
            SesionActividad::class,
            'id_sesion',
            'id_sesion'
        );
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(
            Recurso::class,
            'id_recurso',
            'id_recurso'
        );
    }
}