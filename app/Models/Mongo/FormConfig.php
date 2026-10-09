<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class FormConfig extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'form_configs';
    public $timestamps = false;

    protected $fillable = [
        'id_formulario_pg',
        'sections',
        'field_configs',
    ];
}
