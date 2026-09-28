@extends('layouts.navbars')

@section('title', 'Configurar actividad')

@section('content')
@php
    $pasos = [
        'productos' => ['numero' => 1, 'titulo' => 'Productos o servicios'],
        'datos' => ['numero' => 2, 'titulo' => 'Datos que pediremos al comprador'],
        'precios' => ['numero' => 3, 'titulo' => 'Aumentos de precio y costo'],
        'sesiones' => ['numero' => 4, 'titulo' => 'Programación de la actividad'],
    ];

    $pasoActual = $pasos[$paso] ?? $pasos['productos'];
    $numeroPaso = $pasoActual['numero'];
    $tituloPaso = $pasoActual['titulo'];

    $totalItems = $actividad->items->count();

    /*
    |--------------------------------------------------------------------------
    | PRODUCTOS O SERVICIOS
    |--------------------------------------------------------------------------
    | Ya no interpretamos "0 productos" como configuración incompleta.
    | La decisión del administrador se guarda de forma independiente.
    */
    $productosConfigurados = (bool) ($configuracionProductos['configured'] ?? false);
    $ofreceProductos = (bool) ($configuracionProductos['enabled'] ?? false);

    /*
    | Si ya existen ítems, consideramos que la actividad sí ofrece productos
    | aunque haya sido creada antes de incorporar esta nueva decisión.
    */
    if ($totalItems > 0) {
        $productosConfigurados = true;
        $ofreceProductos = true;
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS DEL COMPRADOR
    |--------------------------------------------------------------------------
    */
    $itemsDatosConfigurados = $actividad->items->filter(function ($item) use ($configuracionesDatos) {
        return $configuracionesDatos->has((string) $item->id_item_actividad);
    })->count();

    $datosCompletos = !$ofreceProductos
        || (
            $totalItems > 0
            && $itemsDatosConfigurados === $totalItems
        );

    /*
    |--------------------------------------------------------------------------
    | PRECIOS
    |--------------------------------------------------------------------------
    */
    $itemsPrecioCompletos = $actividad->items->filter(function ($item) use ($configuracionesDatos, $configuracionesPrecios) {
        $configDatos = $configuracionesDatos->get((string) $item->id_item_actividad);

        if (!$configDatos || !($configDatos['enabled'] ?? false)) {
            return true;
        }

        $camposLista = collect($configDatos['fields'] ?? [])->filter(
            fn ($campo) => ($campo['type'] ?? null) === 'lista'
                && !empty($campo['options'] ?? [])
        );

        if ($camposLista->isEmpty()) {
            return true;
        }

        return $configuracionesPrecios->has(
            (string) $item->id_item_actividad
        );
    })->count();

    $preciosCompletos = !$ofreceProductos
        || (
            $totalItems > 0
            && $itemsPrecioCompletos === $totalItems
        );

    /*
    |--------------------------------------------------------------------------
    | SESIONES
    |--------------------------------------------------------------------------
    */
    $sesionesConfiguradas = !empty($modoSesiones);

    $sesionesCodificadas = base64_encode(
        json_encode(
            $actividad->sesiones
                ->sortBy('orden')
                ->map(function ($sesion) {
                    return [
                        'id_sesion' => $sesion->id_sesion,
                        'nombre' => $sesion->nombre,

                        'fecha_inicio' => $sesion->fecha_inicio
                            ? \Carbon\Carbon::parse($sesion->fecha_inicio)->format('Y-m-d\TH:i')
                            : '',

                        'fecha_fin' => $sesion->fecha_fin
                            ? \Carbon\Carbon::parse($sesion->fecha_fin)->format('Y-m-d\TH:i')
                            : '',

                        'ubicacion' => $sesion->ubicacion,
                        'enlace_acceso' => $sesion->enlace_acceso,
                        'cupo' => $sesion->cupo,

                        'requiere_reserva' => (bool) $sesion->requiere_reserva,
                        'obligatoria' => (bool) $sesion->obligatoria,
                    ];
                })
                ->values()
                ->all(),

            JSON_UNESCAPED_UNICODE
        )
    );

    /*
    |--------------------------------------------------------------------------
    | PERÍODO GENERAL DE REALIZACIÓN
    |--------------------------------------------------------------------------
    */
    $realizacionDesde = $actividad->realizacion_desde
        ? \Carbon\Carbon::parse($actividad->realizacion_desde)->format('Y-m-d\TH:i')
        : '';

    $realizacionHasta = $actividad->realizacion_hasta
        ? \Carbon\Carbon::parse($actividad->realizacion_hasta)->format('Y-m-d\TH:i')
        : '';

    $realizacionDesdeTexto = $actividad->realizacion_desde
        ? \Carbon\Carbon::parse($actividad->realizacion_desde)->format('d/m/Y H:i')
        : null;

    $realizacionHastaTexto = $actividad->realizacion_hasta
        ? \Carbon\Carbon::parse($actividad->realizacion_hasta)->format('d/m/Y H:i')
        : null;

    /*
    |--------------------------------------------------------------------------
    | CUPO GENERAL
    |--------------------------------------------------------------------------
    */
    $tieneInscripcionGeneral = (bool) $actividad->habilita_inscripcion;
    $cupoGeneral = $actividad->cupo_total;
@endphp

<div class="w-full min-w-0 max-w-full overflow-x-hidden">
    <div class="mx-auto w-full max-w-6xl px-4 py-5 sm:px-6 sm:py-7 lg:px-8">

        {{-- ========================================================= --}}
        {{-- ENCABEZADO --}}
        {{-- ========================================================= --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                        Configuración pendiente
                    </span>

                    <span class="text-xs text-gray-500">
                        {{ $actividad->nombre }}
                    </span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Termina de configurar tu actividad
                </h1>

                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">
                    Configura únicamente lo que aplique. Una actividad puede ser gratuita,
                    no tener productos o servicios y aun así utilizar inscripciones,
                    sesiones, horarios o reservas.
                </p>
            </div>

            <a href="{{ route('admin.actividades.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white">
                Volver al listado
            </a>
        </div>

        {{-- ========================================================= --}}
        {{-- PROGRESO --}}
        {{-- ========================================================= --}}
        <div class="mb-7">
            <div class="mb-3 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-400">
                        Paso {{ $numeroPaso }} de 4
                    </p>

                    <p class="mt-1 truncate text-sm font-semibold text-white sm:text-base">
                        {{ $tituloPaso }}
                    </p>
                </div>

                <span class="shrink-0 text-sm font-semibold text-gray-500">
                    {{ $numeroPaso * 25 }}%
                </span>
            </div>

            <div class="flex gap-2">
                @for ($i = 1; $i <= 4; $i++)
                    <div class="h-1.5 flex-1 rounded-full {{ $i <= $numeroPaso ? 'bg-emerald-500' : 'bg-gray-800' }}"></div>
                @endfor
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- PASO 1: PRODUCTOS O SERVICIOS --}}
        {{-- ========================================================= --}}
        @if ($paso === 'productos')

            <div class="mb-6 rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-4 sm:p-5">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-lg text-blue-300">
                        ?
                    </div>

                    <div class="min-w-0">
                        <h2 class="font-semibold text-blue-200">
                            ¿Esta actividad ofrece algún producto o servicio?
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-gray-400">
                            Indica si las personas podrán comprar, solicitar o reservar
                            algún elemento dentro de esta actividad.
                        </p>

                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            Esto es independiente de la inscripción. Una actividad puede
                            ser completamente gratuita y aun así solicitar inscripción o
                            reservar cupos para determinadas sesiones.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ===================================================== --}}
            {{-- DECISIÓN: ¿OFRECE PRODUCTOS/SERVICIOS? --}}
            {{-- ===================================================== --}}
            <form action="{{ route('admin.actividades.productos.configuracion', $actividad) }}"
                method="POST"
                class="mb-6 rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-6">

                @csrf

                <div>
                    <h2 class="font-semibold text-white">
                        ¿Qué aplica para esta actividad?
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-gray-500">
                        Selecciona una opción. Podrás cambiar esta decisión mientras
                        la actividad continúe siendo editable.
                    </p>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-2">

                    {{-- NO --}}
                    <label class="cursor-pointer rounded-2xl border p-4 transition sm:p-5 {{ $productosConfigurados && !$ofreceProductos ? 'border-emerald-500/40 bg-emerald-500/[0.08]' : 'border-white/10 bg-black/10 hover:border-white/20' }}">
                        <div class="flex items-start gap-3">
                            <input type="radio"
                                name="ofrece_productos"
                                value="0"
                                required
                                class="mt-1 h-4 w-4 shrink-0 border-gray-600 bg-gray-800 text-emerald-500 focus:ring-emerald-500"
                                @checked($productosConfigurados && !$ofreceProductos)>

                            <div class="min-w-0">
                                <p class="font-semibold text-white">
                                    No, no ofrece productos o servicios
                                </p>

                                <p class="mt-2 text-sm leading-6 text-gray-500">
                                    Elige esta opción para actividades gratuitas,
                                    informativas o académicas donde no exista nada
                                    adicional que comprar, solicitar o reservar como
                                    producto.
                                </p>

                                <div class="mt-3 rounded-xl border border-white/5 bg-black/10 p-3">
                                    <p class="text-xs font-semibold text-gray-400">
                                        Ejemplos
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-gray-600">
                                        Una ponencia gratuita, una jornada académica,
                                        una actividad abierta o una Semana de Sistemas
                                        sin venta de camisetas ni kits.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </label>

                    {{-- SÍ --}}
                    <label class="cursor-pointer rounded-2xl border p-4 transition sm:p-5 {{ $ofreceProductos ? 'border-emerald-500/40 bg-emerald-500/[0.08]' : 'border-white/10 bg-black/10 hover:border-white/20' }}">
                        <div class="flex items-start gap-3">
                            <input type="radio"
                                name="ofrece_productos"
                                value="1"
                                required
                                class="mt-1 h-4 w-4 shrink-0 border-gray-600 bg-gray-800 text-emerald-500 focus:ring-emerald-500"
                                @checked($ofreceProductos)>

                            <div class="min-w-0">
                                <p class="font-semibold text-white">
                                    Sí, ofrece productos o servicios
                                </p>

                                <p class="mt-2 text-sm leading-6 text-gray-500">
                                    Elige esta opción cuando las personas puedan
                                    comprar, solicitar o reservar algo que necesite
                                    su propio precio, costo, stock o información.
                                </p>

                                <div class="mt-3 rounded-xl border border-white/5 bg-black/10 p-3">
                                    <p class="text-xs font-semibold text-gray-400">
                                        Ejemplos
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-gray-600">
                                        Camisetas, kits de bienvenida, entradas,
                                        cupos de excursión de pago, alimentos,
                                        reservaciones u otros servicios.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="mt-5 rounded-xl border border-blue-500/15 bg-blue-500/[0.04] p-4">
                    <p class="text-sm leading-6 text-gray-400">
                        <span class="font-semibold text-blue-300">Importante:</span>
                        no necesitas crear un producto de $0 para una actividad gratuita.
                        La inscripción general y las reservas de sesiones se configuran
                        de forma independiente.
                    </p>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                        class="w-full rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400 sm:w-auto">
                        {{ $productosConfigurados ? 'Guardar decisión' : 'Continuar' }}
                    </button>
                </div>
            </form>

            {{-- ===================================================== --}}
            {{-- SI NO OFRECE PRODUCTOS --}}
            {{-- ===================================================== --}}
            @if ($productosConfigurados && !$ofreceProductos)
                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 font-bold text-emerald-400">
                            ✓
                        </div>

                        <div class="min-w-0">
                            <h3 class="font-semibold text-emerald-300">
                                Esta actividad continuará sin productos o servicios
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-gray-400">
                                No tendrás que configurar datos del comprador ni
                                aumentos de precio y costo.
                            </p>

                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                A continuación podrás configurar la programación:
                                una ponencia, varias sesiones, talleres, turnos o
                                simplemente indicar que la actividad no necesita horarios.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <a href="{{ route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'sesiones']) }}"
                            class="w-full rounded-xl bg-emerald-500 px-5 py-3 text-center text-sm font-bold text-white transition hover:bg-emerald-400 sm:w-auto">
                            Continuar con la programación
                        </a>
                    </div>
                </div>
            @endif

            {{-- ===================================================== --}}
            {{-- SI SÍ OFRECE PRODUCTOS --}}
            {{-- ===================================================== --}}
            @if ($ofreceProductos)

                <div class="rounded-2xl border border-white/10 bg-white/[0.03]">
                    <div class="flex flex-col gap-4 border-b border-white/10 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                        <div class="min-w-0">
                            <h2 class="font-semibold text-white">
                                Productos o servicios de la actividad
                            </h2>

                            <p class="mt-1 text-sm leading-6 text-gray-500">
                                Agrega únicamente los elementos que realmente se
                                venderán, solicitarán o reservarán.
                            </p>
                        </div>

                        <button type="button"
                            id="btnNuevoItem"
                            class="w-full shrink-0 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-400 sm:w-auto">
                            + Agregar producto o servicio
                        </button>
                    </div>

                    @if ($actividad->items->isEmpty())

                        <div class="px-4 py-12 text-center sm:px-6">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white/5 text-xl text-gray-500">
                                +
                            </div>

                            <h3 class="mt-4 font-semibold text-gray-300">
                                Todavía no has agregado ningún producto o servicio
                            </h3>

                            <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-gray-600">
                                Como seleccionaste que esta actividad sí ofrece
                                productos o servicios, agrega al menos uno para
                                continuar con su configuración.
                            </p>

                            <button type="button"
                                id="btnNuevoItemVacio"
                                class="mt-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-400 transition hover:bg-emerald-500/15">
                                Agregar el primero
                            </button>
                        </div>

                    @else

                        <div class="divide-y divide-white/5">
                            @foreach ($actividad->items->sortBy('orden') as $item)

                                <div class="p-4 sm:p-5">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="break-words font-semibold text-white">
                                                    {{ $item->nombre }}
                                                </h3>

                                                <span class="rounded-full bg-white/5 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-gray-500">
                                                    {{ $item->tipo }}
                                                </span>
                                            </div>

                                            @if ($item->descripcion)
                                                <p class="mt-2 break-words text-sm leading-6 text-gray-500">
                                                    {{ $item->descripcion }}
                                                </p>
                                            @endif

                                            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">

                                                <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                                    <p class="text-[11px] text-gray-600">
                                                        Precio
                                                    </p>

                                                    <p class="mt-1 font-semibold text-emerald-400">
                                                        ${{ number_format((float) $item->precio, 2) }}
                                                    </p>
                                                </div>

                                                <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                                    <p class="text-[11px] text-gray-600">
                                                        Costo
                                                    </p>

                                                    <p class="mt-1 font-semibold text-gray-300">
                                                        ${{ number_format((float) $item->costo_referencia, 2) }}
                                                    </p>
                                                </div>

                                                <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                                    <p class="text-[11px] text-gray-600">
                                                        Margen
                                                    </p>

                                                    <p class="mt-1 font-semibold text-blue-400">
                                                        ${{ number_format(
                                                            (float) $item->precio - (float) $item->costo_referencia,
                                                            2
                                                        ) }}
                                                    </p>
                                                </div>

                                                <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                                    <p class="text-[11px] text-gray-600">
                                                        Stock
                                                    </p>

                                                    <p class="mt-1 font-semibold text-gray-300">
                                                        {{ is_null($item->stock_total) ? 'Sin límite' : $item->stock_total }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0 gap-2">
                                            <button type="button"
                                                class="btnEditarItem flex h-10 w-10 items-center justify-center rounded-xl border border-blue-500/20 text-blue-400 transition hover:bg-blue-500/10"
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
                                                aria-label="Editar {{ $item->nombre }}">
                                                ✎
                                            </button>

                                            <form action="{{ route('admin.actividades.items.destroy', [$actividad, $item]) }}"
                                                method="POST"
                                                onsubmit="return confirm('¿Seguro que deseas eliminar este elemento?');">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-red-500/20 text-red-400 transition hover:bg-red-500/10"
                                                    aria-label="Eliminar {{ $item->nombre }}">
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

                {{-- AVANCE DESDE PRODUCTOS --}}
                <div class="mt-6">
                    @if ($actividad->items->isNotEmpty())

                        <div class="flex justify-end">
                            <a href="{{ route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'datos']) }}"
                                class="w-full rounded-xl bg-emerald-500 px-5 py-3 text-center text-sm font-bold text-white transition hover:bg-emerald-400 sm:w-auto">
                                Continuar con los datos
                            </a>
                        </div>

                    @else

                        <div class="rounded-xl border border-amber-500/20 bg-amber-500/[0.05] p-4">
                            <p class="text-sm leading-6 text-amber-200">
                                Seleccionaste que esta actividad ofrece productos o
                                servicios. Agrega al menos uno para continuar.
                            </p>
                        </div>

                    @endif
                </div>

            @endif
        @endif

        {{-- ========================================================= --}}
        {{-- PASO 2: DATOS DEL COMPRADOR --}}
        {{-- ========================================================= --}}
        @if ($paso === 'datos')

            @if (!$ofreceProductos)

                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-5">
                    <h2 class="font-semibold text-emerald-300">
                        Este paso no aplica
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-gray-400">
                        Como esta actividad no ofrece productos o servicios,
                        no necesitas configurar datos asociados a una compra.
                    </p>

                    <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                        <a href="{{ route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'productos']) }}"
                            class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400">
                            Volver
                        </a>

                        <a href="{{ route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'sesiones']) }}"
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-center text-sm font-bold text-white">
                            Continuar con la programación
                        </a>
                    </div>
                </div>

            @elseif (!$itemSeleccionado)

                <div class="mb-6 rounded-2xl border border-blue-500/20 bg-blue-500/[0.05] p-4 sm:p-5">
                    <h2 class="font-semibold text-blue-300">
                        ¿Qué información necesitas para cada producto o servicio?
                    </h2>

                    <p class="mt-1 text-sm leading-6 text-gray-400">
                        Configura únicamente los datos necesarios para completar
                        correctamente la compra, solicitud o reserva.
                    </p>

                    <div class="mt-4 rounded-xl border border-white/5 bg-black/10 p-4">
                        <p class="text-xs leading-5 text-gray-500">
                            Ejemplos: talla y color para una camiseta; punto de
                            abordaje para una excursión; nombre que irá impreso en
                            un producto personalizado.
                        </p>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach ($actividad->items->sortBy('orden') as $item)
                        @php
                            $config = $configuracionesDatos->get(
                                (string) $item->id_item_actividad
                            );
                        @endphp

                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="min-w-0">
                                    <h3 class="break-words font-semibold text-white">
                                        {{ $item->nombre }}
                                    </h3>

                                    <div class="mt-3">
                                        @if (!$config)

                                            <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                                                Configuración pendiente
                                            </span>

                                        @elseif (!($config['enabled'] ?? false))

                                            <span class="rounded-full bg-gray-500/10 px-2.5 py-1 text-xs font-semibold text-gray-400">
                                                No requiere datos adicionales
                                            </span>

                                        @else

                                            <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
                                                {{ count($config['fields'] ?? []) }}
                                                dato{{ count($config['fields'] ?? []) === 1 ? '' : 's' }}
                                                configurado{{ count($config['fields'] ?? []) === 1 ? '' : 's' }}
                                            </span>

                                        @endif
                                    </div>
                                </div>

                                <a href="{{ route('admin.actividades.configurar', [
                                        'actividad' => $actividad->id_actividad,
                                        'paso' => 'datos',
                                        'item' => $item->id_item_actividad
                                    ]) }}"
                                    class="w-full rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-2.5 text-center text-sm font-semibold text-blue-300 transition hover:bg-blue-500/15 sm:w-auto">

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
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-center text-sm font-bold text-white transition hover:bg-emerald-400">
                            Continuar con precios
                        </a>

                    @else

                        <div class="rounded-xl border border-amber-500/20 bg-amber-500/[0.05] px-4 py-3 text-center text-sm text-amber-200">
                            Configura cada producto o servicio para continuar.
                        </div>

                    @endif
                </div>

            @else

                @php
                    $configActual = $configDatosSeleccionado ?? null;

                    $configCodificada = base64_encode(
                        json_encode(
                            $configActual,
                            JSON_UNESCAPED_UNICODE
                        )
                    );
                @endphp

                <div class="mb-5 rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-400">
                        Configurando datos
                    </p>

                    <h2 class="mt-1 break-words text-lg font-semibold text-white">
                        {{ $itemSeleccionado->nombre }}
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-gray-500">
                        Decide si este producto o servicio necesita información
                        adicional del comprador.
                    </p>
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

                        <label class="cursor-pointer rounded-xl border border-white/10 p-4 transition hover:bg-white/[0.03]">
                            <div class="flex gap-3">

                                <input type="radio"
                                    name="requiere_datos"
                                    value="0"
                                    required
                                    class="mt-1"
                                    @checked(
                                        $configActual
                                        && !($configActual['enabled'] ?? false)
                                    )>

                                <div class="min-w-0">
                                    <p class="font-semibold text-white">
                                        No
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-gray-500">
                                        La persona puede comprarlo, solicitarlo o
                                        reservarlo tal como está.
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
                                    class="mt-1"
                                    @checked(
                                        $configActual
                                        && ($configActual['enabled'] ?? false)
                                    )>

                                <div class="min-w-0">
                                    <p class="font-semibold text-blue-200">
                                        Sí
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-gray-500">
                                        Necesito solicitar una o varias opciones o
                                        datos antes de completar la solicitud.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>

                    <div id="seccionCamposPedido" class="mt-7 hidden">

                        <div class="rounded-xl border border-blue-500/15 bg-blue-500/[0.04] p-4">
                            <p class="text-sm leading-6 text-gray-400">
                                Primero define qué debe elegir o escribir la persona.
                                Después, en el siguiente paso, podrás indicar si alguna
                                de esas opciones aumenta el precio o el costo.
                            </p>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(300px,.9fr)]">

                            <div class="min-w-0">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                                    <div>
                                        <h3 class="font-semibold text-white">
                                            Datos solicitados
                                        </h3>

                                        <p class="mt-1 text-xs leading-5 text-gray-500">
                                            Agrega únicamente lo necesario.
                                        </p>
                                    </div>

                                    <button type="button"
                                        id="btnAgregarCampo"
                                        class="rounded-xl border border-blue-500/20 bg-blue-500/10 px-4 py-2.5 text-sm font-semibold text-blue-300">
                                        + Agregar dato
                                    </button>
                                </div>

                                <div id="contenedorCampos"
                                    class="mt-4 space-y-4">
                                </div>
                            </div>

                            <div class="min-w-0">
                                <div class="sticky top-4 rounded-2xl border border-white/10 bg-black/10 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Vista previa
                                    </p>

                                    <p class="mt-1 text-sm text-gray-400">
                                        Así se verán los datos que deberá completar
                                        la persona.
                                    </p>

                                    <div id="vistaPreviaCampos"
                                        class="mt-5 space-y-4">
                                    </div>
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
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                            Guardar configuración
                        </button>
                    </div>
                </form>

            @endif
        @endif
                {{-- ========================================================= --}}
        {{-- PASO 3: AUMENTOS DE PRECIO Y COSTO --}}
        {{-- ========================================================= --}}
        @if ($paso === 'precios')

            @if (!$ofreceProductos)

                {{-- ================================================= --}}
                {{-- NO APLICA CUANDO NO HAY PRODUCTOS/SERVICIOS --}}
                {{-- ================================================= --}}
                <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/[0.05] p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 font-bold text-emerald-400">
                            ✓
                        </div>

                        <div class="min-w-0">
                            <h2 class="font-semibold text-emerald-300">
                                Este paso no aplica
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-gray-400">
                                Como esta actividad no ofrece productos o servicios,
                                no necesitas configurar precios, costos ni aumentos.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                        <a href="{{ route('admin.actividades.configurar', [
                                'actividad' => $actividad->id_actividad,
                                'paso' => 'productos'
                            ]) }}"
                            class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400">
                            Volver
                        </a>

                        <a href="{{ route('admin.actividades.configurar', [
                                'actividad' => $actividad->id_actividad,
                                'paso' => 'sesiones'
                            ]) }}"
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-center text-sm font-bold text-white transition hover:bg-emerald-400">
                            Continuar con la programación
                        </a>
                    </div>
                </div>

            @elseif (!$itemSeleccionado)

                {{-- ================================================= --}}
                {{-- LISTADO DE PRODUCTOS/SERVICIOS --}}
                {{-- ================================================= --}}
                <div class="mb-6 rounded-2xl border border-violet-500/20 bg-violet-500/[0.05] p-4 sm:p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-500/10 font-bold text-violet-300">
                            $
                        </div>

                        <div class="min-w-0">
                            <h2 class="font-semibold text-violet-300">
                                ¿Alguna opción aumenta el precio o el costo?
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-gray-400">
                                Configura solamente las opciones que generan un
                                aumento sobre el precio o costo base.
                            </p>

                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                Por ejemplo, si una camiseta cuesta $10 y la talla
                                3XL aumenta $3, solamente debes indicar ese aumento.
                                SIDAN calculará automáticamente el precio final.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach ($actividad->items->sortBy('orden') as $item)

                        @php
                            $configDatos = $configuracionesDatos->get(
                                (string) $item->id_item_actividad
                            );

                            $camposLista = collect(
                                $configDatos['fields'] ?? []
                            )->filter(
                                fn ($campo) =>
                                    ($campo['type'] ?? null) === 'lista'
                                    && !empty($campo['options'] ?? [])
                            );

                            $configPrecio = $configuracionesPrecios->get(
                                (string) $item->id_item_actividad
                            );

                            $cantidadReglasPrecio = count(
                                $configPrecio['rules'] ?? []
                            );
                        @endphp

                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                                <div class="min-w-0">
                                    <h3 class="break-words font-semibold text-white">
                                        {{ $item->nombre }}
                                    </h3>

                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                                        <span class="text-gray-500">
                                            Precio base:
                                            <span class="font-semibold text-emerald-400">
                                                ${{ number_format((float) $item->precio, 2) }}
                                            </span>
                                        </span>

                                        <span class="text-gray-500">
                                            Costo base:
                                            <span class="font-semibold text-gray-300">
                                                ${{ number_format((float) $item->costo_referencia, 2) }}
                                            </span>
                                        </span>
                                    </div>

                                    <div class="mt-3">
                                        @if (!$configDatos)

                                            <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                                                Primero configura los datos
                                            </span>

                                        @elseif (!($configDatos['enabled'] ?? false))

                                            <span class="rounded-full bg-gray-500/10 px-2.5 py-1 text-xs font-semibold text-gray-400">
                                                No requiere aumentos
                                            </span>

                                        @elseif ($camposLista->isEmpty())

                                            <span class="rounded-full bg-gray-500/10 px-2.5 py-1 text-xs font-semibold text-gray-400">
                                                No aplica
                                            </span>

                                        @elseif (!$configPrecio)

                                            <span class="rounded-full bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                                                Configuración pendiente
                                            </span>

                                        @elseif (!($configPrecio['enabled'] ?? false))

                                            <span class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400">
                                                Sin aumentos
                                            </span>

                                        @else

                                            <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-xs font-semibold text-violet-300">
                                                {{ $cantidadReglasPrecio }}
                                                aumento{{ $cantidadReglasPrecio === 1 ? '' : 's' }}
                                                configurado{{ $cantidadReglasPrecio === 1 ? '' : 's' }}
                                            </span>

                                        @endif
                                    </div>
                                </div>

                                @if (
                                    $configDatos
                                    && ($configDatos['enabled'] ?? false)
                                    && $camposLista->isNotEmpty()
                                )

                                    <a href="{{ route('admin.actividades.configurar', [
                                            'actividad' => $actividad->id_actividad,
                                            'paso' => 'precios',
                                            'item' => $item->id_item_actividad
                                        ]) }}"
                                        class="w-full shrink-0 rounded-xl border border-violet-500/20 bg-violet-500/10 px-4 py-2.5 text-center text-sm font-semibold text-violet-300 transition hover:bg-violet-500/15 sm:w-auto">

                                        {{ $configPrecio ? 'Editar aumentos' : 'Configurar aumentos' }}
                                    </a>

                                @endif
                            </div>
                        </div>

                    @endforeach
                </div>

                {{-- ================================================= --}}
                {{-- NAVEGACIÓN --}}
                {{-- ================================================= --}}
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
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-center text-sm font-bold text-white transition hover:bg-emerald-400">
                            Continuar con la programación
                        </a>

                    @else

                        <div class="rounded-xl border border-amber-500/20 bg-amber-500/[0.05] px-4 py-3 text-center text-sm text-amber-200">
                            Termina las configuraciones pendientes para continuar.
                        </div>

                    @endif
                </div>

            @else

                {{-- ================================================= --}}
                {{-- CONFIGURACIÓN DE UN PRODUCTO/SERVICIO --}}
                {{-- ================================================= --}}
                @php
                    $configDatos = $configDatosSeleccionado;

                    $camposLista = collect(
                        $configDatos['fields'] ?? []
                    )->filter(
                        fn ($campo) =>
                            ($campo['type'] ?? null) === 'lista'
                            && !empty($campo['options'] ?? [])
                    )->values();

                    $configPrecio = $configPrecioSeleccionado;

                    $datosPrecioCodificados = base64_encode(
                        json_encode(
                            [
                                'config' => $configPrecio,
                                'campos' => $camposLista->values()->all(),
                            ],
                            JSON_UNESCAPED_UNICODE
                        )
                    );
                @endphp

                {{-- ================================================= --}}
                {{-- RESUMEN DEL PRODUCTO --}}
                {{-- ================================================= --}}
                <div class="mb-5 rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-400">
                        Configurando aumentos
                    </p>

                    <h2 class="mt-1 break-words text-lg font-semibold text-white">
                        {{ $itemSeleccionado->nombre }}
                    </h2>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-lg border border-emerald-500/10 bg-emerald-500/[0.05] px-3 py-1.5 text-xs text-gray-400">
                            Precio base:
                            <strong class="text-emerald-400">
                                ${{ number_format((float) $itemSeleccionado->precio, 2) }}
                            </strong>
                        </span>

                        <span class="rounded-lg border border-white/5 bg-black/10 px-3 py-1.5 text-xs text-gray-400">
                            Costo base:
                            <strong class="text-gray-300">
                                ${{ number_format((float) $itemSeleccionado->costo_referencia, 2) }}
                            </strong>
                        </span>
                    </div>
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

                    {{-- ================================================= --}}
                    {{-- PREGUNTA PRINCIPAL --}}
                    {{-- ================================================= --}}
                    <div>
                        <h3 class="font-semibold text-white">
                            ¿Alguna opción aumenta el precio o el costo?
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-gray-500">
                            Si ninguna opción cambia los valores base, selecciona
                            No y SIDAN utilizará siempre el precio y costo originales.
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">

                        {{-- NO --}}
                        <label class="cursor-pointer rounded-xl border border-white/10 p-4 transition hover:bg-white/[0.03]">
                            <div class="flex gap-3">
                                <input type="radio"
                                    name="tiene_cambios"
                                    value="0"
                                    required
                                    class="mt-1"
                                    @checked(
                                        $configPrecio
                                        && !($configPrecio['enabled'] ?? false)
                                    )>

                                <div class="min-w-0">
                                    <p class="font-semibold text-white">
                                        No
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-gray-500">
                                        Todas las opciones mantienen el precio y
                                        costo base.
                                    </p>
                                </div>
                            </div>
                        </label>

                        {{-- SÍ --}}
                        <label class="cursor-pointer rounded-xl border border-violet-500/20 bg-violet-500/[0.04] p-4">
                            <div class="flex gap-3">
                                <input type="radio"
                                    name="tiene_cambios"
                                    value="1"
                                    required
                                    class="mt-1"
                                    @checked(
                                        $configPrecio
                                        && ($configPrecio['enabled'] ?? false)
                                    )>

                                <div class="min-w-0">
                                    <p class="font-semibold text-violet-200">
                                        Sí
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-gray-500">
                                        Una o varias opciones agregan un monto
                                        adicional.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- ================================================= --}}
                    {{-- CONFIGURACIÓN DE AUMENTOS --}}
                    {{-- ================================================= --}}
                    <div id="seccionAjustesPrecio" class="mt-7 hidden">

                        {{-- DATOS QUE PUEDEN MODIFICAR PRECIO --}}
                        <div>
                            <h3 class="font-semibold text-white">
                                ¿Qué datos pueden generar un aumento?
                            </h3>

                            <p class="mt-1 text-sm leading-6 text-gray-500">
                                Selecciona las características que pueden cambiar
                                el precio o costo. Puedes elegir más de una.
                            </p>

                            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach ($camposLista as $campo)

                                    <label class="flex cursor-pointer gap-3 rounded-xl border border-white/10 bg-black/10 p-4 transition hover:border-violet-500/20">

                                        <input type="checkbox"
                                            name="campos_clave[]"
                                            value="{{ $campo['key'] }}"
                                            class="campoPrecioCheckbox mt-1"
                                            @checked(
                                                in_array(
                                                    $campo['key'],
                                                    $configPrecio['pricing_fields'] ?? [],
                                                    true
                                                )
                                            )>

                                        <div class="min-w-0">
                                            <p class="break-words font-medium text-white">
                                                {{ $campo['label'] }}
                                            </p>

                                            <p class="mt-1 text-xs text-gray-500">
                                                {{ count($campo['options'] ?? []) }}
                                                opción{{ count($campo['options'] ?? []) === 1 ? '' : 'es' }}
                                            </p>
                                        </div>
                                    </label>

                                @endforeach
                            </div>
                        </div>

                        {{-- ================================================= --}}
                        {{-- REGLAS DE AUMENTO --}}
                        {{-- ================================================= --}}
                        <div class="mt-7 border-t border-white/10 pt-6">

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-white">
                                        Opciones que generan un aumento
                                    </h3>

                                    <p class="mt-1 text-xs leading-5 text-gray-500">
                                        Agrega únicamente las opciones que modifican
                                        el precio, el costo o ambos.
                                    </p>
                                </div>

                                <button type="button"
                                    id="btnAgregarAjuste"
                                    class="w-full shrink-0 rounded-xl border border-violet-500/20 bg-violet-500/10 px-4 py-2.5 text-sm font-semibold text-violet-300 transition hover:bg-violet-500/15 sm:w-auto">
                                    + Agregar aumento
                                </button>
                            </div>

                            {{-- ============================================= --}}
                            {{-- EJEMPLO --}}
                            {{-- ============================================= --}}
                            <div class="mt-4 rounded-xl border border-blue-500/15 bg-blue-500/[0.04] p-4">
                                <p class="text-xs font-semibold text-blue-300">
                                    Ejemplo
                                </p>

                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                    <span class="rounded-lg bg-white/5 px-2.5 py-1.5 text-gray-300">
                                        Talla
                                    </span>

                                    <span class="text-gray-600">
                                        →
                                    </span>

                                    <span class="rounded-lg bg-white/5 px-2.5 py-1.5 text-gray-300">
                                        3XL
                                    </span>

                                    <span class="rounded-lg bg-emerald-500/10 px-2.5 py-1.5 text-emerald-300">
                                        +$3 precio
                                    </span>

                                    <span class="rounded-lg bg-blue-500/10 px-2.5 py-1.5 text-blue-300">
                                        +$2 costo
                                    </span>
                                </div>

                                <p class="mt-2 text-xs leading-5 text-gray-500">
                                    Si además el color negro aumenta $2, SIDAN
                                    sumará ambos aumentos cuando el comprador
                                    seleccione 3XL + Negro.
                                </p>
                            </div>

                            {{-- ============================================= --}}
                            {{-- AQUÍ JS GENERA LAS REGLAS --}}
                            {{-- ============================================= --}}
                            <div id="contenedorAjustesPrecio"
                                class="mt-4 space-y-3">
                            </div>

                            <p id="mensajeSinOpcionesAjuste"
                                class="mt-4 hidden rounded-xl border border-emerald-500/15 bg-emerald-500/[0.05] p-3 text-xs leading-5 text-emerald-300">
                                Ya configuraste todas las opciones disponibles
                                de los datos seleccionados.
                            </p>

                            {{-- ============================================= --}}
                            {{-- AYUDA SOBRE LAS REGLAS CONTRAÍDAS --}}
                            {{-- ============================================= --}}
                            <div class="mt-4 flex items-start gap-2 rounded-xl border border-white/5 bg-black/10 p-3">
                                <span class="mt-0.5 text-gray-600">
                                    ⌄
                                </span>

                                <p class="text-xs leading-5 text-gray-500">
                                    Cuando termines una regla podrás contraerla.
                                    Así, aunque tengas muchas tallas, colores u
                                    opciones, la pantalla seguirá siendo compacta.
                                </p>
                            </div>
                        </div>

                        {{-- ================================================= --}}
                        {{-- EXPLICACIÓN DEL CÁLCULO --}}
                        {{-- ================================================= --}}
                        <div class="mt-5 rounded-xl border border-violet-500/15 bg-violet-500/[0.04] p-4">
                            <p class="text-xs font-semibold text-violet-300">
                                ¿Cómo calculará SIDAN el precio?
                            </p>

                            <p class="mt-2 text-xs leading-5 text-gray-400">
                                SIDAN tomará el precio y costo base del producto
                                y sumará los aumentos correspondientes a todas las
                                opciones seleccionadas por el comprador.
                            </p>

                            <div class="mt-3 overflow-x-auto">
                                <div class="min-w-[420px] rounded-lg border border-white/5 bg-black/10 p-3">
                                    <div class="flex items-center gap-2 text-xs text-gray-400">
                                        <span>
                                            Precio base
                                        </span>

                                        <span class="text-gray-600">
                                            +
                                        </span>

                                        <span>
                                            aumento talla
                                        </span>

                                        <span class="text-gray-600">
                                            +
                                        </span>

                                        <span>
                                            aumento color
                                        </span>

                                        <span class="text-gray-600">
                                            =
                                        </span>

                                        <span class="font-semibold text-emerald-400">
                                            precio final
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ================================================= --}}
                    {{-- NAVEGACIÓN --}}
                    {{-- ================================================= --}}
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">

                        <a href="{{ route('admin.actividades.configurar', [
                                'actividad' => $actividad->id_actividad,
                                'paso' => 'precios'
                            ]) }}"
                            class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                            Guardar aumentos
                        </button>
                    </div>
                </form>

            @endif
        @endif
                {{-- ========================================================= --}}
        {{-- PASO 4: PROGRAMACIÓN / SESIONES --}}
        {{-- ========================================================= --}}
        @if ($paso === 'sesiones')

            {{-- ===================================================== --}}
            {{-- INTRODUCCIÓN --}}
            {{-- ===================================================== --}}
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
                            Indica si la actividad no necesita horarios, si se realizará
                            en una sola sesión o si tendrá varias sesiones, talleres,
                            turnos o actividades internas.
                        </p>

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                <p class="text-xs font-semibold text-gray-300">
                                    Sin sesiones
                                </p>

                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Ventas, campañas, donaciones o actividades que no
                                    necesitan un horario específico.
                                </p>
                            </div>

                            <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                <p class="text-xs font-semibold text-gray-300">
                                    Una sola sesión
                                </p>

                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Una ponencia, excursión, capacitación o evento que
                                    ocurre una sola vez.
                                </p>
                            </div>

                            <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                <p class="text-xs font-semibold text-gray-300">
                                    Varias sesiones
                                </p>

                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Congresos, semanas académicas, talleres, jornadas
                                    o actividades con varios horarios.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================================================== --}}
            {{-- PERÍODO GENERAL --}}
            {{-- ===================================================== --}}
            @if ($realizacionDesde && $realizacionHasta)

                <div class="mb-6 rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Período general de realización
                            </p>

                            <h3 class="mt-1 font-semibold text-white">
                                {{ $realizacionDesdeTexto }}
                                <span class="mx-1 text-gray-600">→</span>
                                {{ $realizacionHastaTexto }}
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-gray-500">
                                Las sesiones que agregues deberán realizarse dentro
                                de este período.
                            </p>
                        </div>

                        <span class="shrink-0 rounded-full border border-emerald-500/15 bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-400">
                            Período definido
                        </span>
                    </div>
                </div>

            @else

                <div class="mb-6 rounded-2xl border border-amber-500/20 bg-amber-500/[0.06] p-4 sm:p-5">
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 font-bold text-amber-400">
                            !
                        </div>

                        <div class="min-w-0">
                            <h3 class="font-semibold text-amber-300">
                                No se ha definido el período de realización
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-gray-400">
                                Para utilizar una o varias sesiones, la actividad
                                necesita tener una fecha y hora general de inicio y
                                finalización.
                            </p>

                            <p class="mt-2 text-xs leading-5 text-gray-500">
                                Mientras no exista ese período, solamente podrás
                                indicar que esta actividad no necesita sesiones.
                            </p>
                        </div>
                    </div>
                </div>

            @endif

            {{-- ===================================================== --}}
            {{-- INFORMACIÓN DEL CUPO GENERAL --}}
            {{-- ===================================================== --}}
            <div class="mb-6 rounded-2xl border border-white/10 bg-black/10 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/5 text-gray-400">
                        #
                    </div>

                    <div class="min-w-0">
                        <p class="font-semibold text-gray-300">
                            Cupo e inscripción general
                        </p>

                        @if ($tieneInscripcionGeneral && !is_null($cupoGeneral))

                            <p class="mt-1 text-sm leading-6 text-gray-500">
                                Esta actividad tiene inscripción general con un
                                máximo de
                                <strong class="text-white">
                                    {{ $cupoGeneral }} personas
                                </strong>.
                            </p>

                            <p class="mt-1 text-xs leading-5 text-gray-600">
                                Una sesión única heredará automáticamente ese cupo.
                                Si utilizas varias sesiones, ninguna podrá superar
                                las {{ $cupoGeneral }} personas.
                            </p>

                        @elseif ($tieneInscripcionGeneral)

                            <p class="mt-1 text-sm leading-6 text-gray-500">
                                Esta actividad tiene inscripción general, pero no
                                tiene un límite general de personas.
                            </p>

                            <p class="mt-1 text-xs leading-5 text-gray-600">
                                En varias sesiones podrás establecer un cupo
                                específico para cada una o dejarlas sin límite.
                            </p>

                        @else

                            <p class="mt-1 text-sm leading-6 text-gray-500">
                                Esta actividad no utiliza inscripción general.
                            </p>

                            <p class="mt-1 text-xs leading-5 text-gray-600">
                                Aun así, una sesión específica puede requerir reserva
                                y tener su propio cupo. Por ejemplo, una Semana de
                                Sistemas abierta puede incluir talleres de 20 cupos.
                            </p>

                        @endif
                    </div>
                </div>
            </div>

            {{-- ===================================================== --}}
            {{-- FORMULARIO DE SESIONES --}}
            {{-- ===================================================== --}}
            <form id="formSesiones"
                action="{{ route('admin.actividades.sesiones.configuracion', $actividad) }}"
                method="POST"
                class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-6">

                @csrf

                {{-- Datos que utilizará JavaScript --}}
                <input type="hidden"
                    id="sesionesActuales"
                    value="{{ $sesionesCodificadas }}">

                <input type="hidden"
                    id="realizacionDesdeActividad"
                    value="{{ $realizacionDesde }}">

                <input type="hidden"
                    id="realizacionHastaActividad"
                    value="{{ $realizacionHasta }}">

                <input type="hidden"
                    id="cupoGeneralActividad"
                    value="{{ is_null($cupoGeneral) ? '' : $cupoGeneral }}">

                <input type="hidden"
                    id="actividadTieneInscripcion"
                    value="{{ $tieneInscripcionGeneral ? '1' : '0' }}">

                {{-- ================================================= --}}
                {{-- MODO DE PROGRAMACIÓN --}}
                {{-- ================================================= --}}
                <div>
                    <h3 class="font-semibold text-white">
                        Selecciona cómo se desarrollará
                    </h3>

                    <p class="mt-1 text-sm leading-6 text-gray-500">
                        Elige la opción que mejor represente esta actividad.
                    </p>

                    <div class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-3">

                        {{-- ========================================= --}}
                        {{-- SIN SESIONES --}}
                        {{-- ========================================= --}}
                        <label class="cursor-pointer rounded-2xl border p-4 transition sm:p-5 {{ $modoSesiones === 'ninguna' ? 'border-cyan-500/40 bg-cyan-500/[0.08]' : 'border-white/10 bg-black/10 hover:border-white/20' }}">
                            <div class="flex items-start gap-3">
                                <input type="radio"
                                    name="modo_sesiones"
                                    value="ninguna"
                                    required
                                    class="modoSesion mt-1 shrink-0"
                                    @checked(old('modo_sesiones', $modoSesiones) === 'ninguna')>

                                <div class="min-w-0">
                                    <p class="font-semibold text-white">
                                        Sin sesiones u horarios
                                    </p>

                                    <p class="mt-2 text-xs leading-5 text-gray-500">
                                        La actividad no necesita indicar una fecha,
                                        turno o sesión específica.
                                    </p>

                                    <div class="mt-3 rounded-lg border border-white/5 bg-black/10 p-2.5">
                                        <p class="text-[11px] leading-5 text-gray-600">
                                            Ejemplo: venta de camisetas, campaña,
                                            donación o publicación informativa.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- ========================================= --}}
                        {{-- UNA SESIÓN --}}
                        {{-- ========================================= --}}
                        <label class="rounded-2xl border p-4 transition sm:p-5 {{ $realizacionDesde && $realizacionHasta ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }} {{ $modoSesiones === 'unica' ? 'border-cyan-500/40 bg-cyan-500/[0.08]' : 'border-white/10 bg-black/10' }}">
                            <div class="flex items-start gap-3">
                                <input type="radio"
                                    name="modo_sesiones"
                                    value="unica"
                                    required
                                    class="modoSesion mt-1 shrink-0"
                                    @checked(old('modo_sesiones', $modoSesiones) === 'unica')
                                    @disabled(!$realizacionDesde || !$realizacionHasta)>

                                <div class="min-w-0">
                                    <p class="font-semibold text-white">
                                        Una sola sesión
                                    </p>

                                    <p class="mt-2 text-xs leading-5 text-gray-500">
                                        SIDAN creará automáticamente una
                                        <strong class="text-gray-400">
                                            Sesión principal
                                        </strong>
                                        usando el período general de realización.
                                    </p>

                                    <div class="mt-3 rounded-lg border border-white/5 bg-black/10 p-2.5">
                                        <p class="text-[11px] leading-5 text-gray-600">
                                            Ejemplo: una ponencia, una excursión,
                                            una capacitación o una reunión.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- ========================================= --}}
                        {{-- VARIAS SESIONES --}}
                        {{-- ========================================= --}}
                        <label class="rounded-2xl border p-4 transition sm:p-5 {{ $realizacionDesde && $realizacionHasta ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }} {{ $modoSesiones === 'multiples' ? 'border-cyan-500/40 bg-cyan-500/[0.08]' : 'border-white/10 bg-black/10' }}">
                            <div class="flex items-start gap-3">
                                <input type="radio"
                                    name="modo_sesiones"
                                    value="multiples"
                                    required
                                    class="modoSesion mt-1 shrink-0"
                                    @checked(old('modo_sesiones', $modoSesiones) === 'multiples')
                                    @disabled(!$realizacionDesde || !$realizacionHasta)>

                                <div class="min-w-0">
                                    <p class="font-semibold text-white">
                                        Varias sesiones
                                    </p>

                                    <p class="mt-2 text-xs leading-5 text-gray-500">
                                        Agrega dos o más sesiones, talleres, turnos
                                        o actividades con horarios independientes.
                                    </p>

                                    <div class="mt-3 rounded-lg border border-white/5 bg-black/10 p-2.5">
                                        <p class="text-[11px] leading-5 text-gray-600">
                                            Ejemplo: congreso, Semana de Sistemas,
                                            jornadas o talleres simultáneos.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- ================================================= --}}
                {{-- OPCIÓN: SIN SESIONES --}}
                {{-- ================================================= --}}
                <div id="seccionSinSesiones" class="mt-7 hidden">
                    <div class="rounded-2xl border border-gray-500/15 bg-black/10 p-5">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/5 text-gray-400">
                                ✓
                            </div>

                            <div class="min-w-0">
                                <h3 class="font-semibold text-gray-300">
                                    No se crearán sesiones
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-gray-500">
                                    La actividad podrá seguir utilizando productos,
                                    servicios o inscripción general si corresponde,
                                    pero no tendrá horarios o sesiones específicas.
                                </p>

                                @if ($actividad->sesiones->isNotEmpty())
                                    <div class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.05] p-3">
                                        <p class="text-xs leading-5 text-amber-200">
                                            Esta actividad actualmente tiene
                                            {{ $actividad->sesiones->count() }}
                                            sesión{{ $actividad->sesiones->count() === 1 ? '' : 'es' }}.
                                            Al guardar esta opción, esas sesiones
                                            serán eliminadas.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================================================= --}}
                {{-- OPCIÓN: UNA SOLA SESIÓN --}}
                {{-- ================================================= --}}
                <div id="seccionSesionUnica" class="mt-7 hidden">

                    <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/[0.04] p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300">
                                1
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wide text-cyan-400">
                                            Sesión automática
                                        </p>

                                        <h3 class="mt-1 font-semibold text-white">
                                            Sesión principal
                                        </h3>
                                    </div>

                                    <span class="w-fit rounded-full bg-cyan-500/10 px-2.5 py-1 text-xs font-semibold text-cyan-300">
                                        No necesitas crearla manualmente
                                    </span>
                                </div>

                                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">

                                    <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                                            Inicio
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-gray-300">
                                            {{ $realizacionDesdeTexto ?? 'Sin definir' }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-white/5 bg-black/10 p-3">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">
                                            Finalización
                                        </p>

                                        <p class="mt-1 text-sm font-medium text-gray-300">
                                            {{ $realizacionHastaTexto ?? 'Sin definir' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- ================================= --}}
                                {{-- CUPO DE SESIÓN ÚNICA --}}
                                {{-- ================================= --}}
                                <div class="mt-4 rounded-xl border border-white/5 bg-black/10 p-4">
                                    @if ($tieneInscripcionGeneral && !is_null($cupoGeneral))

                                        <p class="text-sm font-semibold text-gray-300">
                                            Cupo de la sesión:
                                            <span class="text-emerald-400">
                                                {{ $cupoGeneral }} personas
                                            </span>
                                        </p>

                                        <p class="mt-1 text-xs leading-5 text-gray-600">
                                            Se utilizará automáticamente el mismo
                                            cupo de la inscripción general.
                                        </p>

                                    @elseif ($tieneInscripcionGeneral)

                                        <p class="text-sm font-semibold text-gray-300">
                                            Sesión sin límite específico
                                        </p>

                                        <p class="mt-1 text-xs leading-5 text-gray-600">
                                            Como la inscripción general no tiene un
                                            límite de cupos, la sesión principal
                                            tampoco tendrá uno.
                                        </p>

                                    @else

                                        <p class="text-sm font-semibold text-gray-300">
                                            No requiere una reserva adicional
                                        </p>

                                        <p class="mt-1 text-xs leading-5 text-gray-600">
                                            La sesión representa directamente la
                                            realización de la actividad y no se
                                            creará una segunda inscripción para ella.
                                        </p>

                                    @endif
                                </div>

                                <div class="mt-4 rounded-xl border border-blue-500/15 bg-blue-500/[0.04] p-4">
                                    <p class="text-xs leading-5 text-gray-400">
                                        Si la actividad utiliza inscripción general,
                                        la persona se inscribe a la actividad. No
                                        tendrá que volver a reservar la única sesión.
                                    </p>
                                </div>

                                @if ($actividad->sesiones->count() > 1)
                                    <div class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/[0.05] p-3">
                                        <p class="text-xs leading-5 text-amber-200">
                                            Actualmente existen varias sesiones.
                                            Al guardar el modo de sesión única,
                                            SIDAN conservará una como Sesión principal
                                            y eliminará las demás.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================================================= --}}
                {{-- OPCIÓN: VARIAS SESIONES --}}
                {{-- ================================================= --}}
                <div id="seccionSesionesMultiples" class="mt-7 hidden">

                    {{-- ============================================= --}}
                    {{-- EXPLICACIÓN --}}
                    {{-- ============================================= --}}
                    <div class="mb-5 rounded-xl border border-cyan-500/15 bg-cyan-500/[0.04] p-4">
                        <p class="text-sm font-semibold text-cyan-200">
                            Configura cada sesión por separado
                        </p>

                        <p class="mt-2 text-xs leading-5 text-gray-500">
                            Cada sesión puede tener su propio nombre, fecha, horario,
                            ubicación, enlace, cupo y configuración de reserva.
                        </p>

                        @if (!is_null($cupoGeneral) && $tieneInscripcionGeneral)

                            <p class="mt-2 text-xs leading-5 text-gray-500">
                                Si dejas vacío el cupo de una sesión, SIDAN utilizará
                                automáticamente el cupo general de
                                <strong class="text-gray-300">
                                    {{ $cupoGeneral }} personas
                                </strong>.
                                También puedes colocar un cupo menor, pero nunca uno mayor.
                            </p>

                        @else

                            <p class="mt-2 text-xs leading-5 text-gray-500">
                                Si dejas vacío el cupo de una sesión, se considerará
                                sin límite específico.
                            </p>

                        @endif
                    </div>

                    {{-- ============================================= --}}
                    {{-- CABECERA --}}
                    {{-- ============================================= --}}
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-white">
                                Sesiones de la actividad
                            </h3>

                            <p class="mt-1 text-xs leading-5 text-gray-500">
                                Necesitas al menos dos sesiones para utilizar este modo.
                            </p>
                        </div>

                        <button type="button"
                            id="btnAgregarSesion"
                            class="w-full shrink-0 rounded-xl border border-cyan-500/20 bg-cyan-500/10 px-4 py-2.5 text-sm font-semibold text-cyan-300 transition hover:bg-cyan-500/15 sm:w-auto">
                            + Agregar sesión
                        </button>
                    </div>

                    {{-- ============================================= --}}
                    {{-- LÍMITES DE FECHAS --}}
                    {{-- ============================================= --}}
                    @if ($realizacionDesde && $realizacionHasta)

                        <div class="mb-4 flex flex-col gap-2 rounded-xl border border-white/5 bg-black/10 p-3 text-xs text-gray-500 sm:flex-row sm:items-center sm:justify-between">
                            <span>
                                Desde:
                                <strong class="text-gray-300">
                                    {{ $realizacionDesdeTexto }}
                                </strong>
                            </span>

                            <span>
                                Hasta:
                                <strong class="text-gray-300">
                                    {{ $realizacionHastaTexto }}
                                </strong>
                            </span>
                        </div>

                    @endif

                    {{-- ============================================= --}}
                    {{-- CONTENEDOR DINÁMICO --}}
                    {{-- ============================================= --}}
                    <div id="contenedorSesiones"
                        class="space-y-3">
                    </div>

                    {{-- ============================================= --}}
                    {{-- MENSAJE CUANDO NO HAY SESIONES --}}
                    {{-- ============================================= --}}
                    <div id="mensajeSinSesiones"
                        class="rounded-2xl border border-dashed border-white/10 bg-black/10 px-4 py-8 text-center">

                        <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-2xl bg-white/5 text-gray-500">
                            +
                        </div>

                        <p class="mt-3 text-sm font-semibold text-gray-400">
                            Todavía no hay sesiones
                        </p>

                        <p class="mx-auto mt-1 max-w-md text-xs leading-5 text-gray-600">
                            Agrega la primera y después podrás crear todas las que
                            necesite la actividad.
                        </p>
                    </div>

                    {{-- ============================================= --}}
                    {{-- ADVERTENCIA DE SOLAPAMIENTOS --}}
                    {{-- ============================================= --}}
                    <div id="advertenciaSolapamientos"
                        class="mt-5 hidden rounded-2xl border border-amber-500/20 bg-amber-500/[0.06] p-4">

                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 font-bold text-amber-400">
                                !
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-amber-300">
                                    Hay sesiones que coinciden en horario
                                </p>

                                <p class="mt-2 text-xs leading-5 text-gray-400">
                                    Esto puede ser completamente válido. Por ejemplo,
                                    dos talleres pueden desarrollarse al mismo tiempo
                                    en lugares diferentes.
                                </p>

                                <div id="listaSolapamientos"
                                    class="mt-3 space-y-1 text-xs text-gray-500">
                                </div>

                                <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl border border-amber-500/15 bg-black/10 p-3">
                                    <input type="checkbox"
                                        id="confirmarSolapamientos"
                                        name="confirmar_solapamientos"
                                        value="1"
                                        class="mt-0.5"
                                        @checked(old('confirmar_solapamientos'))>

                                    <span class="text-xs leading-5 text-amber-100">
                                        Sí, estas sesiones se realizarán
                                        simultáneamente.
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ============================================= --}}
                    {{-- VISTA PREVIA --}}
                    {{-- ============================================= --}}
                    <div class="mt-6 rounded-2xl border border-white/10 bg-black/10 p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300">
                                ◷
                            </div>

                            <div class="min-w-0">
                                <h3 class="font-semibold text-white">
                                    Vista previa de la programación
                                </h3>

                                <p class="mt-1 text-xs leading-5 text-gray-600">
                                    Aquí podrás revisar rápidamente todas las
                                    sesiones antes de guardar.
                                </p>
                            </div>
                        </div>

                        <div id="vistaPreviaSesiones"
                            class="mt-5 space-y-3">
                        </div>
                    </div>

                    {{-- ============================================= --}}
                    {{-- AYUDA DE RESERVAS --}}
                    {{-- ============================================= --}}
                    <div class="mt-5 rounded-xl border border-blue-500/15 bg-blue-500/[0.04] p-4">
                        <p class="text-xs font-semibold text-blue-300">
                            Inscripción general y reservas de sesión son cosas diferentes
                        </p>

                        <p class="mt-2 text-xs leading-5 text-gray-400">
                            Puedes tener una actividad abierta sin inscripción general
                            y aun así exigir reserva para determinados talleres.
                        </p>

                        <div class="mt-3 rounded-lg border border-white/5 bg-black/10 p-3">
                            <p class="text-xs text-gray-500">
                                Ejemplo:
                            </p>

                            <div class="mt-2 space-y-1 text-xs text-gray-400">
                                <p>
                                    Semana de Sistemas — abierta para todos
                                </p>

                                <p>
                                    Taller IoT — reserva obligatoria · 20 cupos
                                </p>

                                <p>
                                    Taller de impresión 3D — reserva obligatoria · 15 cupos
                                </p>

                                <p>
                                    Ponencia inaugural — sin reserva
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================================================= --}}
                {{-- ERRORES DEL FORMULARIO --}}
                {{-- ================================================= --}}
                @if (
                    $errors->has('modo_sesiones')
                    || $errors->has('sesiones')
                    || $errors->has('confirmar_solapamientos')
                )

                    <div class="mt-6 rounded-xl border border-red-500/20 bg-red-500/[0.06] p-4">
                        <p class="text-sm font-semibold text-red-300">
                            Revisa la configuración de la programación
                        </p>

                        <div class="mt-2 space-y-1 text-xs leading-5 text-red-200/80">
                            @foreach (
                                $errors->get('modo_sesiones')
                                as $mensaje
                            )
                                <p>
                                    {{ $mensaje }}
                                </p>
                            @endforeach

                            @foreach (
                                $errors->get('sesiones')
                                as $mensaje
                            )
                                <p>
                                    {{ $mensaje }}
                                </p>
                            @endforeach

                            @foreach (
                                $errors->get('confirmar_solapamientos')
                                as $mensaje
                            )
                                <p>
                                    {{ $mensaje }}
                                </p>
                            @endforeach
                        </div>
                    </div>

                @endif

                {{-- ================================================= --}}
                {{-- NAVEGACIÓN --}}
                {{-- ================================================= --}}
                <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

                    @if ($ofreceProductos)

                        <a href="{{ route('admin.actividades.configurar', [
                                'actividad' => $actividad->id_actividad,
                                'paso' => 'precios'
                            ]) }}"
                            class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400 transition hover:bg-white/5">
                            Volver
                        </a>

                    @else

                        <a href="{{ route('admin.actividades.configurar', [
                                'actividad' => $actividad->id_actividad,
                                'paso' => 'productos'
                            ]) }}"
                            class="rounded-xl border border-white/10 px-5 py-3 text-center text-sm font-semibold text-gray-400 transition hover:bg-white/5">
                            Volver
                        </a>

                    @endif

                    <button type="submit"
                        class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                        Guardar programación
                    </button>
                </div>
            </form>

            {{-- ===================================================== --}}
            {{-- ESTADO GUARDADO --}}
            {{-- ===================================================== --}}
            @if ($sesionesConfiguradas)

                <div class="mt-5 rounded-xl border border-emerald-500/20 bg-emerald-500/[0.06] p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-sm font-bold text-emerald-400">
                            ✓
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-emerald-300">
                                Programación configurada
                            </p>

                            <p class="mt-1 text-xs leading-5 text-gray-500">
                                @if ($modoSesiones === 'ninguna')
                                    Esta actividad quedó configurada sin sesiones ni horarios.
                                @elseif ($modoSesiones === 'unica')
                                    Esta actividad utiliza una sola sesión principal.
                                @elseif ($modoSesiones === 'multiples')
                                    Esta actividad utiliza varias sesiones o turnos.
                                @else
                                    La configuración de programación fue guardada.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

            @endif
        @endif

    </div>
