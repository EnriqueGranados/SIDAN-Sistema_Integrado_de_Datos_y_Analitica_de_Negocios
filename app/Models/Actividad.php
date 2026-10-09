<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actividad extends Model
{
    use HasFactory, SoftDeletes;

    public const ESTADO_BORRADOR = 'borrador';
    public const ESTADO_PENDIENTE_REVISION = 'pendiente_revision';
    public const ESTADO_CAMBIOS_SOLICITADOS = 'cambios_solicitados';
    public const ESTADO_APROBADA = 'aprobada';
    public const ESTADO_RECHAZADA = 'rechazada';
    public const ESTADO_PUBLICADA = 'publicada';

    public const PARTICIPACION_INFORMATIVA = 'informativa';
    public const PARTICIPACION_REGISTRO_GRATUITO = 'registro_gratuito';
    public const PARTICIPACION_REGISTRO_PAGO = 'registro_pago';
    public const PARTICIPACION_VENTA_DIRECTA = 'venta_directa';

    protected $table = 'tbl_actividades';
    protected $primaryKey = 'id_actividad';

    const DELETED_AT = 'eliminado_en';

    protected $fillable = [
        'id_categoria',
        'id_espacio',
        'ubicacion_externa',
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
        'tipo_participacion',
        'habilita_inscripcion',
        'requiere_cuenta',
        'permite_lista_espera',
        'cupo_total',
        'precio_inscripcion',
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
            'precio_inscripcion' => 'decimal:2',
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

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(
            Espacio::class,
            'id_espacio',
            'id_espacio'
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

    public function observacionesRevision(): HasManyThrough
    {
        return $this->hasManyThrough(
            ObservacionRevisionActividad::class,
            RevisionActividad::class,
            'id_actividad',
            'id_revision',
            'id_actividad',
            'id_revision'
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


    public function requiereInscripcionGeneral(): bool
    {
        return in_array(
            $this->tipo_participacion,
            [
                self::PARTICIPACION_REGISTRO_GRATUITO,
                self::PARTICIPACION_REGISTRO_PAGO,
            ],
            true
        );
    }

    public function participacionConPago(): bool
    {
        return $this->tipo_participacion === self::PARTICIPACION_REGISTRO_PAGO;
    }

    public function esVentaDirecta(): bool
    {
        return $this->tipo_participacion === self::PARTICIPACION_VENTA_DIRECTA;
    }

    public function getPrioridadTextoAttribute(): string
    {
        return match ((int) $this->prioridad) {
            100 => 'Alta',
            75 => 'Media',
            50 => 'Regular',
            25 => 'Baja',
            default => 'Sin definir',
        };
    }

    public function scopeVisiblesPara(Builder $query, User $usuario): Builder
    {
        $rol = $usuario->rol?->nombre;

        if (in_array($rol, ['superadmin', 'admin'], true)) {
            return $query;
        }

        return $query->where(
            $this->qualifyColumn('creado_por'),
            $usuario->getAuthIdentifier()
        );
    }

    public function scopePublicadas(Builder $query): Builder
    {
        return $query->where(
            $this->qualifyColumn('estado_publicacion'),
            self::ESTADO_PUBLICADA
        );
    }

    public function scopeVisiblesEnPortal(Builder $query): Builder
    {
        $ahora = now();

        return $query
            ->publicadas()
            ->where($this->qualifyColumn('visibilidad'), 'publica')
            ->where(function (Builder $subquery) use ($ahora) {
                $subquery
                    ->whereNull($this->qualifyColumn('visible_desde'))
                    ->orWhere(
                        $this->qualifyColumn('visible_desde'),
                        '<=',
                        $ahora
                    );
            })
            ->where(function (Builder $subquery) use ($ahora) {
                $subquery
                    ->whereNull($this->qualifyColumn('visible_hasta'))
                    ->orWhere(
                        $this->qualifyColumn('visible_hasta'),
                        '>=',
                        $ahora
                    );
            });
    }

    public function scopeOrdenPortal(Builder $query): Builder
    {
        return $query
            ->orderByDesc($this->qualifyColumn('destacada'))
            ->orderByDesc($this->qualifyColumn('prioridad'))
            ->orderByRaw(
                $this->qualifyColumn('realizacion_desde') . ' IS NULL'
            )
            ->orderBy($this->qualifyColumn('realizacion_desde'))
            ->orderByDesc($this->qualifyColumn('id_actividad'));
    }

    public function perteneceA(User $usuario): bool
    {
        return (int) $this->creado_por
            === (int) $usuario->getAuthIdentifier();
    }

    public function puedePublicarse(): bool
    {
        return $this->estado_publicacion === self::ESTADO_APROBADA;
    }

    public function puedeRetirarseDePublicacion(): bool
    {
        return $this->estado_publicacion === self::ESTADO_PUBLICADA;
    }

    public function estaPublicada(): bool
    {
        return $this->estado_publicacion === self::ESTADO_PUBLICADA;
    }

    public function estaVisibleEnPortal(): bool
    {
        if (
            !$this->estaPublicada()
            || $this->visibilidad !== 'publica'
        ) {
            return false;
        }

        $ahora = now();

        if (
            $this->visible_desde
            && $this->visible_desde->gt($ahora)
        ) {
            return false;
        }

        if (
            $this->visible_hasta
            && $this->visible_hasta->lt($ahora)
        ) {
            return false;
        }

        return true;
    }
}