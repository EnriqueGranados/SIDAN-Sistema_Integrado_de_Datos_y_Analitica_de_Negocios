@extends('layouts.navbars')
@section('title', 'Configurar actividad')
@section('content')
@php
    $pasos = [
        'presentacion' => ['numero' => 1, 'titulo' => 'Presentación'],
        'productos' => ['numero' => 2, 'titulo' => 'Productos o servicios'],
        'datos' => ['numero' => 3, 'titulo' => 'Datos solicitados'],
        'precios' => ['numero' => 4, 'titulo' => 'Precios y costos'],
        'sesiones' => ['numero' => 5, 'titulo' => 'Programación'],
        'resumen' => ['numero' => 6, 'titulo' => 'Resumen'],
    ];
    $pasoActual = $pasos[$paso] ?? $pasos['presentacion'];
    $numeroPaso = $pasoActual['numero'];
    $tituloPaso = $pasoActual['titulo'];
    $totalPasos = count($pasos);
    $porcentaje = (int) round(($numeroPaso / $totalPasos) * 100);
    $totalItems = $actividad->items->count();
    $productosConfigurados = (bool) ($configuracionProductos['configured'] ?? false) || $totalItems > 0;
    $ofreceProductos = $totalItems > 0 ? true : (bool) ($configuracionProductos['enabled'] ?? false);
    $itemsDatosConfigurados = $actividad->items->filter(function ($item) use ($configuracionesDatos) {
        return $configuracionesDatos->has((string) $item->id_item_actividad);
    })->count();
    $datosCompletos = !$ofreceProductos || ($totalItems > 0 && $itemsDatosConfigurados === $totalItems);
    $itemsPrecioCompletos = $actividad->items->filter(function ($item) use ($configuracionesDatos, $configuracionesPrecios) {
        $configDatos = $configuracionesDatos->get((string) $item->id_item_actividad);
        if (!$configDatos || !($configDatos['enabled'] ?? false)) {
            return true;
        }
        $camposLista = collect($configDatos['fields'] ?? [])->filter(
            fn ($campo) => ($campo['type'] ?? null) === 'lista' && !empty($campo['options'] ?? [])
        );
        if ($camposLista->isEmpty()) {
            return true;
        }
        return $configuracionesPrecios->has((string) $item->id_item_actividad);
    })->count();
    $preciosCompletos = !$ofreceProductos || ($totalItems > 0 && $itemsPrecioCompletos === $totalItems);
    $sesionesConfiguradas = (bool) ($configuracionSesiones['configured'] ?? false);
    $modoSesionesActual = $modoSesiones ?? ($configuracionSesiones['mode'] ?? null);
    $presentacionCompleta = $actividad->medios
        ->whereNull('id_item_actividad')
        ->whereNull('id_sesion')
        ->contains(fn ($medio) => (bool) $medio->es_portada);
    $estadoPasos = [
        'presentacion' => $presentacionCompleta,
        'productos' => $productosConfigurados,
        'datos' => $productosConfigurados && $datosCompletos,
        'precios' => $productosConfigurados && $datosCompletos && $preciosCompletos,
        'sesiones' => $sesionesConfiguradas,
        'resumen' => false,
    ];
    $realizacionDesde = $actividad->realizacion_desde
        ? \Carbon\Carbon::parse($actividad->realizacion_desde)->format('Y-m-d\TH:i')
        : '';
    $realizacionHasta = $actividad->realizacion_hasta
        ? \Carbon\Carbon::parse($actividad->realizacion_hasta)->format('Y-m-d\TH:i')
        : '';
    $cupoGeneral = $actividad->habilita_inscripcion && !is_null($actividad->cupo_total)
        ? (int) $actividad->cupo_total
        : null;
    $sesionesCodificadas = base64_encode(
        json_encode(
            $actividad->sesiones->sortBy('orden')->map(function ($sesion) {
                return [
                    'id_sesion' => $sesion->id_sesion,
                    'nombre' => $sesion->nombre,
                    'fecha_inicio' => $sesion->fecha_inicio
                        ? \Carbon\Carbon::parse($sesion->fecha_inicio)->format('Y-m-d\TH:i')
                        : '',
                    'fecha_fin' => $sesion->fecha_fin
                        ? \Carbon\Carbon::parse($sesion->fecha_fin)->format('Y-m-d\TH:i')
                        : '',
                    'id_espacio' => $sesion->id_espacio,
                    'ubicacion' => $sesion->ubicacion,
                    'recursos' => $sesion->recursos->map(fn ($uso) => ['id_recurso' => $uso->id_recurso,'cantidad' => $uso->cantidad,'observacion' => $uso->observacion])->values()->all(),
                    'enlace_acceso' => $sesion->enlace_acceso,
                    'cupo' => $sesion->cupo,
                    'requiere_reserva' => (bool) $sesion->requiere_reserva,
                    'obligatoria' => (bool) $sesion->obligatoria,
                ];
            })->values()->all(),
            JSON_UNESCAPED_UNICODE
        )
    );
    $espaciosCodificados = base64_encode(json_encode($espacios->map(fn ($espacio) => [
        'id' => $espacio->id_espacio,'nombre' => $espacio->nombre,'contenedor' => $espacio->id_espacio_contenedor,'capacidad' => $espacio->capacidad,
    ])->values()->all(),JSON_UNESCAPED_UNICODE));
    $recursosCodificados = base64_encode(json_encode($recursos->map(fn ($recurso) => [
        'id' => $recurso->id_recurso,'nombre' => $recurso->nombre,'categoria' => $recurso->categoria,'es_movil' => (bool) $recurso->es_movil,
        'espacios' => $recurso->espacios->mapWithKeys(fn ($espacio) => [(string) $espacio->id_espacio => (int) $espacio->pivot->cantidad])->all(),
    ])->values()->all(),JSON_UNESCAPED_UNICODE));

    $modoCorreccion = $modoCorreccion ?? false;
    $observacionesCorreccion = $observacionesCorreccion ?? collect();

    $seccionesConfiguracion = [
        'presentacion' => 'presentacion',
        'productos' => 'productos',
        'datos_solicitados' => 'datos',
        'precios_costos' => 'precios',
        'programacion' => 'sesiones',
    ];

    $conteoCorreccionesPaso = collect();

    foreach ($seccionesConfiguracion as $seccionRevision => $pasoConfiguracion) {
        $conteoCorreccionesPaso[$pasoConfiguracion] = $observacionesCorreccion
            ->get($seccionRevision, collect())
            ->count();
    }

    $totalCorreccionesConfiguracion = $conteoCorreccionesPaso->sum();
    $seccionPasoActual = array_search($paso, $seccionesConfiguracion, true);

    $observacionesPasoActual = $seccionPasoActual
        ? $observacionesCorreccion->get($seccionPasoActual, collect())
        : collect();

    $nombreReferenciaCorreccion = function ($observacion) use ($actividad) {
        if (!$observacion->referencia_tipo || !$observacion->referencia_id) {
            return null;
        }

        return match ($observacion->referencia_tipo) {
            'item' => $actividad->items
                ->firstWhere('id_item_actividad', $observacion->referencia_id)?->nombre
                ?? 'Producto o servicio #'.$observacion->referencia_id,

            'sesion' => $actividad->sesiones
                ->firstWhere('id_sesion', $observacion->referencia_id)?->nombre
                ?? 'Sesión #'.$observacion->referencia_id,

            'formulario' => $actividad->formularios
                ->firstWhere('id_formulario', $observacion->referencia_id)?->nombre
                ?? 'Formulario #'.$observacion->referencia_id,

            'campo' => $actividad->formularios
                ->flatMap(fn ($formulario) => $formulario->campos)
                ->firstWhere('id_campo', $observacion->referencia_id)?->nombre
                ?? 'Campo #'.$observacion->referencia_id,

            default => null,
        };
    };
@endphp
<div class="w-full min-w-0 max-w-full overflow-x-hidden">
    <div class="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 sm:py-7 lg:px-8">
        <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                        {{ $modoCorreccion ? 'Cambios solicitados · revisión #'.$actividad->revision_actual : 'Configuración pendiente' }}
                    </span>
                    <span class="truncate text-xs text-gray-500">
                        {{ $actividad->nombre }}
                    </span>
                    @if ($actividad->categoria)<span class="rounded-full border border-white/10 px-2 py-1 text-[11px] text-gray-400">{{ $actividad->categoria->nombre }}</span>@endif
                    @foreach ($actividad->etiquetas as $etiqueta)<span class="rounded-full bg-white/5 px-2 py-1 text-[11px] text-gray-500">#{{ $etiqueta->nombre }}</span>@endforeach
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    {{ $modoCorreccion ? 'Corrige la configuración solicitada' : 'Termina de configurar tu actividad' }}
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    {{ $modoCorreccion ? 'Las observaciones del revisor aparecen en el paso correspondiente. Corrige lo necesario y vuelve a finalizar la configuración.' : 'Configura únicamente lo que aplique. Puedes salir y continuar después.' }}
                </p>
            </div>
            <a href="{{ route('admin.actividades.index') }}"
                class="inline-flex shrink-0 items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white">
                Volver al listado
            </a>
        </div>
        @if ($modoCorreccion)
            <div class="mb-6 overflow-hidden rounded-2xl border border-amber-500/20 bg-amber-500/[0.05]">
                <div class="flex flex-col gap-3 border-b border-amber-500/15 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-amber-400">
                            Correcciones pendientes
                        </p>
                        <h2 class="mt-1 text-base font-semibold text-white">
                            El revisor dejó indicaciones en esta configuración
                        </h2>
                    </div>

                    <span class="inline-flex w-fit items-center rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-300">
                        {{ $totalCorreccionesConfiguracion }}
                        {{ $totalCorreccionesConfiguracion === 1 ? 'corrección' : 'correcciones' }}
                    </span>
                </div>

                @if ($decisionCorrecciones?->observacion && $decisionCorrecciones->observacion !== 'Se solicitaron correcciones específicas.')
                    <div class="px-4 py-4 sm:px-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Comentario general del revisor
                        </p>
                        <p class="mt-2 text-sm leading-6 text-gray-300">
                            {{ $decisionCorrecciones->observacion }}
                        </p>
                    </div>
                @endif
            </div>
        @endif

        <div class="sticky top-0 z-[80] -mx-4 mb-7 border-y border-white/10 bg-[#0b1018]/95 px-4 py-3 shadow-xl shadow-black/20 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="mx-auto max-w-6xl">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="shrink-0 text-xs font-semibold uppercase tracking-wide text-emerald-400">
                                Paso {{ $numeroPaso }} de {{ $totalPasos }}
                            </p>
                            <span class="text-xs text-gray-700">•</span>
                            <p class="truncate text-sm font-semibold text-white">
                                {{ $tituloPaso }}
                            </p>
                        </div>
                    </div>
                    <span class="shrink-0 text-xs font-semibold text-gray-500">
                        {{ $porcentaje }}%
                    </span>
                </div>
                <div class="mt-3 flex gap-1.5">
                    @foreach ($pasos as $clave => $info)
                        @php
                            $esActual = $clave === $paso;
                            $estaCompleto = $estadoPasos[$clave] ?? false;
                            $correccionesPaso = (int) ($conteoCorreccionesPaso[$clave] ?? 0);
                        @endphp
                        <a href="{{ route('admin.actividades.configurar', [
                            'actividad' => $actividad->id_actividad,
                            'paso' => $clave
                        ]) }}"
                            title="{{ $info['numero'] }}. {{ $info['titulo'] }}"
                            class="group relative flex min-w-0 flex-1 flex-col gap-1.5">
                            @if ($modoCorreccion && $correccionesPaso > 0)
                                <span class="absolute -right-1 -top-2 inline-flex min-h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[9px] font-black leading-none text-slate-950 ring-2 ring-[#0b1018]">
                                    {{ min(99, $correccionesPaso) }}
                                </span>
                            @endif
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-800">
                                <div class="h-full rounded-full transition-all
                                    {{ $esActual
                                        ? 'w-full bg-emerald-400'
                                        : ($estaCompleto ? 'w-full bg-emerald-600' : 'w-0') }}">
                                </div>
                            </div>
                            <div class="hidden items-center gap-1.5 lg:flex">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[10px] font-bold
                                    {{ $esActual
                                        ? 'bg-emerald-500 text-white'
                                        : ($estaCompleto
                                            ? 'bg-emerald-500/15 text-emerald-400'
                                            : 'bg-white/5 text-gray-600') }}">
                                    {{ $estaCompleto && !$esActual ? '✓' : $info['numero'] }}
                                </span>
                                <span class="truncate text-[11px]
                                    {{ $esActual
                                        ? 'font-semibold text-white'
                                        : ($estaCompleto ? 'text-gray-400' : 'text-gray-600') }}">
                                    {{ $info['titulo'] }}
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-2 flex items-center justify-between lg:hidden">
                    <p class="truncate text-[11px] text-gray-500">
                        {{ $tituloPaso }}
                    </p>
                    <p class="shrink-0 text-[11px] text-gray-600">
                        {{ $numeroPaso }}/{{ $totalPasos }}
                    </p>
                </div>
            </div>
        </div>
        @if ($modoCorreccion && $observacionesPasoActual->isNotEmpty())
            <div class="mb-6 rounded-2xl border border-amber-500/20 bg-amber-500/[0.05] p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-sm font-black text-amber-300">
                        !
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="font-semibold text-amber-200">
                            Cambios solicitados en {{ $tituloPaso }}
                        </h2>
                        <p class="mt-1 text-xs leading-5 text-gray-500">
                            Estas observaciones no puedes eliminarlas. El revisor las marcará como corregidas cuando compruebe tus cambios.
                        </p>

                        <div class="mt-3 grid gap-2">
                            @foreach ($observacionesPasoActual as $observacion)
                                @php
                                    $referenciaCorreccion = $nombreReferenciaCorreccion($observacion);
                                @endphp

                                <div class="rounded-xl border border-white/10 bg-black/10 p-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-[10px] font-semibold uppercase tracking-wide text-amber-400">
                                            Revisión #{{ $observacion->revision?->numero_revision ?? $actividad->revision_actual }}
                                        </span>

                                        @if ($referenciaCorreccion)
                                            <span class="rounded-full bg-white/5 px-2 py-0.5 text-[10px] font-semibold text-gray-400">
                                                {{ $referenciaCorreccion }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1.5 text-sm leading-6 text-gray-300">
                                        {{ $observacion->observacion }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($paso === 'presentacion')
            @include('admin.actividades.partials.configuracion-presentacion')
        @endif
        @if ($paso === 'productos')
            <div class="mb-6 rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-4 sm:p-5">
                <h2 class="font-semibold text-blue-300">
                    ¿Esta actividad ofrece productos, servicios o algo que las personas puedan comprar, reservar o solicitar?
                </h2>
                <p class="mt-2 text-sm leading-6 text-gray-400">
                    Por ejemplo: camisetas, kits, entradas de pago, cupos de excursión, alimentos, donaciones o servicios. Una actividad gratuita puede continuar sin agregar ningún elemento.
                </p>
            </div>
            <form action="{{ route('admin.actividades.productos.configuracion', $actividad) }}"
                method="POST"
                class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                @csrf
                <p class="font-semibold text-white">
                    ¿Ofrece productos o servicios?
                </p>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="cursor-pointer rounded-xl border border-white/10 p-4 transition hover:bg-white/[0.03] {{ $productosConfigurados && !$ofreceProductos ? 'border-emerald-500/30 bg-emerald-500/[0.04]' : '' }}">
                        <div class="flex items-start gap-3">
                            <input type="radio"
                                name="ofrece_productos"
                                value="0"
                                required
                                @checked($productosConfigurados && !$ofreceProductos)
                                @disabled($actividad->items->isNotEmpty())>
                            <div>
                                <p class="font-semibold text-white">
                                    No, no ofrece productos ni servicios
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    La actividad puede ser gratuita, informativa o manejar únicamente inscripciones y sesiones.
                                </p>
                                @if ($actividad->items->isNotEmpty())
                                    <p class="mt-2 text-xs text-amber-400">
                                        Para elegir esta opción primero debes eliminar los elementos agregados.
                                    </p>
                                @endif
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer rounded-xl border border-blue-500/20 bg-blue-500/[0.04] p-4">
                        <div class="flex items-start gap-3">
                            <input type="radio"
                                name="ofrece_productos"
                                value="1"
                                required
                                @checked($ofreceProductos)>
                            <div>
                                <p class="font-semibold text-blue-200">
                                    Sí, ofrece productos o servicios
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Después podrás agregar cada producto, servicio, acceso, reserva o donación por separado.
                                </p>
                            </div>
                        </div>
                    </label>
                </div>
                <div class="mt-5 flex justify-end">
                    <button type="submit"
                        class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                        {{ $productosConfigurados ? 'Guardar decisión' : 'Continuar' }}
                    </button>
                </div>
            </form>
            @if ($ofreceProductos)
                <div class="mt-5 rounded-2xl border border-white/10 bg-white/[0.03]">
                    <div class="flex flex-col gap-4 border-b border-white/10 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                        <div>
                            <h2 class="font-semibold text-white">
                                Productos o servicios
                            </h2>
                            <p class="mt-1 text-sm text-gray-500">
                                Cada elemento puede tener su propio precio, costo, disponibilidad, imagen y datos de compra.
                            </p>
                        </div>
                        <button type="button"
                            id="btnNuevoItem"
                            class="rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-400">
                            + Agregar elemento
                        </button>
                    </div>
                    @if ($actividad->items->isEmpty())
                        <div class="px-4 py-12 text-center">
                            <h3 class="font-semibold text-gray-300">
                                Todavía no has agregado ningún elemento
                            </h3>
                            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-600">
                                Agrega únicamente lo que realmente se vaya a vender, reservar o solicitar. No necesitas crear un producto de $0 para una actividad gratuita.
                            </p>
                            <button type="button"
                                id="btnNuevoItemVacio"
                                class="mt-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-400">
                                Agregar primer elemento
                            </button>
                        </div>
                    @else
                        <div class="divide-y divide-white/5">
                            @foreach ($actividad->items->sortBy('orden') as $item)
                                <div class="p-4 sm:p-5">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="flex min-w-0 flex-1 flex-col gap-4 sm:flex-row">
                                            <div class="h-28 w-full shrink-0 overflow-hidden rounded-xl border border-white/10 bg-black/20 sm:h-24 sm:w-24">
                                                @if ($item->imagenPrincipal)
                                                    <img src="{{ asset('storage/' . $item->imagenPrincipal->url) }}"
                                                        alt="{{ $item->imagenPrincipal->texto_alternativo ?: $item->nombre }}"
                                                        class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center px-3 text-center text-xs text-gray-600">
                                                        Sin imagen
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="font-semibold text-white">
                                                        {{ $item->nombre }}
                                                    </h3>
                                                    <span class="rounded-full bg-white/5 px-2 py-0.5 text-[10px] uppercase tracking-wide text-gray-500">
                                                        {{ $item->tipo }}
                                                    </span>
                                                </div>
                                                @if ($item->descripcion)
                                                    <p class="mt-2 text-sm text-gray-500">
                                                        {{ $item->descripcion }}
                                                    </p>
                                                @endif
                                                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                                    <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                                        <p class="text-[11px] text-gray-600">Precio</p>
                                                        <p class="mt-1 font-semibold text-emerald-400">
                                                            ${{ number_format((float) $item->precio, 2) }}
                                                        </p>
                                                    </div>
                                                    <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                                        <p class="text-[11px] text-gray-600">Costo</p>
                                                        <p class="mt-1 font-semibold text-gray-300">
                                                            ${{ number_format((float) $item->costo_referencia, 2) }}
                                                        </p>
                                                    </div>
                                                    <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                                        <p class="text-[11px] text-gray-600">Margen</p>
                                                        <p class="mt-1 font-semibold text-blue-400">
                                                            ${{ number_format((float) $item->precio - (float) $item->costo_referencia, 2) }}
                                                        </p>
                                                    </div>
                                                    <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                                        <p class="text-[11px] text-gray-600">Stock</p>
                                                        <p class="mt-1 font-semibold text-gray-300">
                                                            {{ is_null($item->stock_total) ? 'Sin límite' : $item->stock_total }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button"
                                                class="btnEditarItem h-10 w-10 rounded-xl border border-blue-500/20 text-blue-400 transition hover:bg-blue-500/10"
                                                data-url="{{ route('admin.actividades.items.update', [$actividad, $item]) }}"
                                                data-nombre="{{ $item->nombre }}"
                                                data-descripcion="{{ $item->descripcion }}"
                                                data-tipo="{{ $item->tipo }}"
                                                data-precio="{{ $item->precio }}"
                                                data-costo="{{ $item->costo_referencia }}"
                                                data-stock="{{ $item->stock_total }}"
                                                data-desde="{{ $item->venta_desde ? \Carbon\Carbon::parse($item->venta_desde)->format('Y-m-d\TH:i') : '' }}"
                                                data-hasta="{{ $item->venta_hasta ? \Carbon\Carbon::parse($item->venta_hasta)->format('Y-m-d\TH:i') : '' }}"
                                                data-minimo="{{ $item->min_por_inscripcion }}"
                                                data-maximo="{{ $item->max_por_inscripcion }}"
                                                data-participante="{{ $item->requiere_participante ? '1' : '0' }}"
                                                data-imagen="{{ $item->imagenPrincipal ? asset('storage/' . $item->imagenPrincipal->url) : '' }}">
                                                ✎
                                            </button>
                                            <form action="{{ route('admin.actividades.items.destroy', [$actividad, $item]) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Seguro que deseas eliminar este elemento?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="h-10 w-10 rounded-xl border border-red-500/20 text-red-400 transition hover:bg-red-500/10">
                                                    ×
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @if ($actividad->items->isNotEmpty())
                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('admin.actividades.configurar', [
                            'actividad' => $actividad->id_actividad,
                            'paso' => 'datos'
                        ]) }}"
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                            Continuar con datos solicitados
                        </a>
                    </div>
                @endif
            @elseif ($productosConfigurados)
                <div class="mt-5 rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-4 sm:p-5">
                    <p class="font-semibold text-emerald-300">
                        Sin productos ni servicios
                    </p>
                    <p class="mt-1 text-sm leading-6 text-gray-500">
                        Está bien. Como esta actividad no ofrece productos o servicios, puedes continuar directamente con la programación.
                    </p>
                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('admin.actividades.configurar', [
                            'actividad' => $actividad->id_actividad,
                            'paso' => 'sesiones'
                        ]) }}"
                                                    class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white">
                            Continuar con programación
                        </a>
                    </div>
                </div>
            @endif
        @endif
        @if ($paso === 'datos')
            @if (!$itemSeleccionado)
                <div class="mb-6 rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-4 sm:p-5">
                    <h2 class="font-semibold text-blue-300">
                        ¿Qué deberá indicar el comprador?
                    </h2>
                    <p class="mt-1 text-sm leading-6 text-gray-400">
                        Puedes solicitar talla, color, punto de abordaje, nombre personalizado, cantidad u otro dato necesario al registrar la compra.
                    </p>
                </div>
                <div class="space-y-4">
                    @foreach ($actividad->items->sortBy('orden') as $item)
                        @php
                            $config = $configuracionesDatos->get((string) $item->id_item_actividad);
                        @endphp
                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="font-semibold text-white">
                                        {{ $item->nombre }}
                                    </h3>
                                    <div class="mt-3">
                                        @if (!$config)
                                            <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                                                Pendiente
                                            </span>
                                        @elseif (!($config['enabled'] ?? false))
                                            <span class="rounded-full bg-gray-500/10 px-2.5 py-1 text-xs font-semibold text-gray-400">
                                                Sin datos adicionales
                                            </span>
                                        @else
                                            <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
                                                {{ count($config['fields'] ?? []) }} dato{{ count($config['fields'] ?? []) === 1 ? '' : 's' }} configurado{{ count($config['fields'] ?? []) === 1 ? '' : 's' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('admin.actividades.configurar', [
                                    'actividad' => $actividad->id_actividad,
                                    'paso' => 'datos',
                                    'item' => $item->id_item_actividad
                                ]) }}"
                                    class="rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-2.5 text-center text-sm font-semibold text-blue-300">
                                    {{ $config ? 'Editar configuración' : 'Configurar' }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                    <a href="{{ route('admin.actividades.configurar', [
                        'actividad' => $actividad->id_actividad,
                        'paso' => 'productos'
                    ]) }}"
                        class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400">
                        Volver
                    </a>
                    @if ($datosCompletos)
                        <a href="{{ route('admin.actividades.configurar', [
                            'actividad' => $actividad->id_actividad,
                            'paso' => 'precios'
                        ]) }}"
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-center text-sm font-bold text-white">
                            Continuar con precios
                        </a>
                    @endif
                </div>
            @else
                @php
                    $configActual = $configDatosSeleccionado ?? null;
                    $configCodificada = base64_encode(json_encode($configActual, JSON_UNESCAPED_UNICODE));
                @endphp
                <div class="mb-5 rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-400">
                        Configurando
                    </p>
                    <h2 class="mt-1 text-lg font-semibold text-white">
                        {{ $itemSeleccionado->nombre }}
                    </h2>
                </div>
                <form action="{{ route('admin.actividades.datos-pedido.store', [
                    $actividad,
                    $itemSeleccionado
                ]) }}"
                    method="POST"
                    class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-6">
                    @csrf
                    <input type="hidden"
                        id="configDatosActual"
                        value="{{ $configCodificada }}">
                    <p class="mb-4 font-semibold text-white">
                        ¿Necesitas pedirle algún dato al comprador?
                    </p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="cursor-pointer rounded-xl border border-white/10 p-4">
                            <div class="flex gap-3">
                                <input type="radio"
                                    name="requiere_datos"
                                    value="0"
                                    required
                                    @checked($configActual && !($configActual['enabled'] ?? false))>
                                <div>
                                    <p class="font-semibold text-white">
                                        No
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Se vende tal como está.
                                    </p>
                                </div>
                            </div>
                        </label>
                        <label class="cursor-pointer rounded-xl border border-blue-500/20 bg-blue-500/[0.04] p-4">
                            <div class="flex gap-3">
                                <input type="radio"
                                    name="requiere_datos"
                                    value="1"
                                    required
                                    @checked($configActual && ($configActual['enabled'] ?? false))>
                                <div>
                                    <p class="font-semibold text-blue-200">
                                        Sí
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Necesito solicitar uno o varios datos.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div id="seccionCamposPedido" class="mt-6 hidden">
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,.85fr)]">
                            <div>
                                <div class="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <h3 class="font-semibold text-white">
                                            Datos solicitados
                                        </h3>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Abre solamente el dato que quieras modificar.
                                        </p>
                                    </div>
                                    <button type="button"
                                        id="btnAgregarCampo"
                                        class="rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-2.5 text-sm font-semibold text-blue-300">
                                        + Agregar dato
                                    </button>
                                </div>
                                <div id="contenedorCampos" class="space-y-3"></div>
                            </div>
                            <div>
                                <div class="rounded-2xl border border-white/10 bg-black/10 p-4 lg:sticky lg:top-28">
                                    <h3 class="font-semibold text-white">
                                        Vista previa
                                    </h3>
                                    <p class="mt-1 text-xs text-gray-600">
                                        Así se verá al registrar la compra.
                                    </p>
                                    <div id="vistaPreviaCampos" class="mt-5 space-y-4"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                        <a href="{{ route('admin.actividades.configurar', [
                            'actividad' => $actividad->id_actividad,
                            'paso' => 'datos'
                        ]) }}"
                            class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white">
                            Guardar configuración
                        </button>
                    </div>
                </form>
            @endif
        @endif
        @if ($paso === 'precios')
            @if (!$itemSeleccionado)
                <div class="mb-6 rounded-2xl border border-violet-500/20 bg-violet-500/[0.05] p-4 sm:p-5">
                    <h2 class="font-semibold text-violet-300">
                        ¿Alguna opción aumenta el precio o costo?
                    </h2>
                    <p class="mt-1 text-sm leading-6 text-gray-400">
                        Solo indica cuánto aumenta cada opción. Las que no configures conservarán automáticamente el precio y costo base.
                    </p>
                </div>
                <div class="space-y-4">
                    @foreach ($actividad->items->sortBy('orden') as $item)
                        @php
                            $configDatos = $configuracionesDatos->get((string) $item->id_item_actividad);
                            $camposLista = collect($configDatos['fields'] ?? [])->filter(
                                fn ($campo) => ($campo['type'] ?? null) === 'lista' && !empty($campo['options'] ?? [])
                            );
                            $configPrecio = $configuracionesPrecios->get((string) $item->id_item_actividad);
                        @endphp
                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="font-semibold text-white">
                                        {{ $item->nombre }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Precio base ${{ number_format((float) $item->precio, 2) }}
                                        ·
                                        Costo base ${{ number_format((float) $item->costo_referencia, 2) }}
                                    </p>
                                    <div class="mt-3">
                                        @if ($camposLista->isEmpty())
                                            <span class="rounded-full bg-gray-500/10 px-2.5 py-1 text-xs font-semibold text-gray-400">
                                                No aplica
                                            </span>
                                        @elseif (!$configPrecio)
                                            <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                                                Pendiente
                                            </span>
                                        @elseif (!($configPrecio['enabled'] ?? false))
                                            <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
                                                Sin aumentos
                                            </span>
                                        @else
                                            <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-xs font-semibold text-violet-300">
                                                Aumentos configurados
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                @if ($camposLista->isNotEmpty())
                                    <a href="{{ route('admin.actividades.configurar', [
                                        'actividad' => $actividad->id_actividad,
                                        'paso' => 'precios',
                                        'item' => $item->id_item_actividad
                                    ]) }}"
                                        class="rounded-xl border border-violet-500/20 bg-violet-500/10 px-4 py-2.5 text-center text-sm font-semibold text-violet-300">
                                        Configurar
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <a href="{{ route('admin.actividades.configurar', [
                        'actividad' => $actividad->id_actividad,
                        'paso' => 'datos'
                    ]) }}"
                        class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400">
                        Volver
                    </a>
                    @if ($preciosCompletos)
                        <a href="{{ route('admin.actividades.configurar', [
                            'actividad' => $actividad->id_actividad,
                            'paso' => 'sesiones'
                        ]) }}"
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-center text-sm font-bold text-white">
                            Continuar con programación
                        </a>
                    @endif
                </div>
            @else
                @php
                    $configDatos = $configDatosSeleccionado;
                    $camposLista = collect($configDatos['fields'] ?? [])->filter(
                        fn ($campo) => ($campo['type'] ?? null) === 'lista' && !empty($campo['options'] ?? [])
                    )->values();
                    $configPrecio = $configPrecioSeleccionado;
                    $datosPrecioCodificados = base64_encode(
                        json_encode([
                            'config' => $configPrecio,
                            'campos' => $camposLista->values()->all(),
                        ], JSON_UNESCAPED_UNICODE)
                    );
                @endphp
                <div class="mb-5 rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-400">
                        Configurando aumentos
                    </p>
                    <h2 class="mt-1 text-lg font-semibold text-white">
                        {{ $itemSeleccionado->nombre }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Precio base ${{ number_format((float) $itemSeleccionado->precio, 2) }}
                        ·
                        Costo base ${{ number_format((float) $itemSeleccionado->costo_referencia, 2) }}
                    </p>
                </div>
                <form id="formAjustesPrecio"
                    action="{{ route('admin.actividades.ajustes-precio.store', [
                        $actividad,
                        $itemSeleccionado
                    ]) }}"
                    method="POST"
                    class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-6">
                    @csrf
                    <input type="hidden"
                        id="datosPrecioActual"
                        value="{{ $datosPrecioCodificados }}">
                    <p class="font-semibold text-white">
                        ¿Alguna opción aumenta el precio o el costo?
                    </p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="cursor-pointer rounded-xl border border-white/10 p-4">
                            <div class="flex gap-3">
                                <input type="radio"
                                    name="tiene_cambios"
                                    value="0"
                                    required
                                    @checked($configPrecio && !($configPrecio['enabled'] ?? false))>
                                <div>
                                    <p class="font-semibold text-white">
                                        No
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Todas mantienen los valores base.
                                    </p>
                                </div>
                            </div>
                        </label>
                        <label class="cursor-pointer rounded-xl border border-violet-500/20 bg-violet-500/[0.04] p-4">
                            <div class="flex gap-3">
                                <input type="radio"
                                    name="tiene_cambios"
                                    value="1"
                                    required
                                    @checked($configPrecio && ($configPrecio['enabled'] ?? false))>
                                <div>
                                    <p class="font-semibold text-violet-200">
                                        Sí
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Algunas opciones tienen un aumento.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>
                    <div id="seccionAjustesPrecio" class="mt-6 hidden">
                        <h3 class="font-semibold text-white">
                            ¿Qué datos pueden generar un aumento?
                        </h3>
                        <p class="mt-1 text-xs leading-5 text-gray-500">
                            Puedes marcar más de uno. Por ejemplo, talla y color pueden aumentar el precio al mismo tiempo.
                        </p>
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach ($camposLista as $campo)
                                <label class="flex cursor-pointer gap-3 rounded-xl border border-white/10 bg-black/10 p-4">
                                    <input type="checkbox"
                                        name="campos_clave[]"
                                        value="{{ $campo['key'] }}"
                                        class="campoPrecioCheckbox mt-1"
                                        @checked(in_array($campo['key'], $configPrecio['pricing_fields'] ?? [], true))>
                                    <div>
                                        <p class="font-medium text-white">
                                            {{ $campo['label'] }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ count($campo['options'] ?? []) }} opciones disponibles
                                        </p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-7 border-t border-white/10 pt-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="font-semibold text-white">
                                        Opciones que aumentan
                                    </h3>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Cada opción solamente puede configurarse una vez dentro del mismo dato.
                                    </p>
                                </div>
                                <button type="button"
                                    id="btnAgregarAjuste"
                                    class="rounded-xl border border-violet-500/20 bg-violet-500/10 px-4 py-2.5 text-sm font-semibold text-violet-300">
                                    + Agregar opción
                                </button>
                            </div>
                            <div id="contenedorAjustesPrecio"
                                class="mt-4 space-y-3">
                            </div>
                            <p id="mensajeSinOpcionesAjuste"
                                class="mt-4 hidden rounded-xl border border-emerald-500/15 bg-emerald-500/[0.05] p-3 text-xs leading-5 text-emerald-300">
                                Ya configuraste todas las opciones disponibles de los datos seleccionados.
                            </p>
                        </div>
                        <div class="mt-5 rounded-xl border border-blue-500/20 bg-blue-500/[0.05] p-4">
                            <p class="text-xs leading-5 text-gray-400">
                                Al registrar una compra, SIDAN tomará el precio base y sumará automáticamente los aumentos de las opciones elegidas.
                            </p>
                        </div>
                    </div>
                                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                        <a href="{{ route('admin.actividades.configurar', [
                            'actividad' => $actividad->id_actividad,
                            'paso' => 'precios'
                        ]) }}"
                            class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white">
                            Guardar
                        </button>
                    </div>
                </form>
            @endif
        @endif
        @if ($paso === 'sesiones')
            <div class="mb-6 rounded-2xl border border-cyan-500/20 bg-cyan-500/[0.05] p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300">
                        ◷
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-semibold text-cyan-300">
                            ¿Cómo se desarrollará esta actividad?
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-gray-400">
                            Una venta puede no necesitar horarios, una ponencia puede usar una sola sesión y una semana de actividades puede dividirse en muchas ponencias, talleres o turnos.
                        </p>
                        @if ($realizacionDesde && $realizacionHasta)
                            <div class="mt-4 rounded-xl border border-white/5 bg-black/10 p-3">
                                <p class="text-xs font-semibold text-gray-300">
                                    Período de realización definido
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    {{ \Carbon\Carbon::parse($actividad->realizacion_desde)->format('d/m/Y H:i') }}
                                    —
                                    {{ \Carbon\Carbon::parse($actividad->realizacion_hasta)->format('d/m/Y H:i') }}
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-600">
                                    Las sesiones que agregues deberán quedar dentro de este período.
                                </p>
                            </div>
                        @else
                            <div class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.05] p-3 text-xs leading-5 text-amber-300">
                                Para utilizar una o varias sesiones primero debes definir el período de realización de la actividad.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <form id="formSesiones"
                action="{{ route('admin.actividades.sesiones.configuracion', $actividad) }}"
                method="POST"
                class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-6">
                @csrf
                <input type="hidden"
                    id="sesionesActuales"
                    value="{{ $sesionesCodificadas }}">
                <input type="hidden" id="espaciosDisponibles" value="{{ $espaciosCodificados }}">
                <input type="hidden" id="recursosDisponibles" value="{{ $recursosCodificados }}">
                <input type="hidden" id="espacioActividad" value="{{ $actividad->id_espacio ?? '' }}">
                <input type="hidden" id="ubicacionActividad" value="{{ $actividad->ubicacion_externa ?? '' }}">
                <input type="hidden"
                    id="realizacionDesde"
                    value="{{ $realizacionDesde }}">
                <input type="hidden"
                    id="realizacionHasta"
                    value="{{ $realizacionHasta }}">
                <input type="hidden"
                    id="cupoGeneralActividad"
                    value="{{ is_null($cupoGeneral) ? '' : $cupoGeneral }}">
                <input type="hidden"
                    name="confirmar_solapamientos"
                    value="0">
                <p class="font-semibold text-white">
                    Selecciona la opción que mejor represente la actividad
                </p>
                <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-3">
                    <label class="cursor-pointer rounded-xl border border-white/10 p-4 transition hover:bg-white/[0.03]">
                        <div class="flex items-start gap-3">
                            <input type="radio"
                                name="modo_sesiones"
                                value="ninguna"
                                required
                                @checked($modoSesionesActual === 'ninguna')>
                            <div>
                                <p class="font-semibold text-white">
                                    Sin sesiones ni horarios
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Útil para ventas, donaciones o actividades que no necesitan una agenda específica.
                                </p>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer rounded-xl border border-cyan-500/20 bg-cyan-500/[0.04] p-4">
                        <div class="flex items-start gap-3">
                            <input type="radio"
                                name="modo_sesiones"
                                value="unica"
                                required
                                @checked($modoSesionesActual === 'unica')
                                @disabled(!$realizacionDesde || !$realizacionHasta)>
                            <div>
                                <p class="font-semibold text-cyan-200">
                                    Una sola sesión
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Para una ponencia, excursión o actividad que ocurre como un único bloque. SIDAN usará automáticamente el período de realización.
                                </p>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer rounded-xl border border-cyan-500/20 bg-cyan-500/[0.04] p-4">
                        <div class="flex items-start gap-3">
                            <input type="radio"
                                name="modo_sesiones"
                                value="multiples"
                                required
                                @checked($modoSesionesActual === 'multiples')
                                @disabled(!$realizacionDesde || !$realizacionHasta)>
                            <div>
                                <p class="font-semibold text-cyan-200">
                                    Varias sesiones
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Para semanas, congresos, talleres, turnos o actividades con distintos horarios y cupos.
                                </p>
                            </div>
                        </div>
                    </label>
                </div>
                <div id="panelSesionUnica"
                    class="mt-6 hidden rounded-2xl border border-cyan-500/20 bg-cyan-500/[0.04] p-4 sm:p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300">
                            1
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-semibold text-white">
                                Sesión principal automática
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500">
                                No tendrás que volver a escribir fechas ni cupo. SIDAN tomará los datos generales de la actividad.
                            </p>
                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                    <p class="text-[11px] uppercase tracking-wide text-gray-600">
                                        Horario
                                    </p>
                                    <p class="mt-1 text-sm font-medium text-gray-300">
                                        {{ $realizacionDesde ? \Carbon\Carbon::parse($actividad->realizacion_desde)->format('d/m/Y H:i') : 'Sin definir' }}
                                        —
                                        {{ $realizacionHasta ? \Carbon\Carbon::parse($actividad->realizacion_hasta)->format('d/m/Y H:i') : 'Sin definir' }}
                                    </p>
                                </div>
                                <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                    <p class="text-[11px] uppercase tracking-wide text-gray-600">
                                        Cupo
                                    </p>
                                    <p class="mt-1 text-sm font-medium text-gray-300">
                                        {{ is_null($cupoGeneral) ? 'Sin cupo general definido' : $cupoGeneral . ' personas · heredado de la inscripción general' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="seccionSesiones" class="mt-7 hidden">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(320px,.85fr)]">
                        <div class="min-w-0">
                            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="font-semibold text-white">
                                        Sesiones, ponencias, talleres o turnos
                                    </h3>
                                    <p class="mt-1 text-xs leading-5 text-gray-500">
                                        Agrega al menos dos. Cada una puede tener su propio cupo y decidir si necesita reserva.
                                    </p>
                                </div>
                                <button type="button"
                                    id="btnAgregarSesion"
                                    class="rounded-xl border border-cyan-500/20 bg-cyan-500/10 px-4 py-2.5 text-sm font-semibold text-cyan-300">
                                    + Agregar sesión
                                </button>
                            </div>
                            @if (!is_null($cupoGeneral))
                                <div class="mb-4 rounded-xl border border-blue-500/15 bg-blue-500/[0.04] p-3 text-xs leading-5 text-gray-400">
                                    La inscripción general tiene un máximo de
                                    <strong class="text-blue-300">
                                        {{ $cupoGeneral }} personas
                                    </strong>.
                                    Las sesiones heredarán ese valor cuando dejes el cupo vacío y nunca podrán superarlo.
                                </div>
                            @endif
                            <div id="contenedorSesiones"
                                class="space-y-3">
                            </div>
                            <div id="advertenciaSolapamientos"
                                class="mt-4 hidden rounded-xl border border-amber-500/20 bg-amber-500/[0.05] p-4">
                                <p class="font-semibold text-amber-300">
                                    Hay sesiones que se realizan al mismo tiempo
                                </p>
                                <p id="textoSolapamientos"
                                    class="mt-1 text-xs leading-5 text-gray-500">
                                </p>
                                <label class="mt-3 flex cursor-pointer items-start gap-3">
                                    <input type="checkbox"
                                        id="confirmarSolapamientos"
                                        name="confirmar_solapamientos"
                                        value="1"
                                        class="mt-1">
                                    <span class="text-sm text-gray-300">
                                        Sí, estas sesiones se realizarán simultáneamente.
                                    </span>
                                </label>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <div class="rounded-2xl border border-white/10 bg-black/10 p-4 lg:sticky lg:top-28">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300">
                                        ◷
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-white">
                                            Vista previa
                                        </h3>
                                        <p class="mt-1 text-xs leading-5 text-gray-600">
                                            Así se resumirá la programación de la actividad.
                                        </p>
                                    </div>
                                </div>
                                <div id="vistaPreviaSesiones"
                                    class="mt-5 space-y-3">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                    <a href="{{ route('admin.actividades.configurar', [
                        'actividad' => $actividad->id_actividad,
                        'paso' => $ofreceProductos ? 'precios' : 'productos'
                    ]) }}"
                        class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400">
                        Volver
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                        {{ $sesionesConfiguradas ? 'Guardar y revisar resumen' : 'Guardar programación' }}
                    </button>
                </div>
            </form>
            @if ($sesionesConfiguradas)
                <div class="mt-5 flex flex-col gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-emerald-300">
                            ✓ Programación configurada
                        </p>
                        <p class="mt-1 text-xs text-emerald-200/60">
                            Puedes modificarla o continuar directamente al resumen.
                        </p>
                    </div>
                    <a href="{{ route('admin.actividades.configurar', [
                        'actividad' => $actividad->id_actividad,
                        'paso' => 'resumen'
                    ]) }}"
                        class="shrink-0 rounded-xl bg-emerald-500 px-4 py-2.5 text-center text-sm font-bold text-white transition hover:bg-emerald-400">
                        Ir al resumen
                    </a>
                </div>
            @endif
        @endif
        @if ($paso === 'resumen')
            @include('admin.actividades.partials.configuracion-resumen')
        @endif
    </div>
