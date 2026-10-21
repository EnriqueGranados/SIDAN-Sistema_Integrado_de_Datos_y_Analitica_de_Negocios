@php
    $medioItem =
        $item->medios->firstWhere('es_portada', true) ?: $item->medios->first();

    $imagenItem = $urlMedio($medioItem?->url) ?: $actividad['imagen'];

    $variantes = $item->variantes
        ->where('activo', true)
        ->sortBy('orden')
        ->values();

    $variantesCompra = $variantes
        ->map(function ($variante) use ($stockVariantes) {
            return [
                'id' => (int) $variante->id_variante_item,
                'nombre' => (string) $variante->nombre_variante,
                'precio' =>
                    $variante->precio !== null
                        ? (float) $variante->precio
                        : null,
                'stock' =>
                    $stockVariantes[(int) $variante->id_variante_item] ?? null,
            ];
        })
        ->values();

    $configuracionCampos = ($camposPorItem ?? collect())->get(
        $item->id_item_actividad,
    );

    $configuracionPrecio = ($preciosPorItem ?? collect())->get(
        $item->id_item_actividad,
    );

    $campos = collect($configuracionCampos['fields'] ?? [])
        ->filter(fn($campo) => is_array($campo) && !empty($campo['key']))
        ->values();

    $reglasPrecio = collect($configuracionPrecio['rules'] ?? [])
        ->filter(fn($regla) => is_array($regla))
        ->values();

    $ahora = now();

    $disponible =
        (bool) $item->activo &&
        (!$item->venta_desde || $ahora->gte($item->venta_desde)) &&
        (!$item->venta_hasta || $ahora->lte($item->venta_hasta));

    $minimo = max(1, (int) ($item->min_por_inscripcion ?? 1));

    $maximo =
        $item->max_por_inscripcion !== null
            ? (int) $item->max_por_inscripcion
            : null;

    $stockItem = $stockItems[(int) $item->id_item_actividad] ?? null;

    $limite = $maximo;

    if ($stockItem !== null) {
        $limite = $limite === null ? $stockItem : min($limite, $stockItem);
    }

    // Disponibilidad real del producto.
    $tieneDisponibilidad =
        $disponible && ($limite === null || $limite >= $minimo);

    // Permiso para comprar según inscripción.
    $puedeConfigurar = $tieneDisponibilidad && !$compraBloqueadaPorInscripcion;

    $sesionesItem = $item->sesiones->sortBy('fecha_inicio')->values();
@endphp

