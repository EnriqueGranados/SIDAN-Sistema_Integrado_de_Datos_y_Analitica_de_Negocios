@extends('layouts.navbars')
@section('title', 'Espacios')
@section('content')
<style>
html:not(.dark) .espacios-theme .bg-slate-900 { background-color: #ffffff !important; }
html:not(.dark) .espacios-theme .bg-slate-950,
html:not(.dark) .espacios-theme [class*="bg-slate-950/"] { background-color: #f8fafc !important; }
html:not(.dark) .espacios-theme .bg-slate-800,
html:not(.dark) .espacios-theme [class*="bg-slate-800/"] { background-color: #f1f5f9 !important; }
html:not(.dark) .espacios-theme .bg-slate-700,
html:not(.dark) .espacios-theme [class*="bg-slate-700/"] { background-color: #e2e8f0 !important; }
html:not(.dark) .espacios-theme .border-slate-800 { border-color: #e2e8f0 !important; }
html:not(.dark) .espacios-theme .border-slate-700,
html:not(.dark) .espacios-theme .border-slate-600 { border-color: #cbd5e1 !important; }
html:not(.dark) .espacios-theme .divide-slate-800 > :not([hidden]) ~ :not([hidden]) { border-color: #e2e8f0 !important; }
html:not(.dark) .espacios-theme .text-white:not([class*="bg-"]) { color: #0f172a !important; }
html:not(.dark) .espacios-theme .text-slate-300 { color: #334155 !important; }
html:not(.dark) .espacios-theme .text-slate-400 { color: #475569 !important; }
html:not(.dark) .espacios-theme .text-slate-500 { color: #64748b !important; }
html:not(.dark) .espacios-theme .text-slate-600 { color: #94a3b8 !important; }
html:not(.dark) .espacios-theme .text-slate-700 { color: #cbd5e1 !important; }
html:not(.dark) .espacios-theme .text-emerald-300,
html:not(.dark) .espacios-theme .text-emerald-400 { color: #047857 !important; }
html:not(.dark) .espacios-theme .text-sky-300,
html:not(.dark) .espacios-theme .text-sky-400 { color: #0369a1 !important; }
html:not(.dark) .espacios-theme .text-violet-300,
html:not(.dark) .espacios-theme .text-violet-400 { color: #6d28d9 !important; }
html:not(.dark) .espacios-theme .text-amber-300,
html:not(.dark) .espacios-theme .text-amber-400 { color: #b45309 !important; }
html:not(.dark) .espacios-theme .text-red-300,
html:not(.dark) .espacios-theme .text-red-400 { color: #b91c1c !important; }
html:not(.dark) .espacios-theme [class*="bg-emerald-500/10"] { background-color: #ecfdf5 !important; }
html:not(.dark) .espacios-theme [class*="bg-sky-500/5"],
html:not(.dark) .espacios-theme [class*="bg-sky-500/10"] { background-color: #f0f9ff !important; }
html:not(.dark) .espacios-theme [class*="bg-violet-500/5"],
html:not(.dark) .espacios-theme [class*="bg-violet-500/10"] { background-color: #f5f3ff !important; }
html:not(.dark) .espacios-theme [class*="bg-amber-500/5"],
html:not(.dark) .espacios-theme [class*="bg-amber-500/10"] { background-color: #fffbeb !important; }
html:not(.dark) .espacios-theme [class*="bg-red-500/5"],
html:not(.dark) .espacios-theme [class*="bg-red-500/10"] { background-color: #fef2f2 !important; }
html:not(.dark) .espacios-theme [class*="border-emerald-500/20"],
html:not(.dark) .espacios-theme [class*="border-emerald-500/30"] { border-color: #a7f3d0 !important; }
html:not(.dark) .espacios-theme [class*="border-sky-500/20"] { border-color: #bae6fd !important; }
html:not(.dark) .espacios-theme [class*="border-violet-500/20"] { border-color: #ddd6fe !important; }
html:not(.dark) .espacios-theme [class*="border-amber-500/20"] { border-color: #fde68a !important; }
html:not(.dark) .espacios-theme [class*="border-red-500/20"],
html:not(.dark) .espacios-theme [class*="border-red-500/30"],
html:not(.dark) .espacios-theme [class*="border-red-500/40"] { border-color: #fecaca !important; }
html:not(.dark) .espacios-theme [class*="hover:bg-slate-800"]:hover { background-color: #f1f5f9 !important; }
html:not(.dark) .espacios-theme [class*="hover:text-white"]:hover { color: #0f172a !important; }
html:not(.dark) .espacios-theme input:not([type="checkbox"]):not([type="radio"]),
html:not(.dark) .espacios-theme textarea,
html:not(.dark) .espacios-theme select { color: #0f172a !important; }
html:not(.dark) .espacios-theme input::placeholder,
html:not(.dark) .espacios-theme textarea::placeholder { color: #94a3b8 !important; }
html:not(.dark) .espacios-theme select option { background-color: #ffffff; color: #0f172a; }
.dark .espacios-theme select { color-scheme: dark; }
</style>
<div class="espacios-theme min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
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
                                                    class="form-eliminar-espacio"
                                                    data-nombre="{{ $espacio->nombre }}">
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
<script>
function showAppNotification(type, title, message) {
    const previous = document.getElementById('app-notification');
    if (previous) previous.remove();
    const styles = {
        success: {
            iconColor: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
            borderColor: 'border-emerald-200 dark:border-emerald-500/20',
            progressColor: 'bg-emerald-500',
            progressBackground: 'bg-emerald-100 dark:bg-emerald-950/50',
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
    notification.className = 'fixed right-4 bottom-4 sm:right-6 sm:bottom-6 z-[150] w-[calc(100%-2rem)] max-w-sm translate-y-6 opacity-0 transition-all duration-300 ease-out';
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
            <button type="button"
                class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-white/10 dark:hover:text-white"
                aria-label="Cerrar notificación">
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
        notification.classList.remove('opacity-100', 'translate-y-0');
        notification.classList.add('opacity-0', 'translate-y-6');
        setTimeout(() => {
            if (notification.parentNode) notification.remove();
        }, 300);
    };
    closeButton.addEventListener('click', closeNotification);
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            notification.classList.remove('opacity-0', 'translate-y-6');
            notification.classList.add('opacity-100', 'translate-y-0');
            progress.style.transition = 'transform 5s linear';
            progress.style.transform = 'scaleX(0)';
        });
    });
    timeout = setTimeout(closeNotification, 5000);
}
function showSpaceDeleteConfirm(spaceName) {
    if (document.getElementById('space-confirm-overlay')) {
        return Promise.resolve(false);
    }
    return new Promise(resolve => {
        const overlay = document.createElement('div');
        overlay.id = 'space-confirm-overlay';
        overlay.className = 'fixed inset-0 z-[200] flex items-center justify-center bg-slate-950/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200';
        const modal = document.createElement('div');
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('tabindex', '-1');
        modal.className = 'w-full max-w-md translate-y-4 scale-95 rounded-2xl border border-slate-200 bg-white p-6 opacity-0 shadow-2xl outline-none transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]';
        modal.innerHTML = `
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Eliminar espacio</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        ¿Deseas eliminar
                        <span class="space-name font-semibold text-slate-900 dark:text-white"></span>?
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                    class="cancel-delete inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                    Cancelar
                </button>
                <button type="button"
                    class="confirm-delete inline-flex h-10 items-center justify-center rounded-xl bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-500">
                    Eliminar
                </button>
            </div>
        `;
        modal.querySelector('.space-name').textContent = spaceName || 'este espacio';
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        const cancelButton = modal.querySelector('.cancel-delete');
        const confirmButton = modal.querySelector('.confirm-delete');
        let resolved = false;
        const close = result => {
            if (resolved) return;
            resolved = true;
            cancelButton.disabled = true;
            confirmButton.disabled = true;
            document.removeEventListener('keydown', handleKeydown, true);
            overlay.classList.remove('opacity-100');
            modal.classList.remove('translate-y-0', 'scale-100', 'opacity-100');
            modal.classList.add('translate-y-4', 'scale-95', 'opacity-0');
            setTimeout(() => {
                overlay.remove();
                resolve(result);
            }, 200);
        };
        const handleKeydown = event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                event.stopPropagation();
                if (event.repeat || resolved) return;
                close(true);
            }
            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();
                if (resolved) return;
                close(false);
            }
        };
        cancelButton.addEventListener('click', () => close(false));
        confirmButton.addEventListener('click', () => close(true));
        overlay.addEventListener('click', event => {
            if (event.target === overlay) close(false);
        });
        document.addEventListener('keydown', handleKeydown, true);
        requestAnimationFrame(() => {
            overlay.classList.add('opacity-100');
            modal.classList.remove('translate-y-4', 'scale-95', 'opacity-0');
            modal.classList.add('translate-y-0', 'scale-100', 'opacity-100');
            modal.focus();
        });
    });
}
document.addEventListener('DOMContentLoaded', function () {
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
});
document.addEventListener('submit', async function (event) {
    const deleteForm = event.target.closest('.form-eliminar-espacio');
    if (!deleteForm) return;
    event.preventDefault();
    const confirmed = await showSpaceDeleteConfirm(
        deleteForm.dataset.nombre
    );
    if (!confirmed) return;
    deleteForm.submit();
});
</script>
@endsection
