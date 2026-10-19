@extends(auth()->user()?->rol?->nombre === 'usuario' ? 'layouts.user' : 'layouts.navbars')

@section('title', 'Detalles de actividad - SIDAN')

@section(auth()->user()?->rol?->nombre === 'usuario' ? 'user-content' : 'content')
<div class="mx-auto max-w-6xl space-y-6 px-3 py-5 sm:px-6">
    <a href="{{ route('user.inscripciones.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-sidan-600 hover:underline dark:text-sidan-400">← Volver a mis inscripciones</a>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
        @if($actividad->portada?->url)
            <img src="{{ $actividad->portada->url }}" alt="{{ $actividad->portada->texto_alternativo ?: $actividad->nombre }}" class="h-52 w-full object-cover sm:h-72" />
        @endif
        <div class="space-y-4 p-5 sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-sidan-600 dark:text-sidan-400">{{ $actividad->categoria?->nombre ?? 'Actividad' }}</p>
                    <h1 class="mt-1 text-3xl font-bold">{{ $actividad->nombre }}</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Inscripción #{{ $inscripcion->id_inscripcion }} · Estado: <strong class="capitalize">{{ $estadoVista }}</strong></p>
                </div>
                @if($inscripcion->estado === 'pendiente_pago' && $inscripcion->orden && ! $inscripcion->expira_en?->isPast())
                    <a href="{{ route('sidan.ordenes.show', $inscripcion->orden->id_orden) }}" class="rounded-xl bg-sidan-500 px-5 py-3 text-sm font-semibold text-white">Completar pago de inscripción</a>
                @endif
            </div>
            @if($actividad->resumen)<p class="text-base text-slate-600 dark:text-slate-300">{{ $actividad->resumen }}</p>@endif
            <div class="grid gap-3 text-sm sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800"><p class="text-slate-500">Inicio</p><strong>{{ $actividad->realizacion_desde?->format('d/m/Y · h:i A') ?? 'Por definir' }}</strong></div>
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800"><p class="text-slate-500">Finalización</p><strong>{{ $actividad->realizacion_hasta?->format('d/m/Y · h:i A') ?? 'Por definir' }}</strong></div>
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800"><p class="text-slate-500">Ubicación</p><strong>{{ $actividad->ubicacion_externa ?: ($actividad->espacio?->nombre ?? 'Por definir') }}</strong></div>
            </div>
        </div>
    </div>

    @if($actividad->descripcion)
        <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900 sm:p-7">
            <h2 class="mb-3 text-xl font-bold">Acerca de la actividad</h2>
            <div class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $actividad->descripcion }}</div>
        </section>
    @endif

    @if($actividad->sesiones->isNotEmpty())
        <section class="space-y-3">
            <h2 class="text-xl font-bold">Programa y sesiones</h2>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach($actividad->sesiones as $sesion)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
                        <h3 class="font-semibold">{{ $sesion->nombre }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $sesion->fecha_inicio?->format('d/m/Y h:i A') ?? 'Fecha pendiente' }} @if($sesion->fecha_fin) — {{ $sesion->fecha_fin->format('h:i A') }} @endif</p>
                        @if($sesion->ubicacion)<p class="mt-2 text-sm">{{ $sesion->ubicacion }}</p>@endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="space-y-4">
        <div>
            <h2 class="text-2xl font-bold">Productos y servicios disponibles</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Consulta los artículos que ofrece esta actividad. La compra de artículos adicionales se habilitará en el siguiente paso.</p>
        </div>
        @if(! $puedeComprar && $actividad->items->isNotEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/30">Para adquirir productos o servicios, la inscripción debe estar confirmada y la actividad no debe haber finalizado.</div>
        @endif
        @forelse($actividad->items as $item)
            @php
                $iniciaVenta = $item->venta_desde && $item->venta_desde->isFuture();
                $terminaVenta = $item->venta_hasta && $item->venta_hasta->isPast();
                $sinStock = $item->stock_total !== null && $item->stock_total <= 0;
            @endphp
            <article class="flex flex-col gap-5 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900 md:flex-row">
                

                <div class="min-w-0 flex-1 space-y-3">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wide text-sidan-600 dark:text-sidan-400">{{ ucfirst($item->tipo) }}</span>
                            <h3 class="text-xl font-bold">{{ $item->nombre }}</h3>
                        </div>
                        <span class="text-xl font-bold">${{ number_format((float) $item->precio, 2) }}</span>
                    </div>
                    @if($item->descripcion)<p class="whitespace-pre-line text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $item->descripcion }}</p>@endif
                    <div class="text-xs text-slate-500">
                        @if($sinStock)<span class="font-semibold text-red-600">Agotado</span>
                        @elseif($iniciaVenta)<span>Disponible desde {{ $item->venta_desde->format('d/m/Y') }}</span>
                        @elseif($terminaVenta)<span>Período de venta finalizado</span>
                        @elseif($item->stock_total !== null)<span>Existencia configurada: {{ $item->stock_total }}</span>
                        @else<span>Disponibilidad sujeta a confirmación</span>@endif
                    </div>
                    @if($item->variantes->isNotEmpty())
                        <div>
                            <h4 class="text-sm font-semibold">Opciones disponibles</h4>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach($item->variantes as $variante)
                                    <span class="rounded-lg border border-slate-200 px-3 py-2 text-xs dark:border-slate-600">
                                        {{ $variante->nombre_variante }}
                                        @if($variante->precio !== null) · ${{ number_format((float) $variante->precio, 2) }} @endif
                                        @if($variante->stock_total !== null && $variante->stock_total <= 0) · Agotada @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <button type="button" disabled title="La compra de adicionales se implementará en el siguiente paso" class="cursor-not-allowed rounded-xl bg-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-300">Comprar próximamente</button>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center text-slate-500">Esta actividad no tiene productos ni servicios publicados en su catálogo.</div>
        @endforelse
    </section>
</div>
@endsection
