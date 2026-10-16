@extends(auth()->user()?->rol?->nombre === 'usuario' ? 'layouts.user' : 'layouts.navbars')

@section('title', 'Mis inscripciones - SIDAN')

@section(auth()->user()?->rol?->nombre === 'usuario' ? 'user-content' : 'content')
    <div class="mx-auto max-w-6xl space-y-6" x-data="{ filtro: 'todas', cancelarId: null, cancelarNombre: '' }">
        <div>
            <h1 class="text-3xl font-bold">Mis inscripciones</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Consulta tus actividades y administra tus
                inscripciones.</p>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-300 bg-emerald-50 p-4 text-emerald-800" role="status">
                {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl border border-red-300 bg-red-50 p-4 text-red-800" role="alert">{{ $errors->first() }}
            </div>
        @endif

        <div class="flex flex-wrap gap-2">
            @foreach (['todas' => 'Todas', 'activa' => 'Activas', 'pendiente' => 'Pendientes', 'finalizada' => 'Finalizadas', 'cancelada' => 'Canceladas'] as $key => $etiqueta)
                <button type="button" @click="filtro = '{{ $key }}'"
                    :class="filtro === '{{ $key }}' ? 'bg-emerald-600 text-white' :
                        'bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-200'"
                    class="rounded-lg px-4 py-2 text-sm font-semibold shadow-sm">{{ $etiqueta }}</button>
            @endforeach
        </div>

        @forelse($inscripciones as $inscripcion)
            @php
                $estado = $inscripcion->estado_vista;
                $etiquetas = [
                    'activa' => 'Activa',
                    'pendiente' => 'Pendiente',
                    'finalizada' => 'Finalizada',
                    'cancelada' => 'Cancelada',
                    'vencida' => 'Vencida',
                    'pendiente_revision' => 'En revisión',
                ];
                $clases = [
                    'activa' => 'bg-emerald-100 text-emerald-800',
                    'pendiente' => 'bg-amber-100 text-amber-800',
                    'finalizada' => 'bg-slate-200 text-slate-800',
                    'cancelada' => 'bg-red-100 text-red-800',
                    'vencida' => 'bg-red-100 text-red-800',
                    'pendiente_revision' => 'bg-sky-100 text-sky-800',
                ];
            @endphp
            <article
                x-show="filtro === 'todas' || filtro === '{{ $estado }}' || (filtro === 'pendiente' && '{{ $estado }}' === 'pendiente_revision')"
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div class="space-y-2">
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $clases[$estado] }}">{{ $etiquetas[$estado] }}</span>
                        <h2 class="text-xl font-bold">{{ $inscripcion->actividad?->nombre ?? 'Actividad no disponible' }}
                        </h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Inscripción
                            #{{ $inscripcion->id_inscripcion }} · Registrada el
                            {{ $inscripcion->created_at?->format('d/m/Y') }}</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            @if ($inscripcion->actividad?->realizacion_desde)
                                Fecha de actividad: {{ $inscripcion->actividad->realizacion_desde->format('d/m/Y H:i') }}
                            @else
                                Fecha de realización por definir
                            @endif
                        </p>
                        @if ($estado === 'pendiente')
                            <p class="text-sm text-amber-700">Inscripción sin confirmar. Completa el pago antes de
                                {{ $inscripcion->expira_en?->format('d/m/Y H:i') }}.</p>
                        @elseif($estado === 'pendiente_revision')
                            <p class="text-sm text-sky-700">El pago necesita verificación de cupo. Contacta a soporte.</p>
                        @elseif($estado === 'cancelada')
                            <p class="text-sm text-slate-500">La cancelación no genera devolución de pagos realizados.</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2 md:justify-end">
                        {{-- Consultar la página de detalle y estado de esta inscripción. --}}
                        <a href="{{ route('sidan.inscripciones.show', $inscripcion->id_inscripcion) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-[#102b54] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#173e73] focus:outline-none focus:ring-2 focus:ring-[#102b54]/40 dark:bg-[#153968] dark:hover:bg-[#1d4d8b]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 12s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            Ver detalle de inscripción
                        </a>
                        @if ($inscripcion->actividad?->slug)
                            <a href="{{ route('activities.show', ['slug' => $inscripcion->actividad->slug]) }}"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold transition hover:bg-slate-100 dark:border-slate-600 dark:hover:bg-slate-800">
                                Ver detalles de la actividad
                            </a>
                        @endif
                        @if ($estado === 'pendiente' && $inscripcion->orden)
                            <a href="{{ route('sidan.ordenes.show', $inscripcion->orden->id_orden) }}"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Continuar
                                pago</a>
                        @endif
                        @if (in_array($estado, ['activa', 'pendiente'], true))
                            <button type="button"
                                @click="cancelarId = {{ $inscripcion->id_inscripcion }}; cancelarNombre = @js($inscripcion->actividad?->nombre ?? 'esta actividad')"
                                class="rounded-lg border border-red-400 px-4 py-2 text-sm font-semibold text-red-600">Cancelar
                                inscripción</button>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-500">
                Aún no tienes inscripciones asociadas a tu cuenta.
            </div>
        @endforelse

        <div x-show="cancelarId !== null" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4" role="presentation">
            <div role="dialog" aria-modal="true" aria-labelledby="titulo-cancelacion"
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h2 id="titulo-cancelacion" class="text-xl font-bold">¿Cancelar inscripción?</h2>
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">Dejarás de participar en <strong
                        x-text="cancelarNombre"></strong>. Si ya realizaste un pago, <strong>no habrá devolución</strong>.
                    Esta acción no se puede deshacer.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" @click="cancelarId = null" class="rounded-lg border px-4 py-2">Volver</button>
                    <form method="POST" :action="'{{ url('/user/inscripciones') }}/' + cancelarId + '/cancelar'">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 font-semibold text-white">Sí, cancelar
                            inscripción</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
