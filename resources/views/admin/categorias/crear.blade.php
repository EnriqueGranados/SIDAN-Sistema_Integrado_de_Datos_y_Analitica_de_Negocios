@extends('layouts.navbars')

@section('title', 'Nueva categoría')

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

            <h1 class="text-2xl font-bold text-white">Nueva categoría</h1>
            <p class="mt-1 text-sm text-slate-400">Crea una categoría para organizar las actividades del sistema.</p>
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
            action="{{ route('admin.categorias.store') }}"
            class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70 shadow-xl shadow-black/10">
            @csrf

            <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                <h2 class="font-semibold text-white">Información de la categoría</h2>
                <p class="mt-1 text-sm text-slate-500">
                    El slug y la posición se administrarán automáticamente.
                </p>
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
                        value="{{ old('nombre') }}"
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

                    <p class="mt-2 text-xs text-slate-500">
                        Utiliza un nombre claro que permita identificar fácilmente el tipo de actividad.
                    </p>
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
                            @enderror">{{ old('descripcion') }}</textarea>

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
                        <option value="1" @selected(old('activo', '1') === '1')>
                            Activa
                        </option>
                        <option value="0" @selected(old('activo') === '0')>
                            Inactiva
                        </option>
                    </select>

                    @error('activo')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror

                    <p class="mt-2 text-xs text-slate-500">
                        Puedes desactivar una categoría para conservarla sin tener que eliminarla.
                    </p>
                </div>

                <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/5 p-4">
                    <div class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>

                        <div>
                            <p class="text-sm font-semibold text-indigo-300">
                                Posición automática
                            </p>
                            <p class="mt-1 text-sm leading-6 text-slate-400">
                                La nueva categoría se colocará al final de la lista. Después podrás cambiar su posición directamente desde el listado de categorías.
                            </p>
                        </div>
                    </div>
                </div>
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
                    Crear categoría
                </button>
            </div>
        </form>
    </div>
</div>
@endsection