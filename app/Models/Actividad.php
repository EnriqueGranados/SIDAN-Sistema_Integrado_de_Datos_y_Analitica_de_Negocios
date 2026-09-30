<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actividad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tbl_actividades';
    protected $primaryKey = 'id_actividad';

    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'id_categoria',
        'nombre',
        'slug',
        'resumen',
        'descripcion',
        'estado_publicacion',
        'estado_operativo',
        'visibilidad',
        'revision_actual',
        'destacada',
        'prioridad',
        'habilita_inscripcion',
        'requiere_cuenta',
        'permite_lista_espera',
        'cupo_total',
        'inscripcion_desde',
        'inscripcion_hasta',
        'visible_desde',
        'visible_hasta',
        'realizacion_desde',
        'realizacion_hasta',
    ];

    protected function casts(): array
    {
        return [
            'revision_actual' => 'integer',
            'destacada' => 'boolean',
            'prioridad' => 'integer',
            'habilita_inscripcion' => 'boolean',
            'requiere_cuenta' => 'boolean',
            'permite_lista_espera' => 'boolean',
            'cupo_total' => 'integer',
            'inscripcion_desde' => 'datetime',
            'inscripcion_hasta' => 'datetime',
            'visible_desde' => 'datetime',
            'visible_hasta' => 'datetime',
            'realizacion_desde' => 'datetime',
            'realizacion_hasta' => 'datetime',
            'eliminado_en' => 'datetime',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(
            Categoria::class,
            'id_categoria',
            'id_categoria'
        );
    }

    public function etiquetas(): BelongsToMany
    {
        return $this->belongsToMany(
            Etiqueta::class,
            'tbl_actividades_etiquetas',
            'id_actividad',
            'id_etiqueta'
        );
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'creado_por',
            'id_usuario'
        );
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'actualizado_por',
            'id_usuario'
        );
    }

    public function responsables(): HasMany
    {
        return $this->hasMany(
            ResponsableActividad::class,
            'id_actividad',
            'id_actividad'
        );
    }

    public function medios(): HasMany
    {
        return $this->hasMany(
            MedioActividad::class,
            'id_actividad',
            'id_actividad'
        )
            ->whereNull('id_item_actividad')
            ->whereNull('id_sesion');
    }

    public function portada(): HasOne
    {
        return $this->hasOne(
            MedioActividad::class,
            'id_actividad',
            'id_actividad'
        )
            ->whereNull('id_item_actividad')
            ->whereNull('id_sesion')
            ->where('es_portada', true);
    }

    public function formularios(): HasMany
    {
        return $this->hasMany(
            FormularioActividad::class,
            'id_actividad',
            'id_actividad'
        );
    }

    public function sesiones(): HasMany
    {
        return $this->hasMany(
            SesionActividad::class,
            'id_actividad',
            'id_actividad'
        );
    }

    public function revisiones(): HasMany
    {
        return $this->hasMany(
            RevisionActividad::class,
            'id_actividad',
            'id_actividad'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            ItemActividad::class,
            'id_actividad',
            'id_actividad'
        );
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(
            RecursoActividad::class,
            'id_actividad',
            'id_actividad'
        );
    }

    public function promociones(): HasMany
    {
        return $this->hasMany(
            Promocion::class,
            'id_actividad',
            'id_actividad'
        );
    }
}