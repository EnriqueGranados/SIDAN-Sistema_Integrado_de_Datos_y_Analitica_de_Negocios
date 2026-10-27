@extends('layouts.navbars')

@section('title', 'Gestión de pagos')

@section('content')
    <style>
        /* Únicamente presentación. No modifica formularios ni acciones Laravel. */
        #gestion-pagos [x-cloak] { display: none !important; }
        #gestion-pagos .pago-card { box-shadow: 0 6px 24px rgb(15 23 42 / .04); }
        #gestion-pagos button:focus-visible,
        #gestion-pagos input:focus-visible,
        #gestion-pagos textarea:focus-visible,
        #gestion-pagos select:focus-visible,
        #gestion-pagos a:focus-visible,
        #gestion-pagos summary:focus-visible { outline: 2px solid #22c55e; outline-offset: 2px; }
        /* El modal se teletransporta a <body>: ningún padre lo recorta. */
    </style>
<div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
    <div id="gestion-pagos" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                    Administración
                </p>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 dark:text-white">
                    Gestión de pagos
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Revisá transferencias y confirmá el efectivo recibido.
                </p>
            </div>
        </div>
        @if ($errors->any())
            <div role="alert" class="rounded-xl bg-red-50 p-4 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-300">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Cifras acumuladas del libro de cobros; NO dependen del estado o
             método seleccionado para la tabla. Solo se acotan por actividad
             una vez que se pulsa «Aplicar filtros». --}}
        <section aria-labelledby="titulo-resumen-financiero" class="space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="titulo-resumen-financiero" class="text-base font-bold text-slate-900 dark:text-white">Resumen financiero</h2>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 dark:bg-white/10 dark:text-slate-300">
                    Histórico acumulado · {{ $actividadSeleccionada ? ($actividadesFiltro->firstWhere('id_actividad', $actividadSeleccionada)?->nombre ?? 'Actividad elegida') : 'Todas las actividades' }}
                </span>
            </div>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-sidan-200 bg-sidan-50 p-4 dark:border-sidan-500/30 dark:bg-sidan-500/10">
                    <p class="text-xs font-semibold text-sidan-500 dark:text-sidan-300">Dinero recaudado</p>
                    <p class="mt-2 text-2xl font-black text-sidan-500 dark:text-sidan-300">${{ number_format((float) $resumenFinanciero['recaudado'], 2) }}</p>
                    <p class="mt-1 text-xs text-slate-600 text-sidan-500 dark:text-slate-300">Efectivo y transferencias verificados, más Wompi confirmado.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-slate-900">
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Ingresos por ventas realizadas</p>
                    <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">${{ number_format((float) $resumenFinanciero['ventas_realizadas'], 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Productos entregados y conceptos con confirmación financiera.</p>
                </div>
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                    <p class="text-xs font-semibold text-amber-900 dark:text-amber-200">Cobros pendientes de reconocer</p>
                    <p class="mt-2 text-2xl font-black text-amber-900 dark:text-amber-200">${{ number_format((float) $resumenFinanciero['por_reconocer'], 2) }}</p>
                    <p class="mt-1 text-xs text-amber-900/80 dark:text-amber-200/90">Dinero recibido todavía no reconocido como venta o servicio.</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-slate-800/70">
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">Saldo de órdenes activas</p>
                    <p class="mt-2 text-2xl font-black text-slate-900 dark:text-white">${{ number_format((float) $resumenFinanciero['saldo_activo'], 2) }}</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Aún no cobrado; excluye órdenes vencidas.</p>
                </div>
            </div>
            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 text-xs text-slate-600 dark:border-white/10 dark:bg-slate-900 dark:text-slate-300 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-x-6 gap-y-2">
                    <span>Saldo en regularización presencial: <strong class="text-slate-900 dark:text-white">${{ number_format((float) $resumenFinanciero['saldo_regularizacion'], 2) }}</strong></span>
                    <span>Ventas de productos entregados: <strong class="text-slate-900 dark:text-white">${{ number_format((float) $resumenFinanciero['ventas_productos'], 2) }}</strong></span>
                </div>
                <p class="max-w-sm leading-5">El dinero recaudado <strong>no se suma</strong> otra vez al ingreso realizado. Inscripciones y servicios requieren verificar cuándo se prestan.</p>
            </div>
            @if($resumenFinanciero['descuadre'])
                <div role="alert" class="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-xs text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                    Revisá la conciliación: los importes reconocidos superan el dinero recaudado. Podría haber ventas históricas, promociones u otros cobros que requieren análisis.
                </div>
            @endif
            <a href="{{ route('admin.ordenes.vencidas', $actividadSeleccionada ? ['actividad' => $actividadSeleccionada] : []) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-white transition hover:bg-amber-600 dark:bg-amber-500 dark:hover:bg-amber-600">
                <span>Órdenes vencidas con dinero recibido</span>
                <span class="rounded-full bg-white/30 px-2 py-0.5 text-xs">{{ $resumenFinanciero['vencidas_revision'] }} en regularización</span>
            </a>
        </section>
        <section aria-labelledby="titulo-filtros"
            x-data="{
                actividadesAbiertas: false,
                actividadElegidaId: @js($actividadSeleccionada),
                actividadElegida: @js($actividadSeleccionada > 0 ? ($actividadesFiltro->firstWhere('id_actividad', $actividadSeleccionada)?->nombre ?? 'Actividad seleccionada') : 'Todas las actividades, incluso las que ya finalizaron.'),
                cambioActividadSinAplicar: false
            }"
            class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900 mt-5 mb-5">
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="titulo-filtros" class="font-bold text-slate-900 dark:text-white">Filtrar órdenes y pagos</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Los filtros se combinan y se aplican a todo el historial, incluidas actividades finalizadas.</p>
                </div>
                <span class="rounded-full bg-sidan-50 px-3 py-1 text-xs font-semibold text-sidan-600 dark:bg-sidan-500/10 dark:text-sidan-300">
                    {{ $pagos->total() }} {{ $pagos->total() === 1 ? 'resultado' : 'resultados' }}
                </span>
            </div>

            <form method="GET" action="{{ route('admin.pagos.index') }}" class="space-y-4">
                {{-- La actividad es una selección única, no depende de los períodos de venta. --}}
                <div class="rounded-xl border border-slate-200 p-3 dark:border-white/10 sm:p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Actividad</label>
                            <p class="mt-1 break-words text-sm font-semibold text-slate-700 dark:text-slate-200"
                                x-text="actividadElegida" aria-live="polite">
                                {{ $actividadSeleccionada > 0 ? ($actividadesFiltro->firstWhere('id_actividad', $actividadSeleccionada)?->nombre ?? 'Actividad seleccionada') : 'Todas las actividades, incluso las que ya finalizaron.' }}
                            </p>
                            <p x-show="cambioActividadSinAplicar" x-cloak
                                class="mt-1 text-xs font-medium text-amber-700 dark:text-amber-300" aria-live="polite">
                                Actividad seleccionada. Presioná «Aplicar filtros» para actualizar los resultados.
                            </p>
                        </div>
                        <button type="button" @click="actividadesAbiertas = !actividadesAbiertas"
                            :aria-expanded="actividadesAbiertas"
                            aria-controls="filtro-actividades-lista"
                            class="inline-flex items-center gap-2 rounded-xl border border-sidan-500 px-4 py-2.5 text-sm font-black text-sidan-600 transition hover:bg-sidan-500 hover:text-white dark:text-sidan-300 dark:hover:text-white">
                            <span x-text="actividadesAbiertas ? 'Ocultar actividades' : (actividadElegidaId ? 'Cambiar actividad' : 'Seleccionar actividad')"></span>
                            <svg class="h-4 w-4 transition-transform" :class="actividadesAbiertas ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                    </div>
                    <div id="filtro-actividades-lista" x-cloak x-show="actividadesAbiertas" x-transition
                        class="mt-4 max-h-[380px] space-y-2 overflow-y-auto border-t border-slate-100 pt-4 dark:border-white/10">
                        <label class="block cursor-pointer">
                            <input type="radio" name="actividad" value="" data-nombre="Todas las actividades, incluso las que ya finalizaron."
                                class="peer sr-only" @checked($actividadSeleccionada === 0)
                                @change="actividadElegidaId = 0; actividadElegida = $event.target.dataset.nombre; cambioActividadSinAplicar = true; actividadesAbiertas = false">
                            <span class="flex items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 text-sm font-semibold text-slate-700 transition peer-checked:border-sidan-500 peer-checked:bg-sidan-50 peer-focus-visible:ring-2 peer-focus-visible:ring-sidan-500/30 dark:border-white/10 dark:text-slate-200 dark:peer-checked:bg-sidan-500/10">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-sidan-600 dark:bg-white/10 dark:text-sidan-300" aria-hidden="true">✦</span>
                                Todas las actividades
                            </span>
                        </label>
                        @forelse ($actividadesFiltro as $actividadFiltro)
                            @php
                                $rutaPortada = str_replace('\\', '/', trim((string) ($actividadFiltro->portada?->url ?? '')));
                                $imagenPortada = null;
                                if ($rutaPortada !== '') {
                                    if (\Illuminate\Support\Str::startsWith($rutaPortada, ['https://', 'http://', 'data:'])) {
                                        $imagenPortada = $rutaPortada;
                                    } else {
                                        $rutaPortada = ltrim($rutaPortada, '/');
                                        if (\Illuminate\Support\Str::startsWith($rutaPortada, 'public/')) $rutaPortada = substr($rutaPortada, 7);
                                        $imagenPortada = asset(\Illuminate\Support\Str::startsWith($rutaPortada, 'storage/') ? $rutaPortada : 'storage/' . $rutaPortada);
                                    }
                                }
                                $finalizada = $actividadFiltro->realizacion_hasta
                                    ? $actividadFiltro->realizacion_hasta->isPast()
                                    : ($actividadFiltro->realizacion_desde && $actividadFiltro->realizacion_desde->isPast());
                            @endphp
                            <label class="block cursor-pointer">
                                <input type="radio" name="actividad" value="{{ $actividadFiltro->id_actividad }}"
                                    data-nombre="{{ $actividadFiltro->nombre }}"
                                    class="peer sr-only" @checked($actividadSeleccionada === (int) $actividadFiltro->id_actividad)
                                    @change="actividadElegidaId = Number($event.target.value); actividadElegida = $event.target.dataset.nombre; cambioActividadSinAplicar = true; actividadesAbiertas = false">
                                <span class="flex min-w-0 items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-sidan-400 hover:bg-sidan-50/40 peer-checked:border-sidan-500 peer-checked:bg-sidan-50 peer-focus-visible:ring-2 peer-focus-visible:ring-sidan-500/30 dark:border-white/10 dark:hover:bg-sidan-500/5 dark:peer-checked:bg-sidan-500/10">
                                    @if ($imagenPortada)
                                        <img src="{{ $imagenPortada }}" alt="Portada de {{ $actividadFiltro->nombre }}" loading="lazy" class="h-16 w-16 shrink-0 rounded-lg object-cover sm:h-20 sm:w-20">
                                    @else
                                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-sidan-900 text-sidan-300 sm:h-20 sm:w-20" aria-hidden="true">✦</span>
                                    @endif
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="text-[11px] font-bold uppercase tracking-wide text-sidan-600 dark:text-sidan-400">{{ $actividadFiltro->categoria?->nombre ?? 'Sin categoría' }}</span>
                                            @if ($finalizada)
                                                <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">Finalizada</span>
                                            @elseif ($actividadFiltro->trashed())
                                                <span class="rounded-full bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">Archivada</span>
                                            @else
                                                <span class="rounded-full bg-sidan-50 px-2 py-1 text-[10px] font-bold text-sidan-700 dark:bg-sidan-500/10 dark:text-sidan-300">Registrada</span>
                                            @endif
                                        </span>
                                        <strong class="mt-1 block break-words text-sm text-slate-900 dark:text-white">{{ $actividadFiltro->nombre }}</strong>
                                        <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">
                                            {{ $actividadFiltro->realizacion_hasta?->copy()->timezone('America/El_Salvador')->format('d/m/Y') ?? 'Sin fecha de cierre configurada' }}
                                        </span>
                                    </span>
                                </span>
                            </label>
                        @empty
                            <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No hay actividades registradas.</p>
                        @endforelse
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="filtro-estado" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Estado de pago</label>
                        <select id="filtro-estado" name="estado" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white">
                            <option value="">Todos los estados</option>
                            @foreach ($estadosPagos as $estado => $cantidad)
                                <option value="{{ $estado }}" @selected($estadoSeleccionado === $estado)>{{ str_replace('_', ' ', ucfirst($estado)) }} ({{ $cantidad }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filtro-metodo" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Método de pago</label>
                        <select id="filtro-metodo" name="metodo" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white">
                            <option value="">Todos los métodos</option>
                            @foreach ($metodosPagos as $metodo)
                                <option value="{{ $metodo }}" @selected($metodoSeleccionado === $metodo)>{{ ucfirst($metodo) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filtro-situacion" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Situación de la orden</label>
                        <select id="filtro-situacion" name="situacion" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white">
                            <option value="">Todas las situaciones</option>
                            <option value="vencidas" @selected($situacionSeleccionada === 'vencidas')>Vencidas</option>
                            <option value="revision_pendiente" @selected($situacionSeleccionada === 'revision_pendiente')>Abono pendiente de revisión</option>
                            <option value="saldo_pendiente" @selected($situacionSeleccionada === 'saldo_pendiente')>Saldo pendiente de pago</option>
                        </select>
                    </div>
                    <div>
                        <label for="filtro-entrega" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Entrega de productos</label>
                        <select id="filtro-entrega" name="entrega" class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white">
                            <option value="">Todas las entregas</option>
                            <option value="pendientes" @selected($entregaSeleccionada === 'pendientes')>Con productos no entregados</option>
                            <option value="completadas" @selected($entregaSeleccionada === 'completadas')>Todos los productos entregados</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-slate-500 dark:text-slate-400">«No entregados» incluye productos con saldo pendiente; el pago se muestra por separado.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($hayFiltros)
                            <a href="{{ route('admin.pagos.index') }}" class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-white/20 dark:text-slate-200 dark:hover:bg-white/5">Limpiar filtros</a>
                        @endif
                        <button type="submit" class="rounded-xl bg-sidan-500 px-5 py-3 text-sm font-bold text-white hover:bg-sidan-600">Aplicar filtros</button>
                    </div>
                </div>
            </form>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500 dark:text-slate-400">
            <p class="font-semibold text-slate-700 dark:text-slate-200">
                {{ $hayFiltros ? 'Resultados filtrados' : 'Todos los pagos' }}
            </p>
            <p>Mostrando {{ $pagos->firstItem() ?? 0 }}–{{ $pagos->lastItem() ?? 0 }} de {{ $pagos->total() }}</p>
        </div>
        {{-- Lista compacta: una sola orden expandida a la vez. --}}
        <section class="space-y-3 mt-5 mb-5" aria-label="Lista de órdenes y pagos" x-data="{ abierta: null }">
            @forelse ($pagos as $pago)
                @php
                    $ordenPago = $pago->orden;
                    $manual = in_array($pago->proveedor, ['transferencia', 'efectivo'], true);
                    $abonoPendiente = $manual ? $pago->abonos->firstWhere('estado', 'pendiente') : null;
                    $totalCentavos = (int) round((float) $pago->monto * 100);
                    // Wompi confirmado está pagado aunque no tenga abonos manuales.
                    $acreditado = $manual ? $pago->montoAcreditadoCentavos() : ($pago->estado === 'confirmado' ? $totalCentavos : 0);
                    $saldo = $ordenPago?->estado === 'cancelada' ? 0 : max(0, $totalCentavos - $acreditado);
                    $esProducto = $ordenPago?->tipo_orden === 'productos';
                    $esAnticipo = $manual && $esProducto && $ordenPago?->modalidad_pago === 'anticipo';
                    $minimoInicialCentavos = $esAnticipo ? (int) round((float) $ordenPago->minimo_inicial * 100) : $totalCentavos;
                    $faltanteMinimoCentavos = $esAnticipo
                        ? app(\App\Services\AbonosProductosService::class)->faltanteAnticipo($ordenPago)
                        : 0;
                    // El estado del pago es distinto al del vencimiento de la orden.
                    $ordenVencida = $ordenPago?->estado === 'vencida'
                        || ($ordenPago?->estado === 'pendiente_pago'
                            && (($ordenPago?->limite_saldo_en?->isPast() ?? false)
                                || ($ordenPago?->expira_en?->isPast() ?? false)));
                    $revisable = $manual
                        && $ordenPago?->estado === 'pendiente_pago'
                        && in_array($pago->estado, ['pendiente', 'parcial'], true)
                        && $abonoPendiente;
                    $estadoColor = match ($pago->estado) {
                        'confirmado' => 'bg-sidan-50 text-sidan-500 dark:bg-sidan-500/10 dark:text-sidan-400',
                        'parcial', 'pendiente' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300',
                        'rechazado', 'cancelado', 'fallido' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300',
                        default => 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300',
                    };
                @endphp

                <article class="pago-card overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900"
                    x-data="{ modal: null, enviando: false, montoNuevo: '' }">
                    {{-- Toda esta fila es el activador, sin controles anidados. --}}
                    <button type="button" id="cabecera-pago-{{ $pago->id_pago }}"
                        class="flex w-full flex-wrap items-center gap-3 px-4 py-4 text-left transition hover:bg-slate-50 dark:hover:bg-white/[0.04] sm:gap-4 sm:px-5"
                        @click="abierta = abierta === {{ (int) $pago->id_pago }} ? null : {{ (int) $pago->id_pago }}"
                        :aria-expanded="abierta === {{ (int) $pago->id_pago }}"
                        aria-controls="panel-pago-{{ $pago->id_pago }}">
                        <svg class="h-5 w-5 shrink-0 text-slate-500 transition-transform duration-200 dark:text-slate-300"
                            :class="abierta === {{ (int) $pago->id_pago }} ? 'rotate-90' : ''"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                        </svg>
                        <div class="h-10 w-1 shrink-0 rounded-full bg-sidan-500"></div>
                        <div class="min-w-0 flex-1 sm:basis-[290px]">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-bold text-slate-900 dark:text-white">Orden #{{ $pago->id_orden }}</h2>
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                    {{ ucfirst($pago->proveedor) }}
                                </span>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $estadoColor }}">
                                    {{ str_replace('_', ' ', ucfirst($pago->estado)) }}
                                </span>
                                @if($ordenPago?->estado === 'cancelada')
                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 dark:bg-red-500/10 dark:text-red-300">Orden cancelada</span>
                                @endif
                                @if($ordenVencida)
                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 dark:bg-red-500/10 dark:text-red-300">
                                        {{ $ordenPago?->estado === 'vencida' ? 'Orden vencida' : ($pago->proveedor === 'transferencia' && $abonoPendiente ? 'Plazo vencido · por conciliar' : 'Plazo vencido · sin cerrar') }}
                                    </span>
                                @endif
                                @if($ordenPago?->cierre_financiero_estado === 'pendiente_revision')
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800 dark:bg-amber-500/10 dark:text-amber-200">Revisión financiera pendiente</span>
                                @elseif($ordenPago?->cierre_financiero_estado === 'autorizado')
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">Cierre autorizado</span>
                                @endif
                            </div>
                            <p class="mt-1 truncate text-xs font-medium text-slate-600 dark:text-slate-300">
                                {{ $ordenPago?->actividad?->nombre ?? 'Actividad no disponible' }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">
                                {{ $ordenPago?->correo_contacto ?? 'Sin correo de contacto' }}
                            </p>
                        </div>
                        <div class="grid w-full shrink-0 grid-cols-3 gap-3 border-t border-slate-100 pt-3 text-right dark:border-white/10 sm:w-auto sm:min-w-[310px] sm:border-0 sm:pt-0">
                            <div>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">Total</span>
                                <strong class="text-sm text-slate-900 dark:text-white">${{ number_format($totalCentavos / 100, 2) }}</strong>
                            </div>
                            <div>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">Acreditado</span>
                                <strong class="text-sm text-sidan-600 dark:text-sidan-400">${{ number_format($acreditado / 100, 2) }}</strong>
                            </div>
                            <div>
                                <span class="block text-[11px] text-slate-500 dark:text-slate-400">Pendiente</span>
                                <strong class="text-sm {{ $saldo > 0 ? 'text-amber-700 dark:text-amber-300' : 'text-slate-900 dark:text-white' }}">${{ number_format($saldo / 100, 2) }}</strong>
                            </div>
                        </div>
                    </button>

                    <div id="panel-pago-{{ $pago->id_pago }}" x-cloak x-show="abierta === {{ (int) $pago->id_pago }}"
                        role="region" aria-labelledby="cabecera-pago-{{ $pago->id_pago }}"
                        class="border-t border-slate-100 px-4 pb-5 pt-4 dark:border-white/10 sm:px-5">

                        @if($ordenPago && app(\App\Services\GestionOrdenService::class)->puedeCancelar($ordenPago))
                            <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50/60 p-3 dark:border-red-500/20 dark:bg-red-500/5">
                                <p class="text-sm text-red-800 dark:text-red-200">Sin abonos acreditados ni comprobantes presentados. Podés cancelar la orden y conservar el historial.</p>
                                <button type="button" onclick="document.getElementById('admin-cancelar-{{ $ordenPago->id_orden }}').showModal()" class="shrink-0 rounded-lg border border-red-400 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100 dark:text-red-300">Cancelar orden</button>
                            </div>
                            <dialog id="admin-cancelar-{{ $ordenPago->id_orden }}" class="w-[calc(100%-2rem)] max-w-md rounded-2xl border border-slate-200 bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-black/60 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                                <div class="border-b border-slate-200 p-5 dark:border-white/10">
                                    <h3 class="text-lg font-black">Cancelar orden #{{ $ordenPago->id_orden }}</h3>
                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Se conservarán el total, los conceptos y el historial. Su saldo dejará de contabilizarse como cartera pendiente.</p>
                                </div>
                                <form method="POST" action="{{ route('admin.pagos.cancelar-orden', $ordenPago) }}" class="space-y-4 p-5">
                                    @csrf @method('DELETE')
                                    <label class="block text-sm"><strong>Motivo de cancelación</strong>
                                        <textarea name="motivo" required minlength="8" maxlength="300" rows="3" class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white" placeholder="El cliente solicitó cancelar sin haber realizado pagos."></textarea>
                                    </label>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" onclick="this.closest('dialog').close()" class="rounded-xl border border-slate-300 px-4 py-2 font-bold dark:border-white/20">Volver</button>
                                        <button type="submit" class="rounded-xl bg-red-600 px-4 py-2 font-bold text-white hover:bg-red-700">Confirmar cancelación</button>
                                    </div>
                                </form>
                            </dialog>
                        @endif
                        @if($ordenPago?->estado === 'cancelada')
                            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200">
                                <strong>Orden cancelada · Sin saldo exigible.</strong>
                                <p class="mt-1">{{ $ordenPago->motivo_cancelacion ?: 'Se conserva el historial de esta operación.' }}</p>
                            </div>
                        @endif
                        @if($ordenVencida)
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                                <span>Esta orden venció. Sus pagos acreditados permanecen registrados; el saldo que nunca se pagó no es ingreso.</span>
                                @if($ordenPago?->cierre_financiero_estado === 'pendiente_revision')
                                    <a class="shrink-0 rounded-lg bg-amber-500 px-3 py-2 text-xs font-bold text-white hover:bg-amber-600" href="{{ route('admin.ordenes.vencidas', ['actividad' => $ordenPago?->id_actividad]) }}">Revisar cierre</a>
                                @elseif($manual && $acreditado > 0 && $ordenPago?->estado === 'pendiente_pago')
                                    <a class="shrink-0 rounded-lg bg-amber-500 px-3 py-2 text-xs font-bold text-white hover:bg-amber-600"
                                       href="{{ route('admin.ordenes.vencidas', ['vista' => 'conciliacion', 'actividad' => $ordenPago?->id_actividad]) }}">Ver conciliación</a>
                                @endif
                            </div>
                        @endif

                        @if ($esProducto && $manual && $ordenPago?->estado !== 'cancelada')
                            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                                @if ($esAnticipo)
                                    <strong>Anticipo mínimo: ${{ number_format($minimoInicialCentavos / 100, 2) }}</strong>
                                    <span class="ml-1">· {{ $faltanteMinimoCentavos > 0
                                        ? 'Faltan $' . number_format($faltanteMinimoCentavos / 100, 2) . ' para cubrirlo.'
                                        : 'Anticipo cubierto.' }}</span>
                                @else
                                    <strong>Modalidad: pago completo.</strong>
                                    <span class="ml-1">{{ $saldo > 0 ? 'Faltan $' . number_format($saldo / 100, 2) . ' para completar la orden.' : 'Importe cubierto.' }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-white/5">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Total de la orden</p>
                                <p class="mt-1 font-bold text-slate-900 dark:text-white">${{ number_format($totalCentavos / 100, 2) }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-white/5">
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $esAnticipo ? 'Anticipo requerido' : 'Anticipo' }}</p>
                                <p class="mt-1 font-bold text-slate-900 dark:text-white">${{ number_format($esAnticipo ? $minimoInicialCentavos / 100 : 0, 2) }}</p>
                                @unless($esAnticipo)
                                    <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">No aplica</p>
                                @endunless
                            </div>
                            <div class="rounded-xl bg-sidan-50 p-3 dark:bg-sidan-500/10">
                                <p class="text-xs text-sidan-700 dark:text-sidan-300">Dinero acreditado</p>
                                <p class="mt-1 font-bold text-sidan-600 dark:text-sidan-300">${{ number_format($acreditado / 100, 2) }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3 dark:bg-white/5">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Saldo por pagar</p>
                                <p class="mt-1 font-bold text-slate-900 dark:text-white">${{ number_format($saldo / 100, 2) }}</p>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="min-w-0 rounded-xl border border-slate-200 p-4 dark:border-white/10">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <h3 class="font-bold text-slate-900 dark:text-white">Productos y detalles</h3>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Solicitud: {{ $pago->created_at?->copy()->timezone('America/El_Salvador')->format('d/m/Y H:i') ?? '—' }}</span>
                                </div>
                                <div class="mt-3 space-y-3">
                                    @forelse ($ordenPago?->detalles ?? [] as $detalle)
                                        @php
                                            $dist = $esProducto ? app(\App\Services\AbonosProductosService::class) : null;
                                            $pagadoDetalle = $dist ? $dist->aplicado($detalle) : 0;
                                            $netoDetalle = $dist ? $dist->neto($detalle) : 0;
                                            $autorizarEntrega = $esProducto && (
                                                $pagadoDetalle >= $netoDetalle
                                                || ($ordenPago?->estado === 'pagada' && $pago->estado === 'confirmado')
                                            );
                                            $pendienteEntrega = $esProducto && (int) $detalle->cantidad_entregada < (int) $detalle->cantidad;
                                        @endphp
                                        <div class="rounded-lg bg-slate-50 p-3 text-sm dark:bg-white/5">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="break-words font-semibold text-slate-900 dark:text-white">{{ $detalle->nombre_concepto }} × {{ $detalle->cantidad }}</p>
                                                    @if ((float) $detalle->descuento > 0)
                                                        <p class="mt-1 text-xs text-sidan-600 dark:text-sidan-400">Descuento: ${{ number_format((float) $detalle->descuento, 2) }}</p>
                                                    @endif
                                                    @if ($esProducto)
                                                        <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Pagado: ${{ number_format($pagadoDetalle / 100, 2) }} / ${{ number_format($netoDetalle / 100, 2) }}</p>
                                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Entregado: {{ $detalle->cantidad_entregada }}/{{ $detalle->cantidad }}</p>
                                                    @endif
                                                </div>
                                                <span class="shrink-0 font-bold text-slate-900 dark:text-white">${{ number_format((float) $detalle->subtotal - (float) $detalle->descuento, 2) }}</span>
                                            </div>
                                            @if ($pendienteEntrega)
                                                @if ($autorizarEntrega)
                                                    <button type="button" @click="enviando = false; modal = 'entrega-{{ (int) $detalle->id_detalle_orden }}'"
                                                        class="mt-3 inline-flex items-center gap-2 rounded-lg bg-sidan-500 px-3 py-2 text-xs font-bold text-white hover:bg-sidan-600">
                                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h11v10H3zM14 10h4l3 3v4h-7zM7 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/></svg>
                                                        Marcar como entregado
                                                    </button>
                                                @else
                                                    <p class="mt-2 text-xs font-semibold text-amber-700 dark:text-amber-300">Entrega bloqueada: falta completar el pago de este producto.</p>
                                                @endif
                                            @else
                                                @if ($esProducto)
                                                    <span class="mt-2 inline-flex rounded-full bg-sidan-50 px-2 py-1 text-xs font-semibold text-sidan-700 dark:bg-sidan-500/10 dark:text-sidan-300">Entregado</span>
                                                @endif
                                            @endif
                                        </div>

                                        @if ($pendienteEntrega && $autorizarEntrega)
                                            <template x-teleport="body">
                                                <div x-cloak x-show="modal === 'entrega-{{ (int) $detalle->id_detalle_orden }}'" @keydown.escape.window="modal = null"
                                                    class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-slate-950/70 p-4 backdrop-blur-sm"
                                                    @click.self="modal = null" role="dialog" aria-modal="true" aria-labelledby="titulo-entrega-{{ $detalle->id_detalle_orden }}">
                                                    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-slate-900">
                                                        <h3 id="titulo-entrega-{{ $detalle->id_detalle_orden }}" class="text-lg font-bold text-slate-900 dark:text-white">¿Marcar como entregado?</h3>
                                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Confirmá que entregaste físicamente todas las unidades pendientes de <strong>{{ $detalle->nombre_concepto }}</strong> ({{ (int) $detalle->cantidad - (int) $detalle->cantidad_entregada }} unidad/es). Se guardará el movimiento en el historial.</p>
                                                        <form method="POST" action="{{ route('admin.ordenes.entregar', [$ordenPago, $detalle]) }}" class="mt-5 space-y-4" @submit="enviando = true">
                                                            @csrf
                                                            <div>
                                                                <label for="entrega-obs-{{ $detalle->id_detalle_orden }}" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Observación (opcional)</label>
                                                                <textarea id="entrega-obs-{{ $detalle->id_detalle_orden }}" name="observacion" maxlength="1000" rows="2" placeholder="Detalle de la entrega"
                                                                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white"></textarea>
                                                            </div>
                                                            <div class="flex justify-end gap-3">
                                                                <button type="button" @click="modal = null" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-white/20 dark:text-slate-200">Cancelar</button>
                                                                <button type="submit" :disabled="enviando" class="rounded-xl bg-sidan-500 px-4 py-2 text-sm font-bold text-white hover:bg-sidan-600 disabled:opacity-50">Confirmar entrega</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </template>
                                        @endif
                                    @empty
                                        <p class="text-sm text-slate-500 dark:text-slate-400">No hay detalles de productos disponibles.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="min-w-0 rounded-xl border border-slate-200 p-4 dark:border-white/10">
                                <h3 class="font-bold text-slate-900 dark:text-white">Historial y acciones</h3>
                                @if($ordenPago)
                                    <a href="{{ route('admin.pagos.orden.recibo', $ordenPago->id_orden) }}" class="mt-3 inline-flex rounded-xl border border-sidan-500/30 bg-sidan-500/5 px-3 py-2 text-xs font-bold text-sidan-600 hover:bg-sidan-500/10 dark:text-sidan-400">Descargar resumen PDF</a>
                                @endif
                                @if ($manual)
                                    <details class="mt-3 rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                                        <summary class="cursor-pointer text-sm font-semibold text-slate-700 dark:text-slate-200">Ver comprobantes y abonos ({{ $pago->abonos->count() }})</summary>
                                        <div class="mt-3 space-y-2">
                                            @forelse ($pago->abonos as $abono)
                                                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 py-2 text-xs dark:border-white/10">
                                                    <div>
                                                        <span class="font-semibold text-slate-900 dark:text-white">Abono #{{ $abono->id_abono }} · {{ ucfirst($abono->estado) }}</span>
                                                        @if ($abono->estado === 'confirmado')
                                                            <span class="ml-1 text-sidan-600 dark:text-sidan-400">${{ number_format((float) $abono->monto_recibido, 2) }}</span>
                                                        @endif
                                                        @if ($abono->observacion_revision)
                                                            <p class="mt-1 text-slate-500 dark:text-slate-400">{{ $abono->observacion_revision }}</p>
                                                        @endif
                                                    </div>
                                                    @if ($abono->estado === 'confirmado')
                                                        <a href="{{ route('admin.pagos.abono.recibo', [$ordenPago->id_orden, $abono->id_abono]) }}" class="font-semibold text-sidan-600 underline dark:text-sidan-400">Recibo PDF</a>
                                                    @endif
                                                    @if ($abono->comprobante_path)
                                                        <a href="{{ route('admin.pagos.abonos.comprobante', $abono) }}" target="_blank" rel="noopener" class="font-semibold text-blue-600 underline dark:text-blue-400">Ver comprobante</a>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="text-xs text-slate-500 dark:text-slate-400">Todavía no hay abonos registrados.</p>
                                            @endforelse
                                        </div>
                                    </details>
                                @endif

                                @if ($revisable)
                                    {{-- Efectivo: registrar recepción; transferencia: registrar o rechazar comprobante. --}}
                                    <div class="mt-4 grid gap-3 {{ $pago->proveedor === 'transferencia' ? 'sm:grid-cols-2' : 'sm:grid-cols-1' }}">
                                        <button type="button" @click="enviando = false; montoNuevo = ''; modal = 'registro'"
                                            class="flex min-h-[54px] items-center justify-center gap-2 rounded-xl bg-sidan-500 px-3 py-3 text-sm font-bold text-white hover:bg-sidan-600">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 12h14"/></svg>
                                            Registrar dinero
                                        </button>
                                        @if ($pago->proveedor === 'transferencia')
                                            <button type="button" @click="enviando = false; modal = 'rechazo'"
                                                class="flex min-h-[54px] items-center justify-center gap-2 rounded-xl border border-red-300 px-3 py-3 text-sm font-bold text-red-700 hover:bg-red-50 dark:border-red-500/40 dark:text-red-300 dark:hover:bg-red-500/10">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m18 6-12 12M6 6l12 12"/></svg>
                                                Rechazar intento
                                            </button>
                                        @endif
                                    </div>
                                @elseif ($manual && $pago->estado === 'parcial' && $saldo > 0)
                                    <p class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-900 dark:bg-amber-500/10 dark:text-amber-200">
                                        @if($ordenVencida && $abonoPendiente)
                                            Orden vencida con comprobante aún pendiente de conciliación. Revisalo desde «Órdenes vencidas con dinero recibido».
                                        @else
                                            No hay un abono disponible para revisar en esta orden. Saldo no recibido: ${{ number_format($saldo / 100, 2) }}.
                                        @endif
                                    </p>
                                @endif
                                @if ($pago->observacion_revision)
                                    <p class="mt-3 text-xs text-red-700 dark:text-red-300">{{ $pago->observacion_revision }}</p>
                                @endif
                                @if ($pago->fecha_confirmacion)
                                    <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">Confirmado: {{ $pago->fecha_confirmacion->copy()->timezone('America/El_Salvador')->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($revisable)
                        <template x-teleport="body">
                            <div x-cloak x-show="modal === 'registro'" @keydown.escape.window="modal = null"
                                class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-slate-950/70 p-4 backdrop-blur-sm"
                                @click.self="modal = null" role="dialog" aria-modal="true" aria-labelledby="titulo-registro-{{ $pago->id_pago }}">
                                <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-slate-900">
                                    <h3 id="titulo-registro-{{ $pago->id_pago }}" class="text-lg font-bold text-slate-900 dark:text-white">Registrar dinero recibido</h3>
                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Orden #{{ $pago->id_orden }} · Verificá que recibiste realmente el dinero antes de acreditarlo.</p>
                                    <div class="mt-4 grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3 text-sm dark:bg-white/5">
                                        <div><span class="block text-xs text-slate-500 dark:text-slate-400">Acreditado</span><strong class="text-sidan-600 dark:text-sidan-300">${{ number_format($acreditado / 100, 2) }}</strong></div>
                                        <div><span class="block text-xs text-slate-500 dark:text-slate-400">Máximo pendiente</span><strong class="text-slate-900 dark:text-white">${{ number_format($saldo / 100, 2) }}</strong></div>
                                    </div>
                                    <form method="POST" action="{{ route('admin.pagos.aprobar', $pago) }}" class="mt-4 space-y-4" @submit="enviando = true">
                                        @csrf
                                        <div>
                                            <label for="recibido-{{ $pago->id_pago }}" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Dinero realmente recibido ($)</label>
                                            <input id="recibido-{{ $pago->id_pago }}" name="monto_recibido" x-model="montoNuevo" type="number" inputmode="decimal" step="0.01" min="0.01" max="{{ number_format($saldo / 100, 2, '.', '') }}" required
                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white" placeholder="Ejemplo: 7.00">
                                        </div>
                                        <div>
                                            <label for="observacion-{{ $pago->id_pago }}" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Observación (opcional)</label>
                                            <textarea id="observacion-{{ $pago->id_pago }}" name="observacion" rows="2" maxlength="1000" placeholder="Ejemplo: Depósito verificado en el banco"
                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white"></textarea>
                                        </div>
                                        <div class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-white/5">
                                            <div class="flex justify-between gap-3">
                                                <span class="text-slate-600 dark:text-slate-300">Nuevo abono (estimado)</span>
                                                <strong class="text-sidan-600 dark:text-sidan-300" x-text="'$' + (Math.max(0, Number(montoNuevo) || 0)).toFixed(2)"></strong>
                                            </div>
                                            <div class="mt-2 flex justify-between gap-3">
                                                <span class="text-slate-600 dark:text-slate-300">Saldo estimado después</span>
                                                <strong class="text-slate-900 dark:text-white" x-text="'$' + Math.max(0, {{ number_format($saldo / 100, 2, '.', '') }} - (Number(montoNuevo) || 0)).toFixed(2)"></strong>
                                            </div>
                                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">SIDAN volverá a validar el importe al registrar el cobro.</p>
                                        </div>
                                        <div class="flex justify-end gap-3">
                                            <button type="button" @click="modal = null" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-white/20 dark:text-slate-200">Cancelar</button>
                                            <button type="submit" :disabled="enviando" class="rounded-xl bg-sidan-500 px-4 py-2 text-sm font-bold text-white hover:bg-sidan-600 disabled:opacity-50">Confirmar registro</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>

                        @if ($pago->proveedor === 'transferencia')
                        <template x-teleport="body">
                            <div x-cloak x-show="modal === 'rechazo'" @keydown.escape.window="modal = null"
                                class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-slate-950/70 p-4 backdrop-blur-sm"
                                @click.self="modal = null" role="dialog" aria-modal="true" aria-labelledby="titulo-rechazo-{{ $pago->id_pago }}">
                                <div class="w-full max-w-lg rounded-2xl border border-red-200 bg-white p-6 shadow-2xl dark:border-red-500/30 dark:bg-slate-900">
                                    <h3 id="titulo-rechazo-{{ $pago->id_pago }}" class="text-lg font-bold text-red-700 dark:text-red-300">¿Rechazar este intento de cobro?</h3>
                                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                        @if ($acreditado > 0)
                                            Se conservarán intactos los ${{ number_format($acreditado / 100, 2) }} ya acreditados. Solo se rechazará el intento pendiente.
                                        @else
                                            No hay abonos acreditados. El rechazo podrá liberar la reserva o cancelar la solicitud, conforme a las reglas actuales.
                                        @endif
                                    </p>
                                    <form method="POST" action="{{ route('admin.pagos.rechazar', $pago) }}" class="mt-4 space-y-4" @submit="enviando = true">
                                        @csrf
                                        <div>
                                            <label for="motivo-{{ $pago->id_pago }}" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Motivo del rechazo (visible al comprador)</label>
                                            <textarea id="motivo-{{ $pago->id_pago }}" name="motivo" required rows="3" maxlength="1000"
                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white" placeholder="Explicá por qué se rechaza el cobro"></textarea>
                                        </div>
                                        <div class="flex justify-end gap-3">
                                            <button type="button" @click="modal = null" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-white/20 dark:text-slate-200">Volver</button>
                                            <button type="submit" :disabled="enviando" class="rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700 disabled:opacity-50">Confirmar rechazo</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </template>
                        @endif
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-slate-200 p-10 text-center text-slate-500 dark:border-white/10 dark:text-slate-400">
                    {{ $hayFiltros ? 'No hay órdenes que coincidan con los filtros seleccionados.' : 'No hay pagos registrados.' }}
                </div>
            @endforelse
        </section>

        @if($ordenesSinMetodo->isNotEmpty() && !$estadoSeleccionado && !$metodoSeleccionado && !$situacionSeleccionada && !$entregaSeleccionada)
            <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
                <h2 class="font-black text-sidan-900 dark:text-white">Órdenes sin método de pago</h2>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">También pueden cancelarse desde administración. Las órdenes canceladas permanecen visibles en Control de órdenes.</p>
                <div class="mt-4 space-y-3">
                    @foreach($ordenesSinMetodo as $ordenSinMetodo)
                        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-3 dark:border-white/10">
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">Orden #{{ $ordenSinMetodo->id_orden }} · {{ $ordenSinMetodo->actividad?->nombre ?? 'Actividad' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $ordenSinMetodo->correo_contacto }} · Total histórico ${{ number_format((float)$ordenSinMetodo->total,2) }}</p>
                            </div>
                            <button type="button" onclick="document.getElementById('admin-cancelar-sin-pago-{{ $ordenSinMetodo->id_orden }}').showModal()" class="rounded-lg border border-red-300 px-3 py-2 text-sm font-semibold text-red-700 dark:border-red-500/30 dark:text-red-300">Cancelar</button>
                            <dialog id="admin-cancelar-sin-pago-{{ $ordenSinMetodo->id_orden }}" class="w-[calc(100%-2rem)] max-w-md rounded-2xl border border-slate-200 bg-white p-5 text-slate-900 shadow-2xl backdrop:bg-black/60 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                                <h3 class="text-lg font-black">Cancelar orden #{{ $ordenSinMetodo->id_orden }}</h3>
                                <p class="my-3 text-sm">La orden permanecerá en el historial, sin saldo por cobrar.</p>
                                <form method="POST" action="{{ route('admin.pagos.cancelar-orden', $ordenSinMetodo) }}" class="space-y-3">
                                    @csrf @method('DELETE')
                                    <label class="block text-sm font-semibold">Motivo
                                        <textarea name="motivo" required minlength="8" maxlength="300" rows="2" class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 dark:border-white/20 dark:bg-slate-800 dark:text-white"></textarea>
                                    </label>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="this.closest('dialog').close()" class="rounded-lg border px-3 py-2">Volver</button>
                                        <button type="submit" class="rounded-lg bg-red-600 px-3 py-2 font-bold text-white">Cancelar orden</button>
                                    </div>
                                </form>
                            </dialog>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">{{ $ordenesSinMetodo->links() }}</div>
            </section>
        @endif
        {{ $pagos->links() }}
    </div>
</div>
@endsection
