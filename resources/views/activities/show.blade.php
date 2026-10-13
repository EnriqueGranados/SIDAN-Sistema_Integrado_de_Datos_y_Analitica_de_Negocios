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
    <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">

        <div class="mb-7 rounded-xl bg-slate-100 px-5 py-4 dark:bg-white/10">
            <h2 class="text-xl font-black uppercase text-sidan-900 dark:text-white">
                Productos y servicios
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-300">
                Personaliza cada producto y prepara tus selecciones.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            @foreach ($items as $item)
                @php
                    $medioItem = $item->medios->firstWhere('es_portada', true)
                        ?: $item->medios->first();

                    $imagenItem = $urlMedio($medioItem?->url)
                        ?: $actividad['imagen'];

                    $variantes = $item->variantes
                        ->where('activo', true)
                        ->sortBy('orden')
                        ->values();

                    $configuracionCampos = ($camposPorItem ?? collect())
                        ->get($item->id_item_actividad);

                    $configuracionPrecio = ($preciosPorItem ?? collect())
                        ->get($item->id_item_actividad);

                    $campos = collect($configuracionCampos['fields'] ?? [])
                        ->filter(fn ($campo) => is_array($campo) && !empty($campo['key']))
                        ->values();

                    $reglasPrecio = collect($configuracionPrecio['rules'] ?? [])
                        ->filter(fn ($regla) => is_array($regla))
                        ->values();

                    $ahora = now();

                    $disponible = (bool) $item->activo
                        && (!$item->venta_desde || $ahora->gte($item->venta_desde))
                        && (!$item->venta_hasta || $ahora->lte($item->venta_hasta));

                    $minimo = max(1, (int) ($item->min_por_inscripcion ?? 1));

                    $maximo = $item->max_por_inscripcion !== null
                        ? (int) $item->max_por_inscripcion
                        : null;

                    $stockItem = $item->stock_total !== null
                        ? (int) $item->stock_total
                        : null;

                    $limite = $maximo;

                    if ($stockItem !== null) {
                        $limite = $limite === null
                            ? $stockItem
                            : min($limite, $stockItem);
                    }

                    $puedeConfigurar = $disponible
                        && ($limite === null || $limite >= $minimo);

                @endphp

                <article
                    class="sidan-producto flex flex-col rounded-2xl border border-slate-100 bg-white p-4 shadow-[0_5px_25px_rgba(0,0,0,0.09)] dark:border-white/10 dark:bg-sidan-900 {{ $loop->index >= 6 ? 'hidden' : '' }}"
                    data-producto-config
                    data-item-id="{{ $item->id_item_actividad }}"
                    data-precio-base="{{ (float) $item->precio }}"
                    data-minimo="{{ $minimo }}"
                    data-maximo="{{ $limite ?? '' }}"
                    data-stock="{{ $stockItem ?? '' }}"
                    data-reglas='@json($reglasPrecio->all())'
                >
                    <div class="flex h-44 items-center justify-center rounded-xl bg-slate-50 p-3 dark:bg-white/5">
                        <img
                            src="{{ $imagenItem }}"
                            alt="{{ $item->nombre }}"
                            loading="lazy"
                            class="h-full w-full object-contain"
                        >
                    </div>

                    <div class="mt-4 flex flex-1 flex-col">
                        <span class="text-[11px] font-bold uppercase tracking-wide text-sidan-500">
                            {{ ucfirst($item->tipo) }}
                        </span>

                        <h3 class="mt-1 text-lg font-black text-sidan-900 dark:text-white">
                            {{ $item->nombre }}
                        </h3>

                        @if ($item->descripcion)
                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                {{ $item->descripcion }}
                            </p>
                        @endif

                        

                        @if ($campos->isNotEmpty())
                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach ($campos as $campo)
                                    @php
                                        $clave = (string) $campo['key'];
                                        $tipoCampo = (string) ($campo['type'] ?? 'texto');
                                        $requerido = (bool) ($campo['required'] ?? false);

                                        $opciones = is_array($campo['options'] ?? null)
                                            ? $campo['options']
                                            : [];

                                        $idCampo = 'producto-'
                                            . $item->id_item_actividad
                                            . '-campo-'
                                            . $loop->index;
                                    @endphp

                                    <div class="{{ $tipoCampo === 'texto' ? 'sm:col-span-2' : '' }}">
                                        <label
                                            for="{{ $idCampo }}"
                                            class="mb-1.5 block text-sm font-bold text-sidan-900 dark:text-white"
                                        >
                                            {{ $campo['label'] ?? ucfirst($clave) }}

                                            @if ($requerido)
                                                <span class="text-red-600">*</span>
                                            @endif
                                        </label>

                                        @if ($tipoCampo === 'lista')
                                            <select
                                                id="{{ $idCampo }}"
                                                data-campo-clave="{{ $clave }}"
                                                data-requerido="{{ $requerido ? '1' : '0' }}"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-white/20 dark:bg-sidan-950 dark:text-white"
                                                @disabled(!$puedeConfigurar)
                                            >
                                                <option value="">Selecciona una opción</option>

                                                @foreach ($opciones as $opcion)
                                                    <option value="{{ $opcion }}">
                                                        {{ $opcion }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        @elseif ($tipoCampo === 'texto')
                                            <input
                                                id="{{ $idCampo }}"
                                                type="text"
                                                maxlength="200"
                                                data-campo-clave="{{ $clave }}"
                                                data-requerido="{{ $requerido ? '1' : '0' }}"
                                                placeholder="Escribe {{ strtolower($campo['label'] ?? $clave) }}"
                                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-white/20 dark:bg-sidan-950 dark:text-white"
                                                @disabled(!$puedeConfigurar)
                                            >

                                        @else
                                            <p class="text-xs text-amber-700">
                                                Tipo de campo no disponible:
                                                {{ $tipoCampo }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-auto pt-5">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-white/10">
                                <div>
                                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                                        Precio unitario
                                    </p>

                                    <p
                                        class="text-2xl font-black text-red-700 dark:text-red-400"
                                        data-precio-unitario
                                    >
                                        ${{ number_format((float) $item->precio, 2) }}
                                    </p>
                                </div>

                                <div
                                    class="flex items-center rounded-full border border-slate-200 dark:border-white/20"
                                    data-cantidad-control
                                >
                                    <button
                                        type="button"
                                        data-restar
                                        disabled
                                        class="px-3 py-2 font-black text-red-700 disabled:text-slate-300"
                                        aria-label="Restar cantidad"
                                    >
                                        −
                                    </button>

                                    <output
                                        data-cantidad
                                        class="min-w-7 text-center text-sm font-black dark:text-white"
                                    >
                                        0
                                    </output>

                                    <button
                                        type="button"
                                        data-sumar
                                        class="px-3 py-2 font-black text-red-700 disabled:text-slate-300"
                                        @disabled(!$puedeConfigurar)
                                        aria-label="Sumar cantidad"
                                    >
                                        +
                                    </button>
                                </div>
                            </div>

                            <p
                                class="mt-2 text-xs text-slate-500 dark:text-slate-400"
                                data-disponibilidad
                            >
                                {{ $puedeConfigurar
                                    ? 'Configura el producto para continuar.'
                                    : 'No disponible actualmente.' }}
                            </p>

                            <p
                                class="mt-2 hidden text-xs font-semibold text-red-600"
                                data-error
                                role="alert"
                            ></p>

                            <button
                                type="button"
                                data-agregar-configuracion
                                class="mt-4 w-full rounded-xl bg-sidan-500 px-4 py-3 text-sm font-black text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:opacity-40"
                                @disabled(!$puedeConfigurar)
                            >
                                Agregar configuración
                            </button>

                            <div
                                class="mt-4 hidden rounded-xl bg-slate-50 p-3 dark:bg-white/5"
                                data-resumen-configuraciones
                            >
                                <p class="mb-2 text-sm font-black text-sidan-900 dark:text-white">
                                    Configuraciones preparadas
                                </p>

                                <div
                                    class="space-y-2"
                                    data-lineas-configuracion
                                ></div>

                                <p class="mt-3 border-t border-slate-200 pt-2 text-sm font-black text-sidan-900 dark:border-white/10 dark:text-white">
                                    Subtotal:
                                    <span data-subtotal>$0.00</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($items->count() > 6)
            <div class="mt-7 text-center">
                <button
                    type="button"
                    id="btnMasProductos"
                    class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-black text-sidan-900 dark:text-white"
                >
                    Mostrar más productos
                </button>
            </div>
        @endif

        <p class="mt-6 text-xs leading-5 text-slate-500 dark:text-slate-400">
            Las configuraciones de esta pantalla son una preparación visual:
            aún no generan una orden ni reservan existencias.
        </p>
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

    // Configurador visual de productos.
    // No registra pedidos ni persiste selecciones.
    document.querySelectorAll('[data-producto-config]').forEach(tarjeta => {

        const reglas = JSON.parse(tarjeta.dataset.reglas || '[]');
        const variantes = JSON.parse(tarjeta.dataset.variantes || '[]');

        const selectorVariante = tarjeta.querySelector('[data-variante]');
        const campos = Array.from(
            tarjeta.querySelectorAll('[data-campo-clave]')
        );

        const sumar = tarjeta.querySelector('[data-sumar]');
        const restar = tarjeta.querySelector('[data-restar]');
        const cantidadTexto = tarjeta.querySelector('[data-cantidad]');
        const precioTexto = tarjeta.querySelector('[data-precio-unitario]');
        const errorTexto = tarjeta.querySelector('[data-error]');
        const botonAgregar = tarjeta.querySelector('[data-agregar-configuracion]');

        const contenedorResumen = tarjeta.querySelector(
            '[data-resumen-configuraciones]'
        );

        const lineasContenedor = tarjeta.querySelector(
            '[data-lineas-configuracion]'
        );

        const subtotalTexto = tarjeta.querySelector('[data-subtotal]');

        const disponibilidadTexto = tarjeta.querySelector(
            '[data-disponibilidad]'
        );

        const limiteItem = tarjeta.dataset.maximo === ''
            ? Infinity
            : Number(tarjeta.dataset.maximo);

        const stockItem = tarjeta.dataset.stock === ''
            ? Infinity
            : Number(tarjeta.dataset.stock);

        const minimo = Number(tarjeta.dataset.minimo || 1);

        const configuraciones = [];

        let cantidad = 0;

        const dinero = valor => '$' + Number(valor).toFixed(2);

        const varianteElegida = () => variantes.find(
            variante => String(variante.id) === selectorVariante?.value
        );

        const unidadesPreparadas = () => configuraciones.reduce(
            (total, linea) => total + linea.cantidad,
            0
        );

        const stockVariantePreparado = id => configuraciones
            .filter(linea => linea.varianteId === id)
            .reduce((total, linea) => total + linea.cantidad, 0);

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
                    String(valores[regla.field_key] ?? '') ===
                    String(regla.option ?? '')
                ) {
                    precio += Number(regla.price_increment || 0);
                }
            });

            return Math.max(0, Math.round(precio * 100) / 100);
        }

        function limiteActual() {
            return Math.max(
                0,
                Math.min(limiteItem, stockItem) - unidadesPreparadas()
            );
        }

        function validar() {
            for (const campo of campos) {
                if (
                    campo.dataset.requerido === '1' &&
                    !campo.value.trim()
                ) {
                    return `Completa el campo ${campo.dataset.campoClave}.`;
                }
            }

            return '';
        }

        function mostrarError(mensaje) {
            errorTexto.textContent = mensaje;
            errorTexto.classList.toggle('hidden', !mensaje);
        }

        function refrescar() {
            precioTexto.textContent = dinero(precioActual());

            const limite = limiteActual();

            if (cantidad > limite) {
                cantidad = limite >= minimo ? limite : 0;
            }

            cantidadTexto.textContent = cantidad;

            restar.disabled = cantidad === 0;

            sumar.disabled =
                sumar.dataset.bloqueado === '1'
                || limite < minimo
                || cantidad >= limite;
        }

        function renderResumen() {
            lineasContenedor.replaceChildren();

            configuraciones.forEach((linea, indice) => {
                const fila = document.createElement('div');

                fila.className =
                    'flex items-start justify-between gap-2 ' +
                    'rounded-lg border border-slate-200 p-2 ' +
                    'text-xs dark:border-white/10';

                const texto = document.createElement('span');

                const detalle = Object.entries(linea.campos)
                    .map(([clave, valor]) => `${clave}: ${valor}`)
                    .join(' · ');

                texto.textContent =
                    `${linea.cantidad} × ` +
                    `${detalle} — ` +
                    dinero(linea.precio * linea.cantidad);

                const quitar = document.createElement('button');

                quitar.type = 'button';
                quitar.className = 'shrink-0 font-black text-red-700';
                quitar.textContent = 'Quitar';

                quitar.addEventListener('click', () => {
                    configuraciones.splice(indice, 1);

                    renderResumen();
                    refrescar();
                });

                fila.append(texto, quitar);
                lineasContenedor.append(fila);
            });

            contenedorResumen.classList.toggle(
                'hidden',
                configuraciones.length === 0
            );

            const subtotal = configuraciones.reduce(
                (total, linea) => total + linea.precio * linea.cantidad,
                0
            );

            subtotalTexto.textContent = dinero(subtotal);

            const unidades = unidadesPreparadas();

            if (disponibilidadTexto && botonAgregar.disabled === false) {
                disponibilidadTexto.textContent = unidades > 0
                    ? `${unidades} unidad${unidades === 1 ? '' : 'es'} preparada${unidades === 1 ? '' : 's'}.`
                    : 'Configura el producto para continuar.';
            }
        }

        sumar.dataset.bloqueado = sumar.disabled ? '1' : '0';

        sumar.addEventListener('click', () => {
            mostrarError('');

            if (cantidad === 0) {
                if (limiteActual() >= minimo) {
                    cantidad = minimo;
                }
            } else if (cantidad < limiteActual()) {
                cantidad++;
            }

            refrescar();
        });

        restar.addEventListener('click', () => {
            cantidad = cantidad <= minimo
                ? 0
                : cantidad - 1;

            refrescar();
        });

        [...campos, selectorVariante]
            .filter(Boolean)
            .forEach(campo => {
                campo.addEventListener('change', () => {
                    mostrarError('');
                    refrescar();
                });
            });

        campos.forEach(campo => {
            campo.addEventListener('change', () => {
                mostrarError('');
                refrescar();
            });

            campo.addEventListener('input', () => {
                mostrarError('');
                refrescar();
            });
        });

        botonAgregar.addEventListener('click', () => {
            const error = validar();

            if (error) {
                mostrarError(error);
                return;
            }

            if (cantidad < minimo) {
                mostrarError(
                    `Selecciona al menos ${minimo} unidad(es).`
                );
                return;
            }

            if (cantidad > limiteActual()) {
                mostrarError(
                    'La cantidad supera el stock o límite disponible.'
                );

                refrescar();
                return;
            }

            const variante = varianteElegida();

            configuraciones.push({
                idItem: Number(tarjeta.dataset.itemId),
                campos: Object.fromEntries(
                    campos.map(campo => [
                        campo.dataset.campoClave,
                        campo.value.trim()
                    ])
                ),
                cantidad,
                precio: precioActual()
            });

            cantidad = 0;

            campos.forEach(campo => {
                campo.value = '';
            });

            if (selectorVariante) {
                selectorVariante.value = '';
            }

            mostrarError('');
            renderResumen();
            refrescar();
        });

        refrescar();
    });

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
