@extends('layouts.navbars')

@section('title', 'Editar recurso')

@section('content')
<div class="min-h-screen bg-slate-950">
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6">
            <a href="{{ route('admin.recursos.index') }}"
                class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-400 transition hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>
                Volver a recursos
            </a>

            <h1 class="text-2xl font-bold text-white">Editar recurso</h1>
            <p class="mt-2 text-sm leading-6 text-slate-400">
                Actualiza la información de <span class="font-medium text-slate-300">{{ $recurso->nombre }}</span>.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-4">
                <p class="text-sm font-semibold text-red-300">
                    Revisa la información ingresada.
                </p>

                <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-300/90">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-900 p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-white">
                        Uso actual del recurso
                    </p>
                    <p class="mt-1 text-sm text-slate-500">
                        Este recurso está asociado actualmente a
                        <span class="font-semibold text-slate-300">
                            {{ $recurso->espacios_count }}
                        </span>
                        {{ $recurso->espacios_count === 1 ? 'espacio' : 'espacios' }}.
                    </p>
                </div>

                @if ($recurso->espacios_count > 0)
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-400">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        En uso
                    </span>
                @else
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-slate-800 px-3 py-1.5 text-xs font-medium text-slate-400">
                        Sin espacios asociados
                    </span>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('admin.recursos.update', $recurso) }}">
            @csrf
            @method('PUT')

            <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
                <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                    <h2 class="font-semibold text-white">Información del recurso</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Modifica únicamente los datos que necesites actualizar.
                    </p>
                </div>

                <div class="space-y-6 p-5 sm:p-6">
                    <div>
                        <label for="nombre" class="mb-2 block text-sm font-medium text-slate-300">
                            Nombre del recurso
                            <span class="text-red-400">*</span>
                        </label>

                        <input
                            id="nombre"
                            name="nombre"
                            type="text"
                            maxlength="120"
                            required
                            autofocus
                            value="{{ old('nombre', $recurso->nombre) }}"
                            placeholder="Ej. Proyector multimedia"
                            class="w-full rounded-xl border bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-500 focus:ring-2
                                {{ $errors->has('nombre')
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                    : 'border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20' }}">

                        @error('nombre')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="descripcion" class="mb-2 block text-sm font-medium text-slate-300">
                            Descripción
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="3"
                            maxlength="1000"
                            placeholder="Describe brevemente el recurso si es necesario..."
                            class="w-full resize-y rounded-xl border bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-500 focus:ring-2
                                {{ $errors->has('descripcion')
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                    : 'border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20' }}">{{ old('descripcion', $recurso->descripcion) }}</textarea>

                        @error('descripcion')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label for="categoria" class="mb-2 block text-sm font-medium text-slate-300">
                                Categoría
                            </label>

                            <div class="relative">
                                <input
                                    id="categoria"
                                    name="categoria"
                                    type="text"
                                    maxlength="80"
                                    value="{{ old('categoria', $recurso->categoria) }}"
                                    placeholder="Ej. Audiovisual"
                                    autocomplete="off"
                                    class="w-full rounded-xl border bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-500 focus:ring-2
                                        {{ $errors->has('categoria')
                                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                            : 'border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20' }}">

                                <div
                                    id="sugerenciasCategoria"
                                    class="absolute z-20 mt-2 hidden max-h-52 w-full overflow-y-auto rounded-xl border border-slate-700 bg-slate-900 p-1 shadow-2xl">
                                </div>
                            </div>

                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                Agrupa recursos similares. Por ejemplo: Audiovisual, Audio,
                                Mobiliario, Tecnología o Conectividad. Empieza a escribir para ver opciones.
                            </p>

                            @error('categoria')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="unidad_medida" class="mb-2 block text-sm font-medium text-slate-300">
                                Forma de medir la cantidad
                                <span class="text-red-400">*</span>
                            </label>

                            <select
                                id="unidad_medida"
                                name="unidad_medida"
                                required
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                                <option value="unidad"
                                    @selected(old('unidad_medida', $recurso->unidad_medida) === 'unidad')>
                                    Unidades
                                </option>

                                <option value="par"
                                    @selected(old('unidad_medida', $recurso->unidad_medida) === 'par')>
                                    Pares
                                </option>

                                <option value="juego"
                                    @selected(old('unidad_medida', $recurso->unidad_medida) === 'juego')>
                                    Juegos / conjuntos
                                </option>

                                <option value="metro"
                                    @selected(old('unidad_medida', $recurso->unidad_medida) === 'metro')>
                                    Metros
                                </option>
                            </select>

                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                Para proyectores, micrófonos, sillas, mesas y similares utiliza “Unidades”.
                            </p>

                            @error('unidad_medida')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="border-t border-slate-800 pt-6">
                        <p class="text-sm font-medium text-slate-300">
                            ¿Este recurso puede trasladarse entre diferentes espacios?
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Un recurso móvil puede utilizarse en distintos lugares. Si normalmente
                            forma parte de un espacio determinado, déjalo desmarcado.
                        </p>

                        <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-700 bg-slate-950 p-4 transition hover:border-slate-600">
                            <input
                                type="checkbox"
                                name="es_movil"
                                value="1"
                                @checked(old('es_movil', $recurso->es_movil ? '1' : '0') == '1')
                                class="mt-0.5 h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500">

                            <div>
                                <span class="block text-sm font-medium text-white">
                                    Sí, es un recurso móvil
                                </span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Puede trasladarse y utilizarse en diferentes espacios o actividades.
                                </span>
                            </div>
                        </label>
                    </div>

                    <div class="border-t border-slate-800 pt-6">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input
                                type="checkbox"
                                name="activo"
                                value="1"
                                @checked(old('activo', $recurso->activo ? '1' : '0') == '1')
                                class="mt-0.5 h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500">

                            <div>
                                <span class="block text-sm font-medium text-white">
                                    Recurso activo
                                </span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Los recursos activos estarán disponibles para nuevas configuraciones.
                                    Desactivarlo no elimina las asociaciones existentes.
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-800 bg-slate-900/50 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                    <a href="{{ route('admin.recursos.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-6 rounded-2xl border border-red-500/20 bg-slate-900 p-5 sm:p-6">
            <h2 class="font-semibold text-white">Eliminar recurso</h2>

            @if ($recurso->espacios_count > 0)
                <p class="mt-2 text-sm leading-6 text-slate-400">
                    Este recurso no puede eliminarse porque está asociado a
                    {{ $recurso->espacios_count }}
                    {{ $recurso->espacios_count === 1 ? 'espacio' : 'espacios' }}.
                    Si ya no debe utilizarse en nuevas configuraciones, puedes desactivarlo.
                </p>

                <button
                    type="button"
                    disabled
                    class="mt-4 cursor-not-allowed rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-semibold text-slate-600">
                    Eliminar recurso
                </button>
            @else
                <p class="mt-2 text-sm leading-6 text-slate-400">
                    Puedes eliminar este recurso porque todavía no está asociado a ningún espacio.
                    Esta acción no se puede deshacer.
                </p>

                <form
                    method="POST"
                    action="{{ route('admin.recursos.destroy', $recurso) }}"
                    class="mt-4"
                    onsubmit="return confirm('¿Seguro que deseas eliminar este recurso? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="rounded-xl border border-red-500/40 px-4 py-2.5 text-sm font-semibold text-red-400 transition hover:bg-red-500/10">
                        Eliminar recurso
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<div
    id="datosCategorias"
    data-categorias="{{ $categorias->values()->toJson() }}"
    class="hidden">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const categoria = document.getElementById('categoria');
    const sugerenciasCategoria = document.getElementById('sugerenciasCategoria');
    const datosCategorias = document.getElementById('datosCategorias');

    const categoriasDisponibles = JSON.parse(
        datosCategorias.dataset.categorias
    );

    function normalizarTexto(texto) {
        return texto
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    function ocultarSugerencias() {
        sugerenciasCategoria.innerHTML = '';
        sugerenciasCategoria.classList.add('hidden');
    }

    function mostrarSugerencias() {
        const termino = categoria.value.trim();

        if (termino === '') {
            ocultarSugerencias();
            return;
        }

        const terminoNormalizado = normalizarTexto(termino);

        const coincidencias = categoriasDisponibles
            .filter(function (item) {
                return normalizarTexto(item).includes(terminoNormalizado);
            })
            .slice(0, 6);

        if (coincidencias.length === 0) {
            ocultarSugerencias();
            return;
        }

        sugerenciasCategoria.innerHTML = '';

        coincidencias.forEach(function (item) {
            const boton = document.createElement('button');

            boton.type = 'button';
            boton.className =
                'flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm text-slate-300 transition hover:bg-slate-800 hover:text-white';

            const texto = document.createElement('span');
            texto.textContent = item;

            const indicador = document.createElement('span');
            indicador.className = 'text-xs text-slate-500';
            indicador.textContent = 'Usar';

            boton.appendChild(texto);
            boton.appendChild(indicador);

            boton.addEventListener('click', function () {
                categoria.value = item;
                ocultarSugerencias();
                categoria.focus();
            });

            sugerenciasCategoria.appendChild(boton);
        });

        sugerenciasCategoria.classList.remove('hidden');
    }

    categoria.addEventListener('input', mostrarSugerencias);

    categoria.addEventListener('focus', function () {
        if (categoria.value.trim() !== '') {
            mostrarSugerencias();
        }
    });

    document.addEventListener('click', function (event) {
        if (
            !categoria.contains(event.target) &&
            !sugerenciasCategoria.contains(event.target)
        ) {
            ocultarSugerencias();
        }
    });
});
</script>
@endsection