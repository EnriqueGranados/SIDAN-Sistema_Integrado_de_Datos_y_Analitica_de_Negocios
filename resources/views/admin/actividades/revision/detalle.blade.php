@extends('layouts.navbars')

@section('title', 'Revisión de actividad')

@section('content')
@php
    $observacionesActuales = $observacionesPendientes ?? ($revisionActual->observaciones ?? collect());

    $nombresSecciones = [
        'informacion_general' => 'Información general',
        'presentacion' => 'Presentación',
        'productos' => 'Productos / Servicios',
        'datos_solicitados' => 'Datos solicitados',
        'precios_costos' => 'Precios / Costos',
        'programacion' => 'Programación',
        'inscripcion' => 'Inscripción',
    ];

    $etapas = [
        [
            'id' => 'informacion',
            'nombre' => 'Información',
            'secciones' => ['informacion_general'],
        ],
        [
            'id' => 'presentacion',
            'nombre' => 'Presentación',
            'secciones' => ['presentacion'],
        ],
        [
            'id' => 'productos',
            'nombre' => 'Productos / Servicios',
            'secciones' => ['productos'],
        ],
        [
            'id' => 'datos',
            'nombre' => 'Datos solicitados',
            'secciones' => ['datos_solicitados'],
        ],
        [
            'id' => 'precios',
            'nombre' => 'Precios / Costos',
            'secciones' => ['precios_costos'],
        ],
        [
            'id' => 'programacion',
            'nombre' => 'Programación',
            'secciones' => ['programacion', 'inscripcion'],
        ],
        [
            'id' => 'decision',
            'nombre' => 'Resumen y decisión',
            'secciones' => [],
        ],
    ];

    $cambiosRevision = $cambiosDesdeAprobacion ?? [
        'has_baseline' => false,
        'has_changes' => false,
        'baseline_revision' => null,
        'sections' => [],
    ];

    $seccionesCambiadas = collect($cambiosRevision['sections'] ?? [])
        ->filter(fn ($seccion) => (bool) ($seccion['changed'] ?? false));

    $campoFueModificado = function (string $seccion, string $campo) use ($cambiosRevision): bool {
        return collect($cambiosRevision['sections'][$seccion]['fields'] ?? [])->contains($campo);
    };

    $mapaEtapaCambios = [
        'informacion' => ['informacion_general'],
        'presentacion' => ['presentacion'],
        'productos' => ['productos'],
        'datos' => ['datos_solicitados'],
        'precios' => ['precios_costos'],
        'programacion' => ['programacion', 'inscripcion'],
        'decision' => [],
    ];

    $etapaTieneCambios = function (string $etapa) use ($mapaEtapaCambios, $seccionesCambiadas) {
        foreach ($mapaEtapaCambios[$etapa] ?? [] as $seccion) {
            if ($seccionesCambiadas->has($seccion)) {
                return true;
            }
        }

        return false;
    };

    $nombresCambios = [
        'informacion_general' => ['etapa' => 'informacion', 'titulo' => 'Información'],
        'presentacion' => ['etapa' => 'presentacion', 'titulo' => 'Presentación'],
        'productos' => ['etapa' => 'productos', 'titulo' => 'Productos / Servicios'],
        'datos_solicitados' => ['etapa' => 'datos', 'titulo' => 'Datos solicitados'],
        'precios_costos' => ['etapa' => 'precios', 'titulo' => 'Precios / Costos'],
        'programacion' => ['etapa' => 'programacion', 'titulo' => 'Programación'],
        'inscripcion' => ['etapa' => 'programacion', 'titulo' => 'Inscripción'],
    ];

    $conteoEtapa = function (array $secciones) use ($observacionesActuales) {
        return $observacionesActuales
            ->whereIn('seccion', $secciones)
            ->count();
    };

    $observacionesSeccion = function (string $seccion) use ($observacionesActuales) {
        return $observacionesActuales
            ->where('seccion', $seccion)
            ->values();
    };

    $observacionesReferencia = function (
        string $seccion,
        ?string $tipo = null,
        ?int $id = null
    ) use ($observacionesActuales) {
        return $observacionesActuales
            ->filter(function ($observacion) use ($seccion, $tipo, $id) {
                if ($observacion->seccion !== $seccion) {
                    return false;
                }

                if ($tipo === null) {
                    return empty($observacion->referencia_tipo)
                        && empty($observacion->referencia_id);
                }

                return $observacion->referencia_tipo === $tipo
                    && (int) $observacion->referencia_id === (int) $id;
            })
            ->values();
    };

    $resolverMedio = function (?string $ruta) {
        if (!$ruta) {
            return null;
        }

        $ruta = str_replace('\\', '/', trim($ruta));

        if (
            str_starts_with($ruta, 'http://')
            || str_starts_with($ruta, 'https://')
            || str_starts_with($ruta, 'data:')
        ) {
            return $ruta;
        }

        $ruta = ltrim($ruta, '/');

        if (str_starts_with($ruta, 'public/')) {
            $ruta = substr($ruta, 7);
        }

        if (str_starts_with($ruta, 'storage/')) {
            return asset($ruta);
        }

        return asset('storage/'.$ruta);
    };

    $mediosGenerales = $actividad->medios
        ->filter(fn ($medio) =>
            empty($medio->id_item_actividad)
            && empty($medio->id_sesion)
        )
        ->sortBy('orden')
        ->values();

    $portada = $mediosGenerales
        ->first(fn ($medio) => (bool) $medio->es_portada);

    $galeria = $mediosGenerales
        ->filter(fn ($medio) => !(bool) $medio->es_portada)
        ->values();

    $formatoFecha = function ($fecha, bool $hora = true) {
        if (!$fecha) {
            return 'No definido';
        }

        try {
            return \Illuminate\Support\Carbon::parse($fecha)
                ->format($hora ? 'd/m/Y H:i' : 'd/m/Y');
        } catch (\Throwable $e) {
            return (string) $fecha;
        }
    };

    $nombreUsuario = function ($usuario) {
        if (!$usuario) {
            return 'Usuario';
        }

        $informacion = $usuario->informacion_personal ?? null;

        if ($informacion) {
            $nombre = trim(
                ($informacion->nombres ?? '').' '.
                ($informacion->apellidos ?? '')
            );

            if ($nombre !== '') {
                return $nombre;
            }
        }

        return $usuario->name
            ?? $usuario->nombre
            ?? $usuario->correo
            ?? 'Usuario';
    };

    $nombreReferencia = function ($observacion) use ($actividad) {
        if (!$observacion->referencia_tipo || !$observacion->referencia_id) {
            return null;
        }

        return match ($observacion->referencia_tipo) {
            'item' => $actividad->items
                ->firstWhere(
                    'id_item_actividad',
                    $observacion->referencia_id
                )?->nombre
                ?? 'Producto/servicio #'.$observacion->referencia_id,

            'sesion' => $actividad->sesiones
                ->firstWhere(
                    'id_sesion',
                    $observacion->referencia_id
                )?->nombre
                ?? 'Sesión #'.$observacion->referencia_id,

            'formulario' => $actividad->formularios
                ->firstWhere(
                    'id_formulario',
                    $observacion->referencia_id
                )?->nombre
                ?? 'Formulario #'.$observacion->referencia_id,

            'campo' => $actividad->formularios
                ->flatMap(fn ($formulario) => $formulario->campos)
                ->firstWhere(
                    'id_campo',
                    $observacion->referencia_id
                )?->nombre
                ?? 'Campo #'.$observacion->referencia_id,

            default => null,
        };
    };

    $buscarConfiguracionProducto = function ($item) use ($configuracionProductos) {
        $id = (string) $item->id_item_actividad;

        if (isset($configuracionProductos[$id])) {
            return $configuracionProductos[$id];
        }

        foreach ($configuracionProductos as $configuracion) {
            if (!is_array($configuracion)) {
                continue;
            }

            $idConfigurado = $configuracion['id_item_pg']
                ?? $configuracion['id_item_actividad']
                ?? $configuracion['item_id']
                ?? null;

            if ((string) $idConfigurado === $id) {
                return $configuracion;
            }
        }

        return [];
    };

    $extraerAtributos = function (array $configuracion) {
        $atributos = $configuracion['attributes']
            ?? $configuracion['atributos']
            ?? $configuracion['variant_attributes']
            ?? $configuracion['variantAttributes']
            ?? [];

        if (!is_array($atributos)) {
            return [];
        }

        $resultado = [];

        foreach ($atributos as $indice => $atributo) {
            if (!is_array($atributo)) {
                continue;
            }

            $nombre = $atributo['name']
                ?? $atributo['nombre']
                ?? $atributo['label']
                ?? $atributo['attribute']
                ?? (is_string($indice) ? $indice : 'Atributo');

            $claveAtributo = $atributo['key']
                ?? $atributo['clave']
                ?? $atributo['field_key']
                ?? (is_string($indice) ? $indice : $nombre);

            $opciones = $atributo['options']
                ?? $atributo['opciones']
                ?? $atributo['values']
                ?? $atributo['valores']
                ?? [];

            if (!is_array($opciones)) {
                $opciones = [];
            }

            $opcionesNormalizadas = [];

            foreach ($opciones as $clave => $opcion) {
                if (is_array($opcion)) {
                    $opcionesNormalizadas[] = [
                        'valor' => $opcion['value']
                            ?? $opcion['valor']
                            ?? $opcion['name']
                            ?? $opcion['nombre']
                            ?? $opcion['label']
                            ?? (is_string($clave) ? $clave : ''),
                        'precio' => (float) (
                            $opcion['price_delta']
                            ?? $opcion['precio_delta']
                            ?? $opcion['price_adjustment']
                            ?? $opcion['ajuste_precio']
                            ?? 0
                        ),
                        'costo' => (float) (
                            $opcion['cost_delta']
                            ?? $opcion['costo_delta']
                            ?? $opcion['cost_adjustment']
                            ?? $opcion['ajuste_costo']
                            ?? 0
                        ),
                    ];
                } else {
                    $opcionesNormalizadas[] = [
                        'valor' => (string) $opcion,
                        'precio' => 0,
                        'costo' => 0,
                    ];
                }
            }

            if ($opcionesNormalizadas) {
                $resultado[] = [
                    'clave' => (string) $claveAtributo,
                    'nombre' => (string) $nombre,
                    'opciones' => $opcionesNormalizadas,
                ];
            }
        }

        return $resultado;
    };

    $extraerReglas = function (array $configuracion) {
        $reglas = $configuracion['rules']
            ?? $configuracion['reglas']
            ?? $configuracion['pricing_rules']
            ?? $configuracion['pricingRules']
            ?? $configuracion['adjustments']
            ?? $configuracion['ajustes']
            ?? [];

        return is_array($reglas) ? $reglas : [];
    };

    $normalizarClaveRevision = function ($valor) {
        $valor = strtolower(trim((string) $valor));
        $valor = str_replace([' ', '-'], '_', $valor);

        return preg_replace('/_+/', '_', $valor) ?: $valor;
    };

    $aplicarReglasAtributos = function (array $atributos, array $reglas) use ($normalizarClaveRevision) {
        if (!$atributos || !$reglas) {
            return $atributos;
        }

        foreach ($atributos as &$atributo) {
            $claveAtributo = $normalizarClaveRevision($atributo['clave'] ?? $atributo['nombre'] ?? '');
            $nombreAtributo = $normalizarClaveRevision($atributo['nombre'] ?? '');

            foreach ($atributo['opciones'] as &$opcion) {
                $valorOpcion = strtolower(trim((string) ($opcion['valor'] ?? '')));

                foreach ($reglas as $regla) {
                    if (!is_array($regla)) {
                        continue;
                    }

                    $claveRegla = $normalizarClaveRevision($regla['field_key'] ?? '');
                    $nombreRegla = $normalizarClaveRevision($regla['field_label'] ?? '');
                    $valorRegla = strtolower(trim((string) ($regla['option'] ?? '')));

                    $mismoCampo = ($claveRegla !== '' && $claveRegla === $claveAtributo)
                        || ($nombreRegla !== '' && $nombreRegla === $nombreAtributo)
                        || ($claveRegla !== '' && $claveRegla === $nombreAtributo);

                    if (!$mismoCampo || $valorRegla !== $valorOpcion) {
                        continue;
                    }

                    $opcion['precio'] = (float) ($regla['price_increment'] ?? $opcion['precio'] ?? 0);
                    $opcion['costo'] = (float) ($regla['cost_increment'] ?? $opcion['costo'] ?? 0);
                    break;
                }
            }
            unset($opcion);
        }
        unset($atributo);

        return $atributos;
    };

    $valorLegible = function ($valor) {
        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }

        if ($valor === null || $valor === '') {
            return '—';
        }

        if (is_array($valor)) {
            $partes = [];

            foreach ($valor as $clave => $item) {
                if (is_scalar($item) || $item === null) {
                    $partes[] = is_string($clave)
                        ? $clave.': '.$item
                        : (string) $item;
                }
            }

            return $partes
                ? implode(', ', $partes)
                : 'Configuración avanzada';
        }

        return (string) $valor;
    };

    $historial = $actividad->revisiones
        ->sortByDesc('id_revision')
        ->values();

    $etiquetaAccionRevision = function (?string $accion): string {
        return match ($accion) {
            'enviada_revision' => 'Enviada a revisión',
            'cambios_solicitados' => 'Cambios solicitados',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
            'retirada' => 'Revisión retirada',
            default => ucfirst(str_replace('_', ' ', (string) $accion)),
        };
    };

    $tonoAccionRevision = function (?string $accion): string {
        return match ($accion) {
            'aprobada' => 'success',
            'cambios_solicitados' => 'warning',
            'rechazada' => 'danger',
            'retirada' => 'muted',
            default => 'info',
        };
    };

    $totalObservaciones = $observacionesActuales->count();

    $recursosGenerales = $actividad->recursos
        ->filter(fn ($recurso) => empty($recurso->id_sesion))
        ->values();

    $normalizarConfiguracionRevision = function ($valor) {
        if ($valor instanceof \Illuminate\Support\Collection) {
            return $valor->values()->all();
        }

        if (is_object($valor)) {
            $valor = json_decode(json_encode($valor), true);
        }

        return is_array($valor) ? $valor : [];
    };

    $fuenteDatosCompra = $configuracionesDatos ?? ($camposCompra ?? null);

    if ($fuenteDatosCompra === null) {
        try {
            $contenidoRevision = \App\Models\Mongo\ActivityContent::query()
                ->where('id_actividad_pg', (int) $actividad->id_actividad)
                ->first();

            $fuenteDatosCompra = $contenidoRevision?->purchase_fields ?? [];
        } catch (\Throwable $e) {
            $fuenteDatosCompra = [];
        }
    }

    $configuracionesDatosRevision = collect(
        $normalizarConfiguracionRevision($fuenteDatosCompra)
    )
        ->filter(fn ($configuracion) => is_array($configuracion))
        ->keyBy(fn ($configuracion) => (string) ($configuracion['id_item_pg'] ?? ''));

    $tipoDatoHumano = function (?string $tipo) {
        return match ($tipo) {
            'lista' => 'Elegir una opción',
            'numero' => 'Escribir un número',
            'texto' => 'Escribir texto',
            default => 'Ingresar un dato',
        };
    };

    $descripcionDatoHumana = function (array $campo) use ($tipoDatoHumano) {
        $texto = $tipoDatoHumano($campo['type'] ?? null);

        if (($campo['required'] ?? false) === true) {
            $texto .= ' · obligatorio';
        } else {
            $texto .= ' · opcional';
        }

        return $texto;
    };

    $totalDatosCompra = $configuracionesDatosRevision
        ->filter(fn ($configuracion) => (bool) ($configuracion['enabled'] ?? false))
        ->sum(fn ($configuracion) => count($configuracion['fields'] ?? []));
