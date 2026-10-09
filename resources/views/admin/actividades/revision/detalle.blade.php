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
    class="revision-page"
    data-observaciones-url="{{ route('admin.actividades.revision.observaciones.store', $actividad) }}"
    data-aprobar-url="{{ route('admin.actividades.revision.aprobar', $actividad) }}"
    data-cambios-url="{{ route('admin.actividades.revision.solicitar-cambios', $actividad) }}"
    data-rechazar-url="{{ route('admin.actividades.revision.rechazar', $actividad) }}"
    data-csrf="{{ csrf_token() }}"
    data-initial-error="{{ $errors->first() }}"
>
    <div id="toast-container" class="toast-container" aria-live="polite"></div>

    <div class="revision-header">
        <div>
            <a href="{{ route('admin.actividades.revision.index') }}" class="back-link">
                ← Volver a revisiones
            </a>

            <div class="eyebrow">Revisión de actividad</div>
            <h1>{{ $actividad->nombre }}</h1>

            <p>
                Comprueba cómo verá y utilizará esta actividad una persona.
                Si algo debe corregirse, deja la observación justo donde corresponde.
            </p>
        </div>

        <div class="revision-status">
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

    <div class="stage-navigation">
        @foreach ($etapas as $indice => $etapa)
            @php
                $cantidad = $conteoEtapa($etapa['secciones']);
            @endphp

            <button
                type="button"
                class="stage-button {{ $indice === 0 ? 'active' : '' }}"
                data-stage-button="{{ $etapa['id'] }}"
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
                <h3>Inscripción</h3>

                <div class="info-grid">
                    <article class="info-card">
                        <span class="info-label">Inscripción</span>
                        <strong>{{ $actividad->habilita_inscripcion ? 'Habilitada' : 'Deshabilitada' }}</strong>
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

