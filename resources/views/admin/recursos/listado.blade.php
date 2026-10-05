@extends('layouts.navbars')

@section('title', 'Recursos')

@section('content')
    <div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Recursos</h1>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Administra los equipos, mobiliario, infraestructura y otros recursos que pueden
                        estar disponibles en los espacios o utilizarse de forma móvil en las actividades.
                    </p>
                </div>

                <a href="{{ route('admin.recursos.create') }}"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo recurso
                </a>
            </div>

            <div
                class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                <form id="formFiltros" method="GET" action="{{ route('admin.recursos.index') }}">
                    <div class="grid gap-4 lg:grid-cols-[minmax(260px,1fr)_200px_180px_170px_auto]">
                        <div>
                            <label for="buscar" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Buscar recurso
                            </label>

                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="m21 21-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14Z" />
                                    </svg>
                                </div>

                                <input id="buscar" name="buscar" type="search" value="{{ $buscar }}"
                                    autocomplete="off" placeholder="Ej. Proyector, micrófono, silla..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-10 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-white/10">

                                <div id="spinnerBusqueda" class="absolute inset-y-0 right-0 hidden items-center pr-3">
                                    <svg class="h-4 w-4 animate-spin text-emerald-500 dark:text-emerald-400"
                                        viewBox="0 0 24 24" fill="none">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="categoria"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Categoría
                            </label>

                            <select id="categoria" name="categoria"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:bg-white/10 dark:[color-scheme:dark]">
                                <option value="">Todas</option>

                                @foreach ($categorias as $categoriaDisponible)
                                    <option value="{{ $categoriaDisponible }}" @selected($categoria === $categoriaDisponible)>
                                        {{ $categoriaDisponible }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="movilidad"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Disponibilidad
                            </label>

                            <select id="movilidad" name="movilidad"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:bg-white/10 dark:[color-scheme:dark]">
                                <option value="">Todos</option>
                                <option value="movil" @selected($movilidad === 'movil')>
                                    Móviles
                                </option>
                                <option value="fijo" @selected($movilidad === 'fijo')>
                                    Del espacio
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="estado" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Estado
                            </label>

                            <select id="estado" name="estado"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:bg-white/10 dark:[color-scheme:dark]">
                                <option value="">Todos</option>
                                <option value="activo" @selected($estado === 'activo')>
                                    Activos
                                </option>
                                <option value="inactivo" @selected($estado === 'inactivo')>
                                    Inactivos
                                </option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button id="btnLimpiar" type="button"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 lg:w-auto dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                                Limpiar
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="resultadosRecursos">
                @if ($recursos->count() > 0)
                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5">
                                <thead class="bg-slate-50 dark:bg-white/[0.02]">
                                    <tr>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            Recurso
                                        </th>
                                        <th
                                            class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            Categoría
                                        </th>
                                        <th
                                            class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            Disponibilidad
                                        </th>
                                        <th
                                            class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            Espacios
                                        </th>
                                        <th
                                            class="px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            Estado
                                        </th>
                                        <th
                                            class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-200 dark:divide-white/5">
                                    @foreach ($recursos as $recurso)
                                        <tr class="transition hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div
                                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M20 7h-9m9 5h-9m9 5h-9M7 7H4m3 5H4m3 5H4" />
                                                        </svg>
                                                    </div>

                                                    <div>
                                                        <p class="font-medium text-slate-900 dark:text-white">
                                                            {{ $recurso->nombre }}
                                                        </p>

                                                        <p class="mt-0.5 text-xs text-slate-500">
                                                            Se contabiliza por {{ $recurso->unidad_medida }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4">
                                                @if ($recurso->categoria)
                                                    <span
                                                        class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                        {{ $recurso->categoria }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-slate-500">
                                                        Sin categoría
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 text-center">
                                                @if ($recurso->es_movil)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full border border-sky-200 bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-400">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 17h8m-4-4v8M5 4h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                                        </svg>
                                                        Móvil
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full border border-violet-200 bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700 dark:border-violet-500/20 dark:bg-violet-500/10 dark:text-violet-400">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 21s6-4.35 6-10A6 6 0 106 11c0 5.65 6 10 6 10z" />
                                                            <circle cx="12" cy="11" r="2"
                                                                stroke-width="2" />
                                                        </svg>
                                                        Del espacio
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 text-center">
                                                <span
                                                    class="inline-flex min-w-8 items-center justify-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                                    {{ $recurso->espacios_count }}
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 text-center">
                                                @if ($recurso->activo)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                        Activo
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:border-white/5 dark:bg-slate-700/50 dark:text-slate-400">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                                        Inactivo
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('admin.recursos.edit', $recurso) }}"
                                                        title="Editar recurso"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-emerald-500/50 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                    </a>

                                                    <form method="POST"
                                                        action="{{ route('admin.recursos.destroy', $recurso) }}"
                                                        class="form-eliminar-recurso"
                                                        data-nombre="{{ $recurso->nombre }}">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                            title="{{ $recurso->espacios_count > 0 ? 'No puede eliminarse porque está asignado a uno o más espacios' : 'Eliminar recurso' }}"
                                                            @disabled($recurso->espacios_count > 0)
                                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition
                                                            {{ $recurso->espacios_count > 0
                                                                ? 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400 dark:border-white/5 dark:bg-white/[0.02] dark:text-slate-600'
                                                                : 'border-slate-200 bg-white text-slate-600 hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:border-red-500/50 dark:hover:bg-red-500/10 dark:hover:text-red-400' }}">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
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

                        @if ($recursos->hasPages())
                            <div
                                class="recurso-paginacion border-t border-slate-200 px-4 py-4 sm:px-6 dark:border-white/5">
                                {{ $recursos->links() }}
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 text-sm text-slate-500">
                        Mostrando
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $recursos->firstItem() }}</span>
                        a
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $recursos->lastItem() }}</span>
                        de
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $recursos->total() }}</span>
                        recursos.
                    </div>
                @else
                    <div
                        class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm transition-colors sm:px-10 sm:py-20 dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M20 7h-9m9 5h-9m9 5h-9M7 7H4m3 5H4m3 5H4" />
                            </svg>
                        </div>

                        @if ($buscar !== '' || $estado !== '' || $movilidad !== '' || $categoria !== '')
                            <h3 class="mt-5 text-base font-semibold text-slate-900 dark:text-white">
                                No encontramos recursos
                            </h3>

                            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                                No hay recursos que coincidan con los criterios seleccionados.
                                Prueba con otros filtros o limpia la búsqueda.
                            </p>

                            <button type="button" data-limpiar-filtros
                                class="mt-6 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                                Limpiar filtros
                            </button>
                        @else
                            <h3 class="mt-5 text-base font-semibold text-slate-900 dark:text-white">
                                Aún no hay recursos
                            </h3>

                            <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500 dark:text-slate-400">
                                Registra los recursos que SIDAN podrá asociar a los espacios y,
                                cuando corresponda, utilizar como recursos móviles en las actividades.
                            </p>

                            <a href="{{ route('admin.recursos.create') }}"
                                class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Crear primer recurso
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

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

            <button type="button"
                class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white">
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
                setTimeout(() => notification.remove(), 300);
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

        function showDeleteConfirm(resourceName) {
            if (document.getElementById('resource-confirm-overlay')) {
                return Promise.resolve(false);
            }

            return new Promise(resolve => {
                const overlay = document.createElement('div');
                overlay.id = 'resource-confirm-overlay';
                overlay.className =
                    'fixed inset-0 z-[200] flex items-center justify-center bg-slate-950/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200';

                const modal = document.createElement('div');
                modal.setAttribute('role', 'dialog');
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('tabindex', '-1');
                modal.className =
                    'w-full max-w-md translate-y-4 scale-95 rounded-2xl border border-slate-200 bg-white p-6 opacity-0 shadow-2xl outline-none transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]';

                modal.innerHTML = `
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3"></path>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">
                        Eliminar recurso
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        ¿Deseas eliminar
                        <span class="resource-name font-semibold text-slate-900 dark:text-white"></span>?
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

                modal.querySelector('.resource-name').textContent = resourceName || 'este recurso';

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
                    if (event.target === overlay) {
                        close(false);
                    }
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

        document.addEventListener('DOMContentLoaded', function() {
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

            const form = document.getElementById('formFiltros');
            const buscar = document.getElementById('buscar');
            const categoria = document.getElementById('categoria');
            const movilidad = document.getElementById('movilidad');
            const estado = document.getElementById('estado');
            const btnLimpiar = document.getElementById('btnLimpiar');
            const spinner = document.getElementById('spinnerBusqueda');

            const urlIndex = "{{ route('admin.recursos.index') }}";

            let temporizador = null;
            let controlador = null;
            let solicitudActual = 0;

            function construirUrl() {
                const parametros = new URLSearchParams();
                const termino = buscar.value.trim();

                if (termino !== '') {
                    parametros.set('buscar', termino);
                }

                if (categoria.value !== '') {
                    parametros.set('categoria', categoria.value);
                }

                if (movilidad.value !== '') {
                    parametros.set('movilidad', movilidad.value);
                }

                if (estado.value !== '') {
                    parametros.set('estado', estado.value);
                }

                const query = parametros.toString();

                return query === '' ?
                    urlIndex :
                    `${urlIndex}?${query}`;
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
                        throw new Error('No fue posible cargar los recursos.');
                    }

                    const html = await respuesta.text();
                    const documento = new DOMParser().parseFromString(html, 'text/html');
                    const nuevosResultados = documento.getElementById('resultadosRecursos');
                    const resultadosActuales = document.getElementById('resultadosRecursos');

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
                categoria.value = '';
                movilidad.value = '';
                estado.value = '';

                cargarResultados(urlIndex);
                buscar.focus();
            }

            buscar.addEventListener('input', function() {
                clearTimeout(temporizador);

                temporizador = setTimeout(function() {
                    cargarResultados(construirUrl());
                }, 350);
            });

            [categoria, movilidad, estado].forEach(function(select) {
                select.addEventListener('change', function() {
                    clearTimeout(temporizador);
                    cargarResultados(construirUrl());
                });
            });

            btnLimpiar.addEventListener('click', ejecutarLimpieza);

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                clearTimeout(temporizador);
                cargarResultados(construirUrl());
            });

            document.addEventListener('click', function(event) {
                const enlacePaginacion = event.target.closest(
                    '#resultadosRecursos .recurso-paginacion a'
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

            document.addEventListener('submit', async function(event) {
                const deleteForm = event.target.closest('.form-eliminar-recurso');

                if (!deleteForm) return;

                event.preventDefault();

                const confirmed = await showDeleteConfirm(
                    deleteForm.dataset.nombre
                );

                if (!confirmed) return;

                deleteForm.submit();
            });

            window.addEventListener('popstate', function() {
                const parametros = new URLSearchParams(window.location.search);

                buscar.value = parametros.get('buscar') ?? '';
                categoria.value = parametros.get('categoria') ?? '';
                movilidad.value = parametros.get('movilidad') ?? '';
                estado.value = parametros.get('estado') ?? '';

                cargarResultados(window.location.href, false);
            });
        });
    </script>
@endsection
