<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormularioActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_formularios_actividad';
    protected $primaryKey = 'id_formulario';
    public $timestamps = false;

    protected $fillable = [
        'id_actividad',
        'version',
        'nombre',
        'estado',
        'publicado_en',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'creado_en' => 'datetime',
            'publicado_en' => 'datetime',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por', 'id_usuario');
    }

    public function campos(): HasMany
    {
        return $this->hasMany(CampoFormulario::class, 'id_formulario', 'id_formulario')
            ->orderBy('orden');
    }
}
