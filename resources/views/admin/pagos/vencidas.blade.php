@extends('layouts.navbars')
@section('title', 'Órdenes vencidas')
@section('content')
<div class="mx-auto max-w-5xl p-6 space-y-5 text-slate-800 dark:text-white">
    <h1 class="text-2xl font-bold">Órdenes vencidas con dinero recibido</h1>
    <p class="text-sm text-slate-500">Los abonos acreditados permanecen en el historial. Este cierre NO crea un nuevo cobro, NO confirma la venta incompleta y NO crea reembolsos.</p>
    <a class="text-blue-500 underline" href="{{ route('admin.pagos.index') }}">Volver al control de pagos</a>
    @if(session('success')) <div class="rounded-lg p-3 bg-emerald-100 text-emerald-900">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="rounded-lg p-3 bg-red-100 text-red-900">{{ implode(' · ', $errors->all()) }}</div> @endif
    @forelse($ordenes as $orden)
    @php $abonos=$orden->pago?->montoAcreditadoCentavos()??0; @endphp
    <article class="rounded-xl border border-slate-200 dark:border-white/20 p-5 space-y-3">
        <h2 class="font-bold">Orden #{{ $orden->id_orden }} · {{ $orden->actividad?->nombre }}</h2>
        <p class="text-sm">{{ $orden->correo_contacto }} · Total: ${{ number_format((float)$orden->total,2) }} · Recibido: ${{ number_format($abonos/100,2) }} · No recibido: ${{ number_format(max(0,(float)$orden->total-$abonos/100),2) }}</p>
        <div class="text-sm space-y-1">
          @foreach($orden->detalles as $detalle)
            <p>{{ $detalle->nombre_concepto }} ({{ $detalle->cantidad }}) · entregadas: {{ $detalle->cantidad_entregada }} · neto: ${{ number_format((float)$detalle->subtotal-(float)$detalle->descuento,2) }}</p>
          @endforeach
        </div>
        <form method="POST" action="{{ route('admin.ordenes.cerrar',$orden) }}" class="space-y-3" onsubmit="return confirm('¿Autorizás el cierre financiero conservando los abonos originales?');">
            @csrf
            <label class="block text-sm font-semibold">Clasificación del anticipo no entregado</label>
            <select name="clasificacion" class="w-full rounded border p-2 bg-white text-slate-900" required>
                <option value="pendiente_clasificacion">Anticipo retenido pendiente de clasificación contable</option>
                <option value="ingreso_no_ejercido">Ingreso no ejercido (solo con derecho legal/contractual verificado)</option>
            </select>
            <textarea name="justificacion" rows="3" minlength="15" maxlength="2000" required placeholder="Fundamento del cierre, fechas, condiciones aceptadas y conciliación de pagos" class="w-full rounded border p-3 bg-white text-slate-900"></textarea>
            <label class="flex gap-2 text-sm"><input type="checkbox" name="declaracion" value="1" required> Confirmo que revisé los importes, los productos entregados y la procedencia de la clasificación.</label>
            <button class="rounded-lg bg-amber-600 text-white px-4 py-2 font-semibold">Autorizar cierre definitivo</button>
        </form>
    </article>
    @empty
       <p class="rounded-xl border p-8 text-center">No hay órdenes con dinero recibido pendientes de autorización.</p>
    @endforelse
    {{ $ordenes->links() }}
</div>
@endsection
