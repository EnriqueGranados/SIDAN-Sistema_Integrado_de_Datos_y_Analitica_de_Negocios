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
                    'modalidad' => $sesion->enlace_acceso ? 'virtual' : 'presencial',
                    'enlace_acceso' => $sesion->enlace_acceso,
                    'cupo' => $sesion->cupo,
                    'requiere_reserva' => (bool) $sesion->requiere_reserva,
                    'obligatoria' => (bool) $sesion->obligatoria,
                ];
            })->values()->all(),
            JSON_UNESCAPED_UNICODE
        )
    );
    $sesionPrincipalActual = $actividad->sesiones->sortBy('orden')->first();
    $modalidadSesionUnicaActual = $sesionPrincipalActual?->enlace_acceso
        ? 'virtual'
        : 'presencial';
    $enlaceSesionUnicaActual = $sesionPrincipalActual?->enlace_acceso ?? '';
    $idEspacioSesionUnicaActual = $sesionPrincipalActual?->id_espacio ?? null;
    $ubicacionSesionUnicaActual = $sesionPrincipalActual?->ubicacion ?? (!$actividad->id_espacio ? $actividad->ubicacion_externa : null);

    $espaciosCodificados = base64_encode(json_encode($espacios->map(fn ($espacio) => [
        'id' => $espacio->id_espacio,
        'nombre' => $espacio->nombre,
        'contenedor' => $espacio->id_espacio_contenedor,
        'capacidad' => $espacio->capacidad,
        'permite_actividades' => (bool) $espacio->permite_actividades,
        'direccion' => $espacio->direccion,
        'ruta' => method_exists($espacio, 'rutaJerarquica') ? $espacio->rutaJerarquica() : $espacio->nombre,
        'contexto' => method_exists($espacio, 'rutaContenedor') ? $espacio->rutaContenedor() : null,
        'pendiente_validacion' => method_exists($espacio, 'estaPendienteValidacion') ? $espacio->estaPendienteValidacion() : false,
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
                <div class="mb-6 rounded-2xl border border-amber-500/20 bg-amber-500/[0.05] p-4 sm:p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-400">Promociones y descuentos</p>
                            <h3 class="mt-1 font-semibold text-white">Administra códigos y promociones automáticas</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500">Configura vigencia, límites y los productos a los que aplica cada promoción sin alargar este paso.</p>
                            <p class="mt-2 text-xs font-semibold text-amber-300">{{ $actividad->promociones->count() }} promoción{{ $actividad->promociones->count() === 1 ? '' : 'es' }} configurada{{ $actividad->promociones->count() === 1 ? '' : 's' }}</p>
                        </div>
                        <a href="{{ route('admin.actividades.promociones.index', $actividad) }}"
                            class="inline-flex shrink-0 items-center justify-center rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-2.5 text-sm font-semibold text-amber-200 transition hover:bg-amber-500/15">
                            Administrar promociones
                        </a>
                    </div>
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
                    <input type="hidden"
                        name="ajustes_payload"
                        id="ajustesPayloadPrecio"
                        value="">
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
                    <div id="erroresAjustesPrecio"
                        class="mt-5 hidden rounded-xl border border-red-500/20 bg-red-500/[0.06] px-4 py-3 text-sm text-red-200">
                    </div>
                    <div id="seccionAjustesPrecio" class="mt-6 hidden">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="font-semibold text-white">
                                    Variantes con aumento
                                </h3>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Agrega únicamente las opciones que cambien el precio o costo base.
                                </p>
                            </div>
                            <button type="button"
                                id="btnAgregarAjustePrecio"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-violet-500/30 bg-violet-500/10 px-4 py-2.5 text-sm font-semibold text-violet-200 transition hover:bg-violet-500/15">
                                <span class="text-base leading-none">+</span>
                                Agregar variante
                            </button>
                        </div>

                        <div class="mt-4 overflow-hidden rounded-2xl border border-white/10 bg-black/10">
                            <div class="hidden border-b border-white/10 bg-white/[0.025] px-3 py-2.5 lg:grid lg:grid-cols-[minmax(150px,.9fr)_minmax(160px,1fr)_minmax(145px,.9fr)_minmax(145px,.9fr)_44px] lg:gap-3">
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                                    Dato
                                </span>
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                                    Opción
                                </span>
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                                    Aumento de precio
                                </span>
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                                    Aumento de costo
                                </span>
                                <span></span>
                            </div>

                            <div id="contenedorAjustesPrecio"></div>

                            <div id="mensajeSinAjustesPrecio"
                                class="px-4 py-7 text-center text-sm text-gray-500">
                                No has agregado variantes con aumento.
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-blue-500/20 bg-blue-500/[0.05] px-4 py-3">
                            <p class="text-xs leading-5 text-gray-400">
                                Solo agrega las opciones que realmente varían. Las demás conservarán automáticamente el precio y costo base.
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
                <input type="hidden" id="urlBuscarEspaciosSesion" value="{{ route('admin.espacios.buscar') }}">
                <input type="hidden" id="urlCrearEspacioSesion" value="{{ route('admin.espacios.crear-rapido') }}">
                <input type="hidden" id="csrfSesiones" value="{{ csrf_token() }}">
                <input type="hidden" id="actividadIdSesiones" value="{{ $actividad->id_actividad }}">
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
                <div id="errorSesiones"
                    class="mb-5 hidden rounded-xl border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-300">
                </div>

                <div id="estadoBorradorSesiones"
                    class="mb-5 hidden items-center justify-between gap-3 rounded-xl border border-cyan-500/20 bg-cyan-500/[0.06] p-3">
                    <p id="textoBorradorSesiones" class="text-sm text-cyan-200"></p>
                    <button type="button"
                        id="descartarBorradorSesiones"
                        class="shrink-0 rounded-lg border border-white/10 px-3 py-2 text-xs font-semibold text-gray-300 transition hover:bg-white/5 hover:text-white">
                        Descartar borrador
                    </button>
                </div>

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
                        <div class="min-w-0 flex-1">
                            <h3 class="font-semibold text-white">
                                Sesión principal automática
                            </h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500">
                                SIDAN tomará las fechas y el cupo general. Solo indica si será presencial o virtual.
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

                            <div class="mt-5">
                                <p class="mb-2 text-sm font-medium text-gray-300">
                                    Modalidad
                                </p>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <label class="cursor-pointer rounded-xl border border-white/10 bg-black/10 p-3">
                                        <div class="flex items-start gap-3">
                                            <input type="radio"
                                                name="sesion_unica_modalidad"
                                                value="presencial"
                                                class="sesionUnicaModalidad mt-1"
                                                @checked($modalidadSesionUnicaActual === 'presencial')>
                                            <div>
                                                <p class="text-sm font-semibold text-white">Presencial</p>
                                                <p class="mt-1 text-xs leading-5 text-gray-600">
                                                    Selecciona el espacio específico donde se realizará.
                                                </p>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer rounded-xl border border-white/10 bg-black/10 p-3">
                                        <div class="flex items-start gap-3">
                                            <input type="radio"
                                                name="sesion_unica_modalidad"
                                                value="virtual"
                                                class="sesionUnicaModalidad mt-1"
                                                @checked($modalidadSesionUnicaActual === 'virtual')>
                                            <div>
                                                <p class="text-sm font-semibold text-white">Virtual</p>
                                                <p class="mt-1 text-xs leading-5 text-gray-600">
                                                    Solicita un enlace de acceso.
                                                </p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div id="panelSesionUnicaPresencial" class="mt-4 hidden rounded-xl border border-white/10 bg-black/10 p-4">
                                <input type="hidden"
                                    id="sesionUnicaEspacio"
                                    name="sesion_unica_id_espacio"
                                    value="{{ $idEspacioSesionUnicaActual ?? '' }}">
                                <input type="hidden"
                                    id="sesionUnicaUbicacion"
                                    name="sesion_unica_ubicacion"
                                    value="{{ $ubicacionSesionUnicaActual ?? '' }}">

                                <div class="mb-3">
                                    <p class="text-sm font-medium text-gray-300">Lugar de la sesión</p>
                                    <p class="mt-1 text-xs leading-5 text-gray-600">
                                        El lugar general puede ser una facultad, sede o edificio. Aquí debes elegir el espacio que realmente recibirá a los participantes.
                                    </p>
                                </div>

                                <div id="sesionUnicaEspacioSeleccionado" class="hidden rounded-xl border border-cyan-500/20 bg-cyan-500/[0.05] p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <p id="sesionUnicaEspacioNombre" class="text-sm font-semibold text-white"></p>
                                                <span id="sesionUnicaEspacioPendiente" class="hidden rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-300">Pendiente</span>
                                            </div>
                                            <p id="sesionUnicaEspacioRuta" class="mt-1 text-xs leading-5 text-gray-500"></p>
                                        </div>
                                        <button type="button"
                                            id="btnCambiarEspacioSesionUnica"
                                            class="shrink-0 text-xs font-semibold text-cyan-300">
                                            Cambiar
                                        </button>
                                    </div>
                                </div>

                                <div id="sesionUnicaBusquedaEspacio" class="relative">
                                    <input type="text"
                                        id="sesionUnicaBuscarEspacio"
                                        autocomplete="off"
                                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none placeholder:text-gray-600 focus:border-cyan-500"
                                        placeholder="Buscar aula, auditorio, centro de cómputo...">
                                    <div id="sesionUnicaResultadosEspacio" class="absolute left-0 right-0 top-full z-30 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-white/10 bg-[#0f172a] p-2 shadow-2xl"></div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <button type="button"
                                            id="btnUsarEspacioGeneralSesionUnica"
                                            class="hidden rounded-lg border border-white/10 px-3 py-2 text-xs font-semibold text-gray-400">
                                            Usar ubicación general
                                        </button>
                                        <button type="button"
                                            id="btnCrearEspacioSesionUnica"
                                            class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300">
                                            + Registrar espacio
                                        </button>
                                    </div>
                                </div>

                                <div id="sesionUnicaUbicacionExterna" class="mt-3 hidden rounded-lg border border-amber-500/20 bg-amber-500/[0.05] p-3 text-xs leading-5 text-amber-200"></div>
                            </div>

                            <div id="panelSesionUnicaVirtual" class="mt-4 hidden">
                                <label for="sesionUnicaEnlace" class="mb-2 block text-sm font-medium text-gray-300">
                                    Enlace de acceso
                                </label>
                                <input type="url"
                                    id="sesionUnicaEnlace"
                                    name="sesion_unica_enlace_acceso"
                                    value="{{ $enlaceSesionUnicaActual }}"
                                    class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                                    placeholder="https://meet.google.com/...">
                                <p class="mt-2 text-xs leading-5 text-gray-600">
                                    Se pedirá únicamente cuando la sesión sea virtual.
                                </p>
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

            <div id="modalCrearEspacioSesion" class="fixed inset-0 z-[120] hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm">
                <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-white/10 bg-[#111827] shadow-2xl">
                    <div class="flex items-start justify-between gap-4 border-b border-white/10 px-5 py-5 sm:px-6">
                        <div>
                            <h3 class="text-lg font-semibold text-white">Registrar espacio</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-500">Se guardará como espacio reutilizable y quedará pendiente de validación administrativa.</p>
                        </div>
                        <button type="button" id="cerrarModalEspacioSesion" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-white/10 text-gray-400 transition hover:bg-white/5 hover:text-white">×</button>
                    </div>
                    <div class="max-h-[75vh] overflow-y-auto p-5 sm:p-6">
                        <div id="errorEspacioSesion" class="mb-5 hidden rounded-xl border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-300"></div>
                        <div class="space-y-5">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-300">Pertenece a</label>
                                <input type="hidden" id="nuevoEspacioSesionContenedor">
                                <div id="nuevoEspacioSesionContenedorSeleccionado" class="hidden rounded-xl border border-cyan-500/20 bg-cyan-500/[0.05] p-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p id="nuevoEspacioSesionContenedorNombre" class="text-sm font-semibold text-white"></p>
                                            <p id="nuevoEspacioSesionContenedorRuta" class="mt-1 text-xs leading-5 text-gray-500"></p>
                                        </div>
                                        <button type="button" id="cambiarContenedorEspacioSesion" class="shrink-0 text-xs font-semibold text-cyan-300">Cambiar</button>
                                    </div>
                                </div>
                                <div id="nuevoEspacioSesionContenedorBusqueda" class="relative">
                                    <input type="text" id="buscarContenedorEspacioSesion" autocomplete="off" placeholder="Buscar espacio contenedor..." class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none placeholder:text-gray-600 focus:border-cyan-500">
                                    <div id="resultadosContenedorEspacioSesion" class="absolute left-0 right-0 top-full z-20 mt-2 hidden max-h-56 overflow-y-auto rounded-xl border border-white/10 bg-[#0f172a] p-2 shadow-2xl"></div>
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-300">Nombre</label>
                                <input type="text" id="nuevoEspacioSesionNombre" maxlength="150" class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-300">Dirección o referencia</label>
                                <input type="text" id="nuevoEspacioSesionDireccion" maxlength="300" class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-300">Descripción breve</label>
                                <textarea id="nuevoEspacioSesionDescripcion" rows="3" maxlength="1000" class="block w-full resize-none rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none focus:border-emerald-500"></textarea>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-300">Capacidad</label>
                                    <input type="number" id="nuevoEspacioSesionCapacidad" min="1" step="1" class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-300">Latitud</label>
                                    <input type="number" id="nuevoEspacioSesionLatitud" min="-90" max="90" step="any" class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-300">Longitud</label>
                                    <input type="number" id="nuevoEspacioSesionLongitud" min="-180" max="180" step="any" class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none focus:border-emerald-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col-reverse gap-3 border-t border-white/10 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                        <button type="button" id="cancelarModalEspacioSesion" class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-semibold text-gray-300">Cancelar</button>
                        <button type="button" id="guardarEspacioSesion" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-50">Guardar y usar</button>
                    </div>
                </div>
            </div>

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
                <h2 id="modalItemTitulo" class="font-semibold text-white">
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
            novalidate
            class="min-h-0 flex-1 overflow-y-auto">
            @csrf

            <input type="hidden"
                name="_method"
                id="metodoItem"
                value="PUT"
                disabled>

            <div class="space-y-5 p-4 sm:p-6">
                <div id="itemErrorGeneral"
                    class="hidden rounded-xl border border-red-500/25 bg-red-500/[0.07] px-4 py-3 text-sm text-red-300">
                </div>

                <div data-item-field="item_nombre">
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Nombre
                    </label>
                    <input type="text"
                        id="item_nombre"
                        name="item_nombre"
                        maxlength="150"
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                        placeholder="Ej. Camiseta oficial SDS26">
                    <p data-item-error="item_nombre"
                        class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                </div>

                <div data-item-field="item_tipo">
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Tipo
                    </label>
                    <select id="item_tipo"
                        name="item_tipo"
                        class="block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-white">
                        <option value="producto">Producto físico</option>
                        <option value="servicio">Servicio</option>
                        <option value="acceso">Entrada o acceso</option>
                        <option value="reserva">Reserva</option>
                        <option value="donacion">Donación</option>
                        <option value="otro">Otro</option>
                    </select>
                    <p data-item-error="item_tipo"
                        class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                </div>

                <div data-item-field="item_descripcion">
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Descripción
                    </label>
                    <textarea id="item_descripcion"
                        name="item_descripcion"
                        rows="3"
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                        placeholder="Describe brevemente qué recibirá o podrá solicitar la persona."></textarea>
                    <p data-item-error="item_descripcion"
                        class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                </div>

                <div data-item-field="item_imagen">
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

                    <p data-item-error="item_imagen"
                        class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                    <p data-item-error="eliminar_item_imagen"
                        class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div data-item-field="item_precio">
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Precio de venta
                        </label>
                        <input type="number"
                            id="item_precio"
                            name="item_precio"
                            value="0.00"
                            min="0"
                            step="0.01"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                        <p data-item-error="item_precio"
                            class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                    </div>

                    <div data-item-field="item_costo_referencia">
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Costo
                        </label>
                        <input type="number"
                            id="item_costo_referencia"
                            name="item_costo_referencia"
                            value="0.00"
                            min="0"
                            step="0.01"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                        <p data-item-error="item_costo_referencia"
                            class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
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

                <div data-item-field="item_stock_total">
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Cantidad disponible
                    </label>
                    <input type="number"
                        id="item_stock_total"
                        name="item_stock_total"
                        min="0"
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                        placeholder="Vacío = sin límite">
                    <p data-item-error="item_stock_total"
                        class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div data-item-field="item_min_por_inscripcion">
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Mínimo por compra
                        </label>
                        <input type="number"
                            id="item_min_por_inscripcion"
                            name="item_min_por_inscripcion"
                            value="1"
                            min="1"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                        <p data-item-error="item_min_por_inscripcion"
                            class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                    </div>

                    <div data-item-field="item_max_por_inscripcion">
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Máximo por compra
                        </label>
                        <input type="number"
                            id="item_max_por_inscripcion"
                            name="item_max_por_inscripcion"
                            min="1"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                            placeholder="Sin límite">
                        <p data-item-error="item_max_por_inscripcion"
                            class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div data-item-field="item_venta_desde">
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Disponible desde
                        </label>
                        <input type="datetime-local"
                            id="item_venta_desde"
                            name="item_venta_desde"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                        <p data-item-error="item_venta_desde"
                            class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                    </div>

                    <div data-item-field="item_venta_hasta">
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Disponible hasta
                        </label>
                        <input type="datetime-local"
                            id="item_venta_hasta"
                            name="item_venta_hasta"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                        <p data-item-error="item_venta_hasta"
                            class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                    </div>
                </div>

                <p class="-mt-2 text-xs leading-5 text-gray-600">
                    La disponibilidad comercial es independiente de las fechas de realización de la actividad.
                </p>

                <div data-item-field="item_requiere_participante"
                    class="rounded-xl border border-white/10 p-4">
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

                    <p data-item-error="item_requiere_participante"
                        class="mt-1.5 hidden text-xs font-medium text-red-400"></p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-white/10 p-4 sm:flex-row sm:justify-end">
                <button type="button"
                    id="cancelarModalItem"
                    class="rounded-xl border border-white/10 px-5 py-3 text-sm text-gray-400">
                    Cancelar
                </button>

                <button type="submit"
                    id="guardarItemBtn"
                    class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition disabled:cursor-not-allowed disabled:opacity-60">
                    <span id="guardarItemTexto">Agregar elemento</span>
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
        const guardarItemBtn = document.getElementById('guardarItemBtn');
        const guardarItemTexto = document.getElementById('guardarItemTexto');
        const errorGeneral = document.getElementById('itemErrorGeneral');
        const rutaCrear = formItem.action;
        const inputImagen = document.getElementById('item_imagen');
        const previewImagen = document.getElementById('previewItemImagen');
        const placeholderImagen = document.getElementById('placeholderItemImagen');
        const nombreImagen = document.getElementById('nombreItemImagen');
        const contenedorEliminarImagen = document.getElementById('contenedorEliminarItemImagen');
        const eliminarImagen = document.getElementById('eliminar_item_imagen');
        const ventaDesde = document.getElementById('item_venta_desde');
        const ventaHasta = document.getElementById('item_venta_hasta');

        let imagenActualUrl = '';
        let objectUrlPreview = null;
        let enviandoItem = false;

        function abrirItem() {
            modalItem.classList.remove('hidden');
            modalItem.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function cerrarItem(forzar = false) {
            if (enviandoItem && !forzar) {
                return;
            }

            liberarPreviewTemporal();
            limpiarErroresItem();
            modalItem.classList.add('hidden');
            modalItem.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
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

        function limpiarErroresItem() {
            errorGeneral.textContent = '';
            errorGeneral.classList.add('hidden');

            formItem.querySelectorAll('[data-item-error]').forEach(elemento => {
                elemento.textContent = '';
                elemento.classList.add('hidden');
            });

            formItem.querySelectorAll('[data-item-field] input, [data-item-field] select, [data-item-field] textarea')
                .forEach(elemento => {
                    elemento.classList.remove('border-red-500/60');
                });
        }

        function mostrarErrorGeneral(mensaje) {
            errorGeneral.textContent = mensaje || 'No se pudo guardar el producto o servicio.';
            errorGeneral.classList.remove('hidden');
            errorGeneral.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
            });
        }

        function notificarItem(mensaje, tipo = 'error') {
            if (!mensaje) {
                return;
            }

            if (window.SIDANToast?.show) {
                window.SIDANToast.show(mensaje, tipo);
                return;
            }

            window.dispatchEvent(new CustomEvent('sidan:toast', {
                detail: {
                    message: mensaje,
                    type: tipo,
                },
            }));
        }

        function mostrarErroresItem(errores) {
            limpiarErroresItem();

            let primerCampo = null;

            Object.entries(errores || {}).forEach(([campo, mensajes]) => {
                const error = formItem.querySelector(`[data-item-error="${campo}"]`);
                const grupo = formItem.querySelector(`[data-item-field="${campo}"]`);
                const control = grupo?.querySelector('input:not([type="hidden"]), select, textarea');

                if (error) {
                    error.textContent = Array.isArray(mensajes)
                        ? mensajes.join(' ')
                        : String(mensajes ?? '');

                    error.classList.remove('hidden');
                }

                if (control) {
                    control.classList.add('border-red-500/60');

                    if (!primerCampo) {
                        primerCampo = control;
                    }
                }
            });

            if (primerCampo) {
                primerCampo.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center',
                });
                primerCampo.focus({
                    preventScroll: true,
                });
            }
        }

        function actualizarLimitesFechas() {
            if (ventaDesde) {
                ventaDesde.removeAttribute('min');
                ventaDesde.removeAttribute('max');
            }

            if (ventaHasta) {
                if (ventaDesde?.value) {
                    ventaHasta.min = ventaDesde.value;
                } else {
                    ventaHasta.removeAttribute('min');
                }

                ventaHasta.removeAttribute('max');
            }
        }

        function nuevoItem() {
            formItem.reset();
            formItem.action = rutaCrear;
            metodoItem.disabled = true;
            tituloItem.textContent = 'Agregar producto o servicio';
            guardarItemTexto.textContent = 'Agregar elemento';
            document.getElementById('item_precio').value = '0.00';
            document.getElementById('item_costo_referencia').value = '0.00';
            document.getElementById('item_min_por_inscripcion').value = '1';
            limpiarEstadoImagen();
            limpiarErroresItem();
            actualizarMargen();
            actualizarLimitesFechas();
            abrirItem();
        }

        inputImagen?.addEventListener('change', function () {
            limpiarErroresItem();

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
                mostrarErroresItem({
                    item_imagen: ['La imagen debe ser JPG, JPEG, PNG o WEBP.'],
                });
                notificarItem('La imagen debe ser JPG, JPEG, PNG o WEBP.');

                if (imagenActualUrl) {
                    mostrarImagen(imagenActualUrl);
                } else {
                    mostrarImagen('');
                }

                return;
            }

            if (archivo.size > maximoBytes) {
                this.value = '';
                mostrarErroresItem({
                    item_imagen: ['La imagen no puede superar los 5 MB.'],
                });
                notificarItem('La imagen no puede superar los 5 MB.');

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

        ventaDesde?.addEventListener('change', actualizarLimitesFechas);

        formItem.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (enviandoItem) {
                return;
            }

            limpiarErroresItem();
            enviandoItem = true;
            guardarItemBtn.disabled = true;

            const textoOriginal = guardarItemTexto.textContent;
            guardarItemTexto.textContent = 'Guardando...';

            try {
                const response = await fetch(formItem.action, {
                    method: 'POST',
                    body: new FormData(formItem),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                let data = null;

                try {
                    data = await response.json();
                } catch (error) {
                    data = null;
                }

                if (response.status === 422) {
                    if (data?.errors && Object.keys(data.errors).length > 0) {
                        mostrarErroresItem(data.errors);
                        notificarItem('Revisa los campos marcados e intenta nuevamente.');
                    }

                    if (!data?.errors || Object.keys(data.errors).length === 0) {
                        const mensaje = data?.message
                            || 'Revisa la información ingresada e intenta nuevamente.';
                        mostrarErrorGeneral(mensaje);
                        notificarItem(mensaje);
                    }

                    return;
                }

                if (response.status === 419) {
                    const mensaje = 'Tu sesión expiró. Recarga la página e intenta nuevamente.';
                    mostrarErrorGeneral(mensaje);
                    notificarItem(mensaje);
                    return;
                }

                if (!response.ok) {
                    const mensaje = data?.message
                        || 'No se pudo guardar el producto o servicio.';
                    mostrarErrorGeneral(mensaje);
                    notificarItem(mensaje);
                    return;
                }

                cerrarItem(true);
                window.location.reload();
            } catch (error) {
                const mensaje = 'No se pudo conectar con el servidor. Verifica tu conexión e intenta nuevamente.';
                mostrarErrorGeneral(mensaje);
                notificarItem(mensaje);
            } finally {
                enviandoItem = false;
                guardarItemBtn.disabled = false;
                guardarItemTexto.textContent = textoOriginal;
            }
        });

        document
            .getElementById('btnNuevoItem')
            ?.addEventListener('click', nuevoItem);

        document
            .getElementById('btnNuevoItemVacio')
            ?.addEventListener('click', nuevoItem);

        document
            .getElementById('cerrarModalItem')
            ?.addEventListener('click', cerrarItem);

        document
            .getElementById('cancelarModalItem')
            ?.addEventListener('click', cerrarItem);

        document
            .getElementById('item_precio')
            ?.addEventListener('input', actualizarMargen);

        document
            .getElementById('item_costo_referencia')
            ?.addEventListener('input', actualizarMargen);

        document
            .querySelectorAll('.btnEditarItem')
            .forEach(button => {
                button.addEventListener('click', function () {
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
                    limpiarErroresItem();
                    actualizarMargen();
                    actualizarLimitesFechas();
                    abrirItem();
                });
            });

        modalItem.addEventListener('click', function (event) {
            if (event.target === modalItem) {
                cerrarItem();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (
                event.key === 'Escape'
                && !modalItem.classList.contains('hidden')
            ) {
                cerrarItem();
            }
        });
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
    const formAjustesPrecio = document.getElementById('formAjustesPrecio');
    if (formAjustesPrecio) {
        const seccionAjustes = document.getElementById('seccionAjustesPrecio');
        const contenedorAjustes = document.getElementById('contenedorAjustesPrecio');
        const mensajeSinAjustes = document.getElementById('mensajeSinAjustesPrecio');
        const btnAgregarAjuste = document.getElementById('btnAgregarAjustePrecio');
        const ajustesPayload = document.getElementById('ajustesPayloadPrecio');
        const erroresAjustes = document.getElementById('erroresAjustesPrecio');
        const botonGuardar = formAjustesPrecio.querySelector('button[type="submit"]');

        const datosPrecio = decodificarBase64Json(
            document.getElementById('datosPrecioActual')?.value || ''
        ) || {};

        const configPrecio = datosPrecio.config || null;
        const camposDisponibles = Array.isArray(datosPrecio.campos)
            ? datosPrecio.campos
            : [];

        const reglasGuardadas = Array.isArray(configPrecio?.rules)
            ? configPrecio.rules
            : (
                Array.isArray(configPrecio?.adjustments)
                    ? configPrecio.adjustments
                    : []
            );

        function normalizarNumero(valor) {
            const texto = String(valor ?? '').trim().replace(',', '.');
            const numero = Number.parseFloat(texto);

            if (!Number.isFinite(numero) || numero < 0) {
                return 0;
            }

            return Math.round(numero * 100) / 100;
        }

        function formatearNumero(valor) {
            return normalizarNumero(valor).toFixed(2);
        }

        function buscarCampo(clave) {
            return camposDisponibles.find(
                campo => String(campo.key) === String(clave)
            );
        }

        function crearOpcionSelect(valor, texto) {
            const option = document.createElement('option');
            option.value = valor;
            option.textContent = texto;
            return option;
        }

        function obtenerParesUsados(filaExcluida = null) {
            const usados = new Set();

            contenedorAjustes
                .querySelectorAll('.filaAjustePrecio')
                .forEach(fila => {
                    if (fila === filaExcluida) {
                        return;
                    }

                    const campo = String(
                        fila.querySelector('.campoAjustePrecio')?.value || ''
                    ).trim();
                    const opcion = String(
                        fila.querySelector('.opcionAjustePrecio')?.value || ''
                    ).trim();

                    if (campo && opcion) {
                        usados.add(`${campo}|${opcion}`);
                    }
                });

            return usados;
        }

        function obtenerOpcionesDisponibles(campoClave, filaActual = null) {
            const campo = buscarCampo(campoClave);
            if (!campo || !Array.isArray(campo.options)) {
                return [];
            }

            const usados = obtenerParesUsados(filaActual);

            return campo.options
                .map(opcion => String(opcion))
                .filter(opcion => !usados.has(`${campoClave}|${opcion}`));
        }

        function quedanVariantesDisponibles() {
            return camposDisponibles.some(campo =>
                obtenerOpcionesDisponibles(String(campo.key)).length > 0
            );
        }

        function crearSelectDato(valorActual = '', filaActual = null) {
            const select = document.createElement('select');
            select.className =
                'campoAjustePrecio w-full rounded-xl border border-white/10 bg-[#111827] px-3 py-2.5 text-sm text-white outline-none transition focus:border-violet-500/50';

            select.appendChild(
                crearOpcionSelect('', 'Selecciona un dato')
            );

            camposDisponibles.forEach(campo => {
                const clave = String(campo.key);
                const esActual = clave === String(valorActual);
                const disponibles = obtenerOpcionesDisponibles(
                    clave,
                    filaActual
                );

                if (!esActual && disponibles.length === 0) {
                    return;
                }

                const option = crearOpcionSelect(
                    clave,
                    String(campo.label || campo.key)
                );
                option.selected = esActual;
                select.appendChild(option);
            });

            return select;
        }

        function cargarOpciones(
            selectOpcion,
            campoClave,
            valorActual = '',
            filaActual = null
        ) {
            selectOpcion.innerHTML = '';

            const campo = buscarCampo(campoClave);
            if (!campo || !Array.isArray(campo.options)) {
                selectOpcion.appendChild(
                    crearOpcionSelect('', 'Primero selecciona un dato')
                );
                selectOpcion.disabled = true;
                return;
            }

            const disponibles = obtenerOpcionesDisponibles(
                String(campoClave),
                filaActual
            );

            selectOpcion.disabled = false;
            selectOpcion.appendChild(
                crearOpcionSelect('', 'Selecciona una opción')
            );

            disponibles.forEach(valor => {
                const option = crearOpcionSelect(valor, valor);
                option.selected = valor === String(valorActual);
                selectOpcion.appendChild(option);
            });

            if (
                valorActual
                && !disponibles.includes(String(valorActual))
            ) {
                selectOpcion.value = '';
            }
        }

        function actualizarBotonAgregarAjuste() {
            if (!btnAgregarAjuste) {
                return;
            }

            const disponible = quedanVariantesDisponibles();
            btnAgregarAjuste.disabled = !disponible;
            btnAgregarAjuste.classList.toggle('opacity-50', !disponible);
            btnAgregarAjuste.classList.toggle('cursor-not-allowed', !disponible);
            btnAgregarAjuste.title = disponible
                ? 'Agregar otra variante con aumento'
                : 'Ya configuraste todas las opciones disponibles';
        }

        function refrescarSelectoresAjustes() {
            contenedorAjustes
                .querySelectorAll('.filaAjustePrecio')
                .forEach(fila => {
                    const selectCampoActual = fila.querySelector(
                        '.campoAjustePrecio'
                    );
                    const selectOpcion = fila.querySelector(
                        '.opcionAjustePrecio'
                    );

                    if (!selectCampoActual || !selectOpcion) {
                        return;
                    }

                    const campoActual = String(
                        selectCampoActual.value || ''
                    );
                    const opcionActual = String(
                        selectOpcion.value || ''
                    );

                    const selectCampoNuevo = crearSelectDato(
                        campoActual,
                        fila
                    );
                    selectCampoActual.replaceWith(selectCampoNuevo);

                    cargarOpciones(
                        selectOpcion,
                        campoActual,
                        opcionActual,
                        fila
                    );

                    selectCampoNuevo.addEventListener('change', function () {
                        cargarOpciones(
                            selectOpcion,
                            this.value,
                            '',
                            fila
                        );
                        limpiarErroresAjustes();
                        refrescarSelectoresAjustes();
                    });
                });

            actualizarBotonAgregarAjuste();
        }

        function crearInputMoneda(valor, etiqueta, clase) {
            const wrapper = document.createElement('label');
            wrapper.className =
                'flex min-w-0 items-center gap-2 rounded-xl border border-white/10 bg-[#111827] px-3 py-2';

            const prefijo = document.createElement('span');
            prefijo.className =
                'shrink-0 text-xs font-semibold text-violet-300';
            prefijo.textContent = '+$';

            const input = document.createElement('input');
            input.type = 'number';
            input.value = formatearNumero(valor);
            input.min = '0';
            input.step = '0.01';
            input.inputMode = 'decimal';
            input.className =
                `${clase} min-w-0 flex-1 bg-transparent text-sm font-semibold text-white outline-none`;
            input.setAttribute('aria-label', etiqueta);

            input.addEventListener('blur', function () {
                this.value = formatearNumero(this.value);
            });

            wrapper.appendChild(prefijo);
            wrapper.appendChild(input);

            return wrapper;
        }

        function actualizarEstadoVacio() {
            const total = contenedorAjustes.querySelectorAll(
                '.filaAjustePrecio'
            ).length;

            mensajeSinAjustes?.classList.toggle('hidden', total > 0);
        }

        function reindexarAjustes() {
            contenedorAjustes
                .querySelectorAll('.filaAjustePrecio')
                .forEach((fila, indice) => {
                    fila.dataset.indice = String(indice);
                });
        }

        function limpiarErroresAjustes() {
            if (!erroresAjustes) {
                return;
            }

            erroresAjustes.innerHTML = '';
            erroresAjustes.classList.add('hidden');
        }

        function agregarAjuste(datos = null) {
            const campoActual = String(
                datos?.field_key
                ?? datos?.campo
                ?? ''
            );

            const opcionActual = String(
                datos?.option
                ?? datos?.opcion
                ?? ''
            );

            const precioActual =
                datos?.price_increment
                ?? datos?.price_delta
                ?? datos?.aumento_precio
                ?? 0;

            const costoActual =
                datos?.cost_increment
                ?? datos?.cost_delta
                ?? datos?.aumento_costo
                ?? 0;

            const fila = document.createElement('div');
            fila.className =
                'filaAjustePrecio border-b border-white/10 px-3 py-3 last:border-b-0';

            const grid = document.createElement('div');
            grid.className =
                'grid grid-cols-1 gap-3 lg:grid-cols-[minmax(150px,.9fr)_minmax(160px,1fr)_minmax(145px,.9fr)_minmax(145px,.9fr)_44px] lg:items-center lg:gap-3';

            const bloqueCampo = document.createElement('div');
            const labelCampo = document.createElement('span');
            labelCampo.className =
                'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-600 lg:hidden';
            labelCampo.textContent = 'Dato';
            const selectCampo = crearSelectDato(campoActual, fila);
            bloqueCampo.appendChild(labelCampo);
            bloqueCampo.appendChild(selectCampo);

            const bloqueOpcion = document.createElement('div');
            const labelOpcion = document.createElement('span');
            labelOpcion.className =
                'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-600 lg:hidden';
            labelOpcion.textContent = 'Opción';
            const selectOpcion = document.createElement('select');
            selectOpcion.className =
                'opcionAjustePrecio w-full rounded-xl border border-white/10 bg-[#111827] px-3 py-2.5 text-sm text-white outline-none transition focus:border-violet-500/50 disabled:cursor-not-allowed disabled:opacity-50';
            cargarOpciones(
                selectOpcion,
                campoActual,
                opcionActual,
                fila
            );
            bloqueOpcion.appendChild(labelOpcion);
            bloqueOpcion.appendChild(selectOpcion);

            selectCampo.addEventListener('change', function () {
                cargarOpciones(
                    selectOpcion,
                    this.value,
                    '',
                    fila
                );
                limpiarErroresAjustes();
                refrescarSelectoresAjustes();
            });

            selectOpcion.addEventListener('change', function () {
                limpiarErroresAjustes();
                refrescarSelectoresAjustes();
            });

            const bloquePrecio = document.createElement('div');
            const labelPrecio = document.createElement('span');
            labelPrecio.className =
                'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-600 lg:hidden';
            labelPrecio.textContent = 'Aumento de precio';
            bloquePrecio.appendChild(labelPrecio);
            bloquePrecio.appendChild(
                crearInputMoneda(
                    precioActual,
                    'Aumento de precio',
                    'ajustePrecio'
                )
            );

            const bloqueCosto = document.createElement('div');
            const labelCosto = document.createElement('span');
            labelCosto.className =
                'mb-1.5 block text-[11px] font-semibold uppercase tracking-wide text-gray-600 lg:hidden';
            labelCosto.textContent = 'Aumento de costo';
            bloqueCosto.appendChild(labelCosto);
            bloqueCosto.appendChild(
                crearInputMoneda(
                    costoActual,
                    'Aumento de costo',
                    'ajusteCosto'
                )
            );

            const bloqueEliminar = document.createElement('div');
            bloqueEliminar.className = 'flex justify-end lg:justify-center';
            const btnEliminar = document.createElement('button');
            btnEliminar.type = 'button';
            btnEliminar.className =
                'flex h-10 w-10 items-center justify-center rounded-xl border border-red-500/20 bg-red-500/[0.06] text-lg text-red-300 transition hover:bg-red-500/10';
            btnEliminar.setAttribute('aria-label', 'Eliminar variante');
            btnEliminar.title = 'Eliminar variante';
            btnEliminar.textContent = '×';
            btnEliminar.addEventListener('click', function () {
                fila.remove();
                reindexarAjustes();
                actualizarEstadoVacio();
                limpiarErroresAjustes();
                refrescarSelectoresAjustes();
            });
            bloqueEliminar.appendChild(btnEliminar);

            grid.appendChild(bloqueCampo);
            grid.appendChild(bloqueOpcion);
            grid.appendChild(bloquePrecio);
            grid.appendChild(bloqueCosto);
            grid.appendChild(bloqueEliminar);

            fila.appendChild(grid);
            contenedorAjustes.appendChild(fila);

            reindexarAjustes();
            actualizarEstadoVacio();
            refrescarSelectoresAjustes();
        }

        function mostrarErroresAjustes(mensajes) {
            const lista = Array.from(new Set(
                (Array.isArray(mensajes) ? mensajes : [mensajes])
                    .filter(Boolean)
                    .map(mensaje => String(mensaje))
            ));

            if (lista.length === 0 || !erroresAjustes) {
                return;
            }

            erroresAjustes.innerHTML = '';

            const titulo = document.createElement('p');
            titulo.className = 'font-semibold';
            titulo.textContent = lista.length === 1
                ? lista[0]
                : 'Corrige lo siguiente:';
            erroresAjustes.appendChild(titulo);

            if (lista.length > 1) {
                const ul = document.createElement('ul');
                ul.className = 'mt-2 list-disc space-y-1 pl-5 text-xs';

                lista.forEach(mensaje => {
                    const li = document.createElement('li');
                    li.textContent = mensaje;
                    ul.appendChild(li);
                });

                erroresAjustes.appendChild(ul);
            }

            erroresAjustes.classList.remove('hidden');
        }

        function obtenerFilasAjustes() {
            const filas = Array.from(
                contenedorAjustes.querySelectorAll('.filaAjustePrecio')
            );
            const ajustes = [];
            const errores = [];
            const usados = new Set();

            filas.forEach((fila, indice) => {
                const campo = String(
                    fila.querySelector('.campoAjustePrecio')?.value || ''
                ).trim();
                const opcion = String(
                    fila.querySelector('.opcionAjustePrecio')?.value || ''
                ).trim();
                const aumentoPrecio = normalizarNumero(
                    fila.querySelector('.ajustePrecio')?.value
                );
                const aumentoCosto = normalizarNumero(
                    fila.querySelector('.ajusteCosto')?.value
                );
                const numeroFila = indice + 1;

                if (!campo) {
                    errores.push(`Fila ${numeroFila}: selecciona un dato.`);
                }

                if (!opcion) {
                    errores.push(`Fila ${numeroFila}: selecciona una opción.`);
                }

                if (aumentoPrecio <= 0 && aumentoCosto <= 0) {
                    errores.push(
                        `Fila ${numeroFila}: indica un aumento de precio o costo mayor que cero.`
                    );
                }

                if (campo && opcion) {
                    const clave = `${campo}|${opcion}`;
                    if (usados.has(clave)) {
                        errores.push(
                            `Fila ${numeroFila}: esa opción ya fue agregada.`
                        );
                    }
                    usados.add(clave);
                }

                ajustes.push({
                    campo,
                    opcion,
                    aumento_precio: aumentoPrecio,
                    aumento_costo: aumentoCosto,
                });
            });

            return { ajustes, errores };
        }

        function actualizarVisibilidadPrecios() {
            const seleccionado = formAjustesPrecio.querySelector(
                'input[name="tiene_cambios"]:checked'
            );

            if (seleccionado?.value === '1') {
                seccionAjustes.classList.remove('hidden');
            } else {
                seccionAjustes.classList.add('hidden');
                limpiarErroresAjustes();
            }
        }

        formAjustesPrecio
            .querySelectorAll('input[name="tiene_cambios"]')
            .forEach(radio => {
                radio.addEventListener('change', actualizarVisibilidadPrecios);
            });

        btnAgregarAjuste?.addEventListener('click', function () {
            if (!quedanVariantesDisponibles()) {
                const mensaje = 'Ya configuraste todas las opciones disponibles.';
                mostrarErroresAjustes(mensaje);
                window.SIDANToast?.info(mensaje);
                return;
            }

            agregarAjuste();
        });

        reglasGuardadas.forEach(regla => {
            agregarAjuste(regla);
        });
        refrescarSelectoresAjustes();

        formAjustesPrecio.addEventListener('submit', async function (event) {
            event.preventDefault();
            limpiarErroresAjustes();

            const seleccionado = formAjustesPrecio.querySelector(
                'input[name="tiene_cambios"]:checked'
            );

            if (!seleccionado) {
                const mensaje = 'Indica si alguna opción aumenta el precio o costo.';
                mostrarErroresAjustes(mensaje);
                window.SIDANToast?.error(mensaje);
                return;
            }

            let ajustes = [];

            if (seleccionado.value === '1') {
                const resultado = obtenerFilasAjustes();
                ajustes = resultado.ajustes;

                if (ajustes.length === 0) {
                    resultado.errores.push(
                        'Agrega al menos una variante con aumento.'
                    );
                }

                if (resultado.errores.length > 0) {
                    mostrarErroresAjustes(resultado.errores);
                    window.SIDANToast?.error(
                        'Revisa la configuración de aumentos.'
                    );
                    return;
                }
            }

            ajustesPayload.value = JSON.stringify(ajustes);

            const textoOriginal = botonGuardar?.textContent || 'Guardar';
            if (botonGuardar) {
                botonGuardar.disabled = true;
                botonGuardar.textContent = 'Guardando...';
            }

            try {
                const response = await fetch(formAjustesPrecio.action, {
                    method: 'POST',
                    body: new FormData(formAjustesPrecio),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                let data = {};
                try {
                    data = await response.json();
                } catch (_) {
                    data = {};
                }

                if (response.status === 422) {
                    const mensajes = Object.values(data.errors || {})
                        .flat()
                        .filter(Boolean);

                    mostrarErroresAjustes(
                        mensajes.length > 0
                            ? mensajes
                            : (data.message || 'Revisa la configuración enviada.')
                    );
                    window.SIDANToast?.error(
                        mensajes[0]
                        || data.message
                        || 'Revisa la configuración enviada.'
                    );
                    return;
                }

                if (!response.ok || data.ok === false) {
                    const mensaje = data.message
                        || 'No se pudo guardar la configuración de precios.';
                    mostrarErroresAjustes(mensaje);
                    window.SIDANToast?.error(mensaje);
                    return;
                }

                window.SIDANToast?.success(
                    data.message || 'Configuración guardada correctamente.'
                );

                window.location.href = data.redirect
                    || formAjustesPrecio.action;
            } catch (error) {
                const mensaje = 'No se pudo conectar con el servidor. Intenta nuevamente.';
                mostrarErroresAjustes(mensaje);
                window.SIDANToast?.error(mensaje);
            } finally {
                if (botonGuardar) {
                    botonGuardar.disabled = false;
                    botonGuardar.textContent = textoOriginal;
                }
            }
        });

        actualizarEstadoVacio();
        actualizarVisibilidadPrecios();
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
        const cupoGeneral = cupoGeneralValor === '' ? null : parseInt(cupoGeneralValor, 10);
        const existentes = decodificarBase64Json(document.getElementById('sesionesActuales').value) || [];
        const espaciosCatalogo = decodificarBase64Json(document.getElementById('espaciosDisponibles').value) || [];
        const recursosCatalogo = decodificarBase64Json(document.getElementById('recursosDisponibles').value) || [];
        const espacioActividad = document.getElementById('espacioActividad').value;
        const ubicacionActividad = document.getElementById('ubicacionActividad').value;
        const urlBuscarEspacios = document.getElementById('urlBuscarEspaciosSesion').value;
        const urlCrearEspacio = document.getElementById('urlCrearEspacioSesion').value;
        const csrfSesiones = document.getElementById('csrfSesiones').value;
        const actividadIdSesiones = document.getElementById('actividadIdSesiones')?.value || 'sin-id';
        const errorSesiones = document.getElementById('errorSesiones');
        const estadoBorradorSesiones = document.getElementById('estadoBorradorSesiones');
        const textoBorradorSesiones = document.getElementById('textoBorradorSesiones');
        const descartarBorradorSesiones = document.getElementById('descartarBorradorSesiones');
        const panelSesionUnicaPresencial = document.getElementById('panelSesionUnicaPresencial');
        const panelSesionUnicaVirtual = document.getElementById('panelSesionUnicaVirtual');
        const sesionUnicaEnlace = document.getElementById('sesionUnicaEnlace');
        const sesionUnicaEspacio = document.getElementById('sesionUnicaEspacio');
        const sesionUnicaUbicacion = document.getElementById('sesionUnicaUbicacion');
        const sesionUnicaEspacioSeleccionado = document.getElementById('sesionUnicaEspacioSeleccionado');
        const sesionUnicaEspacioNombre = document.getElementById('sesionUnicaEspacioNombre');
        const sesionUnicaEspacioRuta = document.getElementById('sesionUnicaEspacioRuta');
        const sesionUnicaEspacioPendiente = document.getElementById('sesionUnicaEspacioPendiente');
        const sesionUnicaBusquedaEspacio = document.getElementById('sesionUnicaBusquedaEspacio');
        const sesionUnicaBuscarEspacio = document.getElementById('sesionUnicaBuscarEspacio');
        const sesionUnicaResultadosEspacio = document.getElementById('sesionUnicaResultadosEspacio');
        const btnCambiarEspacioSesionUnica = document.getElementById('btnCambiarEspacioSesionUnica');
        const btnUsarEspacioGeneralSesionUnica = document.getElementById('btnUsarEspacioGeneralSesionUnica');
        const btnCrearEspacioSesionUnica = document.getElementById('btnCrearEspacioSesionUnica');
        const sesionUnicaUbicacionExterna = document.getElementById('sesionUnicaUbicacionExterna');
        const botonGuardarSesiones = formSesiones.querySelector('button[type="submit"]');

        const modalEspacio = document.getElementById('modalCrearEspacioSesion');
        const cerrarModalEspacio = document.getElementById('cerrarModalEspacioSesion');
        const cancelarModalEspacio = document.getElementById('cancelarModalEspacioSesion');
        const guardarEspacio = document.getElementById('guardarEspacioSesion');
        const errorEspacio = document.getElementById('errorEspacioSesion');
        const nuevoContenedorId = document.getElementById('nuevoEspacioSesionContenedor');
        const contenedorSeleccionado = document.getElementById('nuevoEspacioSesionContenedorSeleccionado');
        const contenedorNombre = document.getElementById('nuevoEspacioSesionContenedorNombre');
        const contenedorRuta = document.getElementById('nuevoEspacioSesionContenedorRuta');
        const contenedorBusqueda = document.getElementById('nuevoEspacioSesionContenedorBusqueda');
        const buscarContenedor = document.getElementById('buscarContenedorEspacioSesion');
        const resultadosContenedor = document.getElementById('resultadosContenedorEspacioSesion');
        const cambiarContenedor = document.getElementById('cambiarContenedorEspacioSesion');
        const nuevoNombre = document.getElementById('nuevoEspacioSesionNombre');
        const nuevaDireccion = document.getElementById('nuevoEspacioSesionDireccion');
        const nuevaDescripcion = document.getElementById('nuevoEspacioSesionDescripcion');
        const nuevaCapacidad = document.getElementById('nuevoEspacioSesionCapacidad');
        const nuevaLatitud = document.getElementById('nuevoEspacioSesionLatitud');
        const nuevaLongitud = document.getElementById('nuevoEspacioSesionLongitud');

        let contadorSesiones = 0;
        let tarjetaObjetivo = null;
        let objetivoSesionUnica = false;
        let temporizadorContenedor = null;
        let temporizadorBorrador = null;
        let restaurandoBorrador = false;
        let borradorHabilitado = false;
        let guardadoEnServidor = false;
        const claveBorradorSesiones = `sidan:actividad:${actividadIdSesiones}:sesiones:v1`;

        function fechaLocalActual() {
            const ahora = new Date();
            ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
            return ahora.toISOString().slice(0, 16);
        }

        function minimoNuevaSesion() {
            const ahora = fechaLocalActual();
            if (!realizacionDesde) return ahora;
            return realizacionDesde > ahora ? realizacionDesde : ahora;
        }

        function formatearFecha(valor) {
            if (!valor) return 'Fecha pendiente';
            const fecha = new Date(valor);
            if (Number.isNaN(fecha.getTime())) return 'Fecha pendiente';
            return new Intl.DateTimeFormat('es-SV', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }).format(fecha);
        }

        function modoSeleccionado() {
            return document.querySelector('input[name="modo_sesiones"]:checked')?.value ?? null;
        }

        function modalidadSesion(card) {
            return card.querySelector('input.sesionModalidad:checked')?.value ?? 'presencial';
        }

        function modalidadSesionUnica() {
            return document.querySelector('input[name="sesion_unica_modalidad"]:checked')?.value ?? 'presencial';
        }

        function mostrarEstadoBorrador(mensaje) {
            if (!estadoBorradorSesiones || !textoBorradorSesiones) return;
            textoBorradorSesiones.textContent = mensaje;
            estadoBorradorSesiones.classList.remove('hidden');
            estadoBorradorSesiones.classList.add('flex');
        }

        function ocultarEstadoBorrador() {
            if (!estadoBorradorSesiones) return;
            estadoBorradorSesiones.classList.add('hidden');
            estadoBorradorSesiones.classList.remove('flex');
        }

        function mostrarErrorProgramacion(mensaje) {
            if (!errorSesiones) return;
            errorSesiones.textContent = mensaje;
            errorSesiones.classList.remove('hidden');
            errorSesiones.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function limpiarErrorProgramacion() {
            if (!errorSesiones) return;
            errorSesiones.textContent = '';
            errorSesiones.classList.add('hidden');
        }

        function recolectarRecursosSesion(card) {
            const recursos = [];

            card.querySelectorAll('.sesionRecursos input[type="checkbox"][name$="[seleccionado]"]').forEach(check => {
                if (!check.checked) return;

                const coincidencia = check.name.match(/\[recursos\]\[(\d+)\]\[seleccionado\]$/);
                if (!coincidencia) return;

                const idRecurso = coincidencia[1];
                const cantidad = card.querySelector(
                    `[name="sesiones[${card.dataset.indice}][recursos][${idRecurso}][cantidad]"]`
                );
                const observacion = card.querySelector(
                    `[name="sesiones[${card.dataset.indice}][recursos][${idRecurso}][observacion]"]`
                );

                recursos.push({
                    id_recurso: Number(idRecurso),
                    cantidad: cantidad?.value || 1,
                    observacion: observacion?.value || '',
                });
            });

            return recursos;
        }

        function serializarSesionCard(card) {
            const idEspacio = card.querySelector('.sesionEspacio').value;
            const espacio = obtenerEspacio(idEspacio);

            return {
                id_sesion: card.querySelector('input[name$="[id_sesion]"]').value || null,
                nombre: card.querySelector('.sesionNombre').value,
                fecha_inicio: card.querySelector('.sesionInicio').value,
                fecha_fin: card.querySelector('.sesionFin').value,
                modalidad: modalidadSesion(card),
                id_espacio: idEspacio || null,
                ubicacion: card.querySelector('.sesionUbicacion').value || null,
                espacio_detalle: espacio ? {
                    id: espacio.id,
                    nombre: espacio.nombre,
                    contenedor: espacio.contenedor ?? null,
                    capacidad: espacio.capacidad ?? null,
                    permite_actividades: Boolean(espacio.permite_actividades),
                    direccion: espacio.direccion ?? null,
                    ruta: espacio.ruta ?? null,
                    contexto: espacio.contexto ?? null,
                    pendiente_validacion: espacio.pendiente_validacion ?? false,
                } : null,
                enlace_acceso: card.querySelector('.sesionEnlace').value,
                cupo: card.querySelector('.sesionCupo').value,
                requiere_reserva: card.querySelector('.sesionReserva').checked,
                obligatoria: card.querySelector('.sesionObligatoria').checked,
                recursos: recolectarRecursosSesion(card),
            };
        }

        function construirBorradorSesiones() {
            return {
                version: 1,
                guardado_en: Date.now(),
                modo: modoSeleccionado(),
                sesion_unica_modalidad: modalidadSesionUnica(),
                sesion_unica_enlace_acceso: sesionUnicaEnlace?.value || '',
                sesion_unica_id_espacio: sesionUnicaEspacio?.value || null,
                sesion_unica_ubicacion: sesionUnicaUbicacion?.value || '',
                sesion_unica_espacio_detalle: sesionUnicaEspacio?.value ? obtenerEspacio(sesionUnicaEspacio.value) : null,
                confirmar_solapamientos: confirmarSolapamientos?.checked || false,
                sesiones: Array.from(
                    contenedor.querySelectorAll('.sesionCard')
                ).map(serializarSesionCard),
            };
        }

        function guardarBorradorSesiones(mostrarEstado = true) {
            if (!borradorHabilitado || restaurandoBorrador || guardadoEnServidor) return;

            try {
                localStorage.setItem(
                    claveBorradorSesiones,
                    JSON.stringify(construirBorradorSesiones())
                );

                if (mostrarEstado) {
                    mostrarEstadoBorrador('Borrador guardado automáticamente.');
                }
            } catch (error) {
                if (mostrarEstado) {
                    mostrarEstadoBorrador('No se pudo guardar el borrador en este navegador.');
                }
            }
        }

        function programarGuardadoBorrador() {
            if (!borradorHabilitado || restaurandoBorrador || guardadoEnServidor) return;
            clearTimeout(temporizadorBorrador);
            temporizadorBorrador = setTimeout(() => guardarBorradorSesiones(true), 350);
        }

        function cargarBorradorSesiones() {
            try {
                const bruto = localStorage.getItem(claveBorradorSesiones);
                if (!bruto) return null;

                const datos = JSON.parse(bruto);
                if (!datos || datos.version !== 1) return null;

                return datos;
            } catch (error) {
                return null;
            }
        }

        function restaurarBorradorSesiones(borrador) {
            if (!borrador) return false;

            restaurandoBorrador = true;

            try {
                if (borrador.modo) {
                    const radioModo = document.querySelector(
                        `input[name="modo_sesiones"][value="${borrador.modo}"]`
                    );
                    if (radioModo && !radioModo.disabled) {
                        radioModo.checked = true;
                    }
                }

                const modalidadUnica = borrador.sesion_unica_modalidad || 'presencial';
                const radioUnica = document.querySelector(
                    `input[name="sesion_unica_modalidad"][value="${modalidadUnica}"]`
                );
                if (radioUnica) radioUnica.checked = true;

                if (sesionUnicaEnlace) {
                    sesionUnicaEnlace.value = borrador.sesion_unica_enlace_acceso || '';
                }

                if (borrador.sesion_unica_espacio_detalle) {
                    guardarEnCatalogo(borrador.sesion_unica_espacio_detalle);
                }

                if (sesionUnicaEspacio) {
                    sesionUnicaEspacio.value = borrador.sesion_unica_id_espacio || '';
                }

                if (sesionUnicaUbicacion) {
                    sesionUnicaUbicacion.value = borrador.sesion_unica_ubicacion || '';
                }

                if (confirmarSolapamientos) {
                    confirmarSolapamientos.checked = Boolean(borrador.confirmar_solapamientos);
                }

                contenedor.innerHTML = '';
                contadorSesiones = 0;

                (borrador.sesiones || []).forEach(datos => {
                    if (datos?.espacio_detalle) {
                        guardarEnCatalogo(datos.espacio_detalle);
                    }
                    agregarSesion(datos, true);
                });

                renderEspacioSesionUnicaActual();
                actualizarModoSesiones();
                actualizarModalidadSesionUnica();
                mostrarEstadoBorrador('Recuperamos la programación que todavía no habías guardado.');
                return true;
            } finally {
                restaurandoBorrador = false;
            }
        }

        function normalizarEspacio(espacio) {
            if (!espacio) return null;
            return {
                id: Number(espacio.id),
                nombre: espacio.nombre ?? '',
                contenedor: espacio.contenedor ?? espacio.id_espacio_contenedor ?? null,
                capacidad: espacio.capacidad ?? null,
                permite_actividades: Boolean(espacio.permite_actividades),
                direccion: espacio.direccion ?? null,
                ruta: espacio.ruta ?? null,
                contexto: espacio.contexto ?? null,
                pendiente_validacion: Boolean(espacio.pendiente_validacion),
            };
        }

        function guardarEnCatalogo(espacio) {
            const normalizado = normalizarEspacio(espacio);
            if (!normalizado || !normalizado.id) return null;
            const indice = espaciosCatalogo.findIndex(item => String(item.id) === String(normalizado.id));
            if (indice >= 0) {
                espaciosCatalogo[indice] = { ...espaciosCatalogo[indice], ...normalizado };
                return espaciosCatalogo[indice];
            }
            espaciosCatalogo.push(normalizado);
            return normalizado;
        }

        function obtenerEspacio(id) {
            if (!id) return null;
            return espaciosCatalogo.find(item => String(item.id) === String(id)) ?? null;
        }

        function espacioPerteneceAlLugarGeneral(espacio) {
            if (!espacio) return false;
            if (!espacioActividad) return true;
            if (String(espacio.id) === String(espacioActividad)) return true;

            const visitados = new Set();
            let actual = espacio;

            while (actual?.contenedor) {
                if (visitados.has(String(actual.id))) return false;
                visitados.add(String(actual.id));

                if (String(actual.contenedor) === String(espacioActividad)) {
                    return true;
                }

                actual = obtenerEspacio(actual.contenedor);
            }

            return false;
        }

        function rutaCatalogo(espacio) {
            if (!espacio) return '';
            if (espacio.ruta) return espacio.ruta;
            const partes = [espacio.nombre];
            let actual = espacio;
            const visitados = new Set([String(espacio.id)]);
            while (actual?.contenedor) {
                const padre = obtenerEspacio(actual.contenedor);
                if (!padre || visitados.has(String(padre.id))) break;
                visitados.add(String(padre.id));
                partes.unshift(padre.nombre);
                actual = padre;
            }
            return partes.join(' › ');
        }

        function contextoCatalogo(espacio) {
            if (!espacio) return '';
            if (espacio.contexto) return espacio.contexto;
            const ruta = rutaCatalogo(espacio);
            const sufijo = ` › ${espacio.nombre}`;
            return ruta.endsWith(sufijo) ? ruta.slice(0, -sufijo.length) : '';
        }

        function ubicacionSesion(card) {
            if (modalidadSesion(card) === 'virtual') {
                return 'Virtual';
            }

            const id = card.querySelector('.sesionEspacio').value;
            const externa = card.querySelector('.sesionUbicacion').value.trim();
            const espacio = obtenerEspacio(id);
            return espacio?.nombre || externa || '';
        }

        function resumenSesion(card) {
            const nombre = card.querySelector('.sesionNombre').value.trim() || 'Nueva sesión';
            const inicio = card.querySelector('.sesionInicio').value;
            const modalidad = modalidadSesion(card);
            const ubicacion = ubicacionSesion(card);
            const reserva = card.querySelector('.sesionReserva').checked;
            const obligatoria = card.querySelector('.sesionObligatoria').checked;

            card.querySelector('.resumenSesionNombre').textContent = nombre;

            let detalle = formatearFecha(inicio);
            detalle += modalidad === 'virtual'
                ? ' · Virtual'
                : (ubicacion ? ` · ${ubicacion}` : ' · Presencial');

            if (reserva) detalle += ' · Reserva';
            if (obligatoria) detalle += ' · Obligatoria';

            card.querySelector('.resumenSesionDetalle').textContent = detalle;
        }

        function renderPreviewSesiones() {
            if (!preview) return;

            preview.innerHTML = '';
            const modo = modoSeleccionado();

            if (modo === 'ninguna' || !modo) {
                preview.innerHTML = '<div class="rounded-xl border border-white/5 bg-white/[0.02] p-4"><p class="text-sm font-medium text-gray-300">Sin programación específica</p><p class="mt-1 text-xs leading-5 text-gray-600">No se mostrarán sesiones ni turnos.</p></div>';
                return;
            }

            if (modo === 'unica') {
                const modalidad = modalidadSesionUnica();
                const ubicacion = modalidad === 'virtual'
                    ? 'Virtual'
                    : (obtenerEspacio(espacioActividad)?.nombre || ubicacionActividad || 'Presencial');

                preview.innerHTML = `<div class="rounded-xl border border-cyan-500/20 bg-cyan-500/[0.04] p-4"><p class="font-semibold text-white">Sesión principal</p><p class="mt-1 text-xs text-gray-500">${escaparHtml(formatearFecha(realizacionDesde))}</p><p class="mt-2 text-xs text-gray-400">${escaparHtml(ubicacion)}</p>${cupoGeneral !== null ? `<p class="mt-2 text-xs text-gray-400">Cupo: ${cupoGeneral}</p>` : ''}</div>`;
                return;
            }

            const cards = Array.from(contenedor.querySelectorAll('.sesionCard'));

            if (cards.length === 0) {
                preview.innerHTML = '<p class="text-sm text-gray-600">Agrega sesiones para ver la vista previa.</p>';
                return;
            }

            cards.forEach((card, indice) => {
                const nombre = card.querySelector('.sesionNombre').value.trim() || `Sesión ${indice + 1}`;
                const inicio = card.querySelector('.sesionInicio').value;
                const fin = card.querySelector('.sesionFin').value;
                const modalidad = modalidadSesion(card);
                const ubicacion = ubicacionSesion(card);
                const cupo = card.querySelector('.sesionCupo').value;
                const reserva = card.querySelector('.sesionReserva').checked;
                const obligatoria = card.querySelector('.sesionObligatoria').checked;
                const bloque = document.createElement('div');

                bloque.className = 'rounded-xl border border-white/10 bg-white/[0.02] p-4';
                bloque.innerHTML = `<p class="font-semibold text-white">${escaparHtml(nombre)}</p><p class="mt-1 text-xs text-gray-500">${escaparHtml(formatearFecha(inicio))}</p>${fin ? `<p class="text-xs text-gray-600">Hasta: ${escaparHtml(formatearFecha(fin))}</p>` : ''}<p class="mt-2 text-xs text-gray-400">${modalidad === 'virtual' ? 'Virtual' : escaparHtml(ubicacion || 'Presencial')}</p>${cupo !== '' ? `<p class="text-xs text-gray-400">Cupo: ${escaparHtml(cupo)}</p>` : (cupoGeneral !== null ? `<p class="text-xs text-gray-400">Cupo heredado: ${cupoGeneral}</p>` : '')}<div class="mt-2 flex flex-wrap gap-2">${reserva ? '<span class="inline-flex rounded-full bg-cyan-500/10 px-2 py-1 text-[10px] font-semibold text-cyan-300">Requiere reserva</span>' : ''}${obligatoria ? '<span class="inline-flex rounded-full bg-amber-500/10 px-2 py-1 text-[10px] font-semibold text-amber-300">Obligatoria</span>' : ''}</div>`;
                preview.appendChild(bloque);
            });
        }

        function contraerSesion(card) {
            card.querySelector('.sesionCuerpo').classList.add('hidden');
            card.querySelector('.iconoSesion').textContent = '⌄';
            resumenSesion(card);
        }

        function expandirSesion(card) {
            contenedor.querySelectorAll('.sesionCard').forEach(otra => {
                if (otra !== card) contraerSesion(otra);
            });
            card.querySelector('.sesionCuerpo').classList.remove('hidden');
            card.querySelector('.iconoSesion').textContent = '⌃';
        }

        function detectarSolapamientos() {
            const cards = Array.from(contenedor.querySelectorAll('.sesionCard'));
            const cruces = [];
            for (let i = 0; i < cards.length; i++) {
                for (let j = i + 1; j < cards.length; j++) {
                    const aInicioValor = cards[i].querySelector('.sesionInicio').value;
                    const bInicioValor = cards[j].querySelector('.sesionInicio').value;
                    if (!aInicioValor || !bInicioValor) continue;
                    const aInicio = new Date(aInicioValor);
                    const bInicio = new Date(bInicioValor);
                    const aFinValor = cards[i].querySelector('.sesionFin').value;
                    const bFinValor = cards[j].querySelector('.sesionFin').value;
                    const aFin = aFinValor ? new Date(aFinValor) : aInicio;
                    const bFin = bFinValor ? new Date(bFinValor) : bInicio;
                    if (aInicio <= bFin && bInicio <= aFin) {
                        const nombreA = cards[i].querySelector('.sesionNombre').value.trim() || `Sesión ${i + 1}`;
                        const nombreB = cards[j].querySelector('.sesionNombre').value.trim() || `Sesión ${j + 1}`;
                        cruces.push(`${nombreA} con ${nombreB}`);
                    }
                }
            }
            const unicos = [...new Set(cruces)];
            advertenciaSolapamientos.classList.toggle('hidden', unicos.length === 0);
            textoSolapamientos.textContent = unicos.length ? `Coinciden: ${unicos.join('; ')}.` : '';
            if (unicos.length === 0) confirmarSolapamientos.checked = false;
            return unicos;
        }

        function actualizarTodoSesiones() {
            renderPreviewSesiones();
            detectarSolapamientos();
        }

        function recursosPermitidos(idEspacio) {
            return recursosCatalogo.filter(recurso => recurso.es_movil || (idEspacio && recurso.espacios && recurso.espacios[String(idEspacio)]));
        }

        function renderRecursosSesion(card, datosRecursos = []) {
            const contenedorRecursos = card.querySelector('.sesionRecursos');

            if (modalidadSesion(card) === 'virtual') {
                contenedorRecursos.innerHTML = '<p class="text-xs text-gray-600">Las sesiones virtuales no requieren recursos físicos del espacio.</p>';
                return;
            }

            const idEspacio = card.querySelector('.sesionEspacio').value;
            const usados = new Map((datosRecursos || []).map(item => [String(item.id_recurso), item]));
            const disponibles = recursosPermitidos(idEspacio);

            contenedorRecursos.innerHTML = disponibles.length
                ? disponibles.map(recurso => {
                    const usado = usados.get(String(recurso.id));
                    const maximo = idEspacio && !recurso.es_movil
                        ? (recurso.espacios[String(idEspacio)] ?? '')
                        : '';

                    return `<div class="grid grid-cols-1 gap-2 rounded-lg border border-white/5 p-3 sm:grid-cols-[1fr_90px_1fr]"><label class="flex items-center gap-2 text-sm text-gray-300"><input type="hidden" name="sesiones[${card.dataset.indice}][recursos][${recurso.id}][seleccionado]" value="0"><input type="checkbox" name="sesiones[${card.dataset.indice}][recursos][${recurso.id}][seleccionado]" value="1" ${usado ? 'checked' : ''}>${escaparHtml(recurso.nombre)}</label><input type="number" min="1" ${maximo ? `max="${maximo}"` : ''} name="sesiones[${card.dataset.indice}][recursos][${recurso.id}][cantidad]" value="${usado?.cantidad ?? 1}" class="rounded-lg border border-white/10 bg-black/20 px-3 py-2 text-sm text-white"><input type="text" maxlength="300" name="sesiones[${card.dataset.indice}][recursos][${recurso.id}][observacion]" value="${escaparHtml(usado?.observacion ?? '')}" placeholder="Observación" class="rounded-lg border border-white/10 bg-black/20 px-3 py-2 text-sm text-white"></div>`;
                }).join('')
                : '<p class="text-xs text-gray-600">No hay recursos disponibles para esta ubicación.</p>';
        }

        function actualizarUbicacionSesion(card, datosRecursos = null) {
            const idEspacio = card.querySelector('.sesionEspacio').value;
            const cupo = card.querySelector('.sesionCupo');
            const capacidadTexto = card.querySelector('.sesionCapacidad');
            const espacio = obtenerEspacio(idEspacio);
            const capacidad = modalidadSesion(card) === 'presencial'
                ? (espacio?.capacidad ?? '')
                : '';

            capacidadTexto.classList.toggle('hidden', !capacidad);
            capacidadTexto.textContent = capacidad
                ? `Capacidad del espacio: ${capacidad} personas.`
                : '';

            const limites = [cupoGeneral, capacidad]
                .filter(valor => valor !== null && valor !== '' && !Number.isNaN(Number(valor)))
                .map(Number);

            if (limites.length) {
                cupo.max = Math.min(...limites);
            } else {
                cupo.removeAttribute('max');
            }

            renderRecursosSesion(card, datosRecursos ?? []);
        }

        function actualizarModalidadSesion(card, datosRecursos = null) {
            const virtual = modalidadSesion(card) === 'virtual';
            const enlace = card.querySelector('.sesionEnlace');

            card.querySelectorAll('.sesionPresencialPanel').forEach(panel => {
                panel.classList.toggle('hidden', virtual);
            });

            card.querySelectorAll('.sesionVirtualPanel').forEach(panel => {
                panel.classList.toggle('hidden', !virtual);
            });

            enlace.required = virtual;

            actualizarUbicacionSesion(card, datosRecursos);
            resumenSesion(card);
            actualizarTodoSesiones();
        }

        function actualizarModalidadSesionUnica() {
            const modalidad = modalidadSesionUnica();
            const virtual = modalidad === 'virtual';

            panelSesionUnicaPresencial?.classList.toggle('hidden', virtual);
            panelSesionUnicaVirtual?.classList.toggle('hidden', !virtual);

            if (sesionUnicaEnlace) {
                sesionUnicaEnlace.required = virtual;
            }

            if (sesionUnicaEspacio) {
                sesionUnicaEspacio.disabled = virtual;
            }

            if (sesionUnicaUbicacion) {
                sesionUnicaUbicacion.disabled = virtual;
            }

            renderPreviewSesiones();
        }

        function ocultarResultados(card) {
            card.querySelector('.sesionResultadosEspacio').classList.add('hidden');
        }

        function mostrarEspacioSeleccionado(card, espacio, datosRecursos = null) {
            const item = guardarEnCatalogo(espacio);
            if (!item) return;
            card.querySelector('.sesionEspacio').value = item.id;
            card.querySelector('.sesionUbicacion').value = '';
            card.querySelector('.sesionEspacioNombre').textContent = item.nombre;
            card.querySelector('.sesionEspacioRuta').textContent = contextoCatalogo(item) || item.direccion || '';
            card.querySelector('.sesionEspacioPendiente').classList.toggle('hidden', !item.pendiente_validacion);
            card.querySelector('.sesionEspacioSeleccionado').classList.remove('hidden');
            card.querySelector('.sesionBusquedaEspacio').classList.add('hidden');
            card.querySelector('.sesionUbicacionAnterior').classList.add('hidden');
            card.querySelector('.sesionBuscarEspacio').value = '';
            ocultarResultados(card);
            actualizarUbicacionSesion(card, datosRecursos);
            resumenSesion(card);
            actualizarTodoSesiones();
            programarGuardadoBorrador();
        }

        function editarSeleccionEspacio(card) {
            card.querySelector('.sesionEspacioSeleccionado').classList.add('hidden');
            card.querySelector('.sesionBusquedaEspacio').classList.remove('hidden');
            const buscador = card.querySelector('.sesionBuscarEspacio');
            buscador.value = '';
            buscador.focus();
            buscarEspaciosSesion(card);
        }

        function crearBotonResultado(espacio, alSeleccionar) {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.className = 'block w-full rounded-lg px-3 py-2.5 text-left transition hover:bg-white/5';
            const nombre = document.createElement('div');
            nombre.className = 'text-sm font-semibold text-white';
            nombre.textContent = espacio.nombre ?? '';
            boton.appendChild(nombre);
            const contexto = espacio.contexto || contextoCatalogo(espacio);
            if (contexto) {
                const ruta = document.createElement('div');
                ruta.className = 'mt-1 text-xs leading-5 text-gray-500';
                ruta.textContent = contexto;
                boton.appendChild(ruta);
            }
            if (espacio.direccion) {
                const direccion = document.createElement('div');
                direccion.className = 'mt-1 text-xs text-gray-600';
                direccion.textContent = espacio.direccion;
                boton.appendChild(direccion);
            }
            boton.addEventListener('click', () => alSeleccionar(espacio));
            return boton;
        }

        async function consultarEspacios(q = '', modo = 'sesion') {
            const url = new URL(urlBuscarEspacios, window.location.origin);
            url.searchParams.set('modo', modo);
            url.searchParams.set('limite', '20');
            if (espacioActividad) url.searchParams.set('contexto', espacioActividad);
            if (q.trim() !== '') url.searchParams.set('q', q.trim());
            const response = await fetch(url.toString(), { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('No se pudieron cargar los espacios.');
            const data = await response.json();
            return (data.data ?? []).map(guardarEnCatalogo).filter(Boolean);
        }

        function mostrarEspacioUnicoSeleccionado(espacio) {
            const item = guardarEnCatalogo(espacio);
            if (!item || !item.permite_actividades) return;

            sesionUnicaEspacio.value = item.id;
            sesionUnicaUbicacion.value = '';
            sesionUnicaEspacioNombre.textContent = item.nombre;
            sesionUnicaEspacioRuta.textContent = contextoCatalogo(item) || item.direccion || '';
            sesionUnicaEspacioPendiente.classList.toggle('hidden', !item.pendiente_validacion);
            sesionUnicaEspacioSeleccionado.classList.remove('hidden');
            sesionUnicaBusquedaEspacio.classList.add('hidden');
            sesionUnicaResultadosEspacio.classList.add('hidden');
            sesionUnicaBuscarEspacio.value = '';
            sesionUnicaUbicacionExterna.classList.add('hidden');
            programarGuardadoBorrador();
        }

        function mostrarBusquedaEspacioSesionUnica() {
            sesionUnicaEspacioSeleccionado.classList.add('hidden');
            sesionUnicaBusquedaEspacio.classList.remove('hidden');
            sesionUnicaBuscarEspacio.value = '';
            sesionUnicaBuscarEspacio.focus();
            buscarEspaciosSesionUnica();
        }

        async function buscarEspaciosSesionUnica() {
            try {
                const espacios = await consultarEspacios(sesionUnicaBuscarEspacio.value, 'sesion');
                sesionUnicaResultadosEspacio.innerHTML = '';

                if (espacios.length === 0) {
                    sesionUnicaResultadosEspacio.innerHTML = '<p class="px-3 py-3 text-center text-xs text-gray-500">No hay espacios habilitados que coincidan.</p>';
                } else {
                    espacios.forEach(espacio => {
                        sesionUnicaResultadosEspacio.appendChild(
                            crearBotonResultado(espacio, mostrarEspacioUnicoSeleccionado)
                        );
                    });
                }

                sesionUnicaResultadosEspacio.classList.remove('hidden');
            } catch (error) {
                sesionUnicaResultadosEspacio.classList.add('hidden');
            }
        }

        function renderEspacioSesionUnicaActual() {
            const idActual = sesionUnicaEspacio?.value || '';
            const actual = obtenerEspacio(idActual);
            const general = obtenerEspacio(espacioActividad);

            btnUsarEspacioGeneralSesionUnica?.classList.toggle(
                'hidden',
                !general || !general.permite_actividades
            );

            if (actual && actual.permite_actividades && espacioPerteneceAlLugarGeneral(actual)) {
                mostrarEspacioUnicoSeleccionado(actual);
                return;
            }

            let ubicacionAnteriorInvalida = false;

            if (idActual && actual && (!actual.permite_actividades || !espacioPerteneceAlLugarGeneral(actual))) {
                sesionUnicaEspacio.value = '';
                ubicacionAnteriorInvalida = true;
                sesionUnicaUbicacionExterna.textContent = 'La ubicación anterior ya no es válida para el lugar general actual. Selecciona un nuevo espacio.';
                sesionUnicaUbicacionExterna.classList.remove('hidden');
            }

            if (!sesionUnicaEspacio.value && general?.permite_actividades) {
                mostrarEspacioUnicoSeleccionado(general);
                return;
            }

            const externa = sesionUnicaUbicacion?.value?.trim() || '';
            if (!ubicacionAnteriorInvalida && !espacioActividad && externa) {
                sesionUnicaUbicacionExterna.textContent = `Se usará la ubicación externa general: ${externa}`;
                sesionUnicaUbicacionExterna.classList.remove('hidden');
            } else if (!ubicacionAnteriorInvalida) {
                sesionUnicaUbicacionExterna.classList.add('hidden');
            }

            sesionUnicaEspacioSeleccionado.classList.add('hidden');
            sesionUnicaBusquedaEspacio.classList.remove('hidden');
        }

        async function buscarEspaciosSesion(card) {
            const buscador = card.querySelector('.sesionBuscarEspacio');
            const resultados = card.querySelector('.sesionResultadosEspacio');
            try {
                const espacios = await consultarEspacios(buscador.value);
                resultados.innerHTML = '';
                if (espacios.length === 0) {
                    resultados.innerHTML = '<p class="px-3 py-3 text-center text-xs text-gray-500">No hay coincidencias.</p>';
                } else {
                    espacios.forEach(espacio => {
                        resultados.appendChild(crearBotonResultado(espacio, seleccionado => mostrarEspacioSeleccionado(card, seleccionado)));
                    });
                }
                resultados.classList.remove('hidden');
            } catch (error) {
                resultados.classList.add('hidden');
            }
        }

        function inicializarBuscadorSesion(card, datos) {
            const buscador = card.querySelector('.sesionBuscarEspacio');
            const cambiar = card.querySelector('.btnCambiarEspacioSesion');
            const usarGeneral = card.querySelector('.btnUsarEspacioGeneral');
            const crear = card.querySelector('.btnCrearEspacioSesion');
            const externa = card.querySelector('.sesionUbicacion');
            const anterior = card.querySelector('.sesionUbicacionAnterior');
            const anteriorTexto = card.querySelector('.sesionUbicacionAnteriorTexto');

            const general = obtenerEspacio(espacioActividad);
            usarGeneral.classList.toggle('hidden', !general || !general.permite_actividades);

            buscador.addEventListener('focus', () => buscarEspaciosSesion(card));
            buscador.addEventListener('input', () => {
                clearTimeout(card._temporizadorEspacios);
                card._temporizadorEspacios = setTimeout(() => buscarEspaciosSesion(card), 250);
            });
            cambiar.addEventListener('click', () => editarSeleccionEspacio(card));
            usarGeneral.addEventListener('click', () => {
                if (general?.permite_actividades) {
                    mostrarEspacioSeleccionado(card, general);
                }
            });
            crear.addEventListener('click', () => abrirModalCrearEspacio(card));

            if (datos?.id_espacio) {
                const actual = obtenerEspacio(datos.id_espacio);
                if (actual?.permite_actividades && espacioPerteneceAlLugarGeneral(actual)) {
                    mostrarEspacioSeleccionado(card, actual, datos.recursos ?? []);
                    return;
                }
            }

            if (datos?.ubicacion) {
                externa.value = datos.ubicacion;
                anteriorTexto.textContent = datos.ubicacion;
                anterior.classList.remove('hidden');
                card.querySelector('.sesionBusquedaEspacio').classList.remove('hidden');
                return;
            }

            if (general?.permite_actividades) {
                mostrarEspacioSeleccionado(card, general, datos?.recursos ?? []);
            } else {
                card.querySelector('.sesionBusquedaEspacio').classList.remove('hidden');
            }
        }

        function agregarSesion(datos = null, contraida = false) {
            const indice = contadorSesiones++;
            const card = document.createElement('div');
            const existente = Boolean(datos?.id_sesion);
            const modalidadInicial = datos?.modalidad
                || (datos?.enlace_acceso ? 'virtual' : 'presencial');

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
                            <input type="text"
                                name="sesiones[${indice}][nombre]"
                                class="sesionNombre block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                                placeholder="Ej. Ponencia inaugural, Taller IoT"
                                required>
                        </div>
                        <button type="button"
                            class="btnQuitarSesion mt-7 h-10 w-10 rounded-xl border border-red-500/20 text-red-400">
                            ×
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Inicio</label>
                            <input type="datetime-local"
                                name="sesiones[${indice}][fecha_inicio]"
                                class="sesionInicio block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                                required>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Finalización</label>
                            <input type="datetime-local"
                                name="sesiones[${indice}][fecha_fin]"
                                class="sesionFin block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white">
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="mb-2 text-sm font-medium text-gray-300">Modalidad</p>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <label class="cursor-pointer rounded-xl border border-white/10 bg-black/10 p-3">
                                <div class="flex items-start gap-3">
                                    <input type="radio"
                                        name="sesiones[${indice}][modalidad]"
                                        value="presencial"
                                        class="sesionModalidad mt-1"
                                        ${modalidadInicial === 'presencial' ? 'checked' : ''}>
                                    <div>
                                        <p class="text-sm font-semibold text-white">Presencial</p>
                                        <p class="mt-1 text-xs leading-5 text-gray-600">Selecciona el espacio donde se realizará.</p>
                                    </div>
                                </div>
                            </label>
                            <label class="cursor-pointer rounded-xl border border-white/10 bg-black/10 p-3">
                                <div class="flex items-start gap-3">
                                    <input type="radio"
                                        name="sesiones[${indice}][modalidad]"
                                        value="virtual"
                                        class="sesionModalidad mt-1"
                                        ${modalidadInicial === 'virtual' ? 'checked' : ''}>
                                    <div>
                                        <p class="text-sm font-semibold text-white">Virtual</p>
                                        <p class="mt-1 text-xs leading-5 text-gray-600">Solicita un enlace de acceso.</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sesionPresencialPanel">
                            <label class="mb-2 block text-sm font-medium text-gray-300">Lugar</label>
                            <input type="hidden"
                                name="sesiones[${indice}][id_espacio]"
                                class="sesionEspacio"
                                value="">
                            <input type="hidden"
                                name="sesiones[${indice}][ubicacion]"
                                class="sesionUbicacion"
                                value="">

                            <div class="sesionEspacioSeleccionado hidden rounded-xl border border-cyan-500/20 bg-cyan-500/[0.05] p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="sesionEspacioNombre text-sm font-semibold text-white"></p>
                                            <span class="sesionEspacioPendiente hidden rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-300">Pendiente</span>
                                        </div>
                                        <p class="sesionEspacioRuta mt-1 text-xs leading-5 text-gray-500"></p>
                                    </div>
                                    <button type="button"
                                        class="btnCambiarEspacioSesion shrink-0 text-xs font-semibold text-cyan-300">
                                        Cambiar
                                    </button>
                                </div>
                            </div>

                            <div class="sesionBusquedaEspacio relative">
                                <input type="text"
                                    autocomplete="off"
                                    class="sesionBuscarEspacio block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none placeholder:text-gray-600 focus:border-cyan-500"
                                    placeholder="Buscar dentro del lugar general...">
                                <div class="sesionResultadosEspacio absolute left-0 right-0 top-full z-30 mt-2 hidden max-h-64 overflow-y-auto rounded-xl border border-white/10 bg-[#0f172a] p-2 shadow-2xl"></div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <button type="button"
                                        class="btnUsarEspacioGeneral rounded-lg border border-white/10 px-3 py-2 text-xs font-semibold text-gray-400 ${espacioActividad ? '' : 'hidden'}">
                                        Usar ubicación general
                                    </button>
                                    <button type="button"
                                        class="btnCrearEspacioSesion rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300">
                                        + Registrar espacio
                                    </button>
                                </div>
                            </div>

                            <div class="sesionUbicacionAnterior mt-2 hidden rounded-lg border border-amber-500/20 bg-amber-500/[0.05] p-3 text-xs text-amber-200">
                                Ubicación anterior:
                                <span class="sesionUbicacionAnteriorTexto font-semibold"></span>.
                                Puedes conservarla o reemplazarla por un espacio registrado.
                            </div>

                            <p class="sesionCapacidad mt-2 hidden text-xs text-gray-500"></p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">Cupo de esta sesión</label>
                            <input type="number"
                                name="sesiones[${indice}][cupo]"
                                min="0"
                                ${cupoGeneral !== null ? `max="${cupoGeneral}"` : ''}
                                class="sesionCupo block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                                placeholder="${cupoGeneral !== null ? `Vacío = hereda ${cupoGeneral}` : 'Vacío = sin límite propio'}">
                        </div>
                    </div>

                    <div class="sesionPresencialPanel mt-4 rounded-xl border border-white/10 p-4">
                        <p class="text-sm font-medium text-gray-300">Recursos que utilizará</p>
                        <p class="mt-1 text-xs text-gray-600">
                            Se muestran los recursos del espacio seleccionado y los recursos móviles.
                        </p>
                        <div class="sesionRecursos mt-3 space-y-2"></div>
                    </div>

                    <div class="sesionVirtualPanel mt-4 hidden rounded-xl border border-cyan-500/15 bg-cyan-500/[0.03] p-4">
                        <label class="mb-2 block text-sm font-medium text-gray-300">Enlace de acceso</label>
                        <input type="url"
                            name="sesiones[${indice}][enlace_acceso]"
                            class="sesionEnlace block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white"
                            placeholder="https://meet.google.com/...">
                        <p class="mt-2 text-xs leading-5 text-gray-600">
                            Obligatorio únicamente para sesiones virtuales.
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-white/10 p-4">
                            <input type="hidden" name="sesiones[${indice}][requiere_reserva]" value="0">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox"
                                    name="sesiones[${indice}][requiere_reserva]"
                                    value="1"
                                    class="sesionReserva mt-1">
                                <div>
                                    <p class="text-sm font-medium text-gray-300">Requiere reserva</p>
                                    <p class="mt-1 text-xs leading-5 text-gray-600">
                                        La persona deberá elegir esta sesión específicamente.
                                    </p>
                                </div>
                            </label>
                        </div>

                        <div class="rounded-xl border border-white/10 p-4">
                            <input type="hidden" name="sesiones[${indice}][obligatoria]" value="0">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox"
                                    name="sesiones[${indice}][obligatoria]"
                                    value="1"
                                    class="sesionObligatoria mt-1">
                                <div>
                                    <p class="text-sm font-medium text-gray-300">Obligatoria para participantes</p>
                                    <p class="mt-1 text-xs leading-5 text-gray-600">
                                        Indica que forma parte obligatoria de la actividad. No depende de si es presencial o virtual.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="button"
                            class="btnListoSesion rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-300">
                            Listo
                        </button>
                    </div>
                </div>
            `;

            contenedor.appendChild(card);

            const nombre = card.querySelector('.sesionNombre');
            const inicio = card.querySelector('.sesionInicio');
            const fin = card.querySelector('.sesionFin');
            const cupo = card.querySelector('.sesionCupo');
            const enlace = card.querySelector('.sesionEnlace');
            const reserva = card.querySelector('.sesionReserva');
            const obligatoria = card.querySelector('.sesionObligatoria');
            const radiosModalidad = card.querySelectorAll('.sesionModalidad');

            inicio.min = existente ? realizacionDesde : minimoNuevaSesion();
            fin.min = existente ? realizacionDesde : minimoNuevaSesion();

            if (realizacionHasta) {
                inicio.max = realizacionHasta;
                fin.max = realizacionHasta;
            }

            if (datos) {
                nombre.value = datos.nombre ?? '';
                inicio.value = datos.fecha_inicio ?? '';
                fin.value = datos.fecha_fin ?? '';
                cupo.value = datos.cupo ?? '';
                enlace.value = datos.enlace_acceso ?? '';
                reserva.checked = datos.requiere_reserva === true;
                obligatoria.checked = datos.obligatoria === true;
            }

            inicializarBuscadorSesion(card, datos);
            actualizarModalidadSesion(card, datos?.recursos ?? []);

            [nombre, inicio, fin, cupo, enlace].forEach(input => {
                input.addEventListener('input', () => {
                    resumenSesion(card);
                    actualizarTodoSesiones();
                    programarGuardadoBorrador();
                });

                input.addEventListener('change', () => {
                    resumenSesion(card);
                    actualizarTodoSesiones();
                    programarGuardadoBorrador();
                });
            });

            [reserva, obligatoria].forEach(input => {
                input.addEventListener('change', () => {
                    resumenSesion(card);
                    actualizarTodoSesiones();
                    programarGuardadoBorrador();
                });
            });

            radiosModalidad.forEach(radio => {
                radio.addEventListener('change', () => {
                    actualizarModalidadSesion(card, recolectarRecursosSesion(card));
                    programarGuardadoBorrador();
                });
            });

            card.querySelector('.cabeceraSesion').addEventListener('click', () => {
                card.querySelector('.sesionCuerpo').classList.contains('hidden')
                    ? expandirSesion(card)
                    : contraerSesion(card);
            });

            card.querySelector('.btnListoSesion').addEventListener('click', () => {
                contraerSesion(card);
                actualizarTodoSesiones();
                guardarBorradorSesiones(true);
            });

            card.querySelector('.btnQuitarSesion').addEventListener('click', () => {
                card.remove();
                actualizarTodoSesiones();
                guardarBorradorSesiones(true);
            });

            resumenSesion(card);
            contraida ? contraerSesion(card) : expandirSesion(card);
            actualizarTodoSesiones();

            if (!restaurandoBorrador) {
                programarGuardadoBorrador();
            }
        }

        function seleccionarContenedorModal(espacio) {
            const item = guardarEnCatalogo(espacio);
            if (!item) return;
            nuevoContenedorId.value = item.id;
            contenedorNombre.textContent = item.nombre;
            contenedorRuta.textContent = contextoCatalogo(item) || item.direccion || '';
            contenedorSeleccionado.classList.remove('hidden');
            contenedorBusqueda.classList.add('hidden');
            resultadosContenedor.classList.add('hidden');
            buscarContenedor.value = '';
        }

        async function buscarContenedoresModal() {
            try {
                const espacios = await consultarEspacios(buscarContenedor.value, 'contenedor_sesion');
                resultadosContenedor.innerHTML = '';
                if (espacios.length === 0) {
                    resultadosContenedor.innerHTML = '<p class="px-3 py-3 text-center text-xs text-gray-500">No hay coincidencias.</p>';
                } else {
                    espacios.forEach(espacio => resultadosContenedor.appendChild(crearBotonResultado(espacio, seleccionarContenedorModal)));
                }
                resultadosContenedor.classList.remove('hidden');
            } catch (error) {
                resultadosContenedor.classList.add('hidden');
            }
        }

        function limpiarModalEspacio() {
            errorEspacio.classList.add('hidden');
            errorEspacio.textContent = '';
            nuevoNombre.value = '';
            nuevaDireccion.value = '';
            nuevaDescripcion.value = '';
            nuevaCapacidad.value = '';
            nuevaLatitud.value = '';
            nuevaLongitud.value = '';
            buscarContenedor.value = '';
            resultadosContenedor.classList.add('hidden');
        }

        function abrirModalCrearEspacio(card = null, esSesionUnica = false) {
            tarjetaObjetivo = card;
            objetivoSesionUnica = esSesionUnica;
            limpiarModalEspacio();

            const textoBuscado = esSesionUnica
                ? sesionUnicaBuscarEspacio.value.trim()
                : card.querySelector('.sesionBuscarEspacio').value.trim();

            const ubicacionAnterior = esSesionUnica
                ? (sesionUnicaUbicacion?.value?.trim() || '')
                : card.querySelector('.sesionUbicacion').value.trim();

            nuevoNombre.value = textoBuscado || ubicacionAnterior;

            const idActual = esSesionUnica
                ? sesionUnicaEspacio.value
                : card.querySelector('.sesionEspacio').value;

            const actual = obtenerEspacio(idActual);
            const contenedorInicial = actual || obtenerEspacio(espacioActividad);

            if (contenedorInicial) {
                seleccionarContenedorModal(contenedorInicial);
            } else {
                nuevoContenedorId.value = '';
                contenedorSeleccionado.classList.add('hidden');
                contenedorBusqueda.classList.remove('hidden');
            }

            modalEspacio.classList.remove('hidden');
            modalEspacio.classList.add('flex');
            setTimeout(() => nuevoNombre.focus(), 50);
        }

        function cerrarModalCrearEspacio() {
            modalEspacio.classList.add('hidden');
            modalEspacio.classList.remove('flex');
            tarjetaObjetivo = null;
            objetivoSesionUnica = false;
        }

        function mostrarErrorEspacio(mensaje) {
            errorEspacio.textContent = mensaje;
            errorEspacio.classList.remove('hidden');
        }

        async function guardarNuevoEspacio() {
            if (!tarjetaObjetivo && !objetivoSesionUnica) return;
            const nombre = nuevoNombre.value.trim();
            const direccion = nuevaDireccion.value.trim();
            if (!nombre) {
                mostrarErrorEspacio('Escribe el nombre del espacio.');
                nuevoNombre.focus();
                return;
            }
            if (!direccion) {
                mostrarErrorEspacio('Escribe la dirección o referencia del espacio.');
                nuevaDireccion.focus();
                return;
            }
            if (espacioActividad && !nuevoContenedorId.value) {
                mostrarErrorEspacio('Selecciona a qué espacio pertenece.');
                return;
            }

            guardarEspacio.disabled = true;
            errorEspacio.classList.add('hidden');

            try {
                const response = await fetch(urlCrearEspacio, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfSesiones,
                    },
                    body: JSON.stringify({
                        origen: 'sesion',
                        id_espacio_contenedor: nuevoContenedorId.value ? Number(nuevoContenedorId.value) : null,
                        nombre,
                        descripcion: nuevaDescripcion.value.trim() || null,
                        direccion,
                        capacidad: nuevaCapacidad.value !== '' ? Number(nuevaCapacidad.value) : null,
                        latitud: nuevaLatitud.value !== '' ? Number(nuevaLatitud.value) : null,
                        longitud: nuevaLongitud.value !== '' ? Number(nuevaLongitud.value) : null,
                    }),
                });
                const data = await response.json();
                if (!response.ok) {
                    if (data.espacio && data.espacio.permite_actividades) {
                        if (objetivoSesionUnica) {
                            mostrarEspacioUnicoSeleccionado(data.espacio);
                        } else {
                            mostrarEspacioSeleccionado(tarjetaObjetivo, data.espacio);
                        }
                        cerrarModalCrearEspacio();
                        return;
                    }
                    const errores = data.errors ? Object.values(data.errors).flat().join(' ') : null;
                    throw new Error(errores || data.message || 'No se pudo registrar el espacio.');
                }
                if (objetivoSesionUnica) {
                    mostrarEspacioUnicoSeleccionado(data.espacio);
                } else {
                    mostrarEspacioSeleccionado(tarjetaObjetivo, data.espacio);
                }
                cerrarModalCrearEspacio();
            } catch (error) {
                mostrarErrorEspacio(error.message || 'No se pudo registrar el espacio.');
            } finally {
                guardarEspacio.disabled = false;
            }
        }

        function actualizarModoSesiones() {
            const modo = modoSeleccionado();

            panelSesionUnica.classList.toggle('hidden', modo !== 'unica');
            seccionSesiones.classList.toggle('hidden', modo !== 'multiples');

            if (modo === 'multiples') {
                while (contenedor.querySelectorAll('.sesionCard').length < 2) {
                    agregarSesion(null, false);
                }
            }

            actualizarModalidadSesionUnica();
            actualizarTodoSesiones();
        }

        document.querySelectorAll('input[name="modo_sesiones"]').forEach(radio => {
            radio.addEventListener('change', () => {
                actualizarModoSesiones();
                programarGuardadoBorrador();
            });
        });

        document.querySelectorAll('input[name="sesion_unica_modalidad"]').forEach(radio => {
            radio.addEventListener('change', () => {
                actualizarModalidadSesionUnica();
                programarGuardadoBorrador();
            });
        });

        sesionUnicaEnlace?.addEventListener('input', programarGuardadoBorrador);
        sesionUnicaEnlace?.addEventListener('change', programarGuardadoBorrador);

        sesionUnicaBuscarEspacio?.addEventListener('focus', buscarEspaciosSesionUnica);
        sesionUnicaBuscarEspacio?.addEventListener('input', () => {
            clearTimeout(sesionUnicaBuscarEspacio._temporizador);
            sesionUnicaBuscarEspacio._temporizador = setTimeout(buscarEspaciosSesionUnica, 250);
        });

        btnCambiarEspacioSesionUnica?.addEventListener('click', mostrarBusquedaEspacioSesionUnica);

        btnUsarEspacioGeneralSesionUnica?.addEventListener('click', () => {
            const general = obtenerEspacio(espacioActividad);
            if (general?.permite_actividades) {
                mostrarEspacioUnicoSeleccionado(general);
            }
        });

        btnCrearEspacioSesionUnica?.addEventListener('click', () => {
            abrirModalCrearEspacio(null, true);
        });

        btnAgregar?.addEventListener('click', () => {
            agregarSesion(null, false);
            guardarBorradorSesiones(true);
        });

        const borradorRecuperado = cargarBorradorSesiones();

        if (!restaurarBorradorSesiones(borradorRecuperado)) {
            existentes.forEach(sesion => agregarSesion(sesion, true));
        }

        renderEspacioSesionUnicaActual();

        buscarContenedor.addEventListener('focus', buscarContenedoresModal);

        buscarContenedor.addEventListener('input', () => {
            clearTimeout(temporizadorContenedor);
            temporizadorContenedor = setTimeout(buscarContenedoresModal, 250);
        });

        cambiarContenedor.addEventListener('click', () => {
            contenedorSeleccionado.classList.add('hidden');
            contenedorBusqueda.classList.remove('hidden');
            nuevoContenedorId.value = '';
            buscarContenedor.focus();
            buscarContenedoresModal();
        });

        cerrarModalEspacio.addEventListener('click', cerrarModalCrearEspacio);
        cancelarModalEspacio.addEventListener('click', cerrarModalCrearEspacio);
        guardarEspacio.addEventListener('click', guardarNuevoEspacio);

        modalEspacio.addEventListener('click', event => {
            if (event.target === modalEspacio) cerrarModalCrearEspacio();
        });

        descartarBorradorSesiones?.addEventListener('click', () => {
            try {
                localStorage.removeItem(claveBorradorSesiones);
            } catch (error) {
            }

            borradorHabilitado = false;
            ocultarEstadoBorrador();
            window.location.reload();
        });

        document.addEventListener('click', event => {
            formSesiones.querySelectorAll('.sesionResultadosEspacio').forEach(lista => {
                if (!lista.parentElement.contains(event.target)) {
                    lista.classList.add('hidden');
                }
            });

            if (sesionUnicaResultadosEspacio && !sesionUnicaResultadosEspacio.parentElement.contains(event.target)) {
                sesionUnicaResultadosEspacio.classList.add('hidden');
            }

            if (!resultadosContenedor.parentElement.contains(event.target)) {
                resultadosContenedor.classList.add('hidden');
            }
        });

        formSesiones.addEventListener('input', event => {
            if (event.target.closest('.sesionRecursos')) {
                programarGuardadoBorrador();
            }
        });

        formSesiones.addEventListener('change', event => {
            if (event.target.closest('.sesionRecursos')) {
                programarGuardadoBorrador();
            }
        });

        window.addEventListener('beforeunload', () => {
            if (!guardadoEnServidor) {
                guardarBorradorSesiones(false);
            }
        });

        formSesiones.addEventListener('submit', async function (event) {
            event.preventDefault();
            limpiarErrorProgramacion();

            if (modoSeleccionado() === 'multiples') {
                const total = contenedor.querySelectorAll('.sesionCard').length;

                if (total < 2) {
                    mostrarErrorProgramacion('Si seleccionaste varias sesiones, agrega al menos dos.');
                    return;
                }

                const cruces = detectarSolapamientos();

                if (cruces.length > 0 && !confirmarSolapamientos.checked) {
                    mostrarErrorProgramacion(
                        'Hay sesiones que se realizan al mismo tiempo. Confirma el solapamiento antes de guardar.'
                    );
                    advertenciaSolapamientos.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });
                    return;
                }
            }

            if (!formSesiones.checkValidity()) {
                formSesiones.reportValidity();
                return;
            }

            guardarBorradorSesiones(true);

            const textoOriginal = botonGuardarSesiones.textContent;
            botonGuardarSesiones.disabled = true;
            botonGuardarSesiones.textContent = 'Guardando...';

            try {
                const response = await fetch(formSesiones.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(formSesiones),
                });

                let data = {};

                try {
                    data = await response.json();
                } catch (error) {
                    data = {};
                }

                if (!response.ok) {
                    const errores = data.errors
                        ? Object.values(data.errors).flat()
                        : [];

                    const mensaje = errores[0]
                        || data.message
                        || 'No se pudo guardar la programación.';

                    mostrarErrorProgramacion(mensaje);

                    if (window.SIDANToast?.error) {
                        window.SIDANToast.error(mensaje);
                    }

                    return;
                }

                guardadoEnServidor = true;
                borradorHabilitado = false;
                clearTimeout(temporizadorBorrador);

                try {
                    localStorage.removeItem(claveBorradorSesiones);
                } catch (error) {
                }

                ocultarEstadoBorrador();

                if (window.SIDANToast?.success) {
                    window.SIDANToast.success(
                        data.message || 'Programación guardada correctamente.'
                    );
                }

                window.location.href = data.redirect;
            } catch (error) {
                const mensaje = 'No se pudo conectar con el servidor. Tu borrador sigue guardado en este navegador.';
                mostrarErrorProgramacion(mensaje);

                if (window.SIDANToast?.error) {
                    window.SIDANToast.error(mensaje);
                }
            } finally {
                if (!guardadoEnServidor) {
                    botonGuardarSesiones.disabled = false;
                    botonGuardarSesiones.textContent = textoOriginal;
                }
            }
        });

        actualizarModoSesiones();
        actualizarModalidadSesionUnica();
        borradorHabilitado = true;

        if (borradorRecuperado) {
            mostrarEstadoBorrador('Recuperamos la programación que todavía no habías guardado.');
        }
    }
});
</script>
@endsection
