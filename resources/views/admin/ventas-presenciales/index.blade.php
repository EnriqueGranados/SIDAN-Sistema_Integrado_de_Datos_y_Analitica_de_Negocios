@extends('layouts.navbars')
@section('title','Ventas presenciales | SIDAN')
@section('content')
<div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
    <div id="gestion-pagos" class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                    Administración
                </p>

                <h1 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 dark:text-white">
                Nueva venta presencial</h1>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Una orden por actividad. No es necesario que el comprador se inscriba, pero la compra quedará asociada a su cuenta de SIDAN.</p>
            </div>
        </div>
        @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
            <h2 class="font-bold">No se pudo registrar la venta</h2>
            <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
        @endif
        <section x-data="{ actividadesAbiertas: false }" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0f172a] sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black text-sidan-900 dark:text-white">1. Actividad de origen</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Mostramos solamente actividades que no han finalizado. La ventana de venta de cada producto se verifica por separado.</p>
                </div>
                <button type="button"
                    @click="actividadesAbiertas = !actividadesAbiertas"
                    :aria-expanded="actividadesAbiertas.toString()"
                    aria-controls="listado-actividades-caja"
                    class="inline-flex items-center gap-2 rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-sidan-600 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sidan-500/20">
                    <span x-text="actividadesAbiertas ? 'Ocultar actividades' : @js($actividad ? 'Cambiar actividad' : 'Seleccionar actividad')">{{ $actividad ? 'Cambiar actividad' : 'Seleccionar actividad' }}</span>
                    <svg class="h-4 w-4 transition-transform" :class="actividadesAbiertas ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                    </svg>
                </button>
            </div>
            @if($actividad)
                <div class="mt-4 rounded-xl border border-sidan-200 bg-sidan-50 px-4 py-3 text-sm text-sidan-900 dark:border-sidan-500/30 dark:bg-sidan-500/10 dark:text-sidan-100">
                    <span class="font-medium">Actividad seleccionada:</span>
                    <strong>{{ $actividad->nombre }}</strong>
                </div>
            @endif
            <form id="listado-actividades-caja" x-show="actividadesAbiertas" x-cloak x-transition
                method="GET" action="{{ route('admin.ventas-presenciales.index') }}" class="mt-5 space-y-4">
                <div class="max-h-[32rem] space-y-3 overflow-y-auto pr-1">
                    @forelse($actividades as $act)
                        @php
                            // Se resuelve la portada igual que en Administrar Welcome.
                            $rutaPortada = str_replace('\\', '/', trim((string) ($act->portada?->url ?? '')));
                            $imagenActividad = null;
                            if ($rutaPortada !== '') {
                                if (\Illuminate\Support\Str::startsWith($rutaPortada, ['https://', 'http://', 'data:'])) {
                                    $imagenActividad = $rutaPortada;
                                } else {
                                    $rutaPortada = ltrim($rutaPortada, '/');
                                    if (\Illuminate\Support\Str::startsWith($rutaPortada, 'public/')) {
                                        $rutaPortada = substr($rutaPortada, 7);
                                    }
                                    $imagenActividad = asset(
                                        \Illuminate\Support\Str::startsWith($rutaPortada, 'storage/')
                                            ? $rutaPortada
                                            : 'storage/' . $rutaPortada
                                    );
                                }
                            }
                            $ahora = now();
                            if ($act->visible_desde && $act->visible_desde->gt($ahora)) {
                                $textoEstado = 'Programada';
                                $detalleEstado = 'Visible desde ' . $act->visible_desde->format('d/m/Y H:i');
                                $clasesEstado = 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300';
                            } elseif ($act->visible_hasta && $act->visible_hasta->lt($ahora)) {
                                $textoEstado = 'Fuera de ventana';
                                $detalleEstado = 'Dejó de ser visible el ' . $act->visible_hasta->format('d/m/Y H:i');
                                $clasesEstado = 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300';
                            } else {
                                $textoEstado = 'Visible ahora';
                                $detalleEstado = 'Se muestra actualmente en el portal público.';
                                $clasesEstado = 'bg-sidan-100 text-sidan-600 dark:bg-sidan-500/10 dark:text-sidan-300';
                            }
                        @endphp
                        <label class="block cursor-pointer">
                            <input type="radio" name="actividad" value="{{ $act->id_actividad }}" required
                                @checked($actividad && (int) $actividad->id_actividad === (int) $act->id_actividad)
                                class="peer sr-only">
                            <span class="flex min-w-0 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-sidan-400 hover:bg-sidan-50/40 peer-checked:border-sidan-500 peer-checked:bg-sidan-50 peer-focus-visible:ring-4 peer-focus-visible:ring-sidan-500/20 dark:border-white/10 dark:bg-white/[0.025] dark:hover:border-sidan-500/50 dark:hover:bg-sidan-500/5 dark:peer-checked:border-sidan-500 dark:peer-checked:bg-sidan-500/10 sm:gap-4">
                                @if($imagenActividad)
                                    <img src="{{ $imagenActividad }}" alt="Portada de {{ $act->nombre }}" loading="lazy"
                                        class="h-20 w-20 shrink-0 rounded-xl object-cover sm:w-24">
                                @else
                                    <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-sidan-900 to-sidan-600 text-sidan-300 sm:w-24" aria-hidden="true">
                                        <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18"/></svg>
                                    </span>
                                @endif
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="text-[11px] font-black uppercase tracking-wide text-sidan-500">{{ $act->categoria?->nombre ?? 'Sin categoría' }}</span>
                                        <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $clasesEstado }}">{{ $textoEstado }}</span>
                                    </span>
                                    <span class="mt-1 block break-words text-sm font-black text-sidan-900 dark:text-white">{{ $act->nombre }}</span>
                                    <span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">{{ $detalleEstado }}</span>
                                </span>
                            </span>
                        </label>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-5 py-8 text-center text-sm text-slate-500 dark:border-white/10 dark:text-slate-400">
                            No hay actividades publicadas con productos activos disponibles para esta sección.
                        </div>
                    @endforelse
                </div>
                <div class="flex justify-end border-t border-slate-100 pt-4 dark:border-white/10">
                    <button type="submit" @disabled($actividades->isEmpty())
                        class="w-full rounded-xl bg-sidan-500 px-6 py-3 font-bold text-sm text-white transition hover:bg-sidan-600 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-sidan-500/20 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                        Cargar productos
                    </button>
                </div>
            </form>
        </section>
        @if($actividad)
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0f172a] mt-5 mb-5">
                <form method="GET" action="{{ route('admin.ventas-presenciales.index') }}" class="flex flex-wrap items-end gap-3">
                    <input type="hidden" name="actividad" value="{{ $actividad->id_actividad }}">
                    <label class="flex-1 min-w-[200px] space-y-2"><span class="text-base font-black text-sidan-900 dark:text-white">2. Buscar usuario existente</span>
                        <input name="buscar_usuario" value="{{ $busqueda }}" placeholder="Nombre o correo (mínimo dos letras)" class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 px-3 py-3 text-sm text-sidan-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500"></label>
                    <button class="rounded-xl border border-sidan-500 px-4 py-2.5 text-sm font-black text-sidan-600 transition hover:bg-sidan-500 hover:text-white dark:text-sidan-300 dark:hover:text-white">Buscar cuenta</button>
                </form>
            </section>
            <form id="formulario-venta-presencial" method="POST" action="{{ route('admin.ventas-presenciales.store') }}" enctype="multipart/form-data"
                x-data="cajaPresencial()" @submit.prevent="validarEnvio($event)" class="space-y-6">
                @csrf
                <input type="hidden" name="id_actividad" value="{{ $actividad->id_actividad }}">
                {{-- Este campo es el que recibe Laravel; la tarjeta seleccionada lo actualiza mediante Alpine. --}}
                <input type="hidden" name="id_usuario" :value="tipo === 'existente' ? usuarioSeleccionado : ''">
                <input type="hidden" name="carrito" :value="JSON.stringify(carrito.map(l => ({id_item: l.id_item, id_variante: l.id_variante, cantidad: l.cantidad, campos: l.campos})))">
                <section x-ref="seccionComprador" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                    <h2 class="text-base font-black text-sidan-900 dark:text-white">Usuarios</h2>
                    <div class="my-4 flex flex-wrap gap-5 text-sm">
                        <label class="inline-flex cursor-pointer items-center gap-2"><input type="radio" name="comprador_tipo" value="existente" x-model="tipo" @change="errorComprador = ''" class="h-4 w-4 border-slate-300 bg-white text-sidan-500 accent-sidan-500 focus:ring-2 focus:ring-sidan-500/30 dark:border-slate-500 dark:bg-slate-900 dark:text-sidan-500"> Cuenta existente</label>
                        <label class="inline-flex cursor-pointer items-center gap-2"><input type="radio" name="comprador_tipo" value="nuevo" x-model="tipo" @change="errorComprador = ''" class="h-4 w-4 border-slate-300 bg-white text-sidan-500 accent-sidan-500 focus:ring-2 focus:ring-sidan-500/30 dark:border-slate-500 dark:bg-slate-900 dark:text-sidan-500"> Crear cuenta</label>
                    </div>
                    <p x-show="errorComprador" x-cloak x-text="errorComprador" role="alert"
                        class="mb-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200"></p>
                    <div x-show="tipo==='existente'" class="space-y-3">
                        @if($usuarios->isEmpty())
                            <p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900 dark:bg-amber-500/10 dark:text-amber-200">Busca por nombre o correo en la parte de arriba y selecciona un usuario, o puedes elegir «Crear cuenta».</p>
                        @else
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach($usuarios as $usuario)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 transition hover:border-sidan-400 hover:bg-sidan-50 dark:border-white/10 dark:hover:border-sidan-500/50 dark:hover:bg-white/5">
                                        <input type="radio" name="comprador_visual" value="{{ $usuario->id_usuario }}"
                                            data-caja-comprador x-model="usuarioSeleccionado"
                                            @change="errorComprador = ''"
                                            class="h-4 w-4 shrink-0 border-slate-300 bg-white text-sidan-500 accent-sidan-500 focus:ring-2 focus:ring-sidan-500/30 dark:border-slate-500 dark:bg-slate-900 dark:text-sidan-500">
                                        <img src="{{ $usuario->imagen_perfil ? asset('storage/' . $usuario->imagen_perfil) : asset('images/usuario.png') }}"
                                            alt="Foto de {{ trim(($usuario->informacion_personal?->nombres ?? '') . ' ' . ($usuario->informacion_personal?->apellidos ?? '')) ?: 'usuario SIDAN' }}"
                                            loading="lazy" class="h-11 w-11 shrink-0 rounded-full border border-slate-200 bg-slate-100 object-cover dark:border-white/10 dark:bg-white/5">
                                        <span class="min-w-0 text-sm"><strong class="block break-words text-sidan-900 dark:text-white">{{ $usuario->informacion_personal?->nombres }} {{ $usuario->informacion_personal?->apellidos }}</strong><span class="block break-all text-slate-500 dark:text-slate-300">{{ $usuario->correo }}</span></span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <p x-show="tipo === 'existente' && usuarioSeleccionado" x-cloak
                        class="mt-3 rounded-xl border border-sidan-200 bg-sidan-50 px-4 py-3 text-sm text-sidan-900 dark:border-sidan-500/30 dark:bg-sidan-500/10 dark:text-sidan-200">
                        Comprador seleccionado: <strong x-text="nombreComprador"></strong>
                    </p>
                    <div x-show="tipo==='nuevo'" class="grid gap-3 sm:grid-cols-3">
                        <label class="space-y-1"><span class="text-xs font-semibold">Nombre y apellido</span><input type="text" name="nombre" maxlength="200" value="{{ old('nombre') }}" placeholder="Nombre completo" :required="tipo==='nuevo'" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-white/20 dark:bg-sidan-950 dark:text-white"></label>
                        <label class="space-y-1"><span class="text-xs font-semibold">Correo</span><input type="email" name="correo" maxlength="150" value="{{ old('correo') }}" :required="tipo==='nuevo'" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-white/20 dark:bg-sidan-950 dark:text-white"></label>
                        <div class="space-y-1">
                            <label for="telefono" class="block text-xs font-semibold">Teléfono (opcional)</label>
                            {{-- app.js inicializa intl-tel-input sobre #telefono, igual que en Inscripciones. --}}
                            <input type="tel" id="telefono" name="telefono" maxlength="25" autocomplete="tel"
                                value="{{ old('telefono') }}" placeholder="Ej. 7000 0000"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/20 dark:border-white/20 dark:bg-sidan-950 dark:text-white dark:focus:border-sidan-500">
                            <p class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-300">Seleccioná el país para agregar su código internacional al número.</p>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-300 sm:col-span-3">SIDAN enviará un enlace para establecer la contraseña. No se envían contraseñas por correo.</p>
                    </div>
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-[#0f172a] sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="text-base font-black text-sidan-900 dark:text-white">3. Productos de {{ $actividad->nombre }}</h2>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-300">Configurá un producto y agregalo al carrito. Podés repetir el mismo artículo con distintas opciones.</p>
                        </div>
                        <span class="rounded-full bg-sidan-50 px-3 py-1.5 text-xs font-bold text-sidan-600 dark:bg-sidan-500/10 dark:text-sidan-300" x-text="carrito.length + ' configuraciones en el carrito'"></span>
                    </div>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        @forelse($items as $item)
                            @php
                                $camposItem = collect($campos[$item->id_item_actividad] ?? [])->filter(fn($c) => is_array($c) && !empty($c['key']))->values();
                                $stock = $stockItems[(int)$item->id_item_actividad] ?? null;
                                $minimoItem = max(1, (int)($item->min_por_inscripcion ?? 1));
                                $maximoItem = $item->max_por_inscripcion !== null ? (int)$item->max_por_inscripcion : null;
                                $limiteItem = $stock === null ? $maximoItem : ($maximoItem === null ? $stock : min($stock, $maximoItem));
                                $ahoraItem = now();
                                $enVenta = (!$item->venta_desde || $ahoraItem->gte($item->venta_desde)) && (!$item->venta_hasta || $ahoraItem->lte($item->venta_hasta));
                                $puedeAgregar = $enVenta && ($limiteItem === null || $limiteItem >= $minimoItem);
                                $medioItem = $item->medios->firstWhere('es_portada', true) ?: $item->medios->first();
                                $rutaImagen = str_replace('\\', '/', trim((string)($medioItem?->url ?? '')));
                                $imagenItem = null;
                                if ($rutaImagen !== '') {
                                    if (\Illuminate\Support\Str::startsWith($rutaImagen, ['https://', 'http://', 'data:'])) {
                                        $imagenItem = $rutaImagen;
                                    } else {
                                        $rutaImagen = ltrim($rutaImagen, '/');
                                        if (\Illuminate\Support\Str::startsWith($rutaImagen, 'public/')) $rutaImagen = substr($rutaImagen, 7);
                                        $imagenItem = asset(\Illuminate\Support\Str::startsWith($rutaImagen, 'storage/') ? $rutaImagen : 'storage/' . $rutaImagen);
                                    }
                                }
                                $variantesItem = $item->variantes->map(fn($v) => [
                                    'id' => (int)$v->id_variante_item, 'nombre' => $v->nombre_variante,
                                    'precio' => $v->precio === null ? null : (float)$v->precio,
                                    'stock' => $stockVariantes[(int)$v->id_variante_item] ?? null,
                                ])->values()->all();
                            @endphp
                            <article data-caja-producto data-id="{{ $item->id_item_actividad }}"
                                data-nombre="{{ $item->nombre }}" data-precio="{{ $item->precio }}"
                                data-stock="{{ $limiteItem ?? '' }}" data-minimo="{{ $minimoItem }}"
                                data-habilitado="{{ $puedeAgregar ? '1' : '0' }}"
                                data-anticipo="{{ $item->permite_anticipo ? '1' : '0' }}"
                                data-tipo-anticipo="{{ $item->tipo_anticipo }}" data-valor-anticipo="{{ $item->valor_anticipo }}"
                                data-variantes='@json($variantesItem)' data-reglas='@json($reglasPrecio[(int)$item->id_item_actividad] ?? [])'
                                class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/40 p-4 dark:border-white/10 dark:bg-white/[0.025]">
                                <div class="flex items-start gap-4">
                                    @if($imagenItem)
                                        <img src="{{ $imagenItem }}" alt="{{ $item->nombre }}" loading="lazy" class="h-24 w-24 shrink-0 rounded-xl bg-white object-contain dark:bg-sidan-950 sm:h-28 sm:w-28">
                                    @else
                                        <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-xl bg-sidan-900 text-sidan-300 dark:bg-sidan-950 sm:h-28 sm:w-28" aria-hidden="true">
                                            <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 7 12 3l9 4v10l-9 4-9-4V7Zm0 0 9 4 9-4M12 11v10"/></svg>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="text-[11px] font-bold uppercase text-sidan-600 dark:text-sidan-400">{{ ucfirst($item->tipo) }}</p>
                                        <h3 class="mt-1 break-words font-black text-sidan-900 dark:text-white">{{ $item->nombre }}</h3>
                                        <p class="mt-1 text-sm font-black">${{ number_format((float)$item->precio, 2) }} / unidad</p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-300">{{ $item->permite_anticipo ? 'Permite anticipo' : 'Pago completo' }}</p>
                                        <p class="mt-2 text-xs font-semibold {{ !$puedeAgregar ? 'text-red-600 dark:text-red-400' : 'text-sidan-600 dark:text-sidan-400' }}">
                                            @if(!$enVenta) No disponible para venta actualmente
                                            @elseif($limiteItem === 0) Agotado
                                            @elseif(!$puedeAgregar) Stock insuficiente para el mínimo de {{ $minimoItem }}
                                            @elseif($stock !== null) Disponibles: {{ $stock }}
                                            @else Stock sin límite @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    {{-- Los campos dinámicos (talla, color, etc.) ya configuran el producto.
                                         Solo pedir una variante si no existen dichos campos y
                                         el producto utiliza variantes tradicionales de PostgreSQL. --}}
                                    @if($camposItem->isEmpty() && $item->variantes->isNotEmpty())
                                        <label class="block min-w-0 space-y-1 sm:col-span-2">
                                            <span class="text-xs font-semibold">Variante *</span>
                                            <select data-caja-variante @change="actualizarLimite($event.target.closest('[data-caja-producto]'))"
                                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-white/20 dark:bg-sidan-950 dark:text-white" @disabled(!$puedeAgregar)>
                                                <option value="">Seleccionar variante</option>
                                                @foreach($item->variantes as $v)
                                                    <option value="{{ $v->id_variante_item }}" @disabled(($stockVariantes[(int)$v->id_variante_item] ?? null) === 0)>{{ $v->nombre_variante }}{{ ($stockVariantes[(int)$v->id_variante_item] ?? null) === 0 ? ' — Agotada' : '' }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                    @endif
                                    @foreach($camposItem as $campo)
                                        @php($clave = (string)$campo['key'])
                                        <label class="block min-w-0 space-y-1">
                                            <span class="text-xs font-semibold">{{ $campo['label'] ?? $clave }} {{ !empty($campo['required']) ? '*' : '' }}</span>
                                            @if(($campo['type'] ?? '') === 'lista')
                                                <select data-caja-campo="{{ $clave }}" data-obligatorio="{{ !empty($campo['required']) ? '1' : '0' }}"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-white/20 dark:bg-sidan-950 dark:text-white" @disabled(!$puedeAgregar)>
                                                    <option value="">Seleccionar</option>
                                                    @foreach(($campo['options'] ?? []) as $opcion)
                                                        <option value="{{ $opcion }}">{{ $opcion }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input data-caja-campo="{{ $clave }}" data-obligatorio="{{ !empty($campo['required']) ? '1' : '0' }}"
                                                    type="{{ ($campo['type'] ?? '') === 'numero' ? 'number' : 'text' }}"
                                                    @if(($campo['type'] ?? '') === 'numero') step="any" @endif maxlength="200"
                                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-white/20 dark:bg-sidan-950 dark:text-white" @disabled(!$puedeAgregar)>
                                            @endif
                                        </label>
                                    @endforeach
                                    <label class="block min-w-0 space-y-1">
                                        <span class="text-xs font-semibold">Cantidad</span>
                                        <input data-caja-cantidad type="number" min="{{ $minimoItem }}" max="{{ $limiteItem ?? 1000 }}"
                                            value="{{ $minimoItem }}" @disabled(!$puedeAgregar)
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-white/20 dark:bg-sidan-950 dark:text-white">
                                    </label>
                                </div>
                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-3 dark:border-white/10">
                                    <span class="text-xs text-slate-500 dark:text-slate-300">Precio y stock definitivos se verifican al crear la orden.</span>
                                    <button type="button" @click="agregar($event.currentTarget.closest('[data-caja-producto]'))" @disabled(!$puedeAgregar)
                                        class="rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-sidan-600 disabled:cursor-not-allowed disabled:opacity-40">Agregar al carrito</button>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-300 p-5 text-sm text-slate-500 dark:border-white/10 dark:text-slate-300">Esta actividad no tiene productos activos.</p>
                        @endforelse
                    </div>
                    <section class="mt-6 rounded-2xl border border-sidan-200 bg-sidan-50/50 p-4 dark:border-sidan-500/30 dark:bg-sidan-500/5" aria-label="Carrito de la venta presencial">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-black text-sidan-900 dark:text-white">Carrito de compra</h3>
                            <span class="text-xs font-bold text-sidan-600 dark:text-sidan-300" x-text="totalUnidades + ' unidades'"></span>
                        </div>
                        <p x-show="carrito.length===0" class="mt-3 text-sm text-slate-500 dark:text-slate-300">Todavía no has agregado ningún producto.</p>
                        <div class="mt-3 space-y-2">
                            <template x-for="(linea, i) in carrito" :key="linea.uid">
                                <div class="flex items-start justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 dark:border-white/10 dark:bg-sidan-950">
                                    <div class="min-w-0">
                                        <p class="break-words text-sm font-bold" x-text="linea.nombre"></p>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-300" x-text="descripcionLinea(linea)"></p>
                                        <p class="mt-1 text-sm font-bold" x-text="dinero(linea.precioCentavos * linea.cantidad)"></p>
                                    </div>
                                    <button type="button" @click="quitar(i)" class="shrink-0 rounded-lg px-2.5 py-1.5 text-xs font-bold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">Quitar</button>
                                </div>
                            </template>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-sidan-200 pt-3 text-sm dark:border-sidan-500/30">
                            <span class="font-semibold">Subtotal estimado del carrito</span>
                            <strong class="text-lg text-sidan-700 dark:text-sidan-300" x-text="dinero(totalCentavos)"></strong>
                        </div>
                        <p x-show="mensaje" x-text="mensaje" role="alert" class="mt-3 rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-800 dark:bg-amber-500/10 dark:text-amber-200"></p>
                    </section>
                </section>
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                    <h2 class="text-base font-black text-sidan-900 dark:text-white">4. Cobro inicial</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="space-y-1"><span class="text-sm font-semibold">Método de pago</span>
                            <select name="metodo" x-model="metodo" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-white/20 dark:bg-sidan-950 dark:text-white"><option value="efectivo">Efectivo</option><option value="transferencia">Transferencia bancaria</option></select></label>
                        <label class="space-y-1"><span class="text-sm font-semibold">Modalidad solicitada</span>
                            <select name="modalidad" x-model="modalidad" :disabled="!hayAnticipo" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-white/20 dark:bg-sidan-950 dark:text-white"><option value="completo">Pago completo</option><option value="anticipo" x-show="hayAnticipo">Anticipo (productos elegibles)</option></select>
                            <input type="hidden" name="modalidad" value="completo" :disabled="hayAnticipo">
                            <span x-show="!hayAnticipo" class="block text-xs text-slate-500 dark:text-slate-300">Sin productos con anticipo: se requiere pago completo.</span>
                        </label>
                        <label class="space-y-1"><span class="text-sm font-semibold">Importe recibido / reportado ($)</span>
                            <input type="number" name="monto_recibido" min="0" step="0.01" x-model.number="monto" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 dark:border-white/20 dark:bg-sidan-950 dark:text-white"></label>
                        <div x-show="metodo==='transferencia'" x-cloak class="space-y-3">
                            <label class="space-y-1"><span class="text-sm font-semibold">Comprobante de transferencia</span>
                                <input type="file" name="comprobante" accept="image/png,image/jpeg,image/webp" :required="metodo==='transferencia'" class="block w-full text-sm"></label>
                            <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="transferencia_verificada" value="1" x-model="transferenciaVerificada" class="mt-1 accent-sidan-500"> Verifiqué personalmente el depósito en la cuenta bancaria; puedo acreditarlo.</label>
                            <p class="text-xs text-amber-700 dark:text-amber-300">Si no marcás la casilla, el dinero quedará reportado y pendiente de revisión, NO acreditado.</p>
                        </div>
                    </div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-xl bg-slate-100 p-4 dark:bg-white/5"><p class="text-xs text-slate-500 dark:text-slate-300">Total estimado</p><p class="mt-1 text-xl font-black" x-text="dinero(totalCentavos)"></p></div>
                        <div class="rounded-xl bg-slate-100 p-4 dark:bg-white/5"><p class="text-xs text-slate-500 dark:text-slate-300">Anticipo mínimo estimado</p><p class="mt-1 text-xl font-black" x-text="dinero(hayAnticipo ? minimoCentavos : 0)"></p></div>
                        <div class="rounded-xl bg-slate-100 p-4 dark:bg-white/5"><p class="text-xs text-slate-500 dark:text-slate-300">Saldo estimado</p><p class="mt-1 text-xl font-black" x-text="dinero(Math.max(0, totalCentavos - acreditadoCentavos))"></p></div>
                        <div class="rounded-xl bg-slate-100 p-4 dark:bg-white/5"><p class="text-xs text-slate-500 dark:text-slate-300">Falta de anticipo estimada</p><p class="mt-1 text-xl font-black" x-text="dinero(hayAnticipo ? Math.max(0, minimoCentavos - acreditadoCentavos) : 0)"></p></div>
                    </div>
                    <p x-show="metodo==='transferencia' && !transferenciaVerificada && (Number(monto)||0)>0" x-cloak class="mt-3 text-xs font-semibold text-amber-700 dark:text-amber-300">La transferencia reportada aún no se descuenta del saldo. Se acreditará solo después de verificarla.</p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-300">Las cifras del carrito son orientativas. Promociones, descuentos y opciones especiales pueden modificar el importe final; SIDAN lo recalcula en el servidor.</p>
                    <div class="mt-5 rounded-xl border border-amber-300 bg-amber-50 p-4 text-xs text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">El importe real, los descuentos, el anticipo y el saldo final se verifican en el servidor. Los abonos acreditados no generan reembolsos automáticos. El recibo de venta se emite cuando se confirma el total.</div>
                    <button type="submit" :disabled="carrito.length===0 || enviando" class="mt-5 w-full rounded-xl bg-sidan-500 px-6 py-4 text-sm font-black text-white hover:bg-sidan-600 disabled:cursor-not-allowed disabled:opacity-50">Crear orden y registrar cobro</button>
                </section>

                {{-- Se abre únicamente después de validar el comprador y los datos del formulario. --}}
                <div x-show="confirmacionAbierta" x-cloak x-transition.opacity
                    @keydown.escape.window="cerrarConfirmacion()"
                    class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto bg-sidan-950/80 p-4 backdrop-blur-sm">
                    <div role="dialog" aria-modal="true" aria-labelledby="confirmar-venta-titulo"
                        @click.outside="cerrarConfirmacion()"
                        class="my-auto w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 text-slate-900 shadow-2xl dark:border-white/10 dark:bg-[#0f172a] dark:text-white">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div>
                                <h3 id="confirmar-venta-titulo" class="text-xl font-black">¿Confirmar venta presencial?</h3>
                                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Revisá los datos antes de crear la orden y registrar el cobro.</p>
                            </div>
                            <button type="button" @click="cerrarConfirmacion()" :disabled="enviando" aria-label="Cerrar confirmación"
                                class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/10">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <dl class="space-y-3 rounded-xl bg-slate-50 p-4 text-sm dark:bg-white/5">
                            <div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-300">Comprador</dt><dd class="text-right font-bold" x-text="nombreComprador"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-300">Productos</dt><dd class="font-bold" x-text="totalUnidades + ' unidad(es)'"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-300">Método</dt><dd class="font-bold" x-text="metodo === 'efectivo' ? 'Efectivo' : 'Transferencia bancaria'"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-300">Total estimado</dt><dd class="font-bold" x-text="dinero(totalCentavos)"></dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-slate-500 dark:text-slate-300">Importe recibido / reportado</dt><dd class="font-bold" x-text="dinero(Math.round(Math.max(0, Number(monto)||0)*100))"></dd></div>
                            <div class="flex justify-between gap-3 border-t border-slate-200 pt-3 dark:border-white/10"><dt class="text-slate-500 dark:text-slate-300">Saldo estimado</dt><dd class="font-black text-sidan-600 dark:text-sidan-400" x-text="dinero(Math.max(0, totalCentavos - acreditadoCentavos))"></dd></div>
                        </dl>
                        <p x-show="metodo === 'transferencia' && !transferenciaVerificada" class="mt-3 text-xs font-semibold text-amber-700 dark:text-amber-300">
                            La transferencia aún no fue verificada. El dinero quedará pendiente de revisión.
                        </p>
                        <p class="mt-3 text-xs text-slate-500 dark:text-slate-300">El sistema verificará nuevamente los importes, descuentos y existencias antes de registrar la orden.</p>
                        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button" @click="cerrarConfirmacion()" :disabled="enviando"
                                class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold hover:bg-slate-100 disabled:opacity-50 dark:border-white/20 dark:hover:bg-white/5">Cancelar</button>
                            <button type="button" @click="confirmarVenta()" :disabled="enviando"
                                class="rounded-xl bg-sidan-500 px-5 py-3 text-sm font-bold text-white hover:bg-sidan-600 disabled:cursor-not-allowed disabled:opacity-50"
                                x-text="enviando ? 'Registrando venta...' : 'Confirmar y crear orden'"></button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function cajaPresencial() {
    return {
        tipo: @js(old('comprador_tipo', 'existente')),
        usuarioSeleccionado: @js((string) old('id_usuario', '')),
        metodo: @js(old('metodo', 'efectivo')),
        modalidad: @js(old('modalidad', 'completo')),
        transferenciaVerificada: false,
        monto: Number(@js(old('monto_recibido', '0.00'))) || 0,
        carrito: [],
        mensaje: '',
        errorComprador: '',
        confirmacionAbierta: false,
        enviando: false,
        secuencia: 0,
        // Referencia estable al formulario completo. `this.$el` apunta al elemento
        // que ejecuta cada expresión de Alpine (botón, texto, etc.).
        get formulario() {
            return document.getElementById('formulario-venta-presencial');
        },
        get nombreComprador() {
            if (this.tipo === 'nuevo') {
                return this.formulario.querySelector('input[name="nombre"]')?.value.trim() || 'Nuevo comprador';
            }
            const id = String(this.usuarioSeleccionado ?? '');
            const elegido = Array.from(this.formulario.querySelectorAll('input[data-caja-comprador]'))
                .find(radio => radio.value === id);
            return elegido?.closest('label')?.querySelector('strong')?.textContent.trim() || 'Sin seleccionar';
        },
        compradorSeleccionadoValido() {
            if (this.tipo !== 'existente') return true;
            const marcado = this.formulario.querySelector('input[data-caja-comprador]:checked');
            return !!marcado && String(marcado.value) === String(this.usuarioSeleccionado)
                && String(this.usuarioSeleccionado).trim() !== '';
        },
        dinero(c) { return '$' + (c / 100).toFixed(2); },
        get totalUnidades() { return this.carrito.reduce((n, x) => n + x.cantidad, 0); },
        get totalCentavos() { return this.carrito.reduce((n, x) => n + x.precioCentavos * x.cantidad, 0); },
        get hayAnticipo() { return this.carrito.some(x => x.permiteAnticipo && x.minimoCentavos < x.precioCentavos * x.cantidad); },
        get minimoCentavos() { return this.carrito.reduce((n, x) => n + x.minimoCentavos, 0); },
        get acreditadoCentavos() {
            return this.metodo === 'transferencia' && !this.transferenciaVerificada
                ? 0 : Math.max(0, Math.round((Number(this.monto) || 0) * 100));
        },
        lineaVariante(card) {
            const sel = card.querySelector('[data-caja-variante]');
            if (!sel) return null;
            const variantes = JSON.parse(card.dataset.variantes || '[]');
            return variantes.find(v => String(v.id) === sel.value) || null;
        },
        yaAgregado(item, variante) {
            return this.carrito.filter(l => l.id_item === item && (l.id_variante ?? null) === variante)
                .reduce((n, l) => n + l.cantidad, 0);
        },
        limite(card) {
            const id = Number(card.dataset.id);
            const sel = card.querySelector('[data-caja-variante]');
            const variante = this.lineaVariante(card);
            if (sel && !variante) return 0;
            const stockItem = card.dataset.stock === '' ? Infinity : Number(card.dataset.stock);
            let disponible = stockItem - this.carrito.filter(l => l.id_item === id).reduce((n,l) => n + l.cantidad, 0);
            if (variante) {
                const stockVariante = variante.stock === null ? Infinity : Number(variante.stock);
                disponible = Math.min(disponible, stockVariante - this.yaAgregado(id, variante.id));
            }
            return Math.max(0, Math.min(1000, disponible));
        },
        actualizarLimite(card) {
            if (!card) return;
            const campo = card.querySelector('[data-caja-cantidad]');
            const max = this.limite(card);
            campo.max = String(max);
            if (Number(campo.value) > max) campo.value = String(max);
        },
        precioUnitario(card, variante, campos) {
            let precio = variante && variante.precio !== null ? Number(variante.precio) : Number(card.dataset.precio);
            const reglas = JSON.parse(card.dataset.reglas || '[]');
            reglas.forEach(r => {
                if (Object.prototype.hasOwnProperty.call(campos, r.field_key)
                    && String(campos[r.field_key]) === String(r.option ?? '')) {
                    precio += Number(r.price_increment || 0);
                }
            });
            return Math.max(0, Math.round(precio * 100));
        },
        agregar(card) {
            this.mensaje = '';
            if (card.dataset.habilitado !== '1') { this.mensaje = 'Producto fuera de venta o sin stock.'; return; }
            if (this.carrito.length >= 50) { this.mensaje = 'El carrito admite hasta 50 configuraciones.'; return; }
            const id = Number(card.dataset.id);
            const seleccion = card.querySelector('[data-caja-variante]');
            const variante = this.lineaVariante(card);
            if (seleccion && !variante) { this.mensaje = 'Elegí una variante para ' + card.dataset.nombre + '.'; return; }
            const campos = {};
            for (const input of card.querySelectorAll('[data-caja-campo]')) {
                const valor = input.value.trim();
                if (input.dataset.obligatorio === '1' && !valor) {
                    this.mensaje = 'Completá ' + input.dataset.cajaCampo + ' antes de agregar ' + card.dataset.nombre + '.';
                    input.focus(); return;
                }
                campos[input.dataset.cajaCampo] = valor;
            }
            const input = card.querySelector('[data-caja-cantidad]');
            const cantidad = Number(input.value);
            const min = Number(card.dataset.minimo || 1);
            const max = this.limite(card);
            if (!Number.isInteger(cantidad) || cantidad < min || cantidad > max) {
                this.mensaje = 'La cantidad de ' + card.dataset.nombre + ' debe estar entre ' + min + ' y ' + max + ' (stock disponible).';
                input.focus(); return;
            }
            const precio = this.precioUnitario(card, variante, campos);
            const subtotal = precio * cantidad;
            const parcial = card.dataset.anticipo === '1';
            const valor = Math.round((Number(card.dataset.valorAnticipo) || 0) * 100);
            const minimo = !parcial ? subtotal : card.dataset.tipoAnticipo === 'porcentaje'
                ? Math.min(subtotal, Math.ceil(subtotal * valor / 10000))
                : Math.min(subtotal, valor * cantidad);
            this.carrito.push({
                uid: ++this.secuencia, id_item: id, id_variante: variante ? variante.id : null,
                cantidad, campos, nombre: card.dataset.nombre, varianteNombre: variante?.nombre ?? null,
                precioCentavos: precio, minimoCentavos: minimo, permiteAnticipo: parcial,
            });
            // La línea ya conserva su propia copia de variante/campos/cantidad.
            // Limpiar SOLO la tarjeta agregada para preparar otra configuración.
            // Nunca hacerlo antes de agregar correctamente al carrito.
            for (const campo of card.querySelectorAll('[data-caja-campo]')) {
                if (campo.tagName === 'SELECT') campo.selectedIndex = 0;
                else if (campo.type === 'checkbox' || campo.type === 'radio') campo.checked = false;
                else campo.value = '';
            }
            if (seleccion) {
                // Las variantes tradicionales deberán elegirse otra vez.
                seleccion.selectedIndex = 0;
                // Sin variante elegida no hay límite calculable por variante.
                input.max = String(card.dataset.stock || 1000);
            } else {
                this.actualizarLimite(card);
            }
            input.value = String(min);
            this.$nextTick(() => {
                if (!this.hayAnticipo) this.modalidad = 'completo';
            });
        },
        quitar(i) {
            this.carrito.splice(i, 1);
            this.mensaje = '';
            this.$nextTick(() => {
                this.formulario.querySelectorAll('[data-caja-producto]').forEach(c => this.actualizarLimite(c));
                if (!this.hayAnticipo) this.modalidad = 'completo';
            });
        },
        descripcionLinea(l) {
            const opciones = Object.entries(l.campos).filter(([,v]) => v !== '').map(([k,v]) => k + ': ' + v);
            if (l.varianteNombre) opciones.unshift(l.varianteNombre);
            return l.cantidad + ' unidad(es)' + (opciones.length ? ' · ' + opciones.join(' · ') : '');
        },
        validarEnvio(e) {
            // El primer submit solo revisa el formulario; el segundo paso es el modal.
            e.preventDefault();
            if (this.enviando || this.confirmacionAbierta) return;
            this.errorComprador = '';
            if (!this.compradorSeleccionadoValido()) {
                this.errorComprador = 'Seleccioná al comprador antes de crear la orden.';
                this.$nextTick(() => this.$refs.seccionComprador.scrollIntoView({ behavior: 'smooth', block: 'center' }));
                return;
            }
            if (this.carrito.length === 0) {
                this.mensaje = 'Agregá al menos un artículo al carrito.';
                return;
            }
            if (!this.formulario.checkValidity()) {
                this.formulario.reportValidity();
                return;
            }
            this.confirmacionAbierta = true;
        },
        cerrarConfirmacion() {
            if (!this.enviando) this.confirmacionAbierta = false;
        },
        confirmarVenta() {
            if (this.enviando || !this.confirmacionAbierta) return;
            const formulario = this.formulario;
            // Revalidar antes del envío real para impedir datos incompletos.
            if (!formulario.checkValidity()) {
                this.confirmacionAbierta = false;
                this.$nextTick(() => formulario.reportValidity());
                return;
            }
            if (!this.compradorSeleccionadoValido()) {
                this.confirmacionAbierta = false;
                this.errorComprador = 'Seleccioná al comprador antes de crear la orden.';
                this.$nextTick(() => this.$refs.seccionComprador.scrollIntoView({ behavior: 'smooth', block: 'center' }));
                return;
            }
            if (!this.carrito.length) {
                this.confirmacionAbierta = false;
                this.mensaje = 'Agregá al menos un artículo al carrito.';
                return;
            }
            // Sincronizar el ID inmediatamente antes del submit nativo, sin depender
            // del ciclo de renderizado de Alpine ni del radio visual.
            const idUsuario = formulario.querySelector('input[name="id_usuario"]');
            if (idUsuario) {
                idUsuario.value = this.tipo === 'existente' ? String(this.usuarioSeleccionado) : '';
            }
            // El envío nativo mediante form.submit() no dispara el evento 'submit'.
            // Normalizar el teléfono manualmente con la misma instancia utilizada
            // por Inscripciones para conservar el código del país elegido.
            if (this.tipo === 'nuevo') {
                const telefonoInput = formulario.querySelector('#telefono');
                const telefonoIti = window.telefonoIti;
                if (telefonoInput?.value.trim() && telefonoIti) {
                    const internacional = telefonoIti.getNumber();
                    if (internacional && internacional.startsWith('+')) {
                        telefonoInput.value = internacional;
                    }
                }
            }
            this.enviando = true;
            this.confirmacionAbierta = false;
            formulario.submit();
        }
    };
}
</script>
@endpush
