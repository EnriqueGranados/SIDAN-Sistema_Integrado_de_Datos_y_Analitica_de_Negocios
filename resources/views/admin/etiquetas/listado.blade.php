@extends('layouts.navbars')

@section('title', 'Etiquetas')

@section('content')
<div class="min-h-screen bg-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <h1 class="text-2xl font-bold text-white">Etiquetas</h1>
                <p class="mt-2 text-sm leading-6 text-slate-400">
                    Las etiquetas permiten describir características adicionales de una actividad.
                    A diferencia de las categorías, una actividad puede tener varias etiquetas para
                    facilitar su organización e identificación.
                </p>
            </div>

            <a href="{{ route('admin.etiquetas.create') }}"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4v16m8-8H4" />
                </svg>
                Nueva etiqueta
            </a>
        </div>

    
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-900 p-4 shadow-xl">
            <form id="formFiltros" method="GET" action="{{ route('admin.etiquetas.index') }}">
                <div class="grid gap-4 md:grid-cols-[1fr_220px_auto]">
                    <div>
                        <label for="buscar" class="mb-2 block text-sm font-medium text-slate-300">
                            Buscar etiqueta
                        </label>

                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" />
                                </svg>
                            </div>

                            <input
                                id="buscar"
                                name="buscar"
                                type="search"
                                value="{{ $buscar }}"
                                autocomplete="off"
                                placeholder="Ej. Presencial, principiantes, tecnología..."
                                class="w-full rounded-xl border border-slate-700 bg-slate-950 py-2.5 pl-10 pr-10 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">

                            <div id="spinnerBusqueda" class="absolute inset-y-0 right-0 hidden items-center pr-3">
                                <svg class="h-4 w-4 animate-spin text-emerald-400" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="estado" class="mb-2 block text-sm font-medium text-slate-300">
                            Estado
                        </label>

                        <select
                            id="estado"
                            name="estado"
                            class="w-full rounded-xl border border-slate-700 bg-slate-950 px-3 py-2.5 text-sm text-white outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20">
                            <option value="">Todas</option>
                            <option value="activo" @selected($estado === 'activo')>
                                Activas
                            </option>
                            <option value="inactivo" @selected($estado === 'inactivo')>
                                Inactivas
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button
                            id="btnLimpiar"
                            type="button"
                            class="w-full rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-800 hover:text-white md:w-auto">
                            Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="resultadosEtiquetas">
            @if ($etiquetas->count() > 0)
                <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900 shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800">
                            <thead class="bg-slate-900/80">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Etiqueta
                                    </th>

                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Actividades
                                    </th>

                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Estado
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-800">
                                @foreach ($etiquetas as $etiqueta)
                                    <tr class="transition hover:bg-slate-800/40">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M7 7h.01M3 11l8.586-8.586A2 2 0 0113 2h5a2 2 0 012 2v5a2 2 0 01-.586 1.414L10.828 19a2 2 0 01-2.828 0L3 14a2 2 0 010-3z" />
                                                    </svg>
                                                </div>

                                                <div>
                                                    <p class="font-medium text-white">
                                                        {{ $etiqueta->nombre }}
                                                    </p>
                                                    <p class="mt-0.5 text-xs text-slate-500">
                                                        Etiqueta de actividad
                                                    </p>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-slate-800 px-2.5 py-1 text-xs font-semibold text-slate-300">
                                                {{ $etiqueta->actividades_count }}
                                            </span>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            @if ($etiqueta->activo)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                    Activa
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-700/50 px-2.5 py-1 text-xs font-medium text-slate-400">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                                    Inactiva
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a
                                                    href="{{ route('admin.etiquetas.edit', $etiqueta) }}"
                                                    title="Editar etiqueta"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 text-slate-300 transition hover:border-emerald-500/50 hover:bg-emerald-500/10 hover:text-emerald-400">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.etiquetas.destroy', $etiqueta) }}"
                                                    onsubmit="return confirm('¿Seguro que deseas eliminar esta etiqueta? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        title="{{ $etiqueta->actividades_count > 0 ? 'No puede eliminarse porque está siendo utilizada' : 'Eliminar etiqueta' }}"
                                                        @disabled($etiqueta->actividades_count > 0)
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition
                                                            {{ $etiqueta->actividades_count > 0
                                                                ? 'cursor-not-allowed border-slate-800 text-slate-600'
                                                                : 'border-slate-700 text-slate-300 hover:border-red-500/50 hover:bg-red-500/10 hover:text-red-400' }}">
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

                    @if ($etiquetas->hasPages())
                        <div class="etiqueta-paginacion border-t border-slate-800 px-4 py-4 sm:px-6">
                            {{ $etiquetas->links() }}
                        </div>
                    @endif
                </div>

                <div class="mt-4 text-sm text-slate-500">
                    Mostrando
                    <span class="font-medium text-slate-300">{{ $etiquetas->firstItem() }}</span>
                    a
                    <span class="font-medium text-slate-300">{{ $etiquetas->lastItem() }}</span>
                    de
                    <span class="font-medium text-slate-300">{{ $etiquetas->total() }}</span>
                    etiquetas.
                </div>
            @else
                <div class="rounded-2xl border border-slate-800 bg-slate-900 px-6 py-16 text-center shadow-xl sm:px-10 sm:py-20">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-800 text-slate-400">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M7 7h.01M3 11l8.586-8.586A2 2 0 0113 2h5a2 2 0 012 2v5a2 2 0 01-.586 1.414L10.828 19a2 2 0 01-2.828 0L3 14a2 2 0 010-3z" />
                        </svg>
                    </div>

                    @if ($buscar !== '' || $estado !== '')
                        <h3 class="mt-5 text-base font-semibold text-white">
                            No encontramos etiquetas
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                            No hay etiquetas que coincidan con los criterios seleccionados.
                            Prueba con otro término o limpia los filtros.
                        </p>

                        <button
                            type="button"
                            data-limpiar-filtros
                            class="mt-6 inline-flex items-center justify-center rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                            Limpiar filtros
                        </button>
                    @else
                        <h3 class="mt-5 text-base font-semibold text-white">
                            Aún no hay etiquetas
                        </h3>

                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                            Crea etiquetas para describir características que puedan compartirse
                            entre distintas actividades, como el tema, modalidad, público o nivel.
                        </p>

                        <a href="{{ route('admin.etiquetas.create') }}"
                            class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Crear primera etiqueta
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
    const estado = document.getElementById('estado');
    const btnLimpiar = document.getElementById('btnLimpiar');
    const spinner = document.getElementById('spinnerBusqueda');

    const urlIndex = "{{ route('admin.etiquetas.index') }}";

    let temporizador = null;
    let controlador = null;
    let solicitudActual = 0;

    function construirUrl() {
        const parametros = new URLSearchParams();
        const termino = buscar.value.trim();

        if (termino !== '') {
            parametros.set('buscar', termino);
        }

        if (estado.value !== '') {
            parametros.set('estado', estado.value);
        }

        const query = parametros.toString();

        return query === ''
            ? urlIndex
            : `${urlIndex}?${query}`;
    }

    async function cargarResultados(url, actualizarHistorial = true) {
        const idSolicitud = ++solicitudActual;

        if (controlador) {
            controlador.abort();
        }

        controlador = new AbortController();

        spinner.classList.remove('hidden');
        spinner.classList.add('flex');

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
                throw new Error('No fue posible cargar las etiquetas.');
            }

            const html = await respuesta.text();
            const documento = new DOMParser().parseFromString(html, 'text/html');
            const nuevosResultados = documento.getElementById('resultadosEtiquetas');
            const resultadosActuales = document.getElementById('resultadosEtiquetas');

            if (!nuevosResultados || !resultadosActuales) {
                window.location.href = url;
                return;
            }

            resultadosActuales.innerHTML = nuevosResultados.innerHTML;

            if (actualizarHistorial) {
                window.history.replaceState({}, '', url);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error(error);
                window.location.href = url;
            }
        } finally {
            if (idSolicitud === solicitudActual) {
                spinner.classList.add('hidden');
                spinner.classList.remove('flex');
            }
        }
    }

    function ejecutarLimpieza() {
        clearTimeout(temporizador);

        buscar.value = '';
        estado.value = '';

        cargarResultados(urlIndex);
        buscar.focus();
    }

    buscar.addEventListener('input', function () {
        clearTimeout(temporizador);

        temporizador = setTimeout(function () {
            cargarResultados(construirUrl());
        }, 350);
    });

    estado.addEventListener('change', function () {
        clearTimeout(temporizador);
        cargarResultados(construirUrl());
    });

    btnLimpiar.addEventListener('click', ejecutarLimpieza);

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        clearTimeout(temporizador);
        cargarResultados(construirUrl());
    });

    document.addEventListener('click', function (event) {
        const enlacePaginacion = event.target.closest(
            '#resultadosEtiquetas .etiqueta-paginacion a'
        );

        if (enlacePaginacion) {
            event.preventDefault();
            cargarResultados(enlacePaginacion.href);

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

            return;
        }

        const limpiarFiltros = event.target.closest('[data-limpiar-filtros]');

        if (limpiarFiltros) {
            ejecutarLimpieza();
        }
    });

    window.addEventListener('popstate', function () {
        const parametros = new URLSearchParams(window.location.search);

        buscar.value = parametros.get('buscar') ?? '';
        estado.value = parametros.get('estado') ?? '';

        cargarResultados(window.location.href, false);
    });
});
</script>
@endsection