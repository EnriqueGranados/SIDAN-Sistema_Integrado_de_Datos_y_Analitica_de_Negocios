<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Espacio extends Model
{
    use HasFactory;

    public const VALIDACION_PENDIENTE = 'pendiente';
    public const VALIDACION_VALIDADO = 'validado';

    public const ORIGEN_ADMINISTRACION = 'administracion';
    public const ORIGEN_ACTIVIDAD = 'actividad';
    public const ORIGEN_SESION = 'sesion';

    protected $table = 'tbl_espacios';

    protected $primaryKey = 'id_espacio';

    protected $fillable = [
        'id_espacio_contenedor',
        'nombre',
        'descripcion',
        'direccion',
        'indicaciones',
        'latitud',
        'longitud',
        'capacidad',
        'permite_actividades',
        'activo',

        // Control de espacios creados desde actividades/sesiones
        'estado_validacion',
        'origen_registro',
        'creado_por',
        'validado_por',
        'validado_en',
    ];

    protected function casts(): array
    {
        return [
            'id_espacio_contenedor' => 'integer',
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
            'capacidad' => 'integer',
            'permite_actividades' => 'boolean',
            'activo' => 'boolean',

            'creado_por' => 'integer',
            'validado_por' => 'integer',
            'validado_en' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Jerarquía de espacios
    |--------------------------------------------------------------------------
    |
    | Ejemplo:
    |
    | Universidad
    |   └ Facultad
    |       └ Edificio
    |           └ Aula
    |
    */

    public function contenedor(): BelongsTo
    {
        return $this->belongsTo(
            Espacio::class,
            'id_espacio_contenedor',
            'id_espacio'
        );
    }

    public function espaciosInternos(): HasMany
    {
        return $this->hasMany(
            Espacio::class,
            'id_espacio_contenedor',
            'id_espacio'
        )
            ->orderBy('nombre');
    }

    /*
    |--------------------------------------------------------------------------
    | Recursos
    |--------------------------------------------------------------------------
    */

    public function recursos(): BelongsToMany
    {
        return $this->belongsToMany(
            Recurso::class,
            'tbl_espacios_recursos',
            'id_espacio',
            'id_recurso'
        )
            ->withPivot([
                'cantidad',
                'observacion',
            ])
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Auditoría
    |--------------------------------------------------------------------------
    */

    public function creador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'creado_por',
            'id_usuario'
        );
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'validado_por',
            'id_usuario'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopePermitidosParaActividades(Builder $query): Builder
    {
        return $query
            ->where('activo', true)
            ->where('permite_actividades', true);
    }

    public function scopePendientesValidacion(Builder $query): Builder
    {
        return $query->where(
            'estado_validacion',
            self::VALIDACION_PENDIENTE
        );
    }

    public function scopeValidados(Builder $query): Builder
    {
        return $query->where(
            'estado_validacion',
            self::VALIDACION_VALIDADO
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Estado
    |--------------------------------------------------------------------------
    */

    public function estaPendienteValidacion(): bool
    {
        return $this->estado_validacion === self::VALIDACION_PENDIENTE;
    }

    public function estaValidado(): bool
    {
        return $this->estado_validacion === self::VALIDACION_VALIDADO;
    }

    /*
    |--------------------------------------------------------------------------
    | Ruta jerárquica
    |--------------------------------------------------------------------------
    |
    | Devuelve, por ejemplo:
    |
    | Universidad de El Salvador
    | › Facultad Multidisciplinaria Oriental
    | › Edificio Minerva
    | › Aula 31
    |
    */

    public function rutaJerarquica(): string
    {
        $nombres = [
            (string) $this->nombre,
        ];

        $actual = $this;

        $visitados = [
            (int) $this->id_espacio,
        ];

        while ($actual->id_espacio_contenedor) {
            $contenedor = $actual->relationLoaded('contenedor')
                ? $actual->contenedor
                : $actual->contenedor()->first();

            if (!$contenedor) {
                break;
            }

            $idContenedor = (int) $contenedor->id_espacio;

            /*
            |--------------------------------------------------------------------------
            | Protección contra ciclos accidentales
            |--------------------------------------------------------------------------
            */

            if (in_array($idContenedor, $visitados, true)) {
                break;
            }

            $visitados[] = $idContenedor;

            array_unshift(
                $nombres,
                (string) $contenedor->nombre
            );

            $actual = $contenedor;
        }

        return implode(' › ', $nombres);
    }

    /*
    |--------------------------------------------------------------------------
    | Ruta del contenedor
    |--------------------------------------------------------------------------
    |
    | Sirve para el autocompletado:
    |
    | Resultado:
    | Aula 31
    |
    | Contexto:
    | Universidad › Facultad › Edificio Minerva
    |
    */

    public function rutaContenedor(): ?string
    {
        if (!$this->id_espacio_contenedor) {
            return null;
        }

        $ruta = $this->rutaJerarquica();

        $sufijo = ' › ' . $this->nombre;

        if (str_ends_with($ruta, $sufijo)) {
            return substr(
                $ruta,
                0,
                -strlen($sufijo)
            );
        }

        return $this->contenedor?->rutaJerarquica();
    }

    /*
    |--------------------------------------------------------------------------
    | Descendientes
    |--------------------------------------------------------------------------
    |
    | Obtiene hijos, nietos, bisnietos, etc.
    |
    | Esto nos servirá después para que las sesiones puedan buscar únicamente
    | espacios contenidos dentro del lugar general seleccionado para la
    | actividad.
    |
    */

    public function obtenerDescendientes(
        bool $soloActivos = true
    ): EloquentCollection {
        $resultado = new EloquentCollection();

        $query = $this->espaciosInternos();

        if ($soloActivos) {
            $query->where('activo', true);
        }

        $hijos = $query
            ->orderBy('nombre')
            ->get();

        foreach ($hijos as $hijo) {
            $resultado->push($hijo);

            $resultado = $resultado->merge(
                $hijo->obtenerDescendientes($soloActivos)
            );
        }

        return $resultado;
    }

    /*
    |--------------------------------------------------------------------------
    | IDs de descendientes
    |--------------------------------------------------------------------------
    */

    public function idsDescendientes(
        bool $incluirActual = false,
        bool $soloActivos = true
    ): array {
        $ids = $this
            ->obtenerDescendientes($soloActivos)
            ->pluck('id_espacio')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($incluirActual) {
            array_unshift(
                $ids,
                (int) $this->id_espacio
            );
        }

        return array_values(
            array_unique($ids)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Datos para el buscador/autocompletado
    |--------------------------------------------------------------------------
    */

    public function paraSelector(): array
    {
        return [
            'id' => (int) $this->id_espacio,
            'nombre' => $this->nombre,
            'contenedor' => $this->id_espacio_contenedor
                ? (int) $this->id_espacio_contenedor
                : null,
            'permite_actividades' => (bool) $this->permite_actividades,
            'ruta' => $this->rutaJerarquica(),
            'contexto' => $this->rutaContenedor(),
            'direccion' => $this->direccion,
            'capacidad' => $this->capacidad,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'pendiente_validacion' => $this->estaPendienteValidacion(),
        ];
    }
}