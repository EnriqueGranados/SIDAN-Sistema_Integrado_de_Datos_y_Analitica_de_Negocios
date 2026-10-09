@extends('layouts.public')

@section('title', 'SIDAN | ' . $actividad['titulo'])

@section('content')
@php
    $accionActiva = $accionSolicitada ?: session('accion_actividad');
    $sesiones = $actividadModelo->sesiones->sortBy('fecha_inicio')->values();
    $items = $actividadModelo->items->sortBy('orden')->values();
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

        return str_starts_with($ruta, 'storage/')
            ? asset($ruta)
            : asset('storage/' . $ruta);
    };

    $panelInicial = match ($accionActiva) {
        'comprar' => 'productos',
        'participar' => $sesiones->isNotEmpty() ? 'cronograma' : 'resumen',
        default => 'resumen',
    };

    $descripcionCompleta = trim((string) ($actividadModelo->descripcion ?: $actividad['descripcion']));

    $sesionesVista = $sesiones->map(function ($registroSesion) {
        $inicio = $registroSesion->fecha_inicio;
        $fin = $registroSesion->fecha_fin;
        $esVirtual = filled($registroSesion->enlace_acceso);

        return [
            'nombre' => (string) $registroSesion->nombre,
            'virtual' => $esVirtual,
            'modalidad' => $esVirtual ? 'Virtual' : 'Presencial',
            'lugar' => $esVirtual
                ? 'Sesión virtual'
                : ($registroSesion->espacio?->nombre ?: ($registroSesion->ubicacion ?: 'Lugar por definir')),
            'obligatoria' => (bool) $registroSesion->obligatoria,
            'clave_dia' => $inicio ? $inicio->format('Y-m-d') : 'sin-fecha',
            'mes' => $inicio ? $inicio->copy()->locale('es')->translatedFormat('M') : '',
            'dia' => $inicio ? $inicio->format('d') : '',
            'dia_semana' => $inicio ? $inicio->copy()->locale('es')->translatedFormat('l') : 'Sin fecha',
            'fecha_corta' => $inicio ? $inicio->copy()->locale('es')->translatedFormat('d M Y') : 'Sin fecha',
            'hora_inicio' => $inicio ? $inicio->format('g:i a') : '',
            'hora_fin' => $fin ? $fin->format('g:i a') : '',
            'fecha' => $inicio ? $inicio->copy()->locale('es')->translatedFormat('d \d\e F Y') : 'Fecha por definir',
            'cupo' => $registroSesion->cupo !== null ? $registroSesion->cupo . ' personas' : 'Según actividad',
            'reserva' => $registroSesion->requiere_reserva ? 'Requiere reserva' : 'No requiere reserva',
        ];
    })->values();

    $sesionesPorDia = $sesionesVista->groupBy('clave_dia');
    $diaInicialCronograma = $sesionesPorDia->keys()->first();
@endphp

@include('partials.public-header')

