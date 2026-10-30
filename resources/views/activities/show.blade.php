@extends('layouts.public')

@section('title', 'SIDAN | ' . $actividad['titulo'])

@section('content')
    @php
        $accionActiva = $accionSolicitada ?: session('accion_actividad');

        $actividadModelo->loadMissing('items.sesiones');

        $sesiones = $actividadModelo->sesiones->sortBy('fecha_inicio')->values();
        $items = $actividadModelo->items->sortBy('orden')->values();
        $itemsTodaActividad = $items->filter(fn ($item) => ($item->alcance_sesiones ?? 'actividad') === 'actividad')->values();
        $itemsPorSesion = [];
        foreach ($sesiones as $sesionCatalogo) {
            $itemsPorSesion[(int) $sesionCatalogo->id_sesion] = $items->filter(
                fn ($item) => ($item->alcance_sesiones ?? 'actividad') !== 'actividad'
                    && $item->sesiones->contains(fn ($sesionItem) => (int) $sesionItem->id_sesion === (int) $sesionCatalogo->id_sesion)
            )->values();
        }
        $promociones = $actividadModelo->promociones->values();
        $mediosActividad = $actividadModelo->medios
            ->whereNull('id_item_actividad')
            ->whereNull('id_sesion')
            ->sortBy('orden')
            ->values();

        $urlMedio = function ($url) {
            if (!$url) {
                return null;
            }

            $ruta = str_replace('\\', '/', trim($url));

            if (
                str_starts_with($ruta, 'http://') ||
                str_starts_with($ruta, 'https://') ||
                str_starts_with($ruta, 'data:')
            ) {
                return $ruta;
            }

            $ruta = ltrim($ruta, '/');

            if (str_starts_with($ruta, 'public/')) {
                $ruta = substr($ruta, 7);
            }

            return str_starts_with($ruta, 'storage/') ? asset($ruta) : asset('storage/' . $ruta);
        };

        $panelInicial = match ($accionActiva) {
            'comprar' => $itemsTodaActividad->isNotEmpty() ? 'productos' : ($sesiones->isNotEmpty() ? 'cronograma' : 'productos'),
            'participar' => $sesiones->isNotEmpty() ? 'cronograma' : 'resumen',
            default => 'resumen',
        };

        $descripcionCompleta = trim((string) ($actividadModelo->descripcion ?: $actividad['descripcion']));

        $sesionesVista = $sesiones
            ->map(function ($registroSesion) {
                $inicio = $registroSesion->fecha_inicio;
                $fin = $registroSesion->fecha_fin;
                $esVirtual = filled($registroSesion->enlace_acceso);

                return [
                    'id_sesion' => (int) $registroSesion->id_sesion,
                    'nombre' => (string) $registroSesion->nombre,
                    'virtual' => $esVirtual,
                    'modalidad' => $esVirtual ? 'Virtual' : 'Presencial',
                    'lugar' => $esVirtual
                        ? 'Sesión virtual'
                        : ($registroSesion->espacio?->nombre ?:
                        ($registroSesion->ubicacion ?:
                        'Lugar por definir')),
                    'obligatoria' => (bool) $registroSesion->obligatoria,
                    'clave_dia' => $inicio ? $inicio->format('Y-m-d') : 'sin-fecha',
                    'mes' => $inicio ? $inicio->copy()->locale('es')->translatedFormat('M') : '',
                    'dia' => $inicio ? $inicio->format('d') : '',
                    'dia_semana' => $inicio ? $inicio->copy()->locale('es')->translatedFormat('l') : 'Sin fecha',
                    'fecha_corta' => $inicio ? $inicio->copy()->locale('es')->translatedFormat('d M Y') : 'Sin fecha',
                    'hora_inicio' => $inicio ? $inicio->format('g:i a') : '',
                    'hora_fin' => $fin ? $fin->format('g:i a') : '',
                    'fecha' => $inicio
                        ? $inicio->copy()->locale('es')->translatedFormat('d \d\e F Y')
                        : 'Fecha por definir',
                    'cupo' => $registroSesion->cupo !== null ? $registroSesion->cupo . ' personas' : 'Según actividad',
                    'reserva' => $registroSesion->requiere_reserva ? 'Requiere reserva' : 'No requiere reserva',
                ];
            })
            ->values();

        $sesionesPorDia = $sesionesVista->groupBy('clave_dia');
        $diaInicialCronograma = $sesionesPorDia->keys()->first();

        /*
        |--------------------------------------------------------------------------
        | Estado de inscripción del usuario actual
        |--------------------------------------------------------------------------
        |
        | Este bloque únicamente consulta si el usuario ya posee una inscripción
        | confirmada para esta actividad.
        |
        | No modifica la lógica existente de inscripción.
        |
        */

        $usuarioInscritoActividad = false;

        if (auth()->check()) {
            $usuarioActual = auth()->user();

            $correoActual = strtolower(trim((string) $usuarioActual->correo));

            $usuarioInscritoActividad = \Illuminate\Support\Facades\DB::table('tbl_inscripciones')
                ->where('id_actividad', $actividadModelo->id_actividad)
                ->whereIn('estado', ['confirmada', 'confirmada_prueba'])
                ->where(function ($query) use ($usuarioActual, $correoActual) {
                    $query->where('id_usuario', $usuarioActual->id_usuario);

                    if ($correoActual !== '') {
                        $query->orWhereRaw('lower(correo_participante) = ?', [$correoActual]);
                    }
                })
                ->exists();
        }

        $compraBloqueadaPorInscripcion = $actividadModelo->requiereInscripcionGeneral() && !$usuarioInscritoActividad;
    @endphp

    @push('styles')
        <style>
            /* Diseño visual exclusivo del detalle público de actividad. */
            #detalleActividad .sidan-panel:not(.hidden) {
                animation: sidanEntrada .24s ease-out both;
            }

            @keyframes sidanEntrada {
                from {
                    opacity: .45;
                    transform: translateY(5px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            #detalleActividad .sidan-producto {
                transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
            }

            #detalleActividad .sidan-producto:hover {
                transform: translateY(-3px);
                box-shadow: 0 14px 35px rgba(15, 45, 91, .12);
                border-color: #22c55e;
            }

            #detalleActividad .sidan-tab {
                transition: background .17s ease, color .17s ease;
            }

            #detalleActividad .sidan-tab:focus-visible,
            #detalleActividad [data-agregar-configuracion]:focus-visible {
                outline: 2px solid currentColor;
                outline-offset: 2px;
            }

            @media (prefers-reduced-motion: reduce) {
                #detalleActividad .sidan-panel:not(.hidden) {
                    animation: none;
                }

                #detalleActividad .sidan-producto {
                    transition: none;
                }

                #detalleActividad .sidan-producto:hover {
                    transform: none;
                }
            }
            /* En oscuro, las tarjetas siguen el contraste azul marino del resto de SIDAN. */
            .dark #detalleActividad .sidan-producto:hover {
                box-shadow: 0 14px 35px rgba(0, 0, 0, .28);
                border-color: #22c55e;
            }
        </style>
    @endpush

    @include('partials.public-header')

    <main class="min-h-screen bg-[#f6f8fb] text-slate-900 dark:bg-sidan-950 dark:text-slate-100" id="detalleActividad"
        data-panel-inicial="{{ $panelInicial }}">
        <section class="relative overflow-hidden bg-sidan-950">
            <div class="absolute inset-0">
                <img src="{{ $actividad['imagen'] }}" alt="{{ $actividad['titulo'] }}"
                    class="h-full w-full object-cover opacity-55">
                <div class="absolute inset-0 bg-gradient-to-r from-sidan-950 via-sidan-950/95 to-sidan-950/55"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-sidan-950 via-transparent to-transparent"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <a href="{{ route('activities.index') }}"
                    class="inline-flex items-center gap-2 text-sm font-black text-white/70 transition hover:text-zinc-200">
                    ← Volver a actividades
                </a>

                <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px] lg:items-end">
                    <div class="min-w-0">
                        <div class="flex flex-wrap gap-2">
                            <span
                                class="rounded-full bg-sidan-500 px-3 py-1.5 text-xs font-black uppercase tracking-wider text-white">{{ $actividad['categoria'] }}</span>
                            <span
                                class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-black text-white">{{ $actividad['estado'] }}</span>
                        </div>

                        <h1
                            class="mt-5 max-w-4xl text-4xl font-black leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl">
                            {{ $actividad['titulo'] }}
                        </h1>

                        <p class="mt-5 max-w-3xl text-base leading-7 text-white/75 sm:text-lg">
                            {{ $actividad['descripcion'] }}
                        </p>

                        <div class="mt-7 flex flex-wrap gap-3 text-sm font-bold text-white/85">
                            <span class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">📅
                                {{ $actividad['fecha'] }}</span>
                            <span class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">🕒
                                {{ $actividad['hora'] }}</span>
                            <span
                                class="max-w-full truncate rounded-xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">📍
                                {{ $actividad['lugar'] }}</span>
                        </div>
                    </div>

                    <div
                        class="rounded-[1.75rem] border border-white/15 bg-white/10 p-5 shadow-2xl backdrop-blur-xl sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-200">
                                    {{ $usuarioInscritoActividad ? 'Inscrito' : $actividad['accion_texto'] }}
                                </p>
                            </div>
                            @if ($promociones->isNotEmpty())
                                <button type="button" data-panel-target="promociones"
                                    class="rounded-full bg-amber-300 px-3 py-1.5 text-xs font-black text-sidan-950 transition hover:bg-amber-200">
                                    {{ $promociones->count() }} promo{{ $promociones->count() === 1 ? '' : 's' }}
                                </button>
                            @endif
                        </div>

                        @if ($actividadModelo->requiereInscripcionGeneral())

                            @if ($cuposDisponibles === 0)
                                <p class="mt-4 text-center text-sm font-bold text-red-300">
                                    Cupos agotados
                                </p>
                            @elseif ($cuposDisponibles !== null)
                                <p class="mt-4 text-center text-sm font-semibold text-zinc-200">
                                    Cupos disponibles: {{ $cuposDisponibles }}
                                </p>
                            @endif

                        @endif

                        @if ($usuarioInscritoActividad)

                            <button type="button" disabled
                                class="mt-5 flex w-full cursor-default items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-5 py-3.5 text-sm font-black text-zinc-100">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="m5 12 4 4L19 6" />
                                </svg>

                                Inscrito
                            </button>

                            <p class="mt-3 text-center text-xs leading-5 text-white/70">
                                Tu inscripción a esta actividad ya está confirmada.
                            </p>
                        @else
                            @if ($actividadModelo->requiereInscripcionGeneral() && $cuposDisponibles === 0)
                                <button type="button" disabled
                                    class="mt-5 flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-500 px-5 py-3.5 text-sm font-black text-white opacity-60">
                                    Cupos agotados
                                </button>
                            @else
                                <a href="{{ $actividad['accion_url'] }}"
                                    class="mt-5 flex w-full items-center justify-center rounded-xl bg-sidan-900 px-5 py-3.5 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-sidan-700">
                                    {{ $actividad['accion_texto'] }}
                                </a>
                            @endif

                            <p class="mt-3 text-center text-xs leading-5 text-white/65">
                                {{ $actividad['participacion_descripcion'] }}
                            </p>

                            @if ($actividad['requiere_cuenta'])
                                <p class="mt-2 text-center text-xs leading-5 text-white/55">
                                    @guest
                                        {{ $actividadModelo->requiereInscripcionGeneral() ? 'Podés inscribirte sin cuenta; SIDAN te enviará un enlace para establecer tu contraseña. Para comprar productos sí deberás iniciar sesión.' : 'Necesitarás iniciar sesión para comprar productos.' }}
                                    @else
                                        Tu cuenta está lista para continuar.
                                    @endguest
                                </p>
                            @endif

                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section
            class="sticky top-0 z-30 border-b border-zinc-200 bg-white/95 shadow-sm backdrop-blur dark:border-zinc-800 dark:bg-sidan-950/95">
            <div class="mx-auto max-w-7xl overflow-x-auto px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-max gap-2 py-3" role="tablist" aria-label="Secciones de la actividad">
                    <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black"
                        data-panel-target="resumen">Resumen</button>
                    @if ($sesiones->isNotEmpty())
                        <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black"
                            data-panel-target="cronograma">Cronograma <span
                                class="ml-1 opacity-60">{{ $sesiones->count() }}</span></button>
                    @endif
                    @if ($items->isNotEmpty())
                        <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black"
                            data-panel-target="productos">Productos <span
                                class="ml-1 opacity-60">{{ $itemsTodaActividad->count() }}</span></button>
                    @endif
                    @if ($promociones->isNotEmpty())
                        <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black"
                            data-panel-target="promociones">Promociones <span
                                class="ml-1 opacity-60">{{ $promociones->count() }}</span></button>
                    @endif
                    <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black"
                        data-panel-target="ubicacion">Ubicación</button>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="min-w-0">
                    <section class="sidan-panel" data-panel="resumen">
                        <div
                            class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                            <div class="grid gap-8 xl:grid-cols-[1fr_300px]">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">Información
                                        general de la
                                        actividad</p>
                                    <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white sm:text-3xl">Todo lo
                                        que necesitás saber</h2>
                                    <div
                                        class="mt-5 whitespace-pre-line text-base leading-8 text-slate-600 dark:text-slate-300">
                                        {{ $descripcionCompleta !== '' ? $descripcionCompleta : 'La organización todavía no ha publicado una descripción ampliada de esta actividad.' }}
                                    </div>
                                    <div class="mt-6 flex flex-wrap gap-2">
                                        @if ($sesiones->isNotEmpty())
                                            <button type="button" data-panel-target="cronograma"
                                                class="rounded-full border border-zinc-300 px-3 py-2 text-xs font-bold text-zinc-800 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-white/10">
                                                {{ $sesiones->count() }} sesiones programadas →
                                            </button>
                                        @endif
                                        @if ($items->isNotEmpty())
                                            <button type="button" data-panel-target="productos"
                                                class="rounded-full border border-zinc-300 px-3 py-2 text-xs font-bold text-zinc-800 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-white/10">
                                                {{ $itemsTodaActividad->count() }} productos generales →
                                            </button>
                                        @endif
                                    </div>

                                    @if (count($actividad['etiquetas']))
                                        <div class="mt-6 flex flex-wrap gap-2">
                                            @foreach ($actividad['etiquetas'] as $etiqueta)
                                                <a href="{{ route('activities.index', ['etiqueta' => $etiqueta]) }}"
                                                    class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">#{{ $etiqueta }}</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Fecha</p>
                                        <p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['fecha'] }}
                                            · {{ $actividad['hora'] }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Lugar</p>
                                        <p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['lugar'] }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Categoría</p>
                                        <p class="mt-1 font-black text-sidan-900 dark:text-white">
                                            {{ $actividad['categoria'] }}
                                        </p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Precio</p>
                                        <p class="mt-1 font-black text-sidan-900 dark:text-white">
                                            {{ $actividad['precio'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($mediosActividad->count() > 1)
                            <div
                                class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-6">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-sidan-500">Galería
                                        </p>
                                        <h3 class="mt-1 text-lg font-black text-sidan-900 dark:text-white">Un vistazo a la
                                            actividad</h3>
                                    </div>
                                    <span class="text-xs font-bold text-slate-400">Desliza →</span>
                                </div>
                                <div class="mt-4 flex snap-x gap-3 overflow-x-auto pb-2">
                                    @foreach ($mediosActividad as $medio)
                                        @php
                                            $imagenGaleria = $urlMedio($medio->url);
                                        @endphp
                                        @if ($imagenGaleria)
                                            <button type="button"
                                                class="sidan-galeria shrink-0 snap-start overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-white/5"
                                                data-imagen="{{ $imagenGaleria }}"
                                                data-alt="{{ $medio->texto_alternativo ?: $actividad['titulo'] }}">
                                                <img src="{{ $imagenGaleria }}"
                                                    alt="{{ $medio->texto_alternativo ?: $actividad['titulo'] }}"
                                                    class="h-32 w-52 object-cover sm:h-40 sm:w-64" loading="lazy">
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </section>

                    @include('activities.partials.cronograma')

                    @if ($items->isNotEmpty())

                        <section class="sidan-panel hidden" data-panel="productos" id="productos">
                            <div
                                class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">

                                <div
                                    class="mb-7 rounded-2xl border border-zinc-200 bg-zinc-50 px-5 py-5 dark:border-zinc-700 dark:bg-sidan-900">
                                    @if ($compraBloqueadaPorInscripcion)
                                        <div
                                            class="mb-5 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3
                                                                                                                               text-sm font-semibold text-amber-800
                                                                                                                               dark:border-amber-500/30 dark:bg-amber-500/10
                                                                                                                               dark:text-amber-300">

                                            <p class="font-black">
                                                🔒 Compra de productos bloqueada
                                            </p>

                                            <p class="mt-1 text-xs leading-5">
                                                Para comprar productos de esta actividad, primero debes
                                                completar y confirmar tu inscripción.
                                            </p>
                                        </div>
                                    @endif

                                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                                        <div>
                                            <h2 class="text-xl font-black uppercase text-sidan-900 dark:text-white">
                                                Productos y servicios generales
                                            </h2>

                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
                                                Elegí el producto, configurá sus opciones si corresponde y agregalo al
                                                carrito. La compra se confirma después.
                                            </p>
                                        </div>

                                        @auth
                                            <form id="formCompraActividad" method="POST"
                                                action="{{ route('activities.compra.store', ['actividad' => $actividadModelo->slug]) }}"
                                                class="shrink-0">
                                                @csrf

                                                <input type="hidden" name="selecciones" id="seleccionesCompra"
                                                    value="">

                                                <button type="submit" id="btnContinuarCompra" disabled
                                                    class="flex min-w-[220px] items-center justify-center gap-2 rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white transition hover:bg-sidan-600 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-sidan-500 dark:text-white dark:hover:bg-sidan-600">
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M3 3h2l2.4 10.4A2 2 0 0 0 9.35 15H17a2 2 0 0 0 1.93-1.48L21 6H6" />

                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M10 20h.01M17 20h.01" />
                                                    </svg>

                                                    <span>
                                                        Revisar carrito
                                                    </span>

                                                    <span id="cantidadCompraGlobal"
                                                        class="rounded-full bg-white/20 px-2 py-0.5 text-xs">
                                                        0
                                                    </span>
                                                </button>

                                                <p id="totalCompraGlobal"
                                                    class="mt-2 text-right text-xs font-bold text-slate-500 dark:text-slate-300">
                                                    Productos: $0.00
                                                </p>
                                            </form>
                                        @else
                                            @if ($compraBloqueadaPorInscripcion)
                                                <button type="button" disabled
                                                    class="flex min-w-[220px] cursor-not-allowed items-center
                                                                   justify-center rounded-xl bg-slate-400 px-5 py-3
                                                                   text-sm font-black text-white opacity-60">
                                                    Requiere inscripción confirmada
                                                </button>
                                            @else
                                                <a href="{{ route('login') }}"
                                                    class="flex min-w-[220px] items-center justify-center
                                                                   rounded-xl bg-sidan-900 px-5 py-3 text-sm
                                                                   font-black text-white transition hover:bg-sidan-700">
                                                    Iniciar sesión para comprar
                                                </a>
                                            @endif
                                        @endauth

                                    </div>
                                </div>

                                @if ($errors->has('compra'))
                                    <div
                                        class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                                        {{ $errors->first('compra') }}
                                    </div>
                                @endif

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                                    @forelse ($itemsTodaActividad as $item)
                                        @include('activities.partials.producto', ['item' => $item, 'ocultarProducto' => $loop->index >= 6])
                                    @empty
                                        <p class="sm:col-span-2 rounded-xl bg-slate-50 p-5 text-sm text-slate-500 dark:bg-white/5 dark:text-slate-300">
                                            No hay productos generales. Encontrá los productos de cada sesión en Cronograma.
                                        </p>
                                    @endforelse

                                </div>

                                @if ($itemsTodaActividad->count() > 6)
                                    <div class="mt-7 text-center">

                                        <button type="button" id="btnMasProductos"
                                            class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-black text-sidan-900 dark:text-white">
                                            Mostrar más productos
                                        </button>

                                    </div>
                                @endif

                                <div
                                    class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">

                                    <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">
                                        Prepará los productos que deseas comprar y luego presioná
                                        <span class="font-bold text-sidan-900 dark:text-white">
                                            Revisar carrito
                                        </span>.
                                        La orden se generará únicamente cuando confirmés la selección.
                                    </p>

                                </div>

                            </div>
                        </section>

                    @endif

                    @if ($promociones->isNotEmpty())
                        <section class="sidan-panel hidden" data-panel="promociones">
                            <div
                                class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-500">Promociones
                                    </p>
                                    <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white sm:text-3xl">
                                        Aprovecha antes de que terminen</h2>
                                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Estas condiciones
                                        se aplicarán cuando el módulo de compra y pago esté disponible.</p>
                                </div>

                                <div class="mt-6 grid gap-4 md:grid-cols-2">
                                    @foreach ($promociones as $promocion)
                                        @php
                                            $estadoPromo = $promocion->estaVigente()
                                                ? 'Vigente'
                                                : ($promocion->esProxima()
                                                    ? 'Próximamente'
                                                    : 'Finalizada');
                                        @endphp
                                        <article
                                            class="relative overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-sidan-50 p-5 dark:border-amber-500/20 dark:from-amber-500/10 dark:via-white/[0.03] dark:to-sidan-500/10">
                                            <div
                                                class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-amber-300/20 blur-2xl">
                                            </div>
                                            <div class="relative">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <span
                                                            class="inline-flex rounded-lg bg-sidan-950 px-3 py-1.5 text-sm font-black text-white">{{ $promocion->textoDescuento() }}
                                                            OFF</span>
                                                        <h3 class="mt-3 text-lg font-black text-sidan-900 dark:text-white">
                                                            {{ $promocion->nombre }}
                                                        </h3>
                                                    </div>
                                                    <span
                                                        class="rounded-full bg-white/80 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-slate-600 shadow-sm dark:bg-white/10 dark:text-slate-300">{{ $estadoPromo }}</span>
                                                </div>

                                                @if ($promocion->codigo)
                                                    <button type="button"
                                                        class="btnCopiarPromo mt-4 flex w-full items-center justify-between rounded-xl border border-dashed border-amber-300 bg-white/80 px-4 py-3 text-left dark:border-amber-500/30 dark:bg-white/5"
                                                        data-codigo="{{ $promocion->codigo }}">
                                                        <span>
                                                            <span
                                                                class="block text-[10px] font-bold uppercase tracking-wide text-slate-400">Código</span>
                                                            <span
                                                                class="mt-0.5 block font-mono text-base font-black tracking-widest text-sidan-900 dark:text-white">{{ $promocion->codigo }}</span>
                                                        </span>
                                                        <span
                                                            class="text-xs font-black text-amber-600 dark:text-amber-300">Copiar</span>
                                                    </button>
                                                @else
                                                    <div
                                                        class="mt-4 rounded-xl bg-sidan-100/70 px-4 py-3 text-sm font-bold text-sidan-600 dark:bg-white/10 dark:text-zinc-300">
                                                        Se aplica automáticamente</div>
                                                @endif

                                                <div
                                                    class="mt-4 space-y-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                                    <p>
                                                        <strong class="text-slate-700 dark:text-slate-200">Aplica
                                                            a:</strong>
                                                        {{ $promocion->items->isEmpty() ? 'todos los productos y servicios' : $promocion->items->pluck('nombre')->take(3)->implode(', ') }}
                                                        @if ($promocion->items->count() > 3)
                                                            y {{ $promocion->items->count() - 3 }} más
                                                        @endif
                                                    </p>
                                                    @if ($promocion->monto_minimo !== null)
                                                        <p><strong class="text-slate-700 dark:text-slate-200">Compra
                                                                mínima:</strong>
                                                            ${{ number_format((float) $promocion->monto_minimo, 2) }}</p>
                                                    @endif
                                                    @if ($promocion->vigente_desde || $promocion->vigente_hasta)
                                                        <p><strong
                                                                class="text-slate-700 dark:text-slate-200">Vigencia:</strong>
                                                            {{ $promocion->vigente_desde?->format('d/m/Y') ?: 'ahora' }} –
                                                            {{ $promocion->vigente_hasta?->format('d/m/Y') ?: 'sin límite' }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @endif

                    <section class="sidan-panel hidden" data-panel="ubicacion">
                        <div
                            class="overflow-hidden rounded-[2rem] border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-sidan-900">
                            <div class="p-6 sm:p-8">
                                <p
                                    class="text-xs font-black uppercase tracking-[0.18em] text-sidan-600 dark:text-sidan-400">
                                    Ubicación y acceso</p>
                                <h2 class="mt-2 text-2xl font-black text-sidan-950 dark:text-white sm:text-3xl">Encontrá tu
                                    actividad</h2>
                                <p class="mt-3 max-w-xl text-sm leading-6 text-zinc-500 dark:text-zinc-400">Revisá la sede
                                    informada por la organización. Cuando haya un mapa configurado, podés abrirlo para
                                    planificar cómo llegar.</p>
                                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                    <div
                                        class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-sidan-950">
                                        <p
                                            class="text-xs font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                                            Sede o lugar</p>
                                        <p class="mt-2 text-lg font-black text-sidan-950 dark:text-white">
                                            {{ $actividad['lugar'] }}</p>
                                        @if ($actividad['direccion'])
                                            <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                                {{ $actividad['direccion'] }}</p>
                                        @endif
                                    </div>
                                    <div
                                        class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-sidan-950">
                                        <p
                                            class="text-xs font-bold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">
                                            Fecha y horario</p>
                                        <p class="mt-2 text-lg font-black text-sidan-950 dark:text-white">
                                            {{ $actividad['fecha'] }}</p>
                                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $actividad['hora'] }}
                                        </p>
                                        @if ($sesiones->isNotEmpty())
                                            <button type="button" data-panel-target="cronograma"
                                                class="mt-3 text-sm font-bold text-sidan-900 underline underline-offset-4 dark:text-white">Ver
                                                horarios por sesión →</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if ($actividad['mapa_disponible'])
                                <iframe src="{{ $actividad['mapa_embed_url'] }}"
                                    class="h-[360px] w-full border-0 sm:h-[430px]" loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    title="Mapa de {{ $actividad['titulo'] }}"></iframe>
                                <div
                                    class="flex flex-wrap items-center justify-between gap-3 border-t border-zinc-200 p-5 dark:border-zinc-800">
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">El mapa corresponde a la ubicación
                                        configurada para esta actividad.</p>
                                    <a href="{{ $actividad['mapa_url'] }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex rounded-xl bg-sidan-950 px-4 py-3 text-sm font-black text-white transition hover:bg-sidan-800 dark:bg-white dark:text-sidan-950 dark:hover:bg-zinc-200">Abrir
                                        mapa ↗</a>
                                </div>
                            @else
                                <div
                                    class="border-t border-zinc-200 bg-zinc-50 p-5 text-sm text-zinc-500 dark:border-zinc-800 dark:bg-sidan-950 dark:text-zinc-400">
                                    Todavía no se configuró un mapa interactivo para esta ubicación.</div>
                            @endif
                        </div>
                    </section>
                </div>

                <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                    <div
                        class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-sidan-950">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-sidan-500">Resumen rápido</p>
                        <div class="mt-4 space-y-4 text-sm">
                            <div>
                                <p class="text-xs font-bold uppercase text-slate-400">Fecha</p>
                                <p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['fecha'] }} ·
                                    {{ $actividad['hora'] }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase text-slate-400">Lugar</p>
                                <p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['lugar'] }}</p>
                            </div>
                            @if ($sesiones->isNotEmpty())
                                <div>
                                    <p class="text-xs font-bold uppercase text-slate-400">Programación</p>
                                    <button type="button" data-panel-target="cronograma"
                                        class="mt-1 font-black text-sidan-500 hover:underline">{{ $sesiones->count() }}
                                        sesión{{ $sesiones->count() === 1 ? '' : 'es' }} →</button>
                                </div>
                            @endif
                            @if ($items->isNotEmpty())
                                <div>
                                    <p class="text-xs font-bold uppercase text-slate-400">Oferta</p>
                                    <button type="button" data-panel-target="productos"
                                        class="mt-1 font-black text-sidan-500 hover:underline">{{ $itemsTodaActividad->count() }}
                                        producto{{ $itemsTodaActividad->count() === 1 ? '' : 's' }} generales
                                        →</button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div
                        class="rounded-[1.75rem] bg-gradient-to-br from-sidan-950 to-sidan-600 p-5 text-white shadow-lg dark:from-sidan-900 dark:to-sidan-950">

                        @if ($usuarioInscritoActividad)
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-sidan-300">
                                Tu participación
                            </p>

                            <div class="mt-3 flex items-center gap-3">


                                <div>
                                    <h3 class="text-xl font-black">
                                        Estás inscrito
                                    </h3>

                                    <p class="mt-0.5 text-xs text-white/55">
                                        Inscripción confirmada
                                    </p>
                                </div>

                            </div>

                            <p class="mt-4 text-sm leading-6 text-white/65">
                                Tu participación en esta actividad ya se encuentra confirmada.
                            </p>

                            <button type="button" disabled
                                class="mt-4 flex w-full cursor-default items-center justify-center gap-2 rounded-xl border border-sidan-300/30 bg-sidan-400/15 px-5 py-3 text-sm font-black text-sidan-200">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="m5 12 4 4L19 6" />
                                </svg>

                                Inscrito
                            </button>
                        @else
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-zinc-200">
                                ¿Te interesa?
                            </p>

                            <h3 class="mt-2 text-xl font-black">
                                {{ $actividad['accion_texto'] }}
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-white/65">
                                {{ $actividad['participacion_descripcion'] }}
                            </p>

                            @if ($actividadModelo->requiereInscripcionGeneral() && $cuposDisponibles === 0)
                                <button type="button" disabled
                                    class="mt-4 flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-500 px-5 py-3 text-sm font-black text-white opacity-60">
                                    Cupos agotados
                                </button>
                            @else
                                <a href="{{ $actividad['accion_url'] }}"
                                    class="mt-4 flex w-full items-center justify-center rounded-xl bg-sidan-900 px-5 py-3 text-sm font-black text-white transition hover:bg-sidan-700">
                                    {{ $actividad['accion_texto'] }}
                                </a>
                            @endif
                        @endif

                    </div>

                    {{-- Resumen del carrito: aparece únicamente cuando agregan productos. --}}
                    @if ($items->isNotEmpty())
                        <section id="carritoLateralActividad" aria-label="Resumen del carrito"
                            class="hidden overflow-hidden rounded-[1.75rem] border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-sidan-900">
                            <div
                                class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-white/10">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.17em] text-sidan-500">Tu
                                        selección</p>
                                    <h3
                                        class="mt-1 flex items-center gap-2 text-lg font-black text-sidan-900 dark:text-white">
                                        <svg class="h-5 w-5 text-sidan-500" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 3h2l2 11h12l2-8H6M9 20h.01M18 20h.01" />
                                        </svg>
                                        Mi carrito
                                    </h3>
                                </div>
                                <span id="cantidadCarritoLateral"
                                    class="rounded-full bg-sidan-100 px-3 py-1 text-xs font-black text-sidan-600 dark:bg-sidan-500/20 dark:text-sidan-200">0</span>
                            </div>

                            {{-- Los bloques de cada producto se trasladan aquí por JS. --}}
                            <div id="listaCarritoLateral" class="max-h-[45vh] space-y-3 overflow-y-auto px-4 py-4"></div>

                            <div
                                class="border-t border-slate-100 bg-slate-50 px-5 py-5 dark:border-white/10 dark:bg-sidan-950/50">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-bold text-slate-600 dark:text-slate-300">Subtotal productos</p>
                                    <p id="subtotalCarritoLateral"
                                        class="text-xl font-black text-sidan-900 dark:text-white">$0.00</p>
                                </div>
                                @if ($actividadModelo->tipo_participacion === 'registro_pago' && (float) ($actividadModelo->precio_inscripcion ?? 0) > 0)
                                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                                        El precio de inscripción se agrega en el paso correspondiente.
                                    </p>
                                @endif
                                
                                @auth
                                    <button type="submit" form="formCompraActividad" data-revisar-carrito disabled
                                        class="mt-4 w-full rounded-xl bg-sidan-500 px-4 py-3 text-sm font-black text-white disabled:cursor-not-allowed disabled:opacity-40">
                                        Revisar carrito
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="mt-4 block rounded-xl bg-sidan-500 px-4 py-3 text-center text-sm font-black text-white">Iniciar sesión para comprar</a>
                                @endauth
                                <p class="mt-2 text-center text-[11px] text-slate-500 dark:text-slate-400">La orden se
                                    genera al confirmar la compra.</p>
                            </div>
                        </section>
                    @endif
                </aside>
            </div>
        </section>
    </main>

    <div id="modalGaleriaActividad"
        class="fixed inset-0 z-[1200] hidden items-center justify-center bg-slate-950/90 p-4 backdrop-blur-sm">
        <button type="button" id="cerrarGaleriaActividad"
            class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-2xl text-white">×</button>
        <img id="imagenGaleriaActividad" src="" alt=""
            class="max-h-[88vh] max-w-[92vw] rounded-2xl object-contain shadow-2xl">
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const raiz = document.getElementById('detalleActividad');
            const tabs = Array.from(document.querySelectorAll('.sidan-tab'));
            const panels = Array.from(document.querySelectorAll('.sidan-panel'));

            function activarPanel(nombre, desplazar = false) {
                const panel = panels.find(elemento => elemento.dataset.panel === nombre);
                if (!panel) return;

                panels.forEach(elemento => elemento.classList.toggle('hidden', elemento !== panel));
                tabs.forEach(tab => {
                    const activo = tab.dataset.panelTarget === nombre;
                    tab.classList.toggle('bg-sidan-900', activo);
                    tab.classList.toggle('text-white', activo);
                    tab.classList.toggle('bg-slate-100', !activo);
                    tab.classList.toggle('text-slate-600', !activo);
                    tab.classList.toggle('dark:bg-white/10', !activo);
                    tab.classList.toggle('dark:text-slate-300', !activo);
                });

                // El panel cambia sin recargar ni mover automáticamente la página.
                // Si el enlace se activó desde el resumen lateral, acercamos la barra
                // de pestañas únicamente cuando está fuera de la vista.
                if (desplazar && window.scrollY > 450) {
                    const barra = document.querySelector('[role="tablist"]');
                    if (barra && barra.getBoundingClientRect().top < 0) {
                        const destino = window.scrollY + barra.getBoundingClientRect().top - 80;
                        window.scrollTo({
                            top: Math.max(0, destino),
                            behavior: 'smooth'
                        });
                    }
                }
            }

            document.querySelectorAll('[data-panel-target]').forEach(boton => {
                boton.addEventListener('click', function() {
                    activarPanel(this.dataset.panelTarget, !this.classList.contains('sidan-tab'));
                });
            });

            activarPanel(raiz?.dataset.panelInicial || 'resumen');

            function configurarMostrarMas(selector, botonId, lote) {
                const elementos = Array.from(document.querySelectorAll(selector));
                const boton = document.getElementById(botonId);
                let visibles = lote;

                if (!boton) return;

                boton.addEventListener('click', function() {
                    visibles += lote;
                    elementos.forEach((elemento, indice) => elemento.classList.toggle('hidden', indice >=
                        visibles));

                    if (visibles >= elementos.length) {
                        boton.classList.add('hidden');
                    }
                });
            }

            configurarMostrarMas('#productos .sidan-producto', 'btnMasProductos', 6);

            // Configurador visual de productos.
            // No registra pedidos ni persiste selecciones.
            // Configurador de compra de productos.

            const compraActividad = [];
            // Una selección compartida por producto, aunque tenga tarjetas en varias sesiones.
            const estadosProductos = new Map();

            const compraBloqueadaPorInscripcion = @json($compraBloqueadaPorInscripcion);

            const formCompraActividad =
                document.getElementById('formCompraActividad');

            const inputSeleccionesCompra =
                document.getElementById('seleccionesCompra');

            const btnContinuarCompra =
                document.getElementById('btnContinuarCompra');

            const cantidadCompraGlobal =
                document.getElementById('cantidadCompraGlobal');

            const totalCompraGlobal =
                document.getElementById('totalCompraGlobal');

            // Un único resumen visible en el lateral. El contenido de cada
            // producto se mueve sin recrear sus datos ni sus eventos.
            const panelCarritoLateral = document.getElementById('carritoLateralActividad');
            const listaCarritoLateral = document.getElementById('listaCarritoLateral');
            const cantidadCarritoLateral = document.getElementById('cantidadCarritoLateral');
            const subtotalCarritoLateral = document.getElementById('subtotalCarritoLateral');

            const precioInscripcion = Number(
                @json($actividadModelo->tipo_participacion === 'registro_pago' ? (float) ($actividadModelo->precio_inscripcion ?? 0) : 0)
            );

            const dinero = valor =>
                '$' + Number(valor).toFixed(2);

            function actualizarCompraGlobal() {
                const cantidadConfiguraciones =
                    compraActividad.length;

                const totalProductos =
                    compraActividad.reduce(
                        (total, linea) =>
                        total +
                        (
                            Number(linea.precioVisual) *
                            Number(linea.cantidad)
                        ),
                        0
                    );

                if (cantidadCompraGlobal) {
                    cantidadCompraGlobal.textContent =
                        cantidadConfiguraciones;
                }

                if (totalCompraGlobal) {
                    const totalVisual =
                        totalProductos +
                        precioInscripcion;

                    if (precioInscripcion > 0) {
                        totalCompraGlobal.textContent =
                            `Productos: ${dinero(totalProductos)} · ` +
                            `Con inscripción: ${dinero(totalVisual)}`;
                    } else {
                        totalCompraGlobal.textContent =
                            `Productos: ${dinero(totalProductos)}`;
                    }
                }

                if (btnContinuarCompra) {
                    btnContinuarCompra.disabled =
                        compraBloqueadaPorInscripcion ||
                        cantidadConfiguraciones === 0;
                }

                document.querySelectorAll('[data-revisar-carrito]').forEach(boton => {
                    boton.disabled = compraBloqueadaPorInscripcion || cantidadConfiguraciones === 0;
                });

                // Contadores y subtotal visibles debajo de "Estás inscrito".
                // Conservamos el precio de inscripción separado, como antes.
                if (panelCarritoLateral) {
                    panelCarritoLateral.classList.toggle('hidden', cantidadConfiguraciones === 0);
                }
                if (cantidadCarritoLateral) {
                    cantidadCarritoLateral.textContent = String(cantidadConfiguraciones);
                }
                if (subtotalCarritoLateral) {
                    subtotalCarritoLateral.textContent = dinero(totalProductos);
                }
            }

            document
                .querySelectorAll('[data-producto-config]')
                .forEach(tarjeta => {
                    const reglas = JSON.parse(
                        tarjeta.dataset.reglas || '[]'
                    );

                    const variantes = JSON.parse(
                        tarjeta.dataset.variantes || '[]'
                    );

                    const selectorVariante =
                        tarjeta.querySelector(
                            '[data-variante]'
                        );

                    const campos = Array.from(
                        tarjeta.querySelectorAll(
                            '[data-campo-clave]'
                        )
                    );

                    const sumar =
                        tarjeta.querySelector(
                            '[data-sumar]'
                        );

                    const restar =
                        tarjeta.querySelector(
                            '[data-restar]'
                        );

                    const cantidadTexto =
                        tarjeta.querySelector(
                            '[data-cantidad]'
                        );

                    const precioTexto =
                        tarjeta.querySelector(
                            '[data-precio-unitario]'
                        );

                    const errorTexto =
                        tarjeta.querySelector(
                            '[data-error]'
                        );

                    const botonAgregar =
                        tarjeta.querySelector(
                            '[data-agregar-configuracion]'
                        );

                    const resumenPropio = tarjeta.querySelector('[data-resumen-configuraciones]');
                    const claveProducto = String(tarjeta.dataset.itemId);
                    if (!estadosProductos.has(claveProducto)) {
                        estadosProductos.set(claveProducto, {
                            configuraciones: [],
                            resumen: resumenPropio,
                            vistas: []
                        });
                    }
                    const estadoProducto = estadosProductos.get(claveProducto);
                    if (resumenPropio && resumenPropio !== estadoProducto.resumen) {
                        resumenPropio.remove();
                    }
                    const contenedorResumen = estadoProducto.resumen;
                    const lineasContenedor = contenedorResumen?.querySelector('[data-lineas-configuracion]');
                    const subtotalTexto = contenedorResumen?.querySelector('[data-subtotal]');

                    // Reubicar el DOM del resumen fuera de la tarjeta. Al mover
                    // el mismo nodo, renderResumen() y los botones Quitar
                    // conservan sus referencias y lógica originales.
                    if (contenedorResumen && listaCarritoLateral) {
                        listaCarritoLateral.appendChild(contenedorResumen);
                    }

                    const disponibilidadTexto =
                        tarjeta.querySelector(
                            '[data-disponibilidad]'
                        );

                    const limiteItem =
                        tarjeta.dataset.maximo === '' ?
                        Infinity :
                        Number(
                            tarjeta.dataset.maximo
                        );

                    const stockItem =
                        tarjeta.dataset.stock === '' ?
                        Infinity :
                        Number(
                            tarjeta.dataset.stock
                        );

                    const minimo =
                        Number(
                            tarjeta.dataset.minimo ||
                            1
                        );

                    const configuraciones = estadoProducto.configuraciones;

                    let cantidad = 0;

                    const varianteElegida = () => {
                        if (!selectorVariante) {
                            return null;
                        }

                        return variantes.find(
                            variante =>
                            String(variante.id) ===
                            String(
                                selectorVariante.value
                            )
                        ) || null;
                    };

                    const unidadesPreparadas = () =>
                        configuraciones.reduce(
                            (total, linea) =>
                            total +
                            Number(
                                linea.cantidad
                            ),
                            0
                        );

                    const stockVariantePreparado =
                        idVariante =>
                        configuraciones
                        .filter(
                            linea =>
                            String(
                                linea.varianteId
                            ) ===
                            String(idVariante)
                        )
                        .reduce(
                            (total, linea) =>
                            total +
                            Number(
                                linea.cantidad
                            ),
                            0
                        );

                    function precioActual() {
                        let precio = Number(tarjeta.dataset.precioBase);

                        const valores = Object.fromEntries(
                            campos.map(campo => [
                                campo.dataset.campoClave,
                                campo.value
                            ])
                        );

                        reglas.forEach(regla => {
                            if (
                                Object.prototype.hasOwnProperty.call(
                                    valores,
                                    regla.field_key
                                ) &&
                                String(valores[regla.field_key]) ===
                                String(regla.option ?? '')
                            ) {
                                precio += Number(regla.price_increment || 0);
                            }
                        });

                        return Math.max(0, Math.round(precio * 100) / 100);
                    }

                    function limiteActual() {
                        const restanteItem =
                            Math.max(
                                0,
                                Math.min(
                                    limiteItem,
                                    stockItem
                                ) -
                                unidadesPreparadas()
                            );

                        if (!selectorVariante) {
                            return restanteItem;
                        }

                        const variante =
                            varianteElegida();

                        if (!variante) {
                            return 0;
                        }

                        const stockVariante =
                            variante.stock === null ||
                            variante.stock === undefined ?
                            Infinity :
                            Number(
                                variante.stock
                            );

                        const restanteVariante =
                            Math.max(
                                0,
                                stockVariante -
                                stockVariantePreparado(
                                    variante.id
                                )
                            );

                        return Math.min(
                            restanteItem,
                            restanteVariante
                        );
                    }

                    function validar() {
                        if (
                            selectorVariante &&
                            !selectorVariante.value
                        ) {
                            return 'Selecciona una variante.';
                        }

                        for (
                            const campo of campos
                        ) {
                            if (
                                campo.dataset
                                .requerido === '1' &&
                                !campo.value.trim()
                            ) {
                                return (
                                    'Completa el campo ' +
                                    campo.dataset
                                    .campoClave +
                                    '.'
                                );
                            }
                        }

                        return '';
                    }

                    function mostrarError(
                        mensaje
                    ) {
                        if (!errorTexto) {
                            return;
                        }

                        errorTexto.textContent =
                            mensaje;

                        errorTexto.classList.toggle(
                            'hidden',
                            !mensaje
                        );
                    }

                    function refrescar() {
                        if (precioTexto) {
                            precioTexto.textContent =
                                dinero(
                                    precioActual()
                                );
                        }

                        const limite =
                            limiteActual();

                        if (
                            cantidad >
                            limite
                        ) {
                            cantidad =
                                limite >= minimo ?
                                limite :
                                0;
                        }

                        if (cantidadTexto) {
                            cantidadTexto.textContent =
                                cantidad;
                        }

                        if (restar) {
                            restar.disabled =
                                cantidad === 0;
                        }

                        if (sumar) {
                            sumar.disabled =
                                sumar.dataset
                                .bloqueado === '1' ||
                                limite < minimo ||
                                cantidad >= limite;
                        }
                    }

                    function renderResumen() {
                        if (!lineasContenedor) {
                            return;
                        }

                        lineasContenedor
                            .replaceChildren();

                        configuraciones.forEach(
                            (
                                linea,
                                indice
                            ) => {
                                const fila =
                                    document.createElement(
                                        'div'
                                    );

                                fila.className =
                                    'flex items-start justify-between gap-2 ' +
                                    'rounded-lg border border-slate-200 p-2 ' +
                                    'text-xs dark:border-white/10';

                                const texto =
                                    document.createElement(
                                        'span'
                                    );

                                const detalles = [];

                                if (
                                    linea.varianteNombre
                                ) {
                                    detalles.push(
                                        linea.varianteNombre
                                    );
                                }

                                Object.entries(
                                    linea.campos
                                ).forEach(
                                    ([clave, valor]) => {
                                        if (
                                            String(valor)
                                            .trim() !== ''
                                        ) {
                                            detalles.push(
                                                `${clave}: ${valor}`
                                            );
                                        }
                                    }
                                );

                                const descripcion =
                                    detalles.length ?
                                    detalles.join(
                                        ' · '
                                    ) :
                                    'Sin configuración adicional';

                                texto.textContent =
                                    `${linea.cantidad} × ` +
                                    `${descripcion} — ` +
                                    dinero(
                                        linea.precioVisual *
                                        linea.cantidad
                                    );

                                const quitar =
                                    document.createElement(
                                        'button'
                                    );

                                quitar.type =
                                    'button';

                                quitar.className =
                                    'shrink-0 font-black text-red-600 dark:text-red-400';

                                quitar.textContent =
                                    'Quitar';

                                quitar.addEventListener(
                                    'click',
                                    () => {
                                        const lineaEliminada =
                                            configuraciones[
                                                indice
                                            ];

                                        configuraciones.splice(
                                            indice,
                                            1
                                        );

                                        const indiceGlobal =
                                            compraActividad
                                            .indexOf(
                                                lineaEliminada
                                            );

                                        if (
                                            indiceGlobal !==
                                            -1
                                        ) {
                                            compraActividad
                                                .splice(
                                                    indiceGlobal,
                                                    1
                                                );
                                        }

                                        estadoProducto.vistas.forEach(actualizar => actualizar());
                                    actualizarCompraGlobal();
                                    }
                                );

                                fila.append(
                                    texto,
                                    quitar
                                );

                                lineasContenedor
                                    .append(
                                        fila
                                    );
                            }
                        );

                        if (contenedorResumen) {
                            contenedorResumen
                                .classList.toggle(
                                    'hidden',
                                    configuraciones
                                    .length === 0
                                );
                        }

                        const subtotal =
                            configuraciones.reduce(
                                (total, linea) =>
                                total +
                                (
                                    linea.precioVisual *
                                    linea.cantidad
                                ),
                                0
                            );

                        if (subtotalTexto) {
                            subtotalTexto.textContent =
                                dinero(
                                    subtotal
                                );
                        }

                        const unidades =
                            unidadesPreparadas();

                        if (
                            disponibilidadTexto &&
                            botonAgregar &&
                            botonAgregar.disabled ===
                            false
                        ) {
                            disponibilidadTexto
                                .textContent =
                                unidades > 0 ?
                                `${unidades} unidad${unidades === 1 ? '' : 'es'} preparada${unidades === 1 ? '' : 's'}.` :
                                'Configura el producto para continuar.';
                        }
                    }

                    if (sumar) {
                        sumar.dataset.bloqueado =
                            sumar.disabled ?
                            '1' :
                            '0';

                        sumar.addEventListener(
                            'click',
                            () => {
                                mostrarError('');

                                if (
                                    cantidad === 0
                                ) {
                                    if (
                                        limiteActual() >=
                                        minimo
                                    ) {
                                        cantidad =
                                            minimo;
                                    }
                                } else if (
                                    cantidad <
                                    limiteActual()
                                ) {
                                    cantidad++;
                                }

                                refrescar();
                            }
                        );
                    }

                    if (restar) {
                        restar.addEventListener(
                            'click',
                            () => {
                                cantidad =
                                    cantidad <= minimo ?
                                    0 :
                                    cantidad - 1;

                                refrescar();
                            }
                        );
                    }

                    [
                        ...campos,
                        selectorVariante
                    ]
                    .filter(Boolean)
                        .forEach(
                            campo => {
                                campo.addEventListener(
                                    'change',
                                    () => {
                                        mostrarError('');

                                        cantidad = 0;

                                        refrescar();
                                    }
                                );
                            }
                        );

                    campos.forEach(
                        campo => {
                            campo.addEventListener(
                                'input',
                                () => {
                                    mostrarError('');

                                    refrescar();
                                }
                            );
                        }
                    );

                    if (botonAgregar) {
                        botonAgregar
                            .addEventListener(
                                'click',
                                () => {
                                    const error =
                                        validar();

                                    if (error) {
                                        mostrarError(
                                            error
                                        );

                                        return;
                                    }

                                    // UX: con una sola pulsación se agrega la cantidad
                                    // mínima permitida. Se respetan variantes, campos,
                                    // stock y todas las validaciones ya existentes.
                                    if (cantidad === 0 && limiteActual() >= minimo) {
                                        cantidad = minimo;
                                        refrescar();
                                    }

                                    if (
                                        cantidad <
                                        minimo
                                    ) {
                                        mostrarError(
                                            `Selecciona al menos ${minimo} unidad(es).`
                                        );

                                        return;
                                    }

                                    if (
                                        cantidad >
                                        limiteActual()
                                    ) {
                                        mostrarError(
                                            'La cantidad supera el stock o límite disponible.'
                                        );

                                        refrescar();

                                        return;
                                    }

                                    const variante =
                                        varianteElegida();

                                    const lineaNueva = {
                                        itemId: Number(tarjeta.dataset.itemId),
                                        varianteId: variante ? Number(variante.id) : null,
                                        varianteNombre: variante ? variante.nombre : null,
                                        campos: Object.fromEntries(
                                            campos.map(campo => [
                                                campo.dataset.campoClave,
                                                campo.value.trim()
                                            ])
                                        ),
                                        cantidad: Number(cantidad),
                                        precioVisual: precioActual()
                                    };

                                    configuraciones.push(
                                        lineaNueva
                                    );

                                    compraActividad.push(
                                        lineaNueva
                                    );

                                    cantidad = 0;

                                    campos.forEach(
                                        campo => {
                                            campo.value =
                                                '';
                                        }
                                    );

                                    if (
                                        selectorVariante
                                    ) {
                                        selectorVariante
                                            .value = '';
                                    }

                                    mostrarError('');

                                    estadoProducto.vistas.forEach(actualizar => actualizar());
                                    actualizarCompraGlobal();

                                    // Confirmación visual: la selección ya está en compraActividad.
                                    const nombreProducto = tarjeta.querySelector('h3')?.textContent?.trim() ||
                                        'Producto';
                                    window.SIDANToast?.success(`${nombreProducto} agregado al carrito`);
                                    const avisoProducto = tarjeta.querySelector('[data-exito-carrito]');
                                    if (avisoProducto) {
                                        avisoProducto.classList.remove('hidden');
                                        window.clearTimeout(tarjeta._temporizadorAvisoCarrito);
                                        tarjeta._temporizadorAvisoCarrito = window.setTimeout(
                                            () => avisoProducto.classList.add('hidden'), 1750
                                        );
                                    }
                                    if (botonAgregar) {
                                        botonAgregar.classList.add('ring-2', 'ring-sidan-400');
                                        window.setTimeout(() => botonAgregar.classList.remove('ring-2',
                                            'ring-sidan-400'), 850);
                                    }
                                }
                            );
                    }

                    estadoProducto.vistas.push(() => {
                        renderResumen();
                        refrescar();
                    });
                    refrescar();
                });

            if (formCompraActividad) {
                formCompraActividad
                    .addEventListener(
                        'submit',
                        evento => {
                            if (compraBloqueadaPorInscripcion) {
                                evento.preventDefault();
                                return;
                            }
                            if (
                                compraActividad.length ===
                                0
                            ) {
                                evento.preventDefault();

                                window.SIDANToast
                                    ?.info(
                                        'Agregá al menos un producto antes de continuar.'
                                    );

                                return;
                            }

                            const payload = compraActividad.map(linea => ({
                                id_item: Number(linea.itemId),
                                id_variante: linea.varianteId ?? null,
                                cantidad: Number(linea.cantidad),
                                campos: linea.campos ?? {}
                            }));

                            inputSeleccionesCompra
                                .value =
                                JSON.stringify(
                                    payload
                                );

                            btnContinuarCompra
                                .disabled =
                                true;
                            document.querySelectorAll('[data-revisar-carrito]').forEach(boton => {
                                boton.disabled = true;
                            });

                            btnContinuarCompra
                                .innerHTML =
                                `
                                    <svg
                                        class="h-5 w-5 animate-spin"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                    >
                                        <circle
                                            class="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="4"
                                        ></circle>

                                        <path
                                            class="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                        ></path>
                                    </svg>

                                    Preparando orden...
                                `;
                        }
                    );
            }

            actualizarCompraGlobal();

            const tabsDia = Array.from(document.querySelectorAll('.sidan-dia-tab'));
            const panelesDia = Array.from(document.querySelectorAll('.sidan-dia-panel'));
            const botonesDia = Array.from(document.querySelectorAll('[data-dia-target]'));

            function activarDia(clave, desplazar = false) {
                const panelDia = panelesDia.find(panel => panel.dataset.diaPanel === clave);
                if (!panelDia) return;

                panelesDia.forEach(panel => panel.classList.toggle('hidden', panel !== panelDia));
                tabsDia.forEach(tab => {
                    const activo = tab.dataset.diaTarget === clave;
                    tab.classList.toggle('border-sidan-500', activo);
                    tab.classList.toggle('bg-sidan-900', activo);
                    tab.classList.toggle('text-white', activo);
                    tab.classList.toggle('bg-white', !activo);
                    tab.classList.toggle('dark:bg-white/5', !activo);
                    tab.setAttribute('aria-pressed', String(activo));

                    // La fecha tiene text-sidan-900 por defecto: quitarlo en la
                    // pestaña activa evita texto azul sobre fondo azul en modo claro.
                    const [nombreDia, fechaDia] = tab.querySelectorAll('span');
                    if (nombreDia) {
                        nombreDia.classList.toggle('text-slate-500', !activo);
                        nombreDia.classList.toggle('text-sidan-200', activo);
                    }
                    if (fechaDia) {
                        fechaDia.classList.toggle('text-sidan-900', !activo);
                        fechaDia.classList.toggle('text-white', activo);
                    }
                });

                if (desplazar) {
                    window.setTimeout(() => panelDia.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    }), 60);
                }
            }

            botonesDia.forEach(boton => {
                boton.addEventListener('click', function() {
                    activarDia(this.dataset.diaTarget, this.classList.contains(
                        'sidan-dia-resumen'));
                });
            });

            if (panelesDia.length > 0) {
                activarDia(panelesDia[0].dataset.diaPanel);
            }

            document.querySelectorAll('.btnCopiarPromo').forEach(boton => {
                boton.addEventListener('click', async function() {
                    const codigo = this.dataset.codigo || '';
                    if (!codigo) return;

                    try {
                        await navigator.clipboard.writeText(codigo);
                        window.SIDANToast?.success('Código copiado: ' + codigo);
                        const etiqueta = this.querySelector('span:last-child');
                        if (etiqueta) {
                            const original = etiqueta.textContent;
                            etiqueta.textContent = 'Copiado';
                            window.setTimeout(() => etiqueta.textContent = original, 1200);
                        }
                    } catch (error) {
                        window.SIDANToast?.info('Código: ' + codigo);
                    }
                });
            });

            const modalGaleria = document.getElementById('modalGaleriaActividad');
            const imagenGaleria = document.getElementById('imagenGaleriaActividad');

            document.querySelectorAll('.sidan-galeria').forEach(boton => {
                boton.addEventListener('click', function() {
                    imagenGaleria.src = this.dataset.imagen || '';
                    imagenGaleria.alt = this.dataset.alt || '';
                    modalGaleria.classList.remove('hidden');
                    modalGaleria.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                });
            });

            function cerrarGaleria() {
                modalGaleria?.classList.add('hidden');
                modalGaleria?.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }

            document.getElementById('cerrarGaleriaActividad')?.addEventListener('click', cerrarGaleria);
            modalGaleria?.addEventListener('click', evento => {
                if (evento.target === modalGaleria) cerrarGaleria();
            });
            document.addEventListener('keydown', evento => {
                if (evento.key === 'Escape') cerrarGaleria();
            });
        });
    </script>

    @include('partials.public-footer')
@endsection
