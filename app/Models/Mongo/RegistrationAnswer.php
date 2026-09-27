<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class RegistrationAnswer extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'registration_answers';

    protected $fillable = [
        'id_inscripcion_pg',
        'id_formulario_pg',
        'scope',
        'id_detalle_inscripcion_pg',
        'id_participante_pg',
        'answers',
    ];
}