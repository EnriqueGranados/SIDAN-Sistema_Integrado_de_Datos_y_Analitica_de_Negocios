@extends('layouts.navbars')

@section('title', 'Gestión de pagos')

@section('content')
    <style>
        /* Estilos limitados a esta pantalla; los formularios conservan sus acciones. */
        #gestion-pagos .pago-card {
            box-shadow: 0 6px 24px rgb(15 23 42 / .04);
        }

        #gestion-pagos .pago-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
        }

        #gestion-pagos .pago-form>* {
            margin-top: 0;
        }

        #gestion-pagos .pago-form label {
            display: block;
            margin-bottom: .5rem;
        }

        #gestion-pagos .pago-form input,
        #gestion-pagos .pago-form textarea {
            width: 100%;
        }

        #gestion-pagos .pago-form .pago-form-footer {
            grid-column: 1 / -1;
        }

        #gestion-pagos input:focus-visible,
        #gestion-pagos textarea:focus-visible,
        #gestion-pagos select:focus-visible,
        #gestion-pagos button:focus-visible,
        #gestion-pagos a:focus-visible,
        #gestion-pagos summary:focus-visible {
            outline: 2px solid #22c55e;
            outline-offset: 3px;
        }

        #gestion-pagos summary {
            padding-block: .25rem;
        }

        #gestion-pagos .pago-acciones:empty {
            display: none;
        }

        @media (min-width: 768px) {
            #gestion-pagos .pago-form {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1.35fr);
                align-items: start;
            }

            #gestion-pagos .pago-entrega {
                display: flex;
                align-items: end;
                gap: .75rem;
            }

            #gestion-pagos .pago-entrega label {
                flex: 1;
            }

            #gestion-pagos .pago-entrega button {
                flex-shrink: 0;
                margin-top: 0;
            }
        }
    </style>
    <div id="gestion-pagos" class="mx-auto max-w-7xl space-y-6">
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
                class="rounded-xl bg-sidan-50 p-4 text-sm text-sidan-600 dark:bg-sidan-500/10 dark:text-sidan-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div role="alert" class="rounded-xl bg-red-50 p-4 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-300">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $sumaIngresosNoEjercidos = \Illuminate\Support\Facades\DB::table('tbl_cierres_orden')
                ->where('clasificacion', 'ingreso_no_ejercido')
                ->sum('anticipo_sin_entrega');
            $sumaPorClasificar = \Illuminate\Support\Facades\DB::table('tbl_cierres_orden')
                ->where('clasificacion', 'pendiente_clasificacion')
                ->sum('anticipo_sin_entrega');
        @endphp
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
            <div
                class="rounded-2xl border border-sidan-200 bg-sidan-50/60 p-5 text-sidan-600 dark:border-sidan-500/30 dark:bg-sidan-500/5 dark:text-sidan-100">
                <p>Ingresos por anticipos no ejercidos autorizados</p>
                <p class="text-xl font-bold">${{ number_format((float) $sumaIngresosNoEjercidos, 2) }}</p>
                <p class="text-xs">No son entradas adicionales de efectivo.</p>
            </div>
            <div
                class="rounded-2xl border border-amber-200 bg-amber-50/60 p-5 text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/5 dark:text-amber-100">
                <p>Anticipos retenidos pendientes de clasificación</p>
                <p class="text-xl font-bold">${{ number_format((float) $sumaPorClasificar, 2) }}</p>
                <p class="text-xs">Todavía no representan ingresos reconocidos.</p>
            </div>
        </div>
        <a href="{{ route('admin.ordenes.vencidas') }}"
            class="inline-block rounded-xl bg-amber-500 px-4 py-2 font-semibold text-white">Órdenes vencidas con dinero
            recibido</a>
        <section aria-labelledby="titulo-filtros"
            class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-slate-900">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 id="titulo-filtros" class="font-bold text-slate-900 dark:text-white">Explorar pagos</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Seleccioná un estado para consultar sus pagos
                        en todas las páginas.</p>
                </div>
                <span
                    class="rounded-full bg-sidan-50 px-3 py-1 text-xs font-semibold text-sidan-600 dark:bg-sidan-500/10 dark:text-sidan-300">
                    {{ $pagos->total() }} {{ $pagos->total() === 1 ? 'resultado' : 'resultados' }}
                </span>
            </div>
            <form method="GET" action="{{ route('admin.pagos.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="w-full sm:w-72">
                    <label for="filtro-estado"
                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Estado del pago</label>
                    <select id="filtro-estado" name="estado"
                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white">
                        <option value="">Todos los estados ({{ $estadosPagos->sum() }})</option>
                        @foreach ($estadosPagos as $estado => $cantidad)
                            <option value="{{ $estado }}" @selected($estadoSeleccionado === $estado)>
                                {{ str_replace('_', ' ', ucfirst($estado)) }} ({{ $cantidad }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="rounded-xl bg-sidan-500 px-5 py-3 text-sm font-bold text-white hover:bg-sidan-600">Aplicar
                    filtro</button>
                @if ($estadoSeleccionado !== '')
                    <a href="{{ route('admin.pagos.index') }}"
                        class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 dark:border-white/20 dark:text-slate-300 dark:hover:bg-white/5">Ver
                        todos</a>
                @endif
            </form>
        </section>
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-slate-500 dark:text-slate-400">
            <p class="font-semibold text-slate-700 dark:text-slate-200">
                {{ $estadoSeleccionado !== '' ? str_replace('_', ' ', ucfirst($estadoSeleccionado)) : 'Todos los pagos' }}
            </p>
            <p>Mostrando {{ $pagos->firstItem() ?? 0 }}–{{ $pagos->lastItem() ?? 0 }} de {{ $pagos->total() }}</p>
        </div>
        <div class="space-y-6">
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
                    $revisable =
                        $manual &&
                        $ordenPago?->estado === 'pendiente_pago' &&
                        in_array($pago->estado, ['pendiente', 'parcial'], true) &&
                        $abonoPendiente;
                @endphp

                <article
                    class="pago-card overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-4 p-5">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-bold text-slate-900 dark:text-white">
                                    Orden #{{ $pago->id_orden }}
                                </h2>

                                <span
                                    class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                    {{ ucfirst($pago->proveedor) }}
                                </span>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold
                                    {{ $pago->estado === 'pendiente'
                                        ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400'
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

                        <div class="rounded-xl bg-sidan-50 px-4 py-3 text-right dark:bg-sidan-500/10">
                            <p class="text-xs font-semibold text-sidan-600 dark:text-sidan-300">Total de la orden</p>
                            <p class="text-2xl font-bold text-sidan-600 dark:text-sidan-400">
                                ${{ number_format((float) $pago->monto, 2) }}
                            </p>
                        </div>
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
                                            <small class="block text-sidan-600 dark:text-sidan-400">
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
                                            <small
                                                class="mt-1 block text-xs font-semibold text-amber-700 dark:text-amber-300">
                                                {{ $esAnticipo && $minimoDetalle < $totalDetalle ? 'Anticipo mínimo de este producto' : 'Pago completo de este producto' }}:
                                                ${{ number_format($minimoDetalle / 100, 2) }}
                                            </small>
                                        @endif
                                    </span>

                                    <span class="shrink-0 font-semibold">
                                        ${{ number_format((float) $detalle->subtotal - (float) $detalle->descuento, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </details>

                    @if ($manual)
                        <div class="border-t border-slate-100 px-5 py-4 text-sm dark:border-white/5">
                            @if ($esProducto)
                                <div
                                    class="mb-4 rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                                    <p class="text-xs font-bold uppercase tracking-wide">
                                        {{ $esAnticipo ? 'Modalidad: anticipo' : 'Modalidad: pago completo' }}
                                    </p>
                                    <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                                        <span class="font-semibold">
                                            {{ $esAnticipo ? 'Anticipo mínimo inicial solicitado' : 'Primer pago requerido' }}
                                        </span>
                                        <strong
                                            class="text-xl">${{ number_format($minimoInicialCentavos / 100, 2) }}</strong>
                                    </div>
                                    @if ($esAnticipo)
                                        <p class="mt-2 font-semibold">
                                            @if ($faltanteMinimoCentavos > 0)
                                                Falta para cubrir el anticipo:
                                                ${{ number_format($faltanteMinimoCentavos / 100, 2) }}
                                            @else
                                                Anticipo mínimo cubierto. Saldo de toda la orden:
                                                ${{ number_format($saldo / 100, 2) }}
                                            @endif
                                        </p>
                                        <p class="mt-1 text-xs">
                                            El importe mínimo corresponde a las condiciones de la orden, incluidos los
                                            productos que deben pagarse al 100%.
                                        </p>
                                    @else
                                        <p class="mt-2 text-xs">Esta orden requiere pago íntegro; no se seleccionó modalidad
                                            de anticipo.</p>
                                    @endif
                                </div>
                            @endif
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-xl bg-sidan-50 p-4 dark:bg-sidan-500/10">
                                    <p class="text-xs font-semibold text-sidan-600 dark:text-sidan-300">Dinero
                                        acreditado</p>
                                    <p class="mt-1 text-xl font-bold text-sidan-600 dark:text-sidan-300">
                                        ${{ number_format($acreditado / 100, 2) }}</p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-4 dark:bg-white/5">
                                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Saldo por pagar</p>
                                    <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">
                                        ${{ number_format($saldo / 100, 2) }}</p>
                                </div>
                            </div>
                            <h3 class="mt-5 font-bold text-slate-800 dark:text-white">Comprobantes y abonos</h3>
                            <div class="mt-3 space-y-2">
                                @foreach ($pago->abonos as $abono)
                                    <div
                                        class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-slate-50 p-3 dark:bg-white/5">
                                        <div>
                                            <span class="font-semibold">Comprobante #{{ $abono->id_abono }}</span>
                                            <span class="ml-2 text-xs">{{ ucfirst($abono->estado) }}</span>
                                            @if ($abono->estado === 'confirmado')
                                                <span
                                                    class="ml-2 text-sidan-600">${{ number_format((float) $abono->monto_recibido, 2) }}
                                                    recibidos</span>
                                            @endif
                                            @if ($abono->observacion_revision)
                                                <p class="mt-1 text-xs text-slate-500">{{ $abono->observacion_revision }}
                                                </p>
                                            @endif
                                        </div>
                                        @if ($abono->comprobante_path)
                                            <a href="{{ route('admin.pagos.abonos.comprobante', $abono) }}" target="_blank"
                                                rel="noopener"
                                                class="text-sm font-semibold text-blue-600 underline dark:text-blue-400">
                                                Ver comprobante
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($ordenPago?->tipo_orden === 'productos')
                        <div class="border-t border-slate-200 p-4 space-y-3 dark:border-white/10">
                            <h3 class="font-bold text-sm">Entregas por producto</h3>
                            @foreach ($ordenPago->detalles as $detalle)
                                @php
                                    $dist = app(\App\Services\AbonosProductosService::class);
                                    $pagado = $dist->aplicado($detalle);
                                    $autorizado =
                                        $pagado >= $dist->neto($detalle) ||
                                        ($ordenPago->estado === 'pagada' && $pago->estado === 'confirmado');
                                @endphp
                                <div class="rounded-xl bg-slate-50 p-3 text-sm dark:bg-white/5">
                                    <p class="font-semibold">{{ $detalle->nombre_concepto }} · entregado
                                        {{ $detalle->cantidad_entregada }}/{{ $detalle->cantidad }}</p>
                                    <p class="text-xs">Acreditado al producto: ${{ number_format($pagado / 100, 2) }} /
                                        ${{ number_format($dist->neto($detalle) / 100, 2) }}</p>
                                    @if ($autorizado && $detalle->cantidad_entregada < $detalle->cantidad)
                                        <form method="POST"
                                            action="{{ route('admin.ordenes.entregar', [$ordenPago, $detalle]) }}"
                                            onsubmit="return confirm('¿Entregaste físicamente todas las unidades de esta línea?');"
                                            class="pago-entrega mt-3 space-y-2">
                                            @csrf
                                            <label
                                                class="block text-xs font-semibold text-slate-600 dark:text-slate-300">Observación
                                                de entrega (opcional)
                                                <input name="observacion" maxlength="1000"
                                                    class="mt-2 w-full rounded-lg border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white"
                                                    placeholder="Agregá un detalle de la entrega">
                                            </label>
                                            <button
                                                class="rounded-lg bg-sidan-500 px-3 py-2 font-semibold text-white">Registrar
                                                entrega completa</button>
                                        </form>
                                    @elseif(!$autorizado)
                                        <p class="text-amber-600 dark:text-amber-300">Entrega bloqueada: producto no pagado al 100%.</p>
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

                    <div
                        class="pago-acciones space-y-4 border-t border-slate-100 bg-slate-50/70 p-5 dark:border-white/5 dark:bg-white/[0.03]">

                        @if ($revisable)
                            <h3 class="font-bold text-slate-900 dark:text-white">Registrar recepción de dinero</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $pago->proveedor === 'transferencia'
                                    ? 'Aprobá después de verificar que el abono se recibió en la cuenta.'
                                    : 'Aprobá después de recibir el efectivo.' }}
                            </p>

                            <form method="POST" action="{{ route('admin.pagos.aprobar', $pago) }}" class="pago-form"
                                onsubmit="return confirm('¿Confirmás que recibiste realmente el importe indicado?');">
                                @csrf
                                <div>
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
                                            Registrá solamente el dinero realmente verificado. Si es menor al anticipo
                                            requerido,
                                            SIDAN conservará el abono y mostrará al comprador el anticipo faltante.
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-600 dark:text-slate-300"
                                        for="observacion-{{ $pago->id_pago }}">Observación (opcional)</label>
                                    <textarea id="observacion-{{ $pago->id_pago }}" name="observacion" rows="3" maxlength="1000"
                                        placeholder="Ejemplo: Depósito verificado en el banco"
                                        class="w-full rounded-lg border border-slate-300 bg-white p-3 text-slate-900 dark:border-white/20 dark:bg-slate-900 dark:text-white"></textarea>
                                </div>
                                <div class="pago-form-footer border-t border-slate-200 pt-4 dark:border-white/10">
                                    <button type="submit"
                                        class="w-full sm:w-auto rounded-xl bg-sidan-500 px-4 py-2 text-sm font-bold text-white hover:bg-sidan-600">
                                        {{ $pago->proveedor === 'efectivo' ? 'Registrar efectivo realmente recibido' : 'Registrar dinero recibido' }}
                                    </button>
                                </div>
                            </form>

                            <details
                                class="rounded-xl border border-red-200 bg-white p-4 dark:border-red-500/20 dark:bg-slate-900">
                                <summary class="cursor-pointer text-sm font-semibold text-red-600 dark:text-red-400">
                                    {{ $pago->proveedor === 'efectivo' ? 'Rechazar cobro no recibido' : 'Rechazar comprobante' }}
                                </summary>

                                <form method="POST" action="{{ route('admin.pagos.rechazar', $pago) }}"
                                    class="mt-3 space-y-3" onsubmit="return confirm(@js($pago->proveedor === 'efectivo' ? '¿Confirmás la cancelación del efectivo y la liberación de la reserva?' : '¿Confirmás que el comprobante es inválido? Se conservarán los abonos acreditados.'))">
                                    @csrf

                                    <label for="motivo-{{ $pago->id_pago }}"
                                        class="block text-sm text-slate-600 dark:text-slate-300">
                                        {{ $pago->proveedor === 'efectivo' ? 'Motivo de cancelación (visible para el usuario)' : 'Motivo del rechazo' }}
                                    </label>

                                    <textarea id="motivo-{{ $pago->id_pago }}" name="motivo" required maxlength="1000" rows="2"
                                        class="w-full rounded-xl border border-slate-300 bg-white p-3 text-sm text-slate-900 dark:border-white/20 dark:bg-slate-900 dark:text-white"></textarea>

                                    <button type="submit"
                                        class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                        {{ $pago->proveedor === 'efectivo'
                                            ? ($acreditado > 0
                                                ? 'Rechazar intento, conservar abonos'
                                                : 'Cancelar sin cobros')
                                            : ($acreditado > 0
                                                ? 'Rechazar este comprobante'
                                                : 'Rechazar y liberar reserva') }}
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
                <div
                    class="rounded-2xl border border-slate-200 p-10 text-center text-slate-500 dark:border-white/10 dark:text-slate-400">
                    {{ $estadoSeleccionado !== '' ? 'No hay pagos que coincidan con este estado.' : 'No hay pagos registrados.' }}
                </div>
            @endforelse
        </div>

        {{ $pagos->links() }}
    </div>
@endsection
