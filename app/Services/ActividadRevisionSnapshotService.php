<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Mongo\ActivityContent;
use App\Models\Mongo\VariantAttribute;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ActividadRevisionSnapshotService
{
    public function capturarSiAprobada(Actividad $actividad): void
    {
        if ($actividad->estado_publicacion !== 'aprobada') {
            return;
        }

        $documento = $this->obtenerDocumento($actividad);
        $existente = $this->normalizar($documento->approved_revision_snapshot ?? []);

        if (!empty($existente['data'] ?? null)) {
            return;
        }

        $documento->approved_revision_snapshot = [
            'revision' => (int) $actividad->revision_actual,
            'captured_at' => now()->toIso8601String(),
            'data' => $this->crearSnapshot($actividad),
        ];
        $documento->save();
    }

    public function limpiarTrasAprobacion(Actividad $actividad): void
    {
        $documento = ActivityContent::query()
            ->where('id_actividad_pg', (int) $actividad->id_actividad)
            ->first();

        if (!$documento) {
            return;
        }

        $documento->approved_revision_snapshot = null;
        $documento->save();
    }

    public function obtenerCambiosDesdeAprobacion(Actividad $actividad): array
    {
        $documento = ActivityContent::query()
            ->where('id_actividad_pg', (int) $actividad->id_actividad)
            ->first();

        $baseline = $this->normalizar($documento?->approved_revision_snapshot ?? []);
        $anterior = $baseline['data'] ?? null;

        if (!is_array($anterior) || empty($anterior)) {
            return [
                'has_baseline' => false,
                'has_changes' => false,
                'baseline_revision' => null,
                'sections' => $this->seccionesVacias(),
            ];
        }

        $actual = $this->crearSnapshot($actividad);

        $secciones = [
            'informacion_general' => $this->compararInformacionGeneral(
                $anterior['informacion_general'] ?? [],
                $actual['informacion_general'] ?? []
            ),
            'presentacion' => $this->compararPresentacion(
                $anterior['presentacion'] ?? [],
                $actual['presentacion'] ?? []
            ),
            'productos' => $this->compararEntidades(
                $anterior['productos'] ?? [],
                $actual['productos'] ?? [],
                'items',
                'Productos / servicios'
            ),
            'datos_solicitados' => $this->compararDatosSolicitados(
                $anterior['datos_solicitados'] ?? [],
                $actual['datos_solicitados'] ?? []
            ),
            'precios_costos' => $this->compararPrecios(
                $anterior['precios_costos'] ?? [],
                $actual['precios_costos'] ?? []
            ),
            'programacion' => $this->compararProgramacion(
                $anterior['programacion'] ?? [],
                $actual['programacion'] ?? []
            ),
            'inscripcion' => $this->compararInscripcion(
                $anterior['inscripcion'] ?? [],
                $actual['inscripcion'] ?? []
            ),
        ];

        return [
            'has_baseline' => true,
            'has_changes' => collect($secciones)->contains(
                fn (array $seccion) => (bool) ($seccion['changed'] ?? false)
            ),
            'baseline_revision' => isset($baseline['revision'])
                ? (int) $baseline['revision']
                : null,
            'sections' => $secciones,
        ];
    }

    public function crearSnapshot(Actividad $actividad): array
    {
        $actividad->loadMissing([
            'etiquetas',
            'medios',
            'items.variantes',
            'items.medios',
            'formularios.campos',
            'sesiones.recursos',
            'recursos',
            'promociones.items',
        ]);

        $documento = ActivityContent::query()
            ->where('id_actividad_pg', (int) $actividad->id_actividad)
            ->first();

        $idsVariantes = $actividad->items
            ->flatMap(fn ($item) => $item->variantes->pluck('id_variante_item'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $atributosVariantes = collect();

        if (!empty($idsVariantes)) {
            try {
                $atributosVariantes = VariantAttribute::query()
                    ->whereIn('id_variante_pg', $idsVariantes)
                    ->get()
                    ->keyBy(fn ($doc) => (int) $doc->id_variante_pg);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $itemsEstructura = $actividad->items
            ->sortBy('id_item_actividad')
            ->mapWithKeys(function ($item) use ($atributosVariantes) {
                $variantes = $item->variantes
                    ->sortBy('id_variante_item')
                    ->mapWithKeys(function ($variante) use ($atributosVariantes) {
                        $atributos = $this->limpiarConfig(
                            $atributosVariantes->get((int) $variante->id_variante_item)?->attributes ?? []
                        );

                        return [
                            (string) $variante->id_variante_item => [
                                'id' => (int) $variante->id_variante_item,
                                'sku' => $variante->sku,
                                'nombre' => $variante->nombre_variante,
                                'stock_total' => $variante->stock_total,
                                'orden' => $variante->orden,
                                'activo' => (bool) $variante->activo,
                                'atributos' => $atributos,
                            ],
                        ];
                    })
                    ->all();

                $medios = $item->medios
                    ->sortBy(fn ($medio) => sprintf('%08d-%08d', (int) $medio->orden, (int) $medio->id_medio))
                    ->map(fn ($medio) => [
                        'id' => (int) $medio->id_medio,
                        'tipo' => $medio->tipo,
                        'url' => $medio->url,
                        'texto_alternativo' => $medio->texto_alternativo,
                        'es_portada' => (bool) $medio->es_portada,
                        'orden' => (int) $medio->orden,
                    ])
                    ->values()
                    ->all();

                return [
                    (string) $item->id_item_actividad => [
                        'id' => (int) $item->id_item_actividad,
                        'nombre' => $item->nombre,
                        'descripcion' => $item->descripcion,
                        'tipo' => $item->tipo,
                        'stock_total' => $item->stock_total,
                        'venta_desde' => $this->fecha($item->venta_desde),
                        'venta_hasta' => $this->fecha($item->venta_hasta),
                        'min_por_inscripcion' => $item->min_por_inscripcion,
                        'max_por_inscripcion' => $item->max_por_inscripcion,
                        'requiere_participante' => (bool) $item->requiere_participante,
                        'orden' => $item->orden,
                        'activo' => (bool) $item->activo,
                        'medios' => $medios,
                        'variantes' => $variantes,
                    ],
                ];
            })
            ->all();

        $preciosItems = $actividad->items
            ->sortBy('id_item_actividad')
            ->mapWithKeys(function ($item) {
                return [
                    (string) $item->id_item_actividad => [
                        'id' => (int) $item->id_item_actividad,
                        'nombre' => $item->nombre,
                        'precio' => $this->decimal($item->precio),
                        'costo_referencia' => $this->decimal($item->costo_referencia),
                        'variantes' => $item->variantes
                            ->sortBy('id_variante_item')
                            ->mapWithKeys(fn ($variante) => [
                                (string) $variante->id_variante_item => [
                                    'id' => (int) $variante->id_variante_item,
                                    'nombre' => $variante->nombre_variante,
                                    'precio' => $this->decimal($variante->precio),
                                    'costo_referencia' => $this->decimal($variante->costo_referencia),
                                ],
                            ])
                            ->all(),
                    ],
                ];
            })
            ->all();

        $formularios = $actividad->formularios
            ->sortBy('id_formulario')
            ->mapWithKeys(fn ($formulario) => [
                (string) $formulario->id_formulario => [
                    'id' => (int) $formulario->id_formulario,
                    'nombre' => $formulario->nombre,
                    'version' => $formulario->version,
                    'estado' => $formulario->estado,
                    'campos' => $formulario->campos
                        ->sortBy('id_campo')
                        ->mapWithKeys(fn ($campo) => [
                            (string) $campo->id_campo => [
                                'id' => (int) $campo->id_campo,
                                'clave' => $campo->clave,
                                'nombre' => $campo->nombre,
                                'tipo_dato' => $campo->tipo_dato,
                                'obligatorio' => (bool) $campo->obligatorio,
                                'aplica_a' => $campo->aplica_a,
                                'orden' => $campo->orden,
                                'activo' => (bool) $campo->activo,
                            ],
                        ])
                        ->all(),
                ],
            ])
            ->all();

        $sesiones = $actividad->sesiones
            ->sortBy('id_sesion')
            ->mapWithKeys(function ($sesion) {
                return [
                    (string) $sesion->id_sesion => [
                        'id' => (int) $sesion->id_sesion,
                        'nombre' => $sesion->nombre,
                        'fecha_inicio' => $this->fecha($sesion->fecha_inicio),
                        'fecha_fin' => $this->fecha($sesion->fecha_fin),
                        'id_espacio' => $sesion->getAttribute('id_espacio'),
                        'ubicacion' => $sesion->getAttribute('ubicacion'),
                        'enlace_acceso' => $sesion->getAttribute('enlace_acceso')
                            ?? $sesion->getAttribute('enlace'),
                        'cupo' => $sesion->cupo,
                        'requiere_reserva' => (bool) $sesion->requiere_reserva,
                        'obligatoria' => (bool) $sesion->obligatoria,
                        'orden' => $sesion->orden,
                        'estado' => $sesion->estado,
                        'recursos' => $sesion->recursos
                            ->sortBy(fn ($recurso) => (int) ($recurso->getAttribute('id_recurso_actividad') ?? $recurso->getAttribute('id_recurso') ?? 0))
                            ->map(fn ($recurso) => $this->atributosModelo($recurso))
                            ->values()
                            ->all(),
                    ],
                ];
            })
            ->all();

        $promociones = $actividad->promociones
            ->sortBy('id_promocion')
            ->mapWithKeys(fn ($promocion) => [
                (string) $promocion->id_promocion => [
                    'id' => (int) $promocion->id_promocion,
                    'codigo' => $promocion->codigo,
                    'nombre' => $promocion->nombre,
                    'tipo_descuento' => $promocion->tipo_descuento,
                    'valor' => $this->decimal($promocion->valor),
                    'vigente_desde' => $this->fecha($promocion->vigente_desde),
                    'vigente_hasta' => $this->fecha($promocion->vigente_hasta),
                    'limite_usos' => $promocion->limite_usos,
                    'limite_por_persona' => $promocion->limite_por_persona,
                    'monto_minimo' => $this->decimal($promocion->monto_minimo),
                    'activo' => (bool) $promocion->activo,
                    'items' => $promocion->items
                        ->pluck('id_item_actividad')
                        ->map(fn ($id) => (int) $id)
                        ->sort()
                        ->values()
                        ->all(),
                ],
            ])
            ->all();

        $mediosGenerales = $actividad->medios
            ->filter(fn ($medio) => empty($medio->id_item_actividad) && empty($medio->id_sesion))
            ->sortBy(fn ($medio) => sprintf('%08d-%08d', (int) $medio->orden, (int) $medio->id_medio))
            ->map(fn ($medio) => [
                'id' => (int) $medio->id_medio,
                'tipo' => $medio->tipo,
                'url' => $medio->url,
                'texto_alternativo' => $medio->texto_alternativo,
                'es_portada' => (bool) $medio->es_portada,
                'orden' => (int) $medio->orden,
            ])
            ->values()
            ->all();

        $recursosGenerales = $actividad->recursos
            ->sortBy(fn ($recurso) => (int) ($recurso->getAttribute('id_recurso_actividad') ?? $recurso->getAttribute('id_recurso') ?? 0))
            ->map(fn ($recurso) => $this->atributosModelo($recurso))
            ->values()
            ->all();

        return $this->ordenarRecursivo([
            'informacion_general' => [
                'nombre' => $actividad->nombre,
                'id_categoria' => $actividad->id_categoria,
                'resumen' => $actividad->resumen,
                'descripcion' => $actividad->descripcion,
                'visibilidad' => $actividad->visibilidad,
                'visible_desde' => $this->fecha($actividad->visible_desde),
                'visible_hasta' => $this->fecha($actividad->visible_hasta),
                'id_espacio' => $actividad->id_espacio,
                'ubicacion_externa' => $actividad->ubicacion_externa,
                'realizacion_desde' => $this->fecha($actividad->realizacion_desde),
                'realizacion_hasta' => $this->fecha($actividad->realizacion_hasta),
            ],
            'presentacion' => [
                'destacada' => (bool) $actividad->destacada,
                'prioridad' => (int) $actividad->prioridad,
                'etiquetas' => $actividad->etiquetas
                    ->pluck('id_etiqueta')
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all(),
                'medios' => $mediosGenerales,
            ],
            'inscripcion' => [
                'habilita_inscripcion' => (bool) $actividad->habilita_inscripcion,
                'requiere_cuenta' => (bool) $actividad->requiere_cuenta,
                'permite_lista_espera' => (bool) $actividad->permite_lista_espera,
                'cupo_total' => $actividad->cupo_total,
                'inscripcion_desde' => $this->fecha($actividad->inscripcion_desde),
                'inscripcion_hasta' => $this->fecha($actividad->inscripcion_hasta),
            ],
            'productos' => [
                'items' => $itemsEstructura,
                'configuracion' => $this->limpiarConfig($documento?->product_configuration ?? []),
            ],
            'datos_solicitados' => [
                'formularios' => $formularios,
                'purchase_fields' => $this->limpiarConfig($documento?->purchase_fields ?? []),
            ],
            'precios_costos' => [
                'items' => $preciosItems,
                'pricing_configuration' => $this->limpiarConfig($documento?->pricing_configuration ?? []),
                'promociones' => $promociones,
            ],
            'programacion' => [
                'sesiones' => $sesiones,
                'recursos_generales' => $recursosGenerales,
                'session_configuration' => $this->limpiarConfig($documento?->session_configuration ?? []),
            ],
        ]);
    }

    private function compararInformacionGeneral(array $anterior, array $actual): array
    {
        $labels = [
            'nombre' => 'Nombre',
            'id_categoria' => 'Categoría',
            'resumen' => 'Resumen',
            'descripcion' => 'Descripción',
            'visibilidad' => 'Visibilidad',
            'visible_desde' => 'Inicio de visibilidad',
            'visible_hasta' => 'Fin de visibilidad',
            'id_espacio' => 'Espacio general',
            'ubicacion_externa' => 'Ubicación externa',
            'realizacion_desde' => 'Inicio del período de realización',
            'realizacion_hasta' => 'Fin del período de realización',
        ];

        return $this->compararCampos($anterior, $actual, $labels);
    }

    private function compararPresentacion(array $anterior, array $actual): array
    {
        $resultado = $this->compararCampos($anterior, $actual, [
            'destacada' => 'Actividad destacada',
            'prioridad' => 'Prioridad',
            'etiquetas' => 'Etiquetas',
            'medios' => 'Portada o galería',
        ]);

        return $resultado;
    }

    private function compararInscripcion(array $anterior, array $actual): array
    {
        return $this->compararCampos($anterior, $actual, [
            'habilita_inscripcion' => 'Habilitación de inscripciones',
            'requiere_cuenta' => 'Requisito de cuenta',
            'permite_lista_espera' => 'Lista de espera',
            'cupo_total' => 'Cupo total',
            'inscripcion_desde' => 'Inicio de inscripciones',
            'inscripcion_hasta' => 'Fin de inscripciones',
        ]);
    }

    private function compararDatosSolicitados(array $anterior, array $actual): array
    {
        $resultado = $this->compararCampos($anterior, $actual, [
            'formularios' => 'Formularios y campos',
            'purchase_fields' => 'Datos solicitados por producto/servicio',
        ]);

        $resultado['entities'] = $this->detectarEntidadesCambiadas(
            $anterior['formularios'] ?? [],
            $actual['formularios'] ?? []
        );

        return $resultado;
    }

    private function compararPrecios(array $anterior, array $actual): array
    {
        $resultado = $this->compararCampos($anterior, $actual, [
            'items' => 'Precios o costos base/variantes',
            'pricing_configuration' => 'Reglas de precio y costo',
            'promociones' => 'Promociones',
        ]);

        $resultado['entities'] = $this->detectarEntidadesCambiadas(
            $anterior['items'] ?? [],
            $actual['items'] ?? []
        );

        return $resultado;
    }

    private function compararProgramacion(array $anterior, array $actual): array
    {
        $resultado = $this->compararCampos($anterior, $actual, [
            'sesiones' => 'Sesiones, fechas u horarios',
            'recursos_generales' => 'Recursos generales',
            'session_configuration' => 'Configuración de programación',
        ]);

        $resultado['entities'] = $this->detectarEntidadesCambiadas(
            $anterior['sesiones'] ?? [],
            $actual['sesiones'] ?? []
        );

        return $resultado;
    }

    private function compararEntidades(
        array $anterior,
        array $actual,
        string $clave,
        string $label
    ): array {
        $campos = [];

        if (!$this->iguales($anterior[$clave] ?? [], $actual[$clave] ?? [])) {
            $campos[] = $label;
        }

        if (!$this->iguales($anterior['configuracion'] ?? [], $actual['configuracion'] ?? [])) {
            $campos[] = 'Configuración general de productos/servicios';
        }

        return [
            'changed' => !empty($campos),
            'fields' => $campos,
            'entities' => $this->detectarEntidadesCambiadas(
                $anterior[$clave] ?? [],
                $actual[$clave] ?? []
            ),
        ];
    }

    private function compararCampos(array $anterior, array $actual, array $labels): array
    {
        $campos = [];

        foreach ($labels as $clave => $label) {
            if (!$this->iguales($anterior[$clave] ?? null, $actual[$clave] ?? null)) {
                $campos[] = $label;
            }
        }

        return [
            'changed' => !empty($campos),
            'fields' => $campos,
            'entities' => [],
        ];
    }

    private function detectarEntidadesCambiadas(array $anterior, array $actual): array
    {
        $ids = collect(array_keys($anterior))
            ->merge(array_keys($actual))
            ->unique()
            ->sort()
            ->values();

        $resultado = [];

        foreach ($ids as $id) {
            $antes = $anterior[$id] ?? null;
            $ahora = $actual[$id] ?? null;

            if ($antes === null && $ahora !== null) {
                $resultado[] = [
                    'id' => (string) $id,
                    'status' => 'nuevo',
                    'label' => $ahora['nombre'] ?? $ahora['name'] ?? "#{$id}",
                ];
                continue;
            }

            if ($antes !== null && $ahora === null) {
                $resultado[] = [
                    'id' => (string) $id,
                    'status' => 'eliminado',
                    'label' => $antes['nombre'] ?? $antes['name'] ?? "#{$id}",
                ];
                continue;
            }

            if (!$this->iguales($antes, $ahora)) {
                $resultado[] = [
                    'id' => (string) $id,
                    'status' => 'modificado',
                    'label' => $ahora['nombre']
                        ?? $ahora['name']
                        ?? $antes['nombre']
                        ?? $antes['name']
                        ?? "#{$id}",
                ];
            }
        }

        return $resultado;
    }

    private function seccionesVacias(): array
    {
        return collect([
            'informacion_general',
            'presentacion',
            'productos',
            'datos_solicitados',
            'precios_costos',
            'programacion',
            'inscripcion',
        ])->mapWithKeys(fn ($seccion) => [
            $seccion => [
                'changed' => false,
                'fields' => [],
                'entities' => [],
            ],
        ])->all();
    }

    private function obtenerDocumento(Actividad $actividad): ActivityContent
    {
        $documento = ActivityContent::query()
            ->where('id_actividad_pg', (int) $actividad->id_actividad)
            ->first();

        if (!$documento) {
            $documento = new ActivityContent();
            $documento->id_actividad_pg = (int) $actividad->id_actividad;
        }

        return $documento;
    }

    private function atributosModelo(EloquentModel $modelo): array
    {
        $atributos = $modelo->getAttributes();

        foreach (['created_at', 'updated_at', 'creado_en', 'actualizado_en'] as $clave) {
            unset($atributos[$clave]);
        }

        return $this->ordenarRecursivo($atributos);
    }

    private function limpiarConfig(mixed $valor): mixed
    {
        $normalizado = $this->normalizar($valor);

        return $this->eliminarVolatiles($normalizado);
    }

    private function eliminarVolatiles(mixed $valor): mixed
    {
        if (!is_array($valor)) {
            return $valor;
        }

        $resultado = [];

        foreach ($valor as $clave => $item) {
            if (in_array((string) $clave, [
                '_id',
                'updated_at',
                'created_at',
                'completed_at',
                'completed_by',
            ], true)) {
                continue;
            }

            $resultado[$clave] = $this->eliminarVolatiles($item);
        }

        return $this->ordenarRecursivo($resultado);
    }

    private function normalizar(mixed $valor): array
    {
        if (empty($valor)) {
            return [];
        }

        if ($valor instanceof Collection) {
            $valor = $valor->all();
        }

        if (is_array($valor)) {
            return $valor;
        }

        return json_decode(json_encode($valor), true) ?? [];
    }

    private function ordenarRecursivo(mixed $valor): mixed
    {
        if (!is_array($valor)) {
            return $valor;
        }

        foreach ($valor as $clave => $item) {
            $valor[$clave] = $this->ordenarRecursivo($item);
        }

        if (!array_is_list($valor)) {
            ksort($valor);
        }

        return $valor;
    }

    private function iguales(mixed $a, mixed $b): bool
    {
        return json_encode($this->ordenarRecursivo($a), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            === json_encode($this->ordenarRecursivo($b), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function fecha(mixed $valor): ?string
    {
        if (!$valor) {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($valor)->toIso8601String();
        } catch (\Throwable $e) {
            return (string) $valor;
        }
    }

    private function decimal(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return number_format((float) $valor, 2, '.', '');
    }
}