<article
    class="sidan-producto flex flex-col rounded-3xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-sidan-900 {{ ($ocultarProducto ?? false) ? 'hidden' : '' }}"
    data-producto-config data-item-id="{{ $item->id_item_actividad }}"
    data-precio-base="{{ (float) $item->precio }}"
    data-minimo="{{ $minimo }}" data-maximo="{{ $limite ?? '' }}"
    data-stock="{{ $stockItem ?? '' }}"
    data-reglas='@json($reglasPrecio->all())'
    data-variantes='@json($variantesCompra->all())'>

    <div
        class="flex h-48 items-center justify-center overflow-hidden rounded-2xl bg-zinc-50 p-3 dark:bg-sidan-900">
        <img src="{{ $imagenItem }}" alt="{{ $item->nombre }}" loading="lazy"
            class="h-full w-full object-contain transition-transform duration-300 hover:scale-105">
    </div>

    <div class="mt-4 flex flex-1 flex-col">

        <span class="text-[11px] font-bold uppercase tracking-wide text-zinc-500">
            {{ ucfirst($item->tipo) }}
        </span>

        <h3 class="mt-1 text-lg font-black text-sidan-900 dark:text-white">
            {{ $item->nombre }}
        </h3>

        @if ($item->descripcion)
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                {{ $item->descripcion }}
            </p>
        @endif

        @if ($sesiones->count() > 1)
            @if (($item->alcance_sesiones ?? 'actividad') === 'actividad')
                <div
                    class="mt-3 rounded-xl bg-zinc-100 px-3 py-2.5 text-xs font-semibold text-zinc-700 dark:bg-white/10 dark:text-zinc-300">
                    Disponible para toda la actividad
                </div>
            @elseif ($sesionesItem->isNotEmpty())
                <div
                    class="mt-3 rounded-xl bg-zinc-100 px-3 py-2.5 dark:bg-white/10">
                    <p
                        class="text-[10px] font-black uppercase tracking-wide text-zinc-700 dark:text-zinc-300">
                        Aplica a
                        {{ $sesionesItem->count() === 1 ? 'esta sesión' : 'estas sesiones' }}
                    </p>

                    <p
                        class="mt-1 text-xs font-semibold leading-5 text-slate-700 dark:text-slate-200">
                        {{ $sesionesItem->pluck('nombre')->implode(' · ') }}
                    </p>
                </div>
            @else
                <div
                    class="mt-3 rounded-xl bg-amber-50 px-3 py-2.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                    Sesión pendiente de asignación
                </div>
            @endif
        @elseif ($sesionesItem->isNotEmpty())
            <div
                class="mt-3 rounded-xl bg-zinc-100 px-3 py-2.5 text-xs font-semibold text-zinc-700 dark:bg-white/10 dark:text-zinc-300">
                Sesión: {{ $sesionesItem->first()->nombre }}
            </div>
        @endif

        @if ($campos->isNotEmpty())
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">

                @foreach ($campos as $campo)
                    @php
                        $tipo = $campo['type'] ?? 'texto';
                        $clave = $campo['key'] ?? '';
                        $etiqueta = $campo['label'] ?? $clave;
                        $obligatorio = !empty($campo['required']);
                    @endphp

                    <div>
                        <label class="mb-2 block text-sm font-semibold">
                            {{ $etiqueta }}

                            @if ($obligatorio)
                                <span class="text-red-500">*</span>
                            @endif
                        </label>

                        @if ($tipo === 'lista')
                            <select data-campo-clave="{{ $clave }}"
                                @required($obligatorio) @required($obligatorio)
                                @disabled($compraBloqueadaPorInscripcion)
                                class="w-full rounded-xl border border-slate-200 px-3 py-2">
                                <option value="">Seleccionar...</option>

                                @foreach ($campo['options'] ?? [] as $opcion)
                                    <option value="{{ $opcion }}">
                                        {{ $opcion }}
                                    </option>
                                @endforeach
                            </select>
                        @elseif ($tipo === 'numero')
                            <input type="number" step="any"
                                data-campo-clave="{{ $clave }}"
                                @required($obligatorio) @disabled($compraBloqueadaPorInscripcion)
                                class="w-full rounded-xl border border-slate-200 px-3 py-2"
                                placeholder="Ingresar número">
                        @else
                            <input type="text" maxlength="200"
                                data-campo-clave="{{ $clave }}"
                                @required($obligatorio) @disabled($compraBloqueadaPorInscripcion)
                                class="w-full rounded-xl border border-slate-200 px-3 py-2"
                                placeholder="Escribir {{ mb_strtolower($etiqueta) }}">
                        @endif
                    </div>
                @endforeach

            </div>
        @endif

        <div class="relative mt-auto pt-5">

            <div
                class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 dark:border-white/10">

                <div>
                    <p
                        class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                        Precio unitario
                    </p>

                    <p class="text-2xl font-black text-sidan-950 dark:text-white"
                        data-precio-unitario>
                        ${{ number_format((float) $item->precio, 2) }}
                    </p>
                </div>

                <div class="flex items-center rounded-full border border-slate-200 dark:border-white/20"
                    data-cantidad-control>
                    <button type="button" data-restar disabled
                        class="px-3 py-2 font-black text-sidan-900 dark:text-sidan-400 disabled:text-slate-300 dark:disabled:text-slate-600"
                        aria-label="Restar cantidad">
                        −
                    </button>

                    <output data-cantidad
                        class="min-w-7 text-center text-sm font-black dark:text-white">
                        0
                    </output>

                    <button type="button" data-sumar
                        class="px-3 py-2 font-black text-sidan-900 dark:text-sidan-400 disabled:text-slate-300 dark:disabled:text-slate-600"
                        @disabled(!$puedeConfigurar) aria-label="Sumar cantidad">
                        +
                    </button>
                </div>

            </div>

            {{-- Disponibilidad real: mensaje permanente --}}
            <p
                class="mt-2 text-xs font-semibold
                                {{ $stockItem === 0 ? 'text-red-600 dark:text-red-400' : 'text-sidan-600 dark:text-sidan-400' }}">

                @if ($stockItem === 0)
                    Producto agotado
                @elseif (!$disponible)
                    Producto no disponible actualmente
                @elseif (!$tieneDisponibilidad)
                    No hay unidades suficientes para la cantidad mínima
                @elseif ($stockItem !== null)
                    Disponibles: {{ $stockItem }}
                @else
                    Stock sin límite
                @endif
            </p>

            {{-- Mensaje dinámico controlado por JavaScript --}}
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                data-disponibilidad>

                @if ($compraBloqueadaPorInscripcion)
                    Debes tener una inscripción confirmada para comprar.
                @elseif ($puedeConfigurar)
                    Configura el producto para continuar.
                @else
                    No disponible actualmente.
                @endif
            </p>

            <p class="mt-2 hidden text-xs font-semibold text-red-600" data-error
                role="alert"></p>

            <button type="button" data-agregar-configuracion
                class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-sidan-500 px-4 py-3.5 text-sm font-black text-white transition hover:bg-sidan-600 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-sidan-500 dark:text-white dark:hover:bg-sidan-600"
                @disabled(!$puedeConfigurar)>
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 4h2l2 11h12l2-8H6M10 20h.01M17 20h.01" />
                </svg>
                Agregar al carrito
            </button>
            {{-- Aviso flotante temporal: no altera el alto de la tarjeta. --}}
            <p data-exito-carrito role="status" aria-live="polite"
                class="pointer-events-none absolute bottom-16 left-0 right-0 z-10 hidden rounded-xl border border-sidan-200 bg-sidan-50 px-3 py-2.5 text-center text-xs font-black text-sidan-600 shadow-lg dark:border-sidan-500/30 dark:bg-sidan-950 dark:text-sidan-200">
                ✓ Agregado al carrito
            </p>

            {{-- Este bloque se traslada al sidebar al inicializar la vista.
                 Mantenemos los mismos data-* para no alterar las validaciones,
                 la eliminación de líneas ni los cálculos actuales. --}}
            <div class="hidden rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-sidan-950/60"
                data-resumen-configuraciones>
                <p class="mb-3 text-sm font-black text-sidan-900 dark:text-white">
                    {{ $item->nombre }}
                </p>

                <div class="space-y-2" data-lineas-configuracion></div>

                <p
                    class="mt-3 border-t border-slate-200 pt-2 text-sm font-black text-sidan-900 dark:border-white/10 dark:text-white">
                    Subtotal:

                    <span data-subtotal>
                        $0.00
                    </span>
                </p>
            </div>

        </div>
    </div>

</article>
