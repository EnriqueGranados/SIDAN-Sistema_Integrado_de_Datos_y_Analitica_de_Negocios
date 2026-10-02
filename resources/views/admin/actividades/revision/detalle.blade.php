@extends('layouts.navbars')

@section('title', 'Revisar actividad')

@section('content')
<div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('admin.actividades.revision.index') }}" class="text-sm text-gray-400 transition hover:text-white">← Volver a revisiones</a>
            <h1 class="mt-2 text-2xl font-bold text-white">{{ $actividad->nombre }}</h1>
            <p class="mt-1 text-sm text-gray-500">Revisión #{{ $actividad->revision_actual }}</p>
        </div>
        <span class="w-fit rounded-full bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-400">Pendiente de revisión</span>
    </div>

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <section class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                <h2 class="mb-4 text-base font-semibold text-white">Información general</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-600">Categoría</p>
                        <p class="mt-1 text-sm text-gray-300">{{ $actividad->categoria?->nombre ?? 'Sin categoría' }}</p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-600">Visibilidad</p>
                        <p class="mt-1 text-sm text-gray-300">{{ ucfirst($actividad->visibilidad) }}</p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-600">Realización desde</p>
                        <p class="mt-1 text-sm text-gray-300">{{ $actividad->realizacion_desde?->format('d/m/Y H:i') ?? 'Sin definir' }}</p>
                    </div>

                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-600">Realización hasta</p>
                        <p class="mt-1 text-sm text-gray-300">{{ $actividad->realizacion_hasta?->format('d/m/Y H:i') ?? 'Sin definir' }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-gray-600">Resumen</p>
                        <p class="mt-1 text-sm text-gray-300">{{ $actividad->resumen ?: 'Sin resumen' }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="text-xs uppercase tracking-wide text-gray-600">Descripción</p>
                        <div class="mt-1 whitespace-pre-line text-sm text-gray-300">{{ $actividad->descripcion ?: 'Sin descripción' }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                <h2 class="mb-4 text-base font-semibold text-white">Productos y servicios</h2>

                @forelse ($actividad->items as $item)
                    <div class="border-b border-white/10 py-4 first:pt-0 last:border-0 last:pb-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-white">{{ $item->nombre }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ ucfirst($item->tipo) }}</p>

                                @if ($item->descripcion)
                                    <p class="mt-2 text-sm text-gray-400">{{ $item->descripcion }}</p>
                                @endif
                            </div>

                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-300">${{ number_format((float) $item->precio, 2) }}</p>
                                <p class="mt-1 text-xs text-gray-600">Stock: {{ $item->stock_total ?? 'Sin límite' }}</p>
                            </div>
                        </div>

                        @if ($item->variantes->isNotEmpty())
                            <div class="mt-3 rounded-xl bg-black/20 p-3">
                                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-600">Variantes</p>

                                @foreach ($item->variantes as $variante)
                                    <div class="flex justify-between gap-3 py-1 text-sm">
                                        <span class="text-gray-400">{{ $variante->nombre_variante }}</span>
                                        <span class="text-gray-300">
                                            {{ $variante->precio !== null ? '$'.number_format((float) $variante->precio, 2) : 'Precio base' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No hay productos o servicios configurados.</p>
                @endforelse
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                <h2 class="mb-4 text-base font-semibold text-white">Programación</h2>

                @forelse ($actividad->sesiones as $sesion)
                    <div class="border-b border-white/10 py-4 first:pt-0 last:border-0 last:pb-0">
                        <div class="flex flex-col gap-2 sm:flex-row sm:justify-between">
                            <div>
                                <p class="font-medium text-white">{{ $sesion->nombre }}</p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $sesion->fecha_inicio?->format('d/m/Y H:i') }}
                                    @if ($sesion->fecha_fin)
                                        — {{ $sesion->fecha_fin->format('d/m/Y H:i') }}
                                    @endif
                                </p>

                                @if ($sesion->ubicacion)
                                    <p class="mt-1 text-sm text-gray-500">{{ $sesion->ubicacion }}</p>
                                @endif
                            </div>

                            <div class="text-sm text-gray-400">
                                Cupo: {{ $sesion->cupo ?? 'Sin límite' }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No hay sesiones configuradas.</p>
                @endforelse
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                <h2 class="mb-4 text-base font-semibold text-white">Historial de revisión</h2>

                @forelse ($actividad->revisiones->sortByDesc('creado_en') as $revision)
                    <div class="border-b border-white/10 py-4 first:pt-0 last:border-0 last:pb-0">
                        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-sm font-semibold text-gray-300">
                                {{ str_replace('_', ' ', ucfirst($revision->accion)) }}
                            </p>
                            <p class="text-xs text-gray-600">
                                Revisión #{{ $revision->numero_revision }} · {{ $revision->creado_en?->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        @if ($revision->observacion)
                            <p class="mt-2 text-sm text-gray-400">{{ $revision->observacion }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No hay historial registrado.</p>
                @endforelse
            </section>
        </div>

        <div class="space-y-5">
            <section class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                <h2 class="mb-4 text-base font-semibold text-white">Inscripción</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Habilitada</span>
                        <span class="text-gray-300">{{ $actividad->habilita_inscripcion ? 'Sí' : 'No' }}</span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Cupo total</span>
                        <span class="text-gray-300">{{ $actividad->cupo_total ?? 'Sin límite' }}</span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Requiere cuenta</span>
                        <span class="text-gray-300">{{ $actividad->requiere_cuenta ? 'Sí' : 'No' }}</span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Lista de espera</span>
                        <span class="text-gray-300">{{ $actividad->permite_lista_espera ? 'Sí' : 'No' }}</span>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                <h2 class="mb-4 text-base font-semibold text-white">Etiquetas</h2>

                <div class="flex flex-wrap gap-2">
                    @forelse ($actividad->etiquetas as $etiqueta)
                        <span class="rounded-lg bg-white/5 px-2.5 py-1 text-xs text-gray-400">{{ $etiqueta->nombre }}</span>
                    @empty
                        <p class="text-sm text-gray-500">Sin etiquetas.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                <h2 class="mb-4 text-base font-semibold text-white">Decisión</h2>

                <form method="POST" action="{{ route('admin.actividades.revision.aprobar', $actividad) }}" class="mb-5">
                    @csrf
                    <label class="mb-2 block text-sm text-gray-400">Observación de aprobación</label>
                    <textarea name="observacion" rows="3" placeholder="Opcional..." class="w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm text-white outline-none placeholder:text-gray-600 focus:border-emerald-500"></textarea>
                    <button type="submit" onclick="return confirm('¿Aprobar esta actividad?')" class="mt-3 w-full rounded-xl bg-emerald-500/15 px-4 py-2.5 text-sm font-semibold text-emerald-400 transition hover:bg-emerald-500/25">
                        Aprobar actividad
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.actividades.revision.solicitar-cambios', $actividad) }}" class="mb-5 border-t border-white/10 pt-5">
                    @csrf
                    <label class="mb-2 block text-sm text-gray-400">Cambios solicitados</label>
                    <textarea name="observacion" rows="4" required placeholder="Describe qué debe corregirse..." class="w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm text-white outline-none placeholder:text-gray-600 focus:border-amber-500"></textarea>
                    <button type="submit" class="mt-3 w-full rounded-xl bg-amber-500/15 px-4 py-2.5 text-sm font-semibold text-amber-400 transition hover:bg-amber-500/25">
                        Solicitar cambios
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.actividades.revision.rechazar', $actividad) }}" class="border-t border-white/10 pt-5">
                    @csrf
                    <label class="mb-2 block text-sm text-gray-400">Motivo del rechazo</label>
                    <textarea name="observacion" rows="4" required placeholder="Indica por qué se rechaza..." class="w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2 text-sm text-white outline-none placeholder:text-gray-600 focus:border-red-500"></textarea>
                    <button type="submit" onclick="return confirm('¿Rechazar definitivamente esta actividad?')" class="mt-3 w-full rounded-xl bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-400 transition hover:bg-red-500/20">
                        Rechazar actividad
                    </button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection