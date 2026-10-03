@extends('layouts.navbars')

@section('title', 'Espacios')

@section('content')
<div class="min-h-screen bg-slate-950">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">
                    Espacios
                </h1>

                <p class="mt-1 max-w-2xl text-sm text-slate-400">
                    Administra los lugares disponibles para realizar actividades y los espacios que se encuentran dentro de ellos.
                </p>
            </div>

            <a
                href="{{ route('admin.espacios.create') }}"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">

                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>

                Nuevo espacio
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- FILTROS --}}
        <div class="mb-4 rounded-xl border border-slate-800 bg-slate-900 p-4">
            <form
                id="formFiltros"
                method="GET"
                action="{{ route('admin.espacios.index') }}">

                <div class="flex flex-col gap-3 lg:flex-row lg:items-end">

                    <div class="w-full lg:max-w-md">
                        <label
                            for="buscar"
                            class="mb-1.5 block text-xs font-medium text-slate-400">
                            Buscar espacio
                        </label>

                        <input
                            id="buscar"
                            name="buscar"
                            type="search"
                            autocomplete="off"
                            value="{{ $buscar }}"
                            placeholder="Escribe un nombre, dirección o lugar..."
                            class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                    </div>

                    <div class="grid flex-1 grid-cols-1 gap-3 sm:grid-cols-2 lg:max-w-md">

                        <div>
                            <label
                                for="uso"
                                class="mb-1.5 block text-xs font-medium text-slate-400">
                                Se utiliza para
                            </label>

                            <select
                                id="uso"
                                name="uso"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">

                                <option value="">
                                    Cualquier uso
                                </option>

                                <option
                                    value="directo"
                                    @selected($uso === 'directo')>
                                    Realizar actividades
                                </option>

                                <option
                                    value="organizacion"
                                    @selected($uso === 'organizacion')>
                                    Organizar otros espacios
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                for="estado"
                                class="mb-1.5 block text-xs font-medium text-slate-400">
                                Disponibilidad
                            </label>

                            <select
                                id="estado"
                                name="estado"
                                class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">

                                <option value="">
                                    Todos
                                </option>

                                <option
                                    value="activo"
                                    @selected($estado === 'activo')>
                                    Disponibles
                                </option>

                                <option
                                    value="inactivo"
                                    @selected($estado === 'inactivo')>
                                    No disponibles
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-end">
                        <button
                            id="btnLimpiar"
                            type="button"
                            class="inline-flex h-[38px] items-center justify-center rounded-lg border border-slate-700 px-3 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-white">
                            Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- RESULTADOS --}}
        <div id="resultadosEspacios">
            @if ($espacios->count() > 0)

                <div class="overflow-hidden rounded-xl border border-slate-800 bg-slate-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800">
                            <thead class="bg-slate-950/40">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Espacio
                                    </th>

                                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Capacidad
                                    </th>

                                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Recursos
                                    </th>

                                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Uso
                                    </th>

                                    <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Estado
                                    </th>

                                    <th class="w-24 px-4 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-800">
                                @foreach ($espacios as $espacio)
                                    <tr class="transition hover:bg-slate-800/25">

                                        {{-- ESPACIO --}}
                                        <td class="px-4 py-3">
                                            <div class="min-w-[230px]">
                                                <div class="flex items-center gap-2">
                                                    <span class="truncate text-sm font-semibold text-white">
                                                        {{ $espacio->nombre }}
                                                    </span>

                                                    @if ($espacio->espacios_internos_count > 0)
                                                        <span
                                                            title="{{ $espacio->espacios_internos_count }} espacios registrados dentro"
                                                            class="inline-flex shrink-0 items-center rounded-md bg-slate-800 px-1.5 py-0.5 text-[10px] font-medium text-slate-400">
                                                            {{ $espacio->espacios_internos_count }}
                                                            {{ $espacio->espacios_internos_count === 1 ? 'interno' : 'internos' }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if ($espacio->contenedor)
                                                    <div class="mt-1 flex min-w-0 items-center gap-1 text-xs text-slate-500">
                                                        <span class="shrink-0">
                                                            Dentro de
                                                        </span>

                                                        <span class="truncate font-medium text-slate-400">
                                                            {{ $espacio->contenedor->nombre }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <p class="mt-1 text-xs text-slate-600">
                                                        Lugar independiente
                                                    </p>
                                                @endif

                                                @if ($espacio->direccion)
                                                    <p
                                                        title="{{ $espacio->direccion }}"
                                                        class="mt-1 max-w-sm truncate text-[11px] text-slate-600">
                                                        {{ $espacio->direccion }}
                                                    </p>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- CAPACIDAD --}}
                                        <td class="whitespace-nowrap px-4 py-3">
                                            @if ($espacio->capacidad)
                                                <span class="text-sm text-slate-300">
                                                    {{ number_format($espacio->capacidad) }}
                                                </span>

                                                <span class="ml-1 text-xs text-slate-600">
                                                    personas
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-600">
                                                    No definida
                                                </span>
                                            @endif
                                        </td>

                                        {{-- RECURSOS --}}
                                        <td class="whitespace-nowrap px-4 py-3">
                                            @if ($espacio->recursos_count > 0)
                                                <span class="text-sm font-medium text-slate-300">
                                                    {{ $espacio->recursos_count }}
                                                </span>

                                                <span class="ml-1 text-xs text-slate-600">
                                                    {{ $espacio->recursos_count === 1 ? 'recurso' : 'recursos' }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-600">
                                                    Sin recursos
                                                </span>
                                            @endif
                                        </td>

                                        {{-- USO --}}
                                        <td class="px-4 py-3">
                                            @if ($espacio->permite_actividades)
                                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-xs text-sky-400">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span>
                                                    Actividades
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-xs text-violet-400">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-violet-400"></span>
                                                    Agrupa espacios
                                                </span>
                                            @endif
                                        </td>

                                        {{-- ESTADO --}}
                                        <td class="px-4 py-3">
                                            @if ($espacio->activo)
                                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-emerald-400">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                    Disponible
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 whitespace-nowrap text-xs font-medium text-slate-500">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-600"></span>
                                                    No disponible
                                                </span>
                                            @endif
                                        </td>

                                        {{-- ACCIONES --}}
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-1">

                                                <a
                                                    href="{{ route('admin.espacios.edit', $espacio) }}"
                                                    title="Editar"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-emerald-500/10 hover:text-emerald-400">

                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.espacios.destroy', $espacio) }}"
                                                    onsubmit="return confirm('¿Seguro que deseas eliminar este espacio? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        @disabled($espacio->espacios_internos_count > 0)
                                                        title="{{ $espacio->espacios_internos_count > 0
                                                            ? 'Primero debes reubicar o eliminar los espacios que se encuentran dentro'
                                                            : 'Eliminar' }}"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition
                                                            {{ $espacio->espacios_internos_count > 0
                                                                ? 'cursor-not-allowed text-slate-700'
                                                                : 'text-slate-400 hover:bg-red-500/10 hover:text-red-400' }}">

                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($espacios->hasPages())
                        <div class="espacios-paginacion border-t border-slate-800 px-4 py-3">
                            {{ $espacios->links() }}
                        </div>
                    @endif
                </div>

                <p class="mt-3 text-xs text-slate-600">
                    Mostrando
                    <span class="font-medium text-slate-400">
                        {{ $espacios->firstItem() }}
                    </span>
                    a
                    <span class="font-medium text-slate-400">
                        {{ $espacios->lastItem() }}
                    </span>
                    de
                    <span class="font-medium text-slate-400">
                        {{ $espacios->total() }}
                    </span>
                    espacios.
                </p>

            @else

                <div class="rounded-xl border border-slate-800 bg-slate-900 px-5 py-10 text-center">
                    @if ($buscar !== '' || $estado !== '' || $uso !== '')
                        <h3 class="text-sm font-semibold text-white">
                            No encontramos espacios
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                            Prueba con otro término o cambia los filtros seleccionados.
                        </p>

                        <button
                            type="button"
                            data-limpiar-filtros
                            class="mt-4 rounded-lg border border-slate-700 px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white">
                            Limpiar filtros
                        </button>
                    @else
                        <h3 class="text-sm font-semibold text-white">
                            Aún no hay espacios registrados
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                            Comienza registrando una sede, edificio, facultad, salón u otro lugar que utilice la organización.
                        </p>

                        <a
                            href="{{ route('admin.espacios.create') }}"
                            class="mt-4 inline-flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-400">

                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>

                            Crear primer espacio
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formFiltros');
    const buscar = document.getElementById('buscar');
    const uso = document.getElementById('uso');
    const estado = document.getElementById('estado');
    const btnLimpiar = document.getElementById('btnLimpiar');

    const urlIndex = "{{ route('admin.espacios.index') }}";

    let temporizador = null;
    let controlador = null;
    let numeroSolicitud = 0;

    function construirUrl() {
        const parametros = new URLSearchParams();

        const termino = buscar.value.trim();

        if (termino !== '') {
            parametros.set('buscar', termino);
        }

        if (uso.value !== '') {
            parametros.set('uso', uso.value);
        }

        if (estado.value !== '') {
            parametros.set('estado', estado.value);
        }

        const query = parametros.toString();

        return query === ''
            ? urlIndex
            : `${urlIndex}?${query}`;
    }

    async function cargarResultados(
        url,
        actualizarHistorial = true
    ) {
        numeroSolicitud++;

        const solicitud = numeroSolicitud;

        if (controlador) {
            controlador.abort();
        }

        controlador = new AbortController();

        buscar.classList.add(
            'opacity-70'
        );

        try {
            const respuesta = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                signal: controlador.signal
            });

            if (!respuesta.ok) {
                throw new Error(
                    'No fue posible cargar los espacios.'
                );
            }

            const html = await respuesta.text();

            const documento = new DOMParser().parseFromString(
                html,
                'text/html'
            );

            const nuevosResultados =
                documento.getElementById(
                    'resultadosEspacios'
                );

            const resultadosActuales =
                document.getElementById(
                    'resultadosEspacios'
                );

            if (
                !nuevosResultados ||
                !resultadosActuales
            ) {
                window.location.href = url;
                return;
            }

            resultadosActuales.innerHTML =
                nuevosResultados.innerHTML;

            if (actualizarHistorial) {
                window.history.replaceState(
                    {},
                    '',
                    url
                );
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
            }
        } finally {
            if (solicitud === numeroSolicitud) {
                buscar.classList.remove(
                    'opacity-70'
                );
            }
        }
    }

    function buscarAutomaticamente() {
        clearTimeout(temporizador);

        temporizador = setTimeout(
            function () {
                cargarResultados(
                    construirUrl()
                );
            },
            250
        );
    }

    function limpiarFiltros() {
        clearTimeout(temporizador);

        buscar.value = '';
        uso.value = '';
        estado.value = '';

        cargarResultados(
            urlIndex
        );

        buscar.focus();
    }

    buscar.addEventListener(
        'input',
        buscarAutomaticamente
    );

    uso.addEventListener(
        'change',
        function () {
            clearTimeout(temporizador);

            cargarResultados(
                construirUrl()
            );
        }
    );

    estado.addEventListener(
        'change',
        function () {
            clearTimeout(temporizador);

            cargarResultados(
                construirUrl()
            );
        }
    );

    btnLimpiar.addEventListener(
        'click',
        limpiarFiltros
    );

    form.addEventListener(
        'submit',
        function (event) {
            event.preventDefault();

            clearTimeout(temporizador);

            cargarResultados(
                construirUrl()
            );
        }
    );

    document.addEventListener(
        'click',
        function (event) {
            const enlacePaginacion =
                event.target.closest(
                    '#resultadosEspacios .espacios-paginacion a'
                );

            if (enlacePaginacion) {
                event.preventDefault();

                cargarResultados(
                    enlacePaginacion.href
                );

                return;
            }

            const limpiar =
                event.target.closest(
                    '[data-limpiar-filtros]'
                );

            if (limpiar) {
                limpiarFiltros();
            }
        }
    );

    window.addEventListener(
        'popstate',
        function () {
            const parametros =
                new URLSearchParams(
                    window.location.search
                );

            buscar.value =
                parametros.get('buscar') ?? '';

            uso.value =
                parametros.get('uso') ?? '';

            estado.value =
                parametros.get('estado') ?? '';

            cargarResultados(
                window.location.href,
                false
            );
        }
    );
});
</script>
@endsection