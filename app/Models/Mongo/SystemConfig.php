<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class SystemConfig extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'system_config';
    public $timestamps = false;

    protected $fillable = [
        'singleton_key',
        'branding',
        'frontend',
        'modules',
    ];
}