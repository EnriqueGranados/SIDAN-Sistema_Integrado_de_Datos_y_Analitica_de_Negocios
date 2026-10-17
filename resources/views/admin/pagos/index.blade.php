
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

        @php
            $sumaIngresosNoEjercidos=\Illuminate\Support\Facades\DB::table('tbl_cierres_orden')
                ->where('clasificacion','ingreso_no_ejercido')->sum('anticipo_sin_entrega');
            $sumaPorClasificar=\Illuminate\Support\Facades\DB::table('tbl_cierres_orden')
                ->where('clasificacion','pendiente_clasificacion')->sum('anticipo_sin_entrega');
        @endphp
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
            <div class="rounded-xl border border-emerald-300 p-4 dark:border-emerald-500/30">
                <p>Ingresos por anticipos no ejercidos autorizados</p>
                <p class="text-xl font-bold">${{ number_format((float)$sumaIngresosNoEjercidos,2) }}</p>
                <p class="text-xs">No son entradas adicionales de efectivo.</p>
            </div>
            <div class="rounded-xl border border-amber-300 p-4 dark:border-amber-500/30">
                <p>Anticipos retenidos pendientes de clasificación</p>
                <p class="text-xl font-bold">${{ number_format((float)$sumaPorClasificar,2) }}</p>
                <p class="text-xs">Todavía no representan ingresos reconocidos.</p>
            </div>
        </div>
        <a href="{{ route('admin.ordenes.vencidas') }}" class="inline-block rounded-xl bg-amber-600 px-4 py-2 font-semibold text-white">Órdenes vencidas con dinero recibido</a>
        <div class="space-y-4">
            @forelse ($pagos as $pago)
                @php
                    $ordenPago = $pago->orden;
                    $manual = in_array($pago->proveedor, ['transferencia', 'efectivo'], true);
                    $abonoPendiente = $manual ? $pago->abonos->firstWhere('estado', 'pendiente') : null;
                    $acreditado = $manual ? $pago->montoAcreditadoCentavos() : 0;
                    $totalCentavos = (int) round((float) $pago->monto * 100);
                    $saldo = max(0, $totalCentavos - $acreditado);
                    // Condiciones que el comprador aceptó al crear esta orden.
                    // Usamos el mínimo histórico guardado en la orden, no el catálogo actual.
                    $esProducto = $manual && $ordenPago?->tipo_orden === 'productos';
                    $esAnticipo = $esProducto && $ordenPago?->modalidad_pago === 'anticipo';
                    $minimoInicialCentavos = $esAnticipo
                        ? (int) round((float) $ordenPago->minimo_inicial * 100)
                        : $totalCentavos;
                    $faltanteMinimoCentavos = $esAnticipo
                        ? app(\App\Services\AbonosProductosService::class)->faltanteAnticipo($ordenPago)
                        : max(0, $minimoInicialCentavos - $acreditado);
                    $revisable = $manual
                        && $ordenPago?->estado === 'pendiente_pago'
                        && in_array($pago->estado,['pendiente','parcial'],true) && $abonoPendiente;
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
                                        @if ($esProducto)
                                            @php
                                                $reglaDetalle = app(\App\Services\AbonosProductosService::class);
                                                $minimoDetalle = $esAnticipo
                                                    ? $reglaDetalle->minimo($detalle)
                                                    : $reglaDetalle->neto($detalle);
                                                $totalDetalle = $reglaDetalle->neto($detalle);
                                            @endphp
                                            <small class="mt-1 block text-xs font-semibold text-amber-700 dark:text-amber-300">
                                                {{ $esAnticipo && $minimoDetalle < $totalDetalle ? 'Anticipo mínimo de este producto' : 'Pago completo de este producto' }}:
                                                ${{ number_format($minimoDetalle / 100, 2) }}
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

                    @if ($manual)
                        <div class="border-t border-slate-100 px-5 py-4 text-sm dark:border-white/5">
                            @if ($esProducto)
                                <div class="mb-4 rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                                    <p class="text-xs font-bold uppercase tracking-wide">
                                        {{ $esAnticipo ? 'Modalidad: anticipo' : 'Modalidad: pago completo' }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-semibold">
                                            {{ $esAnticipo ? 'Anticipo mínimo inicial solicitado' : 'Primer pago requerido' }}
                                        </span>
                                        <strong class="text-xl">${{ number_format($minimoInicialCentavos / 100, 2) }}</strong>
                                    </div>
                                    @if ($esAnticipo)
                                        <p class="mt-2 font-semibold">
                                            @if ($faltanteMinimoCentavos > 0)
                                                Falta para cubrir el anticipo: ${{ number_format($faltanteMinimoCentavos / 100, 2) }}
                                            @else
                                                Anticipo mínimo cubierto. Saldo de toda la orden: ${{ number_format($saldo / 100, 2) }}
                                            @endif
                                        </p>
                                        <p class="mt-1 text-xs">
                                            El importe mínimo corresponde a las condiciones de la orden, incluidos los productos que deben pagarse al 100%.
                                        </p>
                                    @else
                                        <p class="mt-2 text-xs">Esta orden requiere pago íntegro; no se seleccionó modalidad de anticipo.</p>
                                    @endif
                                </div>
                            @endif
                            <p class="font-bold text-slate-800 dark:text-white">
                                Acreditado: ${{ number_format($acreditado / 100, 2) }}
                                · Por pagar: ${{ number_format($saldo / 100, 2) }}
                            </p>
                            <div class="mt-3 space-y-2">
                                @foreach ($pago->abonos as $abono)
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                                        <div>
                                            <span class="font-semibold">Comprobante #{{ $abono->id_abono }}</span>
                                            <span class="ml-2 text-xs">{{ ucfirst($abono->estado) }}</span>
                                            @if ($abono->estado === 'confirmado')
                                                <span class="ml-2 text-emerald-600">${{ number_format((float) $abono->monto_recibido, 2) }} recibidos</span>
                                            @endif
                                            @if ($abono->observacion_revision)
                                                <p class="mt-1 text-xs text-slate-500">{{ $abono->observacion_revision }}</p>
                                            @endif
                                        </div>
                                        @if($abono->comprobante_path)<a href="{{ route('admin.pagos.abonos.comprobante', $abono) }}"
                                            target="_blank" rel="noopener"
                                            class="text-sm font-semibold text-blue-600 underline dark:text-blue-400">
                                            Ver comprobante
                                        </a>@endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($ordenPago?->tipo_orden === 'productos')
                        <div class="border-t border-slate-200 p-4 space-y-3 dark:border-white/10">
                            <h3 class="font-bold text-sm">Entregas por producto</h3>
                            @foreach($ordenPago->detalles as $detalle)
                                @php
                                    $dist=app(\App\Services\AbonosProductosService::class);
                                    $pagado=$dist->aplicado($detalle);
                                    $autorizado=$pagado >= $dist->neto($detalle) || ($ordenPago->estado==='pagada' && $pago->estado==='confirmado');
                                @endphp
                                <div class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-white/5">
                                    <p class="font-semibold">{{ $detalle->nombre_concepto }} · entregado {{ $detalle->cantidad_entregada }}/{{ $detalle->cantidad }}</p>
                                    <p class="text-xs">Acreditado al producto: ${{ number_format($pagado/100,2) }} / ${{ number_format($dist->neto($detalle)/100,2) }}</p>
                                    @if($autorizado && $detalle->cantidad_entregada < $detalle->cantidad)
                                        <form method="POST" action="{{ route('admin.ordenes.entregar',[$ordenPago,$detalle]) }}" onsubmit="return confirm('¿Entregaste físicamente todas las unidades de esta línea?');" class="mt-2 space-y-2">
                                            @csrf
                                            <input name="observacion" maxlength="1000" class="w-full rounded-lg border p-2 dark:bg-slate-800" placeholder="Observación de entrega (opcional)">
                                            <button class="rounded-lg bg-emerald-700 px-3 py-2 font-semibold text-white">Registrar entrega completa</button>
                                        </form>
                                    @elseif(!$autorizado)
                                        <p class="text-amber-600">Entrega bloqueada: producto no pagado al 100%.</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if ($pago->observacion_revision)
                        <p class="px-5 pb-4 text-sm text-red-600 dark:text-red-400">
                            {{ $pago->observacion_revision }}
                        </p>
                    @endif

                    <div class="space-y-4 border-t border-slate-100 bg-slate-50/70 p-5 dark:border-white/5 dark:bg-white/[0.03]">

                        @if ($revisable)
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $pago->proveedor === 'transferencia'
                                    ? 'Aprobá después de verificar que el abono se recibió en la cuenta.'
                                    : 'Aprobá después de recibir el efectivo.' }}
                            </p>

                            <form method="POST"
                                action="{{ route('admin.pagos.aprobar', $pago) }}"
                                class="space-y-3"
                                onsubmit="return confirm('¿Confirmás que recibiste realmente el importe indicado?');">
                                @csrf
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200"
                                        for="recibido-{{ $pago->id_pago }}">
                                        Dinero realmente recibido (máximo ${{ number_format($saldo / 100, 2) }})
                                    </label>
                                    <input id="recibido-{{ $pago->id_pago }}" type="number" step="0.01"
                                        min="0.01" max="{{ number_format($saldo / 100, 2, '.', '') }}"
                                        name="monto_recibido" required
                                        class="w-full rounded-lg border border-slate-300 bg-white p-3 text-slate-900 dark:border-white/20 dark:bg-slate-900 dark:text-white"
                                        placeholder="Ejemplo: 14.00">
                                    @if ($esAnticipo)
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            Registrá solamente el dinero realmente verificado. Si es menor al anticipo requerido,
                                            SIDAN conservará el abono y mostrará al comprador el anticipo faltante.
                                        </p>
                                    @endif
                                    <label class="block text-sm text-slate-600 dark:text-slate-300"
                                        for="observacion-{{ $pago->id_pago }}">Observación (opcional)</label>
                                    <textarea id="observacion-{{ $pago->id_pago }}" name="observacion"
                                        rows="2" maxlength="1000" placeholder="Ejemplo: Depósito verificado en el banco"
                                        class="w-full rounded-lg border border-slate-300 bg-white p-3 text-slate-900 dark:border-white/20 dark:bg-slate-900 dark:text-white"></textarea>
                                <button type="submit"
                                    class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">
                                    {{ $pago->proveedor === 'efectivo'
                                        ? 'Registrar efectivo realmente recibido'
                                        : 'Registrar dinero recibido' }}
                                </button>
                            </form>

                            <details>
                                <summary class="cursor-pointer text-sm font-semibold text-red-600 dark:text-red-400">
                                    {{ $pago->proveedor === 'efectivo' ? 'Rechazar cobro no recibido' : 'Rechazar comprobante' }}
                                </summary>

                                <form method="POST"
                                    action="{{ route('admin.pagos.rechazar', $pago) }}"
                                    class="mt-3 space-y-3"
                                    onsubmit="return confirm(@js($pago->proveedor === 'efectivo'
                                        ? '¿Confirmás la cancelación del efectivo y la liberación de la reserva?'
                                        : '¿Confirmás que el comprobante es inválido? Se conservarán los abonos acreditados.'))">
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
                                            ? ($acreditado > 0 ? 'Rechazar intento, conservar abonos' : 'Cancelar sin cobros')
                                            : ($acreditado > 0 ? 'Rechazar este comprobante' : 'Rechazar y liberar reserva') }}
                                    </button>
                                </form>
                            </details>
                        @elseif ($manual && $pago->estado === 'parcial')
                            <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">
                                Falta que el comprador envíe otro comprobante por el saldo de
                                ${{ number_format($saldo / 100, 2) }}.
                            </p>
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