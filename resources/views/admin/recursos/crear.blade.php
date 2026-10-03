@extends('layouts.navbars')

@section('title', 'Nuevo recurso')

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

            <h1 class="text-2xl font-bold text-white">Nuevo recurso</h1>
            <p class="mt-2 text-sm leading-6 text-slate-400">
                Registra un equipo, mobiliario, infraestructura u otro elemento que pueda utilizarse
                en los espacios y actividades.
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

        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-800 bg-slate-900">
            <button
                id="btnAyuda"
                type="button"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-slate-800/40"
                aria-expanded="false"
                aria-controls="contenidoAyuda">

                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-white">
                            ¿Qué debo registrar como recurso?
                        </p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            Consulta algunos ejemplos y cómo clasificarlos.
                        </p>
                    </div>
                </div>

                <svg id="iconoAyuda"
                    class="h-5 w-5 shrink-0 text-slate-500 transition-transform"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div id="contenidoAyuda" class="hidden border-t border-slate-800 px-5 py-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl bg-slate-950/70 p-4">
                        <p class="text-sm font-semibold text-slate-200">
                            Recursos del espacio
                        </p>
                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Son elementos que normalmente forman parte de un lugar, como
                            sillas, tomacorrientes, pantalla de proyección o sistema de sonido.
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-950/70 p-4">
                        <p class="text-sm font-semibold text-slate-200">
                            Recursos móviles
                        </p>
                        <p class="mt-2 text-sm leading-6 text-slate-400">
                            Pueden trasladarse y utilizarse en diferentes lugares, como
                            proyectores portátiles, micrófonos, canopies o roll-ups.
                        </p>
                    </div>
                </div>

                <p class="mt-4 text-xs leading-5 text-slate-500">
                    Aquí solo estás creando el recurso. Más adelante podrás indicar qué recursos
                    están disponibles en cada espacio.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.recursos.store') }}">
            @csrf

            <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
                <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                    <h2 class="font-semibold text-white">Información del recurso</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Ingresa la información que permitirá identificarlo y utilizarlo posteriormente.
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
                            value="{{ old('nombre') }}"
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
                                    : 'border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20' }}">{{ old('descripcion') }}</textarea>

                        @error('descripcion')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="categoria" class="mb-2 block text-sm font-medium text-slate-300">
                            Categoría
                        </label>

                        <div class="relative" id="contenedorCategoria">
                            <input
                                id="categoria"
                                name="categoria"
                                type="text"
                                maxlength="80"
                                value="{{ old('categoria') }}"
                                placeholder="Ej. Audiovisual"
                                autocomplete="off"
                                class="w-full rounded-xl border bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition placeholder:text-slate-500 focus:ring-2
                                    {{ $errors->has('categoria')
                                        ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                        : 'border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20' }}">

                            <div
                                id="sugerenciasCategoria"
                                class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-60 overflow-y-auto rounded-xl border border-slate-700 bg-slate-900 p-1 shadow-2xl">
                            </div>
                        </div>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            Agrupa recursos similares. Por ejemplo: Audiovisual, Audio, Mobiliario,
                            Tecnología o Conectividad. Empieza a escribir para ver opciones.
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
                                <option value="unidad" @selected(old('unidad_medida', 'unidad') === 'unidad')>
                                    Unidades
                                </option>

                                <option value="par" @selected(old('unidad_medida') === 'par')>
                                    Pares
                                </option>

                                <option value="juego" @selected(old('unidad_medida') === 'juego')>
                                    Juegos / conjuntos
                                </option>

                                <option value="metro" @selected(old('unidad_medida') === 'metro')>
                                    Metros
                                </option>
                            </select>

                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                Indica cómo se expresará la cantidad disponible. Para proyectores,
                                micrófonos, sillas, mesas y similares utiliza “Unidades”.
                            </p>

                            @error('unidad_medida')
                                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                    <div class="border-t border-slate-800 pt-6">
                        <p class="text-sm font-medium text-slate-300">
                            ¿Este recurso puede trasladarse entre diferentes espacios?
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Esto permitirá distinguir los recursos que pertenecen normalmente a un
                            espacio de aquellos que pueden utilizarse en distintos lugares.
                        </p>

                        <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-700 bg-slate-950 p-4 transition hover:border-slate-600">
                            <input
                                type="checkbox"
                                name="es_movil"
                                value="1"
                                @checked(old('es_movil', '1') == '1')
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
                                @checked(old('activo', '1') == '1')
                                class="mt-0.5 h-4 w-4 rounded border-slate-600 bg-slate-900 text-emerald-500 focus:ring-emerald-500">

                            <div>
                                <span class="block text-sm font-medium text-white">
                                    Recurso activo
                                </span>
                                <span class="mt-1 block text-xs leading-5 text-slate-500">
                                    Los recursos activos estarán disponibles para nuevas configuraciones.
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
                        Guardar recurso
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script id="categorias-data" type="application/json">
{!! json_encode($categorias->values()->all(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const boton = document.getElementById('btnAyuda');
    const contenido = document.getElementById('contenidoAyuda');
    const icono = document.getElementById('iconoAyuda');

    const categoria = document.getElementById('categoria');
    const contenedorCategoria = document.getElementById('contenedorCategoria');
    const sugerenciasCategoria = document.getElementById('sugerenciasCategoria');
    const categoriasData = document.getElementById('categorias-data');

    let categoriasDisponibles = [];

    try {
        categoriasDisponibles = JSON.parse(categoriasData.textContent);
    } catch (error) {
        console.error('No se pudieron cargar las categorías.', error);
    }

    if (boton && contenido && icono) {
        boton.addEventListener('click', function () {
            const abierto = !contenido.classList.contains('hidden');

            contenido.classList.toggle('hidden');
            icono.classList.toggle('rotate-180');

            boton.setAttribute(
                'aria-expanded',
                abierto ? 'false' : 'true'
            );
        });
    }

    function normalizar(texto) {
        return String(texto)
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function cerrarSugerencias() {
        sugerenciasCategoria.innerHTML = '';
        sugerenciasCategoria.classList.add('hidden');
    }

    function seleccionarCategoria(nombre) {
        categoria.value = nombre;
        cerrarSugerencias();
        categoria.focus();
    }

    function buscarCategorias() {
        const valor = categoria.value.trim();

        sugerenciasCategoria.innerHTML = '';

        if (valor === '') {
            cerrarSugerencias();
            return;
        }

        const busqueda = normalizar(valor);

        const coincidencias = categoriasDisponibles
            .filter(function (nombre) {
                return normalizar(nombre).includes(busqueda);
            })
            .slice(0, 8);

        if (coincidencias.length === 0) {
            cerrarSugerencias();
            return;
        }

        coincidencias.forEach(function (nombre) {
            const botonOpcion = document.createElement('button');

            botonOpcion.type = 'button';

            botonOpcion.className =
                'flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-left transition hover:bg-slate-800';

            const bloqueTexto = document.createElement('div');

            const nombreCategoria = document.createElement('p');
            nombreCategoria.className = 'text-sm font-medium text-slate-200';
            nombreCategoria.textContent = nombre;

            const descripcion = document.createElement('p');
            descripcion.className = 'mt-0.5 text-xs text-slate-500';
            descripcion.textContent = 'Categoría existente';

            bloqueTexto.appendChild(nombreCategoria);
            bloqueTexto.appendChild(descripcion);

            const usar = document.createElement('span');
            usar.className =
                'shrink-0 rounded-lg bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-400';
            usar.textContent = 'Usar';

            botonOpcion.appendChild(bloqueTexto);
            botonOpcion.appendChild(usar);

            botonOpcion.addEventListener('mousedown', function (event) {
                event.preventDefault();
                seleccionarCategoria(nombre);
            });

            sugerenciasCategoria.appendChild(botonOpcion);
        });

        sugerenciasCategoria.classList.remove('hidden');
    }

    categoria.addEventListener('input', buscarCategorias);

    categoria.addEventListener('focus', function () {
        if (categoria.value.trim() !== '') {
            buscarCategorias();
        }
    });

    document.addEventListener('mousedown', function (event) {
        if (!contenedorCategoria.contains(event.target)) {
            cerrarSugerencias();
        }
    });
});
</script>
@endsection