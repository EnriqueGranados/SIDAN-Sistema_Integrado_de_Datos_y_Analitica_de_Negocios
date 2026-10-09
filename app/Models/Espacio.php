<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Espacio extends Model
{
    use HasFactory;

    protected $table = 'tbl_espacios';
    protected $primaryKey = 'id_espacio';

    protected $fillable = [
        'id_espacio_contenedor',
        'nombre',
        'descripcion',
        'direccion',
        'indicaciones',
        'latitud',
        'longitud',
        'capacidad',
        'permite_actividades',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'id_espacio_contenedor' => 'integer',
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'capacidad' => 'integer',
            'permite_actividades' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(
            Espacio::class,
            'id_espacio_contenedor',
            'id_espacio'
        );
    }

    public function espaciosInternos(): HasMany
    {
        return $this->hasMany(
            Espacio::class,
            'id_espacio_contenedor',
            'id_espacio'
        );
    }

    public function recursos(): BelongsToMany
    {
        return $this->belongsToMany(
            Recurso::class,
            'tbl_espacios_recursos',
            'id_espacio',
            'id_recurso'
        )
            ->withPivot([
                'cantidad',
                'observacion',
            ])
            ->withTimestamps();
    }
}