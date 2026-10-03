<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Recurso extends Model
{
    use HasFactory;

    protected $table = 'tbl_recursos';
    protected $primaryKey = 'id_recurso';

    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria',
        'unidad_medida',
        'es_movil',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'es_movil' => 'boolean',
            'activo' => 'boolean',
        ];
    }

    public function espacios(): BelongsToMany
    {
        return $this->belongsToMany(
            Espacio::class,
            'tbl_espacios_recursos',
            'id_recurso',
            'id_espacio'
        )
            ->withPivot([
                'cantidad',
                'observacion',
            ])
            ->withTimestamps();
    }
}