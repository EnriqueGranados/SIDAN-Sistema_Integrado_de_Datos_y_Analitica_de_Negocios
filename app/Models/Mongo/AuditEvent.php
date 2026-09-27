<?php

namespace App\Models\Mongo;

use MongoDB\Laravel\Eloquent\Model;

class AuditEvent extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'audit_events';
    public $timestamps = false;

    protected $fillable = [
        'id_usuario_pg',
        'entity',
        'record_id',
        'action',
        'before',
        'after',
        'metadata',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'timestamp' => 'datetime',
        ];
    }
}