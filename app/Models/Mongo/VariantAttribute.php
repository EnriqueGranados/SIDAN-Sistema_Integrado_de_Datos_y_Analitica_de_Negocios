<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class VariantAttribute extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'variant_attributes';
    public $timestamps = false;

    protected $fillable = [
        'id_variante_pg',
        'attributes',
    ];
}