@extends('layouts.navbars')
@section('title', 'Editar espacio')
@section('content')
    <style>
        html:not(.dark) .espacios-theme .bg-slate-900 {
            background-color: #ffffff !important;
        }

        html:not(.dark) .espacios-theme .bg-slate-950,
        html:not(.dark) .espacios-theme [class*="bg-slate-950/"] {
            background-color: #f8fafc !important;
        }

        html:not(.dark) .espacios-theme .bg-slate-800,
        html:not(.dark) .espacios-theme [class*="bg-slate-800/"] {
            background-color: #f1f5f9 !important;
        }

        html:not(.dark) .espacios-theme .bg-slate-700,
        html:not(.dark) .espacios-theme [class*="bg-slate-700/"] {
            background-color: #e2e8f0 !important;
        }

        html:not(.dark) .espacios-theme .border-slate-800 {
            border-color: #e2e8f0 !important;
        }

        html:not(.dark) .espacios-theme .border-slate-700,
        html:not(.dark) .espacios-theme .border-slate-600 {
            border-color: #cbd5e1 !important;
        }

        html:not(.dark) .espacios-theme .divide-slate-800> :not([hidden])~ :not([hidden]) {
            border-color: #e2e8f0 !important;
        }

        html:not(.dark) .espacios-theme .text-white:not([class*="bg-"]) {
            color: #0f172a !important;
        }

        html:not(.dark) .espacios-theme .text-slate-300 {
            color: #334155 !important;
        }

        html:not(.dark) .espacios-theme .text-slate-400 {
            color: #475569 !important;
        }

        html:not(.dark) .espacios-theme .text-slate-500 {
            color: #64748b !important;
        }

        html:not(.dark) .espacios-theme .text-slate-600 {
            color: #94a3b8 !important;
        }

        html:not(.dark) .espacios-theme .text-slate-700 {
            color: #cbd5e1 !important;
        }

        html:not(.dark) .espacios-theme .text-emerald-300,
        html:not(.dark) .espacios-theme .text-emerald-400 {
            color: #047857 !important;
        }

        html:not(.dark) .espacios-theme .text-sky-300,
        html:not(.dark) .espacios-theme .text-sky-400 {
            color: #0369a1 !important;
        }

        html:not(.dark) .espacios-theme .text-violet-300,
        html:not(.dark) .espacios-theme .text-violet-400 {
            color: #6d28d9 !important;
        }

        html:not(.dark) .espacios-theme .text-amber-300,
        html:not(.dark) .espacios-theme .text-amber-400 {
            color: #b45309 !important;
        }

        html:not(.dark) .espacios-theme .text-red-300,
        html:not(.dark) .espacios-theme .text-red-400 {
            color: #b91c1c !important;
        }

        html:not(.dark) .espacios-theme [class*="bg-emerald-500/10"] {
            background-color: #ecfdf5 !important;
        }

        html:not(.dark) .espacios-theme [class*="bg-sky-500/5"],
        html:not(.dark) .espacios-theme [class*="bg-sky-500/10"] {
            background-color: #f0f9ff !important;
        }

        html:not(.dark) .espacios-theme [class*="bg-violet-500/5"],
        html:not(.dark) .espacios-theme [class*="bg-violet-500/10"] {
            background-color: #f5f3ff !important;
        }

        html:not(.dark) .espacios-theme [class*="bg-amber-500/5"],
        html:not(.dark) .espacios-theme [class*="bg-amber-500/10"] {
            background-color: #fffbeb !important;
        }

        html:not(.dark) .espacios-theme [class*="bg-red-500/5"],
        html:not(.dark) .espacios-theme [class*="bg-red-500/10"] {
            background-color: #fef2f2 !important;
        }

        html:not(.dark) .espacios-theme [class*="border-emerald-500/20"],
        html:not(.dark) .espacios-theme [class*="border-emerald-500/30"] {
            border-color: #a7f3d0 !important;
        }

        html:not(.dark) .espacios-theme [class*="border-sky-500/20"] {
            border-color: #bae6fd !important;
        }

        html:not(.dark) .espacios-theme [class*="border-violet-500/20"] {
            border-color: #ddd6fe !important;
        }

        html:not(.dark) .espacios-theme [class*="border-amber-500/20"] {
            border-color: #fde68a !important;
        }

        html:not(.dark) .espacios-theme [class*="border-red-500/20"],
        html:not(.dark) .espacios-theme [class*="border-red-500/30"],
        html:not(.dark) .espacios-theme [class*="border-red-500/40"] {
            border-color: #fecaca !important;
        }

        html:not(.dark) .espacios-theme [class*="hover:bg-slate-800"]:hover {
            background-color: #f1f5f9 !important;
        }

        html:not(.dark) .espacios-theme [class*="hover:text-white"]:hover {
            color: #0f172a !important;
        }

        html:not(.dark) .espacios-theme input:not([type="checkbox"]):not([type="radio"]),
        html:not(.dark) .espacios-theme textarea,
        html:not(.dark) .espacios-theme select {
            color: #0f172a !important;
        }

        html:not(.dark) .espacios-theme input::placeholder,
        html:not(.dark) .espacios-theme textarea::placeholder {
            color: #94a3b8 !important;
        }

        html:not(.dark) .espacios-theme select option {
            background-color: #ffffff;
            color: #0f172a;
        }

        .dark .espacios-theme select {
            color-scheme: dark;
        }

        html:not(.dark) .espacios-theme input[type="checkbox"] {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
        }

        html:not(.dark) .espacios-theme input[type="checkbox"]:checked {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
        }

        html:not(.dark) .espacios-theme input[type="checkbox"]:focus {
            --tw-ring-color: rgb(16 185 129 / 0.25);
        }

        .dark .espacios-theme input[type="checkbox"]:checked {
            background-color: #10b981;
            border-color: #10b981;
        }
    </style>
    <div
        class="espacios-theme min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
        <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-5">
                <a href="{{ route('admin.espacios.index') }}"
                    class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a espacios
                </a>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white">
                            Editar espacio
                        </h1>
                        <p class="mt-1 text-sm text-slate-400">
                            Modifica la información, ubicación y recursos de
                            <span class="font-medium text-slate-300">{{ $espacio->nombre }}</span>.
                        </p>
                    </div>
                    @if ($espacio->espacios_internos_count > 0)
                        <span class="inline-flex w-fit rounded-lg bg-slate-800 px-3 py-1.5 text-xs text-slate-400">
                            {{ $espacio->espacios_internos_count }}
                            {{ $espacio->espacios_internos_count === 1 ? 'espacio dentro' : 'espacios dentro' }}
                        </span>
                    @endif
                </div>
            </div>
            {{-- INDICADOR DE PASOS --}}
            <div class="mb-5 overflow-x-auto pb-1">
                <div class="mx-auto flex min-w-[600px] max-w-4xl items-center">
                    @foreach ([
            1 => ['Información', 'Datos principales'],
            2 => ['Ubicación', 'Cómo encontrarlo'],
            3 => ['Recursos', 'Qué tiene disponible'],
            4 => ['Confirmar', 'Revisar cambios'],
        ] as $numero => $datosPaso)
                        @if ($numero > 1)
                            <div data-linea-paso="{{ $numero - 1 }}" class="mx-3 h-px flex-1 bg-slate-800">
                            </div>
                        @endif
                        <button type="button" data-indicador-paso="{{ $numero }}"
                            class="flex shrink-0 items-center gap-2.5">
                            <span data-circulo-paso
                                class="flex h-8 w-8 items-center justify-center rounded-full border text-xs font-bold transition">
                                {{ $numero }}
                            </span>
                            <div class="hidden text-left sm:block">
                                <p data-titulo-paso class="text-xs font-semibold">
                                    {{ $datosPaso[0] }}
                                </p>
                                <p class="text-[11px] text-slate-600">
                                    {{ $datosPaso[1] }}
                                </p>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
            <form id="formEspacio" method="POST" action="{{ route('admin.espacios.update', $espacio) }}">
                @csrf
                @method('PUT')
                <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
                    {{-- PASO 1 --}}
                    <section data-paso="1">
                        <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400">
                                Paso 1 de 4
                            </p>
                            <h2 class="mt-1 text-lg font-semibold text-white">
                                Información del espacio
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Revisa los datos principales y el lugar donde se encuentra.
                            </p>
                        </div>
                        <div class="space-y-5 p-5 sm:p-6">
                            <div>
                                <label for="nombre" class="mb-2 block text-sm font-medium text-slate-300">
                                    Nombre del espacio
                                    <span class="text-red-400">*</span>
                                </label>
                                <input id="nombre" name="nombre" type="text" maxlength="150" required
                                    value="{{ old('nombre', $espacio->nombre) }}"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <p id="errorNombre" class="mt-2 hidden text-xs text-red-400">
                                    Escribe el nombre del espacio.
                                </p>
                            </div>
                            <div>
                                <label for="descripcion" class="mb-2 block text-sm font-medium text-slate-300">
                                    Descripción
                                    <span class="font-normal text-slate-600">(opcional)</span>
                                </label>
                                <textarea id="descripcion" name="descripcion" rows="3" maxlength="2000"
                                    placeholder="Información adicional sobre el espacio..."
                                    class="w-full resize-y rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">{{ old('descripcion', $espacio->descripcion) }}</textarea>
                            </div>
                            <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                                <label for="id_espacio_contenedor" class="block text-sm font-medium text-white">
                                    ¿Este espacio se encuentra dentro de otro lugar?
                                </label>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Cambia esta opción únicamente si el espacio fue trasladado o registrado en el lugar
                                    equivocado.
                                </p>
                                <select id="id_espacio_contenedor" name="id_espacio_contenedor"
                                    class="mt-3 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                    <option value="">
                                        No, es un lugar independiente
                                    </option>
                                    @foreach ($espaciosDisponibles as $espacioDisponible)
                                        <option value="{{ $espacioDisponible->id_espacio }}" @selected((string) old('id_espacio_contenedor', $espacio->id_espacio_contenedor) === (string) $espacioDisponible->id_espacio)>
                                            {{ $espacioDisponible->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="avisoCambioLugar"
                                    class="mt-3 hidden rounded-lg border border-amber-500/20 bg-amber-500/5 p-3">
                                    <p class="text-xs leading-5 text-slate-400">
                                        Cambiaste el lugar donde se encuentra este espacio.
                                        Puedes conservar su ubicación actual o copiar la ubicación registrada de
                                        <strong id="nombreNuevoLugar" class="font-medium text-amber-300"></strong>.
                                    </p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <button id="btnUsarUbicacionLugar" type="button"
                                            class="rounded-lg bg-amber-500/10 px-3 py-2 text-xs font-semibold text-amber-300 transition hover:bg-amber-500/20">
                                            Usar ubicación de este lugar
                                        </button>
                                        <button id="btnConservarUbicacion" type="button"
                                            class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-medium text-slate-400 transition hover:bg-slate-800 hover:text-white">
                                            Conservar ubicación actual
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="capacidad" class="mb-2 block text-sm font-medium text-slate-300">
                                    Capacidad máxima
                                    <span class="font-normal text-slate-600">(opcional)</span>
                                </label>
                                <div class="relative max-w-xs">
                                    <input id="capacidad" name="capacidad" type="number" min="1" step="1"
                                        value="{{ old('capacidad', $espacio->capacidad) }}" placeholder="Ej. 200"
                                        class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 pr-20 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                    <span
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-slate-500">
                                        personas
                                    </span>
                                </div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label
                                    class="cursor-pointer rounded-xl border border-slate-800 bg-slate-950/50 p-4 transition hover:border-slate-700">
                                    <div class="flex items-start gap-3">
                                        <input id="permite_actividades" type="checkbox" name="permite_actividades"
                                            value="1" @checked(old('permite_actividades', $espacio->permite_actividades ? '1' : '0') == '1')
                                            class="mt-0.5 h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                                        <div>
                                            <span class="block text-sm font-medium text-white">
                                                Se pueden realizar actividades aquí
                                            </span>
                                            <span class="mt-1 block text-xs leading-5 text-slate-500">
                                                Ej. salón, auditorio o centro de cómputo.
                                            </span>
                                        </div>
                                    </div>
                                </label>
                                <label
                                    class="cursor-pointer rounded-xl border border-slate-800 bg-slate-950/50 p-4 transition hover:border-slate-700">
                                    <div class="flex items-start gap-3">
                                        <input id="activo" type="checkbox" name="activo" value="1"
                                            @checked(old('activo', $espacio->activo ? '1' : '0') == '1')
                                            class="mt-0.5 h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                                        <div>
                                            <span class="block text-sm font-medium text-white">
                                                Disponible para usar
                                            </span>
                                            <span class="mt-1 block text-xs leading-5 text-slate-500">
                                                Podrá utilizarse en nuevas configuraciones.
                                            </span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </section>
                    {{-- PASO 2 --}}
                    <section data-paso="2" class="hidden">
                        <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400">
                                Paso 2 de 4
                            </p>
                            <h2 class="mt-1 text-lg font-semibold text-white">
                                Ubicación
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Estos son los datos que actualmente utiliza SIDAN para localizar este espacio.
                            </p>
                        </div>
                        <div class="space-y-5 p-5 sm:p-6">
                            <div id="avisoUbicacionCopiada"
                                class="hidden rounded-xl border border-sky-500/20 bg-sky-500/5 p-4">
                                <p class="text-sm font-medium text-sky-300">
                                    Ubicación actualizada
                                </p>
                                <p class="mt-1 text-xs leading-5 text-slate-400">
                                    Copiamos la ubicación de
                                    <strong id="nombreUbicacionCopiada" class="font-medium text-slate-300"></strong>.
                                    Puedes ajustar cualquier dato antes de guardar.
                                </p>
                            </div>
                            <div>
                                <label for="direccion" class="mb-2 block text-sm font-medium text-slate-300">
                                    Dirección
                                </label>
                                <input id="direccion" name="direccion" type="text" maxlength="300"
                                    value="{{ old('direccion', $espacio->direccion) }}"
                                    placeholder="Ej. Ciudad Universitaria, San Salvador"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                            </div>
                            <div>
                                <label for="indicaciones" class="mb-2 block text-sm font-medium text-slate-300">
                                    Indicaciones para llegar
                                </label>
                                <textarea id="indicaciones" name="indicaciones" rows="3" maxlength="500"
                                    placeholder="Ej. Segundo nivel, frente al laboratorio..."
                                    class="w-full resize-y rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">{{ old('indicaciones', $espacio->indicaciones) }}</textarea>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-slate-800">
                                <button id="btnCoordenadas" type="button"
                                    class="flex w-full items-center justify-between gap-4 bg-slate-950/50 px-4 py-3 text-left transition hover:bg-slate-800/40">
                                    <div>
                                        <p class="text-sm font-medium text-slate-300">
                                            Coordenadas
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            Información geográfica opcional.
                                        </p>
                                    </div>
                                    <svg id="iconoCoordenadas"
                                        class="h-4 w-4 shrink-0 text-slate-500 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="contenidoCoordenadas"
                                    class="{{ old('latitud', $espacio->latitud) || old('longitud', $espacio->longitud) ? '' : 'hidden' }} border-t border-slate-800 p-4">
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label for="latitud" class="mb-2 block text-xs font-medium text-slate-400">
                                                Latitud
                                            </label>
                                            <input id="latitud" name="latitud" type="number" step="0.0000001"
                                                min="-90" max="90"
                                                value="{{ old('latitud', $espacio->latitud) }}"
                                                placeholder="Ej. 13.7188000"
                                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                        </div>
                                        <div>
                                            <label for="longitud" class="mb-2 block text-xs font-medium text-slate-400">
                                                Longitud
                                            </label>
                                            <input id="longitud" name="longitud" type="number" step="0.0000001"
                                                min="-180" max="180"
                                                value="{{ old('longitud', $espacio->longitud) }}"
                                                placeholder="Ej. -89.2034000"
                                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    {{-- PASO 3 --}}
                    <section data-paso="3" class="hidden">
                        <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400">
                                Paso 3 de 4
                            </p>
                            <h2 class="mt-1 text-lg font-semibold text-white">
                                Recursos disponibles
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Actualiza los recursos que normalmente se encuentran disponibles aquí.
                            </p>
                        </div>
                        <div class="p-5 sm:p-6">
                            @if ($recursos->count() > 0)
                                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <input id="buscarRecurso" type="search" autocomplete="off"
                                        placeholder="Buscar recurso..."
                                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 sm:max-w-sm">
                                    <span id="contadorRecursos" class="shrink-0 text-xs font-medium text-slate-500">
                                        0 seleccionados
                                    </span>
                                </div>
                                <div id="listaRecursos" class="space-y-2">
                                    @foreach ($recursos as $recurso)
                                        @php
                                            $recursoActual = $espacio->recursos->firstWhere(
                                                'id_recurso',
                                                $recurso->id_recurso,
                                            );
                                            $seleccionado =
                                                old(
                                                    "recursos.{$recurso->id_recurso}.seleccionado",
                                                    $recursoActual ? '1' : '0',
                                                ) == '1';
                                            $cantidadActual = old(
                                                "recursos.{$recurso->id_recurso}.cantidad",
                                                $recursoActual?->pivot?->cantidad ?? 1,
                                            );
                                            $observacionActual = old(
                                                "recursos.{$recurso->id_recurso}.observacion",
                                                $recursoActual?->pivot?->observacion,
                                            );
                                        @endphp
                                        <div class="recurso-item overflow-hidden rounded-lg border border-slate-800 bg-slate-950/40"
                                            data-nombre="{{ mb_strtolower($recurso->nombre) }}"
                                            data-categoria="{{ mb_strtolower($recurso->categoria ?? '') }}">
                                            <label class="flex cursor-pointer items-center gap-3 px-3 py-2.5">
                                                <input type="checkbox"
                                                    name="recursos[{{ $recurso->id_recurso }}][seleccionado]"
                                                    value="1" data-recurso-check
                                                    data-recurso-id="{{ $recurso->id_recurso }}"
                                                    @checked($seleccionado)
                                                    class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span data-nombre-recurso class="text-sm font-medium text-white">
                                                            {{ $recurso->nombre }}
                                                        </span>
                                                        @if ($recurso->categoria)
                                                            <span class="text-xs text-slate-500">
                                                                {{ $recurso->categoria }}
                                                            </span>
                                                        @endif
                                                        @if (!$recurso->activo)
                                                            <span
                                                                class="rounded bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-medium text-amber-400">
                                                                Ya no disponible
                                                            </span>
                                                        @elseif ($recurso->es_movil)
                                                            <span class="text-[11px] text-sky-400">
                                                                Móvil
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </label>
                                            <div id="detalleRecurso{{ $recurso->id_recurso }}"
                                                class="{{ $seleccionado ? '' : 'hidden' }} border-t border-slate-800 px-3 py-3">
                                                <div class="grid gap-3 sm:grid-cols-[180px_1fr]">
                                                    <div>
                                                        <label for="cantidadRecurso{{ $recurso->id_recurso }}"
                                                            class="mb-1 block text-xs text-slate-500">
                                                            Cantidad disponible
                                                        </label>
                                                        <div class="relative">
                                                            <input id="cantidadRecurso{{ $recurso->id_recurso }}"
                                                                name="recursos[{{ $recurso->id_recurso }}][cantidad]"
                                                                type="number" min="1" step="1"
                                                                value="{{ $cantidadActual }}"
                                                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 pr-16 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                                            <span
                                                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-slate-500">
                                                                {{ $recurso->unidad_medida }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label for="observacionRecurso{{ $recurso->id_recurso }}"
                                                            class="mb-1 block text-xs text-slate-500">
                                                            Nota
                                                            <span class="text-slate-600">(opcional)</span>
                                                        </label>
                                                        <input id="observacionRecurso{{ $recurso->id_recurso }}"
                                                            name="recursos[{{ $recurso->id_recurso }}][observacion]"
                                                            type="text" maxlength="300"
                                                            value="{{ $observacionActual }}"
                                                            placeholder="Ej. Instalado al frente"
                                                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="sinResultadosRecursos" class="hidden py-8 text-center text-sm text-slate-500">
                                    No encontramos recursos con ese nombre o categoría.
                                </div>
                            @else
                                <div class="py-10 text-center">
                                    <p class="text-sm font-medium text-slate-300">
                                        No hay recursos disponibles
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Puedes guardar los cambios sin asignar recursos.
                                    </p>
                                    <a href="{{ route('admin.recursos.create') }}"
                                        class="mt-4 inline-flex rounded-lg border border-slate-700 px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white">
                                        Crear recurso
                                    </a>
                                </div>
                            @endif
                        </div>
                    </section>
                    {{-- PASO 4 --}}
                    <section data-paso="4" class="hidden">
                        <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400">
                                Paso 4 de 4
                            </p>
                            <h2 class="mt-1 text-lg font-semibold text-white">
                                Revisa los cambios
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Confirma cómo quedará el espacio antes de actualizarlo.
                            </p>
                        </div>
                        <div class="p-5 sm:p-6">
                            <div class="divide-y divide-slate-800 rounded-xl border border-slate-800">
                                <div class="grid gap-2 p-4 sm:grid-cols-[170px_1fr]">
                                    <span class="text-xs font-medium text-slate-500">
                                        Nombre
                                    </span>
                                    <span id="resumenNombre" class="text-sm font-medium text-white">
                                        —
                                    </span>
                                </div>
                                <div class="grid gap-2 p-4 sm:grid-cols-[170px_1fr]">
                                    <span class="text-xs font-medium text-slate-500">
                                        Se encuentra en
                                    </span>
                                    <span id="resumenPadre" class="text-sm text-slate-300">
                                        —
                                    </span>
                                </div>
                                <div class="grid gap-2 p-4 sm:grid-cols-[170px_1fr]">
                                    <span class="text-xs font-medium text-slate-500">
                                        Capacidad
                                    </span>
                                    <span id="resumenCapacidad" class="text-sm text-slate-300">
                                        —
                                    </span>
                                </div>
                                <div class="grid gap-2 p-4 sm:grid-cols-[170px_1fr]">
                                    <span class="text-xs font-medium text-slate-500">
                                        Dirección
                                    </span>
                                    <span id="resumenDireccion" class="text-sm text-slate-300">
                                        —
                                    </span>
                                </div>
                                <div class="grid gap-2 p-4 sm:grid-cols-[170px_1fr]">
                                    <span class="text-xs font-medium text-slate-500">
                                        Indicaciones
                                    </span>
                                    <span id="resumenIndicaciones" class="text-sm text-slate-300">
                                        —
                                    </span>
                                </div>
                                <div class="grid gap-2 p-4 sm:grid-cols-[170px_1fr]">
                                    <span class="text-xs font-medium text-slate-500">
                                        Uso
                                    </span>
                                    <span id="resumenUso" class="text-sm text-slate-300">
                                        —
                                    </span>
                                </div>
                                <div class="grid gap-2 p-4 sm:grid-cols-[170px_1fr]">
                                    <span class="text-xs font-medium text-slate-500">
                                        Recursos
                                    </span>
                                    <div>
                                        <span id="resumenCantidadRecursos" class="text-sm text-slate-300">
                                            Ninguno
                                        </span>
                                        <div id="resumenListaRecursos" class="mt-2 space-y-1 text-xs text-slate-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    {{-- NAVEGACIÓN --}}
                    <div class="flex items-center justify-between gap-3 border-t border-slate-800 px-5 py-4 sm:px-6">
                        <div>
                            <a id="btnCancelar" href="{{ route('admin.espacios.index') }}"
                                class="inline-flex items-center justify-center rounded-lg border border-slate-700 px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white">
                                Cancelar
                            </a>
                            <button id="btnAnterior" type="button"
                                class="hidden items-center gap-2 rounded-lg border border-slate-700 px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                                Anterior
                            </button>
                        </div>
                        <button id="btnSiguiente" type="button"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-400">
                            Siguiente
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                        <button id="btnGuardar" type="submit"
                            class="hidden items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </form>
            {{-- ELIMINACIÓN --}}
            <div class="mt-5 rounded-xl border border-slate-800 bg-slate-900 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-300">
                            Eliminar espacio
                        </p>
                        @if ($espacio->espacios_internos_count > 0)
                            <p class="mt-1 text-xs text-slate-500">
                                No puede eliminarse porque hay
                                {{ $espacio->espacios_internos_count }}
                                {{ $espacio->espacios_internos_count === 1 ? 'espacio registrado' : 'espacios registrados' }}
                                dentro.
                            </p>
                        @else
                            <p class="mt-1 text-xs text-slate-500">
                                Esta acción elimina el espacio y sus asociaciones con recursos.
                            </p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.espacios.destroy', $espacio) }}"
                        class="form-eliminar-espacio" data-nombre="{{ $espacio->nombre }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" @disabled($espacio->espacios_internos_count > 0)
                            class="rounded-lg border px-4 py-2 text-sm font-medium transition
                            {{ $espacio->espacios_internos_count > 0
                                ? 'cursor-not-allowed border-slate-800 text-slate-700'
                                : 'border-red-500/30 text-red-400 hover:bg-red-500/10' }}">
                            Eliminar espacio
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- DATOS DE LOS POSIBLES LUGARES --}}
    <div id="datosEspacios" class="hidden">
        @foreach ($espaciosDisponibles as $espacioDisponible)
            <div data-espacio-id="{{ $espacioDisponible->id_espacio }}" data-nombre="{{ $espacioDisponible->nombre }}"
                data-direccion="{{ $espacioDisponible->direccion ?? '' }}"
                data-indicaciones="{{ $espacioDisponible->indicaciones ?? '' }}"
                data-latitud="{{ $espacioDisponible->latitud ?? '' }}"
                data-longitud="{{ $espacioDisponible->longitud ?? '' }}">
            </div>
        @endforeach
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const secciones = Array.from(
                document.querySelectorAll('[data-paso]')
            );
            const indicadores = Array.from(
                document.querySelectorAll('[data-indicador-paso]')
            );
            const btnAnterior = document.getElementById('btnAnterior');
            const btnSiguiente = document.getElementById('btnSiguiente');
            const btnGuardar = document.getElementById('btnGuardar');
            const btnCancelar = document.getElementById('btnCancelar');
            const nombre = document.getElementById('nombre');
            const errorNombre = document.getElementById('errorNombre');
            const contenedor = document.getElementById('id_espacio_contenedor');
            const capacidad = document.getElementById('capacidad');
            const direccion = document.getElementById('direccion');
            const indicaciones = document.getElementById('indicaciones');
            const latitud = document.getElementById('latitud');
            const longitud = document.getElementById('longitud');
            const permiteActividades = document.getElementById('permite_actividades');
            const activo = document.getElementById('activo');
            const avisoCambioLugar = document.getElementById('avisoCambioLugar');
            const nombreNuevoLugar = document.getElementById('nombreNuevoLugar');
            const btnUsarUbicacionLugar = document.getElementById('btnUsarUbicacionLugar');
            const btnConservarUbicacion = document.getElementById('btnConservarUbicacion');
            const avisoUbicacionCopiada = document.getElementById('avisoUbicacionCopiada');
            const nombreUbicacionCopiada = document.getElementById('nombreUbicacionCopiada');
            const btnCoordenadas = document.getElementById('btnCoordenadas');
            const contenidoCoordenadas = document.getElementById('contenidoCoordenadas');
            const iconoCoordenadas = document.getElementById('iconoCoordenadas');
            const buscarRecurso = document.getElementById('buscarRecurso');
            const contadorRecursos = document.getElementById('contadorRecursos');
            const sinResultadosRecursos = document.getElementById('sinResultadosRecursos');
            const espacios = new Map();
            document.querySelectorAll(
                '#datosEspacios [data-espacio-id]'
            ).forEach(function(elemento) {
                espacios.set(
                    String(elemento.dataset.espacioId), {
                        nombre: elemento.dataset.nombre || '',
                        direccion: elemento.dataset.direccion || '',
                        indicaciones: elemento.dataset.indicaciones || '',
                        latitud: elemento.dataset.latitud || '',
                        longitud: elemento.dataset.longitud || ''
                    }
                );
            });
            const lugarOriginal = String(
                "{{ old('id_espacio_contenedor', $espacio->id_espacio_contenedor ?? '') }}"
            );
            let pasoActual = 1;
            let pasoMaximoAlcanzado = 1;

            function mostrarPaso(numero) {
                pasoActual = numero;
                pasoMaximoAlcanzado = Math.max(
                    pasoMaximoAlcanzado,
                    numero
                );
                secciones.forEach(function(seccion) {
                    seccion.classList.toggle(
                        'hidden',
                        Number(seccion.dataset.paso) !== numero
                    );
                });
                indicadores.forEach(function(indicador) {
                    const numeroIndicador = Number(
                        indicador.dataset.indicadorPaso
                    );
                    const circulo = indicador.querySelector(
                        '[data-circulo-paso]'
                    );
                    const titulo = indicador.querySelector(
                        '[data-titulo-paso]'
                    );
                    circulo.className =
                        'flex h-8 w-8 items-center justify-center rounded-full border text-xs font-bold transition';
                    if (numeroIndicador < numero) {
                        circulo.classList.add(
                            'border-emerald-500/50',
                            'bg-emerald-500/10',
                            'text-emerald-400'
                        );
                        titulo.className =
                            'text-xs font-semibold text-emerald-400';
                    } else if (numeroIndicador === numero) {
                        circulo.classList.add(
                            'border-emerald-500',
                            'bg-emerald-500',
                            'text-white'
                        );
                        titulo.className =
                            'text-xs font-semibold text-white';
                    } else {
                        circulo.classList.add(
                            'border-slate-700',
                            'bg-slate-900',
                            'text-slate-500'
                        );
                        titulo.className =
                            'text-xs font-semibold text-slate-500';
                    }
                    indicador.disabled =
                        numeroIndicador > pasoMaximoAlcanzado;
                });
                document.querySelectorAll(
                    '[data-linea-paso]'
                ).forEach(function(linea) {
                    const numeroLinea = Number(
                        linea.dataset.lineaPaso
                    );
                    linea.className =
                        numeroLinea < numero ?
                        'mx-3 h-px flex-1 bg-emerald-500/40' :
                        'mx-3 h-px flex-1 bg-slate-800';
                });
                btnCancelar.classList.toggle(
                    'hidden',
                    numero !== 1
                );
                btnAnterior.classList.toggle(
                    'hidden',
                    numero === 1
                );
                btnAnterior.classList.toggle(
                    'inline-flex',
                    numero > 1
                );
                btnSiguiente.classList.toggle(
                    'hidden',
                    numero === 4
                );
                btnSiguiente.classList.toggle(
                    'inline-flex',
                    numero < 4
                );
                btnGuardar.classList.toggle(
                    'hidden',
                    numero !== 4
                );
                btnGuardar.classList.toggle(
                    'inline-flex',
                    numero === 4
                );
                if (numero === 4) {
                    actualizarResumen();
                }
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            function validarPaso() {
                if (pasoActual === 1) {
                    if (nombre.value.trim() === '') {
                        errorNombre.classList.remove('hidden');
                        nombre.focus();
                        return false;
                    }
                    errorNombre.classList.add('hidden');
                    if (!capacidad.checkValidity()) {
                        capacidad.reportValidity();
                        return false;
                    }
                }
                if (pasoActual === 2) {
                    if (!latitud.checkValidity()) {
                        latitud.reportValidity();
                        return false;
                    }
                    if (!longitud.checkValidity()) {
                        longitud.reportValidity();
                        return false;
                    }
                }
                if (pasoActual === 3) {
                    const seleccionados =
                        document.querySelectorAll(
                            '[data-recurso-check]:checked'
                        );
                    for (const checkbox of seleccionados) {
                        const cantidadRecurso =
                            document.getElementById(
                                `cantidadRecurso${checkbox.dataset.recursoId}`
                            );
                        if (
                            cantidadRecurso &&
                            !cantidadRecurso.checkValidity()
                        ) {
                            cantidadRecurso.reportValidity();
                            return false;
                        }
                    }
                }
                return true;
            }

            function revisarCambioLugar() {
                const nuevoLugar = String(
                    contenedor.value || ''
                );
                avisoUbicacionCopiada.classList.add(
                    'hidden'
                );
                if (
                    nuevoLugar === lugarOriginal ||
                    nuevoLugar === ''
                ) {
                    avisoCambioLugar.classList.add(
                        'hidden'
                    );
                    return;
                }
                const datosLugar = espacios.get(
                    nuevoLugar
                );
                if (!datosLugar) {
                    avisoCambioLugar.classList.add(
                        'hidden'
                    );
                    return;
                }
                nombreNuevoLugar.textContent =
                    datosLugar.nombre;
                avisoCambioLugar.classList.remove(
                    'hidden'
                );
            }

            function copiarUbicacionNuevoLugar() {
                const id = String(
                    contenedor.value || ''
                );
                if (
                    id === '' ||
                    !espacios.has(id)
                ) {
                    return;
                }
                const datosLugar = espacios.get(id);
                direccion.value =
                    datosLugar.direccion;
                indicaciones.value =
                    datosLugar.indicaciones;
                latitud.value =
                    datosLugar.latitud;
                longitud.value =
                    datosLugar.longitud;
                nombreUbicacionCopiada.textContent =
                    datosLugar.nombre;
                avisoUbicacionCopiada.classList.remove(
                    'hidden'
                );
                avisoCambioLugar.classList.add(
                    'hidden'
                );
                if (
                    datosLugar.latitud !== '' ||
                    datosLugar.longitud !== ''
                ) {
                    contenidoCoordenadas.classList.remove(
                        'hidden'
                    );
                    iconoCoordenadas.classList.add(
                        'rotate-180'
                    );
                }
            }

            function actualizarResumen() {
                document.getElementById(
                        'resumenNombre'
                    ).textContent =
                    nombre.value.trim() ||
                    'Sin nombre';
                if (contenedor.value !== '') {
                    const opcion =
                        contenedor.options[
                            contenedor.selectedIndex
                        ];
                    document.getElementById(
                            'resumenPadre'
                        ).textContent =
                        opcion.textContent.trim();
                } else {
                    document.getElementById(
                            'resumenPadre'
                        ).textContent =
                        'Lugar independiente';
                }
                document.getElementById(
                        'resumenCapacidad'
                    ).textContent =
                    capacidad.value !== '' ?
                    `${capacidad.value} personas` :
                    'Sin capacidad definida';
                document.getElementById(
                        'resumenDireccion'
                    ).textContent =
                    direccion.value.trim() ||
                    'Sin dirección registrada';
                document.getElementById(
                        'resumenIndicaciones'
                    ).textContent =
                    indicaciones.value.trim() ||
                    'Sin indicaciones adicionales';
                const usos = [];
                usos.push(
                    permiteActividades.checked ?
                    'Se pueden realizar actividades aquí' :
                    'Se utiliza para organizar otros espacios'
                );
                usos.push(
                    activo.checked ?
                    'Disponible' :
                    'No disponible'
                );
                document.getElementById(
                        'resumenUso'
                    ).textContent =
                    usos.join(' · ');
                const seleccionados = Array.from(
                    document.querySelectorAll(
                        '[data-recurso-check]:checked'
                    )
                );
                const cantidadResumen =
                    document.getElementById(
                        'resumenCantidadRecursos'
                    );
                const listaResumen =
                    document.getElementById(
                        'resumenListaRecursos'
                    );
                listaResumen.innerHTML = '';
                if (seleccionados.length === 0) {
                    cantidadResumen.textContent =
                        'Ninguno';
                    return;
                }
                cantidadResumen.textContent =
                    seleccionados.length === 1 ?
                    '1 recurso seleccionado' :
                    `${seleccionados.length} recursos seleccionados`;
                seleccionados.forEach(function(checkbox) {
                    const id =
                        checkbox.dataset.recursoId;
                    const item =
                        checkbox.closest(
                            '.recurso-item'
                        );
                    const nombreRecurso =
                        item.querySelector(
                            '[data-nombre-recurso]'
                        ).textContent.trim();
                    const cantidadRecurso =
                        document.getElementById(
                            `cantidadRecurso${id}`
                        ).value;
                    const elemento =
                        document.createElement('p');
                    elemento.textContent =
                        `${nombreRecurso} × ${cantidadRecurso}`;
                    listaResumen.appendChild(
                        elemento
                    );
                });
            }

            function actualizarContadorRecursos() {
                if (!contadorRecursos) {
                    return;
                }
                const total =
                    document.querySelectorAll(
                        '[data-recurso-check]:checked'
                    ).length;
                contadorRecursos.textContent =
                    total === 1 ?
                    '1 seleccionado' :
                    `${total} seleccionados`;
            }

            function normalizar(texto) {
                return texto
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase();
            }
            btnSiguiente.addEventListener(
                'click',
                function() {
                    if (!validarPaso()) {
                        return;
                    }
                    if (pasoActual < 4) {
                        mostrarPaso(
                            pasoActual + 1
                        );
                    }
                }
            );
            btnAnterior.addEventListener(
                'click',
                function() {
                    if (pasoActual > 1) {
                        mostrarPaso(
                            pasoActual - 1
                        );
                    }
                }
            );
            indicadores.forEach(function(indicador) {
                indicador.addEventListener(
                    'click',
                    function() {
                        const destino = Number(
                            indicador.dataset.indicadorPaso
                        );
                        if (
                            destino >
                            pasoMaximoAlcanzado
                        ) {
                            return;
                        }
                        if (
                            destino > pasoActual &&
                            !validarPaso()
                        ) {
                            return;
                        }
                        mostrarPaso(destino);
                    }
                );
            });
            nombre.addEventListener(
                'input',
                function() {
                    if (
                        nombre.value.trim() !== ''
                    ) {
                        errorNombre.classList.add(
                            'hidden'
                        );
                    }
                }
            );
            contenedor.addEventListener(
                'change',
                revisarCambioLugar
            );
            btnUsarUbicacionLugar.addEventListener(
                'click',
                copiarUbicacionNuevoLugar
            );
            btnConservarUbicacion.addEventListener(
                'click',
                function() {
                    avisoCambioLugar.classList.add(
                        'hidden'
                    );
                }
            );
            btnCoordenadas.addEventListener(
                'click',
                function() {
                    contenidoCoordenadas.classList.toggle(
                        'hidden'
                    );
                    iconoCoordenadas.classList.toggle(
                        'rotate-180'
                    );
                }
            );
            document.querySelectorAll(
                '[data-recurso-check]'
            ).forEach(function(checkbox) {
                checkbox.addEventListener(
                    'change',
                    function() {
                        const detalle =
                            document.getElementById(
                                `detalleRecurso${checkbox.dataset.recursoId}`
                            );
                        detalle.classList.toggle(
                            'hidden',
                            !checkbox.checked
                        );
                        if (checkbox.checked) {
                            const cantidadRecurso =
                                detalle.querySelector(
                                    'input[type="number"]'
                                );
                            if (
                                cantidadRecurso &&
                                (
                                    cantidadRecurso.value === '' ||
                                    Number(cantidadRecurso.value) < 1
                                )
                            ) {
                                cantidadRecurso.value = 1;
                            }
                        }
                        actualizarContadorRecursos();
                    }
                );
            });
            if (buscarRecurso) {
                buscarRecurso.addEventListener(
                    'input',
                    function() {
                        const termino =
                            normalizar(
                                buscarRecurso.value.trim()
                            );
                        let visibles = 0;
                        document.querySelectorAll(
                            '.recurso-item'
                        ).forEach(function(item) {
                            const nombreRecurso =
                                normalizar(
                                    item.dataset.nombre || ''
                                );
                            const categoriaRecurso =
                                normalizar(
                                    item.dataset.categoria || ''
                                );
                            const coincide =
                                termino === '' ||
                                nombreRecurso.includes(
                                    termino
                                ) ||
                                categoriaRecurso.includes(
                                    termino
                                );
                            item.classList.toggle(
                                'hidden',
                                !coincide
                            );
                            if (coincide) {
                                visibles++;
                            }
                        });
                        if (sinResultadosRecursos) {
                            sinResultadosRecursos.classList.toggle(
                                'hidden',
                                visibles > 0
                            );
                        }
                    }
                );
            }
            if (
                latitud.value !== '' ||
                longitud.value !== ''
            ) {
                contenidoCoordenadas.classList.remove(
                    'hidden'
                );
                iconoCoordenadas.classList.add(
                    'rotate-180'
                );
            }
            actualizarContadorRecursos();
            mostrarPaso(1);
        });
    </script>
    <script>
        function showAppNotification(type, title, message) {
            const previous = document.getElementById('app-notification');
            if (previous) previous.remove();
            const styles = {
                success: {
                    iconColor: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
                    borderColor: 'border-emerald-200 dark:border-emerald-500/20',
                    progressColor: 'bg-emerald-500',
                    progressBackground: 'bg-emerald-100 dark:bg-emerald-950/50',
                    iconPath: 'M5 13l4 4L19 7'
                },
                error: {
                    iconColor: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
                    borderColor: 'border-red-200 dark:border-red-500/20',
                    progressColor: 'bg-red-500',
                    progressBackground: 'bg-red-100 dark:bg-red-950/50',
                    iconPath: 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z'
                },
                warning: {
                    iconColor: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
                    borderColor: 'border-amber-200 dark:border-amber-500/20',
                    progressColor: 'bg-amber-500',
                    progressBackground: 'bg-amber-100 dark:bg-amber-950/50',
                    iconPath: 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z'
                },
                info: {
                    iconColor: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
                    borderColor: 'border-blue-200 dark:border-blue-500/20',
                    progressColor: 'bg-blue-500',
                    progressBackground: 'bg-blue-100 dark:bg-blue-950/50',
                    iconPath: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                }
            };
            const style = styles[type] || styles.info;
            const notification = document.createElement('div');
            notification.id = 'app-notification';
            notification.className =
                'fixed right-4 bottom-4 sm:right-6 sm:bottom-6 z-[150] w-[calc(100%-2rem)] max-w-sm translate-y-6 opacity-0 transition-all duration-300 ease-out';
            notification.innerHTML = `
        <div class="relative overflow-hidden rounded-2xl border ${style.borderColor} bg-white shadow-2xl dark:bg-[#0f172a]">
            <div class="flex items-start gap-3 p-4 pr-12">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${style.iconColor}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${style.iconPath}"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="notification-title text-sm font-black text-slate-900 dark:text-white"></p>
                    <p class="notification-message mt-1 whitespace-pre-line text-sm leading-5 text-slate-600 dark:text-slate-400"></p>
                </div>
            </div>
            <button type="button"
                class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white"
                aria-label="Cerrar notificación">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="h-1 w-full ${style.progressBackground}">
                <div class="notification-progress h-full w-full origin-left ${style.progressColor}"></div>
            </div>
        </div>
    `;
            notification.querySelector('.notification-title').textContent = title;
            notification.querySelector('.notification-message').textContent = message;
            document.body.appendChild(notification);
            const progress = notification.querySelector('.notification-progress');
            const closeButton = notification.querySelector('.notification-close');
            let timeout;
            const closeNotification = () => {
                clearTimeout(timeout);
                notification.classList.remove('opacity-100', 'translate-y-0');
                notification.classList.add('opacity-0', 'translate-y-6');
                setTimeout(() => {
                    if (notification.parentNode) notification.remove();
                }, 300);
            };
            closeButton.addEventListener('click', closeNotification);
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    notification.classList.remove('opacity-0', 'translate-y-6');
                    notification.classList.add('opacity-100', 'translate-y-0');
                    progress.style.transition = 'transform 5s linear';
                    progress.style.transform = 'scaleX(0)';
                });
            });
            timeout = setTimeout(closeNotification, 5000);
        }

        function showSpaceDeleteConfirm(spaceName) {
            if (document.getElementById('space-confirm-overlay')) {
                return Promise.resolve(false);
            }
            return new Promise(resolve => {
                const overlay = document.createElement('div');
                overlay.id = 'space-confirm-overlay';
                overlay.className =
                    'fixed inset-0 z-[200] flex items-center justify-center bg-slate-950/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200';
                const modal = document.createElement('div');
                modal.setAttribute('role', 'dialog');
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('tabindex', '-1');
                modal.className =
                    'w-full max-w-md translate-y-4 scale-95 rounded-2xl border border-slate-200 bg-white p-6 opacity-0 shadow-2xl outline-none transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]';
                modal.innerHTML = `
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Eliminar espacio</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        ¿Deseas eliminar
                        <span class="space-name font-semibold text-slate-900 dark:text-white"></span>?
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                    class="cancel-delete inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                    Cancelar
                </button>
                <button type="button"
                    class="confirm-delete inline-flex h-10 items-center justify-center rounded-xl bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-500">
                    Eliminar
                </button>
            </div>
        `;
                modal.querySelector('.space-name').textContent = spaceName || 'este espacio';
                overlay.appendChild(modal);
                document.body.appendChild(overlay);
                const cancelButton = modal.querySelector('.cancel-delete');
                const confirmButton = modal.querySelector('.confirm-delete');
                let resolved = false;
                const close = result => {
                    if (resolved) return;
                    resolved = true;
                    cancelButton.disabled = true;
                    confirmButton.disabled = true;
                    document.removeEventListener('keydown', handleKeydown, true);
                    overlay.classList.remove('opacity-100');
                    modal.classList.remove('translate-y-0', 'scale-100', 'opacity-100');
                    modal.classList.add('translate-y-4', 'scale-95', 'opacity-0');
                    setTimeout(() => {
                        overlay.remove();
                        resolve(result);
                    }, 200);
                };
                const handleKeydown = event => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        event.stopPropagation();
                        if (event.repeat || resolved) return;
                        close(true);
                    }
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        event.stopPropagation();
                        if (resolved) return;
                        close(false);
                    }
                };
                cancelButton.addEventListener('click', () => close(false));
                confirmButton.addEventListener('click', () => close(true));
                overlay.addEventListener('click', event => {
                    if (event.target === overlay) close(false);
                });
                document.addEventListener('keydown', handleKeydown, true);
                requestAnimationFrame(() => {
                    overlay.classList.add('opacity-100');
                    modal.classList.remove('translate-y-4', 'scale-95', 'opacity-0');
                    modal.classList.add('translate-y-0', 'scale-100', 'opacity-100');
                    modal.focus();
                });
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                showAppNotification(
                    'warning',
                    'Revisa los datos del espacio',
                    @json(collect($errors->all())->implode("\n"))
                );
            @endif
            @if (session('error'))
                showAppNotification(
                    'error',
                    'No se pudo actualizar el espacio',
                    @json(session('error'))
                );
            @endif
            @if (session('success'))
                showAppNotification(
                    'success',
                    'Espacio actualizado correctamente',
                    @json(session('success'))
                );
            @endif
        });
        document.addEventListener('submit', async function(event) {
            const deleteForm = event.target.closest('.form-eliminar-espacio');
            if (!deleteForm) return;
            event.preventDefault();
            const confirmed = await showSpaceDeleteConfirm(
                deleteForm.dataset.nombre
            );
            if (!confirmed) return;
            deleteForm.submit();
        });
    </script>
@endsection