@endphp

<div
    id="revision-app"
    class="revision-page mx-auto w-full max-w-7xl px-2 py-3 text-slate-900 dark:text-white sm:px-4 lg:px-6"
    data-observaciones-url="{{ route('admin.actividades.revision.observaciones.store', $actividad) }}"
    data-aprobar-url="{{ route('admin.actividades.revision.aprobar', $actividad) }}"
    data-cambios-url="{{ route('admin.actividades.revision.solicitar-cambios', $actividad) }}"
    data-rechazar-url="{{ route('admin.actividades.revision.rechazar', $actividad) }}"
    data-csrf="{{ csrf_token() }}"
    data-initial-error="{{ $errors->first() }}"
>
    <div id="toast-container" class="toast-container" aria-live="polite"></div>

    <div class="revision-header mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <a href="{{ route('admin.actividades.revision.index') }}" class="back-link transition-colors hover:text-sidan-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidan-500/30">
                ← Volver a revisiones
            </a>

            <div class="eyebrow text-xs font-black uppercase tracking-[0.16em] text-sidan-500">Revisión de actividad</div>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 dark:text-white">{{ $actividad->nombre }}</h1>

            <p>
                Comprueba cómo verá y utilizará esta actividad una persona.
                Si algo debe corregirse, deja la observación justo donde corresponde.
            </p>
        </div>

        <div class="revision-status" aria-label="Resumen de la revisión">
            <div>
                <span>Revisión</span>
                <strong>#{{ $actividad->revision_actual }}</strong>
            </div>

            <div>
                <span>Observaciones</span>
                <strong id="total-observaciones">{{ $totalObservaciones }}</strong>
            </div>

            <div>
                <span>Estado</span>
                <strong class="status-pending">Pendiente</strong>
            </div>
        </div>
    </div>

    @if (($cambiosRevision['has_baseline'] ?? false) && ($cambiosRevision['has_changes'] ?? false))
        <div class="approved-changes-panel">
            <div class="approved-changes-heading">
                <div>
                    <span class="approved-changes-kicker">Nueva versión sobre una actividad aprobada</span>
                    <h2>Cambios desde la revisión #{{ $cambiosRevision['baseline_revision'] ?? '?' }}</h2>
                    <p>
                        Estas son las secciones que cambiaron desde la última aprobación.
                        Puedes ir directo a ellas sin volver a leer toda la actividad.
                    </p>
                </div>

                <span class="approved-changes-count">
                    {{ $seccionesCambiadas->count() }}
                    {{ $seccionesCambiadas->count() === 1 ? 'sección modificada' : 'secciones modificadas' }}
                </span>
            </div>

            <div class="approved-changes-grid">
                @foreach ($seccionesCambiadas as $claveSeccion => $detalleCambio)
                    @php
                        $metaCambio = $nombresCambios[$claveSeccion] ?? [
                            'etapa' => 'informacion',
                            'titulo' => ucfirst(str_replace('_', ' ', $claveSeccion)),
                        ];
                        $camposCambio = $detalleCambio['fields'] ?? [];
                        $entidadesCambio = $detalleCambio['entities'] ?? [];
                    @endphp

                    <button
                        type="button"
                        class="approved-change-card"
                        data-change-stage="{{ $metaCambio['etapa'] }}"
                    >
                        <span class="approved-change-card-title">
                            {{ $metaCambio['titulo'] }}
                        </span>

                        @if (!empty($camposCambio))
                            <span class="approved-change-card-fields">
                                {{ implode(' · ', array_slice($camposCambio, 0, 3)) }}
                                @if (count($camposCambio) > 3)
                                    · +{{ count($camposCambio) - 3 }}
                                @endif
                            </span>
                        @endif

                        @if (!empty($entidadesCambio))
                            <span class="approved-change-card-entities">
                                @foreach (array_slice($entidadesCambio, 0, 3) as $entidadCambio)
                                    @php
                                        $estadoEntidad = $entidadCambio['status'] ?? 'modificado';
                                    @endphp
                                    <span>
                                        <i
                                            class="entity-status-dot entity-status-dot--{{ $estadoEntidad }}"
                                            title="{{ ucfirst($estadoEntidad) }}"
                                            aria-label="{{ ucfirst($estadoEntidad) }}"
                                        ></i>
                                        {{ $entidadCambio['label'] ?? '#'.$entidadCambio['id'] }}
                                    </span>
                                @endforeach
                            </span>
                        @endif

                        <span class="approved-change-card-action">Ir a revisar →</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <div class="stage-navigation" role="tablist" aria-label="Etapas de revisión de la actividad">
        @foreach ($etapas as $indice => $etapa)
            @php
                $cantidad = $conteoEtapa($etapa['secciones']);
            @endphp

            <button
                type="button"
                class="stage-button focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sidan-500/40 {{ $indice === 0 ? 'active' : '' }}"
                data-stage-button="{{ $etapa['id'] }}"
                role="tab"
                aria-selected="{{ $indice === 0 ? 'true' : 'false' }}"
            >
                <span class="stage-number">{{ $indice + 1 }}</span>
                <span class="stage-name">{{ $etapa['nombre'] }}</span>

                @if ($etapaTieneCambios($etapa['id']))
                    <span
                        class="stage-change-dot"
                        title="Esta etapa contiene cambios desde la última aprobación"
                        aria-label="Esta etapa contiene cambios desde la última aprobación"
                    ></span>
                @endif

                <span
                    class="stage-count {{ $cantidad ? '' : 'hidden' }}"
                    data-stage-count="{{ $etapa['id'] }}"
                >
                    {{ $cantidad }}
                </span>
            </button>
        @endforeach
    </div>

    <main class="review-content">
        {{-- ETAPA 1 --}}
        <section class="review-stage active {{ $etapaTieneCambios('informacion') ? 'stage-has-changes' : '' }}" data-stage="informacion">
            <div class="section-heading">
                <div>
                    <h2>Información general</h2>
                    <p>Qué se publicará, dónde se realizará y durante qué fechas estará disponible.</p>
                </div>
            </div>

            <div class="info-grid">
                <article class="info-card">
                    <span class="info-label">
                        Nombre
                        @if ($campoFueModificado('informacion_general', 'Nombre'))
                            <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                        @endif
                    </span>
                    <strong>{{ $actividad->nombre ?: 'Sin nombre' }}</strong>
                </article>

                <article class="info-card">
                    <span class="info-label">
                        Categoría
                        @if ($campoFueModificado('informacion_general', 'Categoría'))
                            <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                        @endif
                    </span>
                    <strong>
                        {{ $actividad->categoria?->nombre
                            ?? ($actividad->id_categoria ? 'Categoría #'.$actividad->id_categoria : 'Sin categoría') }}
                    </strong>
                </article>

                <article class="info-card">
                    <span class="info-label">
                        Visibilidad
                        @if ($campoFueModificado('informacion_general', 'Visibilidad'))
                            <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                        @endif
                    </span>
                    <strong>{{ ucfirst($actividad->visibilidad ?? 'No definida') }}</strong>
                </article>

                <article class="info-card">
                    <span class="info-label">Estado de publicación</span>
                    <strong>{{ ucfirst(str_replace('_', ' ', $actividad->estado_publicacion ?? 'No definido')) }}</strong>
                </article>

                <article class="info-card">
                    <span class="info-label">Estado operativo</span>
                    <strong>{{ ucfirst(str_replace('_', ' ', $actividad->estado_operativo ?? 'No definido')) }}</strong>
                </article>

                <article class="info-card">
                    <span class="info-label">Creada por</span>
                    <strong>{{ $nombreUsuario($actividad->creador) }}</strong>
                </article>
            </div>

            <div class="content-card">
                <h3>
                    Resumen
                    @if ($campoFueModificado('informacion_general', 'Resumen'))
                        <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                    @endif
                </h3>
                <p>{{ $actividad->resumen ?: 'Sin resumen.' }}</p>

                <h3>
                    Descripción
                    @if ($campoFueModificado('informacion_general', 'Descripción'))
                        <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                    @endif
                </h3>
                <div class="long-text">
                    {!! nl2br(e($actividad->descripcion ?: 'Sin descripción.')) !!}
                </div>
            </div>

            <div class="info-grid">
                <article class="info-card">
                    <span class="info-label">
                        Visible desde
                        @if ($campoFueModificado('informacion_general', 'Inicio de visibilidad'))
                            <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                        @endif
                    </span>
                    <strong>{{ $formatoFecha($actividad->visible_desde) }}</strong>
                </article>

                <article class="info-card">
                    <span class="info-label">
                        Visible hasta
                        @if ($campoFueModificado('informacion_general', 'Fin de visibilidad'))
                            <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                        @endif
                    </span>
                    <strong>{{ $formatoFecha($actividad->visible_hasta) }}</strong>
                </article>

                <article class="info-card">
                    <span class="info-label">
                        Realización desde
                        @if ($campoFueModificado('informacion_general', 'Inicio del período de realización'))
                            <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                        @endif
                    </span>
                    <strong>{{ $formatoFecha($actividad->realizacion_desde) }}</strong>
                </article>

                <article class="info-card">
                    <span class="info-label">
                        Realización hasta
                        @if ($campoFueModificado('informacion_general', 'Fin del período de realización'))
                            <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                        @endif
                    </span>
                    <strong>{{ $formatoFecha($actividad->realizacion_hasta) }}</strong>
                </article>
            </div>

            <div class="content-card">
                <h3>
                    Ubicación general
                    @if ($campoFueModificado('informacion_general', 'Espacio general') || $campoFueModificado('informacion_general', 'Ubicación externa'))
                        <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                    @endif
                </h3>

                @if ($actividad->espacio)
                    <p class="muted compact-meta">Ubicación registrada</p>
                    <p>
                        <strong>{{ $actividad->espacio->nombre ?? 'Espacio asignado' }}</strong>
                    </p>

                    @if ($actividad->espacio->contenedor)
                        <p class="muted">
                            {{ $actividad->espacio->contenedor->nombre }}
                        </p>
                    @endif
                @elseif (!empty($actividad->ubicacion_externa))
                    <p class="muted compact-meta">Ubicación externa</p>
                    <p>{{ $actividad->ubicacion_externa }}</p>
                @else
                    <p class="muted">No se definió una ubicación general.</p>
                @endif
            </div>

            <div class="content-card">
                <h3>
                    Etiquetas
                    @if ($campoFueModificado('presentacion', 'Etiquetas'))
                        <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                    @endif
                </h3>

                @if ($actividad->etiquetas->isNotEmpty())
                    <div class="tag-list">
                        @foreach ($actividad->etiquetas as $etiqueta)
                            <span class="tag">{{ $etiqueta->nombre }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="muted">Sin etiquetas.</p>
                @endif
            </div>

            @include('admin.actividades.revision._observacion_inline', [
                'seccion' => 'informacion_general',
                'titulo' => 'Observaciones de información general',
                'observaciones' => $observacionesReferencia('informacion_general'),
            ])

            <div class="stage-footer">
                <span></span>
                <button type="button" class="btn-primary" data-next-stage>
                    Siguiente →
                </button>
            </div>
        </section>

        {{-- ETAPA 2 --}}
        <section class="review-stage {{ $etapaTieneCambios('presentacion') ? 'stage-has-changes' : '' }}" data-stage="presentacion">
            <div class="section-heading">
                <div>
                    <h2>Presentación</h2>
                    <p>Cómo se presentará visualmente la actividad a las personas.</p>
                </div>
            </div>

            <div class="presentation-grid">
                <div class="content-card">
                    <h3>Portada</h3>

                    @if ($portada)
                        <img
                            src="{{ $resolverMedio($portada->url) }}"
                            alt="{{ $portada->texto_alternativo ?: $actividad->nombre }}"
                            class="cover-image"
                            loading="lazy"
                        >
                    @else
                        <div class="empty-media">Sin imagen de portada</div>
                    @endif
                </div>

                <div class="content-card">
                    <h3>Configuración</h3>

                    <dl class="detail-list">
                        <div>
                            <dt>
                                Destacada
                                @if ($campoFueModificado('presentacion', 'Actividad destacada'))
                                    <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                                @endif
                            </dt>
                            <dd>{{ $actividad->destacada ? 'Sí' : 'No' }}</dd>
                        </div>

                        <div>
                            <dt>
                                Prioridad
                                @if ($campoFueModificado('presentacion', 'Prioridad'))
                                    <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                                @endif
                            </dt>
                            <dd>{{ $actividad->prioridad_texto }}</dd>
                        </div>

                        <div>
                            <dt>
                                Imágenes de galería
                                @if ($campoFueModificado('presentacion', 'Portada o galería'))
                                    <span class="field-change-dot" title="Dato modificado" aria-label="Dato modificado"></span>
                                @endif
                            </dt>
                            <dd>{{ $galeria->count() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="content-card">
                <h3>Galería</h3>

                @if ($galeria->isEmpty())
                    <p class="muted">No se agregaron imágenes adicionales.</p>
                @else
                    <div class="gallery-grid">
                        @foreach ($galeria as $medio)
                            <figure class="gallery-item">
                                <img
                                    src="{{ $resolverMedio($medio->url) }}"
                                    alt="{{ $medio->texto_alternativo ?: $actividad->nombre }}"
                                    loading="lazy"
                                >

                                @if ($medio->texto_alternativo)
                                    <figcaption>
                                        {{ $medio->texto_alternativo }}
                                    </figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                @endif
            </div>

            @include('admin.actividades.revision._observacion_inline', [
                'seccion' => 'presentacion',
                'titulo' => 'Observaciones de presentación',
                'observaciones' => $observacionesReferencia('presentacion'),
            ])

            <div class="stage-footer">
                <button type="button" class="btn-secondary" data-prev-stage>
                    ← Anterior
                </button>

                <button type="button" class="btn-primary" data-next-stage>
                    Siguiente →
                </button>
            </div>
        </section>


        {{-- ETAPA 3 --}}
        <section class="review-stage {{ $etapaTieneCambios('productos') ? 'stage-has-changes' : '' }}" data-stage="productos">
            <div class="section-heading">
                <div>
                    <h2>Productos / Servicios</h2>
                    <p>
                        Lista de lo que una persona podrá comprar o reservar.
                        Los precios, opciones y combinaciones se revisan en Precios / Costos.
                    </p>
                </div>
            </div>

            @if ($actividad->items->isEmpty())
                <div class="empty-state">
                    Esta actividad no tiene productos o servicios configurados.
                </div>
            @else
                <div class="product-list">
                    @foreach ($actividad->items->sortBy('orden') as $item)
                        <details class="product-list-row">
                            <summary>
                                <div class="product-list-main">
                                    <span class="item-type">{{ ucfirst($item->tipo ?? 'servicio') }}</span>
                                    <strong>{{ $item->nombre }}</strong>

                                    @if ($item->descripcion)
                                        <small>{{ \Illuminate\Support\Str::limit($item->descripcion, 95) }}</small>
                                    @endif
                                </div>

                                <div class="product-list-meta">
                                    <span>
                                        {{ $item->stock_total !== null ? $item->stock_total.' disponibles' : 'Sin límite de existencias' }}
                                    </span>
                                    <b>Ver</b>
                                </div>
                            </summary>

                            <div class="product-list-expanded">
                                <div class="product-basic-grid">
                                    <div>
                                        <span>Mínimo por inscripción</span>
                                        <strong>{{ $item->min_por_inscripcion ?? 1 }}</strong>
                                    </div>

                                    <div>
                                        <span>Máximo por inscripción</span>
                                        <strong>{{ $item->max_por_inscripcion ?? 'Sin límite' }}</strong>
                                    </div>

                                    <div>
                                        <span>Participante</span>
                                        <strong>
                                            {{ $item->requiere_participante ? 'Se solicita un participante' : 'No se solicita participante' }}
                                        </strong>
                                    </div>
                                </div>

                                @if ($item->descripcion)
                                    <p class="product-list-description">{{ $item->descripcion }}</p>
                                @endif

                                @include('admin.actividades.revision._observacion_inline', [
                                    'seccion' => 'productos',
                                    'titulo' => 'Observaciones de '.$item->nombre,
                                    'referenciaTipo' => 'item',
                                    'referenciaId' => $item->id_item_actividad,
                                    'referenciaNombre' => $item->nombre,
                                    'observaciones' => $observacionesReferencia(
                                        'productos',
                                        'item',
                                        $item->id_item_actividad
                                    ),
                                ])
                            </div>
                        </details>
                    @endforeach
                </div>
            @endif

            @include('admin.actividades.revision._observacion_inline', [
                'seccion' => 'productos',
                'titulo' => 'Observación general de productos / servicios',
                'observaciones' => $observacionesReferencia('productos'),
            ])

            <div class="stage-footer">
                <button type="button" class="btn-secondary" data-prev-stage>
                    ← Anterior
                </button>

                <button type="button" class="btn-primary" data-next-stage>
                    Siguiente →
                </button>
            </div>
        </section>

        {{-- ETAPA 4 --}}
        <section class="review-stage {{ $etapaTieneCambios('datos') ? 'stage-has-changes' : '' }}" data-stage="datos">
            <div class="section-heading">
                <div>
                    <h2>Datos solicitados</h2>
                    <p>
                        Aquí ves exactamente qué tendrá que responder, escribir o elegir una persona
                        cuando compre o se inscriba.
                    </p>
                </div>
            </div>

            <div class="requested-data-overview">
                <div class="overview-icon">i</div>
                <div>
                    <strong>Qué significa esta sección</strong>
                    <p>
                        Estos datos aparecen durante la compra o inscripción. Las opciones de productos,
                        como talla o color, también se muestran aquí cuando fueron configuradas como una
                        elección que debe hacer el comprador.
                    </p>
                </div>
                <span class="overview-count">
                    {{ $totalDatosCompra }}
                    {{ $totalDatosCompra === 1 ? 'dato configurado' : 'datos configurados' }}
                </span>
            </div>

            @if ($actividad->items->isNotEmpty())
                <div class="stack">
                    @foreach ($actividad->items->sortBy('orden') as $item)
                        @php
                            $configDatosItem = $configuracionesDatosRevision->get(
                                (string) $item->id_item_actividad
                            );
                            $datosHabilitados = (bool) ($configDatosItem['enabled'] ?? false);
                            $camposItem = collect($configDatosItem['fields'] ?? [])->values();
                        @endphp

                        <article class="requested-item">
                            <div class="requested-item-header">
                                <div>
                                    <span class="item-type">{{ ucfirst($item->tipo ?? 'producto') }}</span>
                                    <h3>{{ $item->nombre }}</h3>
                                    <p>
                                        @if (!$configDatosItem)
                                            No se encontró una configuración de datos para este producto o servicio.
                                        @elseif (!$datosHabilitados)
                                            La persona no tendrá que proporcionar datos adicionales para este producto o servicio.
                                        @else
                                            Se solicitarán {{ $camposItem->count() }}
                                            {{ $camposItem->count() === 1 ? 'dato' : 'datos' }} al completar la compra.
                                        @endif
                                    </p>
                                </div>

                                @if ($datosHabilitados)
                                    <span class="friendly-status success">
                                        {{ $camposItem->count() }}
                                        {{ $camposItem->count() === 1 ? 'dato' : 'datos' }}
                                    </span>
                                @else
                                    <span class="friendly-status neutral">Sin datos adicionales</span>
                                @endif
                            </div>

                            @if ($datosHabilitados && $camposItem->isNotEmpty())
                                <div class="requested-fields">
                                    @foreach ($camposItem as $campo)
                                        @php
                                            $opcionesCampo = collect($campo['options'] ?? [])
                                                ->filter(fn ($opcion) => $opcion !== null && $opcion !== '')
                                                ->values();
                                        @endphp

                                        <div class="requested-field">
                                            <div class="requested-field-main">
                                                <div class="field-title-row">
                                                    <strong>{{ $campo['label'] ?? 'Dato sin nombre' }}</strong>
                                                    <span class="field-badge {{ ($campo['required'] ?? false) ? 'required' : 'optional' }}">
                                                        {{ ($campo['required'] ?? false) ? 'Obligatorio' : 'Opcional' }}
                                                    </span>
                                                </div>

                                                <p>{{ $tipoDatoHumano($campo['type'] ?? null) }}</p>

                                                @if (($campo['type'] ?? null) === 'lista' && $opcionesCampo->isNotEmpty())
                                                    <div class="field-options">
                                                        @foreach ($opcionesCampo as $opcion)
                                                            <span>{{ $opcion }}</span>
                                                        @endforeach
                                                    </div>
                                                @elseif (($campo['type'] ?? null) === 'texto')
                                                    <div class="field-example">La persona escribirá su respuesta.</div>
                                                @elseif (($campo['type'] ?? null) === 'numero')
                                                    <div class="field-example">La persona ingresará un valor numérico.</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @include('admin.actividades.revision._observacion_inline', [
                                'seccion' => 'datos_solicitados',
                                'titulo' => 'Observaciones de '.$item->nombre,
                                'referenciaTipo' => 'item',
                                'referenciaId' => $item->id_item_actividad,
                                'referenciaNombre' => $item->nombre,
                                'observaciones' => $observacionesReferencia(
                                    'datos_solicitados',
                                    'item',
                                    $item->id_item_actividad
                                ),
                            ])
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($actividad->formularios->isNotEmpty())
                <div class="content-card forms-section">
                    <div class="card-intro">
                        <div>
                            <h3>Formularios de inscripción</h3>
                            <p>
                                Además de los datos de cada producto o servicio, estos formularios se utilizarán
                                para recopilar información durante la inscripción.
                            </p>
                        </div>
                    </div>

                    <div class="stack compact-stack">
                        @foreach ($actividad->formularios as $formulario)
                            <article class="form-card embedded-card">
                                <div class="form-header">
                                    <div>
                                        <h3>{{ $formulario->nombre }}</h3>
                                        <p>
                                            {{ $formulario->campos->count() }}
                                            {{ $formulario->campos->count() === 1 ? 'dato solicitado' : 'datos solicitados' }}
                                        </p>
                                    </div>

                                    <span class="pill">{{ ucfirst($formulario->estado) }}</span>
                                </div>

                                @if ($formulario->campos->isNotEmpty())
                                    <div class="requested-fields form-fields">
                                        @foreach ($formulario->campos->sortBy('orden') as $campo)
                                            <div class="requested-field">
                                                <div class="requested-field-main">
                                                    <div class="field-title-row">
                                                        <strong>{{ $campo->nombre }}</strong>
                                                        <span class="field-badge {{ $campo->obligatorio ? 'required' : 'optional' }}">
                                                            {{ $campo->obligatorio ? 'Obligatorio' : 'Opcional' }}
                                                        </span>
                                                    </div>
                                                    <p>
                                                        {{ ucfirst(str_replace('_', ' ', $campo->tipo_dato)) }}
                                                        · se solicita en {{ str_replace('_', ' ', $campo->aplica_a) }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @include('admin.actividades.revision._observacion_inline', [
                                    'seccion' => 'datos_solicitados',
                                    'titulo' => 'Observaciones de '.$formulario->nombre,
                                    'referenciaTipo' => 'formulario',
                                    'referenciaId' => $formulario->id_formulario,
                                    'referenciaNombre' => $formulario->nombre,
                                    'observaciones' => $observacionesReferencia(
                                        'datos_solicitados',
                                        'formulario',
                                        $formulario->id_formulario
                                    ),
                                ])
                            </article>
                        @endforeach
                    </div>
                </div>
            @elseif ($actividad->items->isEmpty() || $totalDatosCompra === 0)
                <div class="empty-state compact-empty">
                    No se configuraron formularios ni datos adicionales para solicitar a las personas.
                </div>
            @endif

            @include('admin.actividades.revision._observacion_inline', [
                'seccion' => 'datos_solicitados',
                'titulo' => 'Observación general sobre los datos solicitados',
                'observaciones' => $observacionesReferencia('datos_solicitados'),
            ])

            <div class="stage-footer">
                <button type="button" class="btn-secondary" data-prev-stage>
                    ← Anterior
                </button>

                <button type="button" class="btn-primary" data-next-stage>
                    Siguiente →
                </button>
            </div>
        </section>


        {{-- ETAPA 5 --}}
        <section class="review-stage {{ $etapaTieneCambios('precios') ? 'stage-has-changes' : '' }}" data-stage="precios">
            <div class="section-heading">
                <div>
                    <h2>Precios / Costos</h2>
                    <p>
                        Revisa cada producto por separado. Al abrirlo podrás comprobar su precio base,
                        las opciones disponibles y cuánto cambia el precio o el costo según lo que elija la persona.
                    </p>
                </div>
            </div>

            @if ($actividad->items->isEmpty())
                <div class="empty-state">
                    No hay productos o servicios con precios que revisar.
                </div>
            @else
                <div class="price-products">
                    @foreach ($actividad->items->sortBy('orden') as $item)
                        @php
                            $configProducto = $buscarConfiguracionProducto($item);
                            $configPrecio = $configuracionesPrecios->get(
                                (string) $item->id_item_actividad,
                                []
                            );
                            $configDatosItem = $configuracionesDatosRevision->get(
                                (string) $item->id_item_actividad,
                                []
                            );

                            $reglas = is_array($configPrecio)
                                ? $extraerReglas($configPrecio)
                                : [];

                            if (!$reglas && is_array($configProducto)) {
                                $reglas = $extraerReglas($configProducto);
                            }

                            $atributos = $extraerAtributos($configProducto);

                            if (!$atributos && is_array($configPrecio)) {
                                $atributos = $extraerAtributos($configPrecio);
                            }

                            if (
                                !$atributos
                                && is_array($configDatosItem)
                                && ($configDatosItem['enabled'] ?? false)
                            ) {
                                foreach ($configDatosItem['fields'] ?? [] as $campoCompra) {
                                    if (
                                        ($campoCompra['type'] ?? null) !== 'lista'
                                        || empty($campoCompra['options'] ?? [])
                                    ) {
                                        continue;
                                    }

                                    $opciones = [];

                                    foreach ($campoCompra['options'] as $opcionCompra) {
                                        $reglaOpcion = collect($reglas)->first(function ($regla) use ($campoCompra, $opcionCompra) {
                                            return is_array($regla)
                                                && ($regla['field_key'] ?? null) === ($campoCompra['key'] ?? null)
                                                && (string) ($regla['option'] ?? '') === (string) $opcionCompra;
                                        });

                                        $opciones[] = [
                                            'valor' => (string) $opcionCompra,
                                            'precio' => (float) ($reglaOpcion['price_increment'] ?? 0),
                                            'costo' => (float) ($reglaOpcion['cost_increment'] ?? 0),
                                        ];
                                    }

                                    $atributos[] = [
                                        'clave' => (string) ($campoCompra['key'] ?? $campoCompra['label'] ?? 'opcion'),
                                        'nombre' => $campoCompra['label']
                                            ?? ucfirst(str_replace('_', ' ', $campoCompra['key'] ?? 'Opción')),
                                        'opciones' => $opciones,
                                    ];
                                }
                            }

                            $atributos = $aplicarReglasAtributos($atributos, $reglas);
                        @endphp

                        <details class="price-product-detail">
                            <summary>
                                <div class="price-product-main">
                                    <span class="item-type">{{ ucfirst($item->tipo ?? 'servicio') }}</span>
                                    <strong>{{ $item->nombre }}</strong>

                                    <small>
                                        @if ($atributos)
                                            {{ count($atributos) }}
                                            {{ count($atributos) === 1 ? 'grupo de opciones' : 'grupos de opciones' }}
                                        @else
                                            Sin opciones que cambien el precio
                                        @endif
                                    </small>
                                </div>

                                <div class="price-product-summary-values">
                                    <span>
                                        Precio base
                                        <b>${{ number_format((float) $item->precio, 2) }}</b>
                                    </span>

                                    <span>
                                        Costo base
                                        <b>${{ number_format((float) $item->costo_referencia, 2) }}</b>
                                    </span>

                                    <em>Revisar</em>
                                </div>
                            </summary>

                            <div class="price-product-expanded">
                                <div class="product-basic-grid price-basic-grid">
                                    <div>
                                        <span>Precio inicial</span>
                                        <strong>${{ number_format((float) $item->precio, 2) }}</strong>
                                    </div>

                                    <div>
                                        <span>Costo inicial</span>
                                        <strong>${{ number_format((float) $item->costo_referencia, 2) }}</strong>
                                    </div>

                                    <div>
                                        <span>Existencias</span>
                                        <strong>{{ $item->stock_total !== null ? $item->stock_total : 'Sin límite' }}</strong>
                                    </div>
                                </div>

                                @if ($atributos)
                                    <div class="variant-summary pricing-variant-summary">
                                        <div class="compact-section-heading">
                                            <div>
                                                <h4>Opciones que puede elegir la persona</h4>
                                                <p>
                                                    Abre solo el grupo que quieras revisar.
                                                    Cada opción muestra cuánto suma o resta al precio y al costo base.
                                                </p>
                                            </div>

                                            <span>
                                                {{ count($atributos) }}
                                                {{ count($atributos) === 1 ? 'grupo' : 'grupos' }}
                                            </span>
                                        </div>

                                        <div class="variant-groups">
                                            @foreach ($atributos as $atributo)
                                                <details class="variant-group">
                                                    <summary>
                                                        <span class="variant-group-title">
                                                            <strong>{{ $atributo['nombre'] }}</strong>
                                                            <small>
                                                                {{ count($atributo['opciones']) }}
                                                                {{ count($atributo['opciones']) === 1 ? 'opción' : 'opciones' }}
                                                            </small>
                                                        </span>

                                                        <span class="variant-group-action">Ver opciones</span>
                                                    </summary>

                                                    <div
                                                        class="option-compact-list"
                                                        data-chunk-list
                                                        data-chunk-size="12"
                                                    >
                                                        @foreach ($atributo['opciones'] as $opcion)
                                                            <div
                                                                class="option-compact-row {{ $loop->index >= 12 ? 'chunk-hidden' : '' }}"
                                                                data-chunk-item
                                                            >
                                                                <strong>{{ $opcion['valor'] }}</strong>

                                                                <div class="option-adjustments">
                                                                    @if ($opcion['precio'] != 0)
                                                                        <span>
                                                                            Precio
                                                                            <b>
                                                                                {{ $opcion['precio'] > 0 ? '+' : '' }}${{ number_format($opcion['precio'], 2) }}
                                                                            </b>
                                                                        </span>
                                                                    @endif

                                                                    @if ($opcion['costo'] != 0)
                                                                        <span>
                                                                            Costo
                                                                            <b>
                                                                                {{ $opcion['costo'] > 0 ? '+' : '' }}${{ number_format($opcion['costo'], 2) }}
                                                                            </b>
                                                                        </span>
                                                                    @endif

                                                                    @if ($opcion['precio'] == 0 && $opcion['costo'] == 0)
                                                                        <span class="no-adjustment">No cambia el valor base</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach

                                                        @if (count($atributo['opciones']) > 12)
                                                            <button
                                                                type="button"
                                                                class="show-more-options"
                                                                data-show-more
                                                            >
                                                                Mostrar más opciones
                                                            </button>
                                                        @endif
                                                    </div>
                                                </details>
                                            @endforeach
                                        </div>

                                        <div
                                            class="variant-simulator"
                                            data-variant-simulator
                                            data-base-price="{{ (float) $item->precio }}"
                                            data-base-cost="{{ (float) $item->costo_referencia }}"
                                        >
                                            <div class="simulator-heading">
                                                <div>
                                                    <h4>Comprobar precio final</h4>
                                                    <p>
                                                        Elige una opción de cada grupo para ver el precio y costo resultantes.
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="simulator-compact-row">
                                                <div class="simulator-controls">
                                                    @foreach ($atributos as $atributo)
                                                        <label>
                                                            <span>{{ $atributo['nombre'] }}</span>

                                                            <select data-variant-option>
                                                                @foreach ($atributo['opciones'] as $opcion)
                                                                    <option
                                                                        value="{{ $opcion['valor'] }}"
                                                                        data-price-delta="{{ $opcion['precio'] }}"
                                                                        data-cost-delta="{{ $opcion['costo'] }}"
                                                                    >
                                                                        {{ $opcion['valor'] }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </label>
                                                    @endforeach
                                                </div>

                                                <div class="simulator-result compact-result">
                                                    <div>
                                                        <span>Precio final</span>
                                                        <strong data-simulator-price>
                                                            ${{ number_format((float) $item->precio, 2) }}
                                                        </strong>
                                                    </div>

                                                    <div>
                                                        <span>Costo final</span>
                                                        <strong data-simulator-cost>
                                                            ${{ number_format((float) $item->costo_referencia, 2) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($item->variantes->isNotEmpty())
                                    <details class="variant-group compact-notice-details">
                                        <summary>
                                            <span class="variant-group-title">
                                                <strong>Variantes registradas</strong>
                                                <small>{{ $item->variantes->count() }} combinaciones</small>
                                            </span>

                                            <span class="variant-group-action">Ver información</span>
                                        </summary>

                                        <div class="compact-notice">
                                            Este producto tiene variantes registradas, pero no hay reglas dinámicas disponibles para calcular cambios adicionales desde esta pantalla.
                                        </div>
                                    </details>
                                @else
                                    <div class="compact-notice neutral-notice">
                                        Este producto usa únicamente su precio y costo base.
                                    </div>
                                @endif

                                @include('admin.actividades.revision._observacion_inline', [
                                    'seccion' => 'precios_costos',
                                    'titulo' => 'Observaciones de precio y costo de '.$item->nombre,
                                    'referenciaTipo' => 'item',
                                    'referenciaId' => $item->id_item_actividad,
                                    'referenciaNombre' => $item->nombre,
                                    'observaciones' => $observacionesReferencia(
                                        'precios_costos',
                                        'item',
                                        $item->id_item_actividad
                                    ),
                                ])
                            </div>
                        </details>
                    @endforeach
                </div>
            @endif

            <div class="content-card commercial-card promotions-section">
                <div class="card-intro">
                    <div>
                        <h3>Promociones</h3>
                        <p>Descuentos adicionales que pueden aplicarse al precio final.</p>
                    </div>

                    <span class="friendly-status {{ $actividad->promociones->isNotEmpty() ? 'success' : 'neutral' }}">
                        {{ $actividad->promociones->count() }}
                        {{ $actividad->promociones->count() === 1 ? 'promoción' : 'promociones' }}
                    </span>
                </div>

                @if ($actividad->promociones->isEmpty())
                    <div class="empty-state compact-empty">
                        No hay promociones configuradas.
                    </div>
                @else
                    <div class="promotion-grid">
                        @foreach ($actividad->promociones as $promocion)
                            <article class="promotion-card">
                                <div>
                                    <strong>{{ $promocion->nombre }}</strong>

                                    @if ($promocion->codigo)
                                        <span class="promo-code">Código: {{ $promocion->codigo }}</span>
                                    @endif
                                </div>

                                <strong class="promo-value">
                                    @if ($promocion->tipo_descuento === 'porcentaje')
                                        {{ number_format((float) $promocion->valor, 2) }}% de descuento
                                    @else
                                        ${{ number_format((float) $promocion->valor, 2) }} de descuento
                                    @endif
                                </strong>

                                <span class="{{ $promocion->activo ? 'status-ok' : 'muted' }}">
                                    {{ $promocion->activo ? 'Disponible' : 'No disponible' }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            @include('admin.actividades.revision._observacion_inline', [
                'seccion' => 'precios_costos',
                'titulo' => 'Observación general de precios, costos y promociones',
                'observaciones' => $observacionesReferencia('precios_costos'),
            ])

            <div class="stage-footer">
                <button type="button" class="btn-secondary" data-prev-stage>
                    ← Anterior
                </button>

                <button type="button" class="btn-primary" data-next-stage>
                    Siguiente →
                </button>
            </div>
        </section>

        {{-- ETAPA 6 --}}
        <section class="review-stage {{ $etapaTieneCambios('programacion') ? 'stage-has-changes' : '' }}" data-stage="programacion">
            <div class="section-heading">
                <div>
                    <h2>Programación e inscripción</h2>
                    <p>Cuándo ocurre la actividad, dónde será, qué recursos utiliza y cómo funciona la inscripción.</p>
                </div>
            </div>

            <div class="content-card programming-block sessions-block">
                <div class="card-intro sessions-intro">
                    <div>
                        <h3>Sesiones</h3>
                        <p>
                            Se muestran en grupos pequeños para que puedas revisar muchas sesiones sin convertir la página en una lista interminable.
                        </p>
                    </div>
                    <span class="friendly-status {{ $actividad->sesiones->isNotEmpty() ? 'success' : 'neutral' }}">
                        {{ $actividad->sesiones->count() }}
                        {{ $actividad->sesiones->count() === 1 ? 'sesión' : 'sesiones' }}
                    </span>
                </div>

                @if ($actividad->sesiones->isEmpty())
                    <div class="empty-state compact-empty">No hay sesiones programadas.</div>
                @else
                    <div class="session-list compact-session-list" data-session-list data-page-size="8">
                        @foreach ($actividad->sesiones->sortBy('orden')->values() as $sesion)
                            <details
                                class="session-compact {{ $loop->index >= 8 ? 'session-page-hidden' : '' }}"
                                data-session-row
                            >
                                <summary>
                                    <div class="session-summary-main">
                                        <strong>{{ $sesion->nombre }}</strong>
                                        <span>
                                            {{ $formatoFecha($sesion->fecha_inicio) }}
                                            @if ($sesion->fecha_fin)
                                                — {{ $formatoFecha($sesion->fecha_fin) }}
                                            @endif
                                        </span>
                                    </div>

                                    <div class="session-summary-meta">
                                        <span>
                                            @if ($sesion->espacio)
                                                {{ $sesion->espacio->nombre }}
                                            @else
                                                {{ $sesion->ubicacion ?: 'Ubicación no definida' }}
                                            @endif
                                        </span>
                                        <span>{{ $sesion->cupo ?? 'Sin límite' }} cupos</span>
                                        <b>{{ ucfirst($sesion->estado ?? 'programada') }}</b>
                                    </div>
                                </summary>

                                <div class="session-expanded">
                                    <div class="session-detail-grid">
                                        <div>
                                            <span>Ubicación</span>
                                            <strong>
                                                @if ($sesion->espacio)
                                                    {{ $sesion->espacio->nombre }}
                                                    @if ($sesion->espacio->contenedor)
                                                        · {{ $sesion->espacio->contenedor->nombre }}
                                                    @endif
                                                @else
                                                    {{ $sesion->ubicacion ?: 'No definida' }}
                                                @endif
                                            </strong>
                                        </div>
                                        <div>
                                            <span>Reserva</span>
                                            <strong>{{ $sesion->requiere_reserva ? 'Requerida' : 'No requerida' }}</strong>
                                        </div>
                                        <div>
                                            <span>Asistencia</span>
                                            <strong>{{ $sesion->obligatoria ? 'Obligatoria' : 'Opcional' }}</strong>
                                        </div>
                                        <div>
                                            <span>Cupo</span>
                                            <strong>{{ $sesion->cupo ?? 'Sin límite' }}</strong>
                                        </div>
                                    </div>

                                    @if ($sesion->recursos->isNotEmpty())
                                        <div class="resource-list compact-resource-list">
                                            <strong>Recursos</strong>
                                            <div class="tag-list">
                                                @foreach ($sesion->recursos as $asignacion)
                                                    <span class="tag">
                                                        {{ $asignacion->recurso?->nombre ?? $asignacion->nombre ?? 'Recurso' }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @include('admin.actividades.revision._observacion_inline', [
                                        'seccion' => 'programacion',
                                        'titulo' => 'Observaciones de '.$sesion->nombre,
                                        'referenciaTipo' => 'sesion',
                                        'referenciaId' => $sesion->id_sesion,
                                        'referenciaNombre' => $sesion->nombre,
                                        'observaciones' => $observacionesReferencia(
                                            'programacion',
                                            'sesion',
                                            $sesion->id_sesion
                                        ),
                                    ])
                                </div>
                            </details>
                        @endforeach
                    </div>

                    @if ($actividad->sesiones->count() > 8)
                        <div class="session-pager" data-session-pager>
                            <button type="button" class="btn-secondary compact-button" data-session-prev disabled>
                                ← Anteriores
                            </button>
                            <span data-session-page-info>Página 1</span>
                            <button type="button" class="btn-secondary compact-button" data-session-next>
                                Siguientes →
                            </button>
                        </div>
                    @endif
                @endif
            </div>

            @if ($recursosGenerales->isNotEmpty())
                <div class="content-card programming-block">
                    <h3>Recursos generales</h3>

                    <div class="resource-grid">
                        @foreach ($recursosGenerales as $asignacion)
                            <article class="resource-card">
                                <strong>
                                    {{ $asignacion->recurso?->nombre ?? $asignacion->nombre ?? 'Recurso' }}
                                </strong>

                                @if ($asignacion->recurso?->tipo ?? $asignacion->tipo ?? null)
                                    <span>
                                        {{ ucfirst($asignacion->recurso?->tipo ?? $asignacion->tipo) }}
                                    </span>
                                @endif

                                @if ($asignacion->observacion ?? null)
                                    <small>{{ $asignacion->observacion }}</small>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            @include('admin.actividades.revision._observacion_inline', [
                'seccion' => 'programacion',
                'titulo' => 'Observación general de programación',
                'observaciones' => $observacionesReferencia('programacion'),
            ])

            <div class="content-card programming-block">
                <h3>Participación e inscripción</h3>

                @php
                    $tipoParticipacionRevision = match ($actividad->tipo_participacion) {
                        'registro_gratuito' => 'Inscripción gratuita',
                        'registro_pago' => 'Inscripción con pago',
                        'venta_directa' => 'Venta directa',
                        default => 'Acceso libre / informativa',
                    };
                @endphp

                <div class="info-grid">
                    <article class="info-card">
                        <span class="info-label">Modalidad</span>
                        <strong>{{ $tipoParticipacionRevision }}</strong>
                    </article>

                    <article class="info-card">
                        <span class="info-label">Precio inscripción</span>
                        <strong>
                            @if ($actividad->tipo_participacion === 'registro_pago')
                                ${{ number_format((float) $actividad->precio_inscripcion, 2) }}
                            @elseif ($actividad->tipo_participacion === 'registro_gratuito')
                                Gratis
                            @else
                                No aplica
                            @endif
                        </strong>
                    </article>

                    <article class="info-card">
                        <span class="info-label">Requiere cuenta</span>
                        <strong>{{ $actividad->requiere_cuenta ? 'Sí' : 'No' }}</strong>
                    </article>

                    <article class="info-card">
                        <span class="info-label">Lista de espera</span>
                        <strong>{{ $actividad->permite_lista_espera ? 'Sí' : 'No' }}</strong>
                    </article>

                    <article class="info-card">
                        <span class="info-label">Cupo total</span>
                        <strong>{{ $actividad->cupo_total ?? 'Sin límite' }}</strong>
                    </article>

                    <article class="info-card">
                        <span class="info-label">Inscripción desde</span>
                        <strong>{{ $formatoFecha($actividad->inscripcion_desde) }}</strong>
                    </article>

                    <article class="info-card">
                        <span class="info-label">Inscripción hasta</span>
                        <strong>{{ $formatoFecha($actividad->inscripcion_hasta) }}</strong>
                    </article>
                </div>
            </div>

            @include('admin.actividades.revision._observacion_inline', [
                'seccion' => 'inscripcion',
                'titulo' => 'Observaciones de inscripción',
                'observaciones' => $observacionesReferencia('inscripcion'),
            ])

            <div class="stage-footer">
                <button type="button" class="btn-secondary" data-prev-stage>
                    ← Anterior
                </button>

                <button type="button" class="btn-primary" data-next-stage>
                    Ir a decisión →
                </button>
            </div>
        </section>

        {{-- ETAPA 7 --}}
        <section class="review-stage" data-stage="decision">
            <div class="section-heading">
                <div>
                    <h2>Resumen y decisión</h2>
                    <p>
                        Revisa las observaciones registradas antes de tomar
                        una decisión.
                    </p>
                </div>
            </div>

            <div class="decision-layout">
                <div>
                    <div class="content-card">
                        <div class="summary-heading">
                            <div>
                                <h3>Observaciones pendientes</h3>
                                <p>
                                    Aquí se mantienen las observaciones que aún deben
                                    comprobarse, aunque provengan de revisiones anteriores.
                                </p>
                            </div>

                            <span class="summary-total">
                                <strong data-summary-total>{{ $totalObservaciones }}</strong>
                                observaciones
                            </span>
                        </div>

                        <div id="final-observations-summary">
                            @if ($observacionesActuales->isEmpty())
                                <div class="empty-observations" data-empty-summary>
                                    No hay observaciones pendientes.
                                </div>
                            @else
                                @foreach ($nombresSecciones as $clave => $nombre)
                                    @php
                                        $grupo = $observacionesSeccion($clave);
                                    @endphp

                                    @if ($grupo->isNotEmpty())
                                        <div
                                            class="summary-section"
                                            data-summary-section="{{ $clave }}"
                                        >
                                            <h4>
                                                {{ $nombre }}
                                                <span data-summary-section-count="{{ $clave }}">
                                                    {{ $grupo->count() }}
                                                </span>
                                            </h4>

                                            <div data-summary-list="{{ $clave }}">
                                                @foreach ($grupo as $observacion)
                                                    <div
                                                        class="summary-observation"
                                                        data-summary-observation="{{ $observacion->id_observacion }}"
                                                    >
                                                        @if ($nombreReferencia($observacion))
                                                            <strong>
                                                                {{ $nombreReferencia($observacion) }}
                                                            </strong>
                                                        @endif

                                                        @if (
                                                            (int) ($observacion->revision?->numero_revision ?? $revisionActual->numero_revision)
                                                            !== (int) $revisionActual->numero_revision
                                                        )
                                                            <span class="summary-revision-badge">
                                                                Pendiente desde revisión #{{ $observacion->revision?->numero_revision }}
                                                            </span>
                                                        @endif

                                                        <p>{{ $observacion->observacion }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>

                </div>

                <aside class="decision-panel">
                    <div class="decision-status">
                        <span>Revisión actual</span>
                        <strong>#{{ $actividad->revision_actual }}</strong>

                        <span>
                            <strong id="decision-observation-count">
                                {{ $totalObservaciones }}
                            </strong>
                            observaciones específicas
                        </span>
                    </div>

                    <form id="decision-form">
                        <label for="general-observation">
                            Observación general
                        </label>

                        <textarea
                            id="general-observation"
                            name="observacion"
                            rows="6"
                            maxlength="2000"
                            placeholder="Comentario general de la revisión. Puede utilizarse también al aprobar."
                        ></textarea>

                        <p class="field-help">
                            Al aprobar es opcional. Para solicitar cambios o
                            rechazar debe existir esta observación o al menos
                            una observación específica.
                        </p>

                        <div class="decision-actions">
                            <button
                                type="button"
                                id="approve-button"
                                class="decision-button approve"
                                data-decision="approve"
                                {{ $totalObservaciones > 0 ? 'disabled' : '' }}
                            >
                                <span>✓</span>
                                <div>
                                    <strong>Aprobar</strong>
                                    <small data-approve-help>
                                        @if ($totalObservaciones > 0)
                                            Resuelve las observaciones pendientes para aprobar.
                                        @else
                                            La actividad puede aprobarse.
                                        @endif
                                    </small>
                                </div>
                            </button>

                            <button
                                type="button"
                                class="decision-button changes"
                                data-decision="changes"
                            >
                                <span>↻</span>
                                <div>
                                    <strong>Solicitar cambios</strong>
                                    <small>Devuelve la actividad al creador.</small>
                                </div>
                            </button>

                            <button
                                type="button"
                                class="decision-button reject"
                                data-decision="reject"
                            >
                                <span>✕</span>
                                <div>
                                    <strong>Rechazar</strong>
                                    <small>Finaliza la revisión como rechazada.</small>
                                </div>
                            </button>
                        </div>
                    </form>
                </aside>
            </div>

            <details class="history-shell">
                <summary class="history-shell-summary">
                    <div class="history-shell-title">
                        <span class="history-shell-icon" aria-hidden="true">↺</span>
                        <div>
                            <strong>Historial de revisiones</strong>
                            <small>
                                {{ $historial->count() }}
                                {{ $historial->count() === 1 ? 'movimiento registrado' : 'movimientos registrados' }}
                            </small>
                        </div>
                    </div>

                    <span class="history-shell-toggle">
                        <span class="history-shell-open-text">Ver historial</span>
                        <span class="history-shell-close-text">Ocultar historial</span>
                        <span class="history-chevron" aria-hidden="true">⌄</span>
                    </span>
                </summary>

                <div class="history-shell-body">
                    @forelse ($historial as $revision)
                        @php
                            $observacionesRevision = $revision->observaciones ?? collect();
                            $cantidadObservacionesRevision = $observacionesRevision->count();
                            $tonoRevision = $tonoAccionRevision($revision->accion);
                        @endphp

                        <details class="history-entry">
                            <summary class="history-entry-summary">
                                <div class="history-entry-main">
                                    <span class="history-entry-dot history-entry-dot--{{ $tonoRevision }}"></span>

                                    <div class="history-entry-copy">
                                        <div class="history-entry-title-row">
                                            <strong>{{ $etiquetaAccionRevision($revision->accion) }}</strong>
                                            <span class="history-revision-badge">
                                                Revisión #{{ $revision->numero_revision }}
                                            </span>
                                        </div>

                                        <span class="history-entry-meta">
                                            {{ $formatoFecha($revision->creado_en) }}
                                            · {{ $nombreUsuario($revision->usuario) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="history-entry-side">
                                    @if ($cantidadObservacionesRevision > 0)
                                        <span class="history-observation-count">
                                            {{ $cantidadObservacionesRevision }}
                                            {{ $cantidadObservacionesRevision === 1 ? 'observación' : 'observaciones' }}
                                        </span>
                                    @endif

                                    <span class="history-detail-toggle">
                                        <span>Ver detalles</span>
                                        <span class="history-chevron" aria-hidden="true">⌄</span>
                                    </span>
                                </div>
                            </summary>

                            <div class="history-entry-details">
                                <div class="history-detail-grid">
                                    <div>
                                        <span>Acción</span>
                                        <strong>{{ $etiquetaAccionRevision($revision->accion) }}</strong>
                                    </div>

                                    <div>
                                        <span>Revisión</span>
                                        <strong>#{{ $revision->numero_revision }}</strong>
                                    </div>

                                    <div>
                                        <span>Usuario</span>
                                        <strong>{{ $nombreUsuario($revision->usuario) }}</strong>
                                    </div>

                                    <div>
                                        <span>Fecha y hora</span>
                                        <strong>{{ $formatoFecha($revision->creado_en) }}</strong>
                                    </div>
                                </div>

                                @if ($revision->observacion)
                                    <div class="history-general-note">
                                        <span>Comentario general</span>
                                        <p>{{ $revision->observacion }}</p>
                                    </div>
                                @endif

                                @if ($observacionesRevision->isNotEmpty())
                                    <div class="history-specific-observations">
                                        <div class="history-specific-heading">
                                            <strong>Observaciones específicas de esta revisión</strong>
                                            <span>{{ $cantidadObservacionesRevision }}</span>
                                        </div>

                                        <div class="history-specific-list">
                                            @foreach ($observacionesRevision as $observacionHistorial)
                                                <article class="history-specific-item">
                                                    <div class="history-specific-topline">
                                                        <div>
                                                            <strong>
                                                                {{ $nombresSecciones[$observacionHistorial->seccion] ?? ucfirst(str_replace('_', ' ', $observacionHistorial->seccion)) }}
                                                            </strong>

                                                            @if ($nombreReferencia($observacionHistorial))
                                                                <span>{{ $nombreReferencia($observacionHistorial) }}</span>
                                                            @endif
                                                        </div>

                                                        <span class="history-resolution-badge {{ $observacionHistorial->resuelta ? 'resolved' : 'pending' }}">
                                                            {{ $observacionHistorial->resuelta ? 'Resuelta' : 'Pendiente' }}
                                                        </span>
                                                    </div>

                                                    <p>{{ $observacionHistorial->observacion }}</p>

                                                    @if ($observacionHistorial->resuelta && $observacionHistorial->resuelta_en)
                                                        <small>
                                                            Marcada como resuelta el {{ $formatoFecha($observacionHistorial->resuelta_en) }}.
                                                        </small>
                                                    @endif
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif (!$revision->observacion)
                                    <div class="history-empty-detail">
                                        Este movimiento no tiene comentarios adicionales.
                                    </div>
                                @endif
                            </div>
                        </details>
                    @empty
                        <div class="history-empty-detail">
                            Todavía no hay movimientos registrados para esta actividad.
                        </div>
                    @endforelse
                </div>
            </details>

            <div class="stage-footer">
                <button type="button" class="btn-secondary" data-prev-stage>
                    ← Anterior
                </button>
                <span></span>
            </div>
        </section>
    </main>

    {{-- Modal de decisiones: reemplaza window.confirm sin modificar la lógica del controlador. --}}
    <dialog
        id="revision-decision-dialog"
        class="review-decision-dialog"
        aria-labelledby="review-decision-title"
        aria-describedby="review-decision-description"
        aria-modal="true"
    >
        <div class="review-decision-dialog__header">
            <div class="review-decision-dialog__icon" data-review-dialog-icon aria-hidden="true">✓</div>
            <button type="button" class="review-decision-dialog__close" data-review-dialog-dismiss aria-label="Cerrar confirmación" title="Cerrar">
                &times;
            </button>
        </div>
        <div class="review-decision-dialog__body">
            <p class="review-decision-dialog__eyebrow">Confirmar decisión</p>
            <h2 id="review-decision-title">Confirmar decisión</h2>
            <p id="review-decision-description" class="review-decision-dialog__description"></p>
            <div class="review-decision-dialog__summary">
                <span>Actividad</span>
                <strong>{{ $actividad->nombre }}</strong>
                <small data-review-dialog-summary></small>
            </div>
            <p class="review-decision-dialog__notice">Esta decisión quedará registrada en el historial de revisiones.</p>
            <p class="review-decision-dialog__error" data-review-dialog-error role="alert" hidden></p>
        </div>
        <div class="review-decision-dialog__actions">
            <button type="button" class="review-decision-dialog__cancel" data-review-dialog-dismiss>Cancelar</button>
            <button type="button" class="review-decision-dialog__confirm" data-review-dialog-confirm>Confirmar</button>
        </div>
    </dialog>
</div>

{{-- Plantilla reutilizable de observaciones.
     Se genera aquí para evitar depender de JavaScript con expresiones Blade. --}}
<template id="observation-row-template">
    <div class="saved-observation" data-observation-row>
        <div>
            <p data-observation-text></p>
        </div>

        <button
            type="button"
            class="delete-observation"
            data-delete-observation
            data-delete-kind="delete"
            aria-label="Eliminar observación"
            title="Eliminar observación"
        >
            ×
        </button>
    </div>
</template>

<template id="summary-observation-template">
    <div class="summary-observation" data-summary-observation>
        <strong data-summary-reference class="hidden"></strong>
        <p data-summary-text></p>
    </div>
</template>



<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('revision-app');

    if (!app) {
        return;
    }

    const csrf = app.dataset.csrf;
    const storeObservationUrl = app.dataset.observacionesUrl;
    const approveUrl = app.dataset.aprobarUrl;
    const changesUrl = app.dataset.cambiosUrl;
    const rejectUrl = app.dataset.rechazarUrl;
    const initialError = (app.dataset.initialError || '').trim();

    const stageButtons = Array.from(
        document.querySelectorAll('[data-stage-button]')
    );

    const stages = Array.from(
        document.querySelectorAll('[data-stage]')
    );

    const sectionStageMap = {
        informacion_general: 'informacion',
        presentacion: 'presentacion',
        productos: 'productos',
        datos_solicitados: 'datos',
        precios_costos: 'precios',
        programacion: 'programacion',
        inscripcion: 'programacion'
    };

    let currentStageIndex = 0;

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');

        if (!container) {
            return;
        }

        const toast = document.createElement('div');
        toast.className = 'revision-toast ' + type;

        const icon = document.createElement('strong');
        icon.textContent = type === 'error'
            ? 'No se pudo completar'
            : type === 'success'
                ? 'Listo'
                : 'Información';

        const content = document.createElement('div');
        const text = document.createElement('p');

        text.textContent = message;

        content.appendChild(icon);
        content.appendChild(text);
        toast.appendChild(content);
        container.appendChild(toast);

        window.setTimeout(function () {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-8px)';
            toast.style.transition = '.18s ease';

            window.setTimeout(function () {
                toast.remove();
            }, 200);
        }, 3200);
    }

    if (initialError !== '') {
        window.setTimeout(function () {
            showToast(initialError, 'error');
        }, 50);
    }

    function showStage(index) {
        if (index < 0 || index >= stages.length) {
            return;
        }

        currentStageIndex = index;

        stages.forEach(function (stage, stageIndex) {
            stage.classList.toggle(
                'active',
                stageIndex === currentStageIndex
            );
        });

        stageButtons.forEach(function (button, buttonIndex) {
            const isActive = buttonIndex === currentStageIndex;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    stageButtons.forEach(function (button, index) {
        button.addEventListener('click', function () {
            showStage(index);
        });
    });

    document.querySelectorAll('[data-change-stage]').forEach(function (button) {
        button.addEventListener('click', function () {
            const stageName = button.dataset.changeStage;
            const index = stages.findIndex(function (stage) {
                return stage.dataset.stage === stageName;
            });

            if (index >= 0) {
                showStage(index);
            }
        });
    });

    document.querySelectorAll('[data-next-stage]').forEach(function (button) {
        button.addEventListener('click', function () {
            showStage(currentStageIndex + 1);
        });
    });

    document.querySelectorAll('[data-prev-stage]').forEach(function (button) {
        button.addEventListener('click', function () {
            showStage(currentStageIndex - 1);
        });
    });

    function getJsonError(data, fallback) {
        if (data && data.message) {
            return data.message;
        }

        if (data && data.errors) {
            const keys = Object.keys(data.errors);

            if (keys.length && data.errors[keys[0]].length) {
                return data.errors[keys[0]][0];
            }
        }

        return fallback;
    }

    async function sendRequest(url, options) {
        const response = await fetch(url, options);

        let data = null;

        try {
            data = await response.json();
        } catch (error) {
            data = null;
        }

        if (!response.ok) {
            throw new Error(
                getJsonError(
                    data,
                    'No se pudo completar la operación.'
                )
            );
        }

        return data;
    }

    function getSectionCount(section) {
        return document.querySelectorAll(
            '[data-observation-row][data-section="' + section + '"]'
        ).length;
    }

    function getTotalCount() {
        return document.querySelectorAll(
            '[data-observation-row]'
        ).length;
    }

    function updateCounts() {
        const total = getTotalCount();

        const totalElement = document.getElementById(
            'total-observaciones'
        );

        const decisionCount = document.getElementById(
            'decision-observation-count'
        );

        const summaryTotal = document.querySelector(
            '[data-summary-total]'
        );

        if (totalElement) {
            totalElement.textContent = String(total);
        }

        if (decisionCount) {
            decisionCount.textContent = String(total);
        }

        if (summaryTotal) {
            summaryTotal.textContent = String(total);
        }

        Object.keys(sectionStageMap).forEach(function (section) {
            document.querySelectorAll(
                '[data-observation-count="' + section + '"]'
            ).forEach(function (element) {
                const box = element.closest('[data-observation-box]');
                const count = box
                    ? box.querySelectorAll('[data-observation-row]').length
                    : 0;

                element.textContent = String(count);
                element.classList.toggle('hidden', count === 0);
            });
        });


        stageButtons.forEach(function (button) {
            const stageName = button.dataset.stageButton;
            const counter = button.querySelector(
                '[data-stage-count="' + stageName + '"]'
            );

            if (!counter) {
                return;
            }

            let count = 0;

            Object.entries(sectionStageMap).forEach(function (entry) {
                const section = entry[0];
                const stage = entry[1];

                if (stage === stageName) {
                    count += getSectionCount(section);
                }
            });

            counter.textContent = String(count);
            counter.classList.toggle('hidden', count === 0);
        });

        updateApproveState(total);
        updateEmptySummary(total);
    }

    function updateApproveState(total) {
        const button = document.getElementById('approve-button');
        const help = document.querySelector('[data-approve-help]');

        if (!button) {
            return;
        }

        button.disabled = total > 0;

        if (help) {
            help.textContent = total > 0
                ? 'Resuelve las observaciones pendientes para aprobar.'
                : 'La actividad puede aprobarse.';
        }
    }

    function updateEmptySummary(total) {
        const summary = document.getElementById(
            'final-observations-summary'
        );

        if (!summary) {
            return;
        }

        let empty = summary.querySelector('[data-empty-summary]');

        if (total === 0) {
            summary.querySelectorAll(
                '[data-summary-section]'
            ).forEach(function (section) {
                section.remove();
            });

            if (!empty) {
                empty = document.createElement('div');
                empty.className = 'empty-observations';
                empty.dataset.emptySummary = '';
                empty.textContent =
                    'No hay observaciones pendientes.';

                summary.appendChild(empty);
            }
        } else if (empty) {
            empty.remove();
        }
    }

    function getSectionTitle(section) {
        const titles = {
            informacion_general: 'Información general',
            presentacion: 'Presentación',
            productos: 'Productos / Servicios',
            datos_solicitados: 'Datos solicitados',
            precios_costos: 'Precios / Costos',
            programacion: 'Programación',
            inscripcion: 'Inscripción'
        };

        return titles[section] || section;
    }

    function addSummaryObservation(data, referenceName) {
        const summary = document.getElementById(
            'final-observations-summary'
        );

        if (!summary) {
            return;
        }

        const empty = summary.querySelector('[data-empty-summary]');

        if (empty) {
            empty.remove();
        }

        let section = summary.querySelector(
            '[data-summary-section="' + data.seccion + '"]'
        );

        if (!section) {
            section = document.createElement('div');
            section.className = 'summary-section';
            section.dataset.summarySection = data.seccion;

            const heading = document.createElement('h4');
            heading.appendChild(
                document.createTextNode(
                    getSectionTitle(data.seccion) + ' '
                )
            );

            const count = document.createElement('span');
            count.dataset.summarySectionCount = data.seccion;
            count.textContent = '0';

            heading.appendChild(count);

            const list = document.createElement('div');
            list.dataset.summaryList = data.seccion;

            section.appendChild(heading);
            section.appendChild(list);
            summary.appendChild(section);
        }

        const template = document.getElementById(
            'summary-observation-template'
        );

        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector(
            '[data-summary-observation]'
        );

        row.dataset.summaryObservation = data.id_observacion;

        const reference = row.querySelector(
            '[data-summary-reference]'
        );

        if (referenceName) {
            reference.textContent = referenceName;
            reference.classList.remove('hidden');
        }

        row.querySelector(
            '[data-summary-text]'
        ).textContent = data.observacion;

        section.querySelector(
            '[data-summary-list]'
        ).appendChild(fragment);

        updateSummarySectionCount(data.seccion);
    }

    function removeSummaryObservation(id, sectionName) {
        const row = document.querySelector(
            '[data-summary-observation="' + id + '"]'
        );

        if (row) {
            row.remove();
        }

        updateSummarySectionCount(sectionName);
    }

    function updateSummarySectionCount(sectionName) {
        const section = document.querySelector(
            '[data-summary-section="' + sectionName + '"]'
        );

        if (!section) {
            return;
        }

        const rows = section.querySelectorAll(
            '[data-summary-observation]'
        );

        if (rows.length === 0) {
            section.remove();
            return;
        }

        const count = section.querySelector(
            '[data-summary-section-count]'
        );

        if (count) {
            count.textContent = String(rows.length);
        }
    }

    function createSavedObservation(data, box) {
        const template = document.getElementById(
            'observation-row-template'
        );

        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector(
            '[data-observation-row]'
        );

        row.dataset.observationRow = data.id_observacion;
        row.dataset.section = data.seccion;
        row.dataset.historical = '0';

        row.querySelector(
            '[data-observation-text]'
        ).textContent = data.observacion;

        const deleteButton = row.querySelector(
            '[data-delete-observation]'
        );

        const deleteBaseUrl = box.dataset.deleteBaseUrl;

        deleteButton.dataset.deleteUrl =
            deleteBaseUrl + '/' + data.id_observacion;

        const list = box.querySelector(
            '[data-saved-observations]'
        );

        list.appendChild(fragment);
    }

    document.querySelectorAll(
        '[data-observation-box]'
    ).forEach(function (box) {
        const addButton = box.querySelector(
            '[data-add-observation]'
        );

        if (addButton) {
            addButton.addEventListener('click', function () {
                const editor = box.querySelector(
                    '[data-observation-editor]'
                );

                editor.classList.remove('hidden');

                const textarea = editor.querySelector('textarea');

                if (textarea) {
                    textarea.focus();
                }

                addButton.classList.add('hidden');
            });
        }

        const saveButton = box.querySelector(
            '[data-save-observation]'
        );

        if (!saveButton) {
            return;
        }

        saveButton.addEventListener('click', async function () {
            const editor = box.querySelector(
                '[data-observation-editor]'
            );

            const textarea = editor.querySelector('textarea');
            const text = textarea.value.trim();

            if (text.length < 5) {
                showToast(
                    'La observación debe tener al menos 5 caracteres.',
                    'error'
                );
                textarea.focus();
                return;
            }

            const payload = new FormData();

            payload.append('seccion', box.dataset.section);
            payload.append('observacion', text);

            if (box.dataset.referenceType) {
                payload.append(
                    'referencia_tipo',
                    box.dataset.referenceType
                );
            }

            if (box.dataset.referenceId) {
                payload.append(
                    'referencia_id',
                    box.dataset.referenceId
                );
            }

            saveButton.disabled = true;

            try {
                const data = await sendRequest(
                    storeObservationUrl,
                    {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: payload
                    }
                );

                createSavedObservation(
                    data.observacion,
                    box
                );

                addSummaryObservation(
                    data.observacion,
                    box.dataset.referenceName || ''
                );

                textarea.value = '';
                editor.classList.add('hidden');

                if (addButton) {
                    addButton.classList.remove('hidden');
                }

                updateCounts();

                showToast(
                    data.message || 'Observación agregada correctamente.',
                    'success'
                );
            } catch (error) {
                showToast(error.message, 'error');
            } finally {
                saveButton.disabled = false;
            }
        });
    });

    document.addEventListener('click', async function (event) {
        const button = event.target.closest(
            '[data-delete-observation]'
        );

        if (!button) {
            return;
        }

        const row = button.closest('[data-observation-row]');

        if (!row) {
            return;
        }

        const url = button.dataset.deleteUrl;

        if (!url) {
            return;
        }

        button.disabled = true;

        try {
            const data = await sendRequest(
                url,
                {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    }
                }
            );

            const id = row.dataset.observationRow;
            const section = row.dataset.section;

            row.remove();
            removeSummaryObservation(id, section);
            updateCounts();

            showToast(
                data.message || 'Observación eliminada correctamente.',
                'success'
            );
        } catch (error) {
            button.disabled = false;
            showToast(error.message, 'error');
        }
    });

    document.querySelectorAll(
        '[data-variant-simulator]'
    ).forEach(function (simulator) {
        const selects = simulator.querySelectorAll(
            '[data-variant-option]'
        );

        const priceElement = simulator.querySelector(
            '[data-simulator-price]'
        );

        const costElement = simulator.querySelector(
            '[data-simulator-cost]'
        );

        const basePrice = Number(
            simulator.dataset.basePrice || 0
        );

        const baseCost = Number(
            simulator.dataset.baseCost || 0
        );

        function calculate() {
            let price = basePrice;
            let cost = baseCost;

            selects.forEach(function (select) {
                const option = select.options[
                    select.selectedIndex
                ];

                if (!option) {
                    return;
                }

                price += Number(
                    option.dataset.priceDelta || 0
                );

                cost += Number(
                    option.dataset.costDelta || 0
                );
            });

            priceElement.textContent =
                '$' + price.toFixed(2);

            costElement.textContent =
                '$' + cost.toFixed(2);
        }

        selects.forEach(function (select) {
            select.addEventListener(
                'change',
                calculate
            );
        });

        calculate();
    });


    document.querySelectorAll('[data-chunk-list]').forEach(function (list) {
        const button = list.querySelector('[data-show-more]');

        if (!button) {
            return;
        }

        const items = Array.from(list.querySelectorAll('[data-chunk-item]'));
        const chunkSize = Math.max(1, Number(list.dataset.chunkSize || 12));
        let visible = items.filter(function (item) {
            return !item.classList.contains('chunk-hidden');
        }).length;

        function updateShowMore() {
            const remaining = Math.max(0, items.length - visible);

            if (remaining === 0) {
                button.remove();
                return;
            }

            button.textContent = 'Mostrar ' + Math.min(chunkSize, remaining) + ' opciones más';
        }

        button.addEventListener('click', function () {
            const next = items.slice(visible, visible + chunkSize);

            next.forEach(function (item) {
                item.classList.remove('chunk-hidden');
            });

            visible += next.length;
            updateShowMore();
        });

        updateShowMore();
    });

    document.querySelectorAll('[data-session-list]').forEach(function (list) {
        const rows = Array.from(list.querySelectorAll('[data-session-row]'));
        const pageSize = Math.max(1, Number(list.dataset.pageSize || 8));
        const pager = list.parentElement.querySelector('[data-session-pager]');

        if (!pager || rows.length <= pageSize) {
            return;
        }

        const prev = pager.querySelector('[data-session-prev]');
        const next = pager.querySelector('[data-session-next]');
        const info = pager.querySelector('[data-session-page-info]');
        const pages = Math.ceil(rows.length / pageSize);
        let page = 0;

        function renderSessionPage() {
            const start = page * pageSize;
            const end = start + pageSize;

            rows.forEach(function (row, index) {
                row.classList.toggle('session-page-hidden', index < start || index >= end);

                if (index < start || index >= end) {
                    row.open = false;
                }
            });

            prev.disabled = page === 0;
            next.disabled = page >= pages - 1;
            info.textContent = 'Página ' + (page + 1) + ' de ' + pages;
        }

        prev.addEventListener('click', function () {
            if (page > 0) {
                page -= 1;
                renderSessionPage();
            }
        });

        next.addEventListener('click', function () {
            if (page < pages - 1) {
                page += 1;
                renderSessionPage();
            }
        });

        renderSessionPage();
    });

    // Un único diálogo reutilizable para aprobar, solicitar cambios o rechazar.
    const decisionDialog = document.getElementById('revision-decision-dialog');
    const dialogTitle = document.getElementById('review-decision-title');
    const dialogDescription = document.getElementById('review-decision-description');
    const dialogIcon = decisionDialog.querySelector('[data-review-dialog-icon]');
    const dialogSummary = decisionDialog.querySelector('[data-review-dialog-summary]');
    const dialogError = decisionDialog.querySelector('[data-review-dialog-error]');
    const dialogConfirm = decisionDialog.querySelector('[data-review-dialog-confirm]');
    const dialogDismissButtons = decisionDialog.querySelectorAll('[data-review-dialog-dismiss]');
    const decisionButtons = document.querySelectorAll('[data-decision]');
    const confirmDefaultText = 'Confirmar';
    let preparedDecision = null;
    let decisionSubmitting = false;
    let decisionSucceeded = false;
    let decisionOrigin = null;

    const decisionDialogOptions = {
        approve: {
            title: '¿Aprobar esta actividad?',
            description: 'Confirmas que la actividad cumple con los requisitos de revisión y autorizas su aprobación.',
            confirm: 'Sí, aprobar actividad',
            icon: '✓'
        },
        changes: {
            title: '¿Solicitar cambios al creador?',
            description: 'La actividad se devolverá al creador para que revise las observaciones que registraste.',
            confirm: 'Sí, solicitar cambios',
            icon: '↻'
        },
        reject: {
            title: '¿Rechazar esta actividad?',
            description: 'La revisión terminará como rechazada. Verificá que los motivos estén registrados antes de continuar.',
            confirm: 'Sí, rechazar actividad',
            icon: '✕'
        }
    };

    function dismissDecisionDialog() {
        if (decisionSubmitting || !decisionDialog.open) {
            return;
        }

        decisionDialog.close();
    }

    function setDialogLoading(loading) {
        decisionSubmitting = loading;
        dialogConfirm.disabled = loading;
        dialogDismissButtons.forEach(function (button) {
            button.disabled = loading;
        });
        dialogConfirm.textContent = loading ? 'Procesando…' : (preparedDecision?.confirmText || confirmDefaultText);
        decisionDialog.setAttribute('aria-busy', loading ? 'true' : 'false');
    }

    dialogDismissButtons.forEach(function (button) {
        button.addEventListener('click', dismissDecisionDialog);
    });

    decisionDialog.addEventListener('cancel', function (event) {
        if (decisionSubmitting) {
            event.preventDefault();
        }
    });

    decisionDialog.addEventListener('click', function (event) {
        if (event.target !== decisionDialog || decisionSubmitting) {
            return;
        }

        // Cerrar al pulsar fuera de la tarjeta, no dentro de su contenido.
        const bounds = decisionDialog.getBoundingClientRect();
        const inside = event.clientX >= bounds.left && event.clientX <= bounds.right &&
            event.clientY >= bounds.top && event.clientY <= bounds.bottom;
        if (!inside) {
            dismissDecisionDialog();
        }
    });

    decisionDialog.addEventListener('close', function () {
        preparedDecision = null;
        dialogError.hidden = true;
        dialogError.textContent = '';
        if (!decisionSucceeded && decisionOrigin?.isConnected) {
            decisionOrigin.focus();
        }
        decisionOrigin = null;
    });

    function submitDecision(type) {
        if (decisionSubmitting || decisionDialog.open || !decisionDialogOptions[type]) {
            return;
        }

        const textarea = document.getElementById('general-observation');
        const observation = textarea.value.trim();
        const total = getTotalCount();
        let url = null;

        if (type === 'approve') {
            if (total > 0) {
                showToast('No puedes aprobar mientras existan observaciones pendientes.', 'error');
                return;
            }
            url = approveUrl;
        }

        if (type === 'changes') {
            if (total === 0 && observation === '') {
                showToast('Agrega una observación general o al menos una observación específica para solicitar cambios.', 'error');
                textarea.focus();
                return;
            }
            url = changesUrl;
        }

        if (type === 'reject') {
            if (total === 0 && observation === '') {
                showToast('Debes justificar el rechazo con una observación general o específica.', 'error');
                textarea.focus();
                return;
            }
            url = rejectUrl;
        }

        if (!url) {
            return;
        }

        const option = decisionDialogOptions[type];
        preparedDecision = { type: type, url: url, observation: observation, confirmText: option.confirm };
        decisionSucceeded = false;
        decisionOrigin = document.activeElement;
        decisionDialog.dataset.tone = type;
        dialogTitle.textContent = option.title;
        dialogDescription.textContent = option.description;
        dialogIcon.textContent = option.icon;
        dialogSummary.textContent = total + (total === 1 ? ' observación específica' : ' observaciones específicas') +
            (observation !== '' ? ' · Incluye observación general' : '');
        dialogError.hidden = true;
        dialogError.textContent = '';
        setDialogLoading(false);
        decisionDialog.showModal();
        dialogConfirm.focus();
    }

    dialogConfirm.addEventListener('click', async function () {
        if (!preparedDecision || decisionSubmitting) {
            return;
        }

        const decision = preparedDecision;
        setDialogLoading(true);
        decisionButtons.forEach(function (button) {
            button.disabled = true;
        });

        const payload = new FormData();
        if (decision.observation !== '') {
            payload.append('observacion', decision.observation);
        }

        try {
            const data = await sendRequest(decision.url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: payload
            });

            decisionSucceeded = true;
            setDialogLoading(false);
            decisionDialog.close();
            showToast(data?.message || 'Operación completada.', 'success');

            if (data?.redirect) {
                window.setTimeout(function () {
                    window.location.href = data.redirect;
                }, 650);
            }
        } catch (error) {
            setDialogLoading(false);
            decisionButtons.forEach(function (button) {
                button.disabled = false;
            });
            updateApproveState(getTotalCount());
            dialogError.textContent = error.message || 'No se pudo completar la operación.';
            dialogError.hidden = false;
            dialogConfirm.focus();
            showToast(dialogError.textContent, 'error');
        }
    });

    document.querySelectorAll(
        '[data-decision]'
    ).forEach(function (button) {
        button.addEventListener('click', function () {
            submitDecision(button.dataset.decision);
        });
    });

    updateCounts();
});
</script>
@endsection