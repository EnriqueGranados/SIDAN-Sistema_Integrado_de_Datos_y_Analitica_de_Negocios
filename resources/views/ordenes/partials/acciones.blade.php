@php
    $gestion = app(\App\Services\GestionOrdenService::class);
    $pagoActual = $orden->pago;
    $distribucion = app(\App\Services\AbonosProductosService::class);
    $minimoCalculado = $orden->tipo_orden === 'productos' ? $distribucion->minimoOrden($orden) : $gestion->centavos($orden->total);
    $puedeAnticipo = $orden->tipo_orden === 'productos' && $minimoCalculado > 0 && $minimoCalculado < $gestion->centavos($orden->total);
    $minimoActual = $pagoActual && $orden->modalidad_pago==='anticipo'
        ? $gestion->centavos($orden->minimo_inicial) : $gestion->centavos($orden->total);

    $vigente = $orden->estado === 'pendiente_pago' && (!$orden->expira_en || $orden->expira_en->isFuture());

    $sinPago = !$pagoActual;
    $totalCero = $gestion->centavos($orden->total) === 0;

    $usoPromocion = \Illuminate\Support\Facades\DB::table('tbl_usos_promociones')
        ->where('id_orden', $orden->id_orden)
        ->first();

    $cuenta = config('pagos.transferencia');
    $cuentaLista =
        filled($cuenta['banco'] ?? null) && filled($cuenta['titular'] ?? null) && filled($cuenta['cuenta'] ?? null);

    $manualPendiente =
        $pagoActual &&
        in_array($pagoActual->proveedor, ['transferencia', 'efectivo'], true) &&
        $pagoActual->estado === 'pendiente';

    $parcial = $vigente && in_array($pagoActual?->proveedor, ['transferencia','efectivo'],true)
        && $pagoActual->estado === 'parcial';
    $acreditadoCentavos = $parcial ? $pagoActual->montoAcreditadoCentavos() : 0;
    $saldoCentavos = $parcial
        ? max(0, $gestion->centavos($orden->total) - $acreditadoCentavos) : 0;
    $abonoEnRevision = $parcial && $pagoActual->proveedor==='transferencia'
        ? $pagoActual->abonos->firstWhere('estado', 'pendiente') : null;
    $faltanteAnticipo = $parcial ? $distribucion->faltanteAnticipo($orden) : 0;
    $ultimoAbono = $parcial ? $pagoActual->abonos->last() : null;
@endphp

