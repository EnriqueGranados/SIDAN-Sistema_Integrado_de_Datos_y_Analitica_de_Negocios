@extends('layouts.navbars')

@section('title', 'Revisión de actividades')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white sm:text-3xl">Revisión de actividades</h1>
        <p class="mt-2 text-sm text-gray-400">Actividades pendientes de revisión.</p>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-6 rounded-2xl border border-white/10 bg-white/[0.03] p-4">
        <form method="GET" action="{{ route('admin.actividades.revision.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar actividad..." class="w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-sm text-white outline-none placeholder:text-gray-600 focus:border-emerald-500">
            <button type="submit" class="rounded-xl bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/15">
                Buscar
            </button>

            @if (request('buscar'))
                <a href="{{ route('admin.actividades.revision.index') }}" class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm text-gray-400 transition hover:bg-white/5 hover:text-white">
                    Limpiar
                </a>
            @endif
        </form>
    </div>

    @if ($actividades->isEmpty())
        <div class="rounded-2xl border border-white/10 bg-white/[0.03] px-6 py-14 text-center">
            <h2 class="text-base font-semibold text-gray-300">No hay actividades pendientes</h2>
            <p class="mt-2 text-sm text-gray-600">Las actividades aparecerán aquí cuando sean enviadas a revisión.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-white/10">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[750px] text-left">
                    <thead class="bg-white/[0.04] text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-4">Actividad</th>
                            <th class="px-5 py-4">Categoría</th>
                            <th class="px-5 py-4">Revisión</th>
                            <th class="px-5 py-4">Actualizada</th>
                            <th class="px-5 py-4 text-right">Acción</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-white/10">
                        @foreach ($actividades as $actividad)
                            <tr class="bg-white/[0.02] transition hover:bg-white/[0.04]">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-white">{{ $actividad->nombre }}</p>
                                    <p class="mt-1 text-xs text-gray-600">#{{ $actividad->id_actividad }}</p>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-400">
                                    {{ $actividad->categoria?->nombre ?? 'Sin categoría' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                                        Revisión #{{ $actividad->revision_actual }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-400">
                                    {{ $actividad->updated_at?->format('d/m/Y H:i') }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.actividades.revision.show', $actividad) }}" class="inline-flex rounded-xl bg-emerald-500/10 px-4 py-2 text-sm font-semibold text-emerald-400 transition hover:bg-emerald-500/20">
                                        Revisar
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $actividades->links() }}
        </div>
    @endif
</div>
@endsection