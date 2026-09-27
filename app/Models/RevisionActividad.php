<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_revisiones_actividad';
    protected $primaryKey = 'id_revision';
    public $timestamps = false;

    protected $fillable = [
        'id_actividad',
        'numero_revision',
        'accion',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'numero_revision' => 'integer',
            'creado_en' => 'datetime',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}