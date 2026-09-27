<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Etiqueta extends Model
{
    use HasFactory;

    protected $table = 'tbl_etiquetas';
    protected $primaryKey = 'id_etiqueta';

    protected $fillable = [
        'nombre',
        'slug',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function actividades(): BelongsToMany
    {
        return $this->belongsToMany(
            Actividad::class,
            'tbl_actividades_etiquetas',
            'id_etiqueta',
            'id_actividad'
        );
    }
}