<div class="space-y-4 border-t border-slate-200 p-6 dark:border-white/10">
    @if (session('success'))
        <div role="status"
            class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-2 text-sm">
        <div class="flex justify-between gap-3 text-slate-500 dark:text-slate-400">
            <span>Subtotal</span>
            <span>${{ number_format((float) $orden->subtotal, 2) }}</span>
        </div>

        <div class="flex justify-between gap-3 text-emerald-600 dark:text-emerald-400">
            <span>Descuento</span>
            <span>−${{ number_format((float) $orden->descuento_total, 2) }}</span>
        </div>

        @if ($usoPromocion)
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Código {{ $usoPromocion->codigo }}:
                {{ $usoPromocion->unidades }} unidad(es) con descuento.
            </p>
        @endif
    </div>

    @if ($vigente && $sinPago && $gestion->admiteCodigo($orden))
        <form method="POST" action="{{ route('sidan.ordenes.promocion', $orden) }}" class="space-y-2">
            @csrf

            <label for="codigo-promocional" class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                Código promocional
            </label>

            <div class="flex gap-2">
                <input id="codigo-promocional" name="codigo" type="text" maxlength="60" required
                    value="{{ old('codigo', $usoPromocion->codigo ?? '') }}" placeholder="Ingresá tu código"
                    class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 dark:border-white/20 dark:bg-slate-900 dark:text-white">

                <button type="submit"
                    class="rounded-xl bg-[#102b54] px-4 py-2 text-sm font-bold text-white hover:bg-[#173b70]">
                    Aplicar
                </button>
            </div>

            @error('codigo')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </form>
    @endif

    @if(!$vigente && $pagoActual && in_array($pagoActual->proveedor,['transferencia','efectivo'],true)
        && $pagoActual->montoAcreditadoCentavos()>0 && $orden->estado!=='pagada')
        <div class="rounded-xl border border-amber-500 bg-amber-50 p-4 text-amber-900 dark:bg-amber-500/10 dark:text-amber-100">
            <h3 class="font-bold">Pago vencido con dinero recibido</h3>
            <p>Conservamos ${{ number_format($pagoActual->montoAcreditadoCentavos()/100,2) }} acreditados.
                La orden requiere revisión de administración. No enviés nuevos pagos sin autorización.</p>
        </div>
    @endif
    @if ($orden->tipo_orden === 'productos' && $pagoActual && in_array($pagoActual->proveedor,['transferencia','efectivo'],true))
        <div class="rounded-xl border border-slate-300 p-4 text-sm dark:border-white/20">
            <h3 class="font-bold mb-2">Estado de pago por producto</h3>
            @foreach($orden->detalles as $detalle)
                @php $p=$distribucion->aplicado($detalle); $n=$distribucion->neto($detalle); $m=$distribucion->minimo($detalle); @endphp
                <div class="py-2 border-t border-slate-200 dark:border-white/10">
                    <p class="font-semibold">{{ $detalle->nombre_concepto }} × {{ $detalle->cantidad }}</p>
                    <p>Acreditado ${{ number_format($p/100,2) }} / ${{ number_format($n/100,2) }}
                        · {{ $p >= $n ? 'Completamente pagado' : ($p >= $m ? 'Anticipo cubierto' : 'Falta anticipo') }}</p>
                    <p class="text-xs">{{ $detalle->cantidad_entregada }} de {{ $detalle->cantidad }} entregados</p>
                </div>
            @endforeach
            @if($orden->limite_saldo_en)
                <p class="mt-3 font-semibold">Fecha límite: {{ $orden->limite_saldo_en->timezone('America/El_Salvador')->format('d/m/Y H:i') }}</p>
            @endif
        </div>
    @endif
    @if ($parcial)
        <div role="status"
            class="space-y-4 rounded-xl border border-amber-300 bg-amber-50 p-5 text-sm text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
            <h3 class="font-bold text-lg">Pago parcial acreditado ({{ $pagoActual->proveedor }})</h3>
            <p>El administrador verificó un abono, pero tu orden todavía no se encuentra pagada.
                No necesitás crear otra orden.</p>
            <p class="font-semibold">Los abonos ya acreditados no son reembolsables,
                aunque decidas no completar el saldo restante.</p>
            <div class="space-y-1 font-semibold">
                <p>Total de la orden: ${{ number_format((float) $orden->total, 2) }}</p>
                <p>Dinero acreditado: ${{ number_format($acreditadoCentavos / 100, 2) }}</p>
                @if($orden->modalidad_pago === 'anticipo')
                    <p class="text-lg">{{ $faltanteAnticipo > 0 ? 'Faltante para cumplir el anticipo mínimo' : 'Anticipo mínimo cubierto' }}:
                        ${{ number_format($faltanteAnticipo / 100, 2) }}</p>
                @endif
                <p class="text-lg">Saldo para pagar toda la orden: ${{ number_format($saldoCentavos / 100, 2) }}</p>
            </div>
            @if ($ultimoAbono?->estado === 'rechazado')
                <p class="rounded-lg bg-red-100 p-3 text-red-800 dark:bg-red-500/15 dark:text-red-200">
                    El comprobante más reciente fue rechazado.
                    Motivo: {{ $ultimoAbono->observacion_revision }}
                    Tus abonos anteriores siguen acreditados.
                </p>
            @endif
            @if ($pagoActual->proveedor==='efectivo')
                <p class="rounded-lg border border-amber-300 p-3">Para completar tu saldo de
                    <strong>${{ number_format($saldoCentavos / 100,2) }}</strong>, acercate al lugar de cobro.
                    El administrador registrará cada nuevo importe de efectivo recibido sobre esta misma orden.</p>
            @elseif ($abonoEnRevision)
                <p class="font-semibold">Tu comprobante complementario ya fue enviado y está en revisión.
                    No necesitás volver a subirlo.</p>
            @elseif ($cuentaLista && $saldoCentavos > 0)
                <div class="rounded-lg border border-amber-200 bg-white p-4 text-slate-800 dark:border-white/10 dark:bg-slate-900 dark:text-white">
                    <h4 class="font-bold">Completá tu transferencia</h4>
                    <p class="mt-2">Transferí únicamente <strong>${{ number_format($saldoCentavos / 100, 2) }}</strong>
                        a la misma cuenta y enviá el nuevo comprobante aquí.</p>
                    <p class="mt-2">Banco: <strong>{{ $cuenta['banco'] }}</strong></p>
                    <p>Titular: <strong>{{ $cuenta['titular'] }}</strong></p>
                    <p>Cuenta: <strong>{{ $cuenta['cuenta'] }}</strong></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Referencia sugerida: SIDAN-ORD-{{ $orden->id_orden }}</p>
                    <form method="POST" enctype="multipart/form-data"
                        action="{{ route('sidan.ordenes.complemento', $orden) }}" class="mt-4 space-y-3">
                        @csrf
                        <label for="complemento-{{ $orden->id_orden }}" class="block font-semibold">
                            Nuevo comprobante bancario
                        </label>
                        <input id="complemento-{{ $orden->id_orden }}" type="file" name="comprobante"
                            required accept="image/jpeg,image/png,image/webp"
                            class="block w-full rounded-lg border border-slate-300 p-2 text-sm dark:border-white/20">
                        <button type="submit"
                            class="w-full rounded-lg bg-emerald-600 px-4 py-3 font-bold text-white hover:bg-emerald-700">
                            Enviar comprobante del saldo faltante
                        </button>
                    </form>
                    <p class="mt-2 text-xs">La orden se confirmará únicamente después de que
                        administración verifique el monto restante. Los abonos acreditados no son reembolsables.</p>
                </div>
            @else
                <p>Contactá al administrador para completar el saldo pendiente.</p>
            @endif
        </div>
    @elseif ($manualPendiente)
        <div role="status"
            class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">
            <p class="font-bold">
                {{ $pagoActual->proveedor === 'transferencia'
                    ? 'Transferencia pendiente de revisión'
                    : 'Pago en efectivo pendiente' }}
            </p>

            <p class="mt-2">
                {{ $pagoActual->proveedor === 'transferencia'
                    ? 'Recibimos tu comprobante. El administrador debe validar la transferencia.'
                    : 'Realizá el pago en el lugar y horario indicados en la actividad. El administrador confirmará su recepción.' }}
            </p>

            @if ($pagoActual->proveedor === 'efectivo')
                <p class="mt-2 font-semibold">
                    La reserva estará vigente hasta la fecha límite indicada en la orden.
                    El administrador deberá confirmar el efectivo realmente recibido.
                </p>
            @else
                <p class="mt-2">Tu orden todavía no está pagada.</p>
            @endif
        </div>
    @elseif ($pagoActual?->estado === 'rechazado')
        <div class="rounded-xl bg-red-50 p-4 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-300">
            <p class="font-bold">
                {{ $pagoActual->proveedor === 'efectivo' ? 'Orden cancelada por administración' : 'Pago rechazado' }}
            </p>
            <p class="mt-1">Motivo: {{ $pagoActual->observacion_revision }}</p>
            <p class="mt-2">La reserva fue liberada. Podés crear una nueva orden con el método de pago que prefirás.
            </p>
            <a href="{{ route('activities.index') }}"
                class="mt-3 inline-flex rounded-xl bg-emerald-600 px-4 py-2 font-semibold text-white hover:bg-emerald-700">
                Explorar actividades
            </a>
        </div>
    @elseif ($vigente)
        @if ($sinPago && $totalCero)
            <form method="POST" action="{{ route('sidan.ordenes.gratuita', $orden) }}">
                @csrf
                <button type="submit"
                    class="w-full rounded-xl bg-emerald-600 px-4 py-3 font-bold text-white hover:bg-emerald-700">
                    Confirmar orden sin cobro
                </button>
            </form>
        @else
            @if ($sinPago || ($pagoActual->proveedor === 'wompi' && $pagoActual->estado === 'pendiente'))
                <form id="wompi-payment-form" method="POST"
                    action="{{ route('sidan.pagos.iniciar', $orden->id_orden) }}" target="wompiPayment">
                    @csrf

                    <button id="wompi-payment-button" type="submit"
                        class="w-full rounded-xl bg-emerald-600 px-4 py-3 font-bold text-white hover:bg-emerald-700">
                        {{ $sinPago ? 'Pagar con Wompi' : 'Continuar con Wompi' }}
                    </button>
                </form>

                <div id="payment-waiting"
                    class="hidden rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300">
                    Esperando confirmación de Wompi. Completá el pago en la otra pestaña.
                </div>
            @endif

            @if ($sinPago)
                <div id="opciones-pago-manual" class="space-y-3">
                    <button type="button" data-abrir-pago="modal-transferencia"
                        class="w-full rounded-xl border border-[#102b54]/20 px-4 py-3 font-bold text-[#102b54] hover:bg-slate-50 dark:border-white/20 dark:text-white dark:hover:bg-white/5">
                        Pago por transferencia
                    </button>

                    <button type="button" data-abrir-pago="modal-efectivo"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 font-bold text-slate-700 hover:bg-slate-50 dark:border-white/20 dark:text-slate-200 dark:hover:bg-white/5">
                        Pago en efectivo
                    </button>
                </div>
            @endif
        @endif
    @endif

    @include('ordenes.partials.cancelar', ['orden' => $orden])
