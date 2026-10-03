@extends('layouts.navbars')

@section('title', 'Editar etiqueta')

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

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">
                        Editar etiqueta
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">
                        Modifica el nombre o la disponibilidad de esta etiqueta.
                        Los cambios se reflejarán en las actividades donde se utilice.
                    </p>
                </div>

                @if ($etiqueta->activo)
                    <span class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Activa
                    </span>
                @else
                    <span class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                        Inactiva
                    </span>
                @endif
            </div>
        </div>

        <div class="mb-6 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-5">
            <div class="flex items-start gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-emerald-300">
                        Recuerda para qué sirve una etiqueta
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-slate-400">
                        Una etiqueta describe una característica adicional de una actividad,
                        como su modalidad, temática, público o nivel. Una actividad puede
                        utilizar varias etiquetas al mismo tiempo.
                    </p>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        No debe confundirse con la categoría, que representa la clasificación
                        principal de la actividad.
                    </p>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-4">
                <div class="flex gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M5.07 19h13.86A2 2 0 0020.66 16L13.73 4a2 2 0 00-3.46 0L3.34 16A2 2 0 005.07 19z" />
                    </svg>

                    <div>
                        <p class="text-sm font-semibold text-red-300">
                            Revisa la información ingresada.
                        </p>

                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-300/90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-900 p-5 shadow-xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Uso actual
                    </p>

                    <p class="mt-1 text-base font-semibold text-white">
                        {{ $etiqueta->nombre }}
                    </p>
                </div>

                <div class="flex items-center gap-3 rounded-xl bg-slate-950 px-4 py-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17v-2a4 4 0 014-4h6m0 0-3-3m3 3-3 3M5 7h5a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-xs text-slate-500">
                            Actividades asociadas
                        </p>

                        <p class="text-lg font-bold text-white">
                            {{ $etiqueta->actividades_count }}
                        </p>
                    </div>
                </div>
            </div>

            @if ($etiqueta->actividades_count > 0)
                <div class="mt-4 border-t border-slate-800 pt-4">
                    <p class="text-sm leading-6 text-slate-400">
                        Esta etiqueta está siendo utilizada por
                        <strong class="font-semibold text-slate-300">
                            {{ $etiqueta->actividades_count }}
                            {{ $etiqueta->actividades_count === 1 ? 'actividad' : 'actividades' }}.
                        </strong>
                        Puedes modificarla o desactivarla, pero no eliminarla mientras mantenga estas asociaciones.
                    </p>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.etiquetas.update', $etiqueta) }}">
            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
                <div class="border-b border-slate-800 px-6 py-5">
                    <h2 class="text-base font-semibold text-white">
                        Información de la etiqueta
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Mantén un nombre corto y específico que describa claramente una característica.
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
                            value="{{ old('nombre', $etiqueta->nombre) }}"
                            placeholder="Ej. Principiantes"
                            class="w-full rounded-xl border bg-slate-950 px-4 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-600 focus:ring-2
                                {{ $errors->has('nombre')
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                    : 'border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20' }}">

                        @error('nombre')
                            <p class="mt-2 text-sm text-red-400">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Si cambias el nombre, el cambio también se verá reflejado cuando
                            esta etiqueta aparezca asociada a una actividad.
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
                                @checked(old('activo', $etiqueta->activo ? '1' : '0') == '1')
                                class="mt-0.5 h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-950">

                            <span>
                                <span class="block text-sm font-medium text-white">
                                    Disponible para utilizar
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Si está activa podrá seleccionarse al configurar actividades.
                                    Si la desactivas, conservará sus datos y asociaciones existentes,
                                    pero dejará de estar disponible para nuevas asignaciones.
                                </span>
                            </span>
                        </label>
                    </div>

                    @if ($etiqueta->actividades_count > 0)
                        <div class="rounded-xl border border-amber-500/20 bg-amber-500/10 p-4">
                            <div class="flex items-start gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01M10.29 3.86 1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                </svg>

                                <div>
                                    <p class="text-sm font-semibold text-amber-300">
                                        Esta etiqueta está en uso
                                    </p>

                                    <p class="mt-1 text-sm leading-6 text-amber-200/70">
                                        Antes de cambiar su nombre, considera que el nuevo nombre
                                        se utilizará para todas las actividades que actualmente
                                        tienen asociada esta etiqueta.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
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
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>

        @if ($etiqueta->actividades_count === 0)
            <div class="mt-6 rounded-2xl border border-red-500/20 bg-red-500/5 p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-red-300">
                            Eliminar etiqueta
                        </h2>

                        <p class="mt-1 max-w-xl text-sm leading-6 text-slate-400">
                            Esta etiqueta no está asociada a ninguna actividad y puede eliminarse.
                            La eliminación es permanente y no se puede deshacer.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.etiquetas.destroy', $etiqueta) }}"
                        onsubmit="return confirm('¿Seguro que deseas eliminar esta etiqueta? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-300 transition hover:bg-red-500/20">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                            </svg>
                            Eliminar etiqueta
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection