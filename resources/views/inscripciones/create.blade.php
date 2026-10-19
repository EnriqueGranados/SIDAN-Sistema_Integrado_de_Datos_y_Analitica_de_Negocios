
@extends('layouts.publico-con-navbar')

@section('title', 'Inscripción | ' . $actividad->nombre . ' - SIDAN')

@section('portal-content')
@php
    // Información visual de la actividad.
    $conPago = $actividad->participacionConPago();

    $resumenActividad = trim(strip_tags((string) (
        $actividad->resumen ?: $actividad->descripcion
    )));

    $lugarActividad = trim((string) (
        $actividad->ubicacion_externa ?:
        ($actividad->espacio?->nombre ?? '')
    ));

    $sesionesResumen = $actividad->sesiones()
        ->orderBy('fecha_inicio')
        ->limit(3)
        ->get();
@endphp

<div class="flex min-h-screen flex-col bg-[#f6f8fb] text-slate-900 transition-colors duration-300 dark:bg-sidan-950 dark:text-slate-100">

{{-- PRESENTACIÓN DE LA ACTIVIDAD --}}
    <section class="relative isolate overflow-hidden bg-sidan-900 text-white">
        <div class="pointer-events-none absolute -right-24 -top-40 h-96 w-96 rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute -right-10 -top-24 h-72 w-72 rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/2 h-px w-96 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

        <div class="relative mx-auto max-w-7xl px-5 py-12 sm:py-16 lg:px-8">
            <div class="mb-9 flex flex-wrap items-center gap-2 text-xs font-medium text-zinc-400">
                <a href="{{ route('welcome') }}" class="hover:text-white">Inicio</a>
                <span>/</span>
                <a href="{{ route('activities.index') }}" class="hover:text-white">Actividades</a>
                <span>/</span>
                <span class="text-white">Inscripción</span>
            </div>

            <div class="grid items-end gap-8 lg:grid-cols-12">
                <div class="lg:col-span-9">
                    <div class="mb-5 flex flex-wrap items-center gap-3">
                        <span class="rounded-full border border-white/25 bg-white/10 px-4 py-1.5 text-[11px] font-bold uppercase tracking-widest">
                            Formulario de inscripción
                        </span>

                        @if($actividad->categoria?->nombre)
                            <span class="text-xs font-medium text-zinc-400">
                                {{ $actividad->categoria->nombre }}
                            </span>
                        @endif
                    </div>

                    <h1 class="max-w-4xl break-words text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                        Inscribite a<br>
                        <span class="text-sidan-400">{{ $actividad->nombre }}</span>
                    </h1>

                    @if($resumenActividad !== '')
                        <p class="mt-6 max-w-2xl text-sm leading-7 text-zinc-300 sm:text-base">
                            {{ \Illuminate\Support\Str::limit($resumenActividad, 260) }}
                        </p>
                    @else
                        <p class="mt-6 max-w-2xl text-sm leading-7 text-zinc-300 sm:text-base">
                            Completá tus datos para solicitar tu inscripción y participar en esta actividad.
                        </p>
                    @endif

                    <a href="{{ route('activities.show', $actividad->slug) }}"
                       class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-white underline decoration-zinc-500 underline-offset-4 transition hover:decoration-white">
                        Ver información completa de la actividad
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>

                <div class="lg:col-span-3 lg:text-right">
                    <p class="text-xs font-bold uppercase tracking-widest text-zinc-400">
                        Tipo de inscripción
                    </p>

                    <p class="mt-2 text-3xl font-black tracking-tight">
                        {{ $conPago ? 'De pago' : 'Gratuita' }}
                    </p>

                    <p class="mt-2 text-xs leading-5 text-zinc-400">
                        {{ $conPago
                            ? 'El pago se gestiona después de registrar tus datos.'
                            : 'No requiere pago de inscripción.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="mx-auto w-full max-w-7xl flex-1 px-5 py-10 lg:px-8 lg:py-14">

        <div class="grid items-start gap-8 lg:grid-cols-12">

            {{-- COLUMNA IZQUIERDA: FORMULARIO --}}
            <section class="min-w-0 lg:col-span-7">

                <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <span class="text-xs font-black uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                            Registro de participante
                        </span>

                        <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">
                            Tus datos personales
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                            Completá la información necesaria para continuar con tu inscripción.
                        </p>
                    </div>

                    <span class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-bold dark:border-zinc-700">
                        Paso 01
                    </span>
                </div>

                {{-- ERRORES DE VALIDACIÓN --}}
                @if($errors->any())
                    <div role="alert" class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-red-800 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-sm font-bold text-white dark:bg-red-500 dark:text-white">!</span>
                            <h3 class="font-bold">Revisá los siguientes datos</h3>
                        </div>

                        <ul class="list-inside list-disc space-y-1 text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('sidan.inscripciones.store', $actividad->slug) }}"
                    class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]"
                >
                    @csrf

                    <div class="border-b border-zinc-100 px-6 py-6 sm:px-8 dark:border-white/10">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-zinc-900 dark:bg-white/10 dark:text-white">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
                                    <circle cx="12" cy="8" r="4"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                                </svg>
                            </div>

                            <div>
                                <h3 class="text-base font-black">Información del participante</h3>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    Los campos marcados con * son obligatorios.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 px-6 py-7 sm:px-8">

                        {{-- NOMBRE --}}
                        <div>
                            <label for="nombre" class="mb-2 block text-sm font-bold">
                                Nombre completo <span class="text-zinc-500">*</span>
                            </label>

                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                value="{{ $usuarioAutenticado ? $nombre : old('nombre', $nombre) }}"
                                placeholder="Ingresá tu nombre completo"
                                maxlength="200"
                                autocomplete="name"
                                required
                                @if($usuarioAutenticado) readonly aria-readonly="true" @endif
                                class="block w-full rounded-xl border px-4 py-3.5 text-sm outline-none transition {{ $usuarioAutenticado
                                    ? 'cursor-default border-zinc-200 bg-zinc-100 text-zinc-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-zinc-200'
                                    : 'border-zinc-300 bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/15 dark:border-white/10 dark:bg-sidan-950 dark:text-white dark:focus:border-sidan-400 dark:focus:ring-sidan-400/20' }}"
                            >

                            <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                @if($usuarioAutenticado)
                                    Este nombre se obtiene de tu cuenta SIDAN. Podés actualizarlo desde
                                    <a href="{{ route('profile.edit') }}" class="font-semibold text-sidan-600 underline underline-offset-2 hover:text-sidan-700 dark:text-sidan-400 dark:hover:text-sidan-300">Mi perfil</a>.
                                @else
                                    Utilizá tu nombre real para identificar correctamente tu inscripción.
                                @endif
                            </p>
                        </div>

                        {{-- CORREO --}}
                        <div>
                            <label for="correo" class="mb-2 block text-sm font-bold">
                                Correo electrónico <span class="text-zinc-500">*</span>
                            </label>

                            <input
                                type="email"
                                id="correo"
                                name="correo"
                                value="{{ $usuarioAutenticado ? $correo : old('correo', $correo) }}"
                                placeholder="nombre@ejemplo.com"
                                maxlength="150"
                                autocomplete="email"
                                required
                                @if($usuarioAutenticado) readonly aria-readonly="true" @endif
                                class="block w-full rounded-xl border px-4 py-3.5 text-sm outline-none transition {{ $usuarioAutenticado
                                    ? 'cursor-default border-zinc-200 bg-zinc-100 text-zinc-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-zinc-200'
                                    : 'border-zinc-300 bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/15 dark:border-white/10 dark:bg-sidan-950 dark:text-white dark:focus:border-sidan-400 dark:focus:ring-sidan-400/20' }}"
                            >

                            @if($usuarioAutenticado)
                                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    Utilizaremos el correo vinculado a tu cuenta SIDAN.
                                </p>
                            @endif
                        </div>

                        {{-- TELÉFONO --}}
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <label for="telefono" class="block text-sm font-bold">
                                    Número de teléfono
                                </label>

                                <span class="rounded-md bg-zinc-100 px-2 py-1 text-[10px] font-semibold text-zinc-500 dark:bg-white/10 dark:text-zinc-300">
                                    {{ $telefonoBloqueado ? 'REGISTRADO EN TU PERFIL' : 'OPCIONAL' }}
                                </span>
                            </div>

                            <input
                                type="tel"
                                id="telefono"
                                name="telefono"
                                value="{{ $telefonoBloqueado ? $telefonoRegistrado : old('telefono', $telefonoRegistrado) }}"
                                placeholder="Ej. +503 7000 0000"
                                maxlength="25"
                                autocomplete="tel"
                                @if($telefonoBloqueado) readonly aria-readonly="true" @endif
                                class="block w-full rounded-xl border px-4 py-3.5 text-sm outline-none transition {{ $telefonoBloqueado
                                    ? 'cursor-default border-zinc-200 bg-zinc-100 text-zinc-700 dark:border-white/10 dark:bg-white/[0.06] dark:text-zinc-200'
                                    : 'border-zinc-300 bg-white text-zinc-900 placeholder:text-zinc-400 focus:border-sidan-500 focus:ring-2 focus:ring-sidan-500/15 dark:border-white/10 dark:bg-sidan-950 dark:text-white dark:focus:border-sidan-400 dark:focus:ring-sidan-400/20' }}"
                            >

                            <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                @if($telefonoBloqueado)
                                    El teléfono se obtiene de tu perfil y no puede cambiarse desde esta inscripción.
                                @else
                                    @if($usuarioAutenticado)
                                        Tu cuenta no tiene teléfono registrado. Podés agregar un número de contacto para esta inscripción.
                                    @else
                                        Podés incluir el código de país.
                                    @endif
                                @endif
                            </p>
                        </div>

                        {{-- PRECIO --}}
                        @if($conPago)
                            <div class="flex items-center justify-between gap-4 rounded-2xl bg-zinc-100 p-5 dark:bg-white/10">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                                        Precio de inscripción
                                    </p>
                                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                        El pago se realizará en el siguiente proceso.
                                    </p>
                                </div>

                                <strong class="shrink-0 text-2xl font-black">
                                    ${{ number_format((float) $actividad->precio_inscripcion, 2) }}
                                </strong>
                            </div>
                        @endif

                        {{-- CONFIRMACIÓN --}}
                        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-sidan-950">
                            <label for="confirmacion" class="flex cursor-pointer items-start gap-3">
                                <input
                                    type="checkbox"
                                    id="confirmacion"
                                    name="confirmacion"
                                    value="1"
                                    required
                                    {{ old('confirmacion') ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 shrink-0 cursor-pointer accent-sidan-500 dark:accent-sidan-400"
                                >

                                <span class="text-sm leading-6 text-zinc-700 dark:text-zinc-200">
                                    <strong class="font-bold">
                                        {{ $usuarioAutenticado
                                            ? 'Confirmo que los datos de mi cuenta y el teléfono de contacto son correctos.'
                                            : 'Confirmo que los datos proporcionados son correctos.' }}
                                    </strong>
                                    Entiendo que esta información será utilizada para gestionar mi inscripción a la actividad seleccionada.
                                </span>
                            </label>
                        </div>

                        {{-- ENVÍO DEL FORMULARIO --}}
                        <button
                            type="submit"
                            class="flex w-full items-center justify-center gap-3 rounded-xl bg-sidan-500 px-6 py-4 text-sm font-black text-white transition hover:bg-sidan-600 focus:outline-none focus:ring-4 focus:ring-sidan-500/20 dark:bg-sidan-500 dark:hover:bg-sidan-400 dark:focus:ring-sidan-400/20"
                        >
                            <span>
                                {{ $conPago ? 'Continuar al pago' : 'Confirmar inscripción gratuita' }}
                            </span>

                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
                            </svg>
                        </button>

                        <p class="text-center text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                            {{ $conPago
                                ? 'Al continuar, se registrará tu solicitud y accederás a la orden correspondiente.'
                                : 'Al confirmar, se procesará tu inscripción y podrás consultar su estado.' }}
                        </p>
                    </div>
                </form>

                {{-- INFORMACIÓN ADICIONAL --}}
                <div class="mt-6 flex items-start gap-3 rounded-2xl border border-zinc-200 p-5 dark:border-white/10">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-zinc-200 text-zinc-900 dark:bg-white/10 dark:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                            <circle cx="12" cy="12" r="9"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11v5m0-8h.01"/>
                        </svg>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold">Antes de continuar</h3>
                        <p class="mt-1 text-xs leading-6 text-zinc-500 dark:text-zinc-400">
                            @if($usuarioAutenticado)
                                Verificá los datos de tu cuenta y el teléfono de contacto. Para modificar la información registrada en SIDAN, ingresá a tu perfil.
                            @else
                                Verificá que tu nombre, correo y teléfono de contacto sean correctos.
                            @endif
                            La inscripción está sujeta a las condiciones, disponibilidad y período de registro establecidos para la actividad.
                        </p>
                    </div>
                </div>
            </section>

            {{-- COLUMNA DERECHA --}}
            <aside class="min-w-0 space-y-6 lg:col-span-5">

                {{-- RESUMEN DE INSCRIPCIÓN --}}
                <section class="overflow-hidden rounded-3xl border border-zinc-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">

                    <div class="border-b border-zinc-100 px-6 py-6 dark:border-white/10">
                        <span class="text-xs font-bold uppercase tracking-[0.2em] text-zinc-500 dark:text-zinc-400">
                            Tu actividad
                        </span>

                        <h2 class="mt-2 text-xl font-black tracking-tight">
                            Resumen de inscripción
                        </h2>
                    </div>

                    <div class="space-y-6 p-6">

                        <div>
                            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                ACTIVIDAD SELECCIONADA
                            </p>

                            <h3 class="mt-2 break-words text-lg font-black">
                                {{ $actividad->nombre }}
                            </h3>

                            @if($actividad->categoria?->nombre)
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $actividad->categoria->nombre }}
                                </p>
                            @endif
                        </div>

                        <div class="space-y-5 border-t border-zinc-100 pt-5 dark:border-white/10">

                            {{-- FECHA --}}
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-white/10">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                                        <path d="M7 3v4m10-4v4M3 10h18"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Fecha de realización</p>

                                    <p class="mt-1 text-sm font-bold">
                                        @if($actividad->realizacion_desde)
                                            {{ $actividad->realizacion_desde->format('d/m/Y') }}

                                            @if($actividad->realizacion_hasta && !$actividad->realizacion_desde->isSameDay($actividad->realizacion_hasta))
                                                — {{ $actividad->realizacion_hasta->format('d/m/Y') }}
                                            @endif
                                        @else
                                            Por definir
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- UBICACIÓN --}}
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-white/10">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="2.5"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Ubicación</p>
                                    <p class="mt-1 text-sm font-bold">
                                        {{ $lugarActividad !== '' ? $lugarActividad : 'Consultar detalle de la actividad' }}
                                    </p>
                                </div>
                            </div>

                            {{-- DISPONIBILIDAD --}}
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-white/10">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                        <circle cx="9" cy="8" r="3"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 20v-2a6 6 0 0 1 12 0v2m2-15a3 3 0 0 1 0 6m1 3a5 5 0 0 1 3 5v1"/>
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Capacidad de la actividad</p>

                                    <p class="mt-1 text-sm font-bold">
                                        {{ $actividad->cupo_total !== null
                                            ? $actividad->cupo_total . ' participantes'
                                            : 'Sin límite de cupo configurado' }}
                                    </p>
                                </div>
                            </div>

                            {{-- CIERRE --}}
                            @if($actividad->inscripcion_hasta)
                                <div class="flex items-start gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-white/10">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                            <circle cx="12" cy="12" r="9"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Cierre de inscripciones</p>
                                        <p class="mt-1 text-sm font-bold">
                                            {{ $actividad->inscripcion_hasta->format('d/m/Y - h:i A') }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- TOTAL --}}
                        <div class="rounded-2xl bg-sidan-900 p-5 text-white dark:bg-sidan-900 dark:text-white">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-medium opacity-70">Total de inscripción</p>
                                    <p class="mt-1 text-[11px] opacity-60">
                                        {{ $conPago ? 'Precio establecido para la actividad' : 'Sin costo de inscripción' }}
                                    </p>
                                </div>

                                <strong class="text-3xl font-black tracking-tight">
                                    ${{ number_format($conPago ? (float) $actividad->precio_inscripcion : 0, 2) }}
                                </strong>
                            </div>
                        </div>

                    </div>
                </section>

                {{-- SESIONES PROGRAMADAS --}}
                @if($sesionesResumen->isNotEmpty())
                    <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <h3 class="text-lg font-black">Sesiones programadas</h3>

                        <p class="mt-2 text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                            Algunas fechas de la programación de esta actividad.
                        </p>

                        <div class="mt-5 space-y-4">
                            @foreach($sesionesResumen as $sesion)
                                <div class="flex items-start gap-4">
                                    <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-zinc-100 text-zinc-900 dark:bg-white/10 dark:text-white">
                                        <span class="text-lg font-black leading-none">
                                            {{ $sesion->fecha_inicio?->format('d') ?? '--' }}
                                        </span>
                                        <span class="mt-1 text-[9px] font-bold uppercase">
                                            {{ $sesion->fecha_inicio?->format('M') ?? 'S/F' }}
                                        </span>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="break-words text-sm font-bold">
                                            {{ $sesion->nombre ?: 'Sesión programada' }}
                                        </p>

                                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $sesion->fecha_inicio?->format('h:i A') ?? 'Horario por definir' }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ route('activities.show', $actividad->slug) }}"
                           class="mt-5 inline-flex text-xs font-bold underline underline-offset-4 hover:opacity-70">
                            Consultar programación completa →
                        </a>
                    </section>
                @endif

                {{-- PRÓXIMOS PASOS --}}
                <section class="rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                    <h3 class="text-lg font-black">¿Qué sucede después?</h3>

                    <p class="mt-2 text-xs leading-6 text-zinc-500 dark:text-zinc-400">
                        Este es el proceso para completar tu inscripción.
                    </p>

                    <div class="mt-6 space-y-5">

                        <div class="flex gap-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sidan-500 text-xs font-black text-white dark:bg-sidan-500">
                                1
                            </span>

                            <div>
                                <h4 class="text-sm font-bold">Registrar tus datos</h4>
                                <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                                    Completá y enviá el formulario de inscripción.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-zinc-300 text-xs font-black dark:border-zinc-700">
                                2
                            </span>

                            <div>
                                <h4 class="text-sm font-bold">
                                    {{ $conPago ? 'Consultar la orden' : 'Recibir confirmación' }}
                                </h4>

                                <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                                    {{ $conPago
                                        ? 'El sistema generará una orden con el importe de inscripción.'
                                        : 'El sistema procesará tu solicitud y mostrará el estado de tu inscripción.' }}
                                </p>
                            </div>
                        </div>

                        @if($conPago)
                            <div class="flex gap-4">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-zinc-300 text-xs font-black dark:border-zinc-700">
                                    3
                                </span>

                                <div>
                                    <h4 class="text-sm font-bold">Realizar el pago</h4>
                                    <p class="mt-1 text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                                        Seguí las indicaciones de la orden para completar el pago correspondiente.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- ENLACE DE REGRESO --}}
                <a href="{{ route('activities.show', $actividad->slug) }}"
                   class="flex items-center justify-center gap-2 rounded-2xl border border-zinc-300 px-5 py-4 text-sm font-bold transition hover:bg-zinc-100 dark:border-zinc-700 dark:hover:bg-zinc-900">
                    <span>←</span>
                    Volver al detalle de la actividad
                </a>

            </aside>
        </div>
    </main>

    {{-- FOOTER --}}
    <footer class="border-t border-zinc-200 bg-white dark:border-white/10 dark:bg-sidan-950">
        <div class="mx-auto max-w-7xl px-5 py-10 lg:px-8">

            <div class="grid gap-9 md:grid-cols-3">

                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 font-black text-white dark:bg-sidan-900 dark:text-white">
                            S
                        </div>

                        <span class="text-xl font-black tracking-tight">SIDAN</span>
                    </div>

                    <p class="mt-4 max-w-sm text-xs leading-6 text-zinc-500 dark:text-zinc-400">
                        Sistema Integrado de Datos y Analítica de Negocios.
                        Plataforma para la gestión de actividades, inscripciones y procesos relacionados.
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-black">Navegación</h4>

                    <div class="mt-4 flex flex-col gap-3 text-xs font-medium text-zinc-500 dark:text-zinc-400">
                        <a href="{{ route('welcome') }}" class="hover:underline">Inicio</a>
                        <a href="{{ route('activities.index') }}" class="hover:underline">Explorar actividades</a>

                        @auth
                            <a href="{{ route('user.inscripciones.index') }}" class="hover:underline">
                                Mis inscripciones
                            </a>
                        @endauth
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-black">Información</h4>

                    <p class="mt-4 text-xs leading-6 text-zinc-500 dark:text-zinc-400">
                        Para consultar fechas, condiciones de participación y programación, revisá siempre la información de cada actividad.
                    </p>

                    <a href="{{ route('activities.show', $actividad->slug) }}"
                       class="mt-3 inline-block text-xs font-bold underline underline-offset-4">
                        Ver actividad seleccionada
                    </a>
                </div>
            </div>

            <div class="mt-10 flex flex-col justify-between gap-3 border-t border-zinc-200 pt-6 text-xs text-zinc-500 sm:flex-row dark:border-white/10 dark:text-zinc-400">
                <span>© {{ now()->year }} SIDAN. Todos los derechos reservados.</span>
                <span>Portal de inscripción a actividades</span>
            </div>
        </div>
    </footer>

</div>
@endsection