<main class="min-h-screen bg-slate-50/70 dark:bg-transparent" id="detalleActividad" data-panel-inicial="{{ $panelInicial }}">
    <section class="relative overflow-hidden bg-sidan-950">
        <div class="absolute inset-0">
            <img src="{{ $actividad['imagen'] }}" alt="{{ $actividad['titulo'] }}" class="h-full w-full object-cover opacity-55">
            <div class="absolute inset-0 bg-gradient-to-r from-sidan-950 via-sidan-950/95 to-sidan-950/55"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-sidan-950 via-transparent to-transparent"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <a href="{{ route('activities.index') }}" class="inline-flex items-center gap-2 text-sm font-black text-white/70 transition hover:text-green-300">
                ← Volver a actividades
            </a>

            <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_360px] lg:items-end">
                <div class="min-w-0">
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-sidan-500 px-3 py-1.5 text-xs font-black uppercase tracking-wider text-white">{{ $actividad['categoria'] }}</span>
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-black text-white">{{ $actividad['estado'] }}</span>
                    </div>

                    <h1 class="mt-5 max-w-4xl text-4xl font-black leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl">{{ $actividad['titulo'] }}</h1>

                    <p class="mt-5 max-w-3xl text-base leading-7 text-white/75 sm:text-lg">{{ $actividad['descripcion'] }}</p>

                    <div class="mt-7 flex flex-wrap gap-3 text-sm font-bold text-white/85">
                        <span class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">📅 {{ $actividad['fecha'] }}</span>
                        <span class="rounded-xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">🕒 {{ $actividad['hora'] }}</span>
                        <span class="max-w-full truncate rounded-xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">📍 {{ $actividad['lugar'] }}</span>
                    </div>
                </div>

                <div class="rounded-[1.75rem] border border-white/15 bg-white/10 p-5 shadow-2xl backdrop-blur-xl sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-green-300">{{ $actividad['accion_texto'] }}</p>
                            <p class="mt-2 text-3xl font-black text-white">{{ $actividad['precio'] }}</p>
                        </div>
                        @if ($promociones->isNotEmpty())
                            <button type="button" data-panel-target="promociones" class="rounded-full bg-amber-300 px-3 py-1.5 text-xs font-black text-sidan-950 transition hover:bg-amber-200">
                                {{ $promociones->count() }} promo{{ $promociones->count() === 1 ? '' : 's' }}
                            </button>
                        @endif
                    </div>

                    <a href="{{ $actividad['accion_url'] }}" class="mt-5 flex w-full items-center justify-center rounded-xl bg-sidan-500 px-5 py-3.5 text-sm font-black text-white transition hover:-translate-y-0.5 hover:bg-green-600">
                        {{ $actividad['accion_texto'] }}
                    </a>

                    <p class="mt-3 text-center text-xs leading-5 text-white/65">{{ $actividad['participacion_descripcion'] }}</p>

                    @if ($actividad['requiere_cuenta'])
                        <p class="mt-2 text-center text-xs leading-5 text-white/55">
                            @guest
                                Necesitarás iniciar sesión para completar esta acción.
                            @else
                                Tu cuenta está lista para continuar.
                            @endguest
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur dark:border-white/10 dark:bg-sidan-950/95">
        <div class="mx-auto max-w-7xl overflow-x-auto px-4 sm:px-6 lg:px-8">
            <div class="flex min-w-max gap-2 py-3" role="tablist" aria-label="Secciones de la actividad">
                <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black" data-panel-target="resumen">Resumen</button>
                @if ($sesiones->isNotEmpty())
                    <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black" data-panel-target="cronograma">Cronograma <span class="ml-1 opacity-60">{{ $sesiones->count() }}</span></button>
                @endif
                @if ($items->isNotEmpty())
                    <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black" data-panel-target="productos">Productos <span class="ml-1 opacity-60">{{ $items->count() }}</span></button>
                @endif
                @if ($promociones->isNotEmpty())
                    <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black" data-panel-target="promociones">Promociones <span class="ml-1 opacity-60">{{ $promociones->count() }}</span></button>
                @endif
                <button type="button" class="sidan-tab rounded-xl px-4 py-2.5 text-sm font-black" data-panel-target="ubicacion">Ubicación</button>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="min-w-0">
                <section class="sidan-panel" data-panel="resumen">
                    <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                        <div class="grid gap-8 xl:grid-cols-[1fr_300px]">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">Acerca de esta actividad</p>
                                <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white sm:text-3xl">Conoce lo esencial sin perderte entre bloques</h2>
                                <div class="mt-5 whitespace-pre-line text-base leading-8 text-slate-600 dark:text-slate-300">{{ $descripcionCompleta }}</div>

                                @if (count($actividad['etiquetas']))
                                    <div class="mt-6 flex flex-wrap gap-2">
                                        @foreach ($actividad['etiquetas'] as $etiqueta)
                                            <a href="{{ route('activities.index', ['etiqueta' => $etiqueta]) }}" class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">#{{ $etiqueta }}</a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Fecha</p>
                                    <p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['fecha'] }} · {{ $actividad['hora'] }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Lugar</p>
                                    <p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['lugar'] }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Categoría</p>
                                    <p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['categoria'] }}</p>
                                </div>
                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Precio</p>
                                    <p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['precio'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($mediosActividad->count() > 1)
                        <div class="mt-6 rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-6">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.16em] text-sidan-500">Galería</p>
                                    <h3 class="mt-1 text-lg font-black text-sidan-900 dark:text-white">Un vistazo a la actividad</h3>
                                </div>
                                <span class="text-xs font-bold text-slate-400">Desliza →</span>
                            </div>
                            <div class="mt-4 flex snap-x gap-3 overflow-x-auto pb-2">
                                @foreach ($mediosActividad as $medio)
                                    @php
                                        $imagenGaleria = $urlMedio($medio->url);
                                    @endphp
                                    @if ($imagenGaleria)
                                        <button type="button" class="sidan-galeria shrink-0 snap-start overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-white/5" data-imagen="{{ $imagenGaleria }}" data-alt="{{ $medio->texto_alternativo ?: $actividad['titulo'] }}">
                                            <img src="{{ $imagenGaleria }}" alt="{{ $medio->texto_alternativo ?: $actividad['titulo'] }}" class="h-32 w-52 object-cover sm:h-40 sm:w-64" loading="lazy">
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

                @if ($sesiones->isNotEmpty())
                    <section class="sidan-panel hidden" data-panel="cronograma">
                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">Cronograma</p>
                                    <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white sm:text-3xl">Tu itinerario, organizado por día</h2>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $sesiones->count() }} sesión{{ $sesiones->count() === 1 ? '' : 'es' }} · {{ $sesionesPorDia->count() }} día{{ $sesionesPorDia->count() === 1 ? '' : 's' }}</span>
                            </div>

                            <div class="mt-8">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.16em] text-sidan-500">Detalle por día</p>
                                        <h3 class="mt-1 text-xl font-black text-sidan-900 dark:text-white">Explora todas las sesiones</h3>
                                    </div>
                                </div>

                                <div class="mt-4 flex gap-2 overflow-x-auto pb-2">
                                    @foreach ($sesionesPorDia as $claveDia => $sesionesDia)
                                        @php
                                            $primeraSesionDia = $sesionesDia->first();
                                        @endphp
                                        <button type="button" data-dia-target="{{ $claveDia }}" class="sidan-dia-tab shrink-0 rounded-xl border border-slate-200 px-4 py-3 text-left text-sm transition dark:border-white/10">
                                            <span class="block text-[10px] font-black uppercase tracking-wide text-sidan-500">{{ ucfirst($primeraSesionDia['dia_semana']) }}</span>
                                            <span class="mt-0.5 block font-black text-sidan-900 dark:text-white">{{ $primeraSesionDia['fecha_corta'] }}</span>
                                        </button>
                                    @endforeach
                                </div>

                                <div class="mt-4">
                                    @foreach ($sesionesPorDia as $claveDia => $sesionesDia)
                                        @php
                                            $primeraSesionDia = $sesionesDia->first();
                                        @endphp
                                        <div class="sidan-dia-panel {{ $claveDia === $diaInicialCronograma ? '' : 'hidden' }}" data-dia-panel="{{ $claveDia }}">
                                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-sidan-950 px-5 py-4 text-white">
                                                <div>
                                                    <p class="text-xs font-black uppercase tracking-[0.15em] text-green-300">{{ ucfirst($primeraSesionDia['dia_semana']) }}</p>
                                                    <p class="mt-1 text-xl font-black">{{ $primeraSesionDia['fecha_corta'] }}</p>
                                                </div>
                                                <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-black">{{ $sesionesDia->count() }} sesión{{ $sesionesDia->count() === 1 ? '' : 'es' }}</span>
                                            </div>

                                            <div class="max-h-[68vh] space-y-3 overflow-y-auto pr-1">
                                                @foreach ($sesionesDia as $datosSesion)
                                                    <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/60 dark:border-white/10 dark:bg-white/[0.025]">
                                                        <summary class="flex cursor-pointer list-none flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                                                            <div class="flex min-w-0 gap-3">
                                                                <div class="flex h-12 w-16 shrink-0 items-center justify-center rounded-xl bg-white text-sm font-black text-sidan-500 shadow-sm dark:bg-white/10">{{ $datosSesion['hora_inicio'] }}</div>
                                                                <div class="min-w-0">
                                                                    <div class="flex flex-wrap items-center gap-2">
                                                                        <h4 class="font-black text-sidan-900 dark:text-white">{{ $datosSesion['nombre'] }}</h4>
                                                                        <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-slate-600 shadow-sm dark:bg-white/10 dark:text-slate-300">{{ $datosSesion['modalidad'] }}</span>
                                                                        @if ($datosSesion['obligatoria'])
                                                                            <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Obligatoria</span>
                                                                        @endif
                                                                    </div>
                                                                    <p class="mt-1 line-clamp-1 text-sm text-slate-500 dark:text-slate-400">{{ $datosSesion['lugar'] }}</p>
                                                                </div>
                                                            </div>
                                                            <div class="flex shrink-0 items-center gap-3">
                                                                @if ($datosSesion['hora_fin'])
                                                                    <span class="text-xs font-bold text-slate-400">hasta {{ $datosSesion['hora_fin'] }}</span>
                                                                @endif
                                                                <span class="text-slate-400 transition group-open:rotate-180">⌄</span>
                                                            </div>
                                                        </summary>
                                                        <div class="grid gap-3 border-t border-slate-200 p-4 dark:border-white/10 sm:grid-cols-3">
                                                            <div class="rounded-xl bg-white p-3 dark:bg-white/5">
                                                                <p class="text-[11px] font-bold uppercase text-slate-400">Horario</p>
                                                                <p class="mt-1 text-sm font-bold text-sidan-900 dark:text-white">{{ $datosSesion['hora_inicio'] }}{{ $datosSesion['hora_fin'] ? ' – ' . $datosSesion['hora_fin'] : '' }}</p>
                                                            </div>
                                                            <div class="rounded-xl bg-white p-3 dark:bg-white/5">
                                                                <p class="text-[11px] font-bold uppercase text-slate-400">Cupo</p>
                                                                <p class="mt-1 text-sm font-bold text-sidan-900 dark:text-white">{{ $datosSesion['cupo'] }}</p>
                                                            </div>
                                                            <div class="rounded-xl bg-white p-3 dark:bg-white/5">
                                                                <p class="text-[11px] font-bold uppercase text-slate-400">Reserva</p>
                                                                <p class="mt-1 text-sm font-bold text-sidan-900 dark:text-white">{{ $datosSesion['reserva'] }}</p>
                                                            </div>
                                                        </div>
                                                    </details>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                @if ($items->isNotEmpty())
                    <section class="sidan-panel hidden" data-panel="productos" id="productos">
                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">Productos y servicios</p>
                                    <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white sm:text-3xl">Descubre la oferta</h2>
                                </div>
                                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-black text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $items->count() }} disponible{{ $items->count() === 1 ? '' : 's' }}</span>
                            </div>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach ($items as $item)
                                    @php
                                        $medioItem = $item->medios->firstWhere('es_portada', true) ?: $item->medios->first();
                                        $imagenItem = $urlMedio($medioItem?->url) ?: $actividad['imagen'];
                                        $ahoraItem = now();
                                        $disponibleItem = (!$item->venta_desde || !$ahoraItem->lt($item->venta_desde))
                                            && (!$item->venta_hasta || !$ahoraItem->gt($item->venta_hasta));
                                    @endphp
                                    <article class="sidan-producto overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/50 dark:border-white/10 dark:bg-white/[0.025] {{ $loop->index >= 6 ? 'hidden' : '' }}" data-product-index="{{ $loop->index }}">
                                        <div class="relative aspect-[4/3] overflow-hidden bg-slate-100 dark:bg-white/5">
                                            <img src="{{ $imagenItem }}" alt="{{ $item->nombre }}" class="h-full w-full object-cover transition duration-300 hover:scale-[1.03]" loading="lazy">
                                            <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-black backdrop-blur {{ $disponibleItem ? 'bg-emerald-500/90 text-white' : 'bg-slate-900/75 text-white' }}">{{ $disponibleItem ? 'Disponible' : 'Fuera de fecha' }}</span>
                                        </div>
                                        <div class="p-4">
                                            <p class="text-[11px] font-black uppercase tracking-wide text-sidan-500">{{ ucfirst($item->tipo) }}</p>
                                            <h3 class="mt-1 line-clamp-2 font-black text-sidan-900 dark:text-white">{{ $item->nombre }}</h3>
                                            @if ($item->descripcion)
                                                <p class="mt-2 line-clamp-2 text-sm leading-5 text-slate-500 dark:text-slate-400">{{ $item->descripcion }}</p>
                                            @endif
                                            <div class="mt-4 flex items-end justify-between gap-3 border-t border-slate-200 pt-4 dark:border-white/10">
                                                <div>
                                                    <p class="text-[10px] font-bold uppercase text-slate-400">Desde</p>
                                                    <p class="text-lg font-black text-sidan-900 dark:text-white">${{ number_format((float) $item->precio, 2) }}</p>
                                                </div>
                                                @if ($item->variantes->isNotEmpty())
                                                    <span class="text-xs font-bold text-slate-400">{{ $item->variantes->count() }} variante{{ $item->variantes->count() === 1 ? '' : 's' }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            @if ($items->count() > 6)
                                <div class="mt-6 text-center">
                                    <button type="button" id="btnMasProductos" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-black text-sidan-900 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:text-white">Mostrar más productos</button>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                @if ($promociones->isNotEmpty())
                    <section class="sidan-panel hidden" data-panel="promociones">
                        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.18em] text-amber-500">Promociones</p>
                                <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white sm:text-3xl">Aprovecha antes de que terminen</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Estas condiciones se aplicarán cuando el módulo de compra y pago esté disponible.</p>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-2">
                                @foreach ($promociones as $promocion)
                                    @php
                                        $estadoPromo = $promocion->estaVigente() ? 'Vigente' : ($promocion->esProxima() ? 'Próximamente' : 'Finalizada');
                                    @endphp
                                    <article class="relative overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-emerald-50 p-5 dark:border-amber-500/20 dark:from-amber-500/10 dark:via-white/[0.03] dark:to-emerald-500/10">
                                        <div class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-amber-300/20 blur-2xl"></div>
                                        <div class="relative">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <span class="inline-flex rounded-lg bg-sidan-900 px-3 py-1.5 text-sm font-black text-white">{{ $promocion->textoDescuento() }} OFF</span>
                                                    <h3 class="mt-3 text-lg font-black text-sidan-900 dark:text-white">{{ $promocion->nombre }}</h3>
                                                </div>
                                                <span class="rounded-full bg-white/80 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-slate-600 shadow-sm dark:bg-white/10 dark:text-slate-300">{{ $estadoPromo }}</span>
                                            </div>

                                            @if ($promocion->codigo)
                                                <button type="button" class="btnCopiarPromo mt-4 flex w-full items-center justify-between rounded-xl border border-dashed border-amber-300 bg-white/80 px-4 py-3 text-left dark:border-amber-500/30 dark:bg-white/5" data-codigo="{{ $promocion->codigo }}">
                                                    <span>
                                                        <span class="block text-[10px] font-bold uppercase tracking-wide text-slate-400">Código</span>
                                                        <span class="mt-0.5 block font-mono text-base font-black tracking-widest text-sidan-900 dark:text-white">{{ $promocion->codigo }}</span>
                                                    </span>
                                                    <span class="text-xs font-black text-amber-600 dark:text-amber-300">Copiar</span>
                                                </button>
                                            @else
                                                <div class="mt-4 rounded-xl bg-emerald-100/70 px-4 py-3 text-sm font-bold text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">Se aplica automáticamente</div>
                                            @endif

                                            <div class="mt-4 space-y-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                                <p>
                                                    <strong class="text-slate-700 dark:text-slate-200">Aplica a:</strong>
                                                    {{ $promocion->items->isEmpty() ? 'todos los productos y servicios' : $promocion->items->pluck('nombre')->take(3)->implode(', ') }}
                                                    @if ($promocion->items->count() > 3)
                                                        y {{ $promocion->items->count() - 3 }} más
                                                    @endif
                                                </p>
                                                @if ($promocion->monto_minimo !== null)
                                                    <p><strong class="text-slate-700 dark:text-slate-200">Compra mínima:</strong> ${{ number_format((float) $promocion->monto_minimo, 2) }}</p>
                                                @endif
                                                @if ($promocion->vigente_desde || $promocion->vigente_hasta)
                                                    <p><strong class="text-slate-700 dark:text-slate-200">Vigencia:</strong> {{ $promocion->vigente_desde?->format('d/m/Y') ?: 'ahora' }} – {{ $promocion->vigente_hasta?->format('d/m/Y') ?: 'sin límite' }}</p>
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
                    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="p-6 sm:p-8">
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">Ubicación</p>
                            <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white sm:text-3xl">Encuentra el lugar fácilmente</h2>
                            <div class="mt-5 rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                <p class="font-black text-sidan-900 dark:text-white">{{ $actividad['lugar'] }}</p>
                                @if ($actividad['direccion'])
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $actividad['direccion'] }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($actividad['mapa_disponible'])
                            <iframe src="{{ $actividad['mapa_embed_url'] }}" class="h-[420px] w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Mapa de {{ $actividad['titulo'] }}"></iframe>
                            <div class="border-t border-slate-200 p-4 text-right dark:border-white/10">
                                <a href="{{ $actividad['mapa_url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-black text-sidan-900 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:text-white">Abrir mapa →</a>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-sidan-900">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-sidan-500">Resumen rápido</p>
                    <div class="mt-4 space-y-4 text-sm">
                        <div><p class="text-xs font-bold uppercase text-slate-400">Fecha</p><p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['fecha'] }} · {{ $actividad['hora'] }}</p></div>
                        <div><p class="text-xs font-bold uppercase text-slate-400">Lugar</p><p class="mt-1 font-black text-sidan-900 dark:text-white">{{ $actividad['lugar'] }}</p></div>
                        @if ($sesiones->isNotEmpty())
                            <div>
                                <p class="text-xs font-bold uppercase text-slate-400">Programación</p>
                                <button type="button" data-panel-target="cronograma" class="mt-1 font-black text-sidan-500 hover:underline">{{ $sesiones->count() }} sesión{{ $sesiones->count() === 1 ? '' : 'es' }} →</button>
                            </div>
                        @endif
                        @if ($items->isNotEmpty())
                            <div>
                                <p class="text-xs font-bold uppercase text-slate-400">Oferta</p>
                                <button type="button" data-panel-target="productos" class="mt-1 font-black text-sidan-500 hover:underline">{{ $items->count() }} producto{{ $items->count() === 1 ? '' : 's' }}/servicio{{ $items->count() === 1 ? '' : 's' }} →</button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="rounded-[1.75rem] bg-gradient-to-br from-sidan-900 to-[#0d6a43] p-5 text-white shadow-lg">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-green-300">¿Te interesa?</p>
                    <h3 class="mt-2 text-xl font-black">{{ $actividad['accion_texto'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-white/65">{{ $actividad['participacion_descripcion'] }}</p>
                    <a href="{{ $actividad['accion_url'] }}" class="mt-4 flex w-full items-center justify-center rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white transition hover:bg-green-600">{{ $actividad['accion_texto'] }}</a>
                </div>
            </aside>
        </div>
    </section>
</main>

<div id="modalGaleriaActividad" class="fixed inset-0 z-[1200] hidden items-center justify-center bg-slate-950/90 p-4 backdrop-blur-sm">
    <button type="button" id="cerrarGaleriaActividad" class="absolute right-5 top-5 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-2xl text-white">×</button>
    <img id="imagenGaleriaActividad" src="" alt="" class="max-h-[88vh] max-w-[92vw] rounded-2xl object-contain shadow-2xl">
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
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

        if (desplazar) {
            window.setTimeout(() => panel.scrollIntoView({ behavior: 'smooth', block: 'start' }), 60);
        }
    }

    document.querySelectorAll('[data-panel-target]').forEach(boton => {
        boton.addEventListener('click', function () {
            activarPanel(this.dataset.panelTarget, true);
        });
    });

    activarPanel(raiz?.dataset.panelInicial || 'resumen');

    function configurarMostrarMas(selector, botonId, lote) {
        const elementos = Array.from(document.querySelectorAll(selector));
        const boton = document.getElementById(botonId);
        let visibles = lote;

        if (!boton) return;

        boton.addEventListener('click', function () {
            visibles += lote;
            elementos.forEach((elemento, indice) => elemento.classList.toggle('hidden', indice >= visibles));

            if (visibles >= elementos.length) {
                boton.classList.add('hidden');
            }
        });
    }

    configurarMostrarMas('.sidan-producto', 'btnMasProductos', 6);

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
        });

        if (desplazar) {
            window.setTimeout(() => panelDia.scrollIntoView({ behavior: 'smooth', block: 'nearest' }), 60);
        }
    }

    botonesDia.forEach(boton => {
        boton.addEventListener('click', function () {
            activarDia(this.dataset.diaTarget, this.classList.contains('sidan-dia-resumen'));
        });
    });

    if (panelesDia.length > 0) {
        activarDia(panelesDia[0].dataset.diaPanel);
    }

    document.querySelectorAll('.btnCopiarPromo').forEach(boton => {
        boton.addEventListener('click', async function () {
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
        boton.addEventListener('click', function () {
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
