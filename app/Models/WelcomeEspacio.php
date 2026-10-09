<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelcomeEspacio extends Model
{
    protected $table = 'tbl_welcome_espacios';
    protected $primaryKey = 'id_espacio_welcome';

    protected $fillable = [
        'seccion',
        'nombre',
        'orden',
        'id_actividad',
    ];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }
}