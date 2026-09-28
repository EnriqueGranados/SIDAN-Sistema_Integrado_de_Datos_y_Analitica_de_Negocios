@extends('layouts.navbars')

@section('title', 'Actividades')

@section('content')
<div class="w-full min-w-0 max-w-full overflow-x-hidden">
    <div class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 sm:py-7 lg:px-8">

        {{-- ENCABEZADO --}}
        <div class="mb-6 flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Actividades
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Administra las actividades, continúa las configuraciones pendientes y consulta su estado de publicación.
                </p>
            </div>

            <a href="{{ route('admin.actividades.create') }}"
                class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-400 sm:w-auto">

                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>

                Nueva actividad
            </a>
        </div>

        {{-- FILTROS --}}
        <div class="mb-6 rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
            <form method="GET" action="{{ route('admin.actividades.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                {{-- BUSCAR --}}
                <div class="min-w-0 xl:col-span-2">
                    <label for="buscar" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Buscar actividad
                    </label>

                    <div class="relative">
                        <svg class="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>

                        <input type="text" id="buscar" name="buscar" value="{{ request('buscar') }}"
                            placeholder="Buscar por nombre..."
                            class="block w-full rounded-xl border border-white/10 bg-black/20 py-3 pl-11 pr-4 text-base text-white outline-none placeholder:text-gray-600 focus:border-emerald-500 sm:text-sm">
                    </div>
                </div>

                {{-- ESTADO --}}
                <div class="min-w-0">
                    <label for="estado_publicacion" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Estado
                    </label>

                    <select id="estado_publicacion" name="estado_publicacion"
                        class="block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-base text-white outline-none focus:border-emerald-500 sm:text-sm">

                        <option value="">Todos</option>
                        <option value="borrador" @selected(request('estado_publicacion') === 'borrador')>Configuración pendiente</option>
                        <option value="pendiente_revision" @selected(request('estado_publicacion') === 'pendiente_revision')>En revisión</option>
                        <option value="cambios_solicitados" @selected(request('estado_publicacion') === 'cambios_solicitados')>Cambios solicitados</option>
                        <option value="aprobada" @selected(request('estado_publicacion') === 'aprobada')>Aprobada</option>
                        <option value="rechazada" @selected(request('estado_publicacion') === 'rechazada')>Rechazada</option>
                        <option value="publicada" @selected(request('estado_publicacion') === 'publicada')>Publicada</option>
                        <option value="retirada" @selected(request('estado_publicacion') === 'retirada')>Retirada</option>
                    </select>
                </div>

                {{-- CATEGORÍA --}}
                <div class="min-w-0">
                    <label for="id_categoria" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Categoría
                    </label>

                    <select id="id_categoria" name="id_categoria"
                        class="block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-base text-white outline-none focus:border-emerald-500 sm:text-sm">

                        <option value="">Todas</option>

                        @foreach ($categorias ?? collect() as $categoria)
                            <option value="{{ $categoria->id_categoria }}" @selected((string) request('id_categoria') === (string) $categoria->id_categoria)>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-3 md:col-span-2 md:flex-row xl:col-span-4 xl:justify-end">
                    @if (request()->hasAny(['buscar', 'estado_publicacion', 'id_categoria']))
                        <a href="{{ route('admin.actividades.index') }}"
                            class="inline-flex w-full items-center justify-center rounded-xl border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-400 transition hover:bg-white/5 hover:text-white md:w-auto">
                            Limpiar filtros
                        </a>
                    @endif

                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white/10 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/15 md:w-auto">

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                        </svg>

                        Aplicar filtros
                    </button>
                </div>
            </form>
        </div>

        {{-- SIN ACTIVIDADES --}}
        @if ($actividades->isEmpty())
            <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-14 text-center sm:px-6">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 text-gray-600">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6M7 4h7l3 3v13H7V4z" />
                    </svg>
                </div>

                <h2 class="mt-4 text-base font-semibold text-gray-300">
                    No se encontraron actividades
                </h2>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-600">
                    Puedes crear una nueva actividad o cambiar los filtros utilizados.
                </p>

                <a href="{{ route('admin.actividades.create') }}"
                    class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-400">
                    Crear actividad
                </a>
            </div>
        @else

            {{-- ========================================================= --}}
            {{-- MÓVIL / TABLET --}}
            {{-- ========================================================= --}}
            <div class="space-y-4 lg:hidden">
                @foreach ($actividades as $actividad)
                    <article class="min-w-0 rounded-2xl border border-white/10 bg-white/[0.03] p-4">

                        <div class="flex min-w-0 items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs text-gray-600">
                                    #{{ $actividad->id_actividad }}
                                </p>

                                <h2 class="mt-1 break-words text-base font-semibold text-white">
                                    {{ $actividad->nombre }}
                                </h2>

                                @if ($actividad->categoria)
                                    <p class="mt-1 break-words text-xs text-gray-500">
                                        {{ $actividad->categoria->nombre }}
                                    </p>
                                @endif
                            </div>

                            @if ($actividad->destacada)
                                <span class="shrink-0 rounded-full bg-yellow-500/10 px-2 py-1 text-[10px] font-semibold text-yellow-400">
                                    Destacada
                                </span>
                            @endif
                        </div>

                        {{-- ESTADO --}}
                        <div class="mt-4">
                            @include('admin.actividades.partials.estado', ['actividad' => $actividad])
                        </div>

                        @if ($actividad->estado_publicacion === 'borrador')
                            <div class="mt-3 rounded-xl border border-amber-500/15 bg-amber-500/[0.04] p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86A2 2 0 0020.66 16L13.73 4a2 2 0 00-3.46 0L3.34 16A2 2 0 005.07 19z" />
                                    </svg>

                                    <p class="break-words text-xs leading-5 text-amber-300/80">
                                        Todavía debes completar la configuración antes de poder enviarla a revisión.
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                <p class="text-[11px] text-gray-600">
                                    Productos / servicios
                                </p>

                                <p class="mt-1 text-sm font-semibold text-gray-300">
                                    {{ $actividad->items_count ?? $actividad->items?->count() ?? 0 }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                <p class="text-[11px] text-gray-600">
                                    Sesiones
                                </p>

                                <p class="mt-1 text-sm font-semibold text-gray-300">
                                    {{ $actividad->sesiones_count ?? $actividad->sesiones?->count() ?? 0 }}
                                </p>
                            </div>
                        </div>

                        {{-- ACCIONES --}}
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-white/10 pt-4">

                            <a href="{{ route('admin.actividades.show', ['actividad' => $actividad->id_actividad]) }}"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 text-gray-400 transition hover:bg-white/5 hover:text-white"
                                title="Ver actividad"
                                aria-label="Ver actividad">

                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                                </svg>
                            </a>

                            <a href="{{ route('admin.actividades.edit', ['actividad' => $actividad->id_actividad]) }}"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/5 text-blue-400 transition hover:bg-blue-500/10"
                                title="Editar información básica"
                                aria-label="Editar información básica">

                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>

                            @if (in_array($actividad->estado_publicacion, ['borrador', 'cambios_solicitados']))
                                <a href="{{ route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad]) }}"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-amber-500/25 bg-amber-500/10 text-amber-400 transition hover:bg-amber-500/15"
                                    title="Continuar configuración"
                                    aria-label="Continuar configuración">

                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </a>
                            @endif

                            @if ($actividad->estado_publicacion === 'borrador')
                                <form action="{{ route('admin.actividades.destroy', ['actividad' => $actividad->id_actividad]) }}"
                                    method="POST"
                                    class="ml-auto"
                                    onsubmit="return confirm('¿Seguro que deseas descartar este borrador? Esta acción no debe utilizarse para actividades ya enviadas a revisión.');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-red-500/20 bg-red-500/5 text-red-400 transition hover:bg-red-500/10"
                                        title="Descartar borrador"
                                        aria-label="Descartar borrador">

                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- ========================================================= --}}
            {{-- ESCRITORIO --}}
            {{-- ========================================================= --}}
            <div class="hidden overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03] lg:block">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[950px]">
                        <thead class="border-b border-white/10 bg-black/10">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Actividad
                                </th>

                                <th class="px-5 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Estado
                                </th>

                                <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Items
                                </th>

                                <th class="px-5 py-4 text-center text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Sesiones
                                </th>

                                <th class="px-5 py-4 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">
                                    Acciones
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/5">
                            @foreach ($actividades as $actividad)
                                <tr class="transition hover:bg-white/[0.02]">

                                    {{-- ACTIVIDAD --}}
                                    <td class="px-5 py-5 align-top">
                                        <div class="max-w-sm">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p class="break-words font-semibold text-white">
                                                    {{ $actividad->nombre }}
                                                </p>

                                                @if ($actividad->destacada)
                                                    <span class="rounded-full bg-yellow-500/10 px-2 py-0.5 text-[10px] font-semibold text-yellow-400">
                                                        Destacada
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-600">
                                                <span>#{{ $actividad->id_actividad }}</span>

                                                @if ($actividad->categoria)
                                                    <span>•</span>
                                                    <span>{{ $actividad->categoria->nombre }}</span>
                                                @endif
                                            </div>

                                            @if ($actividad->resumen)
                                                <p class="mt-2 line-clamp-2 break-words text-xs leading-5 text-gray-500">
                                                    {{ $actividad->resumen }}
                                                </p>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- ESTADO --}}
                                    <td class="px-5 py-5 align-top">
                                        @include('admin.actividades.partials.estado', ['actividad' => $actividad])

                                        @if ($actividad->estado_publicacion === 'borrador')
                                            <p class="mt-2 max-w-[230px] text-xs leading-5 text-amber-400/70">
                                                Falta terminar la configuración antes de enviarla a revisión.
                                            </p>
                                        @elseif ($actividad->estado_publicacion === 'cambios_solicitados')
                                            <p class="mt-2 max-w-[230px] text-xs leading-5 text-orange-400/70">
                                                Debes realizar los cambios solicitados antes de volver a enviarla.
                                            </p>
                                        @endif
                                    </td>

                                    {{-- ITEMS --}}
                                    <td class="px-5 py-5 text-center align-top">
                                        <span class="inline-flex min-w-8 items-center justify-center rounded-lg bg-white/5 px-2 py-1 text-sm font-semibold text-gray-300">
                                            {{ $actividad->items_count ?? $actividad->items?->count() ?? 0 }}
                                        </span>
                                    </td>

                                    {{-- SESIONES --}}
                                    <td class="px-5 py-5 text-center align-top">
                                        <span class="inline-flex min-w-8 items-center justify-center rounded-lg bg-white/5 px-2 py-1 text-sm font-semibold text-gray-300">
                                            {{ $actividad->sesiones_count ?? $actividad->sesiones?->count() ?? 0 }}
                                        </span>
                                    </td>

                                    {{-- ACCIONES --}}
                                    <td class="px-5 py-5 align-top">
                                        <div class="flex items-center justify-end gap-2">

                                            {{-- VER --}}
                                            <a href="{{ route('admin.actividades.show', ['actividad' => $actividad->id_actividad]) }}"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 text-gray-400 transition hover:bg-white/5 hover:text-white"
                                                title="Ver actividad"
                                                aria-label="Ver actividad">

                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    <circle cx="12" cy="12" r="3" stroke-width="2" />
                                                </svg>
                                            </a>

                                            {{-- EDITAR INFORMACIÓN BÁSICA --}}
                                            <a href="{{ route('admin.actividades.edit', ['actividad' => $actividad->id_actividad]) }}"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/5 text-blue-400 transition hover:bg-blue-500/10"
                                                title="Editar información básica"
                                                aria-label="Editar información básica">

                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            {{-- CONTINUAR CONFIGURACIÓN --}}
                                            @if (in_array($actividad->estado_publicacion, ['borrador', 'cambios_solicitados']))
                                                <a href="{{ route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad]) }}"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-amber-500/25 bg-amber-500/10 text-amber-400 transition hover:bg-amber-500/15"
                                                    title="Continuar configuración"
                                                    aria-label="Continuar configuración">

                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </a>
                                            @endif

                                            {{-- DESCARTAR BORRADOR --}}
                                            @if ($actividad->estado_publicacion === 'borrador')
                                                <form action="{{ route('admin.actividades.destroy', ['actividad' => $actividad->id_actividad]) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('¿Seguro que deseas descartar este borrador?');">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-500/20 bg-red-500/5 text-red-400 transition hover:bg-red-500/10"
                                                        title="Descartar borrador"
                                                        aria-label="Descartar borrador">

                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGINACIÓN --}}
            @if ($actividades->hasPages())
                <div class="mt-6">
                    {{ $actividades->withQueryString()->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection