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
        'product_configuration',
        'purchase_fields',
        'pricing_configuration',
        'session_configuration',
        'configuration',
    ];

    protected function casts(): array
    {
        return [
            'id_actividad_pg' => 'integer',
            'blocks' => 'array',
            'seo' => 'array',
            'frontend' => 'array',
            'product_configuration' => 'array',
            'purchase_fields' => 'array',
            'pricing_configuration' => 'array',
            'session_configuration' => 'array',
            'configuration' => 'array',
        ];
    }
}