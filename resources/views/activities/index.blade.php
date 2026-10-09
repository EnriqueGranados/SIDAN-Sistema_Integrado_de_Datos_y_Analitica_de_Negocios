@extends('layouts.public')

@section('title', 'SIDAN | Actividades')

@section('content')

<style>
    [x-cloak] {
        display: none !important;
    }

    html.dark select option {
        background-color: #07111f;
        color: #ffffff;
    }
</style>

{{-- HEADER PÚBLICO --}}
@include('partials.public-header')

<main
    x-data="catalogoActividades()"
    x-init="inicializarFiltros()"
    data-suggestions-url="{{ route('activities.suggestions') }}"
    data-q="{{ request('q', request('buscar', '')) }}"
    data-ubicacion="{{ request('ubicacion', '') }}"
    data-categoria="{{ request('categoria', '') }}"
    data-etiqueta="{{ request('etiqueta', '') }}"
    data-fecha="{{ request('fecha', '') }}"
    @keydown.escape.window="cerrarTodo()"
    class="min-h-screen"
>

    {{-- ===================================================== --}}
    {{-- CABECERA --}}
    {{-- ===================================================== --}}

    <section class="relative overflow-hidden border-b border-slate-200 dark:border-white/10">

        <div class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(circle_at_20%_20%,rgba(34,197,94,0.12),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(15,45,91,0.12),transparent_35%)] dark:bg-[radial-gradient(circle_at_20%_20%,rgba(34,197,94,0.08),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(23,69,127,0.28),transparent_35%)]"></div>

        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">

            <div class="max-w-3xl">

                <a
                    href="{{ route('welcome') }}"
                    class="inline-flex items-center gap-2 text-sm font-black text-slate-500 transition hover:text-sidan-500 dark:text-slate-400"
                >
                    <span>←</span>
                    Volver al inicio
                </a>

                <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-sm font-bold text-green-700 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">
                    <span class="h-2 w-2 rounded-full bg-sidan-500"></span>
                    Explora SIDAN
                </div>

                <h1 class="mt-5 text-4xl font-black tracking-tight text-sidan-900 sm:text-5xl dark:text-white">
                    Encuentra tu próxima
                    <span class="text-sidan-500">actividad.</span>
                </h1>

                <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg dark:text-slate-300">
                    Explora las actividades disponibles y encuentra fácilmente por nombre, ubicación, fecha, categoría o etiqueta.
                </p>

            </div>

        </div>

    </section>


    {{-- ===================================================== --}}
    {{-- FILTROS ESCALABLES --}}
    {{-- ===================================================== --}}

    <section class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur-xl dark:border-white/10 dark:bg-sidan-950/95">
        <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
            <form
                method="GET"
                action="{{ route('activities.index') }}"
                @submit="cerrarSugerencias()"
                class="space-y-3"
            >
                <div class="grid gap-3 xl:grid-cols-[1.45fr_1fr_.78fr_auto]">
                    <div class="relative" @click.outside="abiertos.actividad = false">
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-white/5">
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
                                placeholder="Nombre, tema, categoría, etiqueta…"
                                autocomplete="off"
                                class="w-full border-0 bg-transparent p-0 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white"
                            >
                        </label>

                        <div x-show="abiertos.actividad" x-cloak x-transition class="absolute left-0 right-0 top-[calc(100%+8px)] z-50 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-sidan-900">
                            <div x-show="cargando.actividad" class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">Buscando actividades…</div>
                            <template x-if="!cargando.actividad && sugerencias.actividad.length">
                                <div class="max-h-80 overflow-y-auto p-2">
                                    <template x-for="item in sugerencias.actividad" :key="item.id">
                                        <button type="button" @click="seleccionarSugerencia('actividad', item)" class="flex w-full items-center gap-3 rounded-xl p-2.5 text-left transition hover:bg-slate-50 dark:hover:bg-white/5">
                                            <img :src="item.imagen" :alt="item.titulo" class="h-12 w-14 shrink-0 rounded-lg object-cover">
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-sm font-black text-sidan-900 dark:text-white" x-text="item.titulo"></span>
                                                <span class="mt-0.5 block truncate text-xs text-slate-500 dark:text-slate-400">
                                                    <span x-text="item.categoria"></span> · <span x-text="item.fecha"></span> · <span x-text="item.lugar"></span>
                                                </span>
                                            </span>
                                            <span class="text-sidan-500">→</span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                            <div x-show="!cargando.actividad && filtros.q.trim() !== '' && !sugerencias.actividad.length" class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                Sin sugerencias rápidas. Presiona Enter para buscar todas las coincidencias.
                            </div>
                        </div>
                    </div>

                    <div class="relative" @click.outside="abiertos.ubicacion = false">
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                <circle cx="12" cy="10" r="2.5"/>
                            </svg>
                            <input
                                x-model="filtros.ubicacion"
                                @input.debounce.180ms="cargarSugerencias('ubicacion')"
                                @focus="cargarSugerencias('ubicacion')"
                                @keydown.escape.stop="abiertos.ubicacion = false"
                                type="search"
                                name="ubicacion"
                                placeholder="Lugar o dirección"
                                autocomplete="off"
                                class="w-full border-0 bg-transparent p-0 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white"
                            >
                        </label>
                        <div x-show="abiertos.ubicacion" x-cloak x-transition class="absolute left-0 right-0 top-[calc(100%+8px)] z-50 max-h-72 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl dark:border-white/10 dark:bg-sidan-900">
                            <template x-for="item in sugerencias.ubicacion" :key="item.valor + item.detalle">
                                <button type="button" @click="seleccionarSugerencia('ubicacion', item)" class="block w-full rounded-xl px-3 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-white/5">
                                    <span class="block truncate text-sm font-bold text-sidan-900 dark:text-white" x-text="item.valor"></span>
                                    <span class="block text-xs text-slate-400" x-text="item.detalle"></span>
                                </button>
                            </template>
                            <div x-show="!cargando.ubicacion && !sugerencias.ubicacion.length" class="px-3 py-2 text-sm text-slate-400">No encontramos ubicaciones coincidentes.</div>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-white/5">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4M8 3v4M3 10h18"/>
                        </svg>
                        <input x-model="filtros.fecha" type="date" name="fecha" aria-label="Fecha de realización" class="w-full border-0 bg-transparent p-0 text-sm font-semibold text-slate-700 focus:ring-0 dark:text-white dark:[color-scheme:dark]">
                    </label>

                    <button type="submit" class="rounded-xl bg-sidan-500 px-6 py-3 text-sm font-black text-white transition hover:bg-green-600">
                        Buscar
                    </button>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <div class="relative" @click.outside="abiertos.categoria = false">
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-400">Categoría</span>
                            <input
                                x-model="filtros.categoria"
                                @input.debounce.180ms="cargarSugerencias('categoria')"
                                @focus="cargarSugerencias('categoria')"
                                @keydown.escape.stop="abiertos.categoria = false"
                                type="search"
                                name="categoria"
                                placeholder="Buscar categoría"
                                autocomplete="off"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white"
                            >
                        </label>
                        <div x-show="abiertos.categoria" x-cloak x-transition class="absolute left-0 right-0 top-[calc(100%+8px)] z-50 max-h-64 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl dark:border-white/10 dark:bg-sidan-900">
                            <template x-for="item in sugerencias.categoria" :key="item.valor">
                                <button type="button" @click="seleccionarSugerencia('categoria', item)" class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-white/5">
                                    <span class="truncate text-sm font-bold text-sidan-900 dark:text-white" x-text="item.valor"></span>
                                    <span class="shrink-0 text-xs text-slate-400" x-text="item.detalle"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="relative" @click.outside="abiertos.etiqueta = false">
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-white/10 dark:bg-white/5">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-400">Etiqueta</span>
                            <input
                                x-model="filtros.etiqueta"
                                @input.debounce.180ms="cargarSugerencias('etiqueta')"
                                @focus="cargarSugerencias('etiqueta')"
                                @keydown.escape.stop="abiertos.etiqueta = false"
                                type="search"
                                name="etiqueta"
                                placeholder="Buscar etiqueta"
                                autocomplete="off"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white"
                            >
                        </label>
                        <div x-show="abiertos.etiqueta" x-cloak x-transition class="absolute left-0 right-0 top-[calc(100%+8px)] z-50 max-h-64 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl dark:border-white/10 dark:bg-sidan-900">
                            <template x-for="item in sugerencias.etiqueta" :key="item.valor">
                                <button type="button" @click="seleccionarSugerencia('etiqueta', item)" class="flex w-full items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-left hover:bg-slate-50 dark:hover:bg-white/5">
                                    <span class="truncate text-sm font-bold text-sidan-900 dark:text-white" x-text="'#' + item.valor"></span>
                                    <span class="shrink-0 text-xs text-slate-400" x-text="item.detalle"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                @if (request('q', request('buscar')) || request('ubicacion') || request('categoria') || request('etiqueta') || request('fecha'))
                    <div class="flex justify-end">
                        <a href="{{ route('activities.index') }}" class="text-xs font-black text-red-500 transition hover:text-red-600">
                            Limpiar todos los filtros
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </section>


    {{-- ===================================================== --}}
    {{-- RESULTADOS --}}
    {{-- ===================================================== --}}

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">

        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                    Catálogo
                </p>

                <h2 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 dark:text-white">
                    Actividades disponibles
                </h2>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">

                    @if ($actividades->total() === 1)

                        Encontramos 1 actividad.

                    @else

                        Encontramos {{ $actividades->total() }} actividades.

                    @endif

                </p>

            </div>


            @if (
                request('q', request('buscar')) ||
                request('ubicacion') ||
                request('categoria') ||
                request('etiqueta') ||
                request('fecha')
            )

                <div class="flex flex-wrap gap-2">

                    @if (request('q', request('buscar')))

                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                            “{{ request('q', request('buscar')) }}”
                        </span>

                    @endif


                    @if (request('categoria'))

                        <span class="rounded-full bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700 dark:bg-green-500/10 dark:text-green-300">
                            {{ request('categoria') }}
                        </span>

                    @endif


                    @if (request('etiqueta'))

                        <span class="rounded-full bg-sidan-50 px-3 py-1.5 text-xs font-bold text-sidan-700 dark:bg-white/10 dark:text-slate-300">
                            #{{ request('etiqueta') }}
                        </span>

                    @endif

                    @if (request('ubicacion'))

                        <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                            {{ request('ubicacion') }}
                        </span>

                    @endif

                    @if (request('fecha'))

                        <span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                            {{ request('fecha') }}
                        </span>

                    @endif

                </div>

            @endif

        </div>


        {{-- ================================================= --}}
        {{-- GRID --}}
        {{-- ================================================= --}}

        @if ($actividades->count())

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

                @foreach ($actividades as $actividad)

                    <article
                        class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1.5 hover:border-green-300 hover:shadow-soft dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-green-500/30 dark:hover:shadow-none"
                    >

                        {{-- IMAGEN --}}
                        <button
                            type="button"
                            @click="abrirActividad(@js($actividad))"
                            class="relative block h-56 w-full overflow-hidden text-left"
                        >

                            <img
                                src="{{ $actividad['imagen'] }}"
                                alt="{{ $actividad['titulo'] }}"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                            >

                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/65 via-transparent to-transparent"></div>


                            <div class="absolute left-4 top-4 flex flex-wrap gap-2">

                                <span class="rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-black text-sidan-900 shadow-sm">
                                    {{ $actividad['categoria'] }}
                                </span>

                                <span class="rounded-full bg-sidan-500 px-2.5 py-1 text-[11px] font-black text-white shadow-sm">
                                    {{ $actividad['estado'] }}
                                </span>

                            </div>


                            <div class="absolute inset-x-0 bottom-0 p-5">

                                <p class="text-xs font-black uppercase tracking-wider text-green-300">
                                    {{ $actividad['fecha'] }} · {{ $actividad['hora'] }}
                                </p>

                                <h3 class="mt-1 line-clamp-2 text-xl font-black leading-tight text-white">
                                    {{ $actividad['titulo'] }}
                                </h3>

                            </div>

                        </button>


                        {{-- INFORMACIÓN --}}
                        <div class="p-5">

                            <div class="flex items-start justify-between gap-4">

                                <p class="text-xs font-black uppercase tracking-wider text-sidan-500">
                                    {{ $actividad['organizacion'] }}
                                </p>

                                <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700 dark:bg-white/10 dark:text-slate-200">
                                    {{ $actividad['precio'] }}
                                </span>

                            </div>


                            {{-- ETIQUETAS --}}
                            @if (count($actividad['etiquetas']))

                                <div class="mt-3 flex flex-wrap gap-1.5">

                                    @foreach (array_slice($actividad['etiquetas'], 0, 3) as $etiqueta)

                                        <span class="rounded-full bg-slate-50 px-2 py-1 text-[10px] font-bold text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                            #{{ $etiqueta }}
                                        </span>

                                    @endforeach

                                </div>

                            @endif


                            <p class="mt-3 line-clamp-2 min-h-[48px] text-sm leading-6 text-slate-600 dark:text-slate-400">
                                {{ $actividad['descripcion'] }}
                            </p>


                            {{-- INFORMACIÓN RÁPIDA --}}
                            <div class="mt-4 space-y-2 text-sm text-slate-500 dark:text-slate-400">

                                <div class="flex items-center gap-2">

                                    <svg
                                        class="h-4 w-4 shrink-0 text-sidan-500"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <circle cx="12" cy="12" r="9"/>
                                        <path d="M12 7v5l3 2"/>
                                    </svg>

                                    <span>
                                        {{ $actividad['hora'] }}
                                    </span>

                                </div>


                                <div class="flex items-center gap-2">

                                    <svg
                                        class="h-4 w-4 shrink-0 text-sidan-500"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="2.5"/>
                                    </svg>

                                    <span class="line-clamp-1">
                                        {{ $actividad['lugar'] }}
                                    </span>

                                </div>

                            </div>


                            {{-- ACCIONES --}}
                            <div class="mt-5 grid grid-cols-2 gap-2">

                                <button
                                    type="button"
                                    @click="abrirActividad(@js($actividad))"
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


            {{-- ================================================= --}}
            {{-- PAGINACIÓN --}}
            {{-- ================================================= --}}

            @if ($actividades->hasPages())

                <div class="mt-10">

                    {{ $actividades->withQueryString()->links() }}

                </div>

            @endif

        @else

            {{-- ================================================= --}}
            {{-- SIN RESULTADOS --}}
            {{-- ================================================= --}}

            <div class="rounded-[2rem] border border-slate-200 bg-white px-6 py-16 text-center shadow-sm dark:border-white/10 dark:bg-white/[0.04]">

                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-2xl dark:bg-white/5">
                    🔎
                </div>

                <h3 class="mt-5 text-2xl font-black text-sidan-900 dark:text-white">
                    No encontramos actividades
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Prueba cambiando el texto, la ubicación, la fecha, la categoría o la etiqueta.
                </p>

                <a
                    href="{{ route('activities.index') }}"
                    class="mt-6 inline-flex rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white transition hover:bg-green-600"
                >
                    Ver todas las actividades
                </a>

            </div>

        @endif

    </section>


    {{-- ===================================================== --}}
    {{-- CTA --}}
    {{-- ===================================================== --}}

    <section class="mx-auto max-w-7xl px-4 pb-14 sm:px-6 lg:px-8 lg:pb-20">

        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-sidan-900 via-sidan-900 to-[#0d6a43] px-6 py-12 text-center text-white sm:px-10">

            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-sidan-500/25 blur-3xl"></div>

            <div class="relative mx-auto max-w-2xl">

                <p class="text-sm font-black uppercase tracking-[0.18em] text-green-300">
                    Sigue explorando
                </p>

                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">
                    Algo interesante puede estar esperándote.
                </h2>

                <p class="mt-4 text-white/75">
                    Puedes revisar todas las actividades antes de iniciar sesión. Entra a tu cuenta únicamente cuando quieras participar, reservar o comprar.
                </p>

                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">

                    <a
                        href="{{ route('welcome') }}"
                        class="rounded-xl bg-white px-5 py-3 text-sm font-black text-sidan-900 transition hover:bg-green-50"
                    >
                        Volver al inicio
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
    {{-- MODAL DE VISTA RÁPIDA --}}
    {{-- ===================================================== --}}

    <div
        x-show="modalAbierto"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[100] overflow-y-auto bg-slate-950/70 p-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
    >

        <div
            class="flex min-h-full items-center justify-center"
            @click.self="cerrarActividad()"
        >

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

                {{-- CERRAR --}}
                <button
                    type="button"
                    @click="cerrarActividad()"
                    class="absolute right-4 top-4 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-xl font-black text-slate-700 shadow-lg transition hover:bg-red-500 hover:text-white dark:bg-sidan-950 dark:text-white"
                    aria-label="Cerrar"
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


                        {{-- CONTENIDO --}}
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

                                <template
                                    x-for="etiqueta in actividadModal.etiquetas"
                                    :key="etiqueta"
                                >

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
    function catalogoActividades() {
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

            inicializarFiltros() {
                this.filtros.q = this.$root.dataset.q || '';
                this.filtros.ubicacion = this.$root.dataset.ubicacion || '';
                this.filtros.categoria = this.$root.dataset.categoria || '';
                this.filtros.etiqueta = this.$root.dataset.etiqueta || '';
                this.filtros.fecha = this.$root.dataset.fecha || '';
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
            }
        };
    }
</script>

@endsection