</div>
@if ($paso === 'productos')
<div id="modalItem"
    class="fixed inset-0 z-[300] hidden items-center justify-center bg-black/80 p-3 backdrop-blur-sm sm:p-6">
    <div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-white/10 bg-[#10151f]">
        <div class="flex items-center justify-between border-b border-white/10 p-4 sm:px-6">
            <div>
                <h2 id="modalItemTitulo"
                    class="font-semibold text-white">
                    Agregar producto o servicio
                </h2>
                <p class="mt-1 text-xs text-gray-500">
                    Completa la información principal del elemento.
                </p>
            </div>
            <button type="button"
                id="cerrarModalItem"
                class="h-9 w-9 rounded-xl text-gray-500 transition hover:bg-white/5 hover:text-white">
                ×
            </button>
        </div>
        <form id="formItem"
            action="{{ route('admin.actividades.items.store', [
                'actividad' => $actividad->id_actividad
            ]) }}"
            method="POST"
            enctype="multipart/form-data"
            class="min-h-0 flex-1 overflow-y-auto">
            @csrf
            <input type="hidden"
                name="_method"
                id="metodoItem"
                value="PUT"
                disabled>
            <div class="space-y-5 p-4 sm:p-6">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Nombre
                    </label>
                    <input type="text"
                        id="item_nombre"
                        name="item_nombre"
                        maxlength="150"
                        required
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                        placeholder="Ej. Camiseta oficial SDS26">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Tipo
                    </label>
                    <select id="item_tipo"
                        name="item_tipo"
                        required
                        class="block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-white">
                        <option value="producto">Producto físico</option>
                        <option value="servicio">Servicio</option>
                        <option value="acceso">Entrada o acceso</option>
                        <option value="reserva">Reserva</option>
                        <option value="donacion">Donación</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Descripción
                    </label>
                    <textarea id="item_descripcion"
                        name="item_descripcion"
                        rows="3"
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                        placeholder="Describe brevemente qué recibirá o podrá solicitar la persona."></textarea>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Imagen
                    </label>
                    <div class="overflow-hidden rounded-2xl border border-white/10 bg-black/10">
                        <div id="contenedorPreviewItemImagen"
                            class="relative flex min-h-48 items-center justify-center overflow-hidden bg-black/20">
                            <img id="previewItemImagen"
                                src=""
                                alt="Vista previa"
                                class="hidden h-56 w-full object-cover">
                            <div id="placeholderItemImagen"
                                class="flex flex-col items-center justify-center px-6 py-10 text-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-white/10 bg-white/5 text-xl text-gray-500">
                                    ◫
                                </div>
                                <p class="mt-3 text-sm font-medium text-gray-400">
                                    Sin imagen seleccionada
                                </p>
                                <p class="mt-1 max-w-sm text-xs leading-5 text-gray-600">
                                    Puedes agregar una fotografía para que el producto o servicio sea más fácil de identificar.
                                </p>
                            </div>
                        </div>
                        <div class="border-t border-white/10 p-4">
                            <label for="item_imagen"
                                class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-2.5 text-sm font-semibold text-blue-300 transition hover:bg-blue-500/15">
                                Seleccionar imagen
                            </label>
                            <input type="file"
                                id="item_imagen"
                                name="item_imagen"
                                accept="image/jpeg,image/png,image/webp"
                                class="hidden">
                            <p id="nombreItemImagen"
                                class="mt-2 break-all text-xs text-gray-600">
                                JPG, JPEG, PNG o WEBP · Máximo 5 MB
                            </p>
                            <div id="contenedorEliminarItemImagen"
                                class="mt-4 hidden rounded-xl border border-red-500/15 bg-red-500/[0.04] p-3">
                                <label class="flex cursor-pointer items-start gap-3">
                                    <input type="checkbox"
                                        id="eliminar_item_imagen"
                                        name="eliminar_item_imagen"
                                        value="1"
                                                                                class="mt-1">
                                    <div>
                                        <p class="text-sm font-medium text-red-300">
                                            Eliminar imagen actual
                                        </p>
                                        <p class="mt-1 text-xs leading-5 text-gray-600">
                                            Si no seleccionas una nueva imagen, el elemento quedará sin fotografía.
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Precio de venta
                        </label>
                        <input type="number"
                            id="item_precio"
                            name="item_precio"
                            value="0.00"
                            min="0"
                            step="0.01"
                            required
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Costo
                        </label>
                        <input type="number"
                            id="item_costo_referencia"
                            name="item_costo_referencia"
                            value="0.00"
                            min="0"
                            step="0.01"
                            required
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                    </div>
                </div>
                <div class="rounded-xl border border-blue-500/15 bg-blue-500/[0.04] p-4">
                    <div class="flex justify-between gap-4">
                        <span class="text-sm text-gray-400">
                            Margen estimado
                        </span>
                        <span id="margenItem" class="font-bold text-blue-400">
                            $0.00
                        </span>
                    </div>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Cantidad disponible
                    </label>
                    <input type="number"
                        id="item_stock_total"
                        name="item_stock_total"
                        min="0"
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                        placeholder="Vacío = sin límite">
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Mínimo por compra
                        </label>
                        <input type="number"
                            id="item_min_por_inscripcion"
                            name="item_min_por_inscripcion"
                            value="1"
                            min="1"
                            required
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Máximo por compra
                        </label>
                        <input type="number"
                            id="item_max_por_inscripcion"
                            name="item_max_por_inscripcion"
                            min="1"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                            placeholder="Sin límite">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Disponible desde
                        </label>
                        <input type="datetime-local"
                            id="item_venta_desde"
                            name="item_venta_desde"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Disponible hasta
                        </label>
                        <input type="datetime-local"
                            id="item_venta_hasta"
                            name="item_venta_hasta"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                    </div>
                </div>
                <div class="rounded-xl border border-white/10 p-4">
                    <input type="hidden"
                        name="item_requiere_participante"
                        value="0">
                    <label class="flex gap-3">
                        <input type="checkbox"
                            id="item_requiere_participante"
                            name="item_requiere_participante"
                            value="1">
                        <div>
                            <p class="text-sm font-medium text-gray-300">
                                Cada unidad debe asociarse a una persona
                            </p>
                        </div>
                    </label>
                </div>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-white/10 p-4 sm:flex-row sm:justify-end">
                <button type="button"
                    id="cancelarModalItem"
                    class="rounded-xl border border-white/10 px-5 py-3 text-sm text-gray-400">
                    Cancelar
                </button>
                <button type="submit"
                    id="guardarItemTexto"
                    class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white">
                    Agregar elemento
                </button>
            </div>
        </form>
    </div>
