@php
    $actividad = $espacio->actividad;
    $estado = $actividad?->estado_welcome;
    $badge = match ($estado['estado'] ?? null) {
        'visible' => 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300',
        'programada' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
        default => 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300',
    };
@endphp

<article
    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]"
    data-welcome-slot
>
    <div class="flex items-center gap-3 px-4 py-3 sm:px-5">
        <button
            type="button"
            class="js-toggle-welcome-slot flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:text-slate-300"
            aria-expanded="false"
            title="Mostrar detalles"
        >
            <svg class="js-slot-chevron h-4 w-4 transition-transform duration-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <span class="text-[10px] font-black uppercase tracking-[0.14em] text-sidan-500">Posición {{ $espacio->orden }}</span>
                <span class="text-xs font-black text-slate-400">•</span>
                <span class="truncate text-sm font-black text-sidan-900 dark:text-white">{{ $espacio->nombre }}</span>

                @if ($actividad && $estado)
                    <span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $badge }}">{{ $estado['texto'] }}</span>
                @elseif (!$actividad)
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-500 dark:bg-white/10 dark:text-slate-300">Vacío</span>
                @endif
            </div>

            <div class="mt-1 flex min-w-0 items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                @if ($actividad)
                    <span class="truncate font-bold text-slate-700 dark:text-slate-200">{{ $actividad->nombre }}</span>
                    <span class="shrink-0 text-slate-300 dark:text-slate-600">•</span>
                    <span class="truncate">{{ $actividad->categoria?->nombre ?? 'Sin categoría' }}</span>
                @else
                    <span>Sin actividad asignada</span>
                @endif
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1.5">
            <button
                type="button"
                data-url="{{ route('admin.welcome.espacios.mover', $espacio) }}"
                data-direction="arriba"
                class="js-mover-espacio flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-sm font-black text-slate-500 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10"
                title="Mover arriba"
            >↑</button>

            <button
                type="button"
                data-url="{{ route('admin.welcome.espacios.mover', $espacio) }}"
                data-direction="abajo"
                class="js-mover-espacio flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-sm font-black text-slate-500 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10"
                title="Mover abajo"
            >↓</button>

            <button
                type="button"
                data-assign-url="{{ route('admin.welcome.espacios.asignar', $espacio) }}"
                data-slot-name="{{ $espacio->nombre }}"
                class="js-cambiar-actividad hidden rounded-xl border border-amber-400/40 bg-amber-400/10 px-3 py-2 text-xs font-black text-amber-600 transition hover:bg-amber-400 hover:text-slate-950 dark:text-amber-300 sm:inline-flex"
            >
                {{ $actividad ? 'Cambiar' : 'Asignar' }}
            </button>

            <button
                type="button"
                data-url="{{ route('admin.welcome.espacios.destroy', $espacio) }}"
                class="js-eliminar-espacio flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 text-base font-black text-red-500 transition hover:bg-red-500 hover:text-white dark:border-red-500/20"
                title="Eliminar espacio"
            >×</button>
        </div>
    </div>

    <div class="js-welcome-slot-panel hidden border-t border-slate-200 bg-slate-50/70 p-4 dark:border-white/10 dark:bg-sidan-950/30 sm:p-5">
        @if ($actividad)
            <div class="grid gap-4 lg:grid-cols-[180px_minmax(0,1fr)_auto] lg:items-center">
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-white/5">
                    <img
                        src="{{ $actividad->imagen_welcome }}"
                        alt="{{ $actividad->nombre }}"
                        class="h-28 w-full object-cover lg:h-24"
                    >
                </div>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[11px] font-black uppercase tracking-wide text-sidan-500">{{ $actividad->categoria?->nombre ?? 'Sin categoría' }}</span>
                        @if ($estado)
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-black {{ $badge }}">{{ $estado['texto'] }}</span>
                        @endif
                    </div>
                    <p class="mt-1 truncate text-base font-black text-sidan-900 dark:text-white">{{ $actividad->nombre }}</p>
                    @if ($estado)
                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $estado['detalle'] }}</p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2 lg:justify-end">
                    <button
                        type="button"
                        data-assign-url="{{ route('admin.welcome.espacios.asignar', $espacio) }}"
                        data-slot-name="{{ $espacio->nombre }}"
                        class="js-cambiar-actividad rounded-xl bg-sidan-500 px-4 py-2.5 text-xs font-black text-white transition hover:bg-green-600"
                    >
                        Cambiar actividad
                    </button>
                    <button
                        type="button"
                        data-url="{{ route('admin.welcome.espacios.quitar', $espacio) }}"
                        class="js-quitar-actividad rounded-xl border border-red-200 px-4 py-2.5 text-xs font-black text-red-600 transition hover:bg-red-500 hover:text-white dark:border-red-500/20 dark:text-red-300"
                    >
                        Quitar actividad
                    </button>
                </div>
            </div>
        @else
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-black text-sidan-900 dark:text-white">Este espacio está vacío</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Asígnale una actividad publicada o programada para usar esta posición.</p>
                </div>
                <button
                    type="button"
                    data-assign-url="{{ route('admin.welcome.espacios.asignar', $espacio) }}"
                    data-slot-name="{{ $espacio->nombre }}"
                    class="js-cambiar-actividad rounded-xl bg-sidan-500 px-4 py-2.5 text-xs font-black text-white transition hover:bg-green-600"
                >
                    Seleccionar actividad
                </button>
            </div>
        @endif
    </div>
</article>
