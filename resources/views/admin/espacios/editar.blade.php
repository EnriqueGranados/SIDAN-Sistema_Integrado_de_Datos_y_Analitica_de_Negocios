@extends('layouts.navbars')

@section('title', 'Editar espacio')

@section('content')
<div class="min-h-screen bg-slate-950">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-5">
            <a href="{{ route('admin.espacios.index') }}"
                class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
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

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3">
                <p class="text-sm font-semibold text-red-300">
                    Hay información que necesita revisión.
                </p>

                <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-red-300/90">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
                        <div
                            data-linea-paso="{{ $numero - 1 }}"
                            class="mx-3 h-px flex-1 bg-slate-800">
                        </div>
                    @endif

                    <button
                        type="button"
                        data-indicador-paso="{{ $numero }}"
                        class="flex shrink-0 items-center gap-2.5">

                        <span
                            data-circulo-paso
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

        <form
            id="formEspacio"
            method="POST"
            action="{{ route('admin.espacios.update', $espacio) }}">

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
                            <label for="nombre"
                                class="mb-2 block text-sm font-medium text-slate-300">
                                Nombre del espacio
                                <span class="text-red-400">*</span>
                            </label>

                            <input
                                id="nombre"
                                name="nombre"
                                type="text"
                                maxlength="150"
                                required
                                value="{{ old('nombre', $espacio->nombre) }}"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">

                            <p id="errorNombre" class="mt-2 hidden text-xs text-red-400">
                                Escribe el nombre del espacio.
                            </p>
                        </div>

                        <div>
                            <label for="descripcion"
                                class="mb-2 block text-sm font-medium text-slate-300">
                                Descripción
                                <span class="font-normal text-slate-600">(opcional)</span>
                            </label>

                            <textarea
                                id="descripcion"
                                name="descripcion"
                                rows="3"
                                maxlength="2000"
                                placeholder="Información adicional sobre el espacio..."
                                class="w-full resize-y rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">{{ old('descripcion', $espacio->descripcion) }}</textarea>
                        </div>

                        <div class="rounded-xl border border-slate-800 bg-slate-950/40 p-4">
                            <label for="id_espacio_contenedor"
                                class="block text-sm font-medium text-white">
                                ¿Este espacio se encuentra dentro de otro lugar?
                            </label>

                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Cambia esta opción únicamente si el espacio fue trasladado o registrado en el lugar equivocado.
                            </p>

                            <select
                                id="id_espacio_contenedor"
                                name="id_espacio_contenedor"
                                class="mt-3 w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">

                                <option value="">
                                    No, es un lugar independiente
                                </option>

                                @foreach ($espaciosDisponibles as $espacioDisponible)
                                    <option
                                        value="{{ $espacioDisponible->id_espacio }}"
                                        @selected(
                                            (string) old(
                                                'id_espacio_contenedor',
                                                $espacio->id_espacio_contenedor
                                            ) ===
                                            (string) $espacioDisponible->id_espacio
                                        )>
                                        {{ $espacioDisponible->nombre }}
                                    </option>
                                @endforeach
                            </select>

                            <div
                                id="avisoCambioLugar"
                                class="mt-3 hidden rounded-lg border border-amber-500/20 bg-amber-500/5 p-3">

                                <p class="text-xs leading-5 text-slate-400">
                                    Cambiaste el lugar donde se encuentra este espacio.
                                    Puedes conservar su ubicación actual o copiar la ubicación registrada de
                                    <strong id="nombreNuevoLugar" class="font-medium text-amber-300"></strong>.
                                </p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button
                                        id="btnUsarUbicacionLugar"
                                        type="button"
                                        class="rounded-lg bg-amber-500/10 px-3 py-2 text-xs font-semibold text-amber-300 transition hover:bg-amber-500/20">
                                        Usar ubicación de este lugar
                                    </button>

                                    <button
                                        id="btnConservarUbicacion"
                                        type="button"
                                        class="rounded-lg border border-slate-700 px-3 py-2 text-xs font-medium text-slate-400 transition hover:bg-slate-800 hover:text-white">
                                        Conservar ubicación actual
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="capacidad"
                                class="mb-2 block text-sm font-medium text-slate-300">
                                Capacidad máxima
                                <span class="font-normal text-slate-600">(opcional)</span>
                            </label>

                            <div class="relative max-w-xs">
                                <input
                                    id="capacidad"
                                    name="capacidad"
                                    type="number"
                                    min="1"
                                    step="1"
                                    value="{{ old('capacidad', $espacio->capacidad) }}"
                                    placeholder="Ej. 200"
                                    class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 pr-20 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">

                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-slate-500">
                                    personas
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <label class="cursor-pointer rounded-xl border border-slate-800 bg-slate-950/50 p-4 transition hover:border-slate-700">
                                <div class="flex items-start gap-3">
                                    <input
                                        id="permite_actividades"
                                        type="checkbox"
                                        name="permite_actividades"
                                        value="1"
                                        @checked(
                                            old(
                                                'permite_actividades',
                                                $espacio->permite_actividades ? '1' : '0'
                                            ) == '1'
                                        )
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

                            <label class="cursor-pointer rounded-xl border border-slate-800 bg-slate-950/50 p-4 transition hover:border-slate-700">
                                <div class="flex items-start gap-3">
                                    <input
                                        id="activo"
                                        type="checkbox"
                                        name="activo"
                                        value="1"
                                        @checked(
                                            old(
                                                'activo',
                                                $espacio->activo ? '1' : '0'
                                            ) == '1'
                                        )
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

                        <div
                            id="avisoUbicacionCopiada"
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
                            <label for="direccion"
                                class="mb-2 block text-sm font-medium text-slate-300">
                                Dirección
                            </label>

                            <input
                                id="direccion"
                                name="direccion"
                                type="text"
                                maxlength="300"
                                value="{{ old('direccion', $espacio->direccion) }}"
                                placeholder="Ej. Ciudad Universitaria, San Salvador"
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                        </div>

                        <div>
                            <label for="indicaciones"
                                class="mb-2 block text-sm font-medium text-slate-300">
                                Indicaciones para llegar
                            </label>

                            <textarea
                                id="indicaciones"
                                name="indicaciones"
                                rows="3"
                                maxlength="500"
                                placeholder="Ej. Segundo nivel, frente al laboratorio..."
                                class="w-full resize-y rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">{{ old('indicaciones', $espacio->indicaciones) }}</textarea>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-slate-800">
                            <button
                                id="btnCoordenadas"
                                type="button"
                                class="flex w-full items-center justify-between gap-4 bg-slate-950/50 px-4 py-3 text-left transition hover:bg-slate-800/40">

                                <div>
                                    <p class="text-sm font-medium text-slate-300">
                                        Coordenadas
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Información geográfica opcional.
                                    </p>
                                </div>

                                <svg
                                    id="iconoCoordenadas"
                                    class="h-4 w-4 shrink-0 text-slate-500 transition-transform"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div
                                id="contenidoCoordenadas"
                                class="{{ old('latitud', $espacio->latitud) || old('longitud', $espacio->longitud) ? '' : 'hidden' }} border-t border-slate-800 p-4">

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="latitud"
                                            class="mb-2 block text-xs font-medium text-slate-400">
                                            Latitud
                                        </label>

                                        <input
                                            id="latitud"
                                            name="latitud"
                                            type="number"
                                            step="0.0000001"
                                            min="-90"
                                            max="90"
                                            value="{{ old('latitud', $espacio->latitud) }}"
                                            placeholder="Ej. 13.7188000"
                                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                    </div>

                                    <div>
                                        <label for="longitud"
                                            class="mb-2 block text-xs font-medium text-slate-400">
                                            Longitud
                                        </label>

                                        <input
                                            id="longitud"
                                            name="longitud"
                                            type="number"
                                            step="0.0000001"
                                            min="-180"
                                            max="180"
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
                                <input
                                    id="buscarRecurso"
                                    type="search"
                                    autocomplete="off"
                                    placeholder="Buscar recurso..."
                                    class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 sm:max-w-sm">

                                <span
                                    id="contadorRecursos"
                                    class="shrink-0 text-xs font-medium text-slate-500">
                                    0 seleccionados
                                </span>
                            </div>

                            <div id="listaRecursos" class="space-y-2">
                                @foreach ($recursos as $recurso)
                                    @php
                                        $recursoActual = $espacio->recursos->firstWhere(
                                            'id_recurso',
                                            $recurso->id_recurso
                                        );

                                        $seleccionado = old(
                                            "recursos.{$recurso->id_recurso}.seleccionado",
                                            $recursoActual ? '1' : '0'
                                        ) == '1';

                                        $cantidadActual = old(
                                            "recursos.{$recurso->id_recurso}.cantidad",
                                            $recursoActual?->pivot?->cantidad ?? 1
                                        );

                                        $observacionActual = old(
                                            "recursos.{$recurso->id_recurso}.observacion",
                                            $recursoActual?->pivot?->observacion
                                        );
                                    @endphp

                                    <div
                                        class="recurso-item overflow-hidden rounded-lg border border-slate-800 bg-slate-950/40"
                                        data-nombre="{{ mb_strtolower($recurso->nombre) }}"
                                        data-categoria="{{ mb_strtolower($recurso->categoria ?? '') }}">

                                        <label class="flex cursor-pointer items-center gap-3 px-3 py-2.5">
                                            <input
                                                type="checkbox"
                                                name="recursos[{{ $recurso->id_recurso }}][seleccionado]"
                                                value="1"
                                                data-recurso-check
                                                data-recurso-id="{{ $recurso->id_recurso }}"
                                                @checked($seleccionado)
                                                class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500">

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span
                                                        data-nombre-recurso
                                                        class="text-sm font-medium text-white">
                                                        {{ $recurso->nombre }}
                                                    </span>

                                                    @if ($recurso->categoria)
                                                        <span class="text-xs text-slate-500">
                                                            {{ $recurso->categoria }}
                                                        </span>
                                                    @endif

                                                    @if (!$recurso->activo)
                                                        <span class="rounded bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-medium text-amber-400">
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

                                        <div
                                            id="detalleRecurso{{ $recurso->id_recurso }}"
                                            class="{{ $seleccionado ? '' : 'hidden' }} border-t border-slate-800 px-3 py-3">

                                            <div class="grid gap-3 sm:grid-cols-[180px_1fr]">
                                                <div>
                                                    <label
                                                        for="cantidadRecurso{{ $recurso->id_recurso }}"
                                                        class="mb-1 block text-xs text-slate-500">
                                                        Cantidad disponible
                                                    </label>

                                                    <div class="relative">
                                                        <input
                                                            id="cantidadRecurso{{ $recurso->id_recurso }}"
                                                            name="recursos[{{ $recurso->id_recurso }}][cantidad]"
                                                            type="number"
                                                            min="1"
                                                            step="1"
                                                            value="{{ $cantidadActual }}"
                                                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 pr-16 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">

                                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-xs text-slate-500">
                                                            {{ $recurso->unidad_medida }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label
                                                        for="observacionRecurso{{ $recurso->id_recurso }}"
                                                        class="mb-1 block text-xs text-slate-500">
                                                        Nota
                                                        <span class="text-slate-600">(opcional)</span>
                                                    </label>

                                                    <input
                                                        id="observacionRecurso{{ $recurso->id_recurso }}"
                                                        name="recursos[{{ $recurso->id_recurso }}][observacion]"
                                                        type="text"
                                                        maxlength="300"
                                                        value="{{ $observacionActual }}"
                                                        placeholder="Ej. Instalado al frente"
                                                        class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div
                                id="sinResultadosRecursos"
                                class="hidden py-8 text-center text-sm text-slate-500">
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

                                <a
                                    href="{{ route('admin.recursos.create') }}"
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

                                    <div
                                        id="resumenListaRecursos"
                                        class="mt-2 space-y-1 text-xs text-slate-500">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- NAVEGACIÓN --}}
                <div class="flex items-center justify-between gap-3 border-t border-slate-800 px-5 py-4 sm:px-6">

                    <div>
                        <a
                            id="btnCancelar"
                            href="{{ route('admin.espacios.index') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-700 px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white">
                            Cancelar
                        </a>

                        <button
                            id="btnAnterior"
                            type="button"
                            class="hidden items-center gap-2 rounded-lg border border-slate-700 px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                            Anterior
                        </button>
                    </div>

                    <button
                        id="btnSiguiente"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-400">
                        Siguiente

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <button
                        id="btnGuardar"
                        type="submit"
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

                <form
                    method="POST"
                    action="{{ route('admin.espacios.destroy', $espacio) }}"
                    onsubmit="return confirm('¿Seguro que deseas eliminar este espacio? Esta acción no se puede deshacer.');">

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        @disabled($espacio->espacios_internos_count > 0)
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
        <div
            data-espacio-id="{{ $espacioDisponible->id_espacio }}"
            data-nombre="{{ $espacioDisponible->nombre }}"
            data-direccion="{{ $espacioDisponible->direccion ?? '' }}"
            data-indicaciones="{{ $espacioDisponible->indicaciones ?? '' }}"
            data-latitud="{{ $espacioDisponible->latitud ?? '' }}"
            data-longitud="{{ $espacioDisponible->longitud ?? '' }}">
        </div>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
    ).forEach(function (elemento) {
        espacios.set(
            String(elemento.dataset.espacioId),
            {
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

        secciones.forEach(function (seccion) {
            seccion.classList.toggle(
                'hidden',
                Number(seccion.dataset.paso) !== numero
            );
        });

        indicadores.forEach(function (indicador) {
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
        ).forEach(function (linea) {
            const numeroLinea = Number(
                linea.dataset.lineaPaso
            );

            linea.className =
                numeroLinea < numero
                    ? 'mx-3 h-px flex-1 bg-emerald-500/40'
                    : 'mx-3 h-px flex-1 bg-slate-800';
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
            capacidad.value !== ''
                ? `${capacidad.value} personas`
                : 'Sin capacidad definida';

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
            permiteActividades.checked
                ? 'Se pueden realizar actividades aquí'
                : 'Se utiliza para organizar otros espacios'
        );

        usos.push(
            activo.checked
                ? 'Disponible'
                : 'No disponible'
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
            seleccionados.length === 1
                ? '1 recurso seleccionado'
                : `${seleccionados.length} recursos seleccionados`;

        seleccionados.forEach(function (checkbox) {
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
            total === 1
                ? '1 seleccionado'
                : `${total} seleccionados`;
    }

    function normalizar(texto) {
        return texto
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    btnSiguiente.addEventListener(
        'click',
        function () {
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
        function () {
            if (pasoActual > 1) {
                mostrarPaso(
                    pasoActual - 1
                );
            }
        }
    );

    indicadores.forEach(function (indicador) {
        indicador.addEventListener(
            'click',
            function () {
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
        function () {
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
        function () {
            avisoCambioLugar.classList.add(
                'hidden'
            );
        }
    );

    btnCoordenadas.addEventListener(
        'click',
        function () {
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
    ).forEach(function (checkbox) {
        checkbox.addEventListener(
            'change',
            function () {
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
            function () {
                const termino =
                    normalizar(
                        buscarRecurso.value.trim()
                    );

                let visibles = 0;

                document.querySelectorAll(
                    '.recurso-item'
                ).forEach(function (item) {
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
@endsection