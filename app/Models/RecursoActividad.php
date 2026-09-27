<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecursoActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_recursos_actividad';
    protected $primaryKey = 'id_recurso';
    public $timestamps = false;

    protected $fillable = [
        'id_actividad',
        'id_sesion',
        'tipo',
        'nombre',
        'capacidad',
        'estado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'capacidad' => 'integer',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }

    public function sesion(): BelongsTo
    {
        return $this->belongsTo(SesionActividad::class, 'id_sesion', 'id_sesion');
    }
}
