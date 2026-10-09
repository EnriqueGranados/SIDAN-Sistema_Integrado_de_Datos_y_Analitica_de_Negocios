@extends('layouts.public')

@section('title', 'SIDAN | Descubre actividades')

@section('content')

<style>
    #loader{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:radial-gradient(circle at center,rgba(34,197,94,.08),transparent 45%),#07111f;transition:opacity .8s ease,visibility .8s ease}
    #loader.hidden{opacity:0;visibility:hidden;pointer-events:none}
    .loader-letter{display:inline-block;font-family:'Figtree',sans-serif;font-size:clamp(4rem,12vw,10rem);font-weight:800;letter-spacing:-.06em;opacity:0;transform:translateY(25px);animation:letterIn .65s cubic-bezier(.22,1,.36,1) forwards;color:#fff;text-shadow:0 0 30px rgba(15,45,91,.45)}
    .loader-letter:nth-child(1){animation-delay:.05s}.loader-letter:nth-child(2){animation-delay:.15s}.loader-letter:nth-child(3){animation-delay:.25s}.loader-letter:nth-child(4){animation-delay:.35s}.loader-letter:nth-child(5){animation-delay:.45s}
    @keyframes letterIn{from{opacity:0;transform:translateY(25px) scale(.96)}to{opacity:1;transform:translateY(0) scale(1)}}
    [x-cloak]{display:none!important}
</style>

{{-- LOADER --}}
<div id="loader">
    <div class="flex gap-2">
        <span class="loader-letter">S</span>
        <span class="loader-letter">I</span>
        <span class="loader-letter">D</span>
        <span class="loader-letter">A</span>
        <span class="loader-letter">N</span>
    </div>
</div>

{{-- HEADER PÚBLICO --}}
@include('partials.public-header')

@if (!empty($modoPreview))
    <div class="border-b border-amber-300/40 bg-amber-50 px-4 py-3 text-center text-sm font-bold text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
        Vista previa administrativa: aquí también pueden aparecer actividades programadas o fuera de su ventana pública.
    </div>
@endif

<main
    x-data="welcomeExplorer()"
    data-suggestions-url="{{ route('activities.suggestions') }}"
    @keydown.escape.window="cerrarTodo()"
>

    {{-- ===================================================== --}}
    {{-- HERO --}}
    {{-- ===================================================== --}}
    <section id="inicio" class="relative overflow-hidden">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[520px] bg-[radial-gradient(circle_at_20%_20%,rgba(34,197,94,0.13),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(15,45,91,0.14),transparent_35%)] dark:bg-[radial-gradient(circle_at_20%_20%,rgba(34,197,94,0.10),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(23,69,127,0.35),transparent_35%)]"></div>

        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:py-16 lg:grid-cols-[1.04fr_.96fr] lg:items-center lg:px-8 lg:py-20">

            <div>
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-sm font-bold text-green-700 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">
                    <span class="h-2 w-2 rounded-full bg-sidan-500"></span>
                    Explora. Descubre. Participa.
                </div>

                <h1 class="max-w-3xl text-4xl font-black leading-[1.05] tracking-tight text-sidan-900 sm:text-5xl md:text-6xl dark:text-white">
                    Encuentra algo que <span class="text-sidan-500">valga la pena vivir.</span>
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg dark:text-slate-300">
                    Congresos, talleres, excursiones, ventas, actividades académicas y mucho más. Mira qué está pasando y encuentra algo que conecte contigo.
                </p>

                {{-- BUSCADOR --}}
                <form
                    method="GET"
                    action="{{ route('activities.index') }}"
                    @submit="cerrarSugerencias()"
                    class="mt-8 rounded-2xl border border-slate-200 bg-white p-2 shadow-soft dark:border-white/10 dark:bg-white/5 dark:shadow-none"
                >
                    <div class="grid gap-2 md:grid-cols-[minmax(0,1fr)_220px_auto]">
                        <div class="relative" @click.outside="abiertos.actividad = false">
                            <label class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                                <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="11" cy="11" r="7"/>
                                    <path d="m20 20-3.5-3.5" stroke-linecap="round"/>
                                </svg>

                                <input
                                    x-model="filtros.q"
                                    @input.debounce.180ms="cargarSugerencias('actividad')"
                                    @focus="cargarSugerencias('actividad')"
                                    @keydown.escape.stop="abiertos.actividad = false"
                                    type="search"
                                    name="q"
                                    placeholder="Buscar actividades por nombre"
                                    autocomplete="off"
                                    class="w-full border-0 bg-transparent p-0 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white"
                                >
                            </label>

                            <div
                                x-show="abiertos.actividad"
                                x-cloak
                                x-transition
                                class="absolute left-0 right-0 top-[calc(100%+8px)] z-50 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-sidan-900"
                            >
                                <div x-show="cargando.actividad" class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                    Buscando actividades…
                                </div>

                                <template x-if="!cargando.actividad && sugerencias.actividad.length">
                                    <div class="max-h-80 overflow-y-auto p-2">
                                        <template x-for="item in sugerencias.actividad" :key="item.id">
                                            <button
                                                type="button"
                                                @click="seleccionarSugerencia('actividad', item)"
                                                class="flex w-full items-center gap-3 rounded-xl p-2.5 text-left transition hover:bg-slate-50 dark:hover:bg-white/5"
                                            >
                                                <img :src="item.imagen" :alt="item.titulo" class="h-12 w-14 shrink-0 rounded-lg object-cover">
                                                <span class="min-w-0 flex-1">
                                                    <span class="block truncate text-sm font-black text-sidan-900 dark:text-white" x-text="item.titulo"></span>
                                                    <span class="mt-0.5 block truncate text-xs text-slate-500 dark:text-slate-400">
                                                        <span x-text="item.categoria"></span>
                                                        · <span x-text="item.fecha"></span>
                                                        · <span x-text="item.lugar"></span>
                                                    </span>
                                                </span>
                                                <span class="text-sidan-500">→</span>
                                            </button>
                                        </template>
                                    </div>
                                </template>

                                <div
                                    x-show="!cargando.actividad && filtros.q.trim() !== '' && !sugerencias.actividad.length"
                                    class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400"
                                >
                                    No hay coincidencias rápidas. Presiona Enter para buscar en todo el catálogo.
                                </div>
                            </div>
                        </div>

                        <label class="flex items-center gap-2 rounded-xl border-t border-slate-100 px-3 py-2.5 md:border-l md:border-t-0 dark:border-white/10">
                            <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path d="M16 3v4M8 3v4M3 10h18"/>
                            </svg>
                            <input
                                x-model="filtros.fecha"
                                type="date"
                                name="fecha"
                                aria-label="Fecha de realización"
                                class="w-full border-0 bg-transparent p-0 text-sm font-semibold text-slate-700 focus:ring-0 dark:text-white dark:[color-scheme:dark]"
                            >
                        </label>

                        <button type="submit" class="rounded-xl bg-sidan-500 px-6 py-2.5 text-sm font-black text-white transition hover:bg-green-600">
                            Buscar
                        </button>
                    </div>
                </form>



                {{-- ACCESOS POPULARES, SIEMPRE LIMITADOS --}}
                @if ($categoriasPopulares->isNotEmpty())
                    <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                        <span class="font-bold text-slate-700 dark:text-slate-200">Explora:</span>
                        @foreach ($categoriasPopulares->take(4) as $categoria)
                            <a href="{{ route('activities.index', ['categoria' => $categoria]) }}" class="transition hover:text-sidan-500">
                                {{ $categoria }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- PROMOCIONES PRINCIPALES --}}
            @if (count($actividadesHero) <= 3)
                <div class="grid h-[430px] grid-cols-2 grid-rows-2 gap-3 sm:h-[500px]">
                    @foreach ($actividadesHero as $actividad)
                        <a
                            href="{{ $actividad['detalle_url'] }}"
                            @click.prevent="abrirActividad({{ Illuminate\Support\Js::from($actividad) }})"
                            class="group relative overflow-hidden rounded-3xl {{ $loop->first ? 'row-span-2' : '' }}"
                        >
                            <img src="{{ $actividad['imagen'] }}" alt="{{ $actividad['titulo'] }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent"></div>
                            <div class="absolute left-4 top-4 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-black text-slate-900 backdrop-blur">
                                {{ $actividad['categoria'] }}
                            </div>
                            <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-green-300">{{ $actividad['fecha'] }} · {{ $actividad['hora'] }}</p>
                                <h3 class="mt-1 text-lg font-black leading-tight text-white sm:text-xl">{{ $actividad['titulo'] }}</h3>
                                <p class="mt-2 hidden text-sm text-white/70 sm:block">{{ $actividad['lugar'] }}</p>
                                <span class="mt-3 inline-flex text-xs font-black text-green-300">Vista rápida →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 lg:grid lg:h-[500px] lg:grid-cols-2 lg:grid-rows-3 lg:overflow-visible lg:pb-0">
                    @foreach ($actividadesHero as $actividad)
                        <a
                            href="{{ $actividad['detalle_url'] }}"
                            @click.prevent="abrirActividad({{ Illuminate\Support\Js::from($actividad) }})"
                            class="group relative h-[360px] min-w-[82%] snap-start overflow-hidden rounded-3xl sm:min-w-[62%] lg:h-auto lg:min-w-0"
                        >
                            <img src="{{ $actividad['imagen'] }}" alt="{{ $actividad['titulo'] }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent"></div>
                            <div class="absolute left-4 top-4 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-black text-slate-900 backdrop-blur">
                                {{ $actividad['categoria'] }}
                            </div>
                            <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">
                                <p class="text-xs font-bold uppercase tracking-wider text-green-300">{{ $actividad['fecha'] }} · {{ $actividad['hora'] }}</p>
                                <h3 class="mt-1 line-clamp-2 text-lg font-black leading-tight text-white sm:text-xl">{{ $actividad['titulo'] }}</h3>
                                <span class="mt-3 inline-flex text-xs font-black text-green-300">Vista rápida →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </section>


    {{-- ===================================================== --}}
    {{-- DESTACADA --}}
    {{-- ===================================================== --}}
    @if ($destacada)
        <section class="mx-auto max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">

            <div class="mb-5 flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                        No te lo pierdas
                    </p>

                    <h2 class="mt-2 text-2xl font-black tracking-tight text-sidan-900 sm:text-3xl dark:text-white">
                        Destacado en SIDAN
                    </h2>
                </div>

                <a
                    href="{{ url('/actividades') }}"
                    class="hidden text-sm font-black text-slate-500 transition hover:text-sidan-500 sm:inline-flex dark:text-slate-400"
                >
                    Ver todas las actividades →
                </a>
            </div>

            <article class="group relative overflow-hidden rounded-[2rem] bg-sidan-900 text-white shadow-2xl shadow-sidan-900/15 dark:border dark:border-white/10 dark:shadow-none">

                <img
                    src="{{ $destacada['imagen'] }}"
                    alt="{{ $destacada['titulo'] }}"
                    class="absolute inset-0 h-full w-full object-cover opacity-35 transition duration-700 group-hover:scale-105"
                >

                <div class="absolute inset-0 bg-gradient-to-r from-sidan-950 via-sidan-900/95 to-sidan-900/40"></div>

                <div class="relative grid min-h-[320px] gap-8 px-6 py-10 sm:px-10 md:grid-cols-[1.2fr_.8fr] md:items-end lg:px-14 lg:py-14">

                    <div>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex rounded-full bg-sidan-500 px-3 py-1 text-xs font-black uppercase tracking-wider">
                                Destacada
                            </span>

                            <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-black text-white backdrop-blur-md">
                                {{ $destacada['categoria'] }}
                            </span>

                            @foreach ($destacada['etiquetas'] as $etiqueta)
                                <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white/80 backdrop-blur-md">
                                    #{{ $etiqueta }}
                                </span>
                            @endforeach
                        </div>

                        <h2 class="mt-4 max-w-2xl text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">
                            {{ $destacada['titulo'] }}
                        </h2>

                        <p class="mt-4 max-w-2xl text-sm leading-6 text-white/75 sm:text-base">
                            {{ $destacada['descripcion'] }}
                        </p>

                        <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-sm font-semibold text-white/85">
                            <span>{{ $destacada['fecha'] }} · {{ $destacada['hora'] }}</span>
                            <span>{{ $destacada['lugar'] }}</span>
                            <span>{{ $destacada['precio'] }}</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 md:justify-end">
                        <button
                            type="button"
                            @click="abrirActividad({{ Illuminate\Support\Js::from($destacada) }})"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-5 py-3.5 text-sm font-black text-white backdrop-blur transition hover:bg-white/20"
                        >
                            Vista rápida
                        </button>

                        <a
                            href="{{ $destacada['detalle_url'] }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3.5 text-sm font-black text-sidan-900 transition hover:-translate-y-0.5 hover:bg-green-50"
                        >
                            Ver detalles →
                        </a>
                    </div>

                </div>
            </article>

        </section>
    @endif


    {{-- ===================================================== --}}
    {{-- DESCUBRIMIENTO ESCALABLE --}}
    {{-- ===================================================== --}}
    @if ($categoriasPopulares->isNotEmpty() || $etiquetasPopulares->isNotEmpty())
        <section id="categorias" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="border-y border-slate-200 py-7 dark:border-white/10">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-md">
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                            Explora a tu manera
                        </p>
                        <h2 class="mt-1 text-xl font-black text-sidan-900 dark:text-white">
                            Accesos rápidos sin llenar la pantalla
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Aquí mostramos solo opciones populares. Para encontrar cualquiera de las demás, entra al catálogo y utiliza sus filtros.
                        </p>
                    </div>

                    <div class="grid flex-1 gap-5 md:grid-cols-2">
                        @if ($categoriasPopulares->isNotEmpty())
                            <div>
                                <p class="mb-3 text-xs font-black uppercase tracking-[0.16em] text-slate-400">Categorías populares</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($categoriasPopulares as $categoria)
                                        <a href="{{ route('activities.index', ['categoria' => $categoria]) }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                                            {{ $categoria }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($etiquetasPopulares->isNotEmpty())
                            <div>
                                <p class="mb-3 text-xs font-black uppercase tracking-[0.16em] text-slate-400">Etiquetas populares</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($etiquetasPopulares as $etiqueta)
                                        <a href="{{ route('activities.index', ['etiqueta' => $etiqueta]) }}" class="rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-500 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-400">
                                            #{{ $etiqueta }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif


    {{-- ===================================================== --}}
    {{-- SELECCIÓN DEL WELCOME --}}
    {{-- ===================================================== --}}
    <section id="actividades" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">

        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                    Ahora mismo
                </p>

                <h2 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 sm:text-4xl dark:text-white">
                    Algo podría interesarte 👀
                </h2>

                <p class="mt-2 text-slate-600 dark:text-slate-400">
                    Una selección de actividades que puedes descubrir ahora.
                </p>
            </div>

            <a
                href="{{ url('/actividades') }}"
                class="inline-flex items-center gap-2 text-sm font-black text-sidan-900 transition hover:text-sidan-500 dark:text-white"
            >
                Ver todas →
            </a>
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

            @foreach ($actividades as $actividad)

                <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1.5 hover:border-green-300 hover:shadow-soft dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-green-500/30 dark:hover:shadow-none">

                    <button
                        type="button"
                        @click="abrirActividad({{ Illuminate\Support\Js::from($actividad) }})"
                        class="relative block h-52 w-full overflow-hidden text-left"
                    >
                        <img
                            src="{{ $actividad['imagen'] }}"
                            alt="{{ $actividad['titulo'] }}"
                            class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                        >

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent"></div>

                        <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                            <span class="rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-black text-sidan-900 shadow-sm">
                                {{ $actividad['categoria'] }}
                            </span>

                            <span class="rounded-full bg-sidan-500 px-2.5 py-1 text-[11px] font-black text-white shadow-sm">
                                {{ $actividad['estado'] }}
                            </span>
                        </div>

                        <div class="absolute bottom-4 left-4 rounded-xl bg-slate-950/70 px-3 py-2 text-center text-white backdrop-blur-md">
                            <span class="block text-xs font-bold tracking-wide">
                                {{ $actividad['fecha'] }}
                            </span>
                        </div>
                    </button>

                    <div class="p-5">

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-sidan-500">
                                    {{ $actividad['organizacion'] }}
                                </p>

                                <h3 class="mt-1 text-xl font-black leading-snug text-sidan-900 transition group-hover:text-sidan-500 dark:text-white">
                                    {{ $actividad['titulo'] }}
                                </h3>
                            </div>

                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700 dark:bg-white/10 dark:text-slate-200">
                                {{ $actividad['precio'] }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach (collect($actividad['etiquetas'] ?? [])->take(3) as $etiqueta)
                                <span class="rounded-full bg-slate-50 px-2 py-1 text-[10px] font-bold text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                    #{{ $etiqueta }}
                                </span>
                            @endforeach
                        </div>

                        <p class="mt-3 line-clamp-2 min-h-[48px] text-sm leading-6 text-slate-600 dark:text-slate-400">
                            {{ $actividad['descripcion'] }}
                        </p>

                        <div class="mt-4 space-y-2 text-sm text-slate-500 dark:text-slate-400">

                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-sidan-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 7v5l3 2"/>
                                </svg>

                                <span>{{ $actividad['hora'] }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-sidan-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                    <circle cx="12" cy="10" r="2.5"/>
                                </svg>

                                <span class="line-clamp-1">
                                    {{ $actividad['lugar'] }}
                                </span>
                            </div>

                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-2">

                            <button
                                type="button"
                                @click="abrirActividad({{ Illuminate\Support\Js::from($actividad) }})"
                                class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-black text-sidan-900 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:text-white"
                            >
                                Vista rápida
                            </button>

                            <a
                                href="{{ $actividad['detalle_url'] }}"
                                class="inline-flex items-center justify-center rounded-xl bg-sidan-900 px-4 py-3 text-sm font-black text-white transition hover:bg-sidan-700 dark:bg-white dark:text-sidan-950 dark:hover:bg-slate-200"
                            >
                                Ver detalles
                            </a>

                        </div>

                    </div>

                </article>

            @endforeach

        </div>

    </section>


    {{-- ===================================================== --}}
    {{-- CÓMO FUNCIONA --}}
    {{-- ===================================================== --}}
    <section id="como-funciona" class="border-y border-slate-200 bg-white/70 dark:border-white/10 dark:bg-white/[0.025]">

        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">

            <div class="mx-auto max-w-2xl text-center">

                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                    Sin complicaciones
                </p>

                <h2 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 sm:text-4xl dark:text-white">
                    De curioso a participante en 3 pasos.
                </h2>

                <p class="mt-3 text-slate-600 dark:text-slate-400">
                    Puedes explorar todo sin iniciar sesión. Solo te pediremos entrar cuando realmente quieras participar, reservar o comprar.
                </p>

            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-3">

                @foreach ([
                    ['01', 'Explora', 'Busca por categoría, etiqueta, ubicación o simplemente descubre lo que está disponible.'],
                    ['02', 'Descubre', 'Revisa fecha, lugar, costo, disponibilidad, etiquetas y todos los detalles importantes.'],
                    ['03', 'Participa', 'Inscríbete, reserva o adquiere lo que necesites según el tipo de actividad.']
                ] as $paso)

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04]">

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-50 text-sm font-black text-green-700 dark:bg-green-500/10 dark:text-green-300">
                            {{ $paso[0] }}
                        </div>

                        <h3 class="mt-5 text-xl font-black text-sidan-900 dark:text-white">
                            {{ $paso[1] }}
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            {{ $paso[2] }}
                        </p>

                    </div>

                @endforeach

            </div>

        </div>

    </section>


    {{-- ===================================================== --}}
    {{-- CTA --}}
    {{-- ===================================================== --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">

        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-sidan-900 via-sidan-900 to-[#0d6a43] px-6 py-12 text-center text-white sm:px-10 lg:py-16">

            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-sidan-500/25 blur-3xl"></div>

            <div class="relative mx-auto max-w-2xl">

                <p class="text-sm font-black uppercase tracking-[0.18em] text-green-300">
                    Tu próxima experiencia
                </p>

                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">
                    Puede estar a un clic de distancia.
                </h2>

                <p class="mt-4 text-white/75">
                    Explora primero. Decide después. En SIDAN puedes conocer las actividades antes de crear una cuenta.
                </p>

                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">

                    <a
                        href="{{ url('/actividades') }}"
                        class="rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white transition hover:bg-green-600"
                    >
                        Explorar todas
                    </a>

                    @guest
                        <a
                            href="{{ route('register') }}"
                            class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white/15"
                        >
                            Crear mi cuenta
                        </a>
                    @endguest

                </div>

            </div>

        </div>

    </section>


    {{-- ===================================================== --}}
    {{-- MODAL VISTA RÁPIDA --}}
    {{-- ===================================================== --}}
    <div
        x-show="modalAbierto"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[100] overflow-y-auto bg-slate-950/70 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex min-h-full items-center justify-center" @click.self="cerrarActividad()">

            <div
                x-show="modalAbierto"
                x-transition
                class="relative w-full overflow-hidden rounded-[2rem] bg-white shadow-2xl transition-all duration-300 dark:border dark:border-white/10 dark:bg-sidan-900"
                :class="{
                    'max-w-6xl': imagenOrientacion === 'horizontal',
                    'max-w-5xl': imagenOrientacion === 'cuadrada',
                    'max-w-4xl': imagenOrientacion === 'vertical'
                }"
            >

                <button
                    type="button"
                    @click="cerrarActividad()"
                    class="absolute right-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-xl font-black text-slate-700 shadow-lg transition hover:bg-red-500 hover:text-white dark:bg-sidan-950 dark:text-white"
                >
                    ×
                </button>


                <template x-if="actividadModal">

                    <div
                        class="grid"
                        :class="{
                            'lg:grid-cols-[1.18fr_.82fr]': imagenOrientacion === 'horizontal',
                            'lg:grid-cols-[1fr_1fr]': imagenOrientacion === 'cuadrada',
                            'lg:grid-cols-[.72fr_1.28fr]': imagenOrientacion === 'vertical'
                        }"
                    >

                        {{-- IMAGEN --}}
                        <div
                            class="relative flex min-h-[300px] items-center justify-center overflow-hidden bg-slate-950 lg:max-h-[90vh]"
                            :class="{
                                'lg:min-h-[560px]': imagenOrientacion === 'horizontal',
                                'lg:min-h-[620px]': imagenOrientacion === 'cuadrada',
                                'lg:min-h-[720px]': imagenOrientacion === 'vertical'
                            }"
                        >

                            <img
                                :src="actividadModal.imagen"
                                alt=""
                                aria-hidden="true"
                                class="absolute inset-0 h-full w-full scale-110 object-cover opacity-30 blur-2xl"
                            >

                            <div class="absolute inset-0 bg-slate-950/35"></div>

                            <img
                                :src="actividadModal.imagen"
                                :alt="actividadModal.titulo"
                                @load="detectarOrientacionImagen($event)"
                                class="relative z-10 max-h-[48vh] w-full object-contain p-3 sm:p-5 lg:max-h-[90vh] lg:h-full"
                            >

                            <div class="pointer-events-none absolute inset-0 z-10 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent lg:bg-gradient-to-r lg:from-slate-950/25"></div>

                            <div class="absolute inset-x-0 bottom-0 p-6 lg:hidden">
                                <p
                                    class="text-xs font-black uppercase tracking-wider text-green-300"
                                    x-text="actividadModal.categoria"
                                ></p>

                                <h2
                                    class="mt-2 text-2xl font-black text-white"
                                    x-text="actividadModal.titulo"
                                ></h2>
                            </div>

                        </div>


                        {{-- INFORMACIÓN --}}
                        <div class="max-h-[90vh] overflow-y-auto p-6 sm:p-8 lg:p-10">

                            <div class="hidden lg:block">

                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700 dark:bg-green-500/10 dark:text-green-300"
                                        x-text="actividadModal.categoria"
                                    ></span>

                                    <span
                                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600 dark:bg-white/10 dark:text-slate-300"
                                        x-text="actividadModal.estado"
                                    ></span>
                                </div>

                                <h2
                                    class="mt-4 text-3xl font-black leading-tight text-sidan-900 dark:text-white"
                                    x-text="actividadModal.titulo"
                                ></h2>

                            </div>


                            {{-- ETIQUETAS --}}
                            <div class="mt-4 flex flex-wrap gap-2">

                                <template x-for="etiqueta in actividadModal.etiquetas" :key="etiqueta">
                                    <span
                                        class="rounded-full border border-slate-200 px-2.5 py-1 text-xs font-bold text-slate-500 dark:border-white/10 dark:text-slate-400"
                                        x-text="'#' + etiqueta"
                                    ></span>
                                </template>

                            </div>


                            <p
                                class="mt-5 text-sm leading-7 text-slate-600 dark:text-slate-300"
                                x-text="actividadModal.descripcion"
                            ></p>


                            {{-- DATOS --}}
                            <div class="mt-6 grid gap-3 sm:grid-cols-2">

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                        Fecha y hora
                                    </p>

                                    <p class="mt-1 font-black text-sidan-900 dark:text-white">
                                        <span x-text="actividadModal.fecha"></span>
                                        ·
                                        <span x-text="actividadModal.hora"></span>
                                    </p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                        Precio
                                    </p>

                                    <p
                                        class="mt-1 font-black text-sidan-900 dark:text-white"
                                        x-text="actividadModal.precio"
                                    ></p>
                                </div>

                                <div class="rounded-2xl bg-slate-50 p-4 sm:col-span-2 dark:bg-white/5">
                                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                                        Ubicación
                                    </p>

                                    <p
                                        class="mt-1 font-black text-sidan-900 dark:text-white"
                                        x-text="actividadModal.lugar"
                                    ></p>
                                </div>

                            </div>


                            {{-- MAPA --}}
                            <template x-if="actividadModal.mapa_disponible">

                                <div class="mt-5 overflow-hidden rounded-2xl border border-slate-200 dark:border-white/10">

                                    <iframe
                                        :src="actividadModal.mapa_embed_url"
                                        class="h-52 w-full border-0"
                                        loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                    ></iframe>

                                    <div class="flex items-center justify-between gap-4 p-4">

                                        <div>
                                            <p class="text-xs font-black uppercase tracking-wider text-sidan-500">
                                                Ubicación
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                Coordenadas registradas para este espacio.
                                            </p>
                                        </div>

                                        <a
                                            :href="actividadModal.mapa_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="shrink-0 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-black text-sidan-900 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:text-white"
                                        >
                                            Abrir mapa
                                        </a>

                                    </div>

                                </div>

                            </template>


                            {{-- ACCIONES --}}
                            <div class="mt-7 grid gap-3 sm:grid-cols-2">

                                <a
                                    :href="actividadModal.detalle_url"
                                    class="flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3.5 text-sm font-black text-sidan-900 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:text-white"
                                >
                                    Ver todos los detalles
                                </a>

                                <a
                                    :href="actividadModal.accion_url"
                                    class="flex items-center justify-center rounded-xl bg-sidan-500 px-5 py-3.5 text-sm font-black text-white transition hover:bg-green-600"
                                    x-text="actividadModal.accion_texto"
                                ></a>

                            </div>

                            <template x-if="actividadModal.requiere_cuenta">
                                <p class="mt-4 text-center text-xs leading-5 text-slate-400">
                                    Esta acción requiere iniciar sesión. Después de identificarte regresarás a esta actividad.
                                </p>
                            </template>

                        </div>

                    </div>

                </template>

            </div>

        </div>
    </div>

</main>


{{-- FOOTER PÚBLICO --}}
@include('partials.public-footer')


<script>
    function welcomeExplorer() {
        return {
            modalAbierto: false,
            actividadModal: null,
            imagenOrientacion: 'horizontal',
            solicitudes: {},

            filtros: {
                q: '',
                ubicacion: '',
                categoria: '',
                etiqueta: '',
                fecha: '',
            },

            sugerencias: {
                actividad: [],
                ubicacion: [],
                categoria: [],
                etiqueta: [],
            },

            abiertos: {
                actividad: false,
                ubicacion: false,
                categoria: false,
                etiqueta: false,
            },

            cargando: {
                actividad: false,
                ubicacion: false,
                categoria: false,
                etiqueta: false,
            },

            valorFiltro(tipo) {
                if (tipo === 'actividad') {
                    return this.filtros.q;
                }

                return this.filtros[tipo] || '';
            },

            async cargarSugerencias(tipo) {
                const valor = this.valorFiltro(tipo).trim();

                if (tipo === 'actividad' && valor === '') {
                    this.sugerencias.actividad = [];
                    this.abiertos.actividad = false;
                    return;
                }

                if (this.solicitudes[tipo]) {
                    this.solicitudes[tipo].abort();
                }

                const controlador = new AbortController();
                this.solicitudes[tipo] = controlador;
                this.cargando[tipo] = true;
                this.abiertos[tipo] = true;

                const params = new URLSearchParams({
                    tipo,
                    q: valor,
                });

                try {
                    const response = await fetch(
                        `${this.$root.dataset.suggestionsUrl}?${params.toString()}`,
                        {
                            headers: {
                                Accept: 'application/json',
                            },
                            signal: controlador.signal,
                        }
                    );

                    if (!response.ok) {
                        throw new Error('No se pudieron cargar las sugerencias.');
                    }

                    const data = await response.json();
                    this.sugerencias[tipo] = Array.isArray(data.items)
                        ? data.items
                        : [];
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        this.sugerencias[tipo] = [];
                    }
                } finally {
                    if (this.solicitudes[tipo] === controlador) {
                        this.cargando[tipo] = false;
                    }
                }
            },

            seleccionarSugerencia(tipo, item) {
                if (tipo === 'actividad') {
                    window.location.assign(item.url);
                    return;
                }

                this.filtros[tipo] = item.valor;
                this.abiertos[tipo] = false;
            },

            cerrarSugerencias() {
                Object.keys(this.abiertos).forEach(tipo => {
                    this.abiertos[tipo] = false;
                });
            },

            cerrarTodo() {
                this.cerrarSugerencias();

                if (this.modalAbierto) {
                    this.cerrarActividad();
                }
            },

            abrirActividad(actividad) {
                this.imagenOrientacion = 'horizontal';
                this.actividadModal = actividad;
                this.modalAbierto = true;
                document.documentElement.classList.add('overflow-hidden');
            },

            detectarOrientacionImagen(evento) {
                const imagen = evento.currentTarget;
                const ancho = imagen.naturalWidth || 0;
                const alto = imagen.naturalHeight || 0;

                if (!ancho || !alto) {
                    this.imagenOrientacion = 'horizontal';
                    return;
                }

                const relacion = ancho / alto;

                if (relacion > 1.2) {
                    this.imagenOrientacion = 'horizontal';
                } else if (relacion < 0.83) {
                    this.imagenOrientacion = 'vertical';
                } else {
                    this.imagenOrientacion = 'cuadrada';
                }
            },

            cerrarActividad() {
                this.modalAbierto = false;
                document.documentElement.classList.remove('overflow-hidden');

                setTimeout(() => {
                    this.actividadModal = null;
                }, 200);
            },
        };
    }

    window.addEventListener('load', () => {
        setTimeout(() => {
            document.getElementById('loader').classList.add('hidden');
        }, 2200);
    });
</script>

@endsection