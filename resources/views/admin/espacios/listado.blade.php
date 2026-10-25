@extends('layouts.navbars')

@section('title', 'Espacios')

@section('content')
<div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                    Administración
                </p>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 dark:text-white">
                    Espacios
                </h1>

                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Administra los lugares disponibles para realizar actividades y los espacios que se encuentran dentro de ellos.
                </p>
            </div>

            <a
                href="{{ route('admin.espacios.create') }}"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sidan-600"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>

                Nuevo espacio
            </a>
        </div>

        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
            <form
                id="formFiltros"
                method="GET"
                action="{{ route('admin.espacios.index') }}"
            >
                <div class="grid gap-4 lg:grid-cols-[minmax(280px,1fr)_220px_200px_auto]">

                    <div>
                        <label
                            for="buscar"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Buscar espacio
                        </label>

                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg
                                    class="h-5 w-5 text-slate-400 dark:text-slate-500"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z"
                                    />
                                </svg>
                            </div>

                            <input
                                id="buscar"
                                name="buscar"
                                type="search"
                                value="{{ $buscar }}"
                                autocomplete="off"
                                placeholder="Ej. Auditorio, salón, edificio..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:bg-white focus:ring-2 focus:ring-sidan-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-white/10"
                            >

                            <div
                                id="spinnerBusqueda"
                                class="absolute inset-y-0 right-0 hidden items-center pr-3"
                            >
                                <svg
                                    class="h-4 w-4 animate-spin text-sidan-500 dark:text-sidan-400"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>

                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label
                            for="uso"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Se utiliza para
                        </label>

                        <select
                            id="uso"
                            name="uso"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:bg-white focus:ring-2 focus:ring-sidan-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:bg-white/10 dark:[color-scheme:dark]"
                        >
                            <option value="">
                                Cualquier uso
                            </option>

                            <option
                                value="directo"
                                @selected($uso === 'directo')
                            >
                                Realizar actividades
                            </option>

                            <option
                                value="organizacion"
                                @selected($uso === 'organizacion')
                            >
                                Organizar otros espacios
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            for="estado"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300"
                        >
                            Disponibilidad
                        </label>

                        <select
                            id="estado"
                            name="estado"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:bg-white focus:ring-2 focus:ring-sidan-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:bg-white/10 dark:[color-scheme:dark]"
                        >
                            <option value="">
                                Todos
                            </option>

                            <option
                                value="activo"
                                @selected($estado === 'activo')
                            >
                                Disponibles
                            </option>

                            <option
                                value="inactivo"
                                @selected($estado === 'inactivo')
                            >
                                No disponibles
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button
                            id="btnLimpiar"
                            type="button"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 lg:w-auto dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                        >
                            Limpiar
                        </button>
                    </div>

                </div>
            </form>
        </div>

        <div id="resultadosEspacios">
            @if ($espacios->count() > 0)

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5">
                            <thead class="bg-slate-50 dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Espacio
                                    </th>

                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Capacidad
                                    </th>

                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Recursos
                                    </th>

                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Uso
                                    </th>

                                    <th class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Estado
                                    </th>

                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-200 dark:divide-white/5">
                                @foreach ($espacios as $espacio)

                                    <tr class="transition hover:bg-slate-50 dark:hover:bg-white/[0.02]">

                                        <td class="px-6 py-4">
                                            <div class="flex min-w-[270px] items-start gap-3">

                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sidan-50 text-sidan-600 dark:bg-sidan-500/10 dark:text-sidan-400">
                                                    <svg
                                                        class="h-5 w-5"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16M9 7h2m-2 4h2m-2 4h2m6 6v-8h2a2 2 0 012 2v6"
                                                        />
                                                    </svg>
                                                </div>

                                                <div class="min-w-0">

                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <p class="font-medium text-slate-900 dark:text-white">
                                                            {{ $espacio->nombre }}
                                                        </p>

                                                        @if ($espacio->espacios_internos_count > 0)
                                                            <span
                                                                title="{{ $espacio->espacios_internos_count }} espacios registrados dentro"
                                                                class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                                            >
                                                                {{ $espacio->espacios_internos_count }}
                                                                {{ $espacio->espacios_internos_count === 1 ? 'interno' : 'internos' }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if ($espacio->contenedor)
                                                        <div class="mt-1 flex min-w-0 items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                                            <svg
                                                                class="h-3.5 w-3.5 shrink-0"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                viewBox="0 0 24 24"
                                                            >
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 18l6-6-6-6"
                                                                />
                                                            </svg>

                                                            <span>
                                                                Dentro de
                                                            </span>

                                                            <span class="truncate font-medium text-slate-700 dark:text-slate-300">
                                                                {{ $espacio->contenedor->nombre }}
                                                            </span>
                                                        </div>
                                                    @else
                                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                            Lugar independiente
                                                        </p>
                                                    @endif

                                                    @if ($espacio->direccion)
                                                        <div
                                                            title="{{ $espacio->direccion }}"
                                                            class="mt-1.5 flex max-w-sm items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500"
                                                        >
                                                            <svg
                                                                class="h-3.5 w-3.5 shrink-0"
                                                                fill="none"
                                                                stroke="currentColor"
                                                                viewBox="0 0 24 24"
                                                            >
                                                                <path
                                                                    stroke-linecap="round"
                                                                    stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 21s6-4.35 6-10A6 6 0 106 11c0 5.65 6 10 6 10Z"
                                                                />
                                                                <circle cx="12" cy="11" r="2" stroke-width="2" />
                                                            </svg>

                                                            <span class="truncate">
                                                                {{ $espacio->direccion }}
                                                            </span>
                                                        </div>
                                                    @endif

                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            @if ($espacio->capacidad)

                                                <div class="inline-flex items-baseline gap-1">
                                                    <span class="font-semibold text-slate-900 dark:text-white">
                                                        {{ number_format($espacio->capacidad) }}
                                                    </span>

                                                    <span class="text-xs text-slate-500">
                                                        personas
                                                    </span>
                                                </div>

                                            @else

                                                <span class="text-sm text-slate-500">
                                                    No definida
                                                </span>

                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            @if ($espacio->recursos_count > 0)

                                                <span class="inline-flex min-w-8 items-center justify-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                    {{ $espacio->recursos_count }}
                                                </span>

                                            @else

                                                <span class="text-sm text-slate-500">
                                                    Sin recursos
                                                </span>

                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            @if ($espacio->permite_actividades)

                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-400">

                                                    <svg
                                                        class="h-3.5 w-3.5"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v14H4V6a1 1 0 011-1Z"
                                                        />
                                                    </svg>

                                                    Actividades
                                                </span>

                                            @else

                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-400">

                                                    <svg
                                                        class="h-3.5 w-3.5"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 6h6v6H4V6Zm10 0h6v6h-6V6ZM9 12v3m6-3v3M6 18h12"
                                                        />
                                                    </svg>

                                                    Agrupa espacios
                                                </span>

                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-center">
                                            @if ($espacio->activo)

                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-sidan-200 bg-sidan-50 px-2.5 py-1 text-xs font-medium text-sidan-600 dark:border-sidan-500/20 dark:bg-sidan-500/10 dark:text-sidan-400">

                                                    <span class="h-1.5 w-1.5 rounded-full bg-sidan-400"></span>

                                                    Disponible
                                                </span>

                                            @else

                                                <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:border-white/5 dark:bg-slate-700/50 dark:text-slate-400">

                                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>

                                                    No disponible
                                                </span>

                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">

                                                <a
                                                    href="{{ route('admin.espacios.edit', $espacio) }}"
                                                    title="Editar espacio"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-sidan-300 hover:bg-sidan-50 hover:text-sidan-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-sidan-500/50 dark:hover:bg-sidan-500/10 dark:hover:text-sidan-400"
                                                >
                                                    <svg
                                                        class="h-4 w-4"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                        />
                                                    </svg>
                                                </a>

                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.espacios.destroy', $espacio) }}"
                                                    class="form-eliminar-espacio"
                                                    data-nombre="{{ $espacio->nombre }}"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        @disabled($espacio->espacios_internos_count > 0)
                                                        title="{{ $espacio->espacios_internos_count > 0
                                                            ? 'Primero debes reubicar o eliminar los espacios que se encuentran dentro'
                                                            : 'Eliminar espacio' }}"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition
                                                        {{ $espacio->espacios_internos_count > 0
                                                            ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400 dark:border-white/5 dark:bg-white/[0.02] dark:text-slate-600'
                                                            : 'border-slate-200 bg-white text-slate-600 hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-red-500/50 dark:hover:bg-red-500/10 dark:hover:text-red-400' }}"
                                                    >
                                                        <svg
                                                            class="h-4 w-4"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            viewBox="0 0 24 24"
                                                        >
                                                            <path
                                                                stroke-linecap="round"
                                                                stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"
                                                            />
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
                        <div class="espacios-paginacion border-t border-slate-200 px-4 py-4 sm:px-6 dark:border-white/5">
                            {{ $espacios->links() }}
                        </div>
                    @endif

                </div>

                <div class="mt-4 text-sm text-slate-500">
                    Mostrando

                    <span class="font-medium text-slate-700 dark:text-slate-300">
                        {{ $espacios->firstItem() }}
                    </span>

                    a

                    <span class="font-medium text-slate-700 dark:text-slate-300">
                        {{ $espacios->lastItem() }}
                    </span>

                    de

                    <span class="font-medium text-slate-700 dark:text-slate-300">
                        {{ $espacios->total() }}
                    </span>

                    espacios.
                </div>

            @else

                <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm transition-colors sm:px-10 sm:py-20 dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">

                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <svg
                            class="h-8 w-8"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.7"
                                d="M3 21h18M5 21V5a2 2 0 012-2h6a2 2 0 012 2v16M9 7h2m-2 4h2m-2 4h2m6 6v-8h2a2 2 0 012 2v6"
                            />
                        </svg>
                    </div>

                    @if ($buscar !== '' || $estado !== '' || $uso !== '')

                        <h3 class="mt-5 text-base font-semibold text-slate-900 dark:text-white">
                            No encontramos espacios
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                            No hay espacios que coincidan con los criterios seleccionados.
                            Prueba con otros filtros o limpia la búsqueda.
                        </p>

                        <button
                            type="button"
                            data-limpiar-filtros
                            class="mt-6 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                        >
                            Limpiar filtros
                        </button>

                    @else

                        <h3 class="mt-5 text-base font-semibold text-slate-900 dark:text-white">
                            Aún no hay espacios registrados
                        </h3>

                        <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Comienza registrando una sede, edificio, facultad, salón u otro lugar que utilice la organización.
                        </p>

                        <a
                            href="{{ route('admin.espacios.create') }}"
                            class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sidan-600"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 4v16m8-8H4"
                                />
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
    const spinnerBusqueda = document.getElementById('spinnerBusqueda');
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

    async function cargarResultados(url, actualizarHistorial = true) {
        numeroSolicitud++;
        const solicitud = numeroSolicitud;

        if (controlador) {
            controlador.abort();
        }

        controlador = new AbortController();

        buscar.classList.add('opacity-70');
        spinnerBusqueda.classList.remove('hidden');
        spinnerBusqueda.classList.add('flex');

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
                throw new Error('No fue posible cargar los espacios.');
            }

            const html = await respuesta.text();

            const documento = new DOMParser().parseFromString(
                html,
                'text/html'
            );

            const nuevosResultados = documento.getElementById(
                'resultadosEspacios'
            );

            const resultadosActuales = document.getElementById(
                'resultadosEspacios'
            );

            if (!nuevosResultados || !resultadosActuales) {
                window.location.href = url;
                return;
            }

            resultadosActuales.innerHTML = nuevosResultados.innerHTML;

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
                buscar.classList.remove('opacity-70');

                spinnerBusqueda.classList.add('hidden');
                spinnerBusqueda.classList.remove('flex');
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
            const enlacePaginacion = event.target.closest(
                '#resultadosEspacios .espacios-paginacion a'
            );

            if (enlacePaginacion) {
                event.preventDefault();

                cargarResultados(
                    enlacePaginacion.href
                );

                return;
            }

            const limpiar = event.target.closest(
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
            const parametros = new URLSearchParams(
                window.location.search
            );

            buscar.value = parametros.get('buscar') ?? '';
            uso.value = parametros.get('uso') ?? '';
            estado.value = parametros.get('estado') ?? '';

            cargarResultados(
                window.location.href,
                false
            );
        }
    );
});
</script>

<script>
function showAppNotification(type, title, message) {
            // Centralizar avisos sin alterar las acciones existentes del módulo.
            if (window.SIDANToast && typeof window.SIDANToast.show === 'function') {
                return window.SIDANToast.show(message, type, title);
            }

    const previous = document.getElementById('app-notification');

    if (previous) {
        previous.remove();
    }

    const styles = {
        success: {
            iconColor: 'bg-sidan-50 text-sidan-600 dark:bg-sidan-500/10 dark:text-sidan-400',
            borderColor: 'border-sidan-200 dark:border-sidan-500/20',
            progressColor: 'bg-sidan-500',
            progressBackground: 'bg-sidan-100 dark:bg-sidan-950/50',
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

            <button
                type="button"
                class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white"
                aria-label="Cerrar notificación"
            >
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

        notification.classList.remove(
            'opacity-100',
            'translate-y-0'
        );

        notification.classList.add(
            'opacity-0',
            'translate-y-6'
        );

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    };

    closeButton.addEventListener(
        'click',
        closeNotification
    );

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            notification.classList.remove(
                'opacity-0',
                'translate-y-6'
            );

            notification.classList.add(
                'opacity-100',
                'translate-y-0'
            );

            progress.style.transition =
                'transform 5s linear';

            progress.style.transform =
                'scaleX(0)';
        });
    });

    timeout = setTimeout(
        closeNotification,
        5000
    );
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

        modal.setAttribute(
            'role',
            'dialog'
        );

        modal.setAttribute(
            'aria-modal',
            'true'
        );

        modal.setAttribute(
            'tabindex',
            '-1'
        );

        modal.className =
            'w-full max-w-md translate-y-4 scale-95 rounded-2xl border border-slate-200 bg-white p-6 opacity-0 shadow-2xl outline-none transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]';

        modal.innerHTML = `
            <div class="flex items-start gap-4">

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3"
                        ></path>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">

                    <h3 class="text-base font-black text-slate-900 dark:text-white">
                        Eliminar espacio
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        ¿Deseas eliminar
                        <span class="space-name font-semibold text-slate-900 dark:text-white"></span>?
                        Esta acción no se puede deshacer.
                    </p>

                </div>

            </div>

            <div class="mt-6 flex justify-end gap-3">

                <button
                    type="button"
                    class="cancel-delete inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="confirm-delete inline-flex h-10 items-center justify-center rounded-xl bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-500"
                >
                    Eliminar
                </button>

            </div>
        `;

        modal.querySelector(
            '.space-name'
        ).textContent = spaceName || 'este espacio';

        overlay.appendChild(modal);
        document.body.appendChild(overlay);

        const cancelButton = modal.querySelector(
            '.cancel-delete'
        );

        const confirmButton = modal.querySelector(
            '.confirm-delete'
        );

        let resolved = false;

        const close = result => {
            if (resolved) {
                return;
            }

            resolved = true;

            cancelButton.disabled = true;
            confirmButton.disabled = true;

            document.removeEventListener(
                'keydown',
                handleKeydown,
                true
            );

            overlay.classList.remove(
                'opacity-100'
            );

            modal.classList.remove(
                'translate-y-0',
                'scale-100',
                'opacity-100'
            );

            modal.classList.add(
                'translate-y-4',
                'scale-95',
                'opacity-0'
            );

            setTimeout(() => {
                overlay.remove();
                resolve(result);
            }, 200);
        };

        const handleKeydown = event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                event.stopPropagation();

                if (event.repeat || resolved) {
                    return;
                }

                close(true);
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();

                if (resolved) {
                    return;
                }

                close(false);
            }
        };

        cancelButton.addEventListener(
            'click',
            () => close(false)
        );

        confirmButton.addEventListener(
            'click',
            () => close(true)
        );

        overlay.addEventListener(
            'click',
            event => {
                if (event.target === overlay) {
                    close(false);
                }
            }
        );

        document.addEventListener(
            'keydown',
            handleKeydown,
            true
        );

        requestAnimationFrame(() => {
            overlay.classList.add(
                'opacity-100'
            );

            modal.classList.remove(
                'translate-y-4',
                'scale-95',
                'opacity-0'
            );

            modal.classList.add(
                'translate-y-0',
                'scale-100',
                'opacity-100'
            );

            modal.focus();
        });
    });
}

document.addEventListener(
    'DOMContentLoaded',
    function () {
        @if (session('success'))
            showAppNotification(
                'success',
                'Operación realizada correctamente',
                @json(session('success'))
            );
        @endif

        @if (session('error'))
            showAppNotification(
                'error',
                'No se pudo completar la acción',
                @json(session('error'))
            );
        @endif
    }
);

document.addEventListener(
    'submit',
    async function (event) {
        const deleteForm = event.target.closest(
            '.form-eliminar-espacio'
        );

        if (!deleteForm) {
            return;
        }

        event.preventDefault();

        const confirmed = await showSpaceDeleteConfirm(
            deleteForm.dataset.nombre
        );

        if (!confirmed) {
            return;
        }

        deleteForm.submit();
    }
);
</script>
@endsection