</div>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    function decodificarBase64Json(valor) {
        if (!valor) return null;
        try {
            const bytes = Uint8Array.from(
                atob(valor),
                char => char.charCodeAt(0)
            );
            return JSON.parse(new TextDecoder().decode(bytes));
        } catch (error) {
            return null;
        }
    }
    function escaparHtml(valor) {
        return String(valor ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
    const modalItem = document.getElementById('modalItem');
    if (modalItem) {
        const formItem = document.getElementById('formItem');
        const metodoItem = document.getElementById('metodoItem');
        const tituloItem = document.getElementById('modalItemTitulo');
        const guardarItemTexto = document.getElementById('guardarItemTexto');
        const rutaCrear = formItem.action;
        const inputImagen = document.getElementById('item_imagen');
        const previewImagen = document.getElementById('previewItemImagen');
        const placeholderImagen = document.getElementById('placeholderItemImagen');
        const nombreImagen = document.getElementById('nombreItemImagen');
        const contenedorEliminarImagen = document.getElementById('contenedorEliminarItemImagen');
        const eliminarImagen = document.getElementById('eliminar_item_imagen');
        let imagenActualUrl = '';
        let objectUrlPreview = null;
        function abrirItem() {
            modalItem.classList.remove('hidden');
            modalItem.classList.add('flex');
        }
        function cerrarItem() {
            liberarPreviewTemporal();
            modalItem.classList.add('hidden');
            modalItem.classList.remove('flex');
        }
        function liberarPreviewTemporal() {
            if (objectUrlPreview) {
                URL.revokeObjectURL(objectUrlPreview);
                objectUrlPreview = null;
            }
        }
        function mostrarImagen(url) {
            if (!url) {
                previewImagen.src = '';
                previewImagen.classList.add('hidden');
                placeholderImagen.classList.remove('hidden');
                return;
            }
            previewImagen.src = url;
            previewImagen.classList.remove('hidden');
            placeholderImagen.classList.add('hidden');
        }
        function limpiarEstadoImagen() {
            liberarPreviewTemporal();
            imagenActualUrl = '';
            inputImagen.value = '';
            eliminarImagen.checked = false;
            contenedorEliminarImagen.classList.add('hidden');
            nombreImagen.textContent = 'JPG, JPEG, PNG o WEBP · Máximo 5 MB';
            mostrarImagen('');
        }
        function cargarImagenExistente(url) {
            liberarPreviewTemporal();
            imagenActualUrl = url || '';
            inputImagen.value = '';
            eliminarImagen.checked = false;
            if (imagenActualUrl) {
                mostrarImagen(imagenActualUrl);
                contenedorEliminarImagen.classList.remove('hidden');
                nombreImagen.textContent = 'Puedes conservar esta imagen o seleccionar una nueva.';
            } else {
                mostrarImagen('');
                contenedorEliminarImagen.classList.add('hidden');
                nombreImagen.textContent = 'JPG, JPEG, PNG o WEBP · Máximo 5 MB';
            }
        }
        function actualizarMargen() {
            const precio = parseFloat(document.getElementById('item_precio').value || 0);
            const costo = parseFloat(document.getElementById('item_costo_referencia').value || 0);
            document.getElementById('margenItem').textContent =
                `$${(precio - costo).toFixed(2)}`;
        }
        function nuevoItem() {
            formItem.reset();
            formItem.action = rutaCrear;
            metodoItem.disabled = true;
            tituloItem.textContent = 'Agregar producto o servicio';
            guardarItemTexto.textContent = 'Agregar elemento';
            document.getElementById('item_precio').value =
                '0.00';
            document.getElementById('item_costo_referencia').value =
                '0.00';
            document.getElementById('item_min_por_inscripcion').value =
                '1';
            limpiarEstadoImagen();
            actualizarMargen();
            abrirItem();
        }
        inputImagen?.addEventListener('change', function () {
            liberarPreviewTemporal();
            const archivo = this.files?.[0];
            if (!archivo) {
                if (
                    imagenActualUrl
                    && !eliminarImagen.checked
                ) {
                    mostrarImagen(imagenActualUrl);
                    nombreImagen.textContent = 'Puedes conservar esta imagen o seleccionar una nueva.';
                } else {
                    mostrarImagen('');
                    nombreImagen.textContent = 'JPG, JPEG, PNG o WEBP · Máximo 5 MB';
                }
                return;
            }
            const maximoBytes = 5 * 1024 * 1024;
            const tiposPermitidos = [
                'image/jpeg',
                'image/png',
                'image/webp',
            ];
            if (!tiposPermitidos.includes(archivo.type)) {
                this.value = '';
                alert('La imagen debe ser JPG, JPEG, PNG o WEBP.');
                if (imagenActualUrl) {
                    mostrarImagen(imagenActualUrl);
                } else {
                    mostrarImagen('');
                }
                return;
            }
            if (archivo.size > maximoBytes) {
                this.value = '';
                alert('La imagen no puede superar los 5 MB.');
                if (imagenActualUrl) {
                    mostrarImagen(imagenActualUrl);
                } else {
                    mostrarImagen('');
                }
                return;
            }
            eliminarImagen.checked = false;
            objectUrlPreview = URL.createObjectURL(archivo);
            mostrarImagen(objectUrlPreview);
            nombreImagen.textContent = archivo.name;
        });
        eliminarImagen?.addEventListener('change', function () {
            if (this.checked) {
                liberarPreviewTemporal();
                inputImagen.value = '';
                mostrarImagen('');
                nombreImagen.textContent = 'La imagen actual será eliminada al guardar.';
            } else if (imagenActualUrl) {
                mostrarImagen(imagenActualUrl);
                nombreImagen.textContent = 'Puedes conservar esta imagen o seleccionar una nueva.';
            } else {
                mostrarImagen('');
                nombreImagen.textContent = 'JPG, JPEG, PNG o WEBP · Máximo 5 MB';
            }
        });
        document
            .getElementById('btnNuevoItem')
            ?.addEventListener('click',nuevoItem);
        document
            .getElementById('btnNuevoItemVacio')
            ?.addEventListener('click',nuevoItem);
        document
            .getElementById('cerrarModalItem')
            ?.addEventListener('click',cerrarItem);
        document
            .getElementById('cancelarModalItem')
            ?.addEventListener('click',cerrarItem);
        document
            .getElementById('item_precio')
            ?.addEventListener('input',actualizarMargen);
        document
            .getElementById('item_costo_referencia')
            ?.addEventListener('input',actualizarMargen);
        document
            .querySelectorAll('.btnEditarItem')
            .forEach(button => {
                button.addEventListener(
                    'click',
                    function () {
                        formItem.reset();
                        formItem.action = this.dataset.url;
                        metodoItem.disabled = false;
                                                    metodoItem.value = 'PUT';
                        tituloItem.textContent = 'Editar producto o servicio';
                        guardarItemTexto.textContent = 'Guardar cambios';
                        document.getElementById('item_nombre').value =
                            this.dataset.nombre ?? '';
                        document.getElementById('item_descripcion').value =
                            this.dataset.descripcion ?? '';
                        document.getElementById('item_tipo').value =
                            this.dataset.tipo ?? 'producto';
                        document.getElementById('item_precio').value =
                            this.dataset.precio ?? '0.00';
                        document.getElementById('item_costo_referencia').value =
                            this.dataset.costo ?? '0.00';
                        document.getElementById('item_stock_total').value =
                            this.dataset.stock ?? '';
                        document.getElementById('item_venta_desde').value =
                            this.dataset.desde ?? '';
                        document.getElementById('item_venta_hasta').value =
                            this.dataset.hasta ?? '';
                        document.getElementById('item_min_por_inscripcion').value =
                            this.dataset.minimo ?? '1';
                        document.getElementById('item_max_por_inscripcion').value =
                            this.dataset.maximo ?? '';
                        document.getElementById('item_requiere_participante').checked =
                            this.dataset.participante === '1';
                        cargarImagenExistente(this.dataset.imagen ?? '');
                        actualizarMargen();
                        abrirItem();
                    }
                );
            });
        modalItem.addEventListener(
            'click',
            function (event) {
                if (event.target === modalItem) {
                    cerrarItem();
                }
            }
        );
    }
    const seccionCampos = document.getElementById('seccionCamposPedido');
    if (seccionCampos) {
        const contenedor = document.getElementById('contenedorCampos');
        const preview = document.getElementById('vistaPreviaCampos');
        const config = decodificarBase64Json(document.getElementById('configDatosActual').value);
        let contador = 0;
        function actualizarVisibilidad() {
            const seleccionado = document.querySelector('input[name="requiere_datos"]:checked');
            if (seleccionado?.value === '1') {
                seccionCampos.classList.remove('hidden');
                if (
                    contenedor.children.length
                    === 0
                ) {
                    agregarCampo(null,false);
                }
            } else {
                seccionCampos.classList.add('hidden');
            }
        }
        function tipoBonito(tipo) {
            if (tipo === 'lista') {
                return 'Lista de opciones';
            }
            if (tipo === 'numero') {
                return 'Campo numérico';
            }
            return 'Campo de texto';
        }
        function actualizarResumen(fila) {
            const nombre =
                fila.querySelector(
                    '.campoNombre'
                ).value.trim();
            const tipo =
                fila.querySelector(
                    '.campoTipo'
                ).value;
            const requerido =
                fila.querySelector(
                    '.campoRequerido'
                ).checked;
            const resumen = fila.querySelector('.campoResumen');
            resumen.textContent =
                `${nombre || 'Dato sin nombre'} · ${tipoBonito(tipo)}${requerido ? ' · obligatorio' : ''}`;
        }
        function actualizarOpciones(fila) {
            const tipo =
                fila.querySelector(
                    '.campoTipo'
                ).value;
            const seccionOpciones = fila.querySelector('.seccionOpciones');
            if (tipo === 'lista') {
                seccionOpciones.classList.remove('hidden');
            } else {
                seccionOpciones.classList.add('hidden');
            }
        }
        function expandirFila(fila) {
            fila.querySelector('.campoCuerpo')
                ?.classList.remove('hidden');
            fila.querySelector('.campoChevron').textContent =
                '−';
        }
        function contraerFila(fila) {
            fila.querySelector('.campoCuerpo')
                ?.classList.add('hidden');
            fila.querySelector('.campoChevron').textContent =
                '+';
            actualizarResumen(fila);
        }
        function renderPreview() {
            if (!preview) {
                return;
            }
            preview.innerHTML = '';
            const filas = Array.from(contenedor.querySelectorAll('.filaCampo'));
            if (filas.length === 0) {
                preview.innerHTML = `
                    <div class="rounded-xl border border-dashed border-white/10 p-5 text-center text-xs leading-5 text-gray-600">
                        Agrega un dato para visualizar cómo se mostrará al comprador.
                    </div>
                `;
                return;
            }
            filas.forEach(fila => {
                const nombre = fila.querySelector('.campoNombre')?.value.trim() || 'Dato sin nombre';
                const tipo = fila.querySelector('.campoTipo')?.value || 'texto';
                const requerido = fila.querySelector('.campoRequerido')?.checked;
                const opciones =
                    (fila.querySelector('.campoOpciones')?.value || '')
                        .split('\n')
                        .map(valor => valor.trim())
                        .filter(Boolean);
                const bloque = document.createElement('div');
                bloque.className = 'rounded-xl border border-white/10 bg-white/[0.02] p-4';
                let control = '';
                if (tipo === 'lista') {
                    control = `
                        <select disabled class="mt-2 block w-full rounded-xl border border-white/10 bg-[#111827] px-3 py-2.5 text-sm text-gray-500">
                            <option>Selecciona una opción</option>
                            ${opciones.map(opcion => `<option>${escaparHtml(opcion)}</option>`).join('')}
                        </select>
                    `;
                } else if (tipo === 'numero') {
                    control = `
                        <input type="number" disabled
                            class="mt-2 block w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2.5 text-sm text-gray-500"
                            placeholder="Ingresa un número">
                    `;
                } else {
                    control = `
                        <input type="text" disabled
                            class="mt-2 block w-full rounded-xl border border-white/10 bg-black/20 px-3 py-2.5 text-sm text-gray-500"
                            placeholder="Escribe aquí">
                    `;
                }
                bloque.innerHTML = `
                    <label class="text-sm font-medium text-gray-300">
                        ${escaparHtml(nombre)}
                        ${requerido ? '<span class="text-red-400">*</span>' : ''}
                    </label>
                    ${control}
                `;
                preview.appendChild(bloque);
            });
        }
        function agregarCampo(campo = null, contraido = false) {
            contador++;
            const fila = document.createElement('div');
            fila.className = 'filaCampo overflow-hidden rounded-xl border border-white/10 bg-black/10';
            const nombre = campo?.label ?? '';
            const tipo = campo?.type ?? 'texto';
            const requerido = Boolean(campo?.required);
            const opciones =
                Array.isArray(campo?.options)
                    ? campo.options.join('\n')
                    : '';
            fila.innerHTML = `
                <div class="cabeceraCampo flex cursor-pointer items-center justify-between gap-3 p-4">
                    <div class="min-w-0">
                        <p class="font-medium text-white">
                            Dato ${contador}
                        </p>
                        <p class="campoResumen mt-1 truncate text-xs text-gray-500"></p>
                    </div>
                    <button type="button"
                        class="campoChevron flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-white/10 text-gray-400">
                        −
                    </button>
                </div>
                <div class="campoCuerpo border-t border-white/10 p-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Nombre del dato
                        </label>
                        <input type="text"
                            name="campos[${contador}][label]"
                            value="${escaparHtml(nombre)}"
                            maxlength="100"
                            required
                            class="campoNombre block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                            placeholder="Ej. Talla, color, nombre personalizado">
                    </div>
                    <div class="mt-4">
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Tipo de dato
                        </label>
                        <select name="campos[${contador}][type]"
                            class="campoTipo block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-white">
                            <option value="texto" ${tipo === 'texto' ? 'selected' : ''}>Texto</option>
                            <option value="numero" ${tipo === 'numero' ? 'selected' : ''}>Número</option>
                            <option value="lista" ${tipo === 'lista' ? 'selected' : ''}>Lista de opciones</option>
                        </select>
                    </div>
                    <div class="seccionOpciones mt-4 hidden">
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Opciones
                        </label>
                        <textarea name="campos[${contador}][options]"
                            rows="5"
                            class="campoOpciones block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                            placeholder="Una opción por línea">${escaparHtml(opciones)}</textarea>
                        <p class="mt-2 text-xs text-gray-600">
                            Escribe una opción por línea.
                        </p>
                    </div>
                    <div class="mt-4 rounded-xl border border-white/10 p-4">
                        <input type="hidden"
                            name="campos[${contador}][required]"
                            value="0">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input type="checkbox"
                                name="campos[${contador}][required]"
                                value="1"
                                class="campoRequerido mt-1"
                                ${requerido ? 'checked' : ''}>
                            <div>
                                <p class="text-sm font-medium text-gray-300">
                                    Dato obligatorio
                                </p>
                                <p class="mt-1 text-xs text-gray-600">
                                    La persona deberá completarlo antes de continuar.
                                </p>
                            </div>
                        </label>
                    </div>
                    <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">
                        <button type="button"
                            class="btnQuitarCampo rounded-xl border border-red-500/20 px-4 py-2.5 text-sm font-semibold text-red-400">
                            Eliminar dato
                        </button>
                        <button type="button"
                            class="btnListoCampo rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-2.5 text-sm font-semibold text-blue-300">
                            Listo
                        </button>
                    </div>
                </div>
            `;
            const actualizarTipo = () => {
                actualizarOpciones(fila);
                actualizarResumen(fila);
                renderPreview();
            };
            fila.querySelector('.campoNombre')
                .addEventListener('input', function () {
                    actualizarResumen(fila);
                    renderPreview();
                });
            fila.querySelector('.campoTipo')
                .addEventListener('change', actualizarTipo);
            fila.querySelector('.campoOpciones')
                .addEventListener('input', renderPreview);
            fila.querySelector('.campoRequerido')
                .addEventListener('change', function () {
                    actualizarResumen(fila);
                    renderPreview();
                });
            fila.querySelector('.btnQuitarCampo')
                .addEventListener('click', function () {
                    fila.remove();
                    renderPreview();
                });
            fila.querySelector('.btnListoCampo')
                .addEventListener('click', function () {
                    contraerFila(fila);
                    renderPreview();
                });
            fila.querySelector('.cabeceraCampo')
                .addEventListener('click', function (event) {
                    if (event.target.closest('button')) {
                        return;
                    }
                    const cuerpo = fila.querySelector('.campoCuerpo');
                    if (cuerpo.classList.contains('hidden')) {
                        expandirFila(fila);
                    } else {
                        contraerFila(fila);
                    }
                });
            fila.querySelector('.campoChevron')
                .addEventListener('click', function (event) {
                    event.stopPropagation();
                    const cuerpo = fila.querySelector('.campoCuerpo');
                    if (cuerpo.classList.contains('hidden')) {
                        expandirFila(fila);
                    } else {
                        contraerFila(fila);
                    }
                });
            contenedor.appendChild(fila);
            actualizarTipo();
            actualizarResumen(fila);
            if (contraido) {
                contraerFila(fila);
            } else {
                expandirFila(fila);
            }
            renderPreview();
        }
        document
            .querySelectorAll('input[name="requiere_datos"]')
            .forEach(radio => {
                radio.addEventListener('change',actualizarVisibilidad);
            });
        document
            .getElementById('btnAgregarCampo')
            ?.addEventListener('click', function () {
                agregarCampo(null, false);
            });
        if (
            config
            && config.enabled
            && Array.isArray(config.fields)
        ) {
            config.fields.forEach(campo => {
                agregarCampo(campo, true);
            });
        }
        actualizarVisibilidad();
        renderPreview();
    }
    const seccionAjustes = document.getElementById('seccionAjustesPrecio');
    if (seccionAjustes) {
        const datosPrecio =
            decodificarBase64Json(
                document.getElementById('datosPrecioActual').value
            ) || {};
        const configPrecio = datosPrecio.config || null;
        const camposDisponibles =
            Array.isArray(datosPrecio.campos)
                ? datosPrecio.campos
                : [];
        const contenedorAjustes = document.getElementById('contenedorAjustesPrecio');
        const mensajeSinOpciones = document.getElementById('mensajeSinOpcionesAjuste');
        let contadorAjustes = 0;
        function camposSeleccionados() {
            return Array.from(
                document.querySelectorAll(
                    '.campoPrecioCheckbox:checked'
                )
            ).map(input => input.value);
        }
        function buscarCampo(clave) {
            return camposDisponibles.find(
                campo => campo.key === clave
            );
        }
        function obtenerOpcionesUsadas(
            ignorarFila = null
        ) {
            const usados = new Set();
            contenedorAjustes
                .querySelectorAll('.filaAjustePrecio')
                .forEach(fila => {
                    if (fila === ignorarFila) {
                        return;
                    }
                    const campo = fila.querySelector('.ajusteCampo')?.value;
                    const opcion = fila.querySelector('.ajusteOpcion')?.value;
                    if (campo && opcion) {
                        usados.add(
                            `${campo}::${opcion}`
                        );
                    }
                });
            return usados;
        }
        function combinacionesDisponibles(
            ignorarFila = null
        ) {
            const seleccionados = camposSeleccionados();
            const usados = obtenerOpcionesUsadas(ignorarFila);
            const combinaciones = [];
            seleccionados.forEach(clave => {
                const campo = buscarCampo(clave);
                if (!campo) {
                    return;
                }
                (campo.options || []).forEach(opcion => {
                    const identificador =
                        `${clave}::${opcion}`;
                    if (!usados.has(identificador)) {
                        combinaciones.push({
                            campo: clave,
                            opcion: opcion,
                        });
                    }
                });
            });
            return combinaciones;
        }
        function actualizarBotonAgregar() {
            const boton = document.getElementById('btnAgregarAjuste');
            const disponibles = combinacionesDisponibles();
            const hayCampos =
                camposSeleccionados().length > 0;
            if (boton) {
                boton.disabled =
                    !hayCampos
                    || disponibles.length === 0;
                boton.classList.toggle('opacity-40',boton.disabled);
                boton.classList.toggle('cursor-not-allowed',boton.disabled);
            }
            mensajeSinOpciones?.classList.toggle(
                'hidden',
                !hayCampos
                    || disponibles.length > 0
            );
        }
        function llenarSelectCampos(select,valorActual = '') {
            const seleccionados = camposSeleccionados();
            select.innerHTML =
                '<option value="">Selecciona un dato</option>';
            seleccionados.forEach(clave => {
                const campo = buscarCampo(clave);
                if (!campo) {
                    return;
                }
                const option = document.createElement('option');
                option.value = campo.key;
                option.textContent = campo.label;
                select.appendChild(option);
            });
            if (
                valorActual
                && seleccionados.includes(valorActual)
            ) {
                select.value = valorActual;
            }
        }
        function llenarSelectOpciones(fila,valorActual = '') {
            const selectCampo = fila.querySelector('.ajusteCampo');
            const selectOpcion = fila.querySelector('.ajusteOpcion');
            const campo = buscarCampo(selectCampo.value);
            const usados = obtenerOpcionesUsadas(fila);
            selectOpcion.innerHTML =
                '<option value="">Selecciona una opción</option>';
            if (!campo) {
                return;
            }
            (campo.options || []).forEach(opcion => {
                const identificador =
                    `${campo.key}::${opcion}`;
                if (
                    usados.has(identificador)
                    && opcion !== valorActual
                ) {
                    return;
                }
                const option = document.createElement('option');
                option.value = opcion;
                option.textContent = opcion;
                selectOpcion.appendChild(option);
            });
            if (valorActual) {
                selectOpcion.value = valorActual;
            }
        }
        function actualizarResumenAjuste(fila) {
            const campoClave = fila.querySelector('.ajusteCampo')?.value;
            const opcion = fila.querySelector('.ajusteOpcion')?.value;
            const aumentoPrecio = parseFloat(fila.querySelector('.ajustePrecio')?.value || 0);
            const aumentoCosto = parseFloat(fila.querySelector('.ajusteCosto')?.value || 0);
            const campo = buscarCampo(campoClave);
            const nombre = fila.querySelector('.resumenAjusteNombre');
            const detalle = fila.querySelector('.resumenAjusteDetalle');
            if (nombre) {
                nombre.textContent =
                    campo && opcion
                        ? `${campo.label}: ${opcion}`
                        : 'Nueva opción';
            }
            if (detalle) {
                detalle.textContent =
                    `Precio +$${aumentoPrecio.toFixed(2)} · Costo +$${aumentoCosto.toFixed(2)}`;
            }
        }
        function contraerAjuste(fila) {
            fila.querySelector('.ajusteCuerpo')
                ?.classList.add('hidden');
            const icono = fila.querySelector('.iconoAjuste');
            if (icono) {
                icono.textContent = '⌄';
            }
            actualizarResumenAjuste(fila);
        }
        function expandirAjuste(fila) {
            contenedorAjustes
                .querySelectorAll('.filaAjustePrecio')
                .forEach(otra => {
                    if (otra !== fila) {
                        contraerAjuste(otra);
                    }
                });
            fila.querySelector('.ajusteCuerpo')
                ?.classList.remove('hidden');
            const icono = fila.querySelector('.iconoAjuste');
            if (icono) {
                icono.textContent = '⌃';
            }
        }
        function agregarAjuste(datos = null,contraido = false) {
            const indice = contadorAjustes++;
            const fila = document.createElement('div');
            fila.className = 'filaAjustePrecio overflow-hidden rounded-2xl border border-white/10 bg-black/10';
            fila.innerHTML = `
                <button type="button"
                    class="cabeceraAjuste flex w-full items-center justify-between gap-3 p-4 text-left">
                    <div class="min-w-0">
                        <p class="resumenAjusteNombre truncate font-semibold text-white">
                            Nueva opción
                        </p>
                        <p class="resumenAjusteDetalle mt-1 truncate text-xs text-gray-500">
                            Precio +$0.00 · Costo +$0.00
                        </p>
                    </div>
                    <span class="iconoAjuste text-gray-500">⌃</span>
                </button>
                <div class="ajusteCuerpo border-t border-white/10 p-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Dato
                            </label>
                            <select name="ajustes[${indice}][campo]"
                                class="ajusteCampo block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-white"
                                required>
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Opción
                            </label>
                            <select name="ajustes[${indice}][opcion]"
                                class="ajusteOpcion block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-white"
                                required>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Aumento de precio
                            </label>
                            <input type="number"
                                name="ajustes[${indice}][aumento_precio]"
                                value="0.00"
                                min="0"
                                step="0.01"
                                class="ajustePrecio block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                                required>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Aumento de costo
                            </label>
                            <input type="number"
                                name="ajustes[${indice}][aumento_costo]"
                                value="0.00"
                                min="0"
                                step="0.01"
                                class="ajusteCosto block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                                required>
                        </div>
                    </div>
                    <div class="mt-5 flex items-center justify-between gap-3">
                        <button type="button"
                            class="btnQuitarAjuste rounded-xl border border-red-500/20 px-4 py-2 text-sm font-semibold text-red-400">
                            Eliminar
                        </button>
                        <button type="button"
                            class="btnListoAjuste rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2 text-sm font-semibold text-emerald-300">
                            Listo
                        </button>
                    </div>
                </div>
            `;
            const selectCampo = fila.querySelector('.ajusteCampo');
            const selectOpcion = fila.querySelector('.ajusteOpcion');
            const inputPrecio = fila.querySelector('.ajustePrecio');
            const inputCosto = fila.querySelector('.ajusteCosto');
            contenedorAjustes.appendChild(fila);
            llenarSelectCampos(selectCampo,datos?.field_key || '');
            if (
                !selectCampo.value
                && camposSeleccionados().length > 0
            ) {
                const disponibles = combinacionesDisponibles(fila);
                if (disponibles.length > 0) {
                    selectCampo.value = disponibles[0].campo;
                }
            }
            llenarSelectOpciones(fila,datos?.option || '');
            if (
                !selectOpcion.value
                && selectCampo.value
            ) {
                const disponibles =
                    combinacionesDisponibles(fila)
                        .filter(
                            combinacion =>
                                combinacion.campo
                                === selectCampo.value
                        );
                if (disponibles.length > 0) {
                    selectOpcion.value = disponibles[0].opcion;
                }
            }
            inputPrecio.value = datos?.price_delta ?? '0.00';
            inputCosto.value = datos?.cost_delta ?? '0.00';
            selectCampo.addEventListener(
                'change',
                function () {
                    llenarSelectOpciones(fila);
                    actualizarResumenAjuste(fila);
                    actualizarBotonAgregar();
                }
            );
            selectOpcion.addEventListener(
                'change',
                function () {
                    actualizarResumenAjuste(fila);
                    actualizarBotonAgregar();
                }
            );
            inputPrecio.addEventListener(
                'input',
                function () {
                    actualizarResumenAjuste(fila);
                }
            );
            inputCosto.addEventListener(
                'input',
                function () {
                    actualizarResumenAjuste(fila);
                }
            );
            fila.querySelector('.btnQuitarAjuste')
                .addEventListener(
                    'click',
                    function () {
                        fila.remove();
                        actualizarBotonAgregar();
                    }
                );
            fila.querySelector('.btnListoAjuste')
                .addEventListener(
                    'click',
                    function () {
                        contraerAjuste(fila);
                        actualizarBotonAgregar();
                    }
                );
            fila.querySelector('.cabeceraAjuste')
                .addEventListener(
                    'click',
                    function () {
                        const cuerpo = fila.querySelector('.ajusteCuerpo');
                        if (
                            cuerpo.classList.contains(
                                'hidden'
                            )
                        ) {
                            expandirAjuste(fila);
                        } else {
                            contraerAjuste(fila);
                        }
                    }
                );
            actualizarResumenAjuste(fila);
            if (contraido) {
                contraerAjuste(fila);
            } else {
                expandirAjuste(fila);
            }
            actualizarBotonAgregar();
        }
        function actualizarVisibilidadPrecios() {
            const seleccionado = document.querySelector('input[name="tiene_cambios"]:checked');
            if (seleccionado?.value === '1') {
                seccionAjustes.classList.remove('hidden');
            } else {
                seccionAjustes.classList.add('hidden');
            }
            actualizarBotonAgregar();
        }
        document
            .querySelectorAll(
                'input[name="tiene_cambios"]'
            )
            .forEach(radio => {
                radio.addEventListener('change',actualizarVisibilidadPrecios);
            });
        document
            .querySelectorAll(
                '.campoPrecioCheckbox'
            )
            .forEach(checkbox => {
                checkbox.addEventListener(
                    'change',
                    function () {
                        const seleccionados = camposSeleccionados();
                        contenedorAjustes
                            .querySelectorAll(
                                '.filaAjustePrecio'
                            )
                            .forEach(fila => {
                                const select = fila.querySelector('.ajusteCampo');
                                if (
                                    select.value
                                    && !seleccionados.includes(
                                        select.value
                                    )
                                ) {
                                    fila.remove();
                                    return;
                                }
                                const valorActual = select.value;
                                llenarSelectCampos(select,valorActual);
                                llenarSelectOpciones(
                                    fila,
                                    fila.querySelector(
                                        '.ajusteOpcion'
                                    ).value
                                );
                            });
                        actualizarBotonAgregar();
                    }
                );
            });
        document
            .getElementById('btnAgregarAjuste')
            ?.addEventListener(
                'click',
                function () {
                    if (
                        combinacionesDisponibles()
                            .length === 0
                    ) {
                        actualizarBotonAgregar();
                        return;
                    }
                    agregarAjuste(null,false);
                }
            );
        if (
            configPrecio
            && configPrecio.enabled
            && Array.isArray(
                configPrecio.adjustments
            )
        ) {
            configPrecio.adjustments
                .forEach(ajuste => {
                    agregarAjuste(ajuste,true);
                });
        }
        actualizarVisibilidadPrecios();
        actualizarBotonAgregar();
    }
    const formSesiones = document.getElementById('formSesiones');
    if (formSesiones) {
        const contenedor = document.getElementById('contenedorSesiones');
        const preview = document.getElementById('vistaPreviaSesiones');
        const panelSesionUnica = document.getElementById('panelSesionUnica');
        const seccionSesiones = document.getElementById('seccionSesiones');
        const btnAgregar = document.getElementById('btnAgregarSesion');
        const advertenciaSolapamientos = document.getElementById('advertenciaSolapamientos');
        const textoSolapamientos = document.getElementById('textoSolapamientos');
                    const confirmarSolapamientos = document.getElementById('confirmarSolapamientos');
        const realizacionDesde = document.getElementById('realizacionDesde').value;
        const realizacionHasta = document.getElementById('realizacionHasta').value;
        const cupoGeneralValor = document.getElementById('cupoGeneralActividad').value;
        const cupoGeneral =
            cupoGeneralValor === ''
                ? null
                : parseInt(cupoGeneralValor, 10);
        const existentes =
            decodificarBase64Json(
                document.getElementById('sesionesActuales').value
            ) || [];
        const espaciosCatalogo = decodificarBase64Json(document.getElementById('espaciosDisponibles').value) || [];
        const recursosCatalogo = decodificarBase64Json(document.getElementById('recursosDisponibles').value) || [];
        const espacioActividad = document.getElementById('espacioActividad').value;
        const ubicacionActividad = document.getElementById('ubicacionActividad').value;
        let contadorSesiones = 0;
        function fechaLocalActual() {
            const ahora = new Date();
            ahora.setMinutes(
                ahora.getMinutes()
                - ahora.getTimezoneOffset()
            );
            return ahora
                .toISOString()
                .slice(0, 16);
        }
        function minimoNuevaSesion() {
            const ahora = fechaLocalActual();
            if (!realizacionDesde) {
                return ahora;
            }
            return realizacionDesde > ahora
                ? realizacionDesde
                : ahora;
        }
        function formatearFecha(valor) {
            if (!valor) {
                return 'Fecha pendiente';
            }
            const fecha = new Date(valor);
            if (
                Number.isNaN(
                    fecha.getTime()
                )
            ) {
                return 'Fecha pendiente';
            }
            return new Intl.DateTimeFormat(
                'es-SV',
                {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                }
            ).format(fecha);
        }
        function modoSeleccionado() {
            return document.querySelector(
                'input[name="modo_sesiones"]:checked'
            )?.value ?? null;
        }
        function resumenSesion(card) {
            const nombre =
                card.querySelector(
                    '.sesionNombre'
                ).value.trim()
                || 'Nueva sesión';
            const inicio =
                card.querySelector(
                    '.sesionInicio'
                ).value;
            const espacioSelect = card.querySelector('.sesionEspacio');
            const ubicacionManual = card.querySelector('.sesionUbicacion').value.trim();
            const idEspacio = espacioSelect.value === '__heredar__' ? espacioActividad : espacioSelect.value;
            const espacioInfo = espaciosCatalogo.find(item => String(item.id) === String(idEspacio));
            const ubicacion = espacioInfo?.nombre || ubicacionManual || (espacioSelect.value === '__heredar__' ? ubicacionActividad : '');
            const reserva =
                card.querySelector(
                    '.sesionReserva'
                ).checked;
            const obligatoria =
                card.querySelector(
                    '.sesionObligatoria'
                ).checked;
            card.querySelector(
                '.resumenSesionNombre'
            ).textContent =
                nombre;
            let detalle = formatearFecha(inicio);
            if (ubicacion) {
                detalle +=
                    ` · ${ubicacion}`;
            }
            if (reserva) {
                detalle +=
                    ' · Reserva';
            }
            if (obligatoria) {
                detalle +=
                    ' · Obligatoria';
            }
            card.querySelector(
                '.resumenSesionDetalle'
            ).textContent =
                detalle;
        }
        function renderPreviewSesiones() {
            if (!preview) {
                return;
            }
            preview.innerHTML = '';
            const modo = modoSeleccionado();
            if (
                modo === 'ninguna'
                || !modo
            ) {
                preview.innerHTML =
                    '<div class="rounded-xl border border-white/5 bg-white/[0.02] p-4"><p class="text-sm font-medium text-gray-300">Sin programación específica</p><p class="mt-1 text-xs leading-5 text-gray-600">No se mostrarán sesiones ni turnos.</p></div>';
                return;
            }
            if (modo === 'unica') {
                preview.innerHTML =
                    `<div class="rounded-xl border border-cyan-500/20 bg-cyan-500/[0.04] p-4"><p class="font-semibold text-white">Sesión principal</p><p class="mt-1 text-xs text-gray-500">${escaparHtml(formatearFecha(realizacionDesde))}</p>${cupoGeneral !== null ? `<p class="mt-2 text-xs text-gray-400">Cupo: ${cupoGeneral}</p>` : ''}</div>`;
                return;
            }
            const cards =
                Array.from(
                    contenedor.querySelectorAll(
                        '.sesionCard'
                    )
                );
            if (cards.length === 0) {
                preview.innerHTML =
                    '<p class="text-sm text-gray-600">Agrega sesiones para ver la vista previa.</p>';
                return;
            }
            cards.forEach(
                (card, indice) => {
                    const nombre =
                        card.querySelector(
                            '.sesionNombre'
                        ).value.trim()
                        || `Sesión ${indice + 1}`;
                    const inicio =
                        card.querySelector(
                            '.sesionInicio'
                        ).value;
                    const fin =
                        card.querySelector(
                            '.sesionFin'
                        ).value;
                    const ubicacion =
                        card.querySelector(
                            '.sesionUbicacion'
                        ).value.trim();
                    const cupo =
                        card.querySelector(
                            '.sesionCupo'
                        ).value;
                    const reserva =
                        card.querySelector(
                            '.sesionReserva'
                        ).checked;
                    const bloque = document.createElement('div');
                    bloque.className = 'rounded-xl border border-white/10 bg-white/[0.02] p-4';
                    bloque.innerHTML =
                        `<p class="font-semibold text-white">${escaparHtml(nombre)}</p><p class="mt-1 text-xs text-gray-500">${escaparHtml(formatearFecha(inicio))}</p>${fin ? `<p class="text-xs text-gray-600">Hasta: ${escaparHtml(formatearFecha(fin))}</p>` : ''}${ubicacion ? `<p class="mt-2 text-xs text-gray-400">${escaparHtml(ubicacion)}</p>` : ''}${cupo !== '' ? `<p class="text-xs text-gray-400">Cupo: ${escaparHtml(cupo)}</p>` : (cupoGeneral !== null ? `<p class="text-xs text-gray-400">Cupo heredado: ${cupoGeneral}</p>` : '')}${reserva ? '<span class="mt-2 inline-flex rounded-full bg-cyan-500/10 px-2 py-1 text-[10px] font-semibold text-cyan-300">Requiere reserva</span>' : ''}`;
                    preview.appendChild(bloque);
                }
            );
        }
        function contraerSesion(card) {
            card.querySelector(
                '.sesionCuerpo'
            ).classList.add('hidden');
            card.querySelector(
                '.iconoSesion'
            ).textContent =
                '⌄';
            resumenSesion(card);
        }
        function expandirSesion(card) {
            contenedor
                .querySelectorAll(
                    '.sesionCard'
                )
                .forEach(otra => {
                    if (otra !== card) {
                        contraerSesion(otra);
                    }
                });
            card.querySelector(
                '.sesionCuerpo'
            ).classList.remove('hidden');
            card.querySelector(
                '.iconoSesion'
            ).textContent =
                '⌃';
        }
        function detectarSolapamientos() {
            const cards =
                Array.from(
                    contenedor.querySelectorAll(
                        '.sesionCard'
                    )
                );
            const cruces = [];
            for (
                let i = 0;
                i < cards.length;
                i++
            ) {
                for (
                    let j = i + 1;
                    j < cards.length;
                    j++
                ) {
                    const aInicioValor =
                        cards[i]
                            .querySelector(
                                '.sesionInicio'
                            )
                            .value;
                    const bInicioValor =
                        cards[j]
                            .querySelector(
                                '.sesionInicio'
                            )
                            .value;
                    if (
                        !aInicioValor
                        || !bInicioValor
                    ) {
                        continue;
                    }
                    const aFinValor =
                        cards[i]
                            .querySelector(
                                '.sesionFin'
                            )
                            .value;
                    const bFinValor =
                        cards[j]
                            .querySelector(
                                '.sesionFin'
                            )
                            .value;
                    const aInicio = new Date(aInicioValor);
                    const bInicio = new Date(bInicioValor);
                    const aFin =
                        aFinValor
                            ? new Date(
                                aFinValor
                            )
                            : null;
                    const bFin =
                        bFinValor
                            ? new Date(
                                bFinValor
                            )
                            : null;
                    let seCruzan = false;
                    if (
                        aFin
                        && bFin
                    ) {
                        seCruzan =
                            aInicio < bFin
                            && aFin > bInicio;
                    } else if (
                        !aFin
                        && !bFin
                    ) {
                        seCruzan =
                            aInicio.getTime()
                            === bInicio.getTime();
                    } else if (
                        !aFin
                        && bFin
                    ) {
                        seCruzan =
                            aInicio.getTime()
                                === bInicio.getTime()
                            || (
                                aInicio > bInicio
                                && aInicio < bFin
                            );
                    } else if (
                        aFin
                        && !bFin
                    ) {
                        seCruzan =
                            bInicio.getTime()
                                === aInicio.getTime()
                            || (
                                bInicio > aInicio
                                && bInicio < aFin
                            );
                    }
                    if (seCruzan) {
                        const nombreA =
                            cards[i]
                                .querySelector(
                                    '.sesionNombre'
                                )
                                .value
                                .trim()
                            || `Sesión ${i + 1}`;
                        const nombreB =
                            cards[j]
                                .querySelector(
                                    '.sesionNombre'
                                )
                                .value
                                .trim()
                            || `Sesión ${j + 1}`;
                        cruces.push(
                            `${nombreA} con ${nombreB}`
                        );
                    }
                }
            }
            const unicos = [...new Set(cruces)];
            advertenciaSolapamientos
                .classList.toggle('hidden',unicos.length === 0);
            textoSolapamientos.textContent =
                unicos.length
                    ? `Coinciden: ${unicos.join('; ')}.`
                    : '';
            if (unicos.length === 0) {
                confirmarSolapamientos.checked = false;
            }
            return unicos;
        }
        function actualizarTodoSesiones() {
            renderPreviewSesiones();
            detectarSolapamientos();
        }
        function idsEspaciosPermitidos() {
            if (!espacioActividad) return [];
            const permitidos = new Set([String(espacioActividad)]);
            let cambio = true;
            while (cambio) {
                cambio = false;
                espaciosCatalogo.forEach(item => {
                    if (item.contenedor && permitidos.has(String(item.contenedor)) && !permitidos.has(String(item.id))) {
                        permitidos.add(String(item.id));
                        cambio = true;
                    }
                });
            }
            return [...permitidos];
        }
        function cargarEspaciosSesion(select,seleccionado = '') {
            const permitidos = idsEspaciosPermitidos();
            let html = '<option value="">Otro / lugar externo</option>';
            if (espacioActividad) html = '<option value="__heredar__">Usar ubicación general de la actividad</option>' + html;
            espaciosCatalogo.filter(item => permitidos.includes(String(item.id))).forEach(item => {
                html += `<option value="${item.id}" data-capacidad="${item.capacidad ?? ''}">${escaparHtml(item.nombre)}</option>`;
            });
            select.innerHTML = html;
            select.value = seleccionado ? String(seleccionado) : (espacioActividad ? '__heredar__' : '');
        }
        function recursosPermitidos(idEspacio) {
            return recursosCatalogo.filter(recurso => recurso.es_movil || (idEspacio && recurso.espacios && recurso.espacios[String(idEspacio)]));
        }
        function renderRecursosSesion(card,datosRecursos = []) {
            const select = card.querySelector('.sesionEspacio');
            const contenedor = card.querySelector('.sesionRecursos');
            const idEspacio = select.value === '__heredar__' ? espacioActividad : select.value;
            const usados = new Map((datosRecursos || []).map(item => [String(item.id_recurso),item]));
            const disponibles = recursosPermitidos(idEspacio);
            contenedor.innerHTML = disponibles.length ? disponibles.map(recurso => {
                const usado = usados.get(String(recurso.id));
                const maximo = idEspacio && !recurso.es_movil ? (recurso.espacios[String(idEspacio)] ?? '') : '';
                return `<div class="grid grid-cols-1 gap-2 rounded-lg border border-white/5 p-3 sm:grid-cols-[1fr_90px_1fr]"><label class="flex items-center gap-2 text-sm text-gray-300"><input type="hidden" name="sesiones[${card.dataset.indice}][recursos][${recurso.id}][seleccionado]" value="0"><input type="checkbox" name="sesiones[${card.dataset.indice}][recursos][${recurso.id}][seleccionado]" value="1" ${usado ? 'checked' : ''}>${escaparHtml(recurso.nombre)}</label><input type="number" min="1" ${maximo ? `max="${maximo}"` : ''} name="sesiones[${card.dataset.indice}][recursos][${recurso.id}][cantidad]" value="${usado?.cantidad ?? 1}" class="rounded-lg border border-white/10 bg-black/20 px-3 py-2 text-sm text-white"><input type="text" maxlength="300" name="sesiones[${card.dataset.indice}][recursos][${recurso.id}][observacion]" value="${escaparHtml(usado?.observacion ?? '')}" placeholder="Observación" class="rounded-lg border border-white/10 bg-black/20 px-3 py-2 text-sm text-white"></div>`;
            }).join('') : '<p class="text-xs text-gray-600">No hay recursos disponibles para esta ubicación.</p>';
        }
        function actualizarUbicacionSesion(card,datosRecursos = null) {
            const select = card.querySelector('.sesionEspacio');
            const ubicacion = card.querySelector('.sesionUbicacion');
            const cupo = card.querySelector('.sesionCupo');
            const capacidadTexto = card.querySelector('.sesionCapacidad');
            const idEspacio = select.value === '__heredar__' ? espacioActividad : select.value;
            ubicacion.classList.toggle('hidden',Boolean(idEspacio));
            if (idEspacio) ubicacion.value = '';
            const espacio = espaciosCatalogo.find(item => String(item.id) === String(idEspacio));
            const capacidad = espacio?.capacidad ?? '';
            capacidadTexto.classList.toggle('hidden',!capacidad);
            capacidadTexto.textContent = capacidad ? `Capacidad del espacio: ${capacidad} personas.` : '';
            const limites = [cupoGeneral,capacidad].filter(valor => valor !== null && valor !== '' && !Number.isNaN(Number(valor))).map(Number);
            if (limites.length) cupo.max = Math.min(...limites); else cupo.removeAttribute('max');
            renderRecursosSesion(card,datosRecursos ?? []);
        }
        function agregarSesion(datos = null,contraida = false) {
            const indice = contadorSesiones++;
            const card = document.createElement('div');
            const existente = Boolean(datos?.id_sesion);
            card.className = 'sesionCard overflow-hidden rounded-2xl border border-white/10 bg-black/10';
            card.dataset.indice = indice;
                            card.innerHTML = `
                <button type="button" class="cabeceraSesion flex w-full items-center justify-between gap-3 p-4 text-left">
                    <div class="min-w-0">
                        <p class="resumenSesionNombre truncate font-semibold text-white">Nueva sesión</p>
                        <p class="resumenSesionDetalle mt-1 truncate text-xs text-gray-500">Configura fecha y horario</p>
                    </div>
                    <span class="iconoSesion text-gray-500">⌃</span>
                </button>
                <div class="sesionCuerpo border-t border-white/10 p-4">
                    <input type="hidden" name="sesiones[${indice}][id_sesion]" value="${datos?.id_sesion ?? ''}">
                    <div class="flex items-start gap-3">
                        <div class="flex-1">
                            <label class="mb-2 block text-sm font-medium text-gray-300">Nombre de la sesión</label>
                            <input type="text" name="sesiones[${indice}][nombre]" class="sesionNombre block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white" placeholder="Ej. Ponencia inaugural, Taller IoT" required>
                        </div>
                        <button type="button" class="btnQuitarSesion mt-7 h-10 w-10 rounded-xl border border-red-500/20 text-red-400">×</button>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Inicio</label>
                            <input type="datetime-local" name="sesiones[${indice}][fecha_inicio]" class="sesionInicio block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white" required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Finalización</label>
                            <input type="datetime-local" name="sesiones[${indice}][fecha_fin]" class="sesionFin block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Lugar</label>
                            <select name="sesiones[${indice}][id_espacio]" class="sesionEspacio block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-white"></select>
                            <input type="text" name="sesiones[${indice}][ubicacion]" class="sesionUbicacion mt-2 hidden w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white" placeholder="Escribe el lugar externo">
                            <p class="sesionCapacidad mt-2 hidden text-xs text-gray-500"></p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Cupo de esta sesión</label>
                            <input type="number" name="sesiones[${indice}][cupo]" min="0" ${cupoGeneral !== null ? `max="${cupoGeneral}"` : ''} class="sesionCupo block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white" placeholder="${cupoGeneral !== null ? `Vacío = hereda ${cupoGeneral}` : 'Vacío = sin límite propio'}">
                        </div>
                    </div>
                    <div class="mt-4 rounded-xl border border-white/10 p-4">
                        <p class="text-sm font-medium text-gray-300">Recursos que utilizará</p>
                        <p class="mt-1 text-xs text-gray-600">Se muestran los recursos del espacio seleccionado y los recursos móviles.</p>
                        <div class="sesionRecursos mt-3 space-y-2"></div>
                    </div>
                    <div class="mt-4">
                        <label class="mb-2 block text-sm font-medium text-gray-300">Enlace de acceso</label>
                        <input type="url" name="sesiones[${indice}][enlace_acceso]" class="sesionEnlace block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white" placeholder="Opcional · para sesiones virtuales o híbridas">
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-white/10 p-4">
                            <input type="hidden" name="sesiones[${indice}][requiere_reserva]" value="0">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" name="sesiones[${indice}][requiere_reserva]" value="1" class="sesionReserva mt-1">
                                <div>
                                    <p class="text-sm font-medium text-gray-300">Requiere reserva</p>
                                    <p class="mt-1 text-xs leading-5 text-gray-600">La persona deberá elegir esta sesión específicamente.</p>
                                </div>
                            </label>
                        </div>
                        <div class="rounded-xl border border-white/10 p-4">
                            <input type="hidden" name="sesiones[${indice}][obligatoria]" value="0">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" name="sesiones[${indice}][obligatoria]" value="1" class="sesionObligatoria mt-1">
                                <div>
                                    <p class="text-sm font-medium text-gray-300">Sesión obligatoria</p>
                                    <p class="mt-1 text-xs leading-5 text-gray-600">Úsalo cuando forme parte obligatoria de la actividad.</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end">
                        <button type="button" class="btnListoSesion rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-300">
                            Listo
                        </button>
                    </div>
                </div>
            `;
            contenedor.appendChild(card);
            const nombre = card.querySelector('.sesionNombre');
            const inicio = card.querySelector('.sesionInicio');
            const fin = card.querySelector('.sesionFin');
            const espacio = card.querySelector('.sesionEspacio');
            const ubicacion = card.querySelector('.sesionUbicacion');
            const capacidadTexto = card.querySelector('.sesionCapacidad');
            const recursosContenedor = card.querySelector('.sesionRecursos');
            const cupo = card.querySelector('.sesionCupo');
            const enlace = card.querySelector('.sesionEnlace');
            const reserva = card.querySelector('.sesionReserva');
            const obligatoria = card.querySelector('.sesionObligatoria');
            cargarEspaciosSesion(espacio,datos?.id_espacio ?? '');
            if (datos && !datos.id_espacio && datos.ubicacion) espacio.value = '';
            actualizarUbicacionSesion(card,datos?.recursos ?? []);
            inicio.min =
                existente
                    ? realizacionDesde
                    : minimoNuevaSesion();
            fin.min =
                existente
                    ? realizacionDesde
                    : minimoNuevaSesion();
            if (realizacionHasta) {
                inicio.max = realizacionHasta;
                fin.max = realizacionHasta;
            }
            if (datos) {
                nombre.value = datos.nombre ?? '';
                inicio.value =
                    datos.fecha_inicio
                    ?? '';
                fin.value =
                    datos.fecha_fin
                    ?? '';
                ubicacion.value =
                    datos.ubicacion
                    ?? '';
                cupo.value = datos.cupo ?? '';
                enlace.value =
                    datos.enlace_acceso
                    ?? '';
                reserva.checked =
                    datos.requiere_reserva
                    === true;
                obligatoria.checked =
                    datos.obligatoria
                    === true;
            }
            espacio.addEventListener('change',() => {
                actualizarUbicacionSesion(card,[]);
                resumenSesion(card);
                actualizarTodoSesiones();
            });
            [
                nombre,
                inicio,
                fin,
                ubicacion,
                cupo,
                enlace,
            ].forEach(input => {
                input.addEventListener(
                    'input',
                    () => {
                        resumenSesion(card);
                        actualizarTodoSesiones();
                    }
                );
                input.addEventListener(
                    'change',
                    () => {
                        resumenSesion(card);
                        actualizarTodoSesiones();
                    }
                );
            });
            [
                reserva,
                obligatoria,
            ].forEach(input =>
                input.addEventListener(
                    'change',
                    () => {
                        resumenSesion(card);
                        actualizarTodoSesiones();
                    }
                )
            );
            card.querySelector(
                '.cabeceraSesion'
            ).addEventListener(
                'click',
                () =>
                    card.querySelector(
                        '.sesionCuerpo'
                    ).classList.contains(
                        'hidden'
                    )
                        ? expandirSesion(
                            card
                        )
                        : contraerSesion(
                            card
                        )
            );
            card.querySelector(
                '.btnListoSesion'
            ).addEventListener(
                'click',
                () => {
                    contraerSesion(card);
                    actualizarTodoSesiones();
                }
            );
            card.querySelector(
                '.btnQuitarSesion'
            ).addEventListener(
                'click',
                () => {
                    card.remove();
                    actualizarTodoSesiones();
                }
            );
            resumenSesion(card);
            contraida
                ? contraerSesion(
                    card
                )
                : expandirSesion(card);
            actualizarTodoSesiones();
        }
        function actualizarModoSesiones() {
            const modo = modoSeleccionado();
            panelSesionUnica
                .classList.toggle('hidden',modo !== 'unica');
            seccionSesiones
                .classList.toggle('hidden',modo !== 'multiples');
            if (modo === 'multiples') {
                while (
                    contenedor
                        .querySelectorAll(
                            '.sesionCard'
                        ).length < 2
                ) {
                    agregarSesion(null,false);
                }
            }
            actualizarTodoSesiones();
        }
        document
            .querySelectorAll(
                'input[name="modo_sesiones"]'
            )
            .forEach(radio =>
                radio.addEventListener('change',actualizarModoSesiones)
            );
        btnAgregar?.addEventListener(
            'click',
            () =>
                agregarSesion(null,false)
        );
        existentes.forEach(
            sesion =>
                agregarSesion(sesion,true)
        );
        formSesiones.addEventListener(
            'submit',
            function (event) {
                formSesiones.querySelectorAll('.sesionEspacio').forEach(select => {
                    if (select.value === '__heredar__') select.value = espacioActividad;
                });
                if (
                    modoSeleccionado()
                    === 'multiples'
                ) {
                    const total =
                        contenedor
                            .querySelectorAll(
                                '.sesionCard'
                            )
                            .length;
                    if (total < 2) {
                        event.preventDefault();
                        alert('Si seleccionaste varias sesiones, agrega al menos dos.');
                        return;
                    }
                    const cruces = detectarSolapamientos();
                    if (
                        cruces.length > 0
                        && !confirmarSolapamientos
                            .checked
                    ) {
                        event.preventDefault();
                        advertenciaSolapamientos
                            .scrollIntoView({
                                behavior: 'smooth',
                                block: 'center',
                            });
                        return;
                    }
                }
            }
        );
        actualizarModoSesiones();
    }
});
</script>
@endsection
