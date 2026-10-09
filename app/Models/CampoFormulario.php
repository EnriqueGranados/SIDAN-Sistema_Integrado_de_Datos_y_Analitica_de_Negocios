<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampoFormulario extends Model
{
    use HasFactory;

    protected $table = 'tbl_campos_formulario';
    protected $primaryKey = 'id_campo';
    public $timestamps = false;

    protected $fillable = [
        'id_formulario',
        'clave',
        'nombre',
        'tipo_dato',
        'obligatorio',
        'aplica_a',
        'orden',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'obligatorio' => 'boolean',
            'orden' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function formulario(): BelongsTo
    {
        return $this->belongsTo(FormularioActividad::class, 'id_formulario', 'id_formulario');
    }
}
