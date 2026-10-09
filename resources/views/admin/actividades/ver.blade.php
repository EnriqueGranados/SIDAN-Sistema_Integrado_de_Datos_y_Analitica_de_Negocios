@extends('layouts.navbars')

@section('title', 'Detalle de actividad')

@section('content')
@php
    $estadoLabels = [
        'borrador' => 'Borrador',
        'pendiente_revision' => 'Pendiente de revisión',
        'cambios_solicitados' => 'Cambios solicitados',
        'aprobada' => 'Aprobada',
        'rechazada' => 'Rechazada',
        'publicada' => 'Publicada',
    ];

    $estadoClases = [
        'borrador' => 'border-slate-500/20 bg-slate-500/10 text-slate-300',
        'pendiente_revision' => 'border-sky-500/20 bg-sky-500/10 text-sky-300',
        'cambios_solicitados' => 'border-amber-500/20 bg-amber-500/10 text-amber-300',
        'aprobada' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
        'rechazada' => 'border-red-500/20 bg-red-500/10 text-red-300',
        'publicada' => 'border-violet-500/20 bg-violet-500/10 text-violet-300',
    ];

    $prioridadLabel = $actividad->prioridad_texto ?? match ((int) $actividad->prioridad) {
        100 => 'Alta',
        75 => 'Media',
        50 => 'Regular',
        25 => 'Baja',
        default => 'Sin definir',
    };

    $resolverMedio = function (?string $ruta) {
        if (!$ruta) {
            return null;
        }

        $ruta = str_replace('\\', '/', trim($ruta));

        if (
            str_starts_with($ruta, 'http://')
            || str_starts_with($ruta, 'https://')
            || str_starts_with($ruta, 'data:')
        ) {
            return $ruta;
        }

        $ruta = ltrim($ruta, '/');

        if (str_starts_with($ruta, 'public/')) {
            $ruta = substr($ruta, 7);
        }

        if (str_starts_with($ruta, 'storage/')) {
            return asset($ruta);
        }

        return asset('storage/'.$ruta);
    };

    $formatoFecha = function ($fecha, bool $hora = true) {
        if (!$fecha) {
            return 'Sin definir';
        }

        try {
            return \Illuminate\Support\Carbon::parse($fecha)
                ->format($hora ? 'd/m/Y H:i' : 'd/m/Y');
        } catch (\Throwable $e) {
            return (string) $fecha;
        }
    };

    $nombreUsuario = function ($usuario) {
        if (!$usuario) {
            return 'Sin usuario';
        }

        $info = $usuario->informacion_personal ?? null;
        $nombre = trim(
            ($info->nombres ?? '').' '.($info->apellidos ?? '')
        );

        return $nombre !== ''
            ? $nombre
            : ($usuario->nombre ?? $usuario->name ?? $usuario->correo ?? 'Usuario');
    };

    $mediosGenerales = $actividad->medios
        ->filter(fn ($medio) => empty($medio->id_item_actividad) && empty($medio->id_sesion))
        ->sortBy('orden')
        ->values();

    $portada = $mediosGenerales->first(fn ($medio) => (bool) $medio->es_portada);
    $galeria = $mediosGenerales->filter(fn ($medio) => !(bool) $medio->es_portada)->values();

    $camposCompraPorItem = collect($camposCompra ?? [])->keyBy(
        fn ($configuracion) => (string) ($configuracion['id_item_pg'] ?? '')
    );

    $configProductoPorItem = collect($configuracionProductos ?? [])->keyBy(
        fn ($configuracion) => (string) ($configuracion['id_item_pg'] ?? '')
    );

    $productosConfigurados = (bool) ($configuracionProductosGeneral['configured'] ?? false);
    $productosHabilitados = (bool) ($configuracionProductosGeneral['enabled'] ?? false);
    $sesionesConfiguradas = (bool) ($configuracionSesionesGeneral['configured'] ?? false);
    $configuracionCompleta = (bool) ($configuracionGeneral['configured'] ?? false);
    $totalVariantes = $actividad->items->sum(function ($item) {
        return $item->variantes->count();
    });

    $pasos = [
        1 => 'Información',
        2 => 'Presentación',
        3 => 'Productos / Servicios',
        4 => 'Datos solicitados',
        5 => 'Precios / Costos',
        6 => 'Programación',
        7 => 'Resumen',
    ];

    $accionRevisionLabels = [
        'enviada_revision' => 'Enviada a revisión',
        'cambios_solicitados' => 'Cambios solicitados',
        'aprobada' => 'Aprobada',
        'rechazada' => 'Rechazada',
        'retirada' => 'Revisión retirada',
        'reabierta' => 'Reabierta',
        'publicada' => 'Publicada',
        'retirada_publicacion' => 'Retirada de publicación',
    ];

    $accionRevisionClases = [
        'enviada_revision' => 'border-sky-500/20 bg-sky-500/10 text-sky-300',
        'cambios_solicitados' => 'border-amber-500/20 bg-amber-500/10 text-amber-300',
        'aprobada' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300',
        'rechazada' => 'border-red-500/20 bg-red-500/10 text-red-300',
        'retirada' => 'border-slate-500/20 bg-slate-500/10 text-slate-300',
        'reabierta' => 'border-violet-500/20 bg-violet-500/10 text-violet-300',
        'publicada' => 'border-violet-500/20 bg-violet-500/10 text-violet-300',
        'retirada_publicacion' => 'border-amber-500/20 bg-amber-500/10 text-amber-300',
    ];

    $ahora = now();
    $visiblePortal = $actividad->estaVisibleEnPortal();
    $estadoPortal = match (true) {
        $actividad->visibilidad !== 'publica' => ['label' => 'No visible', 'clase' => 'border-amber-500/20 bg-amber-500/10 text-amber-300', 'mensaje' => 'La actividad no aparecerá en el portal porque su visibilidad no está configurada como pública.'],
        $actividad->visible_desde && $actividad->visible_desde->gt($ahora) => ['label' => 'Programada', 'clase' => 'border-sky-500/20 bg-sky-500/10 text-sky-300', 'mensaje' => 'La actividad aparecerá en el portal a partir del '.$formatoFecha($actividad->visible_desde).'.'],
        $actividad->visible_hasta && $actividad->visible_hasta->lt($ahora) => ['label' => 'Período vencido', 'clase' => 'border-amber-500/20 bg-amber-500/10 text-amber-300', 'mensaje' => 'El período de visibilidad finalizó el '.$formatoFecha($actividad->visible_hasta).'.'],
        default => ['label' => 'Visible ahora', 'clase' => 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300', 'mensaje' => 'La actividad está dentro de su período de visibilidad y puede mostrarse en el portal.'],
    };

    $historialPorRevision = $actividad->revisiones
        ->sortByDesc('id_revision')
        ->groupBy(fn ($revision) => (int) $revision->numero_revision)
        ->sortKeysDesc();
@endphp

<div
    class="w-full min-w-0 max-w-full overflow-x-hidden"
    x-data="{ paso: 1, imagenAbierta: false, imagen: '', imagenTitulo: '' }"
    @keydown.escape.window="imagenAbierta = false"
>
    <div class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <a href="{{ route('admin.actividades.index') }}" class="mb-3 inline-flex items-center gap-2 text-sm font-medium text-gray-400 transition hover:text-white">
                    <span aria-hidden="true">←</span>
                    Volver a actividades
                </a>

                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="min-w-0 break-words text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        {{ $actividad->nombre }}
                    </h1>

                    <span class="rounded-full border px-3 py-1 text-xs font-semibold {{ $estadoClases[$actividad->estado_publicacion] ?? 'border-white/10 bg-white/5 text-gray-300' }}">
                        {{ $estadoLabels[$actividad->estado_publicacion] ?? ucfirst(str_replace('_', ' ', $actividad->estado_publicacion)) }}
                    </span>

                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-semibold text-gray-300">
                        Revisión #{{ $actividad->revision_actual }}
                    </span>
                </div>

                <p class="mt-3 max-w-4xl text-sm leading-6 text-gray-400">
                    Vista de solo lectura. Puedes revisar toda la actividad sin modificar ningún dato.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-200">{{ session('success') }}</div>
        @endif

        @if ($errors->has('publicacion'))
            <div class="mb-5 rounded-2xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm font-medium text-red-200">{{ $errors->first('publicacion') }}</div>
        @endif

        @if (in_array($actividad->estado_publicacion, ['aprobada', 'publicada'], true))
            <div class="mb-6 flex flex-col gap-4 rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-bold text-white">{{ $actividad->estado_publicacion === 'publicada' ? 'Publicación del portal' : 'Actividad aprobada' }}</p>
                        <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $estadoPortal['clase'] }}">{{ $estadoPortal['label'] }}</span>
                    </div>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-400">
                        @if ($actividad->estado_publicacion === 'publicada')
                            {{ $visiblePortal ? 'Está publicada y visible actualmente.' : $estadoPortal['mensaje'] }}
                        @else
                            Está aprobada y lista para publicarse. {{ $estadoPortal['mensaje'] }}
                        @endif
                    </p>
                </div>

                @if ($actividad->estado_publicacion === 'aprobada')
                    <form action="{{ route('admin.actividades.publicar', $actividad) }}" method="POST" class="shrink-0" onsubmit="return confirm('¿Publicar esta actividad en el portal?')">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-600 sm:w-auto">Publicar actividad</button>
                    </form>
                @else
                    <form action="{{ route('admin.actividades.retirar-publicacion', $actividad) }}" method="POST" class="shrink-0" onsubmit="return confirm('¿Retirar esta actividad del portal público?')">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-amber-500/30 bg-amber-500/10 px-5 py-3 text-sm font-bold text-amber-200 transition hover:bg-amber-500/20 sm:w-auto">Retirar publicación</button>
                    </form>
                @endif
            </div>
        @endif

        <div class="mb-6 sm:hidden">
            <label for="paso-visualizacion" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                Sección
            </label>
            <select
                id="paso-visualizacion"
                @change="paso = Number($event.target.value)"
                class="block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-sm font-semibold text-white outline-none focus:border-emerald-500"
            >
                @foreach ($pasos as $numero => $titulo)
                    <option value="{{ $numero }}">{{ $numero }}. {{ $titulo }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-6 hidden sm:grid sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 sm:gap-2">
            @foreach ($pasos as $numero => $titulo)
                <button
                    type="button"
                    @click="paso = {{ $numero }}"
                    :class="paso === {{ $numero }} ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' : 'border-white/10 bg-white/[0.02] text-gray-400 hover:bg-white/5 hover:text-white'"
                    class="flex min-w-0 items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-xs font-semibold transition"
                >
                    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-current/20 text-[10px] font-black">
                        {{ $numero }}
                    </span>
                    <span class="truncate">{{ $titulo }}</span>
                </button>
            @endforeach
        </div>

        <section x-show="paso === 1" x-cloak class="space-y-5">
            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-7">
                <div class="mb-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-400">Información general</p>
                    <h2 class="mt-2 text-xl font-bold text-white">Datos principales</h2>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        'Categoría' => $actividad->categoria?->nombre ?: 'Sin categoría',
                        'Visibilidad' => ucfirst($actividad->visibilidad ?: 'Sin definir'),
                        'Prioridad' => $prioridadLabel,
                        'Estado operativo' => ucfirst($actividad->estado_operativo ?: 'Sin definir'),
                    ] as $label => $valor)
                        <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                            <p class="mt-2 break-words text-sm font-semibold text-gray-200">{{ $valor }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Resumen</p>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-300">{{ $actividad->resumen ?: 'Sin resumen.' }}</p>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Descripción completa</p>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-gray-300">{{ $actividad->descripcion ?: 'Sin descripción.' }}</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-5 lg:grid-cols-2">
                <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                    <h3 class="text-base font-bold text-white">Períodos</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        @foreach ([
                            'Visible desde' => $formatoFecha($actividad->visible_desde),
                            'Visible hasta' => $formatoFecha($actividad->visible_hasta),
                            'Realización desde' => $formatoFecha($actividad->realizacion_desde),
                            'Realización hasta' => $formatoFecha($actividad->realizacion_hasta),
                        ] as $label => $valor)
                            <div class="flex flex-col gap-1 border-b border-white/5 pb-3 last:border-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                                <dt class="text-gray-500">{{ $label }}</dt>
                                <dd class="font-medium text-gray-200 sm:text-right">{{ $valor }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                    <h3 class="text-base font-bold text-white">Ubicación general</h3>

                    <div class="mt-4 text-sm leading-6 text-gray-300">
                        @if ($actividad->espacio)
                            <p class="font-semibold text-white">{{ $actividad->espacio->nombre }}</p>
                            @if ($actividad->espacio->contenedor)
                                <p class="mt-1 text-gray-400">Dentro de {{ $actividad->espacio->contenedor->nombre }}</p>
                            @endif
                            @if ($actividad->espacio->capacidad)
                                <p class="mt-1 text-gray-400">Capacidad registrada: {{ $actividad->espacio->capacidad }}</p>
                            @endif
                        @elseif ($actividad->ubicacion_externa)
                            <p class="whitespace-pre-line">{{ $actividad->ubicacion_externa }}</p>
                        @else
                            <p class="text-gray-500">Sin ubicación general definida.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section x-show="paso === 2" x-cloak class="space-y-5">
            <div class="grid gap-5 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,.9fr)]">
                <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-400">Presentación</p>
                            <h2 class="mt-2 text-xl font-bold text-white">Portada</h2>
                        </div>
                        @if ($portada)
                            <span class="text-xs text-gray-500">Clic para ampliar</span>
                        @endif
                    </div>

                    @if ($portada)
                        @php
                            $portadaUrl = $resolverMedio($portada->url);
                        @endphp
                        <button
                            type="button"
                            data-image="{{ $portadaUrl }}"
                            data-title="{{ $portada->texto_alternativo ?: $actividad->nombre }}"
                            @click="imagen = $el.dataset.image; imagenTitulo = $el.dataset.title; imagenAbierta = true"
                            class="mt-5 block w-full overflow-hidden rounded-2xl border border-white/10 bg-black/20 text-left transition hover:border-emerald-500/30"
                        >
                            <img src="{{ $portadaUrl }}" alt="{{ $portada->texto_alternativo ?: $actividad->nombre }}" class="aspect-[16/9] w-full object-cover">
                        </button>
                        <p class="mt-3 text-sm text-gray-400">{{ $portada->texto_alternativo ?: 'Sin texto alternativo.' }}</p>
                    @else
                        <div class="mt-5 rounded-2xl border border-dashed border-white/10 p-8 text-center text-sm text-gray-500">
                            Sin portada configurada.
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                    <h3 class="text-base font-bold text-white">Presentación y etiquetas</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4 border-b border-white/5 pb-3">
                            <dt class="text-gray-500">Destacada</dt>
                            <dd class="font-medium text-gray-200">{{ $actividad->destacada ? 'Sí' : 'No' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-b border-white/5 pb-3">
                            <dt class="text-gray-500">Prioridad</dt>
                            <dd class="font-medium text-gray-200">{{ $prioridadLabel }}</dd>
                        </div>
                    </dl>

                    <div class="mt-5">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Etiquetas</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @forelse ($actividad->etiquetas as $etiqueta)
                                <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs font-semibold text-gray-300">{{ $etiqueta->nombre }}</span>
                            @empty
                                <span class="text-sm text-gray-500">Sin etiquetas.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-white">Galería</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ $galeria->count() }} {{ $galeria->count() === 1 ? 'imagen' : 'imágenes' }}</p>
                    </div>
                    @if ($galeria->isNotEmpty())
                        <span class="text-xs text-gray-500">Clic en una imagen para verla en grande</span>
                    @endif
                </div>

                @if ($galeria->isNotEmpty())
                    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                        @foreach ($galeria as $medio)
                            @php
                                $medioUrl = $resolverMedio($medio->url);
                            @endphp
                            <button
                                type="button"
                                data-image="{{ $medioUrl }}"
                                data-title="{{ $medio->texto_alternativo ?: 'Imagen de galería' }}"
                                @click="imagen = $el.dataset.image; imagenTitulo = $el.dataset.title; imagenAbierta = true"
                                class="group overflow-hidden rounded-2xl border border-white/10 bg-black/20 transition hover:border-emerald-500/30"
                            >
                                <img src="{{ $medioUrl }}" alt="{{ $medio->texto_alternativo ?: $actividad->nombre }}" class="aspect-square w-full object-cover transition duration-200 group-hover:scale-[1.03]">
                            </button>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-gray-500">No hay imágenes adicionales.</p>
                @endif
            </div>
        </section>

        <section x-show="paso === 3" x-cloak class="space-y-5">
            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-400">Productos / Servicios</p>
                        <h2 class="mt-2 text-xl font-bold text-white">Resumen comercial</h2>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-gray-300">{{ $actividad->items->count() }} elementos</span>
                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-gray-300">{{ $totalVariantes }} variantes</span>
                    </div>
                </div>

                @if (!$productosHabilitados && $actividad->items->isEmpty())
                    <div class="mt-5 rounded-2xl border border-white/10 bg-black/10 p-5 text-sm text-gray-400">
                        La actividad está configurada sin productos ni servicios.
                    </div>
                @else
                    <div class="mt-5 space-y-3">
                        @forelse ($actividad->items->sortBy('orden') as $item)
                            @php
                                $imagenItem = $item->medios->firstWhere('es_portada', true) ?? $item->medios->first();
                                $imagenItemUrl = $resolverMedio($imagenItem?->url);
                            @endphp

                            <details class="group overflow-hidden rounded-2xl border border-white/10 bg-black/10">
                                <summary class="flex cursor-pointer list-none items-center gap-4 p-4 sm:p-5">
                                    @if ($imagenItemUrl)
                                        <img src="{{ $imagenItemUrl }}" alt="{{ $imagenItem?->texto_alternativo ?: $item->nombre }}" class="h-14 w-14 shrink-0 rounded-xl object-cover">
                                    @else
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-lg font-black text-gray-500">{{ mb_substr($item->nombre, 0, 1) }}</div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="truncate text-sm font-bold text-white sm:text-base">{{ $item->nombre }}</h3>
                                            <span class="rounded-full border border-white/10 bg-white/5 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-400">{{ $item->tipo }}</span>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $item->variantes->count() }} {{ $item->variantes->count() === 1 ? 'variante' : 'variantes' }} ·
                                            Precio base ${{ number_format((float) $item->precio, 2) }} ·
                                            Stock {{ $item->stock_total ?? 'sin límite' }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 text-xs font-semibold text-emerald-400 group-open:hidden">Ver detalle</span>
                                    <span class="hidden shrink-0 text-xs font-semibold text-emerald-400 group-open:inline">Ocultar</span>
                                </summary>

                                <div class="border-t border-white/10 p-4 sm:p-5">
                                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-3">
                                            <p class="text-[10px] uppercase tracking-wide text-gray-500">Precio base</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-200">${{ number_format((float) $item->precio, 2) }}</p>
                                        </div>
                                        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-3">
                                            <p class="text-[10px] uppercase tracking-wide text-gray-500">Costo referencia</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-200">${{ number_format((float) $item->costo_referencia, 2) }}</p>
                                        </div>
                                        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-3">
                                            <p class="text-[10px] uppercase tracking-wide text-gray-500">Cantidad por inscripción</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-200">{{ $item->min_por_inscripcion ?? 1 }} – {{ $item->max_por_inscripcion ?? 'sin límite' }}</p>
                                        </div>
                                        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-3">
                                            <p class="text-[10px] uppercase tracking-wide text-gray-500">Participante</p>
                                            <p class="mt-1 text-sm font-semibold text-gray-200">{{ $item->requiere_participante ? 'Requerido' : 'No requerido' }}</p>
                                        </div>
                                    </div>

                                    @if ($item->descripcion)
                                        <div class="mt-4 rounded-xl border border-white/10 bg-white/[0.02] p-4">
                                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Descripción</p>
                                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-300">{{ $item->descripcion }}</p>
                                        </div>
                                    @endif

                                    <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-4 text-sm">
                                            <p class="font-semibold text-white">Disponibilidad</p>
                                            <dl class="mt-3 space-y-2 text-gray-400">
                                                <div class="flex justify-between gap-3"><dt>Venta desde</dt><dd class="text-right text-gray-200">{{ $formatoFecha($item->venta_desde) }}</dd></div>
                                                <div class="flex justify-between gap-3"><dt>Venta hasta</dt><dd class="text-right text-gray-200">{{ $formatoFecha($item->venta_hasta) }}</dd></div>
                                                <div class="flex justify-between gap-3"><dt>Activo</dt><dd class="text-right text-gray-200">{{ $item->activo ? 'Sí' : 'No' }}</dd></div>
                                            </dl>
                                        </div>

                                        <div class="rounded-xl border border-white/10 bg-white/[0.02] p-4">
                                            <p class="font-semibold text-white">Variantes</p>
                                            @if ($item->variantes->isEmpty())
                                                <p class="mt-3 text-sm text-gray-500">Sin variantes.</p>
                                            @else
                                                <p class="mt-2 text-xs leading-5 text-gray-500">
                                                    {{ $item->variantes->count() }} combinaciones. Se mantienen comprimidas para no alargar la página.
                                                </p>

                                                <details class="mt-3 rounded-xl border border-white/10 bg-black/10">
                                                    <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-emerald-400">
                                                        Ver {{ $item->variantes->count() }} combinaciones
                                                    </summary>
                                                    <div class="max-h-72 overflow-auto border-t border-white/10">
                                                        <table class="min-w-[860px] w-full text-left text-xs">
                                                            <thead class="sticky top-0 bg-[#101722] text-gray-500">
                                                                <tr>
                                                                    <th class="px-3 py-2 font-semibold">Variante</th>
                                                                    <th class="px-3 py-2 font-semibold">SKU</th>
                                                                    <th class="px-3 py-2 font-semibold">Precio final</th>
                                                                    <th class="px-3 py-2 font-semibold">+ Precio</th>
                                                                    <th class="px-3 py-2 font-semibold">Costo final</th>
                                                                    <th class="px-3 py-2 font-semibold">+ Costo</th>
                                                                    <th class="px-3 py-2 font-semibold">Stock</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-white/5">
                                                                @foreach ($item->variantes->sortBy('orden') as $variante)
                                                                    @php
                                                                        $precioBaseVariante = (float) $item->precio;
                                                                        $costoBaseVariante = (float) $item->costo_referencia;
                                                                        $precioFinalVariante = $variante->precio !== null
                                                                            ? (float) $variante->precio
                                                                            : $precioBaseVariante;
                                                                        $costoFinalVariante = $variante->costo_referencia !== null
                                                                            ? (float) $variante->costo_referencia
                                                                            : $costoBaseVariante;
                                                                        $aumentoPrecioVariante = round($precioFinalVariante - $precioBaseVariante, 2);
                                                                        $aumentoCostoVariante = round($costoFinalVariante - $costoBaseVariante, 2);
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="px-3 py-2 text-gray-200">{{ $variante->nombre_variante ?: 'Variante' }}</td>
                                                                        <td class="px-3 py-2 text-gray-400">{{ $variante->sku ?: '—' }}</td>
                                                                        <td class="px-3 py-2 text-gray-300">${{ number_format($precioFinalVariante, 2) }}</td>
                                                                        <td class="px-3 py-2 font-semibold {{ $aumentoPrecioVariante > 0 ? 'text-emerald-300' : 'text-gray-500' }}">
                                                                            {{ $aumentoPrecioVariante > 0 ? '+$'.number_format($aumentoPrecioVariante, 2) : '$0.00' }}
                                                                        </td>
                                                                        <td class="px-3 py-2 text-gray-300">${{ number_format($costoFinalVariante, 2) }}</td>
                                                                        <td class="px-3 py-2 font-semibold {{ $aumentoCostoVariante > 0 ? 'text-amber-300' : 'text-gray-500' }}">
                                                                            {{ $aumentoCostoVariante > 0 ? '+$'.number_format($aumentoCostoVariante, 2) : '$0.00' }}
                                                                        </td>
                                                                        <td class="px-3 py-2 text-gray-300">{{ $variante->stock_total ?? '—' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </details>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </details>
                        @empty
                            <p class="text-sm text-gray-500">No hay productos o servicios registrados.</p>
                        @endforelse
                    </div>
                @endif
            </div>
        </section>

        <section x-show="paso === 4" x-cloak class="space-y-5">
            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-400">Datos solicitados</p>
                <h2 class="mt-2 text-xl font-bold text-white">Información que se solicita</h2>

                <div class="mt-5 grid gap-4 xl:grid-cols-2">
                    <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                        <h3 class="font-semibold text-white">Formularios generales</h3>
                        <div class="mt-3 space-y-3">
                            @forelse ($actividad->formularios as $formulario)
                                <details class="rounded-xl border border-white/10 bg-white/[0.02]">
                                    <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-gray-200">
                                        {{ $formulario->nombre }} · {{ $formulario->campos->count() }} campos
                                    </summary>
                                    <div class="border-t border-white/10 px-4 py-3">
                                        @forelse ($formulario->campos as $campo)
                                            <div class="flex flex-col gap-1 border-b border-white/5 py-2 last:border-0 sm:flex-row sm:items-center sm:justify-between">
                                                <span class="text-sm text-gray-300">{{ $campo->nombre }}</span>
                                                <span class="text-xs text-gray-500">{{ $campo->tipo_dato }} · {{ $campo->obligatorio ? 'Obligatorio' : 'Opcional' }}</span>
                                            </div>
                                        @empty
                                            <p class="text-sm text-gray-500">Sin campos.</p>
                                        @endforelse
                                    </div>
                                </details>
                            @empty
                                <p class="text-sm text-gray-500">Sin formularios generales.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                        <h3 class="font-semibold text-white">Datos por producto / servicio</h3>
                        <div class="mt-3 space-y-3">
                            @forelse ($actividad->items as $item)
                                @php
                                    $configDatos = $camposCompraPorItem->get((string) $item->id_item_actividad, []);
                                @endphp
                                <details class="rounded-xl border border-white/10 bg-white/[0.02]">
                                    <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-gray-200">
                                        {{ $item->nombre }}
                                        <span class="ml-2 text-xs font-normal text-gray-500">
                                            {{ !empty($configDatos['enabled']) ? count($configDatos['fields'] ?? []) . ' campos' : 'No solicita datos adicionales' }}
                                        </span>
                                    </summary>
                                    <div class="border-t border-white/10 px-4 py-3">
                                        @if (!empty($configDatos['enabled']))
                                            @forelse (($configDatos['fields'] ?? []) as $campo)
                                                <div class="border-b border-white/5 py-2 last:border-0">
                                                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                                        <span class="text-sm text-gray-300">{{ $campo['label'] ?? $campo['key'] ?? 'Campo' }}</span>
                                                        <span class="text-xs text-gray-500">{{ $campo['type'] ?? 'texto' }} · {{ !empty($campo['required']) ? 'Obligatorio' : 'Opcional' }}</span>
                                                    </div>
                                                    @if (!empty($campo['options']))
                                                        <div class="mt-2 flex flex-wrap gap-1.5">
                                                            @foreach ($campo['options'] as $opcion)
                                                                <span class="rounded-full border border-white/10 bg-white/5 px-2 py-1 text-[10px] text-gray-400">{{ is_array($opcion) ? ($opcion['label'] ?? $opcion['value'] ?? 'Opción') : $opcion }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-sm text-gray-500">Sin campos configurados.</p>
                                            @endforelse
                                        @else
                                            <p class="text-sm text-gray-500">No solicita datos adicionales.</p>
                                        @endif
                                    </div>
                                </details>
                            @empty
                                <p class="text-sm text-gray-500">No hay productos o servicios.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="paso === 5" x-cloak class="space-y-5">
            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-400">Precios / Costos</p>
                <h2 class="mt-2 text-xl font-bold text-white">Resumen financiero</h2>

                <div class="mt-5 overflow-x-auto rounded-2xl border border-white/10">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-white/[0.03] text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Producto / servicio</th>
                                <th class="px-4 py-3 font-semibold">Precio base</th>
                                <th class="px-4 py-3 font-semibold">Costo referencia</th>
                                <th class="px-4 py-3 font-semibold">Variantes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($actividad->items as $item)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-200">{{ $item->nombre }}</td>
                                    <td class="px-4 py-3 text-gray-300">${{ number_format((float) $item->precio, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-300">${{ number_format((float) $item->costo_referencia, 2) }}</td>
                                    <td class="px-4 py-3 text-gray-400">{{ $item->variantes->count() }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin productos o servicios.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 space-y-3">
                    @foreach ($actividad->items as $item)
                        @php
                            $configPrecio = $configuracionesPrecios[(string) $item->id_item_actividad] ?? [];
                        @endphp
                        @if (!empty($configPrecio))
                            <details class="rounded-2xl border border-white/10 bg-black/10">
                                <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-gray-200 sm:px-5">
                                    Reglas de {{ $item->nombre }}
                                    <span class="ml-2 text-xs font-normal text-gray-500">{{ count($configPrecio['rules'] ?? []) }} ajustes</span>
                                </summary>
                                <div class="border-t border-white/10 p-4 sm:p-5">
                                    @if (empty($configPrecio['enabled']))
                                        <p class="text-sm text-gray-500">Todas las opciones usan el precio y costo base.</p>
                                    @else
                                        @if (!empty($configPrecio['pricing_fields']))
                                            <div class="mb-3 flex flex-wrap gap-2">
                                                @foreach ($configPrecio['pricing_fields'] as $campo)
                                                    <span class="rounded-full border border-cyan-500/20 bg-cyan-500/10 px-2.5 py-1 text-xs font-semibold text-cyan-300">{{ $campo }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="space-y-2">
                                            @forelse (($configPrecio['rules'] ?? []) as $regla)
                                                @php
                                                    $nombreCampoRegla = $regla['field_label']
                                                        ?? $regla['field']
                                                        ?? $regla['campo']
                                                        ?? $regla['field_key']
                                                        ?? 'Dato';
                                                    $opcionRegla = $regla['option'] ?? $regla['opcion'] ?? '—';
                                                    $aumentoPrecioRegla = (float) (
                                                        $regla['price_increment']
                                                        ?? $regla['price_delta']
                                                        ?? $regla['aumento_precio']
                                                        ?? 0
                                                    );
                                                    $aumentoCostoRegla = (float) (
                                                        $regla['cost_increment']
                                                        ?? $regla['cost_delta']
                                                        ?? $regla['aumento_costo']
                                                        ?? 0
                                                    );
                                                    $precioConRegla = (float) $item->precio + $aumentoPrecioRegla;
                                                    $costoConRegla = (float) $item->costo_referencia + $aumentoCostoRegla;
                                                @endphp
                                                <div class="rounded-xl border border-white/10 bg-white/[0.02] p-3 sm:p-4">
                                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                        <div class="min-w-0">
                                                            <p class="text-xs text-gray-500">{{ $nombreCampoRegla }}</p>
                                                            <p class="mt-1 break-words text-sm font-semibold text-gray-200">{{ $opcionRegla }}</p>
                                                        </div>
                                                        <div class="flex flex-wrap gap-2 text-xs">
                                                            <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 font-semibold text-emerald-300">
                                                                Precio {{ $aumentoPrecioRegla > 0 ? '+$'.number_format($aumentoPrecioRegla, 2) : '+$0.00' }}
                                                            </span>
                                                            <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 font-semibold text-amber-300">
                                                                Costo {{ $aumentoCostoRegla > 0 ? '+$'.number_format($aumentoCostoRegla, 2) : '+$0.00' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3 grid gap-2 border-t border-white/5 pt-3 text-xs sm:grid-cols-2">
                                                        <p class="text-gray-500">Base + esta opción: <span class="font-semibold text-gray-300">Precio ${{ number_format($precioConRegla, 2) }}</span></p>
                                                        <p class="text-gray-500">Base + esta opción: <span class="font-semibold text-gray-300">Costo ${{ number_format($costoConRegla, 2) }}</span></p>
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-sm text-gray-500">Sin reglas adicionales.</p>
                                            @endforelse
                                        </div>
                                    @endif
                                </div>
                            </details>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                <h3 class="text-base font-bold text-white">Promociones</h3>
                <div class="mt-4 space-y-3">
                    @forelse ($actividad->promociones as $promocion)
                        <details class="rounded-2xl border border-white/10 bg-black/10">
                            <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-gray-200 sm:px-5">
                                {{ $promocion->nombre ?: ($promocion->codigo ?: 'Promoción') }}
                                <span class="ml-2 text-xs font-normal text-gray-500">{{ $promocion->activo ? 'Activa' : 'Inactiva' }}</span>
                            </summary>
                            <div class="grid gap-3 border-t border-white/10 p-4 text-sm sm:grid-cols-2 lg:grid-cols-4 sm:p-5">
                                <div><p class="text-xs text-gray-500">Código</p><p class="mt-1 text-gray-200">{{ $promocion->codigo ?: '—' }}</p></div>
                                <div><p class="text-xs text-gray-500">Tipo</p><p class="mt-1 text-gray-200">{{ $promocion->tipo_descuento ?: '—' }}</p></div>
                                <div><p class="text-xs text-gray-500">Valor</p><p class="mt-1 text-gray-200">{{ $promocion->valor }}</p></div>
                                <div><p class="text-xs text-gray-500">Vigencia</p><p class="mt-1 text-gray-200">{{ $formatoFecha($promocion->vigente_desde) }} → {{ $formatoFecha($promocion->vigente_hasta) }}</p></div>
                            </div>
                        </details>
                    @empty
                        <p class="text-sm text-gray-500">Sin promociones registradas.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section x-show="paso === 6" x-cloak class="space-y-5">
            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-400">Programación</p>
                        <h2 class="mt-2 text-xl font-bold text-white">Sesiones y recursos</h2>
                    </div>
                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-gray-300">
                        {{ $actividad->sesiones->count() }} {{ $actividad->sesiones->count() === 1 ? 'sesión' : 'sesiones' }}
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($actividad->sesiones->sortBy('orden') as $sesion)
                        <details class="rounded-2xl border border-white/10 bg-black/10">
                            <summary class="flex cursor-pointer list-none items-start justify-between gap-4 px-4 py-4 sm:px-5">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-200">{{ $sesion->nombre }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $formatoFecha($sesion->fecha_inicio) }} → {{ $formatoFecha($sesion->fecha_fin) }}</p>
                                </div>
                                <span class="shrink-0 text-xs text-emerald-400">Ver detalle</span>
                            </summary>
                            <div class="border-t border-white/10 p-4 sm:p-5">
                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                    <div><p class="text-xs text-gray-500">Espacio / ubicación</p><p class="mt-1 text-sm text-gray-200">{{ $sesion->espacio?->nombre ?: ($sesion->ubicacion ?: 'Sin definir') }}</p></div>
                                    <div><p class="text-xs text-gray-500">Cupo</p><p class="mt-1 text-sm text-gray-200">{{ $sesion->cupo ?? 'Sin definir' }}</p></div>
                                    <div><p class="text-xs text-gray-500">Reserva</p><p class="mt-1 text-sm text-gray-200">{{ $sesion->requiere_reserva ? 'Requerida' : 'No requerida' }}</p></div>
                                    <div><p class="text-xs text-gray-500">Obligatoria</p><p class="mt-1 text-sm text-gray-200">{{ $sesion->obligatoria ? 'Sí' : 'No' }}</p></div>
                                </div>

                                @if ($sesion->enlace_acceso ?? $sesion->enlace ?? null)
                                    <div class="mt-4 rounded-xl border border-white/10 bg-white/[0.02] p-3">
                                        <p class="text-xs text-gray-500">Enlace de acceso</p>
                                        <p class="mt-1 break-all text-sm text-cyan-300">{{ $sesion->enlace_acceso ?? $sesion->enlace }}</p>
                                    </div>
                                @endif

                                <div class="mt-4">
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Recursos de la sesión</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @forelse ($sesion->recursos as $recursoActividad)
                                            @php
                                                $recursoNombre = $recursoActividad->recurso?->nombre
                                                    ?? $recursoActividad->nombre
                                                    ?? ('Recurso #'.($recursoActividad->id_recurso ?? ''));
                                            @endphp
                                            <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-gray-300">{{ $recursoNombre }}</span>
                                        @empty
                                            <span class="text-sm text-gray-500">Sin recursos asignados.</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </details>
                    @empty
                        <p class="text-sm text-gray-500">No hay sesiones configuradas.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                <h3 class="text-base font-bold text-white">Participación e inscripción</h3>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @php
                        $tipoParticipacionTexto = match ($actividad->tipo_participacion) {
                            'registro_gratuito' => 'Inscripción gratuita',
                            'registro_pago' => 'Inscripción con pago',
                            'venta_directa' => 'Venta directa',
                            default => 'Acceso libre / informativa',
                        };
                    @endphp
                    @foreach ([
                        'Modalidad' => $tipoParticipacionTexto,
                        'Precio inscripción' => $actividad->tipo_participacion === 'registro_pago'
                            ? '$'.number_format((float) $actividad->precio_inscripcion, 2)
                            : ($actividad->tipo_participacion === 'registro_gratuito' ? 'Gratis' : 'No aplica'),
                        'Requiere cuenta' => $actividad->requiere_cuenta ? 'Sí' : 'No',
                        'Lista de espera' => $actividad->permite_lista_espera ? 'Sí' : 'No',
                        'Cupo total' => $actividad->cupo_total ?? 'Sin límite',
                        'Desde' => $formatoFecha($actividad->inscripcion_desde),
                        'Hasta' => $formatoFecha($actividad->inscripcion_hasta),
                    ] as $label => $valor)
                        <div class="rounded-xl border border-white/10 bg-black/10 p-3">
                            <p class="text-[10px] uppercase tracking-wide text-gray-500">{{ $label }}</p>
                            <p class="mt-1 text-sm font-semibold text-gray-200">{{ $valor }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section x-show="paso === 7" x-cloak class="space-y-5">
            <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-400">Resumen</p>
                <h2 class="mt-2 text-xl font-bold text-white">Estado de la actividad</h2>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ([
                        'Configuración finalizada' => $configuracionCompleta ? 'Sí' : 'No',
                        'Productos configurados' => $productosConfigurados ? 'Sí' : 'No',
                        'Productos habilitados' => $productosHabilitados ? 'Sí' : 'No',
                        'Programación configurada' => $sesionesConfiguradas ? 'Sí' : 'No',
                    ] as $label => $valor)
                        <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">{{ $label }}</p>
                            <p class="mt-2 text-sm font-semibold text-gray-200">{{ $valor }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-5 xl:grid-cols-[0.9fr_1.1fr]">
                <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-base font-bold text-white">Creación y responsables</h3>
                        <span class="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-gray-400">Solo lectura</span>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-gray-500">Actividad creada por</p>
                            <p class="mt-2 text-sm font-semibold text-gray-100">{{ $nombreUsuario($actividad->creador) }}</p>
                            @if ($actividad->creador?->correo)
                                <p class="mt-1 break-all text-xs text-gray-500">{{ $actividad->creador->correo }}</p>
                            @endif
                            <p class="mt-3 text-xs text-gray-400">
                                <span class="text-gray-500">Fecha de creación:</span>
                                {{ $formatoFecha($actividad->created_at) }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-gray-500">Última modificación</p>
                            <p class="mt-2 text-sm font-semibold text-gray-100">{{ $nombreUsuario($actividad->actualizador ?: $actividad->creador) }}</p>
                            @if (($actividad->actualizador ?: $actividad->creador)?->correo)
                                <p class="mt-1 break-all text-xs text-gray-500">{{ ($actividad->actualizador ?: $actividad->creador)->correo }}</p>
                            @endif
                            <p class="mt-3 text-xs text-gray-400">
                                <span class="text-gray-500">Fecha de actualización:</span>
                                {{ $formatoFecha($actividad->updated_at) }}
                            </p>
                        </div>
                    </div>

                    @if ($actividad->responsables->isNotEmpty())
                        <div class="mt-5 border-t border-white/10 pt-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Responsables asignados</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($actividad->responsables as $responsable)
                                    <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-gray-300">{{ $nombreUsuario($responsable->usuario) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/[0.03] p-5 sm:p-6">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-bold text-white">Historial de revisiones</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ $historialPorRevision->count() }} {{ $historialPorRevision->count() === 1 ? 'revisión' : 'revisiones' }} ·
                                {{ $actividad->revisiones->count() }} movimientos
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($historialPorRevision as $numeroRevision => $movimientos)
                            @php
                                $movimientosOrdenados = $movimientos->sortByDesc('id_revision')->values();
                                $ultimoMovimiento = $movimientosOrdenados->first();
                                $accionUltima = $ultimoMovimiento?->accion;
                                $labelUltima = $accionRevisionLabels[$accionUltima]
                                    ?? ucfirst(str_replace('_', ' ', (string) $accionUltima));
                                $claseUltima = $accionRevisionClases[$accionUltima]
                                    ?? 'border-white/10 bg-white/5 text-gray-300';
                            @endphp

                            <details class="group overflow-hidden rounded-2xl border border-white/10 bg-black/10">
                                <summary class="cursor-pointer list-none px-4 py-4 sm:px-5">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="text-sm font-bold text-gray-100">Revisión #{{ $numeroRevision }}</span>
                                                <span class="rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $claseUltima }}">{{ $labelUltima }}</span>
                                            </div>
                                            <p class="mt-2 text-xs text-gray-500">
                                                {{ $movimientosOrdenados->count() }} {{ $movimientosOrdenados->count() === 1 ? 'movimiento' : 'movimientos' }} ·
                                                Último: {{ $formatoFecha($ultimoMovimiento?->creado_en) }} ·
                                                {{ $nombreUsuario($ultimoMovimiento?->usuario) }}
                                            </p>
                                        </div>
                                        <span class="shrink-0 text-xs font-semibold text-emerald-400 group-open:hidden">Ver detalles</span>
                                        <span class="hidden shrink-0 text-xs font-semibold text-emerald-400 group-open:inline">Ocultar</span>
                                    </div>
                                </summary>

                                <div class="border-t border-white/10 px-4 py-3 sm:px-5">
                                    <div class="space-y-3">
                                        @foreach ($movimientosOrdenados as $revision)
                                            @php
                                                $accionMovimiento = $revision->accion;
                                                $labelMovimiento = $accionRevisionLabels[$accionMovimiento]
                                                    ?? ucfirst(str_replace('_', ' ', (string) $accionMovimiento));
                                                $claseMovimiento = $accionRevisionClases[$accionMovimiento]
                                                    ?? 'border-white/10 bg-white/5 text-gray-300';
                                            @endphp
                                            <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3">
                                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="rounded-full border px-2 py-0.5 text-[10px] font-semibold {{ $claseMovimiento }}">{{ $labelMovimiento }}</span>
                                                        <span class="text-xs text-gray-500">{{ $formatoFecha($revision->creado_en) }}</span>
                                                    </div>
                                                    <span class="text-xs text-gray-400">{{ $nombreUsuario($revision->usuario) }}</span>
                                                </div>
                                                @if ($revision->observacion)
                                                    <p class="mt-2 whitespace-pre-line text-xs leading-5 text-gray-400">{{ $revision->observacion }}</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </details>
                        @empty
                            <div class="rounded-2xl border border-dashed border-white/10 bg-black/10 p-5 text-sm text-gray-500">
                                Esta actividad todavía no tiene movimientos de revisión.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div
        x-show="imagenAbierta"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-4 sm:p-8"
        @click.self="imagenAbierta = false"
    >
        <div class="flex max-h-full w-full max-w-6xl flex-col">
            <div class="mb-3 flex items-center justify-between gap-4">
                <p class="min-w-0 truncate text-sm font-semibold text-white" x-text="imagenTitulo"></p>
                <button
                    type="button"
                    @click="imagenAbierta = false"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/15 bg-white/10 text-xl text-white transition hover:bg-white/20"
                    aria-label="Cerrar imagen"
                >
                    ×
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-auto rounded-2xl border border-white/10 bg-black/40 p-2">
                <img :src="imagen" :alt="imagenTitulo" class="mx-auto max-h-[80vh] max-w-full object-contain">
            </div>
        </div>
    </div>
</div>
@endsection
