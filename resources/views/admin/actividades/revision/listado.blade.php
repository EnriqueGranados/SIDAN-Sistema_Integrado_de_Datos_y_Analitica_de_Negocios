@extends('layouts.navbars')

@section('title', 'SIDAN | Revisiones de actividades')

@section('content')
@php
    $nombreUsuario = function ($usuario) {
        if (!$usuario) {
            return 'Usuario no disponible';
        }

        $informacion = $usuario->informacion_personal ?? null;

        if ($informacion) {
            $nombre = trim(
                ($informacion->nombres ?? '').' '.
                ($informacion->apellidos ?? '')
            );

            if ($nombre !== '') {
                return $nombre;
            }
        }

        return $usuario->name
            ?? $usuario->nombre
            ?? $usuario->correo
            ?? 'Usuario';
    };
@endphp

<div class="min-h-full bg-[#f6f8fb] dark:bg-sidan-950">
    <main class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                    Administración
                </p>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 dark:text-white">
                    Revisiones de actividades
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-400">
                    Aquí aparecen las actividades que ya terminaron su configuración y esperan una revisión antes de continuar.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                    <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400">
                        Pendientes
                    </span>
                    <strong class="mt-1 block text-xl font-black text-sidan-900 dark:text-white">
                        {{ $actividades->total() }}
                    </strong>
                </div>
            </div>
        </div>

        <form
            method="GET"
            action="{{ route('admin.actividades.revision.index') }}"
            class="mt-7 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:flex-row sm:items-center"
        >
            <div class="min-w-0 flex-1">
                <label for="buscar" class="sr-only">Buscar actividad</label>
                <input
                    id="buscar"
                    name="buscar"
                    type="search"
                    value="{{ request('buscar') }}"
                    placeholder="Buscar por nombre de actividad..."
                    class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500"
                >
            </div>

            <button
                type="submit"
                class="rounded-xl bg-sidan-500 px-5 py-2.5 text-sm font-black text-white transition hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500/30"
            >
                Buscar
            </button>

            @if (request()->filled('buscar'))
                <a
                    href="{{ route('admin.actividades.revision.index') }}"
                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10"
                >
                    Limpiar
                </a>
            @endif
        </form>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
            @forelse ($actividades as $actividad)
                <article class="border-b border-slate-200 p-5 last:border-b-0 dark:border-white/10 sm:p-6">
                    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-black text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20">
                                    Pendiente de revisión
                                </span>

                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-white/5 dark:text-slate-300">
                                    Revisión #{{ $actividad->revision_actual }}
                                </span>
                            </div>

                            <h2 class="mt-3 truncate text-lg font-black text-sidan-900 dark:text-white">
                                {{ $actividad->nombre }}
                            </h2>

                            @if ($actividad->resumen)
                                <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                    {{ $actividad->resumen }}
                                </p>
                            @endif

                            <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                                <div>
                                    <span class="block text-xs font-semibold text-slate-400">Categoría</span>
                                    <strong class="mt-1 block font-bold text-slate-700 dark:text-slate-200">
                                        {{ $actividad->categoria?->nombre ?? 'Sin categoría' }}
                                    </strong>
                                </div>

                                <div>
                                    <span class="block text-xs font-semibold text-slate-400">Creada por</span>
                                    <strong class="mt-1 block font-bold text-slate-700 dark:text-slate-200">
                                        {{ $nombreUsuario($actividad->creador) }}
                                    </strong>
                                </div>

                                <div>
                                    <span class="block text-xs font-semibold text-slate-400">Última actualización</span>
                                    <strong class="mt-1 block font-bold text-slate-700 dark:text-slate-200">
                                        {{ $actividad->updated_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center">
                            <a
                                href="{{ route('admin.actividades.revision.show', $actividad) }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sidan-500 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500/30 sm:w-auto"
                            >
                                Revisar actividad
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="px-6 py-14 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-sidan-500 dark:bg-green-500/10">
                        ✓
                    </div>

                    <h2 class="mt-4 text-lg font-black text-sidan-900 dark:text-white">
                        No hay actividades pendientes
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Cuando una actividad sea enviada a revisión aparecerá aquí.
                    </p>
                </div>
            @endforelse
        </section>

        @if ($actividades->hasPages())
            <div class="mt-6">
                {{ $actividades->withQueryString()->links() }}
            </div>
        @endif
    </main>
</div>
@endsection