<style>
    .approved-changes-panel {
        margin-bottom: 20px;
        border: 1px solid rgba(34, 197, 94, .25);
        border-radius: 20px;
        background: linear-gradient(135deg, rgba(34, 197, 94, .08), rgba(14, 165, 233, .04));
        padding: 18px;
    }

    .approved-changes-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
    }

    .approved-changes-kicker {
        display: inline-block;
        color: #86efac;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .approved-changes-heading h2 {
        margin: 5px 0 0;
        color: #fff;
        font-size: 18px;
        font-weight: 800;
    }

    .approved-changes-heading p {
        margin: 6px 0 0;
        max-width: 760px;
        color: #94a3b8;
        font-size: 13px;
        line-height: 1.6;
    }

    .approved-changes-count {
        flex: 0 0 auto;
        border: 1px solid rgba(34, 197, 94, .22);
        border-radius: 999px;
        background: rgba(34, 197, 94, .1);
        padding: 7px 10px;
        color: #86efac;
        font-size: 11px;
        font-weight: 800;
    }

    .approved-changes-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 16px;
    }

    .approved-change-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        border: 1px solid rgba(34, 197, 94, .18);
        border-radius: 14px;
        background: rgba(15, 23, 42, .72);
        padding: 13px;
        text-align: left;
        transition: .18s ease;
    }

    .approved-change-card:hover {
        border-color: rgba(34, 197, 94, .4);
        background: rgba(34, 197, 94, .08);
        transform: translateY(-1px);
    }

    .approved-change-card-title {
        color: #fff;
        font-size: 13px;
        font-weight: 800;
    }

    .approved-change-card-fields {
        color: #cbd5e1;
        font-size: 11px;
        line-height: 1.5;
    }

    .approved-change-card-entities {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .approved-change-card-entities span {
        border-radius: 999px;
        background: rgba(14, 165, 233, .1);
        padding: 3px 7px;
        color: #7dd3fc;
        font-size: 10px;
        font-weight: 700;
    }

    .entity-status-dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        margin-right: 3px;
        border-radius: 999px;
        background: #22c55e;
        vertical-align: middle;
    }

    .entity-status-dot--nuevo {
        background: #38bdf8;
    }

    .entity-status-dot--eliminado {
        background: #f87171;
    }

    .approved-change-card-action {
        margin-top: 2px;
        color: #86efac;
        font-size: 11px;
        font-weight: 800;
    }

    .stage-change-dot,
    .field-change-dot {
        display: inline-block;
        flex: 0 0 auto;
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #22c55e;
        box-shadow: 0 0 0 3px rgba(34, 197, 94, .14);
        vertical-align: middle;
    }

    .stage-change-dot {
        width: 9px;
        height: 9px;
        margin-left: 2px;
    }

    .field-change-dot {
        width: 7px;
        height: 7px;
        margin-left: 5px;
        box-shadow: 0 0 0 2px rgba(34, 197, 94, .12);
    }

    .compact-meta {
        margin-bottom: 4px !important;
        font-size: 11px !important;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .review-stage.stage-has-changes > .section-heading {
        border: 1px solid rgba(34, 197, 94, .22);
        border-radius: 16px;
        background: rgba(34, 197, 94, .055);
        padding: 14px 16px;
    }

    @media (max-width: 1024px) {
        .approved-changes-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .approved-changes-panel {
            padding: 14px;
        }

        .approved-changes-heading {
            flex-direction: column;
        }

        .approved-changes-grid {
            grid-template-columns: 1fr;
        }
    }

    .revision-page {
        --review-text: #0f172a;
        --review-muted: #64748b;
        --review-subtle: #94a3b8;
        --review-surface: #ffffff;
        --review-surface-soft: #f8fafc;
        --review-surface-raised: #ffffff;
        --review-border: #e2e8f0;
        --review-border-strong: #cbd5e1;
        --review-primary: #10b981;
        --review-primary-hover: #059669;
        --review-primary-soft: rgba(16, 185, 129, .10);
        --review-primary-border: rgba(16, 185, 129, .28);
        --review-info: #2563eb;
        --review-info-soft: rgba(37, 99, 235, .08);
        --review-warning: #d97706;
        --review-warning-soft: rgba(245, 158, 11, .10);
        --review-danger: #dc2626;
        --review-danger-soft: rgba(239, 68, 68, .09);
        --review-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        max-width: 1500px;
        margin: 0 auto;
        padding: 24px;
        color: var(--review-text);
    }

    .dark .revision-page {
        --review-text: #f8fafc;
        --review-muted: #94a3b8;
        --review-subtle: #64748b;
        --review-surface: rgba(255, 255, 255, .045);
        --review-surface-soft: rgba(255, 255, 255, .025);
        --review-surface-raised: #101c2d;
        --review-border: rgba(255, 255, 255, .10);
        --review-border-strong: rgba(255, 255, 255, .17);
        --review-primary: #10b981;
        --review-primary-hover: #34d399;
        --review-primary-soft: rgba(16, 185, 129, .11);
        --review-primary-border: rgba(16, 185, 129, .30);
        --review-info: #60a5fa;
        --review-info-soft: rgba(59, 130, 246, .09);
        --review-warning: #f59e0b;
        --review-warning-soft: rgba(245, 158, 11, .09);
        --review-danger: #f87171;
        --review-danger-soft: rgba(248, 113, 113, .10);
        --review-shadow: 0 8px 24px rgba(0, 0, 0, .12);
    }

    .revision-page *,
    .revision-page *::before,
    .revision-page *::after {
        box-sizing: border-box;
    }

    .revision-header {
        display: flex;
        justify-content: space-between;
        gap: 24px;
        align-items: flex-start;
        margin-bottom: 22px;
    }

    .revision-header h1 {
        margin: 6px 0 7px;
        color: var(--review-text);
        font-size: clamp(1.7rem, 3vw, 2.35rem);
        font-weight: 850;
        line-height: 1.12;
    }

    .revision-header p,
    .section-heading p,
    .summary-heading p,
    .simulator-heading p,
    .rules-help,
    .card-intro p,
    .requested-item-header p {
        margin: 0;
        color: var(--review-muted);
        line-height: 1.55;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        margin-bottom: 12px;
        color: var(--review-muted);
        text-decoration: none;
        font-size: .86rem;
        font-weight: 700;
    }

    .back-link:hover {
        color: var(--review-primary);
    }

    .eyebrow,
    .section-kicker {
        color: var(--review-primary);
        font-size: .72rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .09em;
    }

    .revision-status {
        display: flex;
        justify-content: flex-end;
        align-items: stretch;
        flex-wrap: wrap;
        gap: 8px;
    }

    .revision-status > div {
        min-width: 108px;
        padding: 11px 14px;
        background: var(--review-surface);
        border: 1px solid var(--review-border);
        border-radius: 12px;
        box-shadow: var(--review-shadow);
    }

    .revision-status span,
    .revision-status strong {
        display: block;
    }

    .revision-status span {
        margin-bottom: 3px;
        color: var(--review-muted);
        font-size: .72rem;
    }

    .revision-status strong {
        color: var(--review-text);
    }

    .status-pending {
        color: var(--review-warning) !important;
    }

    .stage-navigation {
        position: sticky;
        top: 10px;
        z-index: 30;
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 6px;
        width: 100%;
        padding: 7px;
        margin-bottom: 24px;
        overflow: visible;
        background: var(--review-surface-raised);
        border: 1px solid var(--review-border);
        border-radius: 16px;
        box-shadow: var(--review-shadow);
    }

    .stage-button {
        min-width: 0;
        min-height: 48px;
        border: 1px solid transparent;
        background: transparent;
        border-radius: 11px;
        padding: 8px 9px;
        display: flex;
        align-items: center;
        gap: 7px;
        cursor: pointer;
        color: var(--review-muted);
        font-weight: 800;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .stage-button:hover {
        background: var(--review-surface-soft);
        color: var(--review-text);
    }

    .stage-button.active {
        background: var(--review-primary-soft);
        border-color: var(--review-primary-border);
        color: var(--review-text);
    }

    .stage-number {
        display: grid;
        place-items: center;
        width: 25px;
        height: 25px;
        flex: 0 0 25px;
        border-radius: 50%;
        background: var(--review-surface-soft);
        border: 1px solid var(--review-border);
        color: var(--review-muted);
        font-size: .72rem;
    }

    .stage-button.active .stage-number {
        background: var(--review-primary);
        border-color: var(--review-primary);
        color: #fff;
    }

    .stage-name {
        min-width: 0;
        color: inherit;
        font-size: .76rem;
        line-height: 1.16;
        text-align: left;
        white-space: normal;
    }

    .stage-count {
        min-width: 18px;
        height: 18px;
        padding: 0 5px;
        margin-left: auto;
        display: inline-grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid rgba(245, 158, 11, .28);
        border-radius: 999px;
        background: var(--review-warning-soft);
        color: var(--review-warning);
        font-size: .64rem;
        font-weight: 900;
    }

    .hidden {
        display: none !important;
    }

    .review-stage {
        display: none;
    }

    .review-stage.active {
        display: block;
    }

    .section-heading {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 18px;
    }

    .section-heading h2 {
        margin: 5px 0 4px;
        color: var(--review-text);
        font-size: 1.45rem;
        font-weight: 850;
    }

    .content-card,
    .product-card,
    .form-card,
    .requested-item {
        background: var(--review-surface);
        border: 1px solid var(--review-border);
        border-radius: 16px;
        padding: 19px;
        margin-bottom: 16px;
        color: var(--review-text);
        box-shadow: var(--review-shadow);
    }

    .content-card h3,
    .product-card h3,
    .form-card h3,
    .requested-item h3 {
        margin: 0 0 10px;
        color: var(--review-text);
    }

    .content-card h3:not(:first-child) {
        margin-top: 22px;
    }

    .content-card p,
    .product-card p,
    .form-card p,
    .requested-item p {
        color: var(--review-muted);
    }

    .long-text {
        color: var(--review-muted);
        line-height: 1.65;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .info-grid.compact {
        margin: 16px 0;
    }

    .info-card {
        min-width: 0;
        background: var(--review-surface);
        border: 1px solid var(--review-border);
        border-radius: 13px;
        padding: 14px;
        color: var(--review-text);
    }

    .info-label {
        display: block;
        margin-bottom: 5px;
        color: var(--review-muted);
        font-size: .73rem;
    }

    .tag-list,
    .field-options {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .tag,
    .pill,
    .field-options span {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border: 1px solid var(--review-border);
        border-radius: 999px;
        background: var(--review-surface-soft);
        color: var(--review-text);
        font-size: .76rem;
        font-weight: 700;
    }

    .presentation-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(260px, .6fr);
        gap: 16px;
    }

    .cover-image {
        width: 100%;
        max-height: 460px;
        object-fit: cover;
        border-radius: 12px;
        display: block;
    }

    .empty-media,
    .empty-state {
        min-height: 120px;
        display: grid;
        place-items: center;
        padding: 24px;
        text-align: center;
        border: 1px dashed var(--review-border-strong);
        border-radius: 13px;
        color: var(--review-muted);
        background: var(--review-surface-soft);
        margin-bottom: 16px;
    }

    .compact-empty {
        min-height: 86px;
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 12px;
    }

    .gallery-item {
        margin: 0;
        border: 1px solid var(--review-border);
        border-radius: 12px;
        overflow: hidden;
        background: var(--review-surface-soft);
    }

    .gallery-item img {
        width: 100%;
        aspect-ratio: 4 / 3;
        object-fit: cover;
        display: block;
    }

    .gallery-item figcaption {
        padding: 8px 10px;
        color: var(--review-muted);
        font-size: .75rem;
    }

    .detail-list {
        margin: 0;
    }

    .detail-list > div {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 10px 0;
        border-bottom: 1px solid var(--review-border);
    }

    .detail-list > div:last-child {
        border-bottom: 0;
    }

    .detail-list dt {
        color: var(--review-muted);
    }

    .detail-list dd {
        margin: 0;
        color: var(--review-text);
        text-align: right;
        font-weight: 700;
    }

    .stack {
        display: grid;
        gap: 16px;
    }

    .compact-stack {
        gap: 12px;
    }

    .embedded-card {
        margin: 0;
        box-shadow: none;
        background: var(--review-surface-soft);
    }

    .product-header,
    .form-header,
    .session-header,
    .summary-heading,
    .requested-item-header,
    .card-intro {
        display: flex;
        justify-content: space-between;
        gap: 20px;
        align-items: flex-start;
    }

    .product-header h3,
    .form-header h3,
    .session-header h4,
    .requested-item-header h3 {
        margin: 4px 0;
        color: var(--review-text);
    }

    .product-header p,
    .form-header p,
    .session-header p {
        margin: 0;
        color: var(--review-muted);
    }

    .item-type {
        color: var(--review-info);
        font-size: .7rem;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .price-box {
        min-width: 135px;
        text-align: right;
    }

    .price-box span,
    .price-box strong,
    .price-box small {
        display: block;
    }

    .price-box span,
    .price-box small {
        color: var(--review-muted);
    }

    .price-box strong {
        margin: 2px 0;
        color: var(--review-text);
        font-size: 1.35rem;
    }

    .variant-summary,
    .rules-box {
        border-top: 1px solid var(--review-border);
        padding-top: 16px;
        margin-top: 16px;
    }

    .variant-summary h4,
    .rules-box h4 {
        margin: 0 0 10px;
        color: var(--review-text);
    }

    .rules-help {
        margin: -4px 0 12px;
        font-size: .82rem;
    }

    .attribute-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 10px;
    }

    .attribute-card {
        padding: 12px;
        border: 1px solid var(--review-border);
        border-radius: 11px;
        background: var(--review-surface-soft);
    }

    .attribute-card > strong {
        display: block;
        margin-bottom: 8px;
        color: var(--review-text);
    }

    .variant-simulator {
        margin-top: 16px;
        padding: 16px;
        background: var(--review-info-soft);
        border: 1px solid rgba(59, 130, 246, .16);
        border-radius: 13px;
    }

    .simulator-heading h4 {
        margin: 0 0 3px;
        color: var(--review-text);
    }

    .simulator-controls {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
        gap: 10px;
        margin-top: 14px;
    }

    .simulator-controls label > span {
        display: block;
        margin-bottom: 5px;
        color: var(--review-muted);
        font-size: .78rem;
        font-weight: 700;
    }

    .simulator-controls select,
    #general-observation,
    .observation-editor textarea {
        color-scheme: light;
        background: var(--review-surface-raised);
        color: var(--review-text);
        border: 1px solid var(--review-border-strong);
    }

    .dark .simulator-controls select,
    .dark #general-observation,
    .dark .observation-editor textarea {
        color-scheme: dark;
    }

    .simulator-controls select {
        width: 100%;
        padding: 9px 10px;
        border-radius: 9px;
    }

    .simulator-result {
        display: flex;
        gap: 28px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--review-border);
    }

    .simulator-result span,
    .simulator-result strong {
        display: block;
    }

    .simulator-result span {
        color: var(--review-muted);
        font-size: .74rem;
    }

    .simulator-result strong {
        color: var(--review-text);
        font-size: 1.15rem;
    }

    .compact-notice {
        margin-top: 15px;
        padding: 12px 14px;
        background: var(--review-surface-soft);
        border: 1px solid var(--review-border);
        border-radius: 10px;
        color: var(--review-muted);
    }

    .rules-list {
        display: grid;
        gap: 8px;
    }

    .rule-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        padding: 11px 12px;
        border: 1px solid var(--review-border);
        border-radius: 10px;
        background: var(--review-surface-soft);
    }

    .rule-main strong,
    .rule-main small {
        display: block;
    }

    .rule-main strong {
        color: var(--review-text);
    }

    .rule-main small {
        margin-top: 2px;
        color: var(--review-muted);
    }

    .rule-values {
        display: flex;
        gap: 16px;
        flex: 0 0 auto;
    }

    .rule-values > span {
        color: var(--review-muted);
        font-size: .74rem;
        text-align: right;
    }

    .rule-values strong {
        display: block;
        margin-top: 1px;
        color: var(--review-text);
        font-size: .86rem;
    }

    .requested-data-overview {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr) auto;
        gap: 13px;
        align-items: start;
        margin-bottom: 16px;
        padding: 15px;
        border: 1px solid rgba(59, 130, 246, .18);
        border-radius: 14px;
        background: var(--review-info-soft);
    }

    .overview-icon {
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: rgba(59, 130, 246, .14);
        color: var(--review-info);
        font-weight: 900;
    }

    .requested-data-overview strong {
        display: block;
        margin-bottom: 3px;
        color: var(--review-text);
    }

    .requested-data-overview p {
        margin: 0;
        color: var(--review-muted);
        font-size: .84rem;
        line-height: 1.5;
    }

    .overview-count,
    .friendly-status {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 5px 9px;
        white-space: nowrap;
        font-size: .72rem;
        font-weight: 800;
    }

    .overview-count,
    .friendly-status.success {
        color: var(--review-primary);
        background: var(--review-primary-soft);
        border: 1px solid var(--review-primary-border);
    }

    .friendly-status.neutral {
        color: var(--review-muted);
        background: var(--review-surface-soft);
        border: 1px solid var(--review-border);
    }

    .requested-item-header {
        margin-bottom: 14px;
    }

    .requested-item-header p {
        max-width: 720px;
        margin-top: 4px;
        font-size: .85rem;
    }

    .requested-fields {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .requested-field {
        min-width: 0;
        padding: 13px;
        border: 1px solid var(--review-border);
        border-radius: 12px;
        background: var(--review-surface-soft);
    }

    .field-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .field-title-row strong {
        min-width: 0;
        color: var(--review-text);
    }

    .requested-field-main > p {
        margin: 4px 0 10px;
        color: var(--review-muted);
        font-size: .8rem;
    }

    .field-badge {
        padding: 3px 7px;
        border-radius: 999px;
        font-size: .65rem;
        font-weight: 850;
        flex: 0 0 auto;
    }

    .field-badge.required {
        color: var(--review-info);
        background: var(--review-info-soft);
    }

    .field-badge.optional {
        color: var(--review-muted);
        background: var(--review-surface);
        border: 1px solid var(--review-border);
    }

    .field-example {
        color: var(--review-muted);
        font-size: .77rem;
        font-style: italic;
    }

    .forms-section {
        margin-top: 16px;
    }

    .forms-section .card-intro {
        margin-bottom: 14px;
    }

    .fields-table {
        margin-top: 15px;
        border: 1px solid var(--review-border);
        border-radius: 11px;
        overflow: hidden;
    }

    .field-row {
        display: grid;
        grid-template-columns: minmax(180px, 2fr) repeat(3, 1fr);
        gap: 10px;
        align-items: center;
        padding: 11px 13px;
        border-bottom: 1px solid var(--review-border);
    }

    .field-row:last-child {
        border-bottom: 0;
    }

    .field-row strong,
    .field-row small {
        display: block;
    }

    .field-row small {
        color: var(--review-subtle);
    }

    .required-text {
        color: var(--review-danger);
        font-weight: 700;
    }

    .price-list,
    .session-list,
    .history-list {
        display: grid;
        gap: 10px;
        margin-bottom: 16px;
    }

    .price-item {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 15px;
        background: var(--review-surface);
        border: 1px solid var(--review-border);
        border-radius: 13px;
        padding: 14px;
        color: var(--review-text);
    }

    .price-item span,
    .price-item strong {
        display: block;
    }

    .price-item span {
        color: var(--review-muted);
        font-size: .77rem;
    }

    .promotion-grid,
    .resource-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 10px;
    }

    .promotion-card,
    .resource-card {
        padding: 13px;
        border: 1px solid var(--review-border);
        border-radius: 11px;
        background: var(--review-surface-soft);
        color: var(--review-text);
    }

    .promotion-card strong,
    .promotion-card span,
    .resource-card strong,
    .resource-card span,
    .resource-card small {
        display: block;
    }

    .promo-code {
        color: var(--review-muted);
        font-size: .72rem;
    }

    .promo-value {
        margin: 8px 0;
        font-size: 1.1rem;
    }

    .status-ok {
        color: var(--review-primary);
    }

    .session-card {
        border: 1px solid var(--review-border);
        border-radius: 13px;
        padding: 15px;
        background: var(--review-surface-soft);
        color: var(--review-text);
    }

    .resource-list {
        margin-top: 12px;
    }

    .resource-list > strong {
        display: block;
        margin-bottom: 7px;
    }

    .observation-box {
        margin-top: 14px;
        padding: 13px;
        border: 1px solid var(--review-border);
        background: var(--review-surface);
        border-radius: 13px;
    }

    .observation-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 9px;
    }

    .observation-header h4 {
        margin: 0;
        color: var(--review-text);
        font-size: .86rem;
        font-weight: 800;
    }

    .observation-count {
        display: inline-grid;
        place-items: center;
        min-width: 19px;
        height: 19px;
        padding: 0 5px;
        border-radius: 999px;
        background: var(--review-warning-soft);
        border: 1px solid rgba(245, 158, 11, .25);
        color: var(--review-warning);
        font-size: .64rem;
        font-weight: 900;
    }

    .saved-observations {
        display: grid;
        gap: 7px;
        margin-bottom: 8px;
    }

    .saved-observation {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 10px;
        background: var(--review-warning-soft);
        border: 1px solid rgba(245, 158, 11, .16);
        border-left: 3px solid var(--review-warning);
        border-radius: 9px;
    }

    .saved-observation p {
        margin: 0;
        color: var(--review-text);
        font-size: .84rem;
        line-height: 1.45;
        white-space: pre-wrap;
    }

    .delete-observation {
        width: 27px;
        height: 27px;
        border: 1px solid rgba(239, 68, 68, .18);
        border-radius: 7px;
        background: var(--review-danger-soft);
        color: var(--review-danger);
        cursor: pointer;
        font-size: 1rem;
        flex: 0 0 auto;
    }

    .delete-observation.resolve-observation {
        border-color: rgba(16, 185, 129, .24);
        background: var(--review-success-soft);
        color: var(--review-success);
        font-weight: 900;
    }

    .summary-revision-badge {
        display: inline-flex;
        margin-bottom: 5px;
        padding: 2px 7px;
        border: 1px solid rgba(245, 158, 11, .2);
        border-radius: 999px;
        background: var(--review-warning-soft);
        color: var(--review-warning);
        font-size: .65rem;
        font-weight: 800;
    }

    .observation-editor {
        display: flex;
        align-items: stretch;
        gap: 7px;
    }

    .observation-editor textarea {
        flex: 1;
        resize: vertical;
        min-height: 46px;
        max-height: 150px;
        padding: 10px 11px;
        border-radius: 9px;
        font: inherit;
        font-size: .86rem;
        outline: none;
    }

    .observation-editor textarea:focus,
    #general-observation:focus {
        border-color: var(--review-primary);
        box-shadow: 0 0 0 3px var(--review-primary-soft);
    }

    .save-observation {
        width: 42px;
        border: 0;
        border-radius: 9px;
        background: var(--review-primary);
        color: #fff;
        font-size: 1.05rem;
        font-weight: 900;
        cursor: pointer;
    }

    .save-observation:hover {
        background: var(--review-primary-hover);
    }

    .save-observation:disabled,
    .delete-observation:disabled {
        opacity: .55;
        cursor: wait;
    }

    .add-observation {
        margin-top: 5px;
        padding: 5px 2px;
        border: 0;
        background: transparent;
        color: var(--review-primary);
        font-size: .8rem;
        font-weight: 800;
        cursor: pointer;
    }

    .decision-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 370px;
        gap: 18px;
        align-items: start;
    }

    .summary-total {
        text-align: center;
        color: var(--review-muted);
        font-size: .75rem;
    }

    .summary-total strong {
        display: block;
        color: var(--review-text);
        font-size: 1.35rem;
    }

    .summary-section {
        padding: 13px 0;
        border-top: 1px solid var(--review-border);
    }

    .summary-section h4 {
        margin: 0 0 8px;
        display: flex;
        align-items: center;
        gap: 7px;
        color: var(--review-text);
    }

    .summary-section h4 span {
        min-width: 19px;
        height: 19px;
        display: inline-grid;
        place-items: center;
        background: var(--review-warning-soft);
        border: 1px solid rgba(245, 158, 11, .24);
        color: var(--review-warning);
        border-radius: 999px;
        font-size: .64rem;
    }

    .summary-observation {
        padding: 8px 10px;
        margin-top: 6px;
        background: var(--review-warning-soft);
        border-left: 3px solid var(--review-warning);
        border-radius: 5px;
    }

    .summary-observation strong {
        display: block;
        margin-bottom: 3px;
        color: var(--review-text);
        font-size: .78rem;
    }

    .summary-observation p {
        margin: 0;
        color: var(--review-muted);
        white-space: pre-wrap;
    }

    .empty-observations {
        padding: 20px;
        text-align: center;
        color: var(--review-muted);
        background: var(--review-surface-soft);
        border-radius: 11px;
    }

    .decision-panel {
        position: sticky;
        top: 86px;
        background: var(--review-surface);
        border: 1px solid var(--review-border-strong);
        border-radius: 16px;
        padding: 18px;
        color: var(--review-text);
        box-shadow: var(--review-shadow);
    }

    .decision-status {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px 12px;
        padding-bottom: 14px;
        margin-bottom: 14px;
        border-bottom: 1px solid var(--review-border);
    }

    .decision-status span {
        color: var(--review-muted);
    }

    .decision-status strong {
        color: var(--review-text);
    }

    #decision-form label {
        display: block;
        margin-bottom: 6px;
        color: var(--review-text);
        font-weight: 800;
    }

    #general-observation {
        width: 100%;
        min-height: 120px;
        padding: 11px;
        border-radius: 10px;
        resize: vertical;
        font: inherit;
        outline: none;
    }

    .field-help {
        margin: 7px 0 15px;
        color: var(--review-muted);
        font-size: .74rem;
        line-height: 1.45;
    }

    .decision-actions {
        display: grid;
        gap: 9px;
    }

    .decision-button {
        display: flex;
        gap: 11px;
        align-items: center;
        width: 100%;
        text-align: left;
        padding: 11px 12px;
        border-radius: 11px;
        cursor: pointer;
        background: var(--review-surface-soft);
        color: var(--review-text);
    }

    .decision-button > span {
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        font-weight: 900;
        flex: 0 0 auto;
    }

    .decision-button strong,
    .decision-button small {
        display: block;
    }

    .decision-button small {
        margin-top: 2px;
        color: var(--review-muted);
        font-weight: 400;
    }

    .decision-button.approve {
        border: 1px solid var(--review-primary-border);
    }

    .decision-button.approve > span {
        background: var(--review-primary-soft);
        color: var(--review-primary);
    }

    .decision-button.approve strong {
        color: var(--review-primary);
    }

    .decision-button.changes {
        border: 1px solid rgba(245, 158, 11, .28);
    }

    .decision-button.changes > span {
        background: var(--review-warning-soft);
        color: var(--review-warning);
    }

    .decision-button.changes strong {
        color: var(--review-warning);
    }

    .decision-button.reject {
        border: 1px solid rgba(239, 68, 68, .26);
    }

    .decision-button.reject > span {
        background: var(--review-danger-soft);
        color: var(--review-danger);
    }

    .decision-button.reject strong {
        color: var(--review-danger);
    }

    .decision-button:disabled {
        opacity: .42;
        cursor: not-allowed;
    }

    .history-item {
        display: flex;
        gap: 11px;
    }

    .history-marker {
        width: 9px;
        height: 9px;
        margin-top: 6px;
        border-radius: 50%;
        background: var(--review-primary);
        flex: 0 0 auto;
    }

    .history-item strong {
        color: var(--review-text);
    }

    .history-item span,
    .history-item small {
        display: block;
        color: var(--review-muted);
    }

    .history-item p {
        margin: 5px 0;
        color: var(--review-muted);
    }


    .history-shell {
        margin-top: 20px;
        width: 100%;
        border: 1px solid var(--review-border-strong);
        border-radius: 16px;
        background: var(--review-surface);
        box-shadow: var(--review-shadow);
        overflow: hidden;
        color: var(--review-text);
    }

    .history-shell > summary,
    .history-entry > summary {
        list-style: none;
    }

    .history-shell > summary::-webkit-details-marker,
    .history-entry > summary::-webkit-details-marker {
        display: none;
    }

    .history-shell-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 17px 18px;
        cursor: pointer;
        user-select: none;
    }

    .history-shell-summary:hover {
        background: var(--review-surface-soft);
    }

    .history-shell-title,
    .history-entry-main,
    .history-entry-side,
    .history-entry-title-row,
    .history-specific-topline {
        display: flex;
        align-items: center;
    }

    .history-shell-title {
        gap: 12px;
        min-width: 0;
    }

    .history-shell-icon {
        display: grid;
        place-items: center;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        border-radius: 11px;
        background: var(--review-primary-soft);
        color: var(--review-primary);
        font-size: 1.05rem;
        font-weight: 900;
    }

    .history-shell-title strong,
    .history-shell-title small {
        display: block;
    }

    .history-shell-title strong {
        color: var(--review-text);
        font-size: .94rem;
    }

    .history-shell-title small {
        margin-top: 2px;
        color: var(--review-muted);
        font-size: .74rem;
        font-weight: 600;
    }

    .history-shell-toggle,
    .history-detail-toggle {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: var(--review-primary);
        font-size: .78rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .history-shell-close-text {
        display: none;
    }

    .history-shell[open] .history-shell-open-text {
        display: none;
    }

    .history-shell[open] .history-shell-close-text {
        display: inline;
    }

    .history-chevron {
        display: inline-block;
        transition: transform .16s ease;
        font-size: 1rem;
        line-height: 1;
    }

    .history-shell[open] > .history-shell-summary .history-chevron,
    .history-entry[open] > .history-entry-summary .history-chevron {
        transform: rotate(180deg);
    }

    .history-shell-body {
        display: grid;
        gap: 9px;
        padding: 0 18px 18px;
        border-top: 1px solid var(--review-border);
    }

    .history-entry {
        border: 1px solid var(--review-border);
        border-radius: 12px;
        background: var(--review-surface-soft);
        overflow: hidden;
    }

    .history-entry:first-child {
        margin-top: 14px;
    }

    .history-entry-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 13px 14px;
        cursor: pointer;
    }

    .history-entry-summary:hover {
        background: rgba(148, 163, 184, .045);
    }

    .history-entry-main {
        gap: 11px;
        min-width: 0;
    }

    .history-entry-dot {
        width: 9px;
        height: 9px;
        flex: 0 0 9px;
        border-radius: 50%;
        background: var(--review-info);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .1);
    }

    .history-entry-dot--success {
        background: var(--review-success);
        box-shadow: 0 0 0 4px var(--review-success-soft);
    }

    .history-entry-dot--warning {
        background: var(--review-warning);
        box-shadow: 0 0 0 4px var(--review-warning-soft);
    }

    .history-entry-dot--danger {
        background: var(--review-danger);
        box-shadow: 0 0 0 4px var(--review-danger-soft);
    }

    .history-entry-dot--muted {
        background: var(--review-subtle);
        box-shadow: 0 0 0 4px rgba(148, 163, 184, .08);
    }

    .history-entry-copy {
        min-width: 0;
    }

    .history-entry-title-row {
        flex-wrap: wrap;
        gap: 7px;
    }

    .history-entry-title-row strong {
        color: var(--review-text);
        font-size: .86rem;
    }

    .history-revision-badge,
    .history-observation-count,
    .history-resolution-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: .67rem;
        font-weight: 800;
    }

    .history-revision-badge {
        border: 1px solid var(--review-border);
        background: var(--review-surface);
        color: var(--review-muted);
    }

    .history-entry-meta {
        display: block;
        margin-top: 3px;
        color: var(--review-muted);
        font-size: .72rem;
    }

    .history-entry-side {
        justify-content: flex-end;
        gap: 10px;
        flex: 0 0 auto;
    }

    .history-observation-count {
        border: 1px solid rgba(245, 158, 11, .2);
        background: var(--review-warning-soft);
        color: var(--review-warning);
    }

    .history-entry-details {
        padding: 14px;
        border-top: 1px solid var(--review-border);
        background: var(--review-surface);
    }

    .history-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .history-detail-grid > div {
        min-width: 0;
        padding: 10px 11px;
        border: 1px solid var(--review-border);
        border-radius: 10px;
        background: var(--review-surface-soft);
    }

    .history-detail-grid span,
    .history-detail-grid strong {
        display: block;
    }

    .history-detail-grid span {
        margin-bottom: 4px;
        color: var(--review-subtle);
        font-size: .66rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .history-detail-grid strong {
        color: var(--review-text);
        font-size: .8rem;
        overflow-wrap: anywhere;
    }

    .history-general-note,
    .history-specific-observations,
    .history-empty-detail {
        margin-top: 12px;
        padding: 12px;
        border: 1px solid var(--review-border);
        border-radius: 10px;
        background: var(--review-surface-soft);
    }

    .history-general-note > span {
        display: block;
        margin-bottom: 5px;
        color: var(--review-subtle);
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .history-general-note p,
    .history-specific-item p {
        margin: 0;
        color: var(--review-text);
        line-height: 1.5;
        white-space: pre-wrap;
    }

    .history-specific-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 9px;
    }

    .history-specific-heading > strong {
        color: var(--review-text);
        font-size: .8rem;
    }

    .history-specific-heading > span {
        display: grid;
        place-items: center;
        min-width: 22px;
        height: 22px;
        padding: 0 6px;
        border-radius: 999px;
        background: var(--review-warning-soft);
        color: var(--review-warning);
        font-size: .68rem;
        font-weight: 900;
    }

    .history-specific-list {
        display: grid;
        gap: 8px;
    }

    .history-specific-item {
        padding: 10px 11px;
        border: 1px solid var(--review-border);
        border-radius: 9px;
        background: var(--review-surface);
    }

    .history-specific-topline {
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 7px;
    }

    .history-specific-topline > div > strong,
    .history-specific-topline > div > span {
        display: block;
    }

    .history-specific-topline > div > strong {
        color: var(--review-text);
        font-size: .78rem;
    }

    .history-specific-topline > div > span {
        margin-top: 2px;
        color: var(--review-muted);
        font-size: .7rem;
    }

    .history-resolution-badge.resolved {
        background: var(--review-success-soft);
        border: 1px solid var(--review-primary-border);
        color: var(--review-success);
    }

    .history-resolution-badge.pending {
        background: var(--review-warning-soft);
        border: 1px solid rgba(245, 158, 11, .2);
        color: var(--review-warning);
    }

    .history-specific-item small {
        display: block;
        margin-top: 6px;
        color: var(--review-subtle);
        font-size: .7rem;
    }

    .history-empty-detail {
        color: var(--review-muted);
        text-align: center;
        font-size: .78rem;
    }

    .stage-footer {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid var(--review-border);
    }

    .btn-primary,
    .btn-secondary {
        border-radius: 10px;
        padding: 10px 16px;
        font-weight: 800;
        cursor: pointer;
        transition: .15s ease;
    }

    .btn-primary {
        border: 1px solid var(--review-primary);
        background: var(--review-primary);
        color: #fff;
    }

    .btn-primary:hover {
        background: var(--review-primary-hover);
        border-color: var(--review-primary-hover);
    }

    .btn-secondary {
        border: 1px solid var(--review-border-strong);
        background: var(--review-surface);
        color: var(--review-text);
    }

    .btn-secondary:hover {
        background: var(--review-surface-soft);
    }

    .muted {
        color: var(--review-muted) !important;
    }

    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        width: min(390px, calc(100vw - 32px));
        z-index: 99999;
        display: grid;
        gap: 9px;
        pointer-events: none;
    }

    .revision-toast {
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 13px 14px;
        border-radius: 12px;
        background: var(--review-surface-raised);
        border: 1px solid var(--review-border);
        color: var(--review-text);
        box-shadow: 0 14px 35px rgba(0, 0, 0, .18);
        animation: toast-in .18s ease-out;
    }

    .revision-toast.success {
        border-left: 4px solid var(--review-primary);
    }

    .revision-toast.error {
        border-left: 4px solid var(--review-danger);
    }

    .revision-toast.info {
        border-left: 4px solid var(--review-info);
    }

    .revision-toast strong {
        display: block;
        margin-bottom: 2px;
        color: var(--review-text);
    }

    .revision-toast p {
        margin: 0;
        color: var(--review-muted);
        font-size: .85rem;
    }

    @keyframes toast-in {
        from {
            transform: translateY(-10px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @media (max-width: 1220px) {
        .stage-navigation {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .decision-layout {
            grid-template-columns: 1fr;
        }

        .decision-panel {
            position: static;
        }
    }

    @media (max-width: 820px) {
        .revision-page {
            padding: 14px;
        }

        .revision-header {
            display: block;
        }

        .revision-status {
            margin-top: 15px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .stage-navigation {
            position: static;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .presentation-grid {
            grid-template-columns: 1fr;
        }

        .field-row {
            grid-template-columns: 1fr 1fr;
        }

        .product-header,
        .form-header,
        .session-header,
        .summary-heading,
        .requested-item-header,
        .card-intro {
            display: block;
        }

        .price-box {
            text-align: left;
            margin-top: 12px;
        }

        .summary-total {
            text-align: left;
            margin-top: 10px;
        }

        .requested-data-overview {
            grid-template-columns: 34px minmax(0, 1fr);
        }

        .overview-count {
            grid-column: 2;
            justify-self: start;
        }

        .rule-row {
            display: block;
        }

        .rule-values {
            margin-top: 9px;
        }

        .rule-values > span {
            text-align: left;
        }
    }

    @media (max-width: 540px) {
        .info-grid,
        .requested-fields {
            grid-template-columns: 1fr;
        }

        .revision-status {
            grid-template-columns: 1fr;
        }

        .revision-status > div {
            border-right: 0;
            border-bottom: 1px solid var(--review-border);
        }

        .revision-status > div:last-child {
            border-bottom: 0;
        }

        .stage-navigation {
            grid-template-columns: 1fr;
        }

        .stage-button {
            min-height: 42px;
        }

        .price-item {
            grid-template-columns: 1fr 1fr;
        }

        .price-item > div:first-child {
            grid-column: 1 / -1;
        }

        .field-row {
            grid-template-columns: 1fr;
        }

        .simulator-result {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .toast-container {
            top: 12px;
            right: 12px;
            width: calc(100vw - 24px);
        }

        .stage-footer {
            align-items: stretch;
        }

        .stage-footer .btn-primary,
        .stage-footer .btn-secondary {
            flex: 1;
        }
    }



    .review-stage > .observation-box {
        margin: 18px 0;
    }

    .review-stage > .observation-box + .content-card,
    .review-stage > .observation-box + .programming-block {
        margin-top: 18px;
    }

    .product-list,
    .price-products {
        display: grid;
        gap: 9px;
        margin-bottom: 18px;
    }

    .product-list-row,
    .price-product-detail {
        overflow: hidden;
        background: var(--review-surface);
        border: 1px solid var(--review-border);
        border-radius: 13px;
        box-shadow: var(--review-shadow);
    }

    .product-list-row > summary,
    .price-product-detail > summary {
        display: grid;
        align-items: center;
        gap: 14px;
        padding: 11px 13px;
        cursor: pointer;
        list-style: none;
        color: var(--review-text);
    }

    .product-list-row > summary {
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .price-product-detail > summary {
        grid-template-columns: minmax(0, 1fr) auto;
    }

    .product-list-row > summary::-webkit-details-marker,
    .price-product-detail > summary::-webkit-details-marker {
        display: none;
    }

    .product-list-row[open] > summary,
    .price-product-detail[open] > summary {
        background: var(--review-surface-soft);
        border-bottom: 1px solid var(--review-border);
    }

    .product-list-main,
    .price-product-main {
        min-width: 0;
    }

    .product-list-main strong,
    .price-product-main strong {
        display: block;
        margin-top: 2px;
        color: var(--review-text);
        font-size: .88rem;
    }

    .product-list-main small,
    .price-product-main small {
        display: block;
        margin-top: 2px;
        color: var(--review-muted);
        font-size: .72rem;
        line-height: 1.35;
    }

    .product-list-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--review-muted);
        font-size: .72rem;
        white-space: nowrap;
    }

    .product-list-meta b {
        color: var(--review-primary);
        font-size: .72rem;
    }

    .product-list-expanded,
    .price-product-expanded {
        padding: 12px 13px 13px;
    }

    .product-list-description {
        margin: 10px 0 0;
        color: var(--review-muted);
        font-size: .78rem;
        line-height: 1.5;
    }

    .product-basic-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }

    .product-basic-grid > div {
        min-width: 0;
        padding: 8px 10px;
        border: 1px solid var(--review-border);
        border-radius: 9px;
        background: var(--review-surface-soft);
    }

    .product-basic-grid span,
    .product-basic-grid strong {
        display: block;
    }

    .product-basic-grid span {
        color: var(--review-muted);
        font-size: .66rem;
    }

    .product-basic-grid strong {
        margin-top: 2px;
        color: var(--review-text);
        font-size: .76rem;
    }

    .price-product-summary-values {
        display: grid;
        grid-template-columns: repeat(2, auto) auto;
        gap: 14px;
        align-items: center;
        color: var(--review-muted);
        font-size: .67rem;
        white-space: nowrap;
    }

    .price-product-summary-values span,
    .price-product-summary-values b {
        display: block;
    }

    .price-product-summary-values b {
        margin-top: 1px;
        color: var(--review-text);
        font-size: .82rem;
    }

    .price-product-summary-values em {
        color: var(--review-primary);
        font-size: .7rem;
        font-style: normal;
        font-weight: 900;
    }

    .price-basic-grid {
        margin-bottom: 12px;
    }

    .pricing-variant-summary {
        margin-top: 0;
        padding-top: 0;
        border-top: 0;
    }

    .promotions-section {
        margin-top: 20px;
    }

    .neutral-notice {
        border: 1px solid var(--review-border);
        background: var(--review-surface-soft);
    }


    /* Ajustes de densidad: la revisión debe mostrar mucho sin abrumar. */
    .stage-count {
        min-width: 16px;
        height: 16px;
        padding: 0 4px;
        border-color: var(--review-border-strong);
        background: transparent;
        color: var(--review-warning);
        font-size: .6rem;
    }

    .compact-section-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 10px;
    }

    .compact-section-heading h4,
    .compact-section-heading p {
        margin: 0;
    }

    .compact-section-heading p {
        margin-top: 3px;
        color: var(--review-muted);
        font-size: .8rem;
        line-height: 1.45;
    }

    .compact-section-heading > span {
        flex: 0 0 auto;
        padding: 4px 8px;
        border: 1px solid var(--review-border);
        border-radius: 999px;
        background: var(--review-surface-soft);
        color: var(--review-muted);
        font-size: .7rem;
        font-weight: 800;
    }

    .variant-groups {
        display: grid;
        gap: 7px;
    }

    .variant-group {
        overflow: hidden;
        border: 1px solid var(--review-border);
        border-radius: 10px;
        background: var(--review-surface-soft);
    }

    .variant-group > summary {
        min-height: 42px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 11px;
        cursor: pointer;
        list-style: none;
        color: var(--review-text);
    }

    .variant-group > summary::-webkit-details-marker {
        display: none;
    }

    .variant-group[open] > summary {
        border-bottom: 1px solid var(--review-border);
    }

    .variant-group-title {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .variant-group-title strong {
        color: var(--review-text);
        font-size: .82rem;
    }

    .variant-group-title small {
        color: var(--review-muted);
        font-size: .7rem;
        font-weight: 700;
    }

    .variant-group-action {
        color: var(--review-primary);
        font-size: .7rem;
        font-weight: 900;
    }

    .option-compact-list {
        padding: 7px;
    }

    .option-compact-row {
        min-height: 34px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 6px 8px;
        border-radius: 7px;
    }

    .option-compact-row + .option-compact-row {
        border-top: 1px solid var(--review-border);
        border-radius: 0;
    }

    .option-compact-row > strong {
        color: var(--review-text);
        font-size: .78rem;
    }

    .option-adjustments {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .option-adjustments span {
        color: var(--review-muted);
        font-size: .68rem;
    }

    .option-adjustments b {
        margin-left: 3px;
        color: var(--review-text);
        font-size: .72rem;
    }

    .option-adjustments .no-adjustment {
        color: var(--review-subtle);
    }

    .chunk-hidden,
    .session-page-hidden {
        display: none !important;
    }

    .show-more-options {
        width: 100%;
        margin-top: 5px;
        padding: 7px 9px;
        border: 1px dashed var(--review-border-strong);
        border-radius: 8px;
        background: transparent;
        color: var(--review-primary);
        cursor: pointer;
        font-size: .72rem;
        font-weight: 900;
    }

    .variant-simulator {
        padding: 12px;
    }

    .simulator-heading h4 {
        font-size: .86rem;
    }

    .simulator-heading p {
        font-size: .75rem;
    }

    .simulator-compact-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 14px;
        align-items: end;
        margin-top: 10px;
    }

    .simulator-controls {
        margin-top: 0;
        grid-template-columns: repeat(auto-fit, minmax(135px, 1fr));
        gap: 8px;
    }

    .simulator-controls select {
        padding: 7px 9px;
        font-size: .78rem;
    }

    .simulator-controls label > span {
        margin-bottom: 4px;
        font-size: .7rem;
    }

    .simulator-result.compact-result {
        min-width: 190px;
        margin: 0;
        padding: 8px 10px;
        border: 1px solid var(--review-border);
        border-radius: 9px;
        background: var(--review-surface);
        gap: 16px;
    }

    .simulator-result.compact-result strong {
        font-size: .95rem;
    }

    .compact-notice-details {
        margin-top: 12px;
    }

    .compact-guidance {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 14px;
        padding: 10px 12px;
        border: 1px solid var(--review-primary-border);
        border-radius: 11px;
        background: var(--review-primary-soft);
    }

    .compact-guidance strong {
        flex: 0 0 auto;
        color: var(--review-primary);
        font-size: .78rem;
    }

    .compact-guidance p {
        margin: 0;
        color: var(--review-muted);
        font-size: .78rem;
        line-height: 1.45;
    }

    .commercial-card .card-intro {
        margin-bottom: 12px;
    }

    .programming-block {
        margin-bottom: 18px !important;
    }

    .sessions-intro {
        margin-bottom: 12px;
    }

    .compact-session-list {
        gap: 7px;
        margin-bottom: 0;
    }

    .session-compact {
        overflow: hidden;
        border: 1px solid var(--review-border);
        border-radius: 10px;
        background: var(--review-surface-soft);
    }

    .session-compact > summary {
        min-height: 48px;
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) minmax(320px, 1fr);
        gap: 14px;
        align-items: center;
        padding: 8px 11px;
        cursor: pointer;
        list-style: none;
    }

    .session-compact > summary::-webkit-details-marker {
        display: none;
    }

    .session-compact[open] > summary {
        border-bottom: 1px solid var(--review-border);
        background: var(--review-surface);
    }

    .session-summary-main strong,
    .session-summary-main span {
        display: block;
    }

    .session-summary-main strong {
        color: var(--review-text);
        font-size: .8rem;
    }

    .session-summary-main span {
        margin-top: 2px;
        color: var(--review-muted);
        font-size: .69rem;
    }

    .session-summary-meta {
        display: grid;
        grid-template-columns: minmax(120px, 1.5fr) minmax(80px, .7fr) auto;
        gap: 10px;
        align-items: center;
        color: var(--review-muted);
        font-size: .7rem;
        text-align: right;
    }

    .session-summary-meta b {
        color: var(--review-primary);
        font-size: .68rem;
    }

    .session-expanded {
        padding: 10px 11px 11px;
    }

    .session-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
    }

    .session-detail-grid > div {
        padding: 8px;
        border: 1px solid var(--review-border);
        border-radius: 8px;
        background: var(--review-surface);
    }

    .session-detail-grid span,
    .session-detail-grid strong {
        display: block;
    }

    .session-detail-grid span {
        color: var(--review-muted);
        font-size: .67rem;
    }

    .session-detail-grid strong {
        margin-top: 2px;
        color: var(--review-text);
        font-size: .75rem;
    }

    .compact-resource-list {
        margin-top: 9px;
    }

    .session-pager {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
    }

    .session-pager > span {
        min-width: 74px;
        color: var(--review-muted);
        font-size: .72rem;
        text-align: center;
    }

    .compact-button {
        padding: 7px 10px;
        font-size: .72rem;
    }

    .observation-box {
        padding: 10px 11px;
    }

    .observation-header {
        margin-bottom: 7px;
    }

    .observation-header h4 {
        font-size: .78rem;
    }

    .saved-observation {
        padding: 7px 9px;
    }

    .saved-observation p {
        font-size: .76rem;
    }

    .observation-editor textarea {
        min-height: 40px;
        max-height: 120px;
        padding: 8px 9px;
        font-size: .78rem;
    }

    .save-observation {
        width: 40px;
        min-height: 40px;
    }

    .add-observation {
        margin-top: 5px;
        padding: 4px 2px;
        font-size: .72rem;
    }

    @media (max-width: 980px) {
        .simulator-compact-row {
            grid-template-columns: 1fr;
        }

        .simulator-result.compact-result {
            min-width: 0;
            justify-content: flex-start;
        }

        .session-compact > summary {
            grid-template-columns: 1fr;
            gap: 5px;
        }

        .session-summary-meta {
            text-align: left;
            grid-template-columns: 1fr 1fr auto;
        }

        .session-detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .compact-section-heading,
        .compact-guidance {
            display: block;
        }

        .compact-section-heading > span {
            display: inline-flex;
            margin-top: 7px;
        }

        .compact-guidance p {
            margin-top: 4px;
        }

        .variant-group-title {
            display: block;
        }

        .variant-group-title small {
            display: block;
            margin-top: 2px;
        }

        .option-compact-row {
            align-items: flex-start;
        }

        .option-adjustments {
            justify-content: flex-end;
        }

        .session-summary-meta,
        .session-detail-grid {
            grid-template-columns: 1fr;
        }

        .session-summary-meta {
            gap: 3px;
        }

        .session-pager {
            justify-content: space-between;
        }
    }

    @media (max-width: 1220px) {
        .price-product-summary-values {
            gap: 9px;
        }
    }

    @media (max-width: 820px) {
        .revision-status {
            justify-content: flex-start;
            margin-top: 15px;
        }

        .revision-status > div {
            flex: 1 1 120px;
        }

        .stage-navigation {
            display: flex;
            position: static;
            gap: 7px;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 9px;
            scroll-snap-type: x proximity;
        }

        .stage-button {
            flex: 0 0 auto;
            width: auto;
            min-width: 150px;
            scroll-snap-align: start;
        }

        .product-list-row > summary,
        .price-product-detail > summary {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .product-list-meta,
        .price-product-summary-values {
            justify-content: flex-start;
        }

        .price-product-summary-values {
            grid-template-columns: repeat(2, minmax(90px, 1fr)) auto;
            white-space: normal;
        }

        .product-basic-grid {
            grid-template-columns: 1fr;
        }

        .simulator-compact-row {
            grid-template-columns: 1fr;
        }

        .simulator-result.compact-result {
            min-width: 0;
        }
    }

    @media (max-width: 540px) {
        .revision-status {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
        }

        .revision-status > div {
            min-width: 0;
            padding: 9px 10px;
            border: 1px solid var(--review-border);
            border-radius: 10px;
        }

        .stage-navigation {
            display: flex;
            grid-template-columns: none;
        }

        .stage-button {
            min-width: 138px;
            min-height: 42px;
        }

        .price-product-summary-values {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .price-product-summary-values em {
            grid-column: 1 / -1;
        }
    }


    @media (max-width: 820px) {
        .history-entry-summary {
            align-items: flex-start;
            flex-direction: column;
        }

        .history-entry-side {
            width: 100%;
            justify-content: space-between;
            padding-left: 20px;
        }

        .history-detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 560px) {
        .history-shell-summary {
            align-items: flex-start;
            flex-direction: column;
        }

        .history-shell-toggle {
            padding-left: 48px;
        }

        .history-shell-body {
            padding-left: 10px;
            padding-right: 10px;
        }

        .history-detail-grid {
            grid-template-columns: 1fr;
        }

        .history-entry-side {
            padding-left: 0;
            flex-wrap: wrap;
        }

        .history-specific-topline {
            flex-direction: column;
        }
    }

</style>

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
            button.classList.toggle(
                'active',
                buttonIndex === currentStageIndex
            );
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

    async function submitDecision(type) {
        const textarea = document.getElementById(
            'general-observation'
        );

        const observation = textarea.value.trim();
        const total = getTotalCount();

        let url = null;
        let confirmation = null;

        if (type === 'approve') {
            if (total > 0) {
                showToast(
                    'No puedes aprobar mientras existan observaciones pendientes.',
                    'error'
                );
                return;
            }

            url = approveUrl;
            confirmation = '¿Aprobar esta actividad?';
        }

        if (type === 'changes') {
            if (total === 0 && observation === '') {
                showToast(
                    'Agrega una observación general o al menos una observación específica para solicitar cambios.',
                    'error'
                );
                textarea.focus();
                return;
            }

            url = changesUrl;
            confirmation = '¿Enviar estas correcciones al creador?';
        }

        if (type === 'reject') {
            if (total === 0 && observation === '') {
                showToast(
                    'Debes justificar el rechazo con una observación general o específica.',
                    'error'
                );
                textarea.focus();
                return;
            }

            url = rejectUrl;
            confirmation = '¿Rechazar esta actividad?';
        }

        if (!url) {
            return;
        }

        if (!window.confirm(confirmation)) {
            return;
        }

        const buttons = document.querySelectorAll(
            '[data-decision]'
        );

        buttons.forEach(function (button) {
            button.disabled = true;
        });

        const payload = new FormData();

        if (observation !== '') {
            payload.append('observacion', observation);
        }

        try {
            const data = await sendRequest(
                url,
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: payload
                }
            );

            showToast(
                data.message || 'Operación completada.',
                'success'
            );

            if (data.redirect) {
                window.setTimeout(function () {
                    window.location.href = data.redirect;
                }, 650);
            }
        } catch (error) {
            buttons.forEach(function (button) {
                button.disabled = false;
            });

            updateApproveState(getTotalCount());

            showToast(error.message, 'error');
        }
    }

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