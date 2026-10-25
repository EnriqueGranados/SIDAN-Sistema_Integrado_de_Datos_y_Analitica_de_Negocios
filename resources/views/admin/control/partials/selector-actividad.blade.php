{{-- Selector de actividad compartido por Control de inscripciones, órdenes y consolidado.
     Sigue el patrón visual de Gestión de pagos. La consulta se actualiza al enviar el formulario. --}}
@php
    $valorActividad = (string) request('actividad', '');
    $nombreInicial = $valorActividad === ''
        ? 'Todas las actividades, incluso las que ya finalizaron.'
        : ($actividades->firstWhere('id_actividad', (int) $valorActividad)?->nombre ?? 'Actividad seleccionada');
@endphp
<div class="col-span-full rounded-xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-slate-900 sm:p-4"
    x-data="{
        abierto: false,
        actividadElegidaId: @js($valorActividad),
        actividadElegida: @js($nombreInicial),
        busqueda: '',
        cambioSinAplicar: false,
        coincide(nombre) {
            return nombre.toLocaleLowerCase('es').includes(this.busqueda.trim().toLocaleLowerCase('es'));
        }
    }"
    @keydown.escape.window="abierto = false">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Actividad</p>
            <p class="mt-1 break-words text-sm font-semibold text-slate-700 dark:text-slate-200"
                x-text="actividadElegida" aria-live="polite">{{ $nombreInicial }}</p>
            <p x-show="cambioSinAplicar" x-cloak
                class="mt-1 text-xs font-medium text-amber-700 dark:text-amber-300" aria-live="polite">
                Actividad seleccionada. Aplicá los filtros para actualizar los resultados.
            </p>
        </div>
        <button type="button" @click="abierto = !abierto; if (abierto) busqueda = ''"
            :aria-expanded="abierto.toString()" aria-controls="control-lista-actividades"
            class="inline-flex items-center gap-2 rounded-xl border border-sidan-500 px-4 py-2.5 text-sm font-black text-sidan-600 transition hover:bg-sidan-500 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-sidan-500/30 dark:text-sidan-300 dark:hover:text-white">
            <span x-text="abierto ? 'Ocultar actividades' : (actividadElegidaId ? 'Cambiar actividad' : 'Seleccionar actividad')"></span>
            <svg class="h-4 w-4 transition-transform" :class="abierto ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </button>
    </div>

    <div id="control-lista-actividades" x-show="abierto" x-cloak x-transition
        class="mt-4 space-y-3 border-t border-slate-100 pt-4 dark:border-white/10">
        <label for="control-buscar-actividad" class="sr-only">Buscar actividad</label>
        <input id="control-buscar-actividad" type="search" x-model="busqueda" @keydown.enter.prevent
            placeholder="Buscar actividad por nombre..."
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-sidan-500 focus:ring-sidan-500 dark:border-white/10 dark:bg-slate-800 dark:text-white dark:placeholder:text-slate-400">

        <div class="max-h-[380px] space-y-2 overflow-y-auto pr-1">
            <label class="block cursor-pointer" x-show="coincide('Todas las actividades')">
                <input type="radio" name="actividad" value=""
                    data-nombre="Todas las actividades, incluso las que ya finalizaron."
                    class="peer sr-only" @checked($valorActividad === '')
                    @change="actividadElegidaId = ''; actividadElegida = $event.target.dataset.nombre; cambioSinAplicar = true; abierto = false">
                <span class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-semibold text-slate-700 transition hover:border-sidan-400 hover:bg-sidan-50/40 peer-checked:border-sidan-500 peer-checked:bg-sidan-50 peer-focus-visible:ring-2 peer-focus-visible:ring-sidan-500/30 dark:border-white/10 dark:text-slate-200 dark:hover:bg-sidan-500/5 dark:peer-checked:bg-sidan-500/10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sidan-600 dark:bg-white/10 dark:text-sidan-300" aria-hidden="true">✦</span>
                    Todas las actividades
                </span>
            </label>

            @forelse ($actividades as $actividad)
                @php
                    // Mismo criterio de imagen de portada y estado que usa Gestión de pagos.
                    $rutaPortada = str_replace('\\', '/', trim((string) ($actividad->portada?->url ?? '')));
                    $imagenPortada = null;
                    if ($rutaPortada !== '') {
                        if (\Illuminate\Support\Str::startsWith($rutaPortada, ['https://', 'http://', 'data:'])) {
                            $imagenPortada = $rutaPortada;
                        } else {
                            $rutaPortada = ltrim($rutaPortada, '/');
                            if (\Illuminate\Support\Str::startsWith($rutaPortada, 'public/')) {
                                $rutaPortada = substr($rutaPortada, 7);
                            }
                            $imagenPortada = asset(\Illuminate\Support\Str::startsWith($rutaPortada, 'storage/') ? $rutaPortada : 'storage/' . $rutaPortada);
                        }
                    }
                    $finalizada = $actividad->realizacion_hasta
                        ? $actividad->realizacion_hasta->isPast()
                        : ($actividad->realizacion_desde && $actividad->realizacion_desde->isPast());
                @endphp
                <label class="block cursor-pointer" x-show="coincide(@js($actividad->nombre))">
                    <input type="radio" name="actividad" value="{{ $actividad->id_actividad }}"
                        data-nombre="{{ $actividad->nombre }}"
                        class="peer sr-only" @checked($valorActividad === (string) $actividad->id_actividad)
                        @change="actividadElegidaId = $event.target.value; actividadElegida = $event.target.dataset.nombre; cambioSinAplicar = true; abierto = false">
                    <span class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-sidan-400 hover:bg-sidan-50/40 peer-checked:border-sidan-500 peer-checked:bg-sidan-50 peer-focus-visible:ring-2 peer-focus-visible:ring-sidan-500/30 dark:border-white/10 dark:hover:bg-sidan-500/5 dark:peer-checked:bg-sidan-500/10">
                        @if ($imagenPortada)
                            <img src="{{ $imagenPortada }}" alt="Portada de {{ $actividad->nombre }}" loading="lazy"
                                class="h-16 w-16 shrink-0 rounded-lg object-cover sm:h-20 sm:w-20">
                        @else
                            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-sidan-900 text-sidan-300 sm:h-20 sm:w-20" aria-hidden="true">✦</span>
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wide text-sidan-600 dark:text-sidan-400">{{ $actividad->categoria?->nombre ?? 'Sin categoría' }}</span>
                                @if ($finalizada)
                                    <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">Finalizada</span>
                                @elseif ($actividad->trashed())
                                    <span class="rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Archivada</span>
                                @else
                                    <span class="rounded-full bg-sidan-50 px-2 py-1 text-[10px] font-bold text-sidan-700 dark:bg-sidan-500/10 dark:text-sidan-300">Registrada</span>
                                @endif
                            </span>
                            <strong class="mt-1 block break-words text-sm text-slate-900 dark:text-white">{{ $actividad->nombre }}</strong>
                            <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">
                                {{ $actividad->realizacion_hasta?->copy()->timezone('America/El_Salvador')->format('d/m/Y') ?? 'Sin fecha de cierre configurada' }}
                            </span>
                        </span>
                    </span>
                </label>
            @empty
                <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No hay actividades registradas.</p>
            @endforelse
            <p x-show="busqueda.trim() !== '' && !coincide('Todas las actividades') && !@js($actividades->pluck('nombre')->values()->all()).some(nombre => coincide(nombre))"
                x-cloak class="py-4 text-center text-sm text-slate-500 dark:text-slate-400">
                No se encontraron actividades con ese nombre.
            </p>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Después de elegir, aplicá los filtros para actualizar el listado.</p>
    </div>
</div>
