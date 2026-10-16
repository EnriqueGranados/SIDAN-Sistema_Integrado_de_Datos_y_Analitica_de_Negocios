
@extends(auth()->user()?->rol?->nombre === 'usuario' ? 'layouts.user' : 'layouts.navbars')

@section('title', 'Mis inscripciones - SIDAN')

@section(auth()->user()?->rol?->nombre === 'usuario' ? 'user-content' : 'content')
    <div class="space-y-6"
        x-data="{
            filtro: 'todas',
            cancelarId: null,
            cancelarNombre: ''
        }">
        <div class="mx-auto max-w-7xl px-4 py-8 text-white">
        {{-- Encabezado --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                    Mi cuenta · SIDAN
                </p>

                <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">
                    Mis inscripciones
                </h1>

                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Consulta tus actividades, revisa el estado de tus inscripciones
                    y administra tu participación.
                </p>
            </div>

            @if (auth()->user()?->rol?->nombre === 'usuario')
                <a href="{{ route('user.ordenes.index') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl
                           border border-slate-200 px-5 py-3 text-sm font-bold
                           text-slate-700 transition hover:bg-slate-100
                           dark:border-white/10 dark:text-slate-200
                           dark:hover:bg-white/5">

                    <svg class="h-4 w-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7h18M5 7l1 13h12l1-13M9 11h6M9 15h6" />
                    </svg>

                    Ver mis órdenes
                </a>
            @endif
        </div>

        {{-- Notificaciones --}}
        @if (session('success'))
            <div role="status"
                class="flex items-start gap-3 rounded-xl border border-emerald-200
                       bg-emerald-50 p-4 text-sm text-emerald-800
                       dark:border-emerald-500/20 dark:bg-emerald-500/10
                       dark:text-emerald-300">

                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 13l4 4L19 7" />
                </svg>

                <p class="font-semibold">
                    {{ session('success') }}
                </p>
            </div>
        @endif

        @if ($errors->any())
            <div role="alert"
                class="flex items-start gap-3 rounded-xl border border-red-200
                       bg-red-50 p-4 text-sm text-red-800
                       dark:border-red-500/20 dark:bg-red-500/10
                       dark:text-red-300">

                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v4m0 4h.01M10.3 4.3 2.6 17.5A2 2 0 004.3 20h15.4a2 2 0 001.7-2.5L13.7 4.3a2 2 0 00-3.4 0Z" />
                </svg>

                <p class="font-semibold">
                    {{ $errors->first() }}
                </p>
            </div>
        @endif

        {{-- Filtros --}}
        <div
            class="rounded-2xl border border-slate-200 bg-white p-4 mt-4 mb-4 shadow-sm
                   dark:border-white/10 dark:bg-white/5 sm:p-5">

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-sm font-black text-slate-900 dark:text-white">
                        Filtrar inscripciones
                    </h2>

                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Selecciona un estado para consultar tus registros.
                    </p>
                </div>

                <span
                    class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold
                           text-slate-600 dark:bg-white/10 dark:text-slate-300">
                    {{ $inscripciones->count() }}
                    inscripción(es)
                </span>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'todas' => 'Todas',
                    'activa' => 'Activas',
                    'pendiente' => 'Pendientes',
                    'pendiente_revision' => 'En revisión',
                    'finalizada' => 'Finalizadas',
                    'vencida' => 'Vencidas',
                    'cancelada' => 'Canceladas'
                ] as $key => $etiqueta)

                    <button type="button"
                        @click="filtro = '{{ $key }}'"
                        :aria-pressed="filtro === '{{ $key }}'"
                        :class="filtro === '{{ $key }}'
                            ? 'bg-emerald-600 text-white shadow-sm'
                            : 'bg-slate-50 text-slate-600 hover:bg-slate-100 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800'"
                        class="rounded-xl border border-transparent px-4 py-2.5
                               text-sm font-bold transition
                               focus:outline-none focus:ring-2 focus:ring-emerald-500/30">

                        {{ $etiqueta }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Listado de inscripciones --}}
        <div class="space-y-4">
            @forelse ($inscripciones as $inscripcion)
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
                        'activa' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                        'pendiente' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                        'finalizada' => 'bg-slate-500/10 text-slate-700 dark:text-slate-300',
                        'cancelada' => 'bg-red-500/10 text-red-700 dark:text-red-300',
                        'vencida' => 'bg-red-500/10 text-red-700 dark:text-red-300',
                        'pendiente_revision' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
                    ];

                    $nombreActividad = $inscripcion->actividad?->nombre
                        ?? 'Actividad no disponible';

                    $fechaActividad = $inscripcion->actividad?->realizacion_desde;
                @endphp

                <article
                    x-show="
                        filtro === 'todas' ||
                        filtro === '{{ $estado }}' ||
                        (
                            filtro === 'pendiente' &&
                            '{{ $estado }}' === 'pendiente_revision'
                        )
                    "
                    x-cloak
                    class="overflow-hidden rounded-2xl border border-slate-200
                           bg-white shadow-sm transition-colors
                           dark:border-white/10 dark:bg-slate-900">

                    {{-- Cabecera de tarjeta --}}
                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col justify-between gap-5 lg:flex-row">

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">

                                    <span
                                        class="text-sm font-black text-slate-900 dark:text-white">
                                        Inscripción #{{ $inscripcion->id_inscripcion }}
                                    </span>

                                    <span
                                        class="inline-flex items-center rounded-full
                                               px-3 py-1 text-xs font-black
                                               {{ $clases[$estado] ?? 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300' }}">
                                        {{ $etiquetas[$estado] ?? ucfirst(str_replace('_', ' ', $estado)) }}
                                    </span>
                                </div>

                                <h2 class="mt-3 text-xl font-black leading-snug
                                           text-slate-900 dark:text-white">
                                    {{ $nombreActividad }}
                                </h2>

                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    Registrada el
                                    {{ $inscripcion->created_at?->format('d/m/Y H:i') ?? '—' }}
                                </p>
                            </div>

                            {{-- Resumen rápido --}}
                            <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[300px]">
                                <div
                                    class="rounded-xl bg-slate-50 p-4
                                           dark:bg-white/5">

                                    <p class="text-xs font-semibold text-slate-500
                                              dark:text-slate-400">
                                        Fecha de actividad
                                    </p>

                                    <p class="mt-1 text-sm font-black
                                              text-slate-900 dark:text-white">
                                        {{ $fechaActividad?->format('d/m/Y') ?? 'Por definir' }}
                                    </p>
                                </div>

                                <div
                                    class="rounded-xl bg-slate-50 p-4
                                           dark:bg-white/5">

                                    <p class="text-xs font-semibold text-slate-500
                                              dark:text-slate-400">
                                        Hora
                                    </p>

                                    <p class="mt-1 text-sm font-black
                                              text-slate-900 dark:text-white">
                                        {{ $fechaActividad?->format('H:i') ?? 'Por definir' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Información según el estado --}}
                    @if ($estado === 'pendiente')
                        <div
                            class="flex items-start gap-3 border-t border-amber-200
                                   bg-amber-50 px-5 py-4 text-sm
                                   dark:border-amber-500/20 dark:bg-amber-500/10 sm:px-6">

                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600
                                        dark:text-amber-400"
                                fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" stroke-width="2">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 7v5l3 2" />
                            </svg>

                            <div>
                                <p class="font-bold text-amber-800 dark:text-amber-300">
                                    Inscripción pendiente de confirmación
                                </p>

                                <p class="mt-1 text-amber-700 dark:text-amber-200">
                                    Completa el pago
                                    @if ($inscripcion->expira_en)
                                        antes del
                                        {{ $inscripcion->expira_en->format('d/m/Y H:i') }}.
                                    @else
                                        para confirmar tu participación.
                                    @endif
                                </p>
                            </div>
                        </div>

                    @elseif ($estado === 'pendiente_revision')
                        <div
                            class="flex items-start gap-3 border-t border-sky-200
                                   bg-sky-50 px-5 py-4 text-sm
                                   dark:border-sky-500/20 dark:bg-sky-500/10 sm:px-6">

                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-sky-600
                                        dark:text-sky-400"
                                fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" stroke-width="2">
                                <circle cx="12" cy="12" r="9" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 11v5m0-8h.01" />
                            </svg>

                            <div>
                                <p class="font-bold text-sky-800 dark:text-sky-300">
                                    Pago en revisión
                                </p>

                                <p class="mt-1 text-sky-700 dark:text-sky-200">
                                    El pago necesita verificación de cupo.
                                    Contacta a soporte para más información.
                                </p>
                            </div>
                        </div>

                    @elseif ($estado === 'cancelada')
                        <div
                            class="border-t border-slate-200 bg-slate-50
                                   px-5 py-3 text-xs text-slate-500
                                   dark:border-white/10 dark:bg-white/5
                                   dark:text-slate-400 sm:px-6">

                            La cancelación no genera devolución de pagos realizados.
                        </div>

                    @elseif ($estado === 'vencida')
                        <div
                            class="border-t border-red-200 bg-red-50
                                   px-5 py-3 text-sm font-semibold
                                   text-red-700 dark:border-red-500/20
                                   dark:bg-red-500/10 dark:text-red-300 sm:px-6">

                            El plazo para confirmar esta inscripción ha vencido.
                        </div>
                    @endif

                    {{-- Acciones --}}
                    <div
                        class="flex flex-wrap items-center justify-end gap-3
                               border-t border-slate-200 bg-slate-50/70
                               px-5 py-4 dark:border-white/10
                               dark:bg-white/5 sm:px-6">

                        <a href="{{ route('sidan.inscripciones.show', $inscripcion->id_inscripcion) }}"
                            class="inline-flex items-center justify-center gap-2
                                   rounded-xl border border-[#102b54]/20 bg-white
                                   px-4 py-2.5 text-sm font-bold text-[#102b54]
                                   transition hover:bg-slate-100
                                   focus:outline-none focus:ring-2 focus:ring-[#102b54]/30
                                   dark:border-white/20 dark:bg-slate-900
                                   dark:text-white dark:hover:bg-slate-800">

                            <svg class="h-4 w-4" fill="none"
                                stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 12s3.5-7 9-7 9 7-3.5 7-9 7-9-7-9-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>

                            Ver inscripción
                        </a>

                        @if ($inscripcion->actividad?->slug)
                            <a href="{{ route('activities.show', ['slug' => $inscripcion->actividad->slug]) }}"
                                class="inline-flex items-center justify-center
                                       rounded-xl border border-slate-200 bg-white
                                       px-4 py-2.5 text-sm font-bold
                                       text-slate-700 transition hover:bg-slate-100
                                       dark:border-white/10 dark:bg-slate-900
                                       dark:text-slate-200 dark:hover:bg-slate-800">

                                Ver actividad
                            </a>
                        @endif

                        @if ($estado === 'pendiente' && $inscripcion->orden)
                            <a href="{{ route('sidan.ordenes.show', $inscripcion->orden->id_orden) }}"
                                class="inline-flex items-center justify-center gap-2
                                       rounded-xl bg-emerald-600 px-4 py-2.5
                                       text-sm font-black text-white
                                       transition hover:bg-emerald-700
                                       focus:outline-none focus:ring-2
                                       focus:ring-emerald-500/40">

                                Continuar al pago

                                <span aria-hidden="true">→</span>
                            </a>
                        @endif

                        @if (in_array($estado, ['activa', 'pendiente'], true))
                            <button type="button"
                                @click="
                                    cancelarId = {{ $inscripcion->id_inscripcion }};
                                    cancelarNombre = @js($nombreActividad);
                                "
                                class="inline-flex items-center justify-center gap-2
                                       rounded-xl border border-red-200
                                       bg-red-50 px-4 py-2.5 text-sm
                                       font-bold text-red-700 transition
                                       hover:bg-red-100
                                       dark:border-red-500/20
                                       dark:bg-red-500/10
                                       dark:text-red-300
                                       dark:hover:bg-red-500/20">

                                <svg class="h-4 w-4" fill="none"
                                    stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3" />
                                </svg>

                                Cancelar inscripción
                            </button>
                        @endif
                    </div>
                </article>

            @empty
                {{-- Estado vacío --}}
                <div
                    class="rounded-2xl border border-dashed border-slate-300
                           bg-white px-6 py-14 text-center
                           dark:border-white/10 dark:bg-white/5">

                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center
                               rounded-2xl bg-emerald-50 text-emerald-600
                               dark:bg-emerald-500/10 dark:text-emerald-400">

                        <svg class="h-7 w-7" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="1.8">
                            <rect x="4" y="4" width="16" height="16" rx="3" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 9h8M8 13h5M8 17h4" />
                        </svg>
                    </div>

                    <h2 class="mt-5 text-lg font-black text-slate-900 dark:text-white">
                        Aún no tienes inscripciones
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm leading-6
                              text-slate-500 dark:text-slate-400">
                        Explora las actividades disponibles e inscríbete
                        en las que más te interesen.
                    </p>

                    <a href="{{ route('activities.index') }}"
                        class="mt-5 inline-flex items-center justify-center
                               rounded-xl bg-emerald-600 px-5 py-3
                               text-sm font-bold text-white
                               transition hover:bg-emerald-700">

                        Explorar actividades
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Modal de cancelación --}}
        <div x-show="cancelarId !== null"
            x-cloak
            x-transition.opacity.duration.200ms
            @keydown.escape.window="cancelarId = null"
            @click.self="cancelarId = null"
            class="fixed inset-0 z-[200] flex items-center justify-center
                   bg-slate-950/60 p-4 backdrop-blur-sm">

            <div role="dialog"
                aria-modal="true"
                aria-labelledby="titulo-cancelacion"
                class="w-full max-w-md rounded-2xl border border-slate-200
                       bg-white p-6 shadow-2xl
                       dark:border-white/10 dark:bg-[#0f172a]">

                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center
                               justify-center rounded-xl bg-red-50
                               text-red-600 dark:bg-red-500/10
                               dark:text-red-400">

                        <svg class="h-6 w-6" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.3 4.3 2.6 17.5A2 2 0 004.3 20h15.4a2 2 0 001.7-2.5L13.7 4.3a2 2 0 00-3.4 0Z" />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 id="titulo-cancelacion"
                            class="text-lg font-black text-slate-900 dark:text-white">
                            ¿Cancelar inscripción?
                        </h2>

                        <p class="mt-2 text-sm leading-6
                                  text-slate-600 dark:text-slate-400">
                            Dejarás de participar en

                            <span x-text="cancelarNombre"
                                class="font-bold text-slate-900 dark:text-white">
                            </span>.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 rounded-xl border border-red-200
                           bg-red-50 p-4 dark:border-red-500/20
                           dark:bg-red-500/10">

                    <p class="text-sm leading-6 text-red-700 dark:text-red-300">
                        Si ya realizaste un pago,
                        <strong>no habrá devolución</strong>.
                        Esta acción no se puede deshacer.
                    </p>
                </div>

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button type="button"
                        @click="cancelarId = null"
                        class="inline-flex items-center justify-center
                               rounded-xl border border-slate-200
                               bg-white px-5 py-3 text-sm font-bold
                               text-slate-700 transition hover:bg-slate-100
                               dark:border-white/10 dark:bg-white/5
                               dark:text-slate-200 dark:hover:bg-white/10">

                        Volver
                    </button>

                    <form method="POST"
                        :action="'{{ url('/user/inscripciones') }}/' + cancelarId + '/cancelar'">
                        @csrf
                        @method('PATCH')

                        <button type="submit"
                            class="inline-flex items-center justify-center
                                   rounded-xl bg-red-600 px-5 py-3
                                   text-sm font-black text-white
                                   transition hover:bg-red-500">

                            Sí, cancelar inscripción
                        </button>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection
