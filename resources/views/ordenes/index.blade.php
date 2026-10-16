@extends('layouts.user')

@section('title', 'Mis órdenes - SIDAN')

@section('user-content')
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400">
                    Mi cuenta · SIDAN
                </p>
                <h1 class="mt-2 text-3xl font-black text-slate-900 dark:text-white">
                    Mis órdenes
                </h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Consulta tus compras y continúa con el pago de tus órdenes pendientes.
                </p>
            </div>
            <a href="{{ route('user.inscripciones.index') }}"
                class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:text-slate-200 dark:hover:bg-white/5">
                Ver mis inscripciones
            </a>
        </div>

        @if ($errors->any())
            <div role="alert"
                class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="GET" action="{{ route('user.ordenes.index') }}"
            class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-white/5 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="orden-actividad" class="mb-2 block text-xs font-bold text-slate-500 dark:text-slate-400">
                    Actividad
                </label>
                <select id="orden-actividad" name="actividad"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                    <option value="">Todas</option>
                    @foreach ($actividades as $actividad)
                        <option value="{{ $actividad->id_actividad }}" @selected(request('actividad') == $actividad->id_actividad)>
                            {{ $actividad->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="orden-tipo" class="mb-2 block text-xs font-bold text-slate-500 dark:text-slate-400">
                    Tipo de orden
                </label>
                <select id="orden-tipo" name="tipo"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                    <option value="">Todos</option>
                    <option value="inscripcion" @selected(request('tipo') === 'inscripcion')>
                        Inscripciones
                    </option>
                    <option value="productos" @selected(request('tipo') === 'productos')>
                        Productos
                    </option>
                </select>
            </div>

            <div>
                <label for="orden-estado" class="mb-2 block text-xs font-bold text-slate-500 dark:text-slate-400">
                    Estado
                </label>
                <select id="orden-estado" name="estado"
                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-900 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                    <option value="">Todos</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>
                            {{ ucfirst(str_replace('_', ' ', $estado)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                    class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                    Filtrar
                </button>
                <a href="{{ route('user.ordenes.index') }}"
                    class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-600 dark:border-white/10 dark:text-slate-300">
                    Limpiar
                </a>
            </div>
        </form>

        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ $ordenes->total() }} orden(es) encontrada(s)
        </p>

        <div class="space-y-4">
            @forelse ($ordenes as $orden)
                @php
                    $expirada = $orden->estado === 'pendiente_pago' && $orden->expira_en?->isPast();

                    $puedePagar = $orden->estado === 'pendiente_pago' && !$expirada;

                    $fechaPago = $orden->pago?->fecha_confirmacion;

                    $color = match ($orden->estado) {
                        'pagada' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
                        'pagada_prueba' => 'bg-blue-500/10 text-blue-700 dark:text-blue-300',
                        'pendiente_pago' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
                        'cancelada', 'vencida' => 'bg-red-500/10 text-red-700 dark:text-red-300',
                        default => 'bg-slate-500/10 text-slate-600 dark:text-slate-300',
                    };
                @endphp

                {{-- Tarjeta con desglose expandible y acceso directo al detalle completo. --}}
                <article
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                    <details class="group">
                        <summary class="cursor-pointer list-none p-5 sm:p-6">
                            <div class="flex flex-wrap items-center justify-between gap-5">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-lg font-black text-slate-900 dark:text-white">
                                            Orden #{{ $orden->id_orden }}
                                        </span>
                                        <span class="rounded-full px-3 py-1 text-xs font-black {{ $color }}">
                                            {{ ucfirst(str_replace('_', ' ', $orden->estado)) }}
                                        </span>
                                        <span
                                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                            {{ $orden->tipo_orden === 'productos' ? 'Productos' : 'Inscripción' }}
                                        </span>
                                    </div>
                                    <p class="mt-2 font-semibold text-slate-900 dark:text-white">
                                        {{ $orden->actividad?->nombre ?? 'Actividad no disponible' }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        Creada el {{ $orden->created_at?->format('d/m/Y H:i') ?? '—' }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-center gap-6">
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Total de la orden</p>
                                        <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400">
                                            ${{ number_format((float) $orden->total, 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Fecha de pago</p>
                                        <p class="text-sm font-bold text-slate-900 dark:text-white">
                                            {{ $fechaPago?->format('d/m/Y H:i') ?? 'Sin pago confirmado' }}
                                        </p>
                                    </div>
                                    <svg class="h-5 w-5 text-slate-400 transition group-open:rotate-180" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </div>
                            </div>
                        </summary>

                        <div
                            class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-4 dark:border-white/10 sm:px-6">
                            <p class="text-sm text-slate-500 dark:text-slate-400">
                                @if ($expirada)
                                    El plazo de pago de esta orden venció.
                                @elseif ($puedePagar)
                                    @if ($orden->expira_en)
                                        Paga antes del {{ $orden->expira_en->format('d/m/Y H:i') }}.
                                    @else
                                        Tu orden está pendiente de pago.
                                    @endif
                                @else
                                    Consulta el resumen de tu orden.
                                @endif
                            </p>
                        </div>

                        <div class="border-t border-slate-200 px-5 py-5 dark:border-white/10 sm:px-6">
                            <div class="mb-5 grid gap-3 text-sm sm:grid-cols-3">
                                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-900/60">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Fecha de creación</p>
                                    <p class="mt-1 font-bold text-slate-900 dark:text-white">
                                        {{ $orden->created_at?->format('d/m/Y H:i') ?? '—' }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-900/60">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Estado del pago</p>
                                    <p class="mt-1 font-bold text-slate-900 dark:text-white">
                                        {{ $orden->pago?->estado ? ucfirst(str_replace('_', ' ', $orden->pago->estado)) : 'Sin registro de pago' }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-900/60">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Fecha de confirmación</p>
                                    <p class="mt-1 font-bold text-slate-900 dark:text-white">
                                        {{ $fechaPago?->format('d/m/Y H:i') ?? 'Pendiente' }}
                                    </p>
                                </div>
                            </div>

                            <h2 class="mb-4 text-base font-black text-slate-900 dark:text-white">
                                Artículos y conceptos de la orden
                            </h2>

                            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-white/10">
                                <table class="w-full min-w-[750px] text-left text-sm">
                                    <thead
                                        class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                        <tr>
                                            <th class="px-4 py-3">Producto</th>
                                            <th class="px-4 py-3">Personalización</th>
                                            <th class="px-4 py-3 text-center">Cantidad</th>
                                            <th class="px-4 py-3 text-right">Precio unitario</th>
                                            <th class="px-4 py-3 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-slate-200 text-slate-900 dark:divide-white/10 dark:text-white">
                                        @forelse ($orden->detalles as $detalle)
                                            @php
                                                $configuracion = $detalle->configuracion;
                                                if (is_string($configuracion)) {
                                                    $configuracion = json_decode($configuracion, true);
                                                }
                                                $configuracion = is_array($configuracion) ? $configuracion : [];
                                            @endphp
                                            <tr>
                                                <td class="px-4 py-4 align-top font-bold">
                                                    {{ $detalle->nombre_concepto }}
                                                </td>
                                                <td class="px-4 py-4 align-top">
                                                    @forelse ($configuracion as $clave => $valor)
                                                        <div class="mb-1">
                                                            <span class="font-semibold text-slate-500 dark:text-slate-400">
                                                                {{ ucfirst(str_replace('_', ' ', $clave)) }}:
                                                            </span>
                                                            <span>
                                                                {{ is_scalar($valor) ? (string) $valor : json_encode($valor, JSON_UNESCAPED_UNICODE) }}
                                                            </span>
                                                        </div>
                                                    @empty
                                                        <span class="text-slate-500">Sin personalización</span>
                                                    @endforelse
                                                </td>
                                                <td class="px-4 py-4 text-center align-top font-bold">
                                                    {{ $detalle->cantidad }}
                                                </td>
                                                <td class="px-4 py-4 text-right align-top">
                                                    ${{ number_format((float) $detalle->precio_unitario, 2) }}
                                                </td>
                                                <td
                                                    class="px-4 py-4 text-right align-top font-black text-emerald-600 dark:text-emerald-400">
                                                    ${{ number_format((float) $detalle->subtotal, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                                    Esta orden no tiene conceptos registrados.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-5 flex flex-wrap items-center justify-between gap-4">
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Los precios corresponden al registro histórico de la orden.
                                </p>
                                <div class="text-right">
                                    <p class="text-sm text-slate-500 dark:text-slate-400">
                                        Subtotal: ${{ number_format((float) $orden->subtotal, 2) }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                        Descuento: ${{ number_format((float) $orden->descuento_total, 2) }}
                                    </p>
                                    <p class="mt-2 text-xl font-black text-emerald-600 dark:text-emerald-400">
                                        Total: ${{ number_format((float) $orden->total, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </details>

                    {{-- Visible sin tener que desplegar los conceptos de la orden. --}}
                    <div
                        class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/70 px-5 py-4 dark:border-white/10 dark:bg-white/5 sm:px-6">
                        <a href="{{ route('sidan.ordenes.show', $orden->id_orden) }}"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#102b54]/20 bg-white px-5 py-3 text-sm font-bold text-[#102b54] transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#102b54]/30 dark:border-white/20 dark:bg-slate-900 dark:text-white dark:hover:bg-slate-800">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 12s3.5-7 9-7 9 7 9 7-3.5 7-9 7-9-7-9-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            Ver detalle de la orden
                        </a>

                        @if ($puedePagar)
                            <a href="{{ route('sidan.ordenes.show', $orden->id_orden) }}"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/40">
                                Continuar al pago <span aria-hidden="true">→</span>
                            </a>
                        @endif
                    </div>
                </article>
            @empty
                <div
                    class="rounded-2xl border border-slate-200 bg-white px-6 py-14 text-center dark:border-white/10 dark:bg-white/5">
                    <p class="font-bold text-slate-900 dark:text-white">
                        No se encontraron órdenes.
                    </p>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Si aplicaste filtros, prueba limpiarlos para ver todas tus órdenes.
                    </p>
                    <a href="{{ route('activities.index') }}"
                        class="mt-5 inline-flex rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">
                        Explorar actividades
                    </a>
                </div>
            @endforelse
        </div>

        <div>
            {{ $ordenes->links() }}
        </div>
    </div>
@endsection
