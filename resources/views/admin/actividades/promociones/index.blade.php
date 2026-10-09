@extends('layouts.navbars')

@section('title', 'Promociones · ' . $actividad->nombre)

@section('content')
@php
    $ahora = now();
    $totalPromociones = $actividad->promociones->count();
    $activas = $actividad->promociones->where('activo', true)->count();
    $vigentes = $actividad->promociones->filter(fn ($promocion) => $promocion->estaVigente())->count();
@endphp

<div class="min-h-screen bg-[#f6f8fb] text-slate-900 dark:bg-transparent dark:text-white">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <a href="{{ route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'precios']) }}" class="transition hover:text-emerald-500">
                        Configuración
                    </a>
                    <span>›</span>
                    <span>{{ $actividad->nombre }}</span>
                </div>

                <h1 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white sm:text-3xl">
                    Promociones y descuentos
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Configura descuentos por código o promociones automáticas. La aplicación efectiva del descuento se conectará al módulo de pagos.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'precios']) }}"
                    class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10">
                    Volver a precios
                </a>
                <button type="button" id="btnNuevaPromocion"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-black text-white transition hover:bg-emerald-500">
                    <span class="text-lg leading-none">+</span>
                    Nueva promoción
                </button>
            </div>
        </div>

        <div class="mb-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Configuradas</p>
                <p class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">{{ $totalPromociones }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Activas</p>
                <p class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">{{ $activas }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Vigentes ahora</p>
                <p class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $vigentes }}</p>
            </div>
        </div>

        @if ($actividad->promociones->isEmpty())
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center dark:border-white/10 dark:bg-[#0f172a]">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-2xl text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                    %
                </div>
                <h2 class="mt-4 text-lg font-black text-sidan-900 dark:text-white">Todavía no hay promociones</h2>
                <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Puedes crear descuentos para todos los productos o únicamente para productos seleccionados.
                </p>
                <button type="button" data-crear-promocion
                    class="mt-5 inline-flex rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-500">
                    Crear primera promoción
                </button>
            </div>
        @else
            <div class="space-y-3" id="listaPromociones">
                @foreach ($actividad->promociones as $promocion)
                    @php
                        $estadoPromo = !$promocion->activo
                            ? 'Inactiva'
                            : ($promocion->estaVigente() ? 'Vigente' : ($promocion->esProxima() ? 'Programada' : 'Finalizada'));

                        $claseEstado = match ($estadoPromo) {
                            'Vigente' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300',
                            'Programada' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300',
                            'Finalizada' => 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400',
                            default => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300',
                        };

                        $payloadPromocion = base64_encode(json_encode([
                            'id' => $promocion->id_promocion,
                            'nombre' => $promocion->nombre,
                            'codigo' => $promocion->codigo,
                            'tipo_descuento' => $promocion->tipo_descuento,
                            'valor' => (string) $promocion->valor,
                            'vigente_desde' => $promocion->vigente_desde?->format('Y-m-d\TH:i'),
                            'vigente_hasta' => $promocion->vigente_hasta?->format('Y-m-d\TH:i'),
                            'limite_usos' => $promocion->limite_usos,
                            'limite_por_persona' => $promocion->limite_por_persona,
                            'monto_minimo' => $promocion->monto_minimo,
                            'activo' => (bool) $promocion->activo,
                            'aplica_todos' => $promocion->items->isEmpty(),
                            'items' => $promocion->items->pluck('id_item_actividad')->map(fn ($id) => (int) $id)->values()->all(),
                        ], JSON_UNESCAPED_UNICODE));
                    @endphp

                    <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                        <summary class="flex cursor-pointer list-none flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-sm font-black text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                                        {{ $promocion->textoDescuento() }} OFF
                                    </span>
                                    <h2 class="truncate font-black text-sidan-900 dark:text-white">{{ $promocion->nombre }}</h2>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $claseEstado }}">{{ $estadoPromo }}</span>
                                </div>
                                <p class="mt-2 truncate text-sm text-slate-500 dark:text-slate-400">
                                    {{ $promocion->codigo ? 'Código '.$promocion->codigo : 'Promoción automática' }}
                                    ·
                                    {{ $promocion->items->isEmpty() ? 'Todos los productos' : $promocion->items->count().' producto'.($promocion->items->count() === 1 ? '' : 's') }}
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-2">
                                <button type="button"
                                    class="btnEditarPromocion rounded-lg border border-slate-200 px-3 py-2 text-xs font-black text-slate-700 transition hover:border-emerald-400 hover:text-emerald-600 dark:border-white/10 dark:text-slate-300"
                                    data-promocion="{{ $payloadPromocion }}"
                                    data-update-url="{{ route('admin.actividades.promociones.update', [$actividad, $promocion]) }}">
                                    Editar
                                </button>
                                <span class="px-2 text-slate-400 transition group-open:rotate-180">⌄</span>
                            </div>
                        </summary>

                        <div class="border-t border-slate-100 p-4 sm:p-5 dark:border-white/10">
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-white/5">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Vigencia</p>
                                    <p class="mt-1 text-sm font-bold text-sidan-900 dark:text-white">
                                        {{ $promocion->vigente_desde?->format('d/m/Y H:i') ?: 'Desde ahora' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        hasta {{ $promocion->vigente_hasta?->format('d/m/Y H:i') ?: 'sin fecha límite' }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-white/5">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Monto mínimo</p>
                                    <p class="mt-1 text-sm font-bold text-sidan-900 dark:text-white">
                                        {{ $promocion->monto_minimo !== null ? '$'.number_format((float) $promocion->monto_minimo, 2) : 'Sin mínimo' }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-white/5">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Límite total</p>
                                    <p class="mt-1 text-sm font-bold text-sidan-900 dark:text-white">
                                        {{ $promocion->limite_usos ?: 'Sin límite' }}
                                    </p>
                                </div>
                                <div class="rounded-xl bg-slate-50 p-3 dark:bg-white/5">
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Por persona</p>
                                    <p class="mt-1 text-sm font-bold text-sidan-900 dark:text-white">
                                        {{ $promocion->limite_por_persona ?: 'Sin límite' }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Aplica a</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @if ($promocion->items->isEmpty())
                                        <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">Todos los productos</span>
                                    @else
                                        @foreach ($promocion->items as $item)
                                            <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">{{ $item->nombre }}</span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-white/10">
                                <form class="formTogglePromocion" action="{{ route('admin.actividades.promociones.toggle', [$actividad, $promocion]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="activo" value="{{ $promocion->activo ? 0 : 1 }}">
                                    <button type="submit" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">
                                        {{ $promocion->activo ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>

                                <form class="formEliminarPromocion" action="{{ route('admin.actividades.promociones.destroy', [$actividad, $promocion]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50 dark:border-red-500/20 dark:text-red-300 dark:hover:bg-red-500/10">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div id="modalPromocion" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-950/75 p-4 backdrop-blur-sm">
    <div class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-3xl border border-white/10 bg-white shadow-2xl dark:bg-[#0b1220]">
        <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-200 bg-white/95 px-5 py-4 backdrop-blur dark:border-white/10 dark:bg-[#0b1220]/95 sm:px-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.16em] text-emerald-600">Promoción</p>
                <h2 id="tituloModalPromocion" class="mt-1 text-xl font-black text-sidan-900 dark:text-white">Nueva promoción</h2>
            </div>
            <button type="button" id="cerrarModalPromocion" class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-xl text-slate-500 transition hover:bg-slate-100 dark:border-white/10 dark:hover:bg-white/10">×</button>
        </div>

        <form id="formPromocion" method="POST" action="{{ route('admin.actividades.promociones.store', $actividad) }}" class="p-5 sm:p-6">
            @csrf
            <input type="hidden" name="_method" id="metodoPromocion" value="POST">

            <div id="erroresPromocion" class="mb-5 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-200"></div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Nombre</label>
                    <input type="text" name="nombre" maxlength="140" required placeholder="Ej. Preventa estudiantes"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    <p class="mt-1 hidden text-xs text-red-500" data-error="nombre"></p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Código opcional</label>
                    <input type="text" name="codigo" maxlength="60" placeholder="SIDAN20"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm uppercase outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/15 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    <p class="mt-1 text-xs text-slate-400">Si lo dejas vacío se considera una promoción automática.</p>
                    <p class="mt-1 hidden text-xs text-red-500" data-error="codigo"></p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Estado</label>
                    <select name="activo" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="1">Activa</option>
                        <option value="0">Inactiva</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Tipo de descuento</label>
                    <select name="tipo_descuento" id="tipoDescuentoPromocion" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="porcentaje">Porcentaje</option>
                        <option value="monto">Monto fijo</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Valor</label>
                    <div class="relative">
                        <span id="prefijoValorPromocion" class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-black text-slate-400">%</span>
                        <input type="number" name="valor" min="0.01" step="0.01" required class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm outline-none focus:border-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                    <p class="mt-1 hidden text-xs text-red-500" data-error="valor"></p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Vigente desde</label>
                    <input type="datetime-local" name="vigente_desde" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:[color-scheme:dark]">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Vigente hasta</label>
                    <input type="datetime-local" name="vigente_hasta" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:[color-scheme:dark]">
                    <p class="mt-1 hidden text-xs text-red-500" data-error="vigente_hasta"></p>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Monto mínimo</label>
                    <input type="number" name="monto_minimo" min="0" step="0.01" placeholder="Sin mínimo"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Límite total de usos</label>
                    <input type="number" name="limite_usos" min="1" step="1" placeholder="Sin límite"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Límite por persona</label>
                    <input type="number" name="limite_por_persona" min="1" step="1" placeholder="Sin límite"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 p-4 dark:border-white/10">
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="hidden" name="aplica_todos" value="0">
                    <input type="checkbox" id="aplicaTodosPromocion" name="aplica_todos" value="1" checked class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <p class="font-black text-sidan-900 dark:text-white">Aplica a todos los productos y servicios</p>
                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Desmárcalo si quieres limitar la promoción a productos específicos.</p>
                    </div>
                </label>

                <div id="selectorItemsPromocion" class="mt-4 hidden">
                    <input type="search" id="buscarItemPromocion" placeholder="Buscar producto..."
                        class="mb-3 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-500 dark:border-white/10 dark:bg-white/5 dark:text-white">

                    <div class="max-h-64 space-y-2 overflow-y-auto pr-1" id="listaItemsPromocion">
                        @forelse ($actividad->items as $item)
                            <label class="item-promocion flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-3 transition hover:border-emerald-300 dark:border-white/10" data-search="{{ strtolower($item->nombre) }}">
                                <input type="checkbox" name="items[]" value="{{ $item->id_item_actividad }}" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-bold text-sidan-900 dark:text-white">{{ $item->nombre }}</span>
                                    <span class="text-xs text-slate-400">${{ number_format((float) $item->precio, 2) }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500 dark:bg-white/5 dark:text-slate-400">Esta actividad no tiene productos activos.</p>
                        @endforelse
                    </div>
                    <p class="mt-2 hidden text-xs text-red-500" data-error="items"></p>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" id="cancelarModalPromocion" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">Cancelar</button>
                <button type="submit" id="guardarPromocion" class="rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50">Guardar promoción</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalPromocion');
    const form = document.getElementById('formPromocion');
    const titulo = document.getElementById('tituloModalPromocion');
    const metodo = document.getElementById('metodoPromocion');
    const btnGuardar = document.getElementById('guardarPromocion');
    const aplicaTodos = document.getElementById('aplicaTodosPromocion');
    const selectorItems = document.getElementById('selectorItemsPromocion');
    const buscarItem = document.getElementById('buscarItemPromocion');
    const tipo = document.getElementById('tipoDescuentoPromocion');
    const prefijo = document.getElementById('prefijoValorPromocion');
    const urlCrear = form.action;

    function decodificar(valor) {
        try {
            return JSON.parse(decodeURIComponent(escape(atob(valor))));
        } catch (error) {
            try {
                return JSON.parse(atob(valor));
            } catch (segundoError) {
                return null;
            }
        }
    }

    function abrirModal(datos = null, updateUrl = null) {
        form.reset();
        form.action = updateUrl || urlCrear;
        metodo.value = updateUrl ? 'PATCH' : 'POST';
        titulo.textContent = updateUrl ? 'Editar promoción' : 'Nueva promoción';
        limpiaErrores();

        form.querySelectorAll('input[name="items[]"]').forEach(input => {
            input.checked = false;
        });

        if (datos) {
            form.elements.nombre.value = datos.nombre || '';
            form.elements.codigo.value = datos.codigo || '';
            form.elements.tipo_descuento.value = datos.tipo_descuento || 'porcentaje';
            form.elements.valor.value = datos.valor || '';
            form.elements.vigente_desde.value = datos.vigente_desde || '';
            form.elements.vigente_hasta.value = datos.vigente_hasta || '';
            form.elements.limite_usos.value = datos.limite_usos || '';
            form.elements.limite_por_persona.value = datos.limite_por_persona || '';
            form.elements.monto_minimo.value = datos.monto_minimo ?? '';
            form.elements.activo.value = datos.activo ? '1' : '0';
            aplicaTodos.checked = !!datos.aplica_todos;

            const usados = new Set((datos.items || []).map(String));
            form.querySelectorAll('input[name="items[]"]').forEach(input => {
                input.checked = usados.has(String(input.value));
            });
        } else {
            aplicaTodos.checked = true;
            form.elements.activo.value = '1';
            form.elements.tipo_descuento.value = 'porcentaje';
        }

        actualizarItems();
        actualizarPrefijo();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function cerrarModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    function actualizarItems() {
        selectorItems.classList.toggle('hidden', aplicaTodos.checked);
    }

    function actualizarPrefijo() {
        prefijo.textContent = tipo.value === 'porcentaje' ? '%' : '$';
    }

    function limpiaErrores() {
        document.getElementById('erroresPromocion').classList.add('hidden');
        document.querySelectorAll('[data-error]').forEach(elemento => {
            elemento.textContent = '';
            elemento.classList.add('hidden');
        });
    }

    function mostrarErrores(errors, message) {
        limpiaErrores();
        const caja = document.getElementById('erroresPromocion');
        const mensajes = [];

        Object.entries(errors || {}).forEach(([campo, valores]) => {
            const clave = campo.startsWith('items.') ? 'items' : campo;
            const elemento = document.querySelector(`[data-error="${clave}"]`);
            const texto = Array.isArray(valores) ? valores[0] : String(valores);

            if (elemento) {
                elemento.textContent = texto;
                elemento.classList.remove('hidden');
            }

            mensajes.push(texto);
        });

        if (!mensajes.length && message) {
            mensajes.push(message);
        }

        if (mensajes.length) {
            caja.innerHTML = mensajes.map(texto => `<div>${escapar(texto)}</div>`).join('');
            caja.classList.remove('hidden');
        }

        window.SIDANToast?.error(message || mensajes[0] || 'Revisa los datos de la promoción.');
    }

    function escapar(valor) {
        return String(valor)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    async function enviarFormulario(evento) {
        evento.preventDefault();
        limpiaErrores();
        btnGuardar.disabled = true;
        const textoAnterior = btnGuardar.textContent;
        btnGuardar.textContent = 'Guardando...';

        try {
            const respuesta = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            });

            const datos = await respuesta.json().catch(() => ({}));

            if (!respuesta.ok) {
                mostrarErrores(datos.errors || {}, datos.message || 'No se pudo guardar la promoción.');
                return;
            }

            window.SIDANToast?.success(datos.message || 'Promoción guardada.');
            window.setTimeout(() => window.location.reload(), 450);
        } catch (error) {
            mostrarErrores({}, 'No se pudo conectar con el servidor.');
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = textoAnterior;
        }
    }

    async function enviarAccion(formulario, confirmar = null) {
        if (confirmar && !window.confirm(confirmar)) {
            return;
        }

        try {
            const respuesta = await fetch(formulario.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(formulario)
            });

            const datos = await respuesta.json().catch(() => ({}));

            if (!respuesta.ok) {
                window.SIDANToast?.error(datos.message || 'No se pudo completar la acción.');
                return;
            }

            window.SIDANToast?.success(datos.message || 'Actualizado.');
            window.setTimeout(() => window.location.reload(), 350);
        } catch (error) {
            window.SIDANToast?.error('No se pudo conectar con el servidor.');
        }
    }

    document.getElementById('btnNuevaPromocion')?.addEventListener('click', () => abrirModal());
    document.querySelectorAll('[data-crear-promocion]').forEach(boton => boton.addEventListener('click', () => abrirModal()));
    document.getElementById('cerrarModalPromocion')?.addEventListener('click', cerrarModal);
    document.getElementById('cancelarModalPromocion')?.addEventListener('click', cerrarModal);
    aplicaTodos.addEventListener('change', actualizarItems);
    tipo.addEventListener('change', actualizarPrefijo);
    form.addEventListener('submit', enviarFormulario);

    document.querySelectorAll('.btnEditarPromocion').forEach(boton => {
        boton.addEventListener('click', function (evento) {
            evento.preventDefault();
            evento.stopPropagation();
            abrirModal(decodificar(this.dataset.promocion), this.dataset.updateUrl);
        });
    });

    document.querySelectorAll('.formTogglePromocion').forEach(formulario => {
        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();
            enviarAccion(this);
        });
    });

    document.querySelectorAll('.formEliminarPromocion').forEach(formulario => {
        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();
            enviarAccion(this, '¿Eliminar esta promoción? Esta acción no se puede deshacer.');
        });
    });

    buscarItem?.addEventListener('input', function () {
        const termino = this.value.trim().toLowerCase();
        document.querySelectorAll('.item-promocion').forEach(item => {
            item.classList.toggle('hidden', termino !== '' && !item.dataset.search.includes(termino));
        });
    });

    modal.addEventListener('click', function (evento) {
        if (evento.target === modal) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape' && !modal.classList.contains('hidden')) {
            cerrarModal();
        }
    });
});
</script>
@endsection