</div>

@if ($vigente && $sinPago && !$totalCero)
    <dialog id="modal-transferencia"
        class="w-[calc(100%-2rem)] max-w-lg rounded-2xl bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-black/60 dark:bg-slate-900 dark:text-white">
        <div class="p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-bold">Pago por transferencia</h2>
                <button type="button" data-cerrar-pago aria-label="Cerrar"
                    class="rounded-lg px-3 py-1 text-2xl">×</button>
            </div>

            @if ($cuentaLista)
                <dl class="mt-5 space-y-3 rounded-xl bg-slate-50 p-4 text-sm dark:bg-white/5">
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Banco</dt>
                        <dd class="font-semibold">{{ $cuenta['banco'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Titular</dt>
                        <dd class="font-semibold">{{ $cuenta['titular'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Cuenta</dt>
                        <dd class="break-all font-semibold">{{ $cuenta['cuenta'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Tipo</dt>
                        <dd>{{ $cuenta['tipo'] }}</dd>
                    </div>
                    <div class="border-t border-slate-200 pt-3 dark:border-white/10">
                        <dt>Monto recomendado a transferir</dt>
                        <dd id="transferencia-monto-recomendado" data-total="{{ number_format((float)$orden->total,2,'.','') }}" data-minimo="{{ number_format($minimoCalculado/100,2,'.','') }}" class="text-2xl font-bold text-emerald-600">
                            ${{ number_format((float) $orden->total, 2) }}
                        </dd>
                    </div>
                </dl>

                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                    Indicá la referencia SIDAN-ORD-{{ $orden->id_orden }} cuando tu banco lo permita.
                    Después adjuntá la captura del comprobante.
                </p>

                <form method="POST" enctype="multipart/form-data" action="{{ route('sidan.ordenes.manual', $orden) }}"
                    class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="metodo" value="transferencia">
                    <fieldset class="my-4 rounded-xl border border-emerald-500/20 p-3 text-sm">
                        <legend class="font-bold">Modalidad de pago</legend>
                        <label class="block mt-2"><input type="radio" name="modalidad" value="completo" checked data-modalidad-manual="transferencia"> Pago completo: ${{ number_format((float)$orden->total,2) }}</label>
                        @if($puedeAnticipo)
                        <label class="block mt-2"><input type="radio" name="modalidad" value="anticipo" data-modalidad-manual="transferencia"> Anticipo mínimo: ${{ number_format($minimoCalculado/100,2) }}</label>
                        @endif
                        <p class="mt-2 text-xs">Los productos de pago completo se cubren primero. Los abonos confirmados no son reembolsables, sin perjuicio de los derechos legales aplicables.</p>
                    </fieldset>

                    <div>
                        <label for="comprobante-transferencia" class="mb-2 block text-sm font-semibold">
                            Comprobante de transferencia
                        </label>

                        <input id="comprobante-transferencia" type="file" name="comprobante" required
                            accept="image/jpeg,image/png,image/webp"
                            class="block w-full rounded-xl border border-slate-300 p-2 text-sm dark:border-white/20">

                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            JPG, PNG o WEBP. Máximo 5 MB.
                        </p>
                    </div>

                    <p class="text-sm text-amber-700 dark:text-amber-300">
                        Subir el comprobante no confirma el pago. El administrador debe validarlo.
                        Los pagos y abonos acreditados no son reembolsables.
                        Si transferís menos del importe total, deberás completar el saldo restante.
                    </p>

                    <button type="submit"
                        class="w-full rounded-xl bg-emerald-600 px-4 py-3 font-bold text-white hover:bg-emerald-700">
                        Enviar comprobante
                    </button>
                </form>
            @else
                <p class="mt-5 text-sm text-amber-700 dark:text-amber-300">
                    La cuenta bancaria todavía no está configurada. Contactá al administrador.
                </p>
            @endif
        </div>
    </dialog>

    <dialog id="modal-efectivo"
        class="w-[calc(100%-2rem)] max-w-lg rounded-2xl bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-black/60 dark:bg-slate-900 dark:text-white">
        <div class="p-6">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-bold">Pago en efectivo</h2>
                <button type="button" data-cerrar-pago aria-label="Cerrar"
                    class="rounded-lg px-3 py-1 text-2xl">×</button>
            </div>

            <p class="mt-5 text-sm leading-6 text-slate-600 dark:text-slate-300">
                El pago se realizará presencialmente en la ubicación y horario descritos
                en la información de la actividad. Tu orden permanecerá pendiente hasta
                que el administrador reciba el dinero y confirme el pago.
            </p>

            <a href="{{ route('activities.show', $orden->actividad->slug) }}" target="_blank" rel="noopener"
                class="mt-3 inline-block text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                Consultar lugar y horario de la actividad →
            </a>

            <div class="my-5 rounded-xl bg-slate-50 p-4 dark:bg-white/5">
                <p class="text-sm">Total a pagar</p>
                <p class="mt-1 text-2xl font-bold">
                    ${{ number_format((float) $orden->total, 2) }}
                </p>
            </div>

            <form method="POST" action="{{ route('sidan.ordenes.manual', $orden) }}">
                @csrf
                <input type="hidden" name="metodo" value="efectivo">
                <fieldset class="my-4 rounded-xl border border-emerald-500/20 p-3 text-sm">
                    <legend class="font-bold">Modalidad de pago</legend>
                    <label class="block mt-2"><input type="radio" name="modalidad" value="completo" checked> Pago completo: ${{ number_format((float)$orden->total,2) }}</label>
                    @if($puedeAnticipo)
                    <label class="block mt-2"><input type="radio" name="modalidad" value="anticipo"> Anticipo mínimo: ${{ number_format($minimoCalculado/100,2) }}</label>
                    @endif
                    <p class="mt-2 text-xs">El administrador registrará el dinero recibido. Los abonos confirmados no son reembolsables, salvo los derechos que correspondan por ley.</p>
                </fieldset>

                <button type="submit"
                    class="w-full rounded-xl bg-emerald-600 px-4 py-3 font-bold text-white hover:bg-emerald-700">
                    Confirmar pago presencial pendiente
                </button>
            </form>
        </div>
    </dialog>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Actualizar el importe sugerido según el anticipo/completo.
        document.querySelectorAll('input[name="modalidad"][data-modalidad-manual]').forEach(input => {
            input.addEventListener('change', () => {
                const monto = document.getElementById('transferencia-monto-recomendado');
                if (monto && input.checked) {
                    monto.textContent = '$' + Number(input.value === 'anticipo'
                        ? monto.dataset.minimo : monto.dataset.total).toFixed(2);
                }
            });
        });
        // Abrir el modal seleccionado utilizando el diálogo nativo del navegador.
        document.querySelectorAll('[data-abrir-pago]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById(button.dataset.abrirPago)?.showModal();
            });
        });

        // Cerrar únicamente el diálogo que contiene el botón.
        document.querySelectorAll('[data-cerrar-pago]').forEach((button) => {
            button.addEventListener('click', () => {
                button.closest('dialog')?.close();
            });
        });

        // Evitar seleccionar otro método mientras se inicia Wompi.
        document.getElementById('wompi-payment-form')?.addEventListener('submit', () => {
            document.getElementById('opciones-pago-manual')?.classList.add('hidden');
        });
    });
</script>
