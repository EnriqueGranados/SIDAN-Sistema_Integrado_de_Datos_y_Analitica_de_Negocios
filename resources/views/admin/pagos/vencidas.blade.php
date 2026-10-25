@extends('layouts.navbars')
@section('title', 'Regularización de órdenes vencidas')
@section('content')
@php $registro = app(\App\Services\RegularizacionVencidasService::class); $reparto = app(\App\Services\AbonosProductosService::class); @endphp
<div class="mx-auto max-w-6xl space-y-6 text-slate-900 dark:text-white">
    <header class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.pagos.index') }}" class="text-sm font-bold text-sidan-600 dark:text-sidan-400">← Gestión de pagos</a>
            <h1 class="mt-3 text-2xl font-black">Órdenes vencidas con dinero recibido</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Regularización únicamente presencial durante los 30 días posteriores al vencimiento. Los pagos acreditados y las entregas se conservan.</p>
        </div>
    </header>
    @if(session('success'))<div class="rounded-xl border border-sidan-200 bg-sidan-50 p-4 text-sm text-sidan-700 dark:border-sidan-500/30 dark:bg-sidan-500/10 dark:text-sidan-200">{{ session('success') }}</div>@endif
    @if($errors->any())<div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"><strong>Revisá la operación:</strong><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="grid gap-3 sm:grid-cols-2">
        <a href="{{ route('admin.ordenes.vencidas', array_filter(['vista'=>'abiertas','actividad'=>$actividad ?: null])) }}" class="rounded-2xl border p-5 {{ $vista === 'abiertas' ? 'border-amber-400 bg-amber-50 dark:border-amber-500/30 dark:bg-amber-500/10' : 'border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900' }}">
            <p class="text-sm font-bold">En regularización</p><p class="mt-1 text-2xl font-black">{{ $abiertas }}</p><p class="text-xs text-slate-500 dark:text-slate-400">Se permite recibir efectivo, revisar comprobantes antiguos y entregar artículos pagados.</p>
        </a>
        <a href="{{ route('admin.ordenes.vencidas', array_filter(['vista'=>'finalizadas','actividad'=>$actividad ?: null])) }}" class="rounded-2xl border p-5 {{ $vista === 'finalizadas' ? 'border-sidan-400 bg-sidan-50 dark:border-sidan-500/30 dark:bg-sidan-500/10' : 'border-slate-200 bg-white dark:border-white/10 dark:bg-slate-900' }}">
            <p class="text-sm font-bold">Finalizadas y regularizadas</p><p class="mt-1 text-2xl font-black">{{ $finalizadas }}</p><p class="text-xs text-slate-500 dark:text-slate-400">Pago completo, finalización solicitada o plazo de 30 días agotado.</p>
        </a>
    </div>
    @if($actividad)
        <p class="text-sm text-slate-500 dark:text-slate-400">Filtrado por actividad #{{ $actividad }} · <a class="font-bold text-sidan-600 underline" href="{{ route('admin.ordenes.vencidas', ['vista' => $vista]) }}">Ver todas</a></p>
    @endif
    <div class="space-y-3">
        @forelse($ordenes as $orden)
            @php
                $recibido = $orden->pago?->montoAcreditadoCentavos() ?? 0;
                $total = (int) round((float)$orden->total * 100);
                $saldo = max(0, $total - $recibido);
                $plazoOriginal = $orden->limite_saldo_en ?? $orden->expira_en;
                $plazo = $registro->plazo($orden);
                $abierta = $registro->vigente($orden);
                $pendientesTransferencia = $orden->pago?->abonos?->filter(fn($a)=>$a->estado==='pendiente' && $a->metodo==='transferencia') ?? collect();
            @endphp
            <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-slate-900">
                <summary class="flex cursor-pointer list-none flex-wrap items-center justify-between gap-4 p-5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2"><strong>Orden #{{ $orden->id_orden }}</strong>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $abierta ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200' : 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-200' }}">{{ $abierta ? 'Regularización presencial' : ($orden->estado === 'pagada' ? 'Pago completado' : 'Finalizada') }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $orden->actividad?->nombre ?? 'Actividad archivada' }} · {{ $orden->correo_contacto }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-6 text-sm">
                        <div><p class="text-xs text-slate-500">Total</p><strong>${{ number_format($total/100,2) }}</strong></div>
                        <div><p class="text-xs text-slate-500">Acreditado</p><strong class="text-sidan-600 dark:text-sidan-300">${{ number_format($recibido/100,2) }}</strong></div>
                        <div><p class="text-xs text-slate-500">Pendiente</p><strong class="text-amber-700 dark:text-amber-300">${{ number_format($saldo/100,2) }}</strong></div>
                        <span class="text-slate-400 group-open:rotate-180">⌄</span>
                    </div>
                </summary>
                <div class="space-y-5 border-t border-slate-200 p-5 dark:border-white/10">
                    @if($plazo)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                            Vencimiento original: <strong>{{ $plazoOriginal?->copy()->timezone('America/El_Salvador')->format('d/m/Y H:i') }}</strong> · Último día para regularizar: <strong>{{ $plazo->copy()->timezone('America/El_Salvador')->format('d/m/Y H:i') }}</strong>.
                        </div>
                    @endif
                    @if(!$abierta && $orden->motivo_finalizacion)
                        <div class="rounded-xl border border-slate-200 p-3 text-sm dark:border-white/10"><strong>Motivo de finalización:</strong> {{ $orden->motivo_finalizacion }}</div>
                    @endif
                    <div class="grid gap-4 lg:grid-cols-2">
                        <section class="rounded-xl border border-slate-200 p-4 dark:border-white/10">
                            <h3 class="mb-3 font-black">Conceptos y entregas</h3>
                            @foreach($orden->detalles as $detalle)
                                @php
                                    $neto = $reparto->neto($detalle); $asignado = $orden->tipo_orden === 'inscripcion' ? $recibido : $reparto->aplicado($detalle);
                                    $entregable = $orden->tipo_orden === 'productos' && $asignado >= $neto && (int)$detalle->cantidad_entregada < (int)$detalle->cantidad;
                                @endphp
                                <div class="mb-3 rounded-xl bg-slate-50 p-3 text-sm dark:bg-white/5">
                                    <p class="font-semibold">{{ $detalle->nombre_concepto }} × {{ $detalle->cantidad }}</p>
                                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">Pagado ${{ number_format($asignado/100,2) }} / ${{ number_format($neto/100,2) }} · Entregado {{ $detalle->cantidad_entregada }}/{{ $detalle->cantidad }}</p>
                                    @if($entregable)
                                        <button type="button" onclick="document.getElementById('entrega-vencida-{{ $detalle->id_detalle_orden }}').showModal()" class="mt-3 rounded-lg bg-sidan-500 px-3 py-2 text-xs font-bold text-white">Marcar como entregado</button>
                                        <dialog id="entrega-vencida-{{ $detalle->id_detalle_orden }}" class="w-[min(92vw,440px)] rounded-2xl border border-slate-200 bg-white p-6 text-slate-900 shadow-xl backdrop:bg-black/60 dark:border-white/20 dark:bg-slate-900 dark:text-white">
                                            <h4 class="text-lg font-bold">¿Confirmar entrega?</h4><p class="my-3 text-sm">{{ $detalle->nombre_concepto }} — {{ $detalle->cantidad }} unidad(es). Solo se entrega el producto pagado.</p>
                                            <form method="POST" action="{{ route('admin.ordenes.entregar', [$orden, $detalle]) }}" class="space-y-3">@csrf
                                                <label class="block text-xs font-bold">Observación (opcional)<textarea name="observacion" maxlength="1000" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 p-2 text-sm dark:border-white/20 dark:bg-slate-800"></textarea></label>
                                                <div class="flex justify-end gap-2"><button type="button" onclick="this.closest('dialog').close()" class="rounded-lg border px-4 py-2 text-sm">Cancelar</button><button class="rounded-lg bg-sidan-500 px-4 py-2 text-sm font-bold text-white">Confirmar entrega</button></div>
                                            </form>
                                        </dialog>
                                    @elseif($orden->tipo_orden === 'inscripcion')
                                        <p class="mt-1 text-xs text-slate-500">Inscripción: no tiene entrega física.</p>
                                    @elseif((int)$detalle->cantidad_entregada >= (int)$detalle->cantidad)
                                        <p class="mt-1 text-xs font-bold text-sidan-600 dark:text-sidan-300">Entregado</p>
                                    @else
                                        <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">Pendiente de completar el pago de este artículo</p>
                                    @endif
                                </div>
                            @endforeach
                        </section>
                        <section class="rounded-xl border border-slate-200 p-4 dark:border-white/10">
                            <h3 class="font-black">Historial de dinero</h3>
                            <p class="mb-3 text-xs text-slate-500">Método inicial: {{ ucfirst($orden->pago?->proveedor ?? 'No registrado') }}. Los complementos posteriores al vencimiento son solo en efectivo.</p>
                            @forelse($orden->pago?->abonos ?? [] as $abono)
                                <p class="border-b border-slate-100 py-2 text-xs dark:border-white/10">#{{ $abono->id_abono }} · {{ ucfirst($abono->metodo) }} · {{ ucfirst($abono->estado) }} @if($abono->estado === 'confirmado') <strong class="text-sidan-600 dark:text-sidan-300">${{ number_format((float)$abono->monto_recibido,2) }}</strong>@endif</p>
                            @empty<p class="text-xs text-slate-500">Sin movimientos.</p>@endforelse
                            @if($abierta && $pendientesTransferencia->isNotEmpty())
                                <div class="mt-3 rounded-xl border border-blue-200 bg-blue-50 p-3 text-xs text-blue-900 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-200">
                                    <strong>Comprobante histórico pendiente</strong><p class="mt-1">Resolvelo antes de registrar un nuevo efectivo. No se aceptan transferencias nuevas en una orden vencida.</p>
                                    @foreach($pendientesTransferencia as $abono)
                                        <div class="mt-3 border-t border-blue-200 pt-3 dark:border-white/10">
                                            <p class="font-bold">Comprobante #{{ $abono->id_abono }}
                                            @if($abono->comprobante_path) · <a href="{{ route('admin.pagos.abonos.comprobante', $abono) }}" target="_blank" rel="noopener" class="underline">Ver archivo</a>@endif</p>
                                            <form class="mt-2 space-y-2" method="POST" action="{{ route('admin.ordenes.conciliar', [$orden,$abono]) }}">@csrf
                                                <input name="accion" type="hidden" value="aprobar">
                                                <label class="block">Importe verificado ($)<input name="monto_recibido" required min="0.01" max="{{ number_format($saldo/100,2,'.','') }}" step="0.01" type="number" class="mt-1 w-full rounded-lg border border-slate-300 p-2 dark:border-white/20 dark:bg-slate-900"></label>
                                                <button class="rounded-lg bg-sidan-500 px-3 py-2 text-white">Conciliar transferencia anterior</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.ordenes.conciliar', [$orden,$abono]) }}" class="mt-3 space-y-2">@csrf
                                                <input name="accion" type="hidden" value="rechazar">
                                                <label class="block">Motivo del rechazo<textarea name="motivo" required minlength="5" maxlength="1000" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 p-2 dark:border-white/20 dark:bg-slate-900"></textarea></label>
                                                <button class="rounded-lg border border-red-300 px-3 py-2 text-red-700 dark:text-red-300">Rechazar comprobante histórico</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            @if($abierta && $saldo > 0)
                                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                    <button type="button" onclick="document.getElementById('cobro-vencido-{{ $orden->id_orden }}').showModal()" class="rounded-lg bg-sidan-500 px-3 py-3 text-sm font-bold text-white">Registrar efectivo</button>
                                    <button type="button" onclick="document.getElementById('finalizar-vencido-{{ $orden->id_orden }}').showModal()" class="rounded-lg border border-amber-300 px-3 py-3 text-sm font-bold text-amber-700 dark:text-amber-300">Finalizar orden</button>
                                </div>
                                <dialog id="cobro-vencido-{{ $orden->id_orden }}" class="w-[min(92vw,460px)] rounded-2xl border border-slate-200 bg-white p-6 text-slate-900 shadow-xl backdrop:bg-black/60 dark:border-white/20 dark:bg-slate-900 dark:text-white">
                                    <h3 class="text-lg font-black">Registrar efectivo presencial</h3><p class="my-3 text-sm">Orden #{{ $orden->id_orden }} · Saldo máximo ${{ number_format($saldo/100,2) }}. El usuario debe estar presente.</p>
                                    <form method="POST" action="{{ route('admin.ordenes.abonar', $orden) }}" class="space-y-4">@csrf
                                        <label class="block text-sm font-bold">Dinero realmente recibido ($)<input type="number" required min="0.01" max="{{ number_format($saldo/100,2,'.','') }}" step="0.01" name="monto_recibido" class="mt-1 w-full rounded-lg border border-slate-300 p-3 dark:border-white/20 dark:bg-slate-800"></label>
                                        <label class="block text-sm font-bold">Observación (opcional)<textarea name="observacion" rows="2" maxlength="1000" class="mt-1 w-full rounded-lg border border-slate-300 p-3 dark:border-white/20 dark:bg-slate-800"></textarea></label>
                                        <div class="flex justify-end gap-2"><button type="button" onclick="this.closest('dialog').close()" class="rounded-lg border px-4 py-2">Cancelar</button><button class="rounded-lg bg-sidan-500 px-4 py-2 font-bold text-white">Confirmar efectivo</button></div>
                                    </form>
                                </dialog>
                                <dialog id="finalizar-vencido-{{ $orden->id_orden }}" class="w-[min(92vw,460px)] rounded-2xl border border-slate-200 bg-white p-6 text-slate-900 shadow-xl backdrop:bg-black/60 dark:border-white/20 dark:bg-slate-900 dark:text-white">
                                    <h3 class="text-lg font-black">Finalizar sin completar saldo</h3>
                                    <p class="my-3 text-sm">Se bloquearán nuevos pagos. Los artículos pagados podrán entregarse; los abonos confirmados no se borran.</p>
                                    <form method="POST" action="{{ route('admin.ordenes.cerrar', $orden) }}" class="space-y-4">@csrf
                                        <label class="block text-sm font-bold">Motivo de finalización<textarea name="motivo" required minlength="10" maxlength="500" rows="3" placeholder="El comprador informó que no continuará pagando" class="mt-1 w-full rounded-lg border border-slate-300 p-3 dark:border-white/20 dark:bg-slate-800"></textarea></label>
                                        <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="confirmacion" value="1" required class="mt-1 accent-sidan-500">Confirmo que revisé los pagos y productos antes de finalizar.</label>
                                        <div class="flex justify-end gap-2"><button type="button" onclick="this.closest('dialog').close()" class="rounded-lg border px-4 py-2">Cancelar</button><button class="rounded-lg bg-amber-500 px-4 py-2 font-bold text-white">Finalizar orden</button></div>
                                    </form>
                                </dialog>
                            @endif
                        </section>
                    </div>
                </div>
            </details>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-slate-900">{{ $vista === 'abiertas' ? 'No hay órdenes con abonos en período de regularización.' : 'No hay órdenes finalizadas en este listado.' }}</div>
        @endforelse
    </div>
    {{ $ordenes->links() }}
    <p class="text-xs text-slate-500 dark:text-slate-400">La finalización de una orden no registra ingresos nuevos ni equivale a entregar artículos sin pagar. Conservá los comprobantes y cumplí las obligaciones legales aplicables.</p>
</div>
@endsection