</div>

{{-- ============================================================= --}}
{{-- MODAL: PRODUCTO O SERVICIO --}}
{{-- ============================================================= --}}
@if ($paso === 'productos' && $ofreceProductos)

<div id="modalItem"
    class="fixed inset-0 z-[300] hidden items-center justify-center bg-black/80 p-3 backdrop-blur-sm sm:p-6">

    <div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-white/10 bg-[#10151f]">

        {{-- ========================================================= --}}
        {{-- CABECERA --}}
        {{-- ========================================================= --}}
        <div class="flex items-center justify-between border-b border-white/10 p-4 sm:px-6">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-400">
                    Producto o servicio
                </p>

                <h2 id="modalItemTitulo"
                    class="mt-1 truncate font-semibold text-white">
                    Agregar producto o servicio
                </h2>
            </div>

            <button type="button"
                id="cerrarModalItem"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xl text-gray-500 transition hover:bg-white/5 hover:text-white">
                ×
            </button>
        </div>

        {{-- ========================================================= --}}
        {{-- FORMULARIO --}}
        {{-- ========================================================= --}}
        <form id="formItem"
            action="{{ route('admin.actividades.items.store', [
                    'actividad' => $actividad->id_actividad
                ]) }}"
            method="POST"
            class="min-h-0 flex-1 overflow-y-auto">

            @csrf

            <input type="hidden"
                name="_method"
                id="metodoItem"
                value="PUT"
                disabled>

            <div class="space-y-5 p-4 sm:p-6">

                {{-- NOMBRE --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Nombre
                    </label>

                    <input type="text"
                        id="item_nombre"
                        name="item_nombre"
                        maxlength="150"
                        required
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-emerald-500/40"
                        placeholder="Ej. Camiseta oficial SDS26">
                </div>

                {{-- TIPO --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Tipo
                    </label>

                    <select id="item_tipo"
                        name="item_tipo"
                        required
                        class="block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-white outline-none">

                        <option value="producto">
                            Producto físico
                        </option>

                        <option value="servicio">
                            Servicio
                        </option>

                        <option value="acceso">
                            Entrada o acceso
                        </option>

                        <option value="reserva">
                            Reserva
                        </option>

                        <option value="donacion">
                            Donación
                        </option>

                        <option value="otro">
                            Otro
                        </option>
                    </select>
                </div>

                {{-- DESCRIPCIÓN --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Descripción
                    </label>

                    <textarea id="item_descripcion"
                        name="item_descripcion"
                        rows="3"
                        class="block w-full resize-y rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-emerald-500/40"
                        placeholder="Describe brevemente este producto o servicio"></textarea>
                </div>

                {{-- PRECIO Y COSTO --}}
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
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-emerald-500/40">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Costo de referencia
                        </label>

                        <input type="number"
                            id="item_costo_referencia"
                            name="item_costo_referencia"
                            value="0.00"
                            min="0"
                            step="0.01"
                            required
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-emerald-500/40">
                    </div>
                </div>

                {{-- MARGEN --}}
                <div class="rounded-xl border border-blue-500/15 bg-blue-500/[0.04] p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-gray-400">
                                Margen estimado
                            </p>

                            <p class="mt-1 text-xs text-gray-600">
                                Precio menos costo de referencia
                            </p>
                        </div>

                        <span id="margenItem"
                            class="text-lg font-bold text-blue-400">
                            $0.00
                        </span>
                    </div>
                </div>

                {{-- STOCK --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-300">
                        Cantidad disponible
                    </label>

                    <input type="number"
                        id="item_stock_total"
                        name="item_stock_total"
                        min="0"
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-emerald-500/40"
                        placeholder="Vacío = sin límite">

                    <p class="mt-1 text-xs text-gray-600">
                        Déjalo vacío cuando no exista un límite de unidades.
                    </p>
                </div>

                {{-- MÍNIMO / MÁXIMO --}}
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
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-emerald-500/40">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Máximo por compra
                        </label>

                        <input type="number"
                            id="item_max_por_inscripcion"
                            name="item_max_por_inscripcion"
                            min="1"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-emerald-500/40"
                            placeholder="Sin límite">
                    </div>
                </div>

                {{-- DISPONIBILIDAD --}}
                <div>
                    <p class="mb-3 text-sm font-medium text-gray-300">
                        Período de disponibilidad
                    </p>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Disponible desde
                            </label>

                            <input type="datetime-local"
                                id="item_venta_desde"
                                name="item_venta_desde"
                                class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-emerald-500/40">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Disponible hasta
                            </label>

                            <input type="datetime-local"
                                id="item_venta_hasta"
                                name="item_venta_hasta"
                                class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-emerald-500/40">
                        </div>
                    </div>
                </div>

                {{-- PARTICIPANTE --}}
                <div class="rounded-xl border border-white/10 p-4">

                    <input type="hidden"
                        name="item_requiere_participante"
                        value="0">

                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox"
                            id="item_requiere_participante"
                            name="item_requiere_participante"
                            value="1"
                            class="mt-1">

                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-300">
                                Cada unidad debe asociarse a una persona
                            </p>

                            <p class="mt-1 text-xs leading-5 text-gray-600">
                                Útil para entradas, reservaciones o servicios donde
                                necesitas saber quién utilizará cada unidad.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- ===================================================== --}}
            {{-- BOTONES DEL MODAL --}}
            {{-- ===================================================== --}}
            <div class="flex flex-col-reverse gap-3 border-t border-white/10 p-4 sm:flex-row sm:justify-end sm:px-6">

                <button type="button"
                    id="cancelarModalItem"
                    class="rounded-xl border border-white/10 px-5 py-3 text-sm font-semibold text-gray-400 transition hover:bg-white/5">
                    Cancelar
                </button>

                <button type="submit"
                    id="guardarItemTexto"
                    class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                    Agregar elemento
                </button>
            </div>
        </form>
    </div>
</div>

@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | UTILIDADES GENERALES
    |--------------------------------------------------------------------------
    */

    function decodificarBase64Json(valor) {
        if (!valor) return null;

        try {
            const bytes = Uint8Array.from(
                atob(valor),
                char => char.charCodeAt(0)
            );

            return JSON.parse(
                new TextDecoder().decode(bytes)
            );
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

    function moneda(valor) {
        const numero = Number(valor ?? 0);

        if (Number.isNaN(numero)) {
            return '$0.00';
        }

        return `$${numero.toFixed(2)}`;
    }

    function fechaLocalAhora() {
        const ahora = new Date();
        ahora.setSeconds(0, 0);

        const compensada = new Date(
            ahora.getTime() - ahora.getTimezoneOffset() * 60000
        );

        return compensada
            .toISOString()
            .slice(0, 16);
    }

    function formatearFecha(valor) {
        if (!valor) {
            return 'Fecha pendiente';
        }

        const fecha = new Date(valor);

        if (Number.isNaN(fecha.getTime())) {
            return 'Fecha pendiente';
        }

        return new Intl.DateTimeFormat('es-SV', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }).format(fecha);
    }

    /*
    |--------------------------------------------------------------------------
    | PRODUCTOS O SERVICIOS
    |--------------------------------------------------------------------------
    */

    const modalItem = document.getElementById('modalItem');

    if (modalItem) {
        const formItem = document.getElementById('formItem');
        const metodoItem = document.getElementById('metodoItem');
        const tituloItem = document.getElementById('modalItemTitulo');
        const guardarItemTexto = document.getElementById('guardarItemTexto');
        const rutaCrear = formItem.action;

        function abrirItem() {
            modalItem.classList.remove('hidden');
            modalItem.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function cerrarItem() {
            modalItem.classList.add('hidden');
            modalItem.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        function actualizarMargen() {
            const precio = parseFloat(
                document.getElementById('item_precio')?.value || 0
            );

            const costo = parseFloat(
                document.getElementById('item_costo_referencia')?.value || 0
            );

            const margen = document.getElementById('margenItem');

            if (margen) {
                margen.textContent =
                    `$${(precio - costo).toFixed(2)}`;
            }
        }

        function nuevoItem() {
            formItem.reset();
            formItem.action = rutaCrear;

            metodoItem.disabled = true;

            tituloItem.textContent =
                'Agregar producto o servicio';

            guardarItemTexto.textContent =
                'Agregar elemento';

            document.getElementById('item_precio').value =
                '0.00';

            document.getElementById('item_costo_referencia').value =
                '0.00';

            document.getElementById('item_min_por_inscripcion').value =
                '1';

            actualizarMargen();
            abrirItem();
        }

        document
            .getElementById('btnNuevoItem')
            ?.addEventListener(
                'click',
                nuevoItem
            );

        document
            .getElementById('btnNuevoItemVacio')
            ?.addEventListener(
                'click',
                nuevoItem
            );

        document
            .getElementById('cerrarModalItem')
            ?.addEventListener(
                'click',
                cerrarItem
            );

        document
            .getElementById('cancelarModalItem')
            ?.addEventListener(
                'click',
                cerrarItem
            );

        document
            .getElementById('item_precio')
            ?.addEventListener(
                'input',
                actualizarMargen
            );

        document
            .getElementById('item_costo_referencia')
            ?.addEventListener(
                'input',
                actualizarMargen
            );

        modalItem.addEventListener(
            'click',
            function (event) {
                if (event.target === modalItem) {
                    cerrarItem();
                }
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Escape'
                    && !modalItem.classList.contains('hidden')
                ) {
                    cerrarItem();
                }
            }
        );

        document
            .querySelectorAll('.btnEditarItem')
            .forEach(button => {
                button.addEventListener(
                    'click',
                    function () {
                        formItem.action =
                            this.dataset.url;

                        metodoItem.disabled =
                            false;

                        metodoItem.value =
                            'PUT';

                        tituloItem.textContent =
                            'Editar producto o servicio';

                        guardarItemTexto.textContent =
                            'Guardar cambios';

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

                        actualizarMargen();
                        abrirItem();
                    }
                );
            });
    }

    /*
    |--------------------------------------------------------------------------
    | DATOS DEL COMPRADOR
    |--------------------------------------------------------------------------
    */

    const seccionCampos =
        document.getElementById('seccionCamposPedido');

    if (seccionCampos) {
        const contenedor =
            document.getElementById('contenedorCampos');

        const preview =
            document.getElementById('vistaPreviaCampos');

        const config =
            decodificarBase64Json(
                document.getElementById('configDatosActual')?.value
            );

        let contador = 0;

        function actualizarVisibilidadCampos() {
            const seleccionado =
                document.querySelector(
                    'input[name="requiere_datos"]:checked'
                );

            if (seleccionado?.value === '1') {
                seccionCampos.classList.remove('hidden');

                if (contenedor.children.length === 0) {
                    agregarCampo(
                        null,
                        false
                    );
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

        function actualizarResumenCampo(fila) {
            const nombre =
                fila
                    .querySelector('.campoNombre')
                    .value
                    .trim()
                || 'Dato sin nombre';

            const tipo =
                fila
                    .querySelector('.campoTipo')
                    .value;

            const requerido =
                fila
                    .querySelector('.campoRequerido')
                    .checked;

            const opciones =
                fila
                    .querySelector('.campoOpciones')
                    .value
                    .split(/[\n,]+/)
                    .map(valor => valor.trim())
                    .filter(Boolean);

            fila
                .querySelector('.resumenNombre')
                .textContent =
                    nombre;

            let detalle =
                tipoBonito(tipo);

            if (tipo === 'lista') {
                detalle +=
                    ` · ${opciones.length} opciones`;
            }

            detalle +=
                requerido
                    ? ' · Obligatorio'
                    : ' · Opcional';

            fila
                .querySelector('.resumenDetalle')
                .textContent =
                    detalle;
        }

        function renderPreviewCampos() {
            preview.innerHTML = '';

            const filas =
                contenedor.querySelectorAll(
                    '.campoPedido'
                );

            if (filas.length === 0) {
                preview.innerHTML = `
                    <p class="text-sm text-gray-600">
                        Agrega un dato para ver la vista previa.
                    </p>
                `;

                return;
            }

            filas.forEach(fila => {
                const nombre =
                    fila
                        .querySelector('.campoNombre')
                        .value
                        .trim()
                    || 'Dato sin nombre';

                const tipo =
                    fila
                        .querySelector('.campoTipo')
                        .value;

                const requerido =
                    fila
                        .querySelector('.campoRequerido')
                        .checked;

                const opcionesTexto =
                    fila
                        .querySelector('.campoOpciones')
                        .value;

                const bloque =
                    document.createElement('div');

                const label =
                    document.createElement('label');

                label.className =
                    'mb-2 block text-sm font-medium text-gray-300';

                label.textContent =
                    nombre
                    + (
                        requerido
                            ? ' *'
                            : ''
                    );

                bloque.appendChild(label);

                if (tipo === 'lista') {
                    const select =
                        document.createElement('select');

                    select.disabled =
                        true;

                    select.className =
                        'block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-gray-400';

                    const placeholder =
                        document.createElement('option');

                    placeholder.textContent =
                        'Selecciona una opción';

                    select.appendChild(
                        placeholder
                    );

                    opcionesTexto
                        .split(/[\n,]+/)
                        .map(valor => valor.trim())
                        .filter(Boolean)
                        .forEach(opcion => {
                            const option =
                                document.createElement('option');

                            option.textContent =
                                opcion;

                            select.appendChild(
                                option
                            );
                        });

                    bloque.appendChild(
                        select
                    );
                } else {
                    const input =
                        document.createElement('input');

                    input.disabled =
                        true;

                    input.type =
                        tipo === 'numero'
                            ? 'number'
                            : 'text';

                    input.placeholder =
                        tipo === 'numero'
                            ? 'Ingresa un número'
                            : 'Escribe aquí';

                    input.className =
                        'block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-gray-500';

                    bloque.appendChild(
                        input
                    );
                }

                preview.appendChild(
                    bloque
                );
            });
        }

        function contraerCampo(fila) {
            fila
                .querySelector('.campoCuerpo')
                .classList
                .add('hidden');

            fila
                .querySelector('.iconoExpandir')
                .textContent =
                    '⌄';

            actualizarResumenCampo(
                fila
            );
        }

        function expandirCampo(fila) {
            contenedor
                .querySelectorAll('.campoPedido')
                .forEach(otra => {
                    if (otra !== fila) {
                        contraerCampo(
                            otra
                        );
                    }
                });

            fila
                .querySelector('.campoCuerpo')
                .classList
                .remove('hidden');

            fila
                .querySelector('.iconoExpandir')
                .textContent =
                    '⌃';
        }

        function agregarCampo(
            datos = null,
            contraido = false
        ) {
            const indice =
                contador++;

            const fila =
                document.createElement('div');

            fila.className =
                'campoPedido overflow-hidden rounded-2xl border border-white/10 bg-black/10';

            fila.innerHTML = `
                <button type="button"
                    class="cabeceraCampo flex w-full items-center justify-between gap-3 p-4 text-left transition hover:bg-white/[0.03]">

                    <div class="min-w-0">
                        <p class="resumenNombre truncate font-semibold text-white">
                            Nuevo dato
                        </p>

                        <p class="resumenDetalle mt-1 truncate text-xs text-gray-500">
                            Configura este dato
                        </p>
                    </div>

                    <span class="iconoExpandir shrink-0 text-gray-500">
                        ⌃
                    </span>
                </button>

                <div class="campoCuerpo border-t border-white/10 p-4">
                    <div class="flex items-start gap-3">

                        <div class="min-w-0 flex-1">
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Nombre
                            </label>

                            <input type="text"
                                name="campos[${indice}][nombre]"
                                required
                                class="campoNombre block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-blue-500/40"
                                placeholder="Ej. Talla">
                        </div>

                        <button type="button"
                            class="btnQuitarCampo mt-6 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-red-500/20 text-red-400 transition hover:bg-red-500/10">
                            ×
                        </button>
                    </div>

                    <div class="mt-4">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Tipo de respuesta
                        </label>

                        <select name="campos[${indice}][tipo]"
                            class="campoTipo block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-white outline-none">

                            <option value="lista">
                                Lista de opciones
                            </option>

                            <option value="texto">
                                Campo de texto
                            </option>

                            <option value="numero">
                                Campo numérico
                            </option>
                        </select>
                    </div>

                    <div class="campoOpcionesContenedor mt-4">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                            Opciones
                        </label>

                        <textarea name="campos[${indice}][opciones]"
                            rows="3"
                            class="campoOpciones block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-blue-500/40"
                            placeholder="S, M, L, XL"></textarea>

                        <p class="mt-2 text-xs leading-5 text-gray-600">
                            Puedes separar las opciones con comas o escribir
                            una por línea.
                        </p>
                    </div>

                    <div class="mt-4">
                        <input type="hidden"
                            name="campos[${indice}][requerido]"
                            value="0">

                        <label class="flex cursor-pointer items-start gap-3">
                            <input type="checkbox"
                                name="campos[${indice}][requerido]"
                                value="1"
                                class="campoRequerido mt-1">

                            <div>
                                <span class="text-sm font-medium text-gray-300">
                                    Será obligatorio
                                </span>

                                <p class="mt-1 text-xs leading-5 text-gray-600">
                                    La persona no podrá continuar sin completar
                                    este dato.
                                </p>
                            </div>
                        </label>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="button"
                            class="btnListoCampo rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500/15">
                            Listo
                        </button>
                    </div>
                </div>
            `;

            const nombre =
                fila.querySelector(
                    '.campoNombre'
                );

            const tipo =
                fila.querySelector(
                    '.campoTipo'
                );

            const opciones =
                fila.querySelector(
                    '.campoOpciones'
                );

            const opcionesContenedor =
                fila.querySelector(
                    '.campoOpcionesContenedor'
                );

            const requerido =
                fila.querySelector(
                    '.campoRequerido'
                );

            if (datos) {
                nombre.value =
                    datos.label ?? '';

                tipo.value =
                    datos.type ?? 'lista';

                requerido.checked =
                    datos.required === true;

                if (
                    Array.isArray(
                        datos.options
                    )
                ) {
                    opciones.value =
                        datos.options.join(', ');
                }
            }

            function actualizarTipo() {
                opcionesContenedor
                    .classList
                    .toggle(
                        'hidden',
                        tipo.value !== 'lista'
                    );

                actualizarResumenCampo(
                    fila
                );

                renderPreviewCampos();
            }

            nombre.addEventListener(
                'input',
                function () {
                    actualizarResumenCampo(
                        fila
                    );

                    renderPreviewCampos();
                }
            );

            tipo.addEventListener(
                'change',
                actualizarTipo
            );

            opciones.addEventListener(
                'input',
                function () {
                    actualizarResumenCampo(
                        fila
                    );

                    renderPreviewCampos();
                }
            );

            requerido.addEventListener(
                'change',
                function () {
                    actualizarResumenCampo(
                        fila
                    );

                    renderPreviewCampos();
                }
            );

            fila
                .querySelector('.cabeceraCampo')
                .addEventListener(
                    'click',
                    function () {
                        const cuerpo =
                            fila.querySelector(
                                '.campoCuerpo'
                            );

                        if (
                            cuerpo
                                .classList
                                .contains('hidden')
                        ) {
                            expandirCampo(
                                fila
                            );
                        } else {
                            contraerCampo(
                                fila
                            );
                        }
                    }
                );

            fila
                .querySelector('.btnListoCampo')
                .addEventListener(
                    'click',
                    function () {
                        contraerCampo(
                            fila
                        );
                    }
                );

            fila
                .querySelector('.btnQuitarCampo')
                .addEventListener(
                    'click',
                    function () {
                        fila.remove();
                        renderPreviewCampos();
                    }
                );

            contenedor.appendChild(
                fila
            );

            actualizarTipo();

            if (contraido) {
                contraerCampo(
                    fila
                );
            } else {
                expandirCampo(
                    fila
                );
            }

            renderPreviewCampos();
        }

        document
            .querySelectorAll(
                'input[name="requiere_datos"]'
            )
            .forEach(radio => {
                radio.addEventListener(
                    'change',
                    actualizarVisibilidadCampos
                );
            });

        document
            .getElementById('btnAgregarCampo')
            ?.addEventListener(
                'click',
                function () {
                    agregarCampo(
                        null,
                        false
                    );
                }
            );

        if (
            config?.enabled
            && Array.isArray(
                config.fields
            )
        ) {
            config.fields.forEach(
                campo => {
                    agregarCampo(
                        campo,
                        true
                    );
                }
            );
        }

        actualizarVisibilidadCampos();
        renderPreviewCampos();
    }

    /*
    |--------------------------------------------------------------------------
    | AUMENTOS DE PRECIO Y COSTO
    |--------------------------------------------------------------------------
    */

    const seccionAjustes =
        document.getElementById(
            'seccionAjustesPrecio'
        );

    if (seccionAjustes) {
        const formAjustes =
            document.getElementById(
                'formAjustesPrecio'
            );

        const datos =
            decodificarBase64Json(
                document.getElementById(
                    'datosPrecioActual'
                )?.value
            );

        const contenedor =
            document.getElementById(
                'contenedorAjustesPrecio'
            );

        const btnAgregar =
            document.getElementById(
                'btnAgregarAjuste'
            );

        const mensajeSinOpciones =
            document.getElementById(
                'mensajeSinOpcionesAjuste'
            );

        let contadorAjustes = 0;

        function cambiosPrecioActivos() {
            return document.querySelector(
                'input[name="tiene_cambios"]:checked'
            )?.value === '1';
        }

        function camposSeleccionados() {
            return Array.from(
                document.querySelectorAll(
                    '.campoPrecioCheckbox:checked'
                )
            ).map(
                input => input.value
            );
        }

        function obtenerCampo(clave) {
            return (
                datos?.campos ?? []
            ).find(
                campo =>
                    campo.key === clave
            );
        }

        function combinacionesUsadas(
            filaIgnorada = null
        ) {
            const usados =
                new Set();

            contenedor
                .querySelectorAll(
                    '.ajusteFila'
                )
                .forEach(fila => {
                    if (
                        fila === filaIgnorada
                    ) {
                        return;
                    }

                    const campo =
                        fila
                            .querySelector(
                                '.ajusteCampo'
                            )
                            ?.value;

                    const opcion =
                        fila
                            .querySelector(
                                '.ajusteOpcion'
                            )
                            ?.value;

                    if (
                        campo
                        && opcion
                    ) {
                        usados.add(
                            `${campo}|||${opcion}`
                        );
                    }
                });

            return usados;
        }

        function opcionesDisponiblesParaCampo(
            campoClave,
            filaActual = null
        ) {
            const campo =
                obtenerCampo(
                    campoClave
                );

            if (!campo) {
                return [];
            }

            const usados =
                combinacionesUsadas(
                    filaActual
                );

            return (
                campo.options ?? []
            ).filter(opcion => {
                return !usados.has(
                    `${campoClave}|||${opcion}`
                );
            });
        }

        function hayOpcionesDisponibles() {
            const seleccionados =
                camposSeleccionados();

            return seleccionados.some(
                clave => {
                    return opcionesDisponiblesParaCampo(
                        clave
                    ).length > 0;
                }
            );
        }

        function actualizarEstadoAgregar() {
            const activo =
                cambiosPrecioActivos();

            const hay =
                activo
                && hayOpcionesDisponibles();

            btnAgregar.disabled =
                !hay;

            btnAgregar
                .classList
                .toggle(
                    'opacity-40',
                    !hay
                );

            btnAgregar
                .classList
                .toggle(
                    'cursor-not-allowed',
                    !hay
                );

            mensajeSinOpciones
                .classList
                .toggle(
                    'hidden',
                    !activo || hay
                );
        }

        function rellenarSelectCampo(
            fila,
            campoActual = null
        ) {
            const selectCampo =
                fila.querySelector(
                    '.ajusteCampo'
                );

            const seleccionados =
                camposSeleccionados();

            const opcionActual =
                fila
                    .querySelector(
                        '.ajusteOpcion'
                    )
                    ?.value;

            selectCampo.innerHTML =
                '';

            seleccionados.forEach(
                clave => {
                    const campo =
                        obtenerCampo(
                            clave
                        );

                    const disponibles =
                        opcionesDisponiblesParaCampo(
                            clave,
                            fila
                        );

                    const permitirCampo =
                        disponibles.length > 0
                        || (
                            campoActual === clave
                            && opcionActual
                        );

                    if (!permitirCampo) {
                        return;
                    }

                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        clave;

                    option.textContent =
                        campo?.label
                        ?? clave;

                    selectCampo.appendChild(
                        option
                    );
                }
            );

            if (
                campoActual
                && seleccionados.includes(
                    campoActual
                )
            ) {
                selectCampo.value =
                    campoActual;
            }
        }

        function rellenarOpcionesFila(
            fila,
            opcionActual = null
        ) {
            const campoClave =
                fila
                    .querySelector(
                        '.ajusteCampo'
                    )
                    .value;

            const select =
                fila.querySelector(
                    '.ajusteOpcion'
                );

            const campo =
                obtenerCampo(
                    campoClave
                );

            const usados =
                combinacionesUsadas(
                    fila
                );

            select.innerHTML =
                '';

            (
                campo?.options ?? []
            ).forEach(opcion => {
                const identificador =
                    `${campoClave}|||${opcion}`;

                if (
                    usados.has(
                        identificador
                    )
                    && opcion !== opcionActual
                ) {
                    return;
                }

                const option =
                    document.createElement(
                        'option'
                    );

                option.value =
                    opcion;

                option.textContent =
                    opcion;

                select.appendChild(
                    option
                );
            });

            if (
                opcionActual
                && Array
                    .from(
                        select.options
                    )
                    .some(
                        option =>
                            option.value
                            === opcionActual
                    )
            ) {
                select.value =
                    opcionActual;
            }
        }

        function actualizarResumenAjuste(
            fila
        ) {
            const campoSelect =
                fila.querySelector(
                    '.ajusteCampo'
                );

            const opcionSelect =
                fila.querySelector(
                    '.ajusteOpcion'
                );

            const aumentoPrecio =
                Number(
                    fila
                        .querySelector(
                            '.aumentoPrecio'
                        )
                        .value
                    || 0
                );

            const aumentoCosto =
                Number(
                    fila
                        .querySelector(
                            '.aumentoCosto'
                        )
                        .value
                    || 0
                );

            const campo =
                campoSelect
                    .selectedOptions[0]
                    ?.textContent
                    ?.trim()
                || 'Dato';

            const opcion =
                opcionSelect.value
                || 'Opción';

            fila
                .querySelector(
                    '.resumenAjustePrincipal'
                )
                .textContent =
                    `${campo} → ${opcion}`;

            const partes = [];

            if (aumentoPrecio > 0) {
                partes.push(
                    `+${moneda(aumentoPrecio)} precio`
                );
            }

            if (aumentoCosto > 0) {
                partes.push(
                    `+${moneda(aumentoCosto)} costo`
                );
            }

            fila
                .querySelector(
                    '.resumenAjusteDetalle'
                )
                .textContent =
                    partes.length > 0
                        ? partes.join(' · ')
                        : 'Sin aumento definido';
        }

        function contraerAjuste(
            fila
        ) {
            fila
                .querySelector(
                    '.ajusteCuerpo'
                )
                .classList
                .add('hidden');

            fila
                .querySelector(
                    '.iconoAjuste'
                )
                .textContent =
                    '⌄';

            actualizarResumenAjuste(
                fila
            );
        }

        function expandirAjuste(
            fila
        ) {
            contenedor
                .querySelectorAll(
                    '.ajusteFila'
                )
                .forEach(otra => {
                    if (
                        otra !== fila
                    ) {
                        contraerAjuste(
                            otra
                        );
                    }
                });

            fila
                .querySelector(
                    '.ajusteCuerpo'
                )
                .classList
                .remove('hidden');

            fila
                .querySelector(
                    '.iconoAjuste'
                )
                .textContent =
                    '⌃';
        }

        function actualizarTodasLasFilas(
            filaOrigen = null
        ) {
            const filas =
                Array.from(
                    contenedor.querySelectorAll(
                        '.ajusteFila'
                    )
                );

            filas.forEach(fila => {
                if (
                    fila === filaOrigen
                ) {
                    actualizarResumenAjuste(
                        fila
                    );

                    return;
                }

                const campoActual =
                    fila
                        .querySelector(
                            '.ajusteCampo'
                        )
                        .value;

                const opcionActual =
                    fila
                        .querySelector(
                            '.ajusteOpcion'
                        )
                        .value;

                rellenarSelectCampo(
                    fila,
                    campoActual
                );

                const selectCampo =
                    fila.querySelector(
                        '.ajusteCampo'
                    );

                if (!selectCampo.value) {
                    fila.remove();
                    return;
                }

                rellenarOpcionesFila(
                    fila,
                    opcionActual
                );

                const selectOpcion =
                    fila.querySelector(
                        '.ajusteOpcion'
                    );

                if (
                    selectOpcion.options.length
                    === 0
                ) {
                    fila.remove();
                    return;
                }

                actualizarResumenAjuste(
                    fila
                );
            });

            actualizarEstadoAgregar();
        }

        function agregarAjuste(
            regla = null,
            contraido = false
        ) {
            const seleccionados =
                camposSeleccionados();

            if (
                seleccionados.length === 0
            ) {
                alert(
                    'Selecciona primero al menos un dato que pueda generar un aumento.'
                );

                return;
            }

            if (
                !hayOpcionesDisponibles()
                && !regla
            ) {
                actualizarEstadoAgregar();
                return;
            }

            const indice =
                contadorAjustes++;

            const fila =
                document.createElement(
                    'div'
                );

            fila.className =
                'ajusteFila overflow-hidden rounded-2xl border border-white/10 bg-black/10';

            fila.innerHTML = `
                <button type="button"
                    class="cabeceraAjuste flex w-full items-center justify-between gap-3 p-4 text-left transition hover:bg-white/[0.03]">

                    <div class="min-w-0">
                        <p class="resumenAjustePrincipal truncate font-semibold text-white">
                            Nuevo aumento
                        </p>

                        <p class="resumenAjusteDetalle mt-1 truncate text-xs text-gray-500">
                            Configura esta opción
                        </p>
                    </div>

                    <span class="iconoAjuste shrink-0 text-gray-500">
                        ⌃
                    </span>
                </button>

                <div class="ajusteCuerpo border-t border-white/10 p-4">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Dato
                            </label>

                            <select name="ajustes[${indice}][campo]"
                                class="ajusteCampo block w-full rounded-xl border border-white/10 bg-[#111827] px-3 py-3 text-white"
                                required>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Opción
                            </label>

                            <select name="ajustes[${indice}][opcion]"
                                class="ajusteOpcion block w-full rounded-xl border border-white/10 bg-[#111827] px-3 py-3 text-white"
                                required>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Aumenta el precio
                            </label>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                                    +$
                                </span>

                                <input type="number"
                                    name="ajustes[${indice}][aumento_precio]"
                                    min="0"
                                    step="0.01"
                                    value="0.00"
                                    required
                                    class="aumentoPrecio block w-full rounded-xl border border-white/10 bg-black/20 py-3 pl-9 pr-3 text-white">
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Aumenta el costo
                            </label>

                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                                    +$
                                </span>

                                <input type="number"
                                    name="ajustes[${indice}][aumento_costo]"
                                    min="0"
                                    step="0.01"
                                    value="0.00"
                                    required
                                    class="aumentoCosto block w-full rounded-xl border border-white/10 bg-black/20 py-3 pl-9 pr-3 text-white">
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-between">
                        <button type="button"
                            class="btnQuitarAjuste rounded-xl border border-red-500/20 px-4 py-2.5 text-sm font-semibold text-red-400 transition hover:bg-red-500/10">
                            Eliminar aumento
                        </button>

                        <button type="button"
                            class="btnListoAjuste rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500/15">
                            Listo
                        </button>
                    </div>
                </div>
            `;

            contenedor.appendChild(
                fila
            );

            const selectCampo =
                fila.querySelector(
                    '.ajusteCampo'
                );

            const selectOpcion =
                fila.querySelector(
                    '.ajusteOpcion'
                );

            const aumentoPrecio =
                fila.querySelector(
                    '.aumentoPrecio'
                );

            const aumentoCosto =
                fila.querySelector(
                    '.aumentoCosto'
                );

            rellenarSelectCampo(
                fila,
                regla?.field_key ?? null
            );

            if (!selectCampo.value) {
                fila.remove();
                actualizarEstadoAgregar();
                return;
            }

            rellenarOpcionesFila(
                fila,
                regla?.option ?? null
            );

            if (
                selectOpcion.options.length
                === 0
            ) {
                fila.remove();
                actualizarEstadoAgregar();
                return;
            }

            aumentoPrecio.value =
                Number(
                    regla?.price_increment ?? 0
                ).toFixed(2);

            aumentoCosto.value =
                Number(
                    regla?.cost_increment ?? 0
                ).toFixed(2);

            selectCampo.addEventListener(
                'change',
                function () {
                    rellenarOpcionesFila(
                        fila
                    );

                    actualizarResumenAjuste(
                        fila
                    );

                    actualizarTodasLasFilas(
                        fila
                    );
                }
            );

            selectOpcion.addEventListener(
                'change',
                function () {
                    actualizarResumenAjuste(
                        fila
                    );

                    actualizarTodasLasFilas(
                        fila
                    );
                }
            );

            aumentoPrecio.addEventListener(
                'input',
                function () {
                    actualizarResumenAjuste(
                        fila
                    );
                }
            );

            aumentoCosto.addEventListener(
                'input',
                function () {
                    actualizarResumenAjuste(
                        fila
                    );
                }
            );

            fila
                .querySelector(
                    '.cabeceraAjuste'
                )
                .addEventListener(
                    'click',
                    function () {
                        const cuerpo =
                            fila.querySelector(
                                '.ajusteCuerpo'
                            );

                        if (
                            cuerpo
                                .classList
                                .contains('hidden')
                        ) {
                            expandirAjuste(
                                fila
                            );
                        } else {
                            contraerAjuste(
                                fila
                            );
                        }
                    }
                );

            fila
                .querySelector(
                    '.btnListoAjuste'
                )
                .addEventListener(
                    'click',
                    function () {
                        contraerAjuste(
                            fila
                        );
                    }
                );

            fila
                .querySelector(
                    '.btnQuitarAjuste'
                )
                .addEventListener(
                    'click',
                    function () {
                        fila.remove();

                        actualizarTodasLasFilas();
                    }
                );

            actualizarResumenAjuste(
                fila
            );

            if (contraido) {
                contraerAjuste(
                    fila
                );
            } else {
                expandirAjuste(
                    fila
                );
            }

            actualizarTodasLasFilas(
                fila
            );
        }

        function actualizarVisibilidadAjustes() {
            const activo =
                cambiosPrecioActivos();

            seccionAjustes
                .classList
                .toggle(
                    'hidden',
                    !activo
                );

            seccionAjustes
                .querySelectorAll(
                    'input, select, textarea'
                )
                .forEach(elemento => {
                    elemento.disabled =
                        !activo;
                });

            actualizarEstadoAgregar();
        }

        document
            .querySelectorAll(
                'input[name="tiene_cambios"]'
            )
            .forEach(radio => {
                radio.addEventListener(
                    'change',
                    actualizarVisibilidadAjustes
                );
            });

        document
            .querySelectorAll(
                '.campoPrecioCheckbox'
            )
            .forEach(checkbox => {
                checkbox.addEventListener(
                    'change',
                    function () {
                        const activos =
                            camposSeleccionados();

                        contenedor
                            .querySelectorAll(
                                '.ajusteFila'
                            )
                            .forEach(fila => {
                                const campo =
                                    fila
                                        .querySelector(
                                            '.ajusteCampo'
                                        )
                                        .value;

                                if (
                                    campo
                                    && !activos.includes(
                                        campo
                                    )
                                ) {
                                    fila.remove();
                                }
                            });

                        actualizarTodasLasFilas();
                    }
                );
            });

        btnAgregar.addEventListener(
            'click',
            function () {
                agregarAjuste(
                    null,
                    false
                );
            }
        );

        const reglasExistentes =
            datos?.config?.rules ?? [];

        reglasExistentes.forEach(
            regla => {
                agregarAjuste(
                    regla,
                    true
                );
            }
        );

        formAjustes?.addEventListener(
            'submit',
            function (event) {
                const tieneCambios =
                    document.querySelector(
                        'input[name="tiene_cambios"]:checked'
                    );

                if (!tieneCambios) {
                    event.preventDefault();

                    alert(
                        'Indica si alguna opción aumenta el precio o el costo.'
                    );

                    return;
                }

                if (
                    tieneCambios.value !== '1'
                ) {
                    return;
                }

                const seleccionados =
                    camposSeleccionados();

                if (
                    seleccionados.length === 0
                ) {
                    event.preventDefault();

                    alert(
                        'Selecciona al menos un dato que pueda modificar el precio o costo.'
                    );

                    return;
                }

                const filas =
                    Array.from(
                        contenedor.querySelectorAll(
                            '.ajusteFila'
                        )
                    );

                if (
                    filas.length === 0
                ) {
                    event.preventDefault();

                    alert(
                        'Agrega al menos una opción que aumente el precio o costo.'
                    );

                    return;
                }

                const usados =
                    new Set();

                let duplicado =
                    false;

                let reglaSinAumento =
                    false;

                filas.forEach(fila => {
                    const campo =
                        fila
                            .querySelector(
                                '.ajusteCampo'
                            )
                            .value;

                    const opcion =
                        fila
                            .querySelector(
                                '.ajusteOpcion'
                            )
                            .value;

                    const precio =
                        Number(
                            fila
                                .querySelector(
                                    '.aumentoPrecio'
                                )
                                .value
                            || 0
                        );

                    const costo =
                        Number(
                            fila
                                .querySelector(
                                    '.aumentoCosto'
                                )
                                .value
                            || 0
                        );

                    const clave =
                        `${campo}|||${opcion}`;

                    if (
                        usados.has(
                            clave
                        )
                    ) {
                        duplicado =
                            true;
                    }

                    usados.add(
                        clave
                    );

                    if (
                        precio <= 0
                        && costo <= 0
                    ) {
                        reglaSinAumento =
                            true;
                    }
                });

                if (duplicado) {
                    event.preventDefault();

                    alert(
                        'Una misma opción no puede configurarse dos veces.'
                    );

                    return;
                }

                if (reglaSinAumento) {
                    event.preventDefault();

                    alert(
                        'Cada opción configurada debe aumentar el precio, el costo o ambos.'
                    );
                }
            }
        );

        actualizarVisibilidadAjustes();
        actualizarEstadoAgregar();
    }

    /*
    |--------------------------------------------------------------------------
    | PROGRAMACIÓN / SESIONES
    |--------------------------------------------------------------------------
    */

    const formSesiones =
        document.getElementById(
            'formSesiones'
        );

    if (formSesiones) {
        const seccionSinSesiones =
            document.getElementById(
                'seccionSinSesiones'
            );

        const seccionSesionUnica =
            document.getElementById(
                'seccionSesionUnica'
            );

        const seccionMultiples =
            document.getElementById(
                'seccionSesionesMultiples'
            );

        const contenedor =
            document.getElementById(
                'contenedorSesiones'
            );

        const preview =
            document.getElementById(
                'vistaPreviaSesiones'
            );

        const btnAgregarSesion =
            document.getElementById(
                'btnAgregarSesion'
            );

        const mensajeSinSesiones =
            document.getElementById(
                'mensajeSinSesiones'
            );

        const advertenciaSolapamientos =
            document.getElementById(
                'advertenciaSolapamientos'
            );

        const listaSolapamientos =
            document.getElementById(
                'listaSolapamientos'
            );

        const confirmarSolapamientos =
            document.getElementById(
                'confirmarSolapamientos'
            );

        const realizacionDesde =
            document.getElementById(
                'realizacionDesdeActividad'
            )?.value
            ?? '';

        const realizacionHasta =
            document.getElementById(
                'realizacionHastaActividad'
            )?.value
            ?? '';

        const cupoGeneralValor =
            document.getElementById(
                'cupoGeneralActividad'
            )?.value
            ?? '';

        const tieneInscripcionGeneral =
            document.getElementById(
                'actividadTieneInscripcion'
            )?.value
            === '1';

        const cupoGeneral =
            cupoGeneralValor === ''
                ? null
                : Number(
                    cupoGeneralValor
                );

        const existentes =
            decodificarBase64Json(
                document.getElementById(
                    'sesionesActuales'
                )?.value
            ) ?? [];

        let contadorSesiones =
            0;

        function modoActual() {
            return document.querySelector(
                'input[name="modo_sesiones"]:checked'
            )?.value
            ?? null;
        }

        function minimoNuevaSesion() {
            const ahora =
                fechaLocalAhora();

            if (
                realizacionDesde
                && realizacionDesde > ahora
            ) {
                return realizacionDesde;
            }

            return ahora;
        }

        function actualizarLimitesFecha(
            card
        ) {
            const idSesion =
                card
                    .querySelector(
                        '.sesionId'
                    )
                    .value;

            const inicio =
                card.querySelector(
                    '.sesionInicio'
                );

            const fin =
                card.querySelector(
                    '.sesionFin'
                );

            inicio.min =
                idSesion
                    ? realizacionDesde
                    : minimoNuevaSesion();

            if (realizacionHasta) {
                inicio.max =
                    realizacionHasta;

                fin.max =
                    realizacionHasta;
            }

            fin.min =
                inicio.value
                || (
                    idSesion
                        ? realizacionDesde
                        : minimoNuevaSesion()
                );
        }

        function actualizarLimiteCupo(
            card
        ) {
            const cupo =
                card.querySelector(
                    '.sesionCupo'
                );

            const ayuda =
                card.querySelector(
                    '.ayudaCupoSesion'
                );

            if (
                tieneInscripcionGeneral
                && cupoGeneral !== null
            ) {
                cupo.max =
                    String(
                        cupoGeneral
                    );

                cupo.placeholder =
                    `Vacío = hereda ${cupoGeneral}`;

                ayuda.textContent =
                    `Puedes usar un cupo menor. Si lo dejas vacío, heredará el cupo general de ${cupoGeneral} personas.`;
            } else {
                cupo.removeAttribute(
                    'max'
                );

                cupo.placeholder =
                    'Vacío = sin límite';

                ayuda.textContent =
                    'Déjalo vacío si esta sesión no tendrá un límite específico.';
            }
        }

        function resumenSesion(
            card
        ) {
            const nombre =
                card
                    .querySelector(
                        '.sesionNombre'
                    )
                    .value
                    .trim()
                || 'Nueva sesión';

            const inicio =
                card
                    .querySelector(
                        '.sesionInicio'
                    )
                    .value;

            const ubicacion =
                card
                    .querySelector(
                        '.sesionUbicacion'
                    )
                    .value
                    .trim();

            const cupo =
                card
                    .querySelector(
                        '.sesionCupo'
                    )
                    .value;

            const reserva =
                card
                    .querySelector(
                        '.sesionReserva'
                    )
                    .checked;

            const obligatoria =
                card
                    .querySelector(
                        '.sesionObligatoria'
                    )
                    .checked;

            card
                .querySelector(
                    '.resumenSesionNombre'
                )
                .textContent =
                    nombre;

            const detalles = [];

            detalles.push(
                formatearFecha(
                    inicio
                )
            );

            if (ubicacion) {
                detalles.push(
                    ubicacion
                );
            }

            if (cupo !== '') {
                detalles.push(
                    `${cupo} cupos`
                );
            } else if (
                tieneInscripcionGeneral
                && cupoGeneral !== null
            ) {
                detalles.push(
                    `${cupoGeneral} cupos heredados`
                );
            }

            if (reserva) {
                detalles.push(
                    'Requiere reserva'
                );
            }

            if (obligatoria) {
                detalles.push(
                    'Obligatoria'
                );
            }

            card
                .querySelector(
                    '.resumenSesionDetalle'
                )
                .textContent =
                    detalles.join(' · ');
        }

        function contraerSesion(
            card
        ) {
            card
                .querySelector(
                    '.sesionCuerpo'
                )
                .classList
                .add('hidden');

            card
                .querySelector(
                    '.iconoSesion'
                )
                .textContent =
                    '⌄';

            actualizarLimitesFecha(
                card
            );

            actualizarLimiteCupo(
                card
            );

            resumenSesion(
                card
            );
        }

        function expandirSesion(
            card
        ) {
            contenedor
                .querySelectorAll(
                    '.sesionCard'
                )
                .forEach(otra => {
                    if (
                        otra !== card
                    ) {
                        contraerSesion(
                            otra
                        );
                    }
                });

            card
                .querySelector(
                    '.sesionCuerpo'
                )
                .classList
                .remove('hidden');

            card
                .querySelector(
                    '.iconoSesion'
                )
                .textContent =
                    '⌃';

            actualizarLimitesFecha(
                card
            );

            actualizarLimiteCupo(
                card
            );
        }

        function obtenerDatosSesion(
            card
        ) {
            const inicioValor =
                card
                    .querySelector(
                        '.sesionInicio'
                    )
                    .value;

            const finValor =
                card
                    .querySelector(
                        '.sesionFin'
                    )
                    .value;

            return {
                nombre:
                    card
                        .querySelector(
                            '.sesionNombre'
                        )
                        .value
                        .trim()
                    || 'Sesión sin nombre',

                inicio:
                    inicioValor
                        ? new Date(
                            inicioValor
                        )
                        : null,

                fin:
                    finValor
                        ? new Date(
                            finValor
                        )
                        : null,
            };
        }

        function sesionesSeCruzan(
            a,
            b
        ) {
            if (
                !a.inicio
                || !b.inicio
                || Number.isNaN(
                    a.inicio.getTime()
                )
                || Number.isNaN(
                    b.inicio.getTime()
                )
            ) {
                return false;
            }

            const inicioA =
                a.inicio.getTime();

            const inicioB =
                b.inicio.getTime();

            const finA =
                a.fin
                && !Number.isNaN(
                    a.fin.getTime()
                )
                    ? a.fin.getTime()
                    : null;

            const finB =
                b.fin
                && !Number.isNaN(
                    b.fin.getTime()
                )
                    ? b.fin.getTime()
                    : null;

            if (
                finA !== null
                && finB !== null
            ) {
                return (
                    inicioA < finB
                    && finA > inicioB
                );
            }

            if (
                finA === null
                && finB === null
            ) {
                return (
                    inicioA === inicioB
                );
            }

            if (
                finA === null
                && finB !== null
            ) {
                return (
                    inicioA === inicioB
                    || (
                        inicioA > inicioB
                        && inicioA < finB
                    )
                );
            }

            return (
                inicioB === inicioA
                || (
                    inicioB > inicioA
                    && inicioB < finA
                )
            );
        }

        function obtenerSolapamientos() {
            const cards =
                Array.from(
                    contenedor.querySelectorAll(
                        '.sesionCard'
                    )
                );

            const conflictos = [];

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
                    const a =
                        obtenerDatosSesion(
                            cards[i]
                        );

                    const b =
                        obtenerDatosSesion(
                            cards[j]
                        );

                    if (
                        sesionesSeCruzan(
                            a,
                            b
                        )
                    ) {
                        conflictos.push({
                            a: a.nombre,
                            b: b.nombre,
                        });
                    }
                }
            }

            return conflictos;
        }

        function renderSolapamientos() {
            if (
                modoActual()
                !== 'multiples'
            ) {
                advertenciaSolapamientos
                    .classList
                    .add('hidden');

                confirmarSolapamientos.checked =
                    false;

                return [];
            }

            const conflictos =
                obtenerSolapamientos();

            listaSolapamientos.innerHTML =
                '';

            if (
                conflictos.length === 0
            ) {
                advertenciaSolapamientos
                    .classList
                    .add('hidden');

                confirmarSolapamientos.checked =
                    false;

                return [];
            }

            conflictos.forEach(
                conflicto => {
                    const linea =
                        document.createElement(
                            'p'
                        );

                    linea.textContent =
                        `• ${conflicto.a} coincide con ${conflicto.b}`;

                    listaSolapamientos.appendChild(
                        linea
                    );
                }
            );

            advertenciaSolapamientos
                .classList
                .remove('hidden');

            return conflictos;
        }

        function renderPreviewSesiones() {
            if (!preview) {
                return;
            }

            preview.innerHTML =
                '';

            const cards =
                Array.from(
                    contenedor.querySelectorAll(
                        '.sesionCard'
                    )
                );

            if (
                cards.length === 0
            ) {
                preview.innerHTML = `
                    <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">
                        <p class="text-sm text-gray-500">
                            Agrega sesiones para ver cómo quedará la programación.
                        </p>
                    </div>
                `;

                return;
            }

            cards.forEach(
                (card, indice) => {
                    const nombre =
                        card
                            .querySelector(
                                '.sesionNombre'
                            )
                            .value
                            .trim()
                        || `Sesión ${indice + 1}`;

                    const inicio =
                        card
                            .querySelector(
                                '.sesionInicio'
                            )
                            .value;

                    const fin =
                        card
                            .querySelector(
                                '.sesionFin'
                            )
                            .value;

                    const ubicacion =
                        card
                            .querySelector(
                                '.sesionUbicacion'
                            )
                            .value
                            .trim();

                    const cupo =
                        card
                            .querySelector(
                                '.sesionCupo'
                            )
                            .value;

                    const reserva =
                        card
                            .querySelector(
                                '.sesionReserva'
                            )
                            .checked;

                    const obligatoria =
                        card
                            .querySelector(
                                '.sesionObligatoria'
                            )
                            .checked;

                    const bloque =
                        document.createElement(
                            'div'
                        );

                    bloque.className =
                        'rounded-xl border border-white/10 bg-white/[0.02] p-4';

                    let etiquetas =
                        '';

                    if (reserva) {
                        etiquetas += `
                            <span class="rounded-full bg-cyan-500/10 px-2 py-1 text-[10px] font-semibold text-cyan-300">
                                Requiere reserva
                            </span>
                        `;
                    }

                    if (obligatoria) {
                        etiquetas += `
                            <span class="rounded-full bg-amber-500/10 px-2 py-1 text-[10px] font-semibold text-amber-300">
                                Obligatoria
                            </span>
                        `;
                    }

                    let cupoTexto =
                        '';

                    if (cupo !== '') {
                        cupoTexto =
                            `${cupo} personas`;
                    } else if (
                        tieneInscripcionGeneral
                        && cupoGeneral !== null
                    ) {
                        cupoTexto =
                            `${cupoGeneral} personas · heredado`;
                    } else {
                        cupoTexto =
                            'Sin límite específico';
                    }

                    bloque.innerHTML = `
                        <div class="flex items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300">
                                ${indice + 1}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="break-words font-semibold text-white">
                                    ${escaparHtml(nombre)}
                                </p>

                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    ${escaparHtml(formatearFecha(inicio))}
                                </p>

                                ${fin ? `
                                    <p class="text-xs leading-5 text-gray-600">
                                        Finaliza:
                                        ${escaparHtml(formatearFecha(fin))}
                                    </p>
                                ` : ''}

                                ${ubicacion ? `
                                    <p class="mt-2 text-xs leading-5 text-gray-400">
                                        Lugar:
                                        ${escaparHtml(ubicacion)}
                                    </p>
                                ` : ''}

                                <p class="text-xs leading-5 text-gray-400">
                                    Cupo:
                                    ${escaparHtml(cupoTexto)}
                                </p>

                                ${etiquetas ? `
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        ${etiquetas}
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    `;

                    preview.appendChild(
                        bloque
                    );
                }
            );
        }

        function actualizarMensajeCantidad() {
            const cantidad =
                contenedor.querySelectorAll(
                    '.sesionCard'
                ).length;

            mensajeSinSesiones
                .classList
                .toggle(
                    'hidden',
                    cantidad > 0
                );
        }

        function actualizarSesiones() {
            contenedor
                .querySelectorAll(
                    '.sesionCard'
                )
                .forEach(card => {
                    actualizarLimitesFecha(
                        card
                    );

                    actualizarLimiteCupo(
                        card
                    );

                    resumenSesion(
                        card
                    );
                });

            actualizarMensajeCantidad();
            renderPreviewSesiones();
            renderSolapamientos();
        }

        function agregarSesion(
            datos = null,
            contraida = false
        ) {
            const indice =
                contadorSesiones++;

            const card =
                document.createElement(
                    'div'
                );

            card.className =
                'sesionCard overflow-hidden rounded-2xl border border-white/10 bg-black/10';

            card.innerHTML = `
                <input type="hidden"
                    name="sesiones[${indice}][id_sesion]"
                    value="${escaparHtml(datos?.id_sesion ?? '')}"
                    class="sesionId">

                <button type="button"
                    class="cabeceraSesion flex w-full items-center justify-between gap-3 p-4 text-left transition hover:bg-white/[0.03]">

                    <div class="min-w-0">
                        <p class="resumenSesionNombre truncate font-semibold text-white">
                            Nueva sesión
                        </p>

                        <p class="resumenSesionDetalle mt-1 truncate text-xs text-gray-500">
                            Completa la información
                        </p>
                    </div>

                    <span class="iconoSesion shrink-0 text-gray-500">
                        ⌃
                    </span>
                </button>

                <div class="sesionCuerpo border-t border-white/10 p-4">

                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <label class="mb-2 block text-sm font-medium text-gray-300">
                                Nombre de la sesión
                            </label>

                            <input type="text"
                                name="sesiones[${indice}][nombre]"
                                required
                                maxlength="150"
                                class="sesionNombre block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-cyan-500/40"
                                placeholder="Ej. Taller IoT, Ponencia inaugural, Turno de mañana">
                        </div>

                        <button type="button"
                            class="btnQuitarSesion mt-7 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-red-500/20 text-red-400 transition hover:bg-red-500/10">
                            ×
                        </button>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">
                                Fecha y hora de inicio
                            </label>

                            <input type="datetime-local"
                                name="sesiones[${indice}][fecha_inicio]"
                                required
                                class="sesionInicio block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-cyan-500/40">

                            <p class="mt-1 text-xs leading-5 text-gray-600">
                                Debe estar dentro del período general de realización.
                            </p>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">
                                Fecha y hora de finalización
                            </label>

                            <input type="datetime-local"
                                name="sesiones[${indice}][fecha_fin]"
                                class="sesionFin block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-cyan-500/40">

                            <p class="mt-1 text-xs leading-5 text-gray-600">
                                Puedes dejarla vacía cuando no necesites indicar una finalización.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">
                                Lugar
                            </label>

                            <input type="text"
                                name="sesiones[${indice}][ubicacion]"
                                maxlength="250"
                                class="sesionUbicacion block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-cyan-500/40"
                                placeholder="Ej. Auditorio, laboratorio, punto de salida">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-300">
                                Cupo de esta sesión
                            </label>

                            <input type="number"
                                name="sesiones[${indice}][cupo]"
                                min="0"
                                class="sesionCupo block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-cyan-500/40">

                            <p class="ayudaCupoSesion mt-1 text-xs leading-5 text-gray-600">
                            </p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="mb-2 block text-sm font-medium text-gray-300">
                            Enlace de acceso
                        </label>

                        <input type="url"
                            name="sesiones[${indice}][enlace_acceso]"
                            class="sesionEnlace block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none transition focus:border-cyan-500/40"
                            placeholder="Opcional · Ej. enlace de Google Meet, Zoom o plataforma virtual">

                        <p class="mt-1 text-xs leading-5 text-gray-600">
                            Déjalo vacío cuando la sesión sea presencial o no necesite un enlace.
                        </p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">

                        <div class="rounded-xl border border-white/10 p-4">
                            <input type="hidden"
                                name="sesiones[${indice}][requiere_reserva]"
                                value="0">

                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox"
                                    name="sesiones[${indice}][requiere_reserva]"
                                    value="1"
                                    class="sesionReserva mt-1">

                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-300">
                                        Requiere reserva
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-gray-600">
                                        Actívalo cuando la persona deba reservar específicamente esta sesión.
                                    </p>
                                </div>
                            </label>
                        </div>

                        <div class="rounded-xl border border-white/10 p-4">
                            <input type="hidden"
                                name="sesiones[${indice}][obligatoria]"
                                value="0">

                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox"
                                    name="sesiones[${indice}][obligatoria]"
                                    value="1"
                                    class="sesionObligatoria mt-1">

                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-300">
                                        Sesión obligatoria
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-gray-600">
                                        Actívalo cuando forme parte obligatoria de la actividad.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button type="button"
                            class="btnListoSesion rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500/15">
                            Listo
                        </button>
                    </div>
                </div>
            `;

            const nombre =
                card.querySelector(
                    '.sesionNombre'
                );

            const inicio =
                card.querySelector(
                    '.sesionInicio'
                );

            const fin =
                card.querySelector(
                    '.sesionFin'
                );

            const ubicacion =
                card.querySelector(
                    '.sesionUbicacion'
                );

            const cupo =
                card.querySelector(
                    '.sesionCupo'
                );

            const enlace =
                card.querySelector(
                    '.sesionEnlace'
                );

            const reserva =
                card.querySelector(
                    '.sesionReserva'
                );

            const obligatoria =
                card.querySelector(
                    '.sesionObligatoria'
                );

            if (datos) {
                nombre.value =
                    datos.nombre ?? '';

                inicio.value =
                    datos.fecha_inicio ?? '';

                fin.value =
                    datos.fecha_fin ?? '';

                ubicacion.value =
                    datos.ubicacion ?? '';

                cupo.value =
                    datos.cupo ?? '';

                enlace.value =
                    datos.enlace_acceso ?? '';

                reserva.checked =
                    datos.requiere_reserva
                    === true;

                obligatoria.checked =
                    datos.obligatoria
                    === true;
            }

            actualizarLimitesFecha(
                card
            );

            actualizarLimiteCupo(
                card
            );

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
                    function () {
                        if (
                            input === inicio
                        ) {
                            actualizarLimitesFecha(
                                card
                            );
                        }

                        resumenSesion(
                            card
                        );

                        renderPreviewSesiones();
                        renderSolapamientos();
                    }
                );

                input.addEventListener(
                    'change',
                    function () {
                        if (
                            input === inicio
                        ) {
                            actualizarLimitesFecha(
                                card
                            );
                        }

                        resumenSesion(
                            card
                        );

                        renderPreviewSesiones();
                        renderSolapamientos();
                    }
                );
            });

            reserva.addEventListener(
                'change',
                function () {
                    resumenSesion(
                        card
                    );

                    renderPreviewSesiones();
                }
            );

            obligatoria.addEventListener(
                'change',
                function () {
                    resumenSesion(
                        card
                    );

                    renderPreviewSesiones();
                }
            );

            card
                .querySelector(
                    '.cabeceraSesion'
                )
                .addEventListener(
                    'click',
                    function () {
                        const cuerpo =
                            card.querySelector(
                                '.sesionCuerpo'
                            );

                        if (
                            cuerpo
                                .classList
                                .contains('hidden')
                        ) {
                            expandirSesion(
                                card
                            );
                        } else {
                            contraerSesion(
                                card
                            );
                        }
                    }
                );

            card
                .querySelector(
                    '.btnListoSesion'
                )
                .addEventListener(
                    'click',
                    function () {
                        contraerSesion(
                            card
                        );

                        renderPreviewSesiones();
                        renderSolapamientos();
                    }
                );

            card
                .querySelector(
                    '.btnQuitarSesion'
                )
                .addEventListener(
                    'click',
                    function () {
                        card.remove();

                        actualizarSesiones();
                    }
                );

            contenedor.appendChild(
                card
            );

            resumenSesion(
                card
            );

            if (contraida) {
                contraerSesion(
                    card
                );
            } else {
                expandirSesion(
                    card
                );
            }

            actualizarSesiones();
        }

        function habilitarCamposMultiples(
            habilitar
        ) {
            contenedor
                .querySelectorAll(
                    'input, select, textarea'
                )
                .forEach(elemento => {
                    elemento.disabled =
                        !habilitar;
                });
        }

        function asegurarCantidadInicial() {
            const cards =
                contenedor.querySelectorAll(
                    '.sesionCard'
                );

            if (
                modoActual() !== 'multiples'
            ) {
                return;
            }

            if (
                cards.length === 0
            ) {
                agregarSesion(
                    null,
                    false
                );

                agregarSesion(
                    null,
                    true
                );

                return;
            }

            if (
                cards.length === 1
                && !cards[0]
                    .querySelector(
                        '.sesionId'
                    )
                    .value
            ) {
                agregarSesion(
                    null,
                    true
                );
            }
        }

        function actualizarModoSesiones() {
            const modo =
                modoActual();

            seccionSinSesiones
                .classList
                .toggle(
                    'hidden',
                    modo !== 'ninguna'
                );

            seccionSesionUnica
                .classList
                .toggle(
                    'hidden',
                    modo !== 'unica'
                );

            seccionMultiples
                .classList
                .toggle(
                    'hidden',
                    modo !== 'multiples'
                );

            habilitarCamposMultiples(
                modo === 'multiples'
            );

            if (
                modo === 'multiples'
            ) {
                asegurarCantidadInicial();

                habilitarCamposMultiples(
                    true
                );
            }

            if (
                modo !== 'multiples'
            ) {
                advertenciaSolapamientos
                    .classList
                    .add('hidden');

                confirmarSolapamientos.checked =
                    false;
            }

            actualizarSesiones();
        }

        document
            .querySelectorAll(
                'input[name="modo_sesiones"]'
            )
            .forEach(radio => {
                radio.addEventListener(
                    'change',
                    actualizarModoSesiones
                );
            });

        btnAgregarSesion
            ?.addEventListener(
                'click',
                function () {
                    agregarSesion(
                        null,
                        false
                    );
                }
            );

        existentes.forEach(
            sesion => {
                agregarSesion(
                    sesion,
                    true
                );
            }
        );

        formSesiones.addEventListener(
            'submit',
            function (event) {
                const modo =
                    modoActual();

                if (!modo) {
                    event.preventDefault();

                    alert(
                        'Indica cómo se desarrollará esta actividad.'
                    );

                    return;
                }

                if (
                    modo !== 'multiples'
                ) {
                    return;
                }

                const cards =
                    Array.from(
                        contenedor.querySelectorAll(
                            '.sesionCard'
                        )
                    );

                if (
                    cards.length < 2
                ) {
                    event.preventDefault();

                    alert(
                        'Si seleccionaste varias sesiones, agrega al menos dos.'
                    );

                    return;
                }

                const ahora =
                    fechaLocalAhora();

                for (
                    const card
                    of cards
                ) {
                    const idSesion =
                        card
                            .querySelector(
                                '.sesionId'
                            )
                            .value;

                    const nombre =
                        card
                            .querySelector(
                                '.sesionNombre'
                            )
                            .value
                            .trim();

                    const inicio =
                        card
                            .querySelector(
                                '.sesionInicio'
                            )
                            .value;

                    const fin =
                        card
                            .querySelector(
                                '.sesionFin'
                            )
                            .value;

                    const cupo =
                        card
                            .querySelector(
                                '.sesionCupo'
                            )
                            .value;

                    if (!nombre) {
                        event.preventDefault();

                        alert(
                            'Cada sesión debe tener un nombre.'
                        );

                        expandirSesion(
                            card
                        );

                        return;
                    }

                    if (!inicio) {
                        event.preventDefault();

                        alert(
                            `Indica la fecha y hora de inicio de "${nombre}".`
                        );

                        expandirSesion(
                            card
                        );

                        return;
                    }

                    if (
                        realizacionDesde
                        && inicio < realizacionDesde
                    ) {
                        event.preventDefault();

                        alert(
                            `"${nombre}" no puede comenzar antes del período de realización de la actividad.`
                        );

                        expandirSesion(
                            card
                        );

                        return;
                    }

                    if (
                        realizacionHasta
                        && inicio > realizacionHasta
                    ) {
                        event.preventDefault();

                        alert(
                            `"${nombre}" debe comenzar dentro del período de realización de la actividad.`
                        );

                        expandirSesion(
                            card
                        );

                        return;
                    }

                    if (
                        !idSesion
                        && inicio < ahora
                    ) {
                        event.preventDefault();

                        alert(
                            `No puedes crear "${nombre}" con una fecha de inicio que ya pasó.`
                        );

                        expandirSesion(
                            card
                        );

                        return;
                    }

                    if (
                        fin
                        && fin < inicio
                    ) {
                        event.preventDefault();

                        alert(
                            `La finalización de "${nombre}" no puede ser anterior a su inicio.`
                        );

                        expandirSesion(
                            card
                        );

                        return;
                    }

                    if (
                        fin
                        && realizacionHasta
                        && fin > realizacionHasta
                    ) {
                        event.preventDefault();

                        alert(
                            `"${nombre}" debe finalizar dentro del período de realización de la actividad.`
                        );

                        expandirSesion(
                            card
                        );

                        return;
                    }

                    if (
                        cupo !== ''
                        && Number(cupo) < 0
                    ) {
                        event.preventDefault();

                        alert(
                            `El cupo de "${nombre}" no puede ser negativo.`
                        );

                        expandirSesion(
                            card
                        );

                        return;
                    }

                    if (
                        tieneInscripcionGeneral
                        && cupoGeneral !== null
                        && cupo !== ''
                        && Number(cupo) > cupoGeneral
                    ) {
                        event.preventDefault();

                        alert(
                            `El cupo de "${nombre}" no puede superar el cupo general de ${cupoGeneral} personas.`
                        );

                        expandirSesion(
                            card
                        );

                        return;
                    }
                }

                const conflictos =
                    renderSolapamientos();

                if (
                    conflictos.length > 0
                    && !confirmarSolapamientos.checked
                ) {
                    event.preventDefault();

                    advertenciaSolapamientos
                        .scrollIntoView({
                            behavior: 'smooth',
                            block: 'center',
                        });

                    alert(
                        'Hay sesiones que coinciden en horario. Confirma que se realizarán simultáneamente para continuar.'
                    );
                }
            }
        );

        actualizarModoSesiones();
    }
});
</script>
@endsection