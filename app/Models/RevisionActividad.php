<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RevisionActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_revisiones_actividad';
    protected $primaryKey = 'id_revision';
    public $timestamps = false;

    protected $fillable = [
        'id_actividad',
        'numero_revision',
        'id_usuario',
        'accion',
        'observacion',
        'creado_en',
    ];

    protected function casts(): array
    {
        return [
            'id_actividad' => 'integer',
            'numero_revision' => 'integer',
            'id_usuario' => 'integer',
            'creado_en' => 'datetime',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(
            Actividad::class,
            'id_actividad',
            'id_actividad'
        );
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id_usuario'
        );
    }

    public function observaciones(): HasMany
    {
        return $this->hasMany(
            ObservacionRevisionActividad::class,
            'id_revision',
            'id_revision'
        );
    }
}
