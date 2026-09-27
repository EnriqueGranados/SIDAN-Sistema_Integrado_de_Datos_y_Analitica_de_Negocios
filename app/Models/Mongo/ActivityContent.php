<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class ActivityContent extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'activity_content';
    public $timestamps = false;

    protected $fillable = [
        'id_actividad_pg',
        'blocks',
        'seo',
        'frontend',
    ];
}