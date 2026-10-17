@extends('layouts.navbars')

@section('title', 'Control de inscripciones')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 text-sidan-900 dark:text-white">

        <div class="mb-7 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                    Administración · SIDAN
                </p>

                <h1 class="mt-2 text-3xl font-black">
                    Control de inscripciones
                </h1>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Consulta participantes, estados y registros por actividad.
                </p>
            </div>

            <a href="{{ route('admin.control.ordenes') }}"
                class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white hover:bg-emerald-700">
                Ver órdenes →
            </a>
        </div>

        <form method="GET" action="{{ route('admin.control.inscripciones') }}"
            class="mb-6 grid gap-4 rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 p-5 md:grid-cols-4">

            <div>
                <label class="mb-2 block text-xs font-bold text-slate-500 dark:text-slate-400">
                    Actividad
                </label>

                <select name="actividad"
                    class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 px-3 py-3 text-sm text-sidan-900 dark:text-white">
                    <option value="">Todas las actividades</option>

                    @foreach ($actividades as $actividad)
                        <option value="{{ $actividad->id_actividad }}" @selected(request('actividad') == $actividad->id_actividad)>
                            {{ $actividad->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold text-slate-500 dark:text-slate-400">
                    Estado
                </label>

                <select name="estado"
                    class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 px-3 py-3 text-sm text-sidan-900 dark:text-white">
                    <option value="">Todos los estados</option>

                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>
                            {{ ucfirst(str_replace('_', ' ', $estado)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold text-slate-500 dark:text-slate-400">
                    Buscar participante
                </label>

                <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o correo"
                    class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 px-3 py-3 text-sm text-sidan-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-black text-white">
                    Filtrar
                </button>

                <a href="{{ route('admin.control.inscripciones') }}"
                    class="rounded-xl border border-slate-200 dark:border-white/10 px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-300">
                    Limpiar
                </a>
            </div>
        </form>

        <div class="mb-4 text-sm text-slate-500 dark:text-slate-400">
            {{ $inscripciones->total() }} inscripción(es) encontrada(s)
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-white/5 text-xs uppercase text-slate-500 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-4">Inscripción</th>
                            <th class="px-5 py-4">Actividad</th>
                            <th class="px-5 py-4">Participante</th>
                            <th class="px-5 py-4">Estado</th>
                            <th class="px-5 py-4">Fecha</th>
                            <th class="px-5 py-4">Órdenes</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                        @forelse ($inscripciones as $inscripcion)
                            @php
                                $estado = $inscripcion->estado;

                                $color = match ($estado) {
                                    'confirmada',
                                    'confirmada_prueba'
                                        => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300',

                                    'pendiente_pago',
                                    'revision_cupo'
                                        => 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300',

                                    'cancelada',
                                    'vencida'
                                        => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-300',

                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-500/10 dark:text-slate-300',
                                };
                            @endphp

                            <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                <td class="px-5 py-4 font-black">
                                    #{{ $inscripcion->id_inscripcion }}
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold">
                                        {{ $inscripcion->actividad?->nombre ?? 'Actividad eliminada' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-bold">
                                        {{ $inscripcion->nombre_participante }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $inscripcion->correo_participante }}
                                    </p>

                                    @if ($inscripcion->telefono_participante)
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ $inscripcion->telefono_participante }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1.5 text-xs font-black {{ $color }}">
                                        {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-slate-700 dark:text-slate-300">
                                    {{ $inscripcion->created_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.control.ordenes', ['actividad' => $inscripcion->id_actividad, 'buscar' => $inscripcion->correo_participante]) }}"
                                        class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                        {{ $inscripcion->ordenes_count }} orden(es) →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-slate-500 dark:text-slate-400">
                                    No se encontraron inscripciones con estos filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $inscripciones->links() }}
        </div>
    </div>
@endsection
