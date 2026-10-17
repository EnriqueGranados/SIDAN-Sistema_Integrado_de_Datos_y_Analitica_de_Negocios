@extends('layouts.navbars')

@section('title', 'Consolidado de pedidos | SIDAN')

@section('content')
    @php
        // Las pestañas son enlaces GET: mantienen los filtros y funcionan sin JavaScript.
        $pestanas = [
            'productos' => ['titulo' => 'Productos', 'descripcion' => 'Lista para preparar'],
            'variaciones' => ['titulo' => 'Personalizaciones', 'descripcion' => 'Opciones por artículo'],
            'compradores' => ['titulo' => 'Compradores', 'descripcion' => 'Pedidos por orden'],
        ];

        $filtrosActuales = request()->only(['actividad', 'buscar']);
        $totalUnidades = $productos->sum('unidades');
        $totalImporte = $productos->sum('importe');
        $totalProductos = $productos->count();
    @endphp

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8">
        {{-- Encabezado y acciones: imprimir siempre conserva filtros y pestaña. --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-emerald-500">
                    Administración · Control de compras
                </p>
                <h1 class="mt-2 text-3xl font-black text-sidan-900 dark:text-white">
                    Consolidado de pedidos
                </h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Preparación y distribución de productos de órdenes pagadas.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.control.consolidado.imprimir', array_merge($filtrosActuales, ['vista' => $vista])) }}"
                    target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z" />
                    </svg>
                    Imprimir lista
                </a>
                <a href="{{ route('admin.control.ordenes') }}"
                    class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-sidan-900 transition hover:bg-slate-100 dark:border-white/10 dark:text-white dark:hover:bg-white/5">
                    Ver todas las órdenes
                </a>
            </div>
        </div>

        {{-- Los filtros afectan por igual indicadores, listas e impresión. --}}
        <form method="GET" action="{{ route('admin.control.consolidado') }}"
            class="space-y-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-[#0f172a]">
            <input type="hidden" name="vista" value="{{ $vista }}">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="consolidado-actividad"
                        class="mb-2 block text-xs font-bold text-slate-600 dark:text-slate-300">Actividad</label>
                    <select id="consolidado-actividad" name="actividad"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-sidan-900 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                        <option value="">Todas las actividades</option>
                        @foreach ($actividades as $actividad)
                            <option value="{{ $actividad->id_actividad }}" @selected(request('actividad') == $actividad->id_actividad)>
                                {{ $actividad->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="consolidado-buscar" class="mb-2 block text-xs font-bold text-slate-600 dark:text-slate-300">
                        Búsqueda general
                    </label>
                    <input id="consolidado-buscar" type="search" name="buscar" maxlength="150"
                        value="{{ request('buscar') }}" placeholder="Producto, correo, nombre o #orden"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-sidan-900 placeholder:text-slate-400 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                </div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Solo se incluyen artículos de órdenes pagadas.
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('admin.control.consolidado', ['vista' => $vista]) }}"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-100 dark:border-white/10 dark:text-slate-200 dark:hover:bg-white/5">
                        Limpiar
                    </a>
                    <button type="submit"
                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-700">
                        Aplicar filtros
                    </button>
                </div>
            </div>
            @if ($errors->any())
                <p role="alert" class="text-sm text-red-600 dark:text-red-400">{{ $errors->first() }}</p>
            @endif
        </form>

        {{-- Indicadores netos de las mismas líneas pagadas que se muestran abajo. --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-[#0f172a]">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Unidades pagadas</p>
                <p class="mt-2 text-3xl font-black text-sidan-900 dark:text-white">{{ number_format($totalUnidades) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-[#0f172a]">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Productos distintos</p>
                <p class="mt-2 text-3xl font-black text-sidan-900 dark:text-white">{{ number_format($totalProductos) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-[#0f172a]">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Importe neto de artículos</p>
                <p class="mt-2 text-3xl font-black text-emerald-600 dark:text-emerald-400">
                    ${{ number_format((float) $totalImporte, 2) }}
                </p>
                <p class="mt-1 text-xs text-slate-400">Después de descuentos de artículos</p>
            </div>
        </div>

        {{-- Enlaces accesibles sin JavaScript; evitan una página interminable. --}}
        <nav aria-label="Secciones del consolidado" class="grid gap-2 sm:grid-cols-3">
            @foreach ($pestanas as $clave => $pestana)
                <a href="{{ route('admin.control.consolidado', array_merge($filtrosActuales, ['vista' => $clave])) }}"
                    @if ($vista === $clave) aria-current="page" @endif
                    class="rounded-xl border px-4 py-3 transition
                    {{ $vista === $clave
                        ? 'border-emerald-500 bg-emerald-50 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300'
                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:bg-[#0f172a] dark:text-slate-300 dark:hover:bg-white/5' }}">
                    <span class="block text-sm font-black">{{ $pestana['titulo'] }}</span>
                    <span class="mt-1 block text-xs opacity-75">{{ $pestana['descripcion'] }}</span>
                </a>
            @endforeach
        </nav>

        @if ($vista === 'productos')
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-[#0f172a]">
                <h2 class="text-xl font-black text-sidan-900 dark:text-white">Lista de artículos por preparar</h2>
                <p class="mt-1 mb-4 text-sm text-slate-500 dark:text-slate-400">
                    Cantidades agrupadas por actividad y producto, sin distinguir personalizaciones.
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[560px] text-left text-sm">
                        <thead
                            class="border-b border-slate-200 text-xs uppercase text-slate-500 dark:border-white/10 dark:text-slate-400">
                            <tr>
                                <th class="px-3 py-3">Actividad</th>
                                <th class="px-3 py-3">Producto</th>
                                <th class="px-3 py-3 text-center">Unidades</th>
                                <th class="px-3 py-3 text-right">Importe neto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                            @forelse ($productos as $producto)
                                <tr class="hover:bg-slate-50 dark:hover:bg-white/[0.03]">
                                    <td class="px-3 py-4 text-slate-600 dark:text-slate-300">
                                        {{ $producto->actividad_nombre }}</td>
                                    <td class="px-3 py-4 font-bold text-sidan-900 dark:text-white">
                                        {{ $producto->nombre_concepto }}</td>
                                    <td class="px-3 py-4 text-center font-black text-sidan-900 dark:text-white">
                                        {{ $producto->unidades }}</td>
                                    <td class="px-3 py-4 text-right font-black text-emerald-600 dark:text-emerald-400">
                                        ${{ number_format((float) $producto->importe, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-10 text-center text-slate-500">No hay productos
                                        pagados con estos filtros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @elseif ($vista === 'variaciones')
            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-[#0f172a]">
                <h2 class="text-xl font-black text-sidan-900 dark:text-white">Lista por personalización</h2>
                <p class="mt-1 mb-5 text-sm text-slate-500 dark:text-slate-400">
                    Cada combinación de talla, color, nombre u otras opciones se muestra por separado.
                </p>
                <div class="grid gap-3 md:grid-cols-2">
                    @forelse ($variaciones as $variacion)
                        @php
                            $opciones = $variacion->configuracion;
                            $opciones = is_string($opciones) ? json_decode($opciones, true) : $opciones;
                            $opciones = is_array($opciones) ? $opciones : [];
                        @endphp
                        <article class="rounded-xl border border-slate-200 p-4 dark:border-white/10">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ $variacion->actividad_nombre }}</p>
                                    <h3 class="mt-1 font-black text-sidan-900 dark:text-white">
                                        {{ $variacion->nombre_concepto }}</h3>
                                    <p class="mt-1 text-xs text-slate-400">Producto #{{ $variacion->id_item_actividad }}
                                    </p>
                                </div>
                                <span
                                    class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                    {{ $variacion->unidades }} uds.
                                </span>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse ($opciones as $clave => $valor)
                                    <span
                                        class="rounded-lg bg-slate-100 px-3 py-2 text-xs text-slate-700 dark:bg-white/10 dark:text-slate-200">
                                        <strong>{{ ucfirst(str_replace('_', ' ', $clave)) }}:</strong>
                                        {{ is_scalar($valor) ? $valor : json_encode($valor, JSON_UNESCAPED_UNICODE) }}
                                    </span>
                                @empty
                                    <span class="text-xs text-slate-500">Sin personalización</span>
                                @endforelse
                            </div>
                            <p class="mt-4 text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                Importe neto: ${{ number_format((float) $variacion->importe, 2) }}
                            </p>
                        </article>
                    @empty
                        <p class="text-sm text-slate-500">No hay personalizaciones para los filtros seleccionados.</p>
                    @endforelse
                </div>
            </section>
        @else
            <section class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-xl font-black text-sidan-900 dark:text-white">¿Quién pidió cada artículo?</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Pedidos agrupados por orden, con su comprador y detalle para entrega.
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                        {{ $ordenes->total() }} orden(es)
                    </span>
                </div>

                @forelse ($ordenes as $orden)
                    @php
                        $lineas = $pedidosPorOrden->get($orden->id_orden, collect());
                        $importeOrden = $lineas->sum(
                            fn($linea) => (float) $linea->subtotal - (float) $linea->descuento,
                        );
                    @endphp
                    <article
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div
                            class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 p-5 dark:border-white/10">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-black text-sidan-900 dark:text-white">Orden #{{ $orden->id_orden }}
                                    </h3>
                                    <span
                                        class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">Pagada</span>
                                </div>
                                <p class="mt-2 text-sm font-bold text-slate-700 dark:text-slate-200">
                                    {{ $orden->nombre_participante ?: $orden->correo_contacto }}
                                </p>
                                @if ($orden->nombre_participante)
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        {{ $orden->correo_contacto }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $orden->actividad_nombre }}
                                    · Creada
                                    {{ \Illuminate\Support\Carbon::parse($orden->fecha_orden)->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Importe de artículos listados</p>
                                <p class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                                    ${{ number_format($importeOrden, 2) }}</p>
                            </div>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-white/10">
                            @foreach ($lineas as $linea)
                                @php
                                    $opciones = is_string($linea->configuracion)
                                        ? json_decode($linea->configuracion, true)
                                        : $linea->configuracion;
                                    $opciones = is_array($opciones) ? $opciones : [];
                                @endphp
                                <div class="flex flex-wrap items-start justify-between gap-3 px-5 py-3 text-sm">
                                    <div>
                                        <p class="font-bold text-sidan-900 dark:text-white">{{ $linea->nombre_concepto }}
                                        </p>
                                        @if ($opciones)
                                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                @foreach ($opciones as $clave => $valor)
                                                    {{ ucfirst(str_replace('_', ' ', $clave)) }}:
                                                    {{ is_scalar($valor) ? $valor : json_encode($valor, JSON_UNESCAPED_UNICODE) }}{{ !$loop->last ? ' · ' : '' }}
                                                @endforeach
                                            </p>
                                        @else
                                            <p class="mt-1 text-xs text-slate-400">Sin personalización</p>
                                        @endif
                                    </div>
                                    <span
                                        class="shrink-0 font-black text-sidan-900 dark:text-white">{{ $linea->cantidad }}
                                        uds.</span>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div
                        class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-500 dark:border-white/10 dark:bg-[#0f172a]">
                        No hay órdenes pagadas con estos filtros.
                    </div>
                @endforelse
                <div>{{ $ordenes->links() }}</div>
            </section>
        @endif
    </div>
@endsection
