@extends('layouts.navbars')

@section('title', 'Consolidado de pedidos | SIDAN')

@section('content')
<div class="mx-auto max-w-7xl space-y-7 px-4 py-8">

    {{-- Encabezado --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs font-black uppercase tracking-widest text-emerald-500">
                Administración · Control de compras
            </p>

            <h1 class="mt-2 text-3xl font-black text-sidan-900 dark:text-white">
                Consolidado de pedidos
            </h1>

            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Artículos pagados, cantidades y personalizaciones
                para preparar pedidos.
            </p>
        </div>

        <a
            href="{{ route('admin.control.ordenes') }}"
            class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold
                   text-sidan-900 hover:bg-slate-100
                   dark:border-white/10 dark:text-white dark:hover:bg-white/5"
        >
            Ver todas las órdenes
        </a>
    </div>

    {{-- Filtros --}}
    <form
        method="GET"
        action="{{ route('admin.control.consolidado') }}"
        class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5
               dark:border-white/10 dark:bg-[#0f172a] md:grid-cols-[2fr_2fr_auto]"
    >
        <div>
            <label class="mb-2 block text-sm font-bold dark:text-white">
                Actividad
            </label>

            <select
                name="actividad"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3
                       text-sm dark:border-white/10 dark:bg-slate-900 dark:text-white"
            >
                <option value="">Todas las actividades</option>

                @foreach ($actividades as $actividad)
                    <option
                        value="{{ $actividad->id_actividad }}"
                        @selected(request('actividad') == $actividad->id_actividad)
                    >
                        {{ $actividad->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-2 block text-sm font-bold dark:text-white">
                Buscar producto o comprador
            </label>

            <input
                type="search"
                name="buscar"
                value="{{ request('buscar') }}"
                placeholder="Ej. CAMISA o correo electrónico"
                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3
                       text-sm dark:border-white/10 dark:bg-slate-900 dark:text-white"
            >
        </div>

        <div class="flex items-end gap-2">
            <button
                type="submit"
                class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-black
                       text-white hover:bg-emerald-600"
            >
                Filtrar
            </button>

            <a
                href="{{ route('admin.control.consolidado') }}"
                class="rounded-xl border border-slate-200 px-4 py-3 text-sm
                       font-bold dark:border-white/10 dark:text-white"
            >
                Limpiar
            </a>
        </div>
    </form>

    @php
        $totalUnidades = $productos->sum('unidades');
        $totalImporte = $productos->sum('importe');
        $totalProductos = $productos->count();
    @endphp

    {{-- Indicadores --}}
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5
                    dark:border-white/10 dark:bg-[#0f172a]">
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                Unidades pedidas
            </p>

            <p class="mt-2 text-3xl font-black text-sidan-900 dark:text-white">
                {{ number_format($totalUnidades) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5
                    dark:border-white/10 dark:bg-[#0f172a]">
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                Productos distintos
            </p>

            <p class="mt-2 text-3xl font-black text-sidan-900 dark:text-white">
                {{ $totalProductos }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5
                    dark:border-white/10 dark:bg-[#0f172a]">
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                Importe de artículos pagados
            </p>

            <p class="mt-2 text-3xl font-black text-emerald-500">
                ${{ number_format($totalImporte, 2) }}
            </p>
        </div>
    </div>

    {{-- Resumen por producto --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5
                    dark:border-white/10 dark:bg-[#0f172a]">

        <h2 class="mb-5 text-xl font-black text-sidan-900 dark:text-white">
            Cantidades por producto
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase
                              text-slate-500 dark:border-white/10">
                    <tr>
                        <th class="px-3 py-3">Actividad</th>
                        <th class="px-3 py-3">Producto</th>
                        <th class="px-3 py-3 text-center">Unidades</th>
                        <th class="px-3 py-3 text-right">Importe</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                    @forelse ($productos as $producto)
                        <tr>
                            <td class="px-3 py-4 dark:text-slate-300">
                                {{ $producto->actividad_nombre }}
                            </td>

                            <td class="px-3 py-4 font-bold dark:text-white">
                                {{ $producto->nombre_concepto }}
                            </td>

                            <td class="px-3 py-4 text-center font-black dark:text-white">
                                {{ $producto->unidades }}
                            </td>

                            <td class="px-3 py-4 text-right font-black text-emerald-600">
                                ${{ number_format((float) $producto->importe, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-10 text-center text-slate-500">
                                No hay artículos pagados con estos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- Desglose por opciones --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5
                    dark:border-white/10 dark:bg-[#0f172a]">

        <h2 class="text-xl font-black text-sidan-900 dark:text-white">
            Desglose de personalizaciones
        </h2>

        <p class="mt-1 mb-5 text-sm text-slate-500 dark:text-slate-400">
            Cada combinación de talla, color, nombre u otras opciones
            se muestra por separado.
        </p>

        <div class="grid gap-3 md:grid-cols-2">
            @forelse ($variaciones as $variacion)
                @php
                    $campos = $variacion->configuracion;

                    if (is_string($campos)) {
                        $campos = json_decode($campos, true);
                    }

                    $campos = is_array($campos) ? $campos : [];
                @endphp

                <div class="rounded-xl border border-slate-200 p-4
                            dark:border-white/10">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-black text-sidan-900 dark:text-white">
                                {{ $variacion->nombre_concepto }}
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                Producto #{{ $variacion->id_item_actividad }}
                            </p>
                        </div>

                        <span class="rounded-full bg-emerald-100 px-3 py-1
                                     text-xs font-black text-emerald-700
                                     dark:bg-emerald-500/10 dark:text-emerald-400">
                            {{ $variacion->unidades }} unidades
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($campos as $clave => $valor)
                            <span class="rounded-lg bg-slate-100 px-3 py-2 text-xs
                                         text-slate-700 dark:bg-white/10 dark:text-slate-200">
                                <strong>
                                    {{ ucfirst(str_replace('_', ' ', $clave)) }}:
                                </strong>

                                {{ is_scalar($valor)
                                    ? $valor
                                    : json_encode($valor, JSON_UNESCAPED_UNICODE) }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-500">
                                Sin personalización
                            </span>
                        @endforelse
                    </div>

                    <p class="mt-4 text-sm font-bold text-emerald-600">
                        Importe: ${{ number_format((float) $variacion->importe, 2) }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-500">
                    No hay personalizaciones registradas.
                </p>
            @endforelse
        </div>
    </section>

    {{-- Pedidos individuales --}}
    <section class="rounded-2xl border border-slate-200 bg-white p-5
                    dark:border-white/10 dark:bg-[#0f172a]">

        <h2 class="mb-5 text-xl font-black text-sidan-900 dark:text-white">
            ¿Quién pidió cada artículo?
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase
                              text-slate-500 dark:border-white/10">
                    <tr>
                        <th class="px-3 py-3">Orden</th>
                        <th class="px-3 py-3">Comprador</th>
                        <th class="px-3 py-3">Artículo</th>
                        <th class="px-3 py-3">Opciones</th>
                        <th class="px-3 py-3 text-center">Cantidad</th>
                        <th class="px-3 py-3 text-right">Importe</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 dark:divide-white/10">
                    @forelse ($pedidos as $pedido)
                        @php
                            $opciones = $pedido->configuracion;

                            if (is_string($opciones)) {
                                $opciones = json_decode($opciones, true);
                            }

                            $opciones = is_array($opciones) ? $opciones : [];
                        @endphp

                        <tr>
                            <td class="px-3 py-4 font-black dark:text-white">
                                #{{ $pedido->id_orden }}
                            </td>

                            <td class="px-3 py-4">
                                <p class="font-semibold dark:text-white">
                                    {{ $pedido->nombre_participante
                                        ?: $pedido->correo_contacto }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $pedido->correo_contacto }}
                                </p>
                            </td>

                            <td class="px-3 py-4 font-bold dark:text-white">
                                {{ $pedido->nombre_concepto }}
                            </td>

                            <td class="px-3 py-4">
                                @foreach ($opciones as $clave => $valor)
                                    <p class="mb-1 text-xs dark:text-slate-300">
                                        <strong>
                                            {{ ucfirst(str_replace('_', ' ', $clave)) }}:
                                        </strong>

                                        {{ is_scalar($valor)
                                            ? $valor
                                            : json_encode($valor, JSON_UNESCAPED_UNICODE) }}
                                    </p>
                                @endforeach
                            </td>

                            <td class="px-3 py-4 text-center font-bold dark:text-white">
                                {{ $pedido->cantidad }}
                            </td>

                            <td class="px-3 py-4 text-right font-black text-emerald-600">
                                ${{ number_format((float) $pedido->subtotal, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-10 text-center text-slate-500">
                                No se encontraron pedidos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $pedidos->links() }}
        </div>
    </section>
</div>
@endsection