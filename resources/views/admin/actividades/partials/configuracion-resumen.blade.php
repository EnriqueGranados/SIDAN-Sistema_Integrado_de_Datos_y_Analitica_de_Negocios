@php
    $mediosActividad = $actividad->medios->whereNull('id_item_actividad')->whereNull('id_sesion');
    $portada = $mediosActividad->firstWhere('es_portada',true);
    $galeria = $mediosActividad->where('es_portada',false);

    $productosHabilitados = ($configuracionProductos['enabled'] ?? false) === true;
    $totalProductos = $actividad->items->count();
    $totalVariantes = $actividad->items->sum(fn ($item) => $item->variantes->count());

    $totalCampos = 0;
    $productosConDatos = 0;
    $productosConAjustes = 0;

    foreach ($actividad->items as $item) {
        $configDatos = $configuracionesDatos->get((string) $item->id_item_actividad);

        if ($configDatos && ($configDatos['enabled'] ?? false)) {
            $productosConDatos++;
            $totalCampos += count($configDatos['fields'] ?? []);
        }

        $configPrecio = $configuracionesPrecios->get((string) $item->id_item_actividad);

        if ($configPrecio && ($configPrecio['enabled'] ?? false)) {
            $productosConAjustes++;
        }
    }

    $modoResumen = $modoSesiones ?? ($configuracionSesiones['mode'] ?? 'ninguna');

    $textoModoSesiones = match ($modoResumen) {
        'unica' => 'Una sesión',
        'multiples' => 'Varias sesiones',
        default => 'Sin sesiones',
    };

    $totalSesiones = $actividad->sesiones->count();

    $primeraSesion = $actividad->sesiones->sortBy('fecha_inicio')->first();
    $ultimaSesion = $actividad->sesiones->sortByDesc(fn ($sesion) => $sesion->fecha_fin ?: $sesion->fecha_inicio)->first();
    $ubicacionGeneral = $actividad->espacio?->nombre ?: ($actividad->ubicacion_externa ?: 'Sin ubicación definida');
    $tipoUbicacionGeneral = $actividad->espacio ? 'Espacio registrado' : ($actividad->ubicacion_externa ? 'Lugar externo' : 'Sin definir');
    $totalRecursosSesiones = $actividad->sesiones->sum(fn ($sesion) => $sesion->recursos->count());

    $urlMedio = function ($url) {
        if (!$url) {
            return '';
        }

        if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $ruta = ltrim($url, '/');

        if (\Illuminate\Support\Str::startsWith($ruta, 'storage/')) {
            return asset($ruta);
        }

        return asset('storage/' . $ruta);
    };
@endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Datos generales</p>
                <h2 class="mt-1 text-lg font-semibold text-white">{{ $actividad->nombre }}</h2>
            </div>
            <a href="{{ route('admin.actividades.edit',$actividad) }}" class="rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-white/[0.04]">Editar datos generales</a>
        </div>
        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
            <div class="rounded-xl border border-white/10 bg-black/10 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Categoría</p>
                <p class="mt-1 text-sm font-medium text-gray-300">{{ $actividad->categoria?->nombre ?: 'Sin categoría' }}</p>
            </div>
            <div class="rounded-xl border border-white/10 bg-black/10 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Etiquetas</p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @forelse ($actividad->etiquetas as $etiqueta)
                        <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-300">{{ $etiqueta->nombre }}</span>
                    @empty
                        <span class="text-sm text-gray-500">Sin etiquetas</span>
                    @endforelse
                </div>
            </div>
            <div class="rounded-xl border border-white/10 bg-black/10 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Ubicación general</p>
                <p class="mt-1 text-sm font-medium text-gray-300">{{ $ubicacionGeneral }}</p>
                <p class="mt-1 text-xs text-gray-600">{{ $tipoUbicacionGeneral }}@if($actividad->espacio?->capacidad) · Capacidad {{ $actividad->espacio->capacidad }}@endif</p>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-4 sm:p-5">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-lg text-emerald-300">
                ✓
            </div>

            <div>
                <h2 class="font-semibold text-emerald-300">
                    Revisa la configuración
                </h2>

                <p class="mt-1 text-sm leading-6 text-gray-400">
                    Confirma que todo esté correcto antes de finalizar. Después podrás enviar la actividad a revisión desde el listado.
                </p>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]">
        <details class="group" open>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-4 sm:p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-xs font-bold text-blue-300">
                            1
                        </span>

                        <h3 class="font-semibold text-white">
                            Presentación
                        </h3>

                        <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
                            Completa
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-gray-500">
                        Portada y {{ $galeria->count() }} imagen{{ $galeria->count() === 1 ? '' : 'es' }} adicional{{ $galeria->count() === 1 ? '' : 'es' }}
                    </p>
                </div>

                <span class="text-gray-500 transition group-open:rotate-180">
                    ⌄
                </span>
            </summary>

            <div class="border-t border-white/10 p-4 sm:p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    @if ($portada)
                        <img
                            src="{{ $urlMedio($portada->url) }}"
                            alt="{{ $portada->texto_alternativo ?: $actividad->nombre }}"
                            class="h-32 w-full rounded-xl object-cover sm:w-52">
                    @endif

                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">
                            Portada
                        </p>

                        <p class="mt-1 truncate font-semibold text-white">
                            {{ $portada?->texto_alternativo ?: $actividad->nombre }}
                        </p>

                        @if ($galeria->isNotEmpty())
                            <p class="mt-2 text-sm text-gray-500">
                                La galería contiene {{ $galeria->count() }} imagen{{ $galeria->count() === 1 ? '' : 'es' }}.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="mt-4">
                    <a href="{{ route('admin.actividades.configurar',['actividad' => $actividad->id_actividad,'paso' => 'presentacion']) }}"
                        class="inline-flex rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-white/[0.04]">
                        Editar presentación
                    </a>
                </div>
            </div>
        </details>
    </div>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]">
        <details class="group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-4 sm:p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-xs font-bold text-blue-300">
                            2
                        </span>

                        <h3 class="font-semibold text-white">
                            Productos o servicios
                        </h3>

                        <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
                            Completo
                        </span>
                    </div>

                    <p class="mt-2 text-sm text-gray-500">
                        @if ($productosHabilitados)
                            {{ $totalProductos }} producto{{ $totalProductos === 1 ? '' : 's' }} o servicio{{ $totalProductos === 1 ? '' : 's' }}
                            · {{ $totalVariantes }} variante{{ $totalVariantes === 1 ? '' : 's' }}
                        @else
                            La actividad no ofrecerá productos ni servicios
                        @endif
                    </p>
                </div>

                <span class="text-gray-500 transition group-open:rotate-180">
                    ⌄
                </span>
            </summary>

            <div class="border-t border-white/10 p-4 sm:p-5">
                @if ($productosHabilitados && $actividad->items->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($actividad->items->sortBy('orden')->take(5) as $item)
                            <div class="flex flex-col gap-2 rounded-xl border border-white/10 bg-black/10 p-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-white">
                                        {{ $item->nombre }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ ucfirst($item->tipo ?? 'servicio') }}
                                        · {{ $item->variantes->count() }} variante{{ $item->variantes->count() === 1 ? '' : 's' }}
                                    </p>
                                </div>

                                <div class="shrink-0 text-sm font-semibold text-gray-300">
                                    ${{ number_format((float) $item->precio, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($totalProductos > 5)
                        <p class="mt-3 text-xs text-gray-600">
                            Se muestran 5 de {{ $totalProductos }} productos o servicios.
                        </p>
                    @endif
                @else
                    <p class="text-sm text-gray-400">
                        Se configuró esta actividad sin productos ni servicios adicionales.
                    </p>
                @endif

                <div class="mt-4">
                    <a href="{{ route('admin.actividades.configurar',['actividad' => $actividad->id_actividad,'paso' => 'productos']) }}"
                        class="inline-flex rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-white/[0.04]">
                        Editar productos
                    </a>
                </div>
            </div>
        </details>
    </div>

    @if ($productosHabilitados)
        <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]">
            <details class="group">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-4 sm:p-5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-xs font-bold text-blue-300">
                                3
                            </span>

                            <h3 class="font-semibold text-white">
                                Datos solicitados
                            </h3>

                            <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
                                Completo
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-gray-500">
                            {{ $productosConDatos }} producto{{ $productosConDatos === 1 ? '' : 's' }} solicita{{ $productosConDatos === 1 ? '' : 'n' }} datos
                            · {{ $totalCampos }} campo{{ $totalCampos === 1 ? '' : 's' }}
                        </p>
                    </div>

                    <span class="text-gray-500 transition group-open:rotate-180">
                        ⌄
                    </span>
                </summary>

                <div class="border-t border-white/10 p-4 sm:p-5">
                    <div class="space-y-3">
                        @foreach ($actividad->items->sortBy('orden')->take(5) as $item)
                            @php
                                $configDatos = $configuracionesDatos->get((string) $item->id_item_actividad);
                                $campos = collect($configDatos['fields'] ?? []);
                            @endphp

                            <div class="rounded-xl border border-white/10 bg-black/10 p-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-semibold text-white">
                                        {{ $item->nombre }}
                                    </p>

                                    @if ($configDatos && ($configDatos['enabled'] ?? false))
                                        <span class="text-xs font-semibold text-blue-300">
                                            {{ $campos->count() }} campo{{ $campos->count() === 1 ? '' : 's' }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">
                                            Sin datos adicionales
                                        </span>
                                    @endif
                                </div>

                                @if ($campos->isNotEmpty())
                                    <p class="mt-2 text-xs leading-5 text-gray-500">
                                        {{ $campos->pluck('label')->filter()->take(4)->implode(' · ') }}
                                        @if ($campos->count() > 4)
                                            · +{{ $campos->count() - 4 }} más
                                        @endif
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.actividades.configurar', [
                            'actividad' => $actividad->id_actividad,
                            'paso' => 'datos'
                        ]) }}"
                            class="inline-flex rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-white/[0.04]">
                            Editar datos solicitados
                        </a>
                    </div>
                </div>
            </details>
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]">
            <details class="group">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-4 sm:p-5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-xs font-bold text-blue-300">
                                4
                            </span>

                            <h3 class="font-semibold text-white">
                                Precios y costos
                            </h3>

                            <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
                                Completo
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-gray-500">
                            {{ $productosConAjustes }} producto{{ $productosConAjustes === 1 ? '' : 's' }} con aumentos configurados
                        </p>
                    </div>

                    <span class="text-gray-500 transition group-open:rotate-180">
                        ⌄
                    </span>
                </summary>

                <div class="border-t border-white/10 p-4 sm:p-5">
                    <div class="space-y-3">
                        @foreach ($actividad->items->sortBy('orden')->take(5) as $item)
                            @php
                                $configPrecio = $configuracionesPrecios->get((string) $item->id_item_actividad);
                                $reglas = collect($configPrecio['rules'] ?? []);
                            @endphp

                            <div class="flex flex-col gap-2 rounded-xl border border-white/10 bg-black/10 p-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-white">
                                        {{ $item->nombre }}
                                    </p>

                                    <p class="mt-1 text-xs text-gray-500">
                                        Base ${{ number_format((float) $item->precio, 2) }}
                                        · Costo ${{ number_format((float) $item->costo_referencia, 2) }}
                                    </p>
                                </div>

                                <div class="text-xs text-gray-400">
                                    @if ($configPrecio && ($configPrecio['enabled'] ?? false))
                                        {{ $reglas->count() }} ajuste{{ $reglas->count() === 1 ? '' : 's' }}
                                    @else
                                        Precio base
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.actividades.configurar', [
                            'actividad' => $actividad->id_actividad,
                            'paso' => 'precios'
                        ]) }}"
                            class="inline-flex rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-white/[0.04]">
                            Editar precios
                        </a>
                    </div>
                </div>
            </details>
        </div>
    @else
        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-xs font-bold text-blue-300">
                    3
                </span>

                <div>
                    <h3 class="font-semibold text-white">
                        Datos solicitados
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        No aplica porque la actividad no ofrece productos o servicios.
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-xs font-bold text-blue-300">
                    4
                </span>

                <div>
                    <h3 class="font-semibold text-white">
                        Precios y costos
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        No aplica porque la actividad no ofrece productos o servicios.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-amber-500/20 bg-amber-500/[0.03]">
        <details class="group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-4 sm:p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-xs font-bold text-amber-300">%</span>
                        <h3 class="font-semibold text-white">Promociones</h3>
                        <span class="rounded-full bg-white/[0.05] px-2.5 py-1 text-xs font-semibold text-gray-400">Opcional</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">{{ $actividad->promociones->count() }} promoción{{ $actividad->promociones->count() === 1 ? '' : 'es' }} configurada{{ $actividad->promociones->count() === 1 ? '' : 's' }}</p>
                </div>
                <span class="text-gray-500 transition group-open:rotate-180">⌄</span>
            </summary>
            <div class="border-t border-white/10 p-4 sm:p-5">
                @if ($actividad->promociones->isEmpty())
                    <p class="text-sm text-gray-400">No configuraste promociones para esta actividad.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($actividad->promociones->take(5) as $promocion)
                            <div class="flex flex-col gap-2 rounded-xl border border-white/10 bg-black/10 p-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-semibold text-white">{{ $promocion->nombre }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $promocion->codigo ?: 'Automática' }} · {{ $promocion->tipo_descuento === 'porcentaje' ? number_format((float) $promocion->valor, 2).'%' : '$'.number_format((float) $promocion->valor, 2) }}</p>
                                </div>
                                <span class="text-xs font-semibold {{ $promocion->activo ? 'text-emerald-400' : 'text-gray-500' }}">{{ $promocion->activo ? 'Activa' : 'Inactiva' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="mt-4">
                    <a href="{{ route('admin.actividades.promociones.index', $actividad) }}" class="inline-flex rounded-xl border border-amber-500/20 bg-amber-500/10 px-4 py-2 text-sm font-semibold text-amber-200 hover:bg-amber-500/15">Administrar promociones</a>
                </div>
            </div>
        </details>
    </div>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03]">
        <details class="group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 p-4 sm:p-5">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/10 text-xs font-bold text-blue-300">5</span>
                        <h3 class="font-semibold text-white">Programación</h3>
                        <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">Completa</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500">{{ $textoModoSesiones }}@if($totalSesiones > 0) · {{ $totalSesiones }} sesión{{ $totalSesiones === 1 ? '' : 'es' }} · {{ $totalRecursosSesiones }} recurso{{ $totalRecursosSesiones === 1 ? '' : 's' }} asignado{{ $totalRecursosSesiones === 1 ? '' : 's' }}@endif</p>
                </div>
                <span class="text-gray-500 transition group-open:rotate-180">⌄</span>
            </summary>
            <div class="border-t border-white/10 p-4 sm:p-5">
                @if ($modoResumen === 'ninguna')
                    <p class="text-sm text-gray-400">La actividad fue configurada sin sesiones adicionales.</p>
                @elseif ($totalSesiones > 0)
                    <div class="space-y-3">
                        @foreach ($actividad->sesiones->sortBy('orden') as $sesion)
                            @php
                                $ubicacionSesion = $sesion->espacio?->nombre ?: ($sesion->ubicacion ?: $ubicacionGeneral);
                                $tipoUbicacionSesion = $sesion->espacio ? 'Espacio registrado' : ($sesion->ubicacion ? 'Lugar externo' : 'Ubicación general');
                            @endphp
                            <div class="rounded-xl border border-white/10 bg-black/10 p-3">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-white">{{ $sesion->nombre }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $sesion->fecha_inicio ? \Carbon\Carbon::parse($sesion->fecha_inicio)->format('d/m/Y H:i') : 'Sin fecha' }}@if($sesion->fecha_fin) → {{ \Carbon\Carbon::parse($sesion->fecha_fin)->format('d/m/Y H:i') }}@endif</p>
                                    </div>
                                    @if($sesion->cupo !== null)<span class="shrink-0 rounded-full bg-white/[0.05] px-2.5 py-1 text-xs font-semibold text-gray-400">Cupo {{ $sesion->cupo }}</span>@endif
                                </div>
                                <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-2">
                                    <div class="rounded-lg border border-white/5 p-2.5">
                                        <p class="text-xs text-gray-600">Ubicación · {{ $tipoUbicacionSesion }}</p>
                                        <p class="mt-1 text-sm text-gray-300">{{ $ubicacionSesion }}</p>
                                    </div>
                                    <div class="rounded-lg border border-white/5 p-2.5">
                                        <p class="text-xs text-gray-600">Recursos</p>
                                        @if($sesion->recursos->isNotEmpty())
                                            <div class="mt-1 flex flex-wrap gap-1.5">
                                                @foreach($sesion->recursos as $uso)
                                                    <span class="rounded-full bg-white/[0.05] px-2 py-1 text-xs text-gray-300">{{ $uso->recurso?->nombre ?: 'Recurso' }} × {{ $uso->cantidad }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="mt-1 text-sm text-gray-500">Sin recursos asignados</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="mt-4"><a href="{{ route('admin.actividades.configurar',['actividad' => $actividad->id_actividad,'paso' => 'sesiones']) }}" class="inline-flex rounded-xl border border-white/10 px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-white/[0.04]">Editar programación</a></div>
            </div>
        </details>
    </div>

    <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.04] p-4 sm:p-6">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">
                    Todo listo para finalizar
                </h3>

                <p class="mt-1 max-w-2xl text-sm leading-6 text-gray-400">
                    Al finalizar, la configuración quedará marcada como completa. La actividad seguirá en borrador hasta que decidas enviarla a revisión.
                </p>
            </div>

            <form method="POST"
                action="{{ route('admin.actividades.configuracion.finalizar', $actividad) }}"
                class="shrink-0">
                @csrf

                <button type="submit"
                    class="w-full rounded-xl bg-emerald-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-400 sm:w-auto">
                    Guardar y finalizar configuración
                </button>
            </form>
        </div>
    </div>

    <div class="flex">
        <a href="{{ route('admin.actividades.configurar',['actividad' => $actividad->id_actividad,'paso' => 'sesiones']) }}"
            class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400 hover:bg-white/[0.04]">
            Volver a programación
        </a>
    </div>
</div>