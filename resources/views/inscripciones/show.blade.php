
@extends('layouts.publico-con-navbar')

@section('title', 'Estado de mi inscripción - SIDAN')

@push('styles')
<style>
    @media print {
        .sidan-no-print {
            display: none !important;
        }

        .sidan-confirmacion {
            background: white !important;
            color: black !important;
        }

        .sidan-confirmacion-hero {
            background: white !important;
            color: black !important;
            border-bottom: 2px solid black;
        }

        .sidan-confirmacion-hero * {
            color: black !important;
        }

        .sidan-panel {
            background: white !important;
            color: black !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .sidan-panel * {
            color: black !important;
        }
    }
</style>
@endpush

@section('portal-content')
@php
    // Información de la inscripción y su actividad.
    $actividad = $inscripcion->actividad;
    $orden = $inscripcion->orden;
    $estado = $inscripcion->estado;

    $esUsuario = auth()->check()
        && auth()->user()->rol?->nombre === 'usuario';

    $plazoVencido = $estado === 'pendiente_pago'
        && $inscripcion->expira_en?->isPast();

    $puedeContinuarPago = $estado === 'pendiente_pago'
        && !$plazoVencido
        && $orden !== null;

    // Mensajes visuales según el estado real.
    $tituloEstado = match (true) {
        $estado === 'confirmada' => 'Inscripción confirmada',
        $estado === 'confirmada_prueba' => 'Confirmada en pruebas',
        $plazoVencido => 'Plazo de pago finalizado',
        $estado === 'pendiente_pago' => 'Pendiente de pago',
        $estado === 'revision_cupo' => 'Inscripción en revisión',
        $estado === 'vencida' => 'Inscripción vencida',
        $estado === 'cancelada' => 'Inscripción cancelada',
        default => 'Estado de inscripción',
    };

    $mensajeEstado = match (true) {
        $estado === 'confirmada' =>
            'Tu inscripción fue registrada y confirmada correctamente. Ya formás parte de esta actividad.',
        $estado === 'confirmada_prueba' =>
            'La inscripción fue confirmada en un ambiente de pruebas. Esta confirmación no representa un pago real.',
        $plazoVencido =>
            'El plazo establecido para completar el pago ha finalizado. Consultá el estado de tu orden.',
        $estado === 'pendiente_pago' =>
            'Tu solicitud de inscripción fue registrada. Para confirmar tu participación, todavía debés completar el pago.',
        $estado === 'revision_cupo' =>
            'Tu inscripción está siendo revisada. La participación aún no se encuentra confirmada.',
        $estado === 'vencida' =>
            'El plazo de esta inscripción finalizó sin completar los requisitos necesarios.',
        $estado === 'cancelada' =>
            'Esta inscripción fue cancelada y ya no se encuentra activa.',
        default =>
            'Podés consultar aquí la información y el estado registrado de tu inscripción.',
    };

    $referencia = 'SIDAN-INS-' . str_pad(
        (string) $inscripcion->id_inscripcion,
        6,
        '0',
        STR_PAD_LEFT
    );

    $ubicacion = $actividad?->ubicacion_externa
        ?: ($actividad?->espacio?->nombre ?? 'Por definir');
@endphp

<div class="sidan-confirmacion flex min-h-screen flex-col bg-[#f6f8fb] text-slate-900 transition-colors duration-300 dark:bg-sidan-950 dark:text-slate-100">

{{-- HERO DE CONFIRMACIÓN --}}
    <section class="sidan-confirmacion-hero relative isolate overflow-hidden bg-sidan-900 text-white">
        <div class="pointer-events-none absolute -right-24 -top-40 h-96 w-96 rounded-full border border-sidan-400/15"></div>
        <div class="pointer-events-none absolute -left-20 bottom-0 h-56 w-56 rounded-full bg-sidan-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-10 -top-24 h-72 w-72 rounded-full border border-white/10"></div>

        <div class="relative mx-auto max-w-7xl px-5 py-12 sm:py-16 lg:px-8">

            <div class="sidan-no-print mb-10 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-400">
                <a href="{{ route('welcome') }}" class="hover:text-white">Inicio</a>
                <span>/</span>
                <a href="{{ route('activities.index') }}" class="hover:text-white">Actividades</a>
                <span>/</span>
                <span class="text-white">Mi inscripción</span>
            </div>

            <div class="grid items-center gap-8 lg:grid-cols-12">

                <div class="lg:col-span-8">

                    {{-- INDICADOR DE ESTADO --}}
                    <div class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl border border-sidan-400/40 bg-sidan-500/15 text-sidan-300">
                        @if($estado === 'confirmada' || $estado === 'confirmada_prueba')
                            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="m7.5 12 3 3 6-6"/>
                            </svg>
                        @elseif($estado === 'pendiente_pago' && !$plazoVencido)
                            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                            </svg>
                        @else
                            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5m0 3h.01"/>
                            </svg>
                        @endif
                    </div>

                    <span class="inline-flex rounded-full border border-sidan-400/35 bg-sidan-500/10 px-4 py-1.5 text-[11px] font-bold uppercase tracking-widest text-sidan-200">
                        Estado de participación
                    </span>

                    <h1 class="mt-5 text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                        {{ $tituloEstado }}
                    </h1>

                    <p class="mt-6 max-w-2xl text-sm leading-7 text-slate-300 sm:text-base">
                        {{ $mensajeEstado }}
                    </p>

                </div>

                {{-- REFERENCIA DESTACADA --}}
                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-6 backdrop-blur-sm">
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">
                            Número de inscripción
                        </p>

                        <p class="mt-3 text-4xl font-black tracking-tight text-sidan-300">
                            #{{ $inscripcion->id_inscripcion }}
                        </p>

                        <div class="mt-5 border-t border-white/20 pt-5">
                            <p class="text-xs text-slate-400">Referencia de consulta</p>
                            <p class="mt-2 break-all font-mono text-sm font-bold tracking-wide">
                                {{ $referencia }}
                            </p>
                        </div>

                        <p class="mt-4 text-xs leading-5 text-slate-400">
                            Utilizá esta referencia para identificar tu inscripción.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- CUERPO PRINCIPAL --}}
    <main class="mx-auto w-full max-w-7xl flex-1 px-5 py-10 lg:px-8 lg:py-14">

        <div class="grid items-start gap-8 lg:grid-cols-12">

            {{-- COLUMNA PRINCIPAL --}}
            <div class="min-w-0 space-y-7 lg:col-span-7">

                {{-- DATOS DE INSCRIPCIÓN --}}
                <section class="sidan-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                    <div class="border-b border-slate-100 px-6 py-6 sm:px-8 dark:border-white/10">
                        <span class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                            Información del registro
                        </span>
                        <h2 class="mt-2 text-2xl font-black tracking-tight">
                            Detalles de tu inscripción
                        </h2>
                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Resumen de los datos asociados a tu participación.
                        </p>
                    </div>

                    <div class="px-6 py-7 sm:px-8">

                        <div class="grid gap-7 sm:grid-cols-2">

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Participante
                                </p>
                                <p class="mt-2 break-words text-base font-bold">
                                    {{ $inscripcion->nombre_participante }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Correo electrónico
                                </p>
                                <p class="mt-2 break-all text-sm font-bold">
                                    {{ $inscripcion->correo_participante }}
                                </p>
                            </div>

                            @if($inscripcion->telefono_participante)
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Teléfono
                                    </p>
                                    <p class="mt-2 text-sm font-bold">
                                        {{ $inscripcion->telefono_participante }}
                                    </p>
                                </div>
                            @endif

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Fecha de registro
                                </p>
                                <p class="mt-2 text-sm font-bold">
                                    {{ $inscripcion->created_at?->format('d/m/Y - h:i A') ?? 'No disponible' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    Estado actual
                                </p>
                                <span class="mt-2 inline-flex rounded-full border border-sidan-200 bg-sidan-50 px-3 py-1.5 text-xs font-black dark:border-sidan-500/30 dark:bg-sidan-500/10">
                                    {{ $tituloEstado }}
                                </span>
                            </div>

                            @if($inscripcion->fecha_confirmacion)
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        Fecha de confirmación
                                    </p>
                                    <p class="mt-2 text-sm font-bold">
                                        {{ $inscripcion->fecha_confirmacion->format('d/m/Y - h:i A') }}
                                    </p>
                                </div>
                            @endif

                        </div>

                    </div>
                </section>

                {{-- INFORMACIÓN IMPORTANTE SEGÚN ESTADO --}}
                <section class="sidan-panel rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-white/10 dark:bg-[#0f172a]">

                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5m0-8h.01"/>
                            </svg>
                        </div>

                        <div>
                            <h2 class="text-xl font-black">¿Qué sucede ahora?</h2>
                            <p class="mt-2 text-sm leading-7 text-slate-500 dark:text-slate-400">
                                Los siguientes pasos dependen del estado de tu inscripción.
                            </p>
                        </div>
                    </div>

                    <div class="mt-7 space-y-5">

                        @if($estado === 'confirmada')

                            <div class="flex gap-4">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sidan-500 text-white dark:bg-sidan-500">
                                    ✓
                                </span>
                                <div>
                                    <h3 class="text-sm font-black">Registro completado</h3>
                                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                        Tus datos fueron registrados correctamente y la inscripción se encuentra confirmada.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-300 text-sm font-bold dark:border-slate-700">
                                    2
                                </span>
                                <div>
                                    <h3 class="text-sm font-black">Prepararte para la actividad</h3>
                                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                        Revisá las fechas, horarios, ubicación e indicaciones publicadas por la organización.
                                    </p>
                                </div>
                            </div>

                        @elseif($puedeContinuarPago)

                            <div class="flex gap-4">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sidan-500 text-sidan-950 dark:bg-sidan-500 dark:text-sidan-950">
                                    ✓
                                </span>
                                <div>
                                    <h3 class="text-sm font-black">Solicitud registrada</h3>
                                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                        Ya contamos con tus datos de inscripción.
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-300 text-sm font-bold dark:border-slate-700">
                                    2
                                </span>
                                <div>
                                    <h3 class="text-sm font-black">Completar el pago</h3>
                                    <p class="mt-1 text-xs leading-6 text-slate-500 dark:text-slate-400">
                                        Consultá tu orden y completá el pago dentro del plazo establecido.
                                    </p>

                                    @if($inscripcion->expira_en)
                                        <p class="mt-2 text-xs font-bold">
                                            Fecha límite: {{ $inscripcion->expira_en->format('d/m/Y - h:i A') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                        @elseif($estado === 'confirmada_prueba')

                            <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">
                                Esta inscripción está confirmada únicamente en el entorno de pruebas. No debe interpretarse como un comprobante de pago real.
                            </p>

                        @elseif($estado === 'revision_cupo')

                            <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">
                                La solicitud requiere una revisión adicional relacionada con la disponibilidad de cupos. Consultá nuevamente su estado antes de considerar confirmada tu participación.
                            </p>

                        @elseif($estado === 'pendiente_pago' && $plazoVencido)

                            <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">
                                El plazo de pago registrado ya transcurrió. Podés consultar la orden relacionada para revisar su estado. No se debe asumir que el cupo continúa reservado.
                            </p>

                        @elseif(in_array($estado, ['vencida', 'cancelada'], true))

                            <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">
                                Esta inscripción ya no está activa. Podés consultar la actividad para conocer si existen nuevas oportunidades de participación.
                            </p>

                        @else

                            <p class="text-sm leading-7 text-slate-600 dark:text-slate-300">
                                El sistema muestra el último estado registrado de tu inscripción. Consultá nuevamente cuando necesités conocer su situación.
                            </p>

                        @endif

                    </div>

                    {{-- ACCESO A ORDEN --}}
                    @if($orden)
                        <div class="sidan-no-print mt-7 border-t border-slate-200 pt-6 dark:border-white/10">
                            <a href="{{ route('sidan.ordenes.show', $orden->id_orden) }}"
                               class="inline-flex items-center gap-2 rounded-xl bg-sidan-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-sidan-600 dark:bg-sidan-500 dark:hover:bg-sidan-400">
                                {{ $puedeContinuarPago ? 'Continuar al pago' : 'Consultar orden relacionada' }}
                                <span>→</span>
                            </a>
                        </div>
                    @endif

                </section>

                {{-- ACCIONES --}}
                <section class="sidan-no-print rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 dark:border-white/10 dark:bg-[#0f172a]">

                    <h2 class="text-xl font-black">Administrar mi inscripción</h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Consultá tus inscripciones o conservá una copia impresa de esta información.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">

                        @if($esUsuario)
                            <a href="{{ route('user.inscripciones.index') }}"
                               class="inline-flex items-center justify-center gap-2 rounded-xl bg-sidan-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-sidan-600 dark:bg-sidan-500 dark:hover:bg-sidan-400">
                                Mis inscripciones
                                <span>→</span>
                            </a>
                        @endif

                        <button type="button"
                            onclick="window.print()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v5a2 2 0 0 1-2 2h-2"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 14h12v7H6z"/>
                            </svg>
                            Imprimir resumen
                        </button>

                    </div>

                    <p class="mt-4 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        El resumen impreso muestra el estado de la inscripción y no sustituye un comprobante de pago.
                    </p>
                </section>

            </div>

            {{-- COLUMNA DERECHA --}}
            <aside class="min-w-0 space-y-6 lg:col-span-5">

                {{-- TARJETA DE ACTIVIDAD --}}
                <section class="sidan-panel overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

                    <div class="border-b border-slate-100 p-6 dark:border-white/10">
                        <span class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
                            Actividad seleccionada
                        </span>

                        <h2 class="mt-3 break-words text-2xl font-black tracking-tight">
                            {{ $actividad?->nombre ?? 'Actividad no disponible' }}
                        </h2>

                        @if($actividad?->categoria?->nombre)
                            <span class="mt-3 inline-flex rounded-full border border-slate-200 px-3 py-1.5 text-xs font-bold dark:border-slate-700">
                                {{ $actividad->categoria->nombre }}
                            </span>
                        @endif
                    </div>

                    <div class="space-y-6 p-6">

                        {{-- FECHA --}}
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                                    <path d="M7 3v4m10-4v4M3 10h18"/>
                                </svg>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Inicio de la actividad
                                </p>
                                <p class="mt-1 text-sm font-bold">
                                    {{ $actividad?->realizacion_desde?->format('d/m/Y - h:i A') ?? 'Por definir' }}
                                </p>
                            </div>
                        </div>

                        {{-- FINALIZACIÓN --}}
                        @if($actividad?->realizacion_hasta)
                            <div class="flex gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Finalización
                                    </p>
                                    <p class="mt-1 text-sm font-bold">
                                        {{ $actividad->realizacion_hasta->format('d/m/Y - h:i A') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- UBICACIÓN --}}
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                    <circle cx="12" cy="10" r="2.5"/>
                                </svg>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Ubicación
                                </p>
                                <p class="mt-1 text-sm font-bold">
                                    {{ $ubicacion }}
                                </p>
                            </div>
                        </div>

                        {{-- PARTICIPACIÓN --}}
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <circle cx="9" cy="8" r="3"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 20v-2a6 6 0 0 1 12 0v2m2-15a3 3 0 0 1 0 6m1 3a5 5 0 0 1 3 5v1"/>
                                </svg>
                            </div>

                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Tipo de inscripción
                                </p>
                                <p class="mt-1 text-sm font-bold">
                                    {{ $orden ? 'Inscripción de pago' : 'Inscripción gratuita' }}
                                </p>
                            </div>
                        </div>

                        {{-- IMPORTE --}}
                        <div class="flex items-center justify-between gap-4 rounded-2xl bg-sidan-900 p-5 text-white dark:bg-sidan-900 dark:text-white">
                            <div>
                                <p class="text-xs font-semibold opacity-70">
                                    Importe registrado
                                </p>
                                <p class="mt-1 text-[11px] opacity-60">
                                    {{ $orden ? 'Total de la orden' : 'Sin costo' }}
                                </p>
                            </div>

                            <strong class="text-3xl font-black">
                                ${{ number_format((float) ($orden?->total ?? 0), 2) }}
                            </strong>
                        </div>

                        @if($actividad?->slug)
                            <a href="{{ route('activities.show', $actividad->slug) }}"
                               class="sidan-no-print flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">
                                Ver detalles de la actividad
                                <span>↗</span>
                            </a>
                        @endif

                    </div>
                </section>

                {{-- IDENTIFICACIÓN --}}
                <section class="sidan-panel rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                    <h3 class="text-lg font-black">Referencia del registro</h3>
                    <p class="mt-2 text-xs leading-6 text-slate-500 dark:text-slate-400">
                        Esta referencia permite distinguir tu inscripción de otras solicitudes dentro de SIDAN.
                    </p>

                    <div class="mt-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center dark:border-slate-700 dark:bg-sidan-950">
                        <span class="break-all font-mono text-base font-black tracking-wide sm:text-lg">
                            {{ $referencia }}
                        </span>
                    </div>

                    <p class="mt-4 text-center text-xs text-slate-500 dark:text-slate-400">
                        Registro generado por SIDAN
                    </p>
                </section>

                {{-- NOTA DE CONSULTA --}}
                <section class="sidan-panel rounded-3xl border border-slate-200 p-6 dark:border-white/10">
                    <h3 class="text-sm font-black">
                        Información importante
                    </h3>

                    <p class="mt-3 text-xs leading-6 text-slate-500 dark:text-slate-400">
                        El estado mostrado corresponde a los datos registrados en el sistema al momento de consultar esta página.
                    </p>

                    <p class="mt-3 text-xs leading-6 text-slate-500 dark:text-slate-400">
                        Las fechas, horarios y demás condiciones de la actividad pueden ser actualizados por la organización.
                    </p>
                </section>

            </aside>
        </div>

        {{-- NAVEGACIÓN INFERIOR --}}
        <div class="sidan-no-print mt-10 flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 pt-7 dark:border-white/10">

            <a href="{{ route('activities.index') }}"
               class="inline-flex items-center gap-2 text-sm font-bold transition hover:text-sidan-600 dark:hover:text-sidan-400">
                <span>←</span>
                Regresar a explorar actividades
            </a>

            <a href="{{ route('welcome') }}"
               class="text-sm font-semibold text-slate-500 underline underline-offset-4 transition hover:text-sidan-600 dark:hover:text-sidan-400">
                Volver al inicio
            </a>
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="sidan-no-print border-t border-slate-200 bg-white dark:border-white/10 dark:bg-sidan-950">
        <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8">

            <div class="grid gap-9 md:grid-cols-3">

                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 font-black text-white">
                            S
                        </div>
                        <span class="text-xl font-black tracking-tight">SIDAN</span>
                    </div>

                    <p class="mt-4 max-w-sm text-xs leading-6 text-slate-500 dark:text-slate-400">
                        Sistema Integrado de Datos y Analítica de Negocios.
                        Plataforma para la administración de actividades, participantes e inscripciones.
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-black">Navegación</h4>

                    <div class="mt-4 flex flex-col gap-3 text-xs font-medium text-slate-500 dark:text-slate-400">
                        <a href="{{ route('welcome') }}" class="hover:underline">
                            Inicio
                        </a>
                        <a href="{{ route('activities.index') }}" class="hover:underline">
                            Explorar actividades
                        </a>

                        @if($esUsuario)
                            <a href="{{ route('user.inscripciones.index') }}" class="hover:underline">
                                Mis inscripciones
                            </a>
                            <a href="{{ route('user.ordenes.index') }}" class="hover:underline">
                                Mis órdenes
                            </a>
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-black">Sobre tu inscripción</h4>

                    <p class="mt-4 text-xs leading-6 text-slate-500 dark:text-slate-400">
                        Consultá periódicamente el estado de tu inscripción y verificá las indicaciones correspondientes a la actividad.
                    </p>

                    <p class="mt-3 text-xs leading-6 text-slate-500 dark:text-slate-400">
                        Las inscripciones de visitantes sin cuenta se pueden consultar mientras se conserve la sesión autorizada del navegador.
                    </p>
                </div>

            </div>

            <div class="mt-10 flex flex-col justify-between gap-3 border-t border-slate-200 pt-6 text-xs text-slate-500 sm:flex-row dark:border-white/10 dark:text-slate-400">
                <span>© {{ now()->year }} SIDAN. Todos los derechos reservados.</span>
                <span>Sistema de gestión de inscripciones</span>
            </div>

        </div>
    </footer>

</div>
@endsection
