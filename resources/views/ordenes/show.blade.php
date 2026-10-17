@extends('layouts.public')



@section('title', 'Mi orden - SIDAN')



@section('content')

    @include('partials.public-header')

    @if (auth()->user()?->rol?->nombre === 'usuario')
        <div class="mx-auto max-w-6xl px-4 pt-6">
            <a href="{{ route('user.ordenes.index') }}"
                class="text-sm font-bold text-emerald-600 transition hover:text-emerald-700 dark:text-emerald-400">
                ← Mis órdenes
            </a>
        </div>
    @endif

    @php

        $usuario = auth()->user();

        $informacionPersonal = $usuario?->informacion_personal;

        $nombreCompleto = trim(($informacionPersonal?->nombres ?? '') . ' ' . ($informacionPersonal?->apellidos ?? ''));

        $estadoConfiguracion = match ($orden->estado) {
            'pendiente_pago' => [
                'texto' => 'Pendiente de pago',

                'clases' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300',

                'punto' => 'bg-amber-500',
            ],

            'pagada' => [
                'texto' => 'Pagada',

                'clases' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300',

                'punto' => 'bg-emerald-500',
            ],

            'pagada_prueba' => [
                'texto' => 'Pago de prueba',

                'clases' => 'bg-blue-100 text-blue-800 dark:bg-blue-500/10 dark:text-blue-300',

                'punto' => 'bg-blue-500',
            ],

            'revision_cupo' => [
                'texto' => 'Revisión de cupo',

                'clases' => 'bg-orange-100 text-orange-800 dark:bg-orange-500/10 dark:text-orange-300',

                'punto' => 'bg-orange-500',
            ],

            default => [
                'texto' => ucfirst(str_replace('_', ' ', $orden->estado)),

                'clases' => 'bg-slate-100 text-slate-700 dark:bg-white/5 dark:text-slate-300',

                'punto' => 'bg-slate-400',
            ],
        };
        if ($orden->estado === 'pendiente_pago' && $orden->pago?->estado === 'parcial') {
            $estadoConfiguracion['texto'] = 'Abono recibido · saldo pendiente';
        }
    @endphp



    <span id="sidan-order-state" data-state="{{ $orden->estado }}" class="hidden"></span>



    <main
        class="min-h-screen bg-[#f6f8fb] py-10 text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">

        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">



            <div class="mb-8">

                <a href="{{ route('activities.show', ['slug' => $orden->actividad->slug]) }}"
                    class="mb-5 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6" />
                    </svg>
                    Volver a la actividad
                </a>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">



                    <div>

                        <p
                            class="mb-2 text-sm font-semibold uppercase tracking-[0.18em] text-emerald-600 dark:text-emerald-400">

                            Resumen de compra

                        </p>



                        <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">

                            Orden #{{ $orden->id_orden }}

                        </h1>



                        <p class="mt-2 max-w-2xl text-sm text-slate-500 dark:text-slate-400">

                            Revisá los detalles de tu inscripción antes de continuar con el pago.

                        </p>

                    </div>



                    <span
                        class="inline-flex w-fit items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold {{ $estadoConfiguracion['clases'] }}">

                        <span class="h-2 w-2 rounded-full {{ $estadoConfiguracion['punto'] }}"></span>

                        {{ $estadoConfiguracion['texto'] }}

                    </span>



                </div>

            </div>



            @if ($errors->any())

                <div
                    class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                    <div class="flex gap-3">



                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v4m0 4h.01M10.3 3.7 2.4 17.4A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.6L13.7 3.7a2 2 0 0 0-3.4 0Z" />

                        </svg>



                        <div>

                            <p class="font-semibold">

                                No pudimos continuar con la orden.

                            </p>



                            <div class="mt-2 space-y-1 text-sm">

                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach

                            </div>

                        </div>



                    </div>

                </div>

            @endif



            <div class="grid gap-6 lg:grid-cols-[1fr_360px]">



                <div class="space-y-6">



                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">



                        <div class="border-b border-slate-200 px-6 py-5 dark:border-white/10">

                            <div class="flex items-center gap-3">



                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">

                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z" />

                                    </svg>

                                </div>



                                <div>

                                    <h2 class="font-semibold text-slate-900 dark:text-white">

                                        Actividad

                                    </h2>



                                    <p class="text-sm text-slate-500 dark:text-slate-400">

                                        Información asociada a esta orden.

                                    </p>

                                </div>



                            </div>

                        </div>



                        <div class="px-6 py-6">



                            <p class="text-lg font-semibold text-slate-900 dark:text-white">

                                {{ $orden->actividad->nombre }}

                            </p>



                            <div class="mt-4 flex flex-wrap gap-3 text-sm">



                                <div
                                    class="rounded-lg bg-slate-50 px-3 py-2 text-slate-600 dark:bg-white/[0.04] dark:text-slate-300">

                                    Orden:

                                    <span class="font-semibold text-slate-900 dark:text-white">

                                        #{{ $orden->id_orden }}

                                    </span>

                                </div>



                                <div
                                    class="rounded-lg bg-slate-50 px-3 py-2 text-slate-600 dark:bg-white/[0.04] dark:text-slate-300">

                                    Estado:

                                    <span class="font-semibold text-slate-900 dark:text-white">

                                        {{ $estadoConfiguracion['texto'] }}

                                    </span>

                                </div>



                            </div>



                        </div>



                    </section>



                    @auth

                        <section
                            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">



                            <div class="border-b border-slate-200 px-6 py-5 dark:border-white/10">



                                <div class="flex items-center justify-between gap-4">



                                    <div class="flex items-center gap-3">



                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 dark:bg-white/5 dark:text-slate-300">

                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">

                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 21a8 8 0 0 0-16 0m8-9a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />

                                            </svg>

                                        </div>



                                        <div>

                                            <h2 class="font-semibold text-slate-900 dark:text-white">

                                                Datos del comprador

                                            </h2>



                                            <p class="text-sm text-slate-500 dark:text-slate-400">

                                                Información vinculada a tu cuenta SIDAN.

                                            </p>

                                        </div>



                                    </div>



                                    <span
                                        class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-white/5 dark:text-slate-400 sm:inline-flex">

                                        Solo lectura

                                    </span>



                                </div>



                            </div>



                            <div class="grid gap-5 px-6 py-6 sm:grid-cols-2">



                                @if ($nombreCompleto !== '')
                                    <div>

                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">

                                            Nombre

                                        </p>



                                        <p class="mt-1 font-medium text-slate-900 dark:text-white">

                                            {{ $nombreCompleto }}

                                        </p>

                                    </div>
                                @endif



                                <div>

                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">

                                        Correo electrónico

                                    </p>



                                    <p class="mt-1 break-all font-medium text-slate-900 dark:text-white">

                                        {{ $usuario->correo }}

                                    </p>

                                </div>



                                @if (!empty($informacionPersonal?->documento))
                                    <div>

                                        <p
                                            class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">

                                            Documento

                                        </p>



                                        <p class="mt-1 font-medium text-slate-900 dark:text-white">

                                            {{ $informacionPersonal->documento }}

                                        </p>

                                    </div>
                                @endif



                            </div>



                            <div
                                class="border-t border-slate-200 bg-slate-50 px-6 py-4 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[0.02] dark:text-slate-400">

                                Estos datos no pueden modificarse desde la orden. Si necesitás corregirlos, hacelo desde tu
                                perfil antes de realizar el pago.

                            </div>



                        </section>

                    @endauth



                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">



                        <div class="border-b border-slate-200 px-6 py-5 dark:border-white/10">



                            <div class="flex items-center gap-3">



                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">

                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 7h16M4 12h16M4 17h10" />

                                    </svg>

                                </div>



                                <div>

                                    <h2 class="font-semibold text-slate-900 dark:text-white">

                                        Detalle de la orden

                                    </h2>



                                    <p class="text-sm text-slate-500 dark:text-slate-400">

                                        Conceptos incluidos en tu inscripción.

                                    </p>

                                </div>



                            </div>



                        </div>



                        <div class="divide-y divide-slate-100 dark:divide-white/5">



                            @foreach ($orden->detalles as $detalle)
                                <div class="flex items-start justify-between gap-6 px-6 py-5">



                                    <div class="min-w-0">



                                        <p class="font-medium text-slate-900 dark:text-white">

                                            {{ $detalle->nombre_concepto }}

                                        </p>



                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">

                                            Cantidad: {{ $detalle->cantidad }}

                                        </p>



                                    </div>



                                    <p class="shrink-0 font-semibold text-slate-900 dark:text-white">

                                        @if ((float) $detalle->descuento > 0)
                                            <span class="block text-xs font-normal text-slate-400 line-through">
                                                ${{ number_format((float) $detalle->subtotal, 2) }}
                                            </span>
                                        @endif

                                        <span class="block">
                                            ${{ number_format((float) $detalle->subtotal - (float) $detalle->descuento, 2) }}
                                        </span>

                                        @if ((float) $detalle->descuento > 0)
                                            <span
                                                class="mt-1 block text-xs font-normal text-emerald-600 dark:text-emerald-400">
                                                {{ $detalle->unidades_descuento }} unidad(es) con descuento
                                            </span>
                                        @endif

                                    </p>



                                </div>
                            @endforeach



                        </div>



                    </section>



                </div>



                <aside class="lg:sticky lg:top-6 lg:self-start">



                    <div
                        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">



                        <div class="px-6 py-6">



                            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">

                                Resumen de pago

                            </h2>



                            <div class="mt-6 space-y-4">



                                <div class="flex items-center justify-between text-sm">



                                    <span class="text-slate-500 dark:text-slate-400">

                                        Conceptos

                                    </span>



                                    <span class="font-medium text-slate-900 dark:text-white">

                                        {{ $orden->detalles->count() }}

                                    </span>



                                </div>



                                <div class="border-t border-slate-200 pt-5 dark:border-white/10">



                                    <div class="flex items-end justify-between gap-4">



                                        <span class="font-semibold text-slate-900 dark:text-white">

                                            Total

                                        </span>



                                        <span class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">

                                            ${{ number_format((float) $orden->total, 2) }}

                                        </span>



                                    </div>



                                </div>



                            </div>



                        </div>



                        @include('ordenes.partials.acciones', ['orden' => $orden])



                    </div>



                    @if ($orden->id_inscripcion)
                        <a href="{{ route('sidan.inscripciones.show', $orden->id_inscripcion) }}"
                            class="mt-4 flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-700 dark:border-white/10 dark:bg-[#0f172a] dark:text-slate-300 dark:hover:border-emerald-500/40 dark:hover:text-emerald-400">



                            Consultar inscripción



                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m9 18 6-6-6-6" />

                            </svg>



                        </a>
                    @endif



                </aside>



            </div>



        </div>

    </main>



    <div id="payment-result-modal"
        class="fixed inset-0 z-[250] hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm"
        role="dialog" aria-modal="true" aria-labelledby="payment-result-title">
        <div
            class="relative w-full max-w-lg overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">
            <button id="payment-modal-close" type="button" aria-label="Cerrar"
                class="absolute right-5 top-5 z-10 flex h-10 w-10 items-center justify-center rounded-full text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-200/70 dark:hover:bg-white/10 dark:hover:text-white dark:focus:ring-white/10">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="p-7 text-center sm:p-8">
                <div id="payment-modal-success-icon"
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 ring-8 ring-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/5">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="m5 12 4 4L19 6" />
                    </svg>
                </div>

                <div id="payment-modal-warning-icon"
                    class="mx-auto hidden h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600 ring-8 ring-amber-50 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/5">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v4m0 4h.01M10.3 3.7 2.4 17.4A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-2.6L13.7 3.7a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </div>

                <p id="payment-modal-label"
                    class="mt-6 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300">
                    Pago confirmado
                </p>

                <h2 id="payment-result-title"
                    class="mt-3 text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                    Pago confirmado
                </h2>

                <p id="payment-result-message"
                    class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-500 dark:text-slate-400"></p>

                <div
                    class="mt-6 flex items-center justify-between gap-5 rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4 text-left dark:border-white/10 dark:bg-white/[0.04]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                            Orden
                        </p>
                        <p class="mt-1 text-base font-bold text-slate-900 dark:text-white">
                            #{{ $orden->id_orden }}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">
                            Total
                        </p>
                        <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                            ${{ number_format((float) $orden->total, 2) }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    <button id="payment-modal-refresh" type="button"
                        class="group flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3.5 font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/20">
                        Ver orden actualizada
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
                        </svg>
                    </button>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <a href="{{ route('welcome') }}"
                            class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 dark:border-white/10 dark:bg-transparent dark:text-slate-300 dark:hover:border-emerald-500/30 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 11.5 12 4l9 7.5M5 10v10h14V10M9 20v-6h6v6" />
                            </svg>
                            Volver al inicio
                        </a>

                        @if ($orden->id_inscripcion)
                            <a href="{{ route('sidan.inscripciones.show', $orden->id_inscripcion) }}"
                                class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 dark:border-white/10 dark:bg-transparent dark:text-slate-300 dark:hover:bg-white/5">
                                Ver mi inscripción
                            </a>
                        @endif
                    </div>
                </div>

                <p class="mt-5 text-xs leading-5 text-slate-400 dark:text-slate-500">
                    Si la pestaña de Wompi permanece abierta, podés cerrarla con seguridad.
                </p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const form = document.getElementById('wompi-payment-form');

            const paymentButton = document.getElementById('wompi-payment-button');

            const waitingPanel = document.getElementById('payment-waiting');

            const stateMarker = document.getElementById('sidan-order-state');

            const modal = document.getElementById('payment-result-modal');

            const modalTitle = document.getElementById('payment-result-title');

            const modalMessage = document.getElementById('payment-result-message');

            const modalLabel = document.getElementById('payment-modal-label');

            const successIcon = document.getElementById('payment-modal-success-icon');

            const warningIcon = document.getElementById('payment-modal-warning-icon');

            const refreshButton = document.getElementById('payment-modal-refresh');

            const closeModalButton = document.getElementById('payment-modal-close');



            const orderId = @json($orden->id_orden);

            const initialState = @json($orden->estado);

            const storageKey = `sidan_wompi_order_${orderId}`;



            let wompiWindow = null;

            let pollingActive = false;

            let pollingTimer = null;

            let pollingStartedAt = null;



            const pollingInterval = 3000;

            const maximumPollingTime = 35 * 60 * 1000;



            function setWaitingState(active) {

                if (!form || !waitingPanel) {

                    return;

                }



                if (active) {

                    form.classList.add('hidden');

                    waitingPanel.classList.remove('hidden');

                } else {

                    form.classList.remove('hidden');

                    waitingPanel.classList.add('hidden');

                }

            }



            function stopPolling() {

                pollingActive = false;



                if (pollingTimer) {

                    clearTimeout(pollingTimer);

                    pollingTimer = null;

                }

            }



            function closePaymentModal() {

                if (!modal) {

                    return;

                }



                modal.classList.add('hidden');

                modal.classList.remove('flex');

                document.body.classList.remove('overflow-hidden');

            }



            function tryCloseWompi() {

                try {

                    if (wompiWindow && !wompiWindow.closed) {

                        wompiWindow.close();

                    }

                } catch (error) {

                }



                try {

                    window.focus();

                } catch (error) {

                }

            }



            function showResult(state) {

                stopPolling();

                sessionStorage.removeItem(storageKey);

                tryCloseWompi();



                if (!modal) {

                    window.location.reload();

                    return;

                }



                successIcon.classList.remove('hidden');

                successIcon.classList.add('flex');



                warningIcon.classList.add('hidden');

                warningIcon.classList.remove('flex');



                modalLabel.className =
                    'mt-6 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300';

                modalLabel.textContent = 'Pago confirmado';



                if (state === 'pagada') {

                    modalTitle.textContent = 'Pago confirmado';

                    modalMessage.textContent =
                        'Wompi confirmó correctamente tu pago. Tu inscripción quedó confirmada en SIDAN.';

                }



                if (state === 'pagada_prueba') {

                    modalLabel.className =
                        'mt-6 inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-blue-700 dark:bg-blue-500/10 dark:text-blue-300';

                    modalLabel.textContent = 'Modo de prueba';

                    modalTitle.textContent = 'Pago de prueba confirmado';

                    modalMessage.textContent =
                        'Wompi confirmó correctamente la transacción de prueba. El flujo completo funcionó, pero no representa una recaudación real.';

                }



                if (state === 'revision_cupo') {

                    successIcon.classList.add('hidden');

                    successIcon.classList.remove('flex');



                    warningIcon.classList.remove('hidden');

                    warningIcon.classList.add('flex');



                    modalLabel.className =
                        'mt-6 inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-amber-700 dark:bg-amber-500/10 dark:text-amber-300';

                    modalLabel.textContent = 'Revisión necesaria';

                    modalTitle.textContent = 'Pago recibido';

                    modalMessage.textContent =
                        'Wompi confirmó el pago, pero el cupo necesita revisión administrativa. No realices otro pago.';

                }



                modal.classList.remove('hidden');

                modal.classList.add('flex');



                document.body.classList.add('overflow-hidden');

            }



            function showTimeoutMessage() {

                stopPolling();



                if (!modal) {

                    return;

                }



                successIcon.classList.add('hidden');

                successIcon.classList.remove('flex');



                warningIcon.classList.remove('hidden');

                warningIcon.classList.add('flex');



                modalLabel.className =
                    'mt-6 inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-amber-700 dark:bg-amber-500/10 dark:text-amber-300';

                modalLabel.textContent = 'Confirmación pendiente';

                modalTitle.textContent = 'La confirmación está tardando';

                modalMessage.textContent =
                    'SIDAN todavía no recibió la confirmación final de Wompi. Esto no significa que el pago haya fallado. Revisá la orden nuevamente antes de intentar otro pago.';



                modal.classList.remove('hidden');

                modal.classList.add('flex');



                document.body.classList.add('overflow-hidden');

            }



            async function checkOrderState() {

                if (!pollingActive) {

                    return;

                }



                if (

                    pollingStartedAt &&

                    Date.now() - pollingStartedAt > maximumPollingTime

                ) {

                    showTimeoutMessage();

                    return;

                }



                try {

                    const url = new URL(window.location.href);



                    url.searchParams.set(

                        '_payment_check',

                        Date.now().toString()

                    );



                    const response = await fetch(url.toString(), {

                        method: 'GET',

                        credentials: 'same-origin',

                        cache: 'no-store',

                        headers: {

                            'X-Requested-With': 'XMLHttpRequest',

                            'Cache-Control': 'no-cache'

                        }

                    });



                    if (response.ok) {

                        const html = await response.text();



                        const documentResult = new DOMParser().parseFromString(

                            html,

                            'text/html'

                        );



                        const newStateMarker = documentResult.querySelector(

                            '#sidan-order-state'

                        );



                        const newState = newStateMarker?.dataset?.state;



                        if (

                            newState === 'pagada' ||

                            newState === 'pagada_prueba' ||

                            newState === 'revision_cupo'

                        ) {

                            showResult(newState);

                            return;

                        }



                        if (

                            newState &&

                            newState !== 'pendiente_pago'

                        ) {

                            stopPolling();

                            sessionStorage.removeItem(storageKey);

                            window.location.reload();

                            return;

                        }

                    }

                } catch (error) {

                }



                if (pollingActive) {

                    pollingTimer = setTimeout(

                        checkOrderState,

                        pollingInterval

                    );

                }

            }



            function startPolling() {

                if (pollingActive) {

                    return;

                }



                pollingActive = true;

                pollingStartedAt = Date.now();



                checkOrderState();

            }



            if (form) {

                form.addEventListener('submit', () => {

                    wompiWindow = window.open(

                        '',

                        'wompiPayment'

                    );



                    if (!wompiWindow) {

                        form.removeAttribute('target');

                        return;

                    }



                    sessionStorage.setItem(

                        storageKey,

                        'waiting'

                    );



                    if (paymentButton) {

                        paymentButton.disabled = true;

                    }



                    setWaitingState(true);

                    startPolling();

                });

            }



            if (

                initialState === 'pendiente_pago' &&

                sessionStorage.getItem(storageKey) === 'waiting'

            ) {

                setWaitingState(true);

                startPolling();

            }



            if (

                (

                    initialState === 'pagada' ||

                    initialState === 'pagada_prueba' ||

                    initialState === 'revision_cupo'

                ) &&

                sessionStorage.getItem(storageKey) === 'waiting'

            ) {

                showResult(initialState);

            }



            document.addEventListener('visibilitychange', () => {

                if (

                    document.visibilityState === 'visible' &&

                    pollingActive

                ) {

                    if (pollingTimer) {

                        clearTimeout(pollingTimer);

                        pollingTimer = null;

                    }



                    checkOrderState();

                }

            });



            if (refreshButton) {

                refreshButton.addEventListener('click', () => {

                    window.location.reload();

                });

            }



            if (closeModalButton) {

                closeModalButton.addEventListener('click', () => {

                    closePaymentModal();

                });

            }

        });
    </script>

@endsection