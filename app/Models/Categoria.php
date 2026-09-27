<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'tbl_categorias';
    protected $primaryKey = 'id_categoria';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class, 'id_categoria', 'id_categoria');
    }
}