@extends('layouts.navbars')
@section('title','Venta presencial #'.$orden->id_orden)
@section('content')
@php
    $abonado = $orden->pago?->montoAcreditadoCentavos() ?? 0;
    $total = (int)round((float)$orden->total*100);
    // En pago completo, minimo_inicial = total es una regla interna del cobro,
    // no un anticipo que debamos mostrar al comprador o al cajero.
    $esAnticipo = $orden->modalidad_pago === 'anticipo';
    $minimo = $esAnticipo ? (int) round((float) $orden->minimo_inicial * 100) : 0;
    $saldo = max(0,$total-$abonado);
@endphp
<div class="min-h-screen bg-[#f6f8fb] px-4 py-8 text-slate-900 dark:bg-transparent dark:text-white sm:px-8">
    <div class="mx-auto max-w-4xl space-y-6">
        <header class="flex flex-wrap items-center justify-between gap-3"><div><h1 class="text-3xl font-black">Orden #{{ $orden->id_orden }}</h1><p class="mt-1 text-sm text-slate-500 dark:text-slate-300">{{ $orden->actividad?->nombre }} · {{ $orden->correo_contacto }}</p></div>
            <a href="{{ route('admin.ventas-presenciales.index') }}" class="rounded-xl bg-sidan-500 px-5 py-3 font-semibold text-sm text-white hover:bg-sidan-600">Nueva venta</a></header>
        @if(session('success'))<div class="rounded-xl border border-sidan-200 bg-sidan-50 p-4 text-sm text-sidan-800 dark:border-sidan-500/30 dark:bg-sidan-500/10 dark:text-sidan-300">{{ session('success') }}</div>@endif
        <div class="grid gap-3 sm:grid-cols-4">
            @foreach([['Total',$total],['Anticipo mínimo',$minimo],['Acreditado',$abonado],['Saldo pendiente',$saldo]] as [$etiqueta,$valor])
                <section class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-[#0f172a]"><p class="text-xs text-slate-500 dark:text-slate-300">{{ $etiqueta }}</p><p class="mt-2 text-2xl font-black">${{ number_format($valor/100,2) }}</p></section>
            @endforeach
        </div>
        @if($esAnticipo && $abonado < $minimo)
            <p class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">Faltan ${{ number_format(($minimo - $abonado) / 100, 2) }} para cubrir el anticipo mínimo y ${{ number_format($saldo / 100, 2) }} para completar la orden.</p>
        @elseif($esAnticipo && $saldo > 0)
            <p class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">Anticipo cubierto. Quedan ${{ number_format($saldo / 100, 2) }} para completar la compra.</p>
        @elseif($saldo > 0)
            <p class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">Modalidad de pago completo. Se acreditaron ${{ number_format($abonado / 100, 2) }} de ${{ number_format($total / 100, 2) }}. Faltan ${{ number_format($saldo / 100, 2) }} para completar la orden.</p>
        @else
            <p class="rounded-xl border border-sidan-200 bg-sidan-50 p-4 text-sm text-sidan-800 dark:border-sidan-500/30 dark:bg-sidan-500/10 dark:text-sidan-300">Orden completamente pagada. La entrega se administra por producto.</p>
        @endif
        <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-[#0f172a]">
            <h2 class="mb-4 text-lg font-black">Productos</h2>
            <div class="space-y-3">@foreach($orden->detalles as $detalle)
                <div class="flex flex-wrap justify-between gap-2 border-b border-slate-100 py-2 text-sm dark:border-white/10"><span>{{ $detalle->nombre_concepto }} × {{ $detalle->cantidad }}</span><span class="font-bold">${{ number_format((float)$detalle->subtotal-(float)$detalle->descuento,2) }}</span></div>
            @endforeach</div>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-[#0f172a]">
            <h2 class="mb-3 text-lg font-black">Historial de abonos</h2>
            <p class="mb-3 text-xs text-slate-500 dark:text-slate-300">Estado de la orden: {{ $orden->estado }} · Método: {{ $orden->pago?->proveedor }}</p>
            @forelse(($orden->pago?->abonos ?? collect()) as $abono)
                <div class="flex flex-wrap justify-between gap-2 border-b border-slate-100 py-3 text-sm dark:border-white/10"><span>Abono #{{ $abono->id_abono }} · {{ $abono->metodo }} · <strong>{{ $abono->estado }}</strong></span><span>{{ $abono->estado==='confirmado' ? 'Acreditado: $'.number_format((float)$abono->monto_recibido,2) : 'Reportado: $'.number_format((float)$abono->monto_reportado,2) }}</span></div>
            @empty <p class="text-sm">Sin abonos registrados.</p> @endforelse
            <div class="mt-5 flex flex-wrap gap-3"><a href="{{ route('admin.pagos.index') }}" class="rounded-xl border border-sidan-500 px-4 py-2.5 text-sm font-black text-sidan-600 transition hover:bg-sidan-500 hover:text-white dark:text-sidan-300">Revisar pagos y complementos</a><a href="{{ route('admin.control.ordenes') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200">Todas las órdenes</a></div>
        </section>
    </div>
</div>
@endsection
