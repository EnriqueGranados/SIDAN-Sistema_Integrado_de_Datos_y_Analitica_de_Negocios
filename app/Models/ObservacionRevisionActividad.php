<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObservacionRevisionActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_observaciones_revision_actividad';
    protected $primaryKey = 'id_observacion';

    protected $fillable = [
        'id_revision',
        'seccion',
        'referencia_tipo',
        'referencia_id',
        'observacion',
        'resuelta',
        'resuelta_por',
        'id_revision_resuelta',
        'resuelta_en',
    ];

    protected function casts(): array
    {
        return [
            'id_revision' => 'integer',
            'referencia_id' => 'integer',
            'resuelta' => 'boolean',
            'resuelta_por' => 'integer',
            'id_revision_resuelta' => 'integer',
            'resuelta_en' => 'datetime',
        ];
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(
            RevisionActividad::class,
            'id_revision',
            'id_revision'
        );
    }

    public function usuarioResolucion(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'resuelta_por',
            'id_usuario'
        );
    }

    public function revisionResolucion(): BelongsTo
    {
        return $this->belongsTo(
            RevisionActividad::class,
            'id_revision_resuelta',
            'id_revision'
        );
    }
}
