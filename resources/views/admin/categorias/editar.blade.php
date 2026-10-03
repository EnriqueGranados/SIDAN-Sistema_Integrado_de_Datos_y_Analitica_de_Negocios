@extends('layouts.navbars')

@section('title', 'Editar categoría')

@section('content')
<div class="min-h-screen bg-slate-950">
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.categorias.index') }}"
                class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6" />
                </svg>
                Volver a categorías
            </a>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Editar categoría</h1>
                    <p class="mt-1 text-sm text-slate-400">
                        Actualiza la información de {{ $categoria->nombre }}.
                    </p>
                </div>

                @if ($categoria->activo)
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1 text-xs font-medium text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Activa
                    </span>
                @else
                    <span class="inline-flex w-fit items-center gap-1.5 rounded-full border border-slate-600 bg-slate-800 px-3 py-1 text-xs font-medium text-slate-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                        Inactiva
                    </span>
                @endif
            </div>
        </div>

        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3">
                <div class="flex items-start gap-3">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                    </svg>

                    <div>
                        <p class="text-sm font-semibold text-red-300">Revisa la información ingresada.</p>
                        <ul class="mt-2 space-y-1 text-sm text-red-300/90">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST"
            action="{{ route('admin.categorias.update', $categoria) }}"
            class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70 shadow-xl shadow-black/10">
            @csrf
            @method('PUT')

            <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-white">Información de la categoría</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            El slug se actualiza automáticamente cuando cambia el nombre.
                        </p>
                    </div>

                    <div class="rounded-lg bg-slate-800 px-3 py-2">
                        <p class="text-xs text-slate-500">Slug actual</p>
                        <p class="mt-0.5 text-xs font-medium text-slate-300">{{ $categoria->slug }}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-6 p-5 sm:p-6">
                <div>
                    <label for="nombre" class="mb-2 block text-sm font-semibold text-slate-300">
                        Nombre
                        <span class="text-red-400">*</span>
                    </label>

                    <input type="text"
                        id="nombre"
                        name="nombre"
                        value="{{ old('nombre', $categoria->nombre) }}"
                        maxlength="100"
                        required
                        autofocus
                        placeholder="Ej. Cursos y talleres"
                        class="h-11 w-full rounded-xl border bg-slate-950/80 px-4 text-sm text-white outline-none transition placeholder:text-slate-600 focus:ring-2
                            @error('nombre')
                                border-red-500 focus:border-red-500 focus:ring-red-500/20
                            @else
                                border-slate-700 focus:border-indigo-500 focus:ring-indigo-500/20
                            @enderror">

                    @error('nombre')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label for="descripcion" class="block text-sm font-semibold text-slate-300">
                            Descripción
                        </label>
                        <span class="text-xs text-slate-600">Opcional</span>
                    </div>

                    <textarea id="descripcion"
                        name="descripcion"
                        rows="5"
                        placeholder="Describe brevemente qué tipo de actividades pertenecen a esta categoría..."
                        class="w-full resize-y rounded-xl border bg-slate-950/80 px-4 py-3 text-sm leading-6 text-white outline-none transition placeholder:text-slate-600 focus:ring-2
                            @error('descripcion')
                                border-red-500 focus:border-red-500 focus:ring-red-500/20
                            @else
                                border-slate-700 focus:border-indigo-500 focus:ring-indigo-500/20
                            @enderror">{{ old('descripcion', $categoria->descripcion) }}</textarea>

                    @error('descripcion')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="activo" class="mb-2 block text-sm font-semibold text-slate-300">
                        Estado
                        <span class="text-red-400">*</span>
                    </label>

                    <select id="activo"
                        name="activo"
                        required
                        class="h-11 w-full rounded-xl border bg-slate-950/80 px-3 text-sm text-white outline-none transition focus:ring-2
                            @error('activo')
                                border-red-500 focus:border-red-500 focus:ring-red-500/20
                            @else
                                border-slate-700 focus:border-indigo-500 focus:ring-indigo-500/20
                            @enderror">
                        <option value="1" @selected((string) old('activo', $categoria->activo ? '1' : '0') === '1')>
                            Activa
                        </option>
                        <option value="0" @selected((string) old('activo', $categoria->activo ? '1' : '0') === '0')>
                            Inactiva
                        </option>
                    </select>

                    @error('activo')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-xs text-slate-500">
                        Puedes desactivar la categoría sin eliminarla del sistema.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Actividades asociadas
                        </p>

                        <div class="mt-2 flex items-end gap-2">
                            <span class="text-2xl font-bold text-white">
                                {{ $categoria->actividades_count }}
                            </span>

                            <span class="pb-1 text-xs text-slate-500">
                                {{ $categoria->actividades_count === 1 ? 'actividad' : 'actividades' }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Identificador
                        </p>

                        <p class="mt-2 text-2xl font-bold text-white">
                            #{{ $categoria->id_categoria }}
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/5 p-4">
                    <div class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>

                        <div>
                            <p class="text-sm font-semibold text-indigo-300">
                                Posición de la categoría
                            </p>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                La posición se administra desde el listado de categorías, por lo que no necesitas asignar números manualmente.
                            </p>
                        </div>
                    </div>
                </div>

                @if ($categoria->actividades_count > 0)
                    <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
                        <div class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                            </svg>

                            <div>
                                <p class="text-sm font-semibold text-amber-300">
                                    Categoría en uso
                                </p>
                                <p class="mt-1 text-sm leading-6 text-slate-400">
                                    Esta categoría tiene actividades asociadas. Puedes modificarla o desactivarla, pero no eliminarla mientras existan esas relaciones.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-800 bg-slate-900/50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                <a href="{{ route('admin.categorias.index') }}"
                    class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-700 px-5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                    Cancelar
                </a>

                <button type="submit"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12l4 4L19 6" />
                    </svg>
                    Guardar cambios
                </button>
            </div>
        </form>

        @if ($categoria->actividades_count === 0)
            <div class="mt-6 rounded-2xl border border-red-500/20 bg-red-500/5 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold text-red-300">Eliminar categoría</h2>
                        <p class="mt-1 text-sm text-slate-400">
                            Esta acción es permanente y no se puede deshacer.
                        </p>
                    </div>

                    <form method="POST"
                        action="{{ route('admin.categorias.destroy', $categoria) }}"
                        onsubmit="return confirm('¿Eliminar esta categoría? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-red-500/20 bg-red-500/10 px-4 text-sm font-semibold text-red-300 transition hover:bg-red-500/20 sm:w-auto">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3" />
                            </svg>
                            Eliminar categoría
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection