
@extends('layouts.navbars')

@section('title', 'Gestión de pagos')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                Gestión de pagos
            </h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Revisá transferencias y confirmá el efectivo recibido.
            </p>
        </div>

        @if (session('success'))
            <div role="status"
                class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert"
                class="rounded-xl bg-red-50 p-4 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-300">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-4">
            @forelse ($pagos as $pago)
                @php
                    $ordenPago = $pago->orden;
                    $manual = in_array($pago->proveedor, ['transferencia', 'efectivo'], true);
                    $revisable = $manual
                        && $pago->estado === 'pendiente'
                        && $ordenPago?->estado === 'pendiente_pago';
                @endphp

                <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-4 p-5">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-bold text-slate-900 dark:text-white">
                                    Orden #{{ $pago->id_orden }}
                                </h2>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                    {{ ucfirst($pago->proveedor) }}
                                </span>

                                <span class="rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $pago->estado === 'pendiente'
                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300'
                                        : 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300' }}">
                                    {{ str_replace('_', ' ', ucfirst($pago->estado)) }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                                {{ $ordenPago?->actividad?->nombre ?? 'Actividad no disponible' }}
                            </p>

                            <p class="mt-1 break-all text-sm text-slate-500 dark:text-slate-400">
                                {{ $ordenPago?->correo_contacto }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Solicitud: {{ $pago->created_at?->timezone('America/El_Salvador')->format('d/m/Y H:i') }}
                            </p>
                        </div>

                        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            ${{ number_format((float) $pago->monto, 2) }}
                        </p>
                    </div>

                    <details class="border-t border-slate-100 px-5 py-3 dark:border-white/5">
                        <summary class="cursor-pointer text-sm font-semibold text-slate-600 dark:text-slate-300">
                            Ver productos y descuentos
                        </summary>

                        <div class="mt-3 space-y-3">
                            @foreach ($ordenPago?->detalles ?? [] as $detalle)
                                <div class="flex justify-between gap-4 text-sm text-slate-600 dark:text-slate-300">
                                    <span>
                                        {{ $detalle->nombre_concepto }}
                                        × {{ $detalle->cantidad }}

                                        @if ((float) $detalle->descuento > 0)
                                            <small class="block text-emerald-600 dark:text-emerald-400">
                                                Descuento:
                                                ${{ number_format((float) $detalle->descuento, 2) }}
                                                sobre {{ $detalle->unidades_descuento }} unidad(es)
                                            </small>
                                        @endif
                                    </span>

                                    <span class="shrink-0 font-semibold">
                                        ${{ number_format(
                                            (float) $detalle->subtotal - (float) $detalle->descuento,
                                            2
                                        ) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </details>

                    @if ($pago->observacion_revision)
                        <p class="px-5 pb-4 text-sm text-red-600 dark:text-red-400">
                            {{ $pago->observacion_revision }}
                        </p>
                    @endif

                    <div class="space-y-4 border-t border-slate-100 bg-slate-50/70 p-5 dark:border-white/5 dark:bg-white/[0.03]">
                        @if ($pago->proveedor === 'transferencia' && $pago->comprobante_path)
                            <a href="{{ route('admin.pagos.comprobante', $pago) }}"
                                target="_blank" rel="noopener"
                                class="inline-flex rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 dark:border-white/20 dark:text-slate-200">
                                Ver comprobante
                            </a>
                        @endif

                        @if ($revisable)
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $pago->proveedor === 'transferencia'
                                    ? 'Aprobá después de verificar que el abono se recibió en la cuenta.'
                                    : 'Aprobá después de recibir el efectivo.' }}
                            </p>

                            <form method="POST"
                                action="{{ route('admin.pagos.aprobar', $pago) }}"
                                onsubmit="return confirm('¿Confirmás que recibiste el importe completo de esta orden?');">
                                @csrf
                                <button type="submit"
                                    class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">
                                    {{ $pago->proveedor === 'efectivo'
                                        ? 'Confirmar efectivo recibido'
                                        : 'Aprobar transferencia' }}
                                </button>
                            </form>

                            <details>
                                <summary class="cursor-pointer text-sm font-semibold text-red-600 dark:text-red-400">
                                    {{ $pago->proveedor === 'efectivo' ? 'Cancelar solicitud de efectivo' : 'Rechazar pago' }}
                                </summary>

                                <form method="POST"
                                    action="{{ route('admin.pagos.rechazar', $pago) }}"
                                    class="mt-3 space-y-3"
                                    onsubmit="return confirm('¿Confirmás que querés cancelar esta solicitud y liberar la reserva?');">
                                    @csrf

                                    <label for="motivo-{{ $pago->id_pago }}"
                                        class="block text-sm text-slate-600 dark:text-slate-300">
                                        {{ $pago->proveedor === 'efectivo' ? 'Motivo de cancelación (visible para el usuario)' : 'Motivo del rechazo' }}
                                    </label>

                                    <textarea id="motivo-{{ $pago->id_pago }}" name="motivo"
                                        required maxlength="1000" rows="2"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-900 dark:text-white"></textarea>

                                    <button type="submit"
                                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                        {{ $pago->proveedor === 'efectivo'
                                            ? 'Cancelar orden y liberar reserva'
                                            : 'Rechazar y liberar reserva' }}
                                    </button>
                                </form>
                            </details>
                        @elseif ($pago->fecha_confirmacion)
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Confirmado:
                                {{ $pago->fecha_confirmacion->timezone('America/El_Salvador')->format('d/m/Y H:i') }}
                            </p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-slate-200 p-10 text-center text-slate-500 dark:border-white/10 dark:text-slate-400">
                    No hay pagos registrados.
                </div>
            @endforelse
        </div>

        {{ $pagos->links() }}
    </div>
@endsection
