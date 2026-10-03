@extends('layouts.navbars')

@section('title', 'Nueva etiqueta')

@section('content')
<div class="min-h-screen bg-slate-950">
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6">
            <a href="{{ route('admin.etiquetas.index') }}"
                class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>
                Volver a etiquetas
            </a>

            <h1 class="text-2xl font-bold text-white">Nueva etiqueta</h1>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
                Crea una etiqueta para identificar una característica específica que pueda
                aplicarse a una o varias actividades.
            </p>
        </div>

        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
            <button
                type="button"
                id="btnGuiaEtiquetas"
                aria-expanded="false"
                aria-controls="guiaEtiquetas"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-slate-800/50">

                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-white">
                            ¿No sabes qué etiqueta crear?
                        </p>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Consulta algunos criterios y ejemplos antes de crearla.
                        </p>
                    </div>
                </div>

                <svg
                    id="iconoGuiaEtiquetas"
                    class="h-5 w-5 shrink-0 text-slate-500 transition-transform duration-200"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div id="guiaEtiquetas" class="hidden border-t border-slate-800">
                <div class="space-y-5 px-5 py-5">
                    <div>
                        <h2 class="text-sm font-semibold text-emerald-300">
                            ¿Cuándo conviene crear una etiqueta?
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Utiliza una etiqueta cuando necesites describir una característica
                            adicional que pueda compartirse entre distintas actividades.
                            Una misma actividad puede tener varias etiquetas.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Buenos ejemplos
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="rounded-lg bg-slate-800 px-2.5 py-1 text-xs text-slate-300">
                                    Presencial
                                </span>
                                <span class="rounded-lg bg-slate-800 px-2.5 py-1 text-xs text-slate-300">
                                    Virtual
                                </span>
                                <span class="rounded-lg bg-slate-800 px-2.5 py-1 text-xs text-slate-300">
                                    Principiantes
                                </span>
                                <span class="rounded-lg bg-slate-800 px-2.5 py-1 text-xs text-slate-300">
                                    Excel
                                </span>
                                <span class="rounded-lg bg-slate-800 px-2.5 py-1 text-xs text-slate-300">
                                    Emprendimiento
                                </span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Diferencia con una categoría
                            </p>

                            <p class="mt-3 text-sm leading-6 text-slate-400">
                                La <strong class="font-semibold text-slate-300">categoría</strong>
                                indica la clasificación principal de la actividad.
                                Las <strong class="font-semibold text-slate-300">etiquetas</strong>
                                agregan características más específicas.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
                        <p class="text-sm leading-6 text-slate-400">
                            <strong class="font-semibold text-amber-300">Ejemplo:</strong>
                            una actividad puede pertenecer a la categoría
                            <span class="font-medium text-slate-300">Capacitación</span>
                            y utilizar las etiquetas
                            <span class="font-medium text-slate-300">Excel</span>,
                            <span class="font-medium text-slate-300">Principiantes</span> y
                            <span class="font-medium text-slate-300">Presencial</span>.
                        </p>
                    </div>

                    <p class="text-xs leading-5 text-slate-500">
                        Procura utilizar nombres cortos y concretos. Antes de crear una nueva
                        etiqueta, verifica que no exista otra con el mismo significado.
                    </p>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-4">
                <p class="text-sm font-semibold text-red-300">
                    Revisa la información ingresada.
                </p>

                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-300/90">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.etiquetas.store') }}">
            @csrf

            <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
                <div class="border-b border-slate-800 px-6 py-5">
                    <h2 class="text-base font-semibold text-white">
                        Información de la etiqueta
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Utiliza un nombre corto, claro y fácil de reconocer.
                    </p>
                </div>

                <div class="space-y-6 p-6">
                    <div>
                        <label for="nombre" class="mb-2 block text-sm font-medium text-slate-300">
                            Nombre de la etiqueta
                            <span class="text-red-400">*</span>
                        </label>

                        <input
                            id="nombre"
                            name="nombre"
                            type="text"
                            maxlength="120"
                            required
                            autofocus
                            value="{{ old('nombre') }}"
                            placeholder="Ej. Principiantes"
                            class="w-full rounded-xl border bg-slate-950 px-4 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:ring-2
                                {{ $errors->has('nombre')
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                    : 'border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20' }}">

                        @error('nombre')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Describe una característica concreta. Evita nombres demasiado amplios
                            que deberían utilizarse como categoría.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-800 bg-slate-950/60 p-4">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input type="hidden" name="activo" value="0">

                            <input
                                id="activo"
                                name="activo"
                                type="checkbox"
                                value="1"
                                @checked(old('activo', '1') == '1')
                                class="mt-0.5 h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-950">

                            <span>
                                <span class="block text-sm font-medium text-white">
                                    Disponible para utilizar
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Si está activa podrá seleccionarse al configurar actividades.
                                    Puedes desactivarla posteriormente sin eliminarla.
                                </span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-800 px-6 py-4 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.etiquetas.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Crear etiqueta
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const boton = document.getElementById('btnGuiaEtiquetas');
    const contenido = document.getElementById('guiaEtiquetas');
    const icono = document.getElementById('iconoGuiaEtiquetas');

    if (!boton || !contenido || !icono) {
        return;
    }

    boton.addEventListener('click', function () {
        const abierto = boton.getAttribute('aria-expanded') === 'true';

        boton.setAttribute('aria-expanded', abierto ? 'false' : 'true');
        contenido.classList.toggle('hidden', abierto);
        icono.classList.toggle('rotate-180', !abierto);
    });
});
</script>
@endsection