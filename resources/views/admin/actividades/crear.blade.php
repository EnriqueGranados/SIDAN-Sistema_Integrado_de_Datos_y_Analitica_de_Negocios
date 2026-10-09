@extends('layouts.navbars')

@section('title', 'Nueva actividad')

@section('content')
<div class="w-full min-w-0 max-w-full overflow-x-hidden">
    <div class="mx-auto w-full max-w-5xl px-4 py-5 sm:px-6 sm:py-7 lg:px-8">

        {{-- ENCABEZADO --}}
        <div class="mb-6 flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                        Borrador
                    </span>

                    <span class="text-xs text-gray-500">
                        Nueva actividad
                    </span>
                </div>

                <h1 class="break-words text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Crear actividad
                </h1>

                <p class="mt-2 max-w-2xl break-words text-sm leading-6 text-gray-400">
                    Completa los pasos siguientes. Podrás modificar y ampliar la actividad antes de publicarla.
                </p>
            </div>

            <a href="{{ route('admin.actividades.index') }}" class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-gray-300 transition hover:bg-white/10 hover:text-white sm:w-auto">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>

                Volver
            </a>
        </div>

        {{-- ERRORES --}}
        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 p-4">
                <p class="font-semibold text-red-300">
                    Revisa la información ingresada.
                </p>

                <p class="mt-1 text-sm leading-5 text-red-300/80">
                    Hay uno o más campos que deben corregirse antes de guardar.
                </p>
            </div>
        @endif

        {{-- STEPPER ESCRITORIO --}}
        <div class="mb-8 hidden w-full sm:block">
            <div class="flex w-full items-center">

                @foreach ([
                    1 => 'Información',
                    2 => 'Presentación',
                    3 => 'Inscripciones',
                    4 => 'Confirmación'
                ] as $numero => $titulo)

                    <div class="flex min-w-0 items-center {{ $numero < 4 ? 'flex-1' : '' }}">
                        <div class="step-desktop flex min-w-0 shrink-0 items-center gap-3" data-step-indicator="{{ $numero }}">
                            <div class="step-circle flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-gray-700 bg-gray-900 text-sm font-bold text-gray-400 transition">
                                {{ $numero }}
                            </div>

                            <span class="step-label hidden whitespace-nowrap text-sm font-semibold text-gray-500 md:block">
                                {{ $titulo }}
                            </span>
                        </div>

                        @if ($numero < 4)
                            <div class="step-line mx-4 h-px min-w-5 flex-1 bg-gray-700 lg:mx-6"></div>
                        @endif
                    </div>

                @endforeach
            </div>
        </div>

        {{-- STEPPER MÓVIL --}}
        <div class="mb-6 sm:hidden">
            <div class="mb-3 flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p id="mobileStepNumber" class="text-xs font-semibold uppercase tracking-wide text-emerald-400">
                        Paso 1 de 4
                    </p>

                    <p id="mobileStepTitle" class="mt-1 truncate text-base font-semibold text-white">
                        Información
                    </p>
                </div>

                <span id="mobilePercentage" class="shrink-0 text-sm font-semibold text-gray-500">
                    25%
                </span>
            </div>

            <div class="flex gap-2">
                @for ($i = 1; $i <= 4; $i++)
                    <div class="mobile-step-bar h-1.5 flex-1 rounded-full bg-gray-800 transition" data-mobile-step="{{ $i }}"></div>
                @endfor
            </div>
        </div>

        <form id="formActividad" action="{{ route('admin.actividades.store') }}" method="POST" enctype="multipart/form-data" class="w-full min-w-0">
            @csrf

            {{-- ===================================================== --}}
            {{-- PASO 1: INFORMACIÓN --}}
            {{-- ===================================================== --}}
            <section class="form-step space-y-5" data-step="1">
                <div class="rounded-2xl border border-white/10 bg-white/[0.03]">
                    <div class="border-b border-white/10 px-4 py-5 sm:px-6">
                        <h2 class="text-lg font-semibold text-white">
                            Información de la actividad
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-gray-500">
                            Define los datos generales, visibilidad, ubicación y período de realización.
                        </p>
                    </div>

                    <div class="space-y-6 p-4 sm:p-6">

                        {{-- NOMBRE --}}
                        <div class="min-w-0">
                            <div class="mb-2 flex items-center gap-2">
                                <label for="nombre" class="text-sm font-medium text-gray-300">
                                    Nombre de la actividad
                                    <span class="text-red-400">*</span>
                                </label>

                                <button type="button"
                                    class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400 transition hover:border-emerald-500/50 hover:text-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/30"
                                    data-help="Escribe el nombre con el que las personas reconocerán la actividad. Por ejemplo: Congreso de Innovación y Tecnología 2026."
                                    aria-label="Información sobre el nombre">
                                    i
                                </button>
                            </div>

                            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="180" required
                                placeholder="Ej. Congreso de Innovación y Tecnología 2026"
                                class="block w-full min-w-0 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-white outline-none transition placeholder:text-gray-600 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 sm:text-sm">

                            @error('nombre')
                                <p class="mt-2 break-words text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- CATEGORÍA SOLO SI EXISTE --}}
                        @if ($categorias->isNotEmpty())
                            <div class="min-w-0">
                                <div class="mb-2 flex items-center gap-2">
                                    <label for="id_categoria" class="text-sm font-medium text-gray-300">
                                        ¿Qué tipo de actividad es?
                                    </label>

                                    <button type="button"
                                        class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                        data-help="Selecciona la categoría que mejor describa la actividad. Esto ayuda a organizarla y facilita que las personas puedan encontrarla."
                                        aria-label="Información sobre categorías">
                                        i
                                    </button>
                                </div>

                                <select id="id_categoria" name="id_categoria"
                                    class="block w-full min-w-0 rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-base text-white outline-none focus:border-emerald-500 sm:text-sm">

                                    <option value="">Seleccionar categoría</option>

                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id_categoria }}" @selected(old('id_categoria') == $categoria->id_categoria)>
                                            {{ $categoria->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- RESUMEN --}}
                        <div class="min-w-0">
                            <div class="mb-2 flex items-center gap-2">
                                <label for="resumen" class="text-sm font-medium text-gray-300">
                                    Resumen breve
                                </label>

                                <button type="button"
                                    class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                    data-help="Escribe una explicación corta de la actividad. Este texto puede aparecer en las tarjetas y listados que verán los usuarios."
                                    aria-label="Información sobre el resumen">
                                    i
                                </button>
                            </div>

                            <textarea id="resumen" name="resumen" rows="3" maxlength="300"
                                placeholder="Ej. Una jornada dedicada a innovación, tecnología y nuevas tendencias..."
                                class="block w-full min-w-0 resize-y rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base leading-6 text-white outline-none placeholder:text-gray-600 focus:border-emerald-500 sm:text-sm">{{ old('resumen') }}</textarea>

                            <div class="mt-2 flex justify-end">
                                <span class="text-xs text-gray-600">
                                    Máximo 300 caracteres
                                </span>
                            </div>
                        </div>

                        {{-- DESCRIPCIÓN --}}
                        <div class="min-w-0">
                            <div class="mb-2 flex items-center gap-2">
                                <label for="descripcion" class="text-sm font-medium text-gray-300">
                                    Descripción completa
                                </label>

                                <button type="button"
                                    class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                    data-help="Explica con más detalle de qué trata la actividad, qué podrán encontrar los participantes y cualquier información relevante."
                                    aria-label="Información sobre la descripción">
                                    i
                                </button>
                            </div>

                            <textarea id="descripcion" name="descripcion" rows="7"
                                placeholder="Describe la actividad, sus objetivos y lo que podrán encontrar los participantes..."
                                class="block w-full min-w-0 resize-y rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base leading-6 text-white outline-none placeholder:text-gray-600 focus:border-emerald-500 sm:text-sm">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>
                </div>

                    {{-- VISIBILIDAD --}}
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03]">
                        <div class="border-b border-white/10 px-4 py-5 sm:px-6">
                            <h3 class="font-semibold text-white">
                                Visibilidad
                            </h3>

                            <p class="mt-1 text-sm text-gray-500">
                                Decide quién podrá encontrar la actividad y durante qué período.
                            </p>
                        </div>

                        <div class="space-y-5 p-4 sm:p-6">

                            <div>
                                <div class="mb-2 flex items-center gap-2">
                                    <label for="visibilidad" class="text-sm font-medium text-gray-300">
                                        ¿Quién podrá ver esta actividad?
                                    </label>

                                    <button type="button"
                                        class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                        data-help="Pública: podrá encontrarse normalmente. No listada: estará disponible principalmente mediante su enlace. Interna: estará orientada al uso dentro del sistema."
                                        aria-label="Información sobre visibilidad">
                                        i
                                    </button>
                                </div>

                                <select id="visibilidad" name="visibilidad" required
                                    class="block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-base text-white outline-none focus:border-emerald-500 sm:text-sm">

                                    <option value="publica" @selected(old('visibilidad', 'publica') === 'publica')>
                                        Pública
                                    </option>

                                    <option value="no_listada" @selected(old('visibilidad') === 'no_listada')>
                                        No listada
                                    </option>

                                    <option value="interna" @selected(old('visibilidad') === 'interna')>
                                        Interna
                                    </option>
                                </select>
                            </div>

                            <div>
                                <div class="mb-3 flex items-center gap-2">
                                    <p class="text-sm font-medium text-gray-300">
                                        ¿Cuándo quieres que pueda mostrarse?
                                    </p>

                                    <button type="button"
                                        class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                        data-help="Estas fechas controlan cuándo podrá mostrarse públicamente después de ser aprobada y publicada. No son necesariamente las fechas en que se realizará la actividad."
                                        aria-label="Información sobre fechas de visibilidad">
                                        i
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label for="visible_desde" class="mb-2 block text-xs font-medium text-gray-500">
                                            Mostrar desde
                                        </label>

                                        <input type="datetime-local" id="visible_desde" name="visible_desde"
                                            value="{{ old('visible_desde') }}"
                                            class="future-date block w-full min-w-0 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-gray-200 outline-none focus:border-emerald-500 sm:text-sm">

                                        @error('visible_desde')
                                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="visible_hasta" class="mb-2 block text-xs font-medium text-gray-500">
                                            Mostrar hasta
                                        </label>

                                        <input type="datetime-local" id="visible_hasta" name="visible_hasta"
                                            value="{{ old('visible_hasta') }}"
                                            class="future-date block w-full min-w-0 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-gray-200 outline-none focus:border-emerald-500 sm:text-sm">

                                        @error('visible_hasta')
                                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                {{-- UBICACIÓN GENERAL --}}
                <div class="rounded-2xl border border-white/10 bg-white/[0.03]">
                    <div class="border-b border-white/10 px-4 py-5 sm:px-6"><h2 class="text-lg font-semibold text-white">Ubicación general</h2><p class="mt-1 text-sm text-gray-500">Define dónde se realizará principalmente la actividad. Las sesiones podrán precisar esta ubicación después.</p></div>
                    <div class="space-y-4 p-4 sm:p-6">
                        <div class="flex flex-wrap gap-4">
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-300"><input type="radio" name="tipo_ubicacion" value="registrada" class="tipo-ubicacion"> Espacio registrado</label>
                            <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-300"><input type="radio" name="tipo_ubicacion" value="externa" class="tipo-ubicacion"> Otro / lugar externo</label>
                        </div>
                        <div id="bloqueEspacioRegistrado" class="hidden"><label for="id_espacio" class="mb-2 block text-sm font-medium text-gray-300">Espacio</label><select id="id_espacio" name="id_espacio" class="block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-white outline-none focus:border-emerald-500"><option value="">Seleccionar espacio</option>@foreach($espacios as $espacio)<option value="{{ $espacio->id_espacio }}" data-capacidad="{{ $espacio->capacidad }}" @selected(old('id_espacio') == $espacio->id_espacio)>{{ $espacio->nombre }}@if($espacio->contenedor) — dentro de {{ $espacio->contenedor->nombre }}@endif</option>@endforeach</select><p id="informacionCapacidadEspacio" class="mt-2 hidden text-xs text-cyan-300">Capacidad registrada: <span id="capacidadEspacioTexto"></span></p></div>
                        <div id="bloqueUbicacionExterna" class="hidden"><label for="ubicacion_externa" class="mb-2 block text-sm font-medium text-gray-300">Lugar externo</label><input id="ubicacion_externa" name="ubicacion_externa" value="{{ old('ubicacion_externa') }}" maxlength="300" placeholder="Ej. Hotel, auditorio externo o dirección" class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none focus:border-emerald-500"></div>
                    </div>
                </div>

                {{-- PERÍODO REAL DE LA ACTIVIDAD --}}
                <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/[0.04]">
                    <div class="border-b border-cyan-500/10 px-4 py-5 sm:px-6">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg font-semibold text-white">
                                        Período de realización
                                    </h2>

                                    <span class="rounded-full border border-white/10 bg-white/5 px-2 py-0.5 text-[10px] font-medium text-gray-400">
                                        Opcional
                                    </span>
                                </div>

                                <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-400">
                                    Indica cuándo de que fecha a que fecha se llevara a cabo esta actividad.
                                </p>
                            </div>

                            <button type="button"
                                class="help-button flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-white/15 text-xs font-bold text-gray-400 transition hover:border-cyan-500/50 hover:text-cyan-300"
                                data-help="Define el período general en que sucede la actividad. Más adelante, si agregas días, horarios, turnos, ponencias o talleres, esas fechas deberán quedar dentro de este rango."
                                aria-label="Información sobre período de realización">
                                i
                            </button>
                        </div>
                    </div>

                    <div class="space-y-5 p-4 sm:p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label for="realizacion_desde" class="mb-2 block text-sm font-medium text-gray-300">
                                    La actividad inicia
                                </label>

                                <input type="datetime-local" id="realizacion_desde" name="realizacion_desde"
                                    value="{{ old('realizacion_desde') }}"
                                    class="future-date block w-full min-w-0 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-gray-200 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/10 sm:text-sm">

                                @error('realizacion_desde')
                                    <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="realizacion_hasta" class="mb-2 block text-sm font-medium text-gray-300">
                                    La actividad finaliza
                                </label>

                                <input type="datetime-local" id="realizacion_hasta" name="realizacion_hasta"
                                    value="{{ old('realizacion_hasta') }}"
                                    class="future-date block w-full min-w-0 rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-gray-200 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/10 sm:text-sm">

                                @error('realizacion_hasta')
                                    <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div id="mensajeRealizacionIncompleta" class="hidden rounded-xl border border-amber-500/20 bg-amber-500/[0.05] p-3">
                            <p class="text-xs leading-5 text-amber-300">
                                Si defines un período de realización, debes completar tanto la fecha de inicio como la fecha de finalización.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================================================== --}}
            {{-- PASO 2: PRESENTACIÓN --}}
            {{-- ===================================================== --}}
            <section class="form-step hidden" data-step="2">
                <div class="space-y-5">

                    {{-- PORTADA --}}
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03]">
                        <div class="border-b border-white/10 px-4 py-5 sm:px-6">
                            <h2 class="text-lg font-semibold text-white">
                                Presentación de la actividad
                            </h2>

                            <p class="mt-1 text-sm leading-6 text-gray-500">
                                Personaliza cómo se mostrará la actividad.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 p-4 sm:p-6 lg:grid-cols-2">

                            {{-- FOTO --}}
                            <div class="min-w-0">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-gray-300">
                                        Fotografía de portada
                                    </span>

                                    <span class="rounded-full bg-white/5 px-2 py-0.5 text-[10px] text-gray-500">
                                        Opcional
                                    </span>

                                    <button type="button"
                                        class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                        data-help="Selecciona una imagen que represente la actividad. Si no agregas ninguna, posteriormente podrá mostrarse una imagen predeterminada."
                                        aria-label="Información sobre portada">
                                        i
                                    </button>
                                </div>

                                <button type="button" id="abrirPreviewPortada"
                                    class="group relative flex aspect-video w-full min-w-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-white/10 bg-black/20 text-left">

                                    <img id="portadaPreview" src="" alt="Vista previa de la portada" class="hidden h-full w-full object-cover">

                                    <div id="portadaPlaceholder" class="flex flex-col items-center px-6 py-8 text-center">
                                        <svg class="mb-3 h-8 w-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>

                                        <p class="text-sm font-medium text-gray-400">
                                            Sin fotografía
                                        </p>

                                        <p class="mt-1 text-xs text-gray-600">
                                            Puedes crear la actividad sin imagen.
                                        </p>
                                    </div>

                                    <div id="indicadorAmpliar" class="absolute inset-x-0 bottom-0 hidden bg-black/60 px-3 py-2 text-center text-xs font-medium text-white opacity-0 backdrop-blur-sm transition group-hover:opacity-100">
                                        Clic para ver fotografía completa
                                    </div>
                                </button>

                                <label for="portada"
                                    class="mt-3 flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-gray-300 transition hover:border-emerald-500/30 hover:bg-emerald-500/5 hover:text-emerald-400">

                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m0 0L8 8m4-4l4 4" />
                                    </svg>

                                    <span id="portadaNombre" class="min-w-0 truncate">
                                        Seleccionar fotografía
                                    </span>
                                </label>

                                <input type="file" id="portada" name="portada" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="sr-only">

                                <p class="mt-2 text-center text-xs text-gray-600">
                                    JPG, PNG o WEBP · Máximo 5 MB
                                </p>

                                <button type="button" id="quitarPortada"
                                    class="mt-3 hidden w-full items-center justify-center rounded-xl border border-red-500/20 bg-red-500/5 px-4 py-2.5 text-sm font-medium text-red-400 transition hover:bg-red-500/10">
                                    Quitar fotografía
                                </button>
                            </div>

                            <div class="min-w-0 space-y-5">

                                {{-- ALT --}}
                                <div>
                                    <div class="mb-2 flex items-center gap-2">
                                        <label for="texto_alternativo_portada" class="text-sm font-medium text-gray-300">
                                            Descripción de la fotografía
                                        </label>

                                        <button type="button"
                                            class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                            data-help="Describe brevemente qué aparece en la fotografía. Esto ayuda a personas que utilizan lectores de pantalla. Si lo dejas vacío, usaremos el nombre de la actividad."
                                            aria-label="Información sobre descripción de fotografía">
                                            i
                                        </button>
                                    </div>

                                    <input type="text" id="texto_alternativo_portada" name="texto_alternativo_portada"
                                        value="{{ old('texto_alternativo_portada') }}" maxlength="180"
                                        placeholder="Ej. Estudiantes participando en el evento"
                                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-white outline-none placeholder:text-gray-600 focus:border-emerald-500 sm:text-sm">
                                </div>

                                {{-- DESTACADA --}}
                                <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                                    <input type="hidden" name="destacada" value="0">

                                    <label class="flex cursor-pointer items-start gap-3">
                                        <input type="checkbox" id="destacada" name="destacada" value="1"
                                            @checked(old('destacada') == '1')
                                            class="mt-1 h-4 w-4 shrink-0 rounded border-gray-600 bg-gray-800 text-emerald-500 focus:ring-emerald-500">

                                        <span class="min-w-0">
                                            <span class="flex flex-wrap items-center gap-2 text-sm font-medium text-gray-300">
                                                Mostrar como actividad destacada

                                                <button type="button"
                                                    class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                                    data-help="Las actividades destacadas podrán recibir mayor relevancia visual en la página pública una vez que estén publicadas."
                                                    aria-label="Información sobre actividad destacada">
                                                    i
                                                </button>
                                            </span>

                                            <span class="mt-1 block break-words text-xs leading-5 text-gray-600">
                                                Úsalo cuando quieras darle mayor relevancia visual.
                                            </span>
                                        </span>
                                    </label>
                                </div>

                                {{-- PRIORIDAD --}}
                                <div>
                                    <div class="mb-2 flex items-center gap-2">
                                        <label for="prioridad" class="text-sm font-medium text-gray-300">
                                            Prioridad de visualización
                                        </label>

                                        <button type="button"
                                            class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                            data-help="Indica qué tanta prioridad tendrá la actividad respecto a otras. El sistema transformará automáticamente tu selección a su valor interno."
                                            aria-label="Información sobre prioridad">
                                            i
                                        </button>
                                    </div>

                                    <select id="prioridad" name="prioridad"
                                        class="block w-full rounded-xl border border-white/10 bg-[#111827] px-4 py-3 text-base text-white outline-none focus:border-emerald-500 sm:text-sm">

                                        <option value="100" @selected(old('prioridad') == 100)>
                                            Alta
                                        </option>

                                        <option value="75" @selected(old('prioridad') == 75)>
                                            Media
                                        </option>

                                        <option value="50" @selected(old('prioridad', 50) == 50)>
                                            Regular
                                        </option>

                                        <option value="25" @selected(old('prioridad') == 25)>
                                            Baja
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ETIQUETAS SOLO SI EXISTEN --}}
                    @if ($etiquetas->isNotEmpty())
                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-6">
                            <div class="mb-4 flex items-center gap-2">
                                <div>
                                    <h3 class="font-semibold text-white">
                                        Etiquetas
                                    </h3>

                                    <p class="mt-1 text-sm text-gray-500">
                                        Selecciona palabras que ayuden a describir la actividad.
                                    </p>
                                </div>

                                <button type="button"
                                    class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                    data-help="Las etiquetas permiten clasificar una actividad con varias características al mismo tiempo."
                                    aria-label="Información sobre etiquetas">
                                    i
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3">
                                @foreach ($etiquetas as $etiqueta)
                                    <label class="flex min-w-0 cursor-pointer items-center gap-3 rounded-xl border border-white/10 bg-black/10 px-3 py-3 transition hover:bg-white/[0.03]">
                                        <input type="checkbox" name="etiquetas[]" value="{{ $etiqueta->id_etiqueta }}"
                                            data-nombre-etiqueta="{{ $etiqueta->nombre }}"
                                            @checked(in_array($etiqueta->id_etiqueta, old('etiquetas', [])))
                                            class="h-4 w-4 shrink-0 rounded border-gray-600 bg-gray-800 text-emerald-500 focus:ring-emerald-500">

                                        <span class="min-w-0 break-words text-sm text-gray-400">
                                            {{ $etiqueta->nombre }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </section>

            {{-- ===================================================== --}}
            {{-- PASO 3 --}}
            {{-- ===================================================== --}}
            <section class="form-step hidden" data-step="3">
                <div class="rounded-2xl border border-white/10 bg-white/[0.03]">
                    <div class="border-b border-white/10 px-4 py-5 sm:px-6">
                        <h2 class="text-lg font-semibold text-white">
                            Inscripciones
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-gray-500">
                            Configura cómo podrán registrarse las personas interesadas.
                        </p>
                    </div>

                    <div class="space-y-6 p-4 sm:p-6">

                        <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                            <input type="hidden" name="habilita_inscripcion" value="0">

                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" id="habilita_inscripcion" name="habilita_inscripcion" value="1"
                                    @checked(old('habilita_inscripcion', '1') == '1')
                                    class="mt-1 h-5 w-5 shrink-0 rounded border-gray-600 bg-gray-800 text-emerald-500 focus:ring-emerald-500">

                                <span class="min-w-0">
                                    <span class="flex flex-wrap items-center gap-2 text-sm font-semibold text-white">
                                        ¿La actividad requiere inscripción general?

                                        <button type="button"
                                            class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                            data-help="Activa esta opción cuando las personas deban registrarse a la actividad completa. Las reservas de talleres, turnos u otras sesiones se configurarán después de forma independiente."
                                            aria-label="Información sobre inscripciones">
                                            i
                                        </button>
                                    </span>

                                    <span class="mt-1 block break-words text-xs leading-5 text-gray-500">
                                        Desactívalo para actividades abiertas que no necesitan registro.
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div id="configuracionInscripcion" class="space-y-6">

                            {{-- CUPO --}}
                            <div>
                                <div class="mb-2 flex items-center gap-2">
                                    <label for="cupo_total" class="text-sm font-medium text-gray-300">
                                        Cupo máximo
                                    </label>

                                    <button type="button"
                                        class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                        data-help="Indica el número máximo de personas que podrán inscribirse. Si no existe un límite, deja el campo vacío."
                                        aria-label="Información sobre cupo">
                                        i
                                    </button>
                                </div>

                                <input type="number" id="cupo_total" name="cupo_total" value="{{ old('cupo_total') }}" min="0"
                                    placeholder="Ej. 100"
                                    class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-white outline-none placeholder:text-gray-600 focus:border-emerald-500 sm:max-w-sm sm:text-sm">
                            </div>

                            {{-- OPCIONES --}}
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                                    <input type="hidden" name="requiere_cuenta" value="0">

                                    <label class="flex cursor-pointer items-start gap-3">
                                        <input type="checkbox" id="requiere_cuenta" name="requiere_cuenta" value="1"
                                            @checked(old('requiere_cuenta') == '1')
                                            class="mt-1 h-4 w-4 shrink-0 rounded border-gray-600 bg-gray-800 text-emerald-500 focus:ring-emerald-500">

                                        <span class="min-w-0">
                                            <span class="flex flex-wrap items-center gap-2 text-sm font-medium text-gray-300">
                                                Requerir inicio de sesión

                                                <button type="button"
                                                    class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                                    data-help="Si activas esta opción, la persona deberá tener una cuenta e iniciar sesión antes de poder inscribirse."
                                                    aria-label="Información sobre inicio de sesión">
                                                    i
                                                </button>
                                            </span>
                                        </span>
                                    </label>
                                </div>

                                <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                                    <input type="hidden" name="permite_lista_espera" value="0">

                                    <label class="flex cursor-pointer items-start gap-3">
                                        <input type="checkbox" id="permite_lista_espera" name="permite_lista_espera" value="1"
                                            @checked(old('permite_lista_espera') == '1')
                                            class="mt-1 h-4 w-4 shrink-0 rounded border-gray-600 bg-gray-800 text-emerald-500 focus:ring-emerald-500">

                                        <span class="min-w-0">
                                            <span class="flex flex-wrap items-center gap-2 text-sm font-medium text-gray-300">
                                                Permitir lista de espera

                                                <button type="button"
                                                    class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                                    data-help="Si se completa el cupo, las siguientes personas podrán registrarse como interesadas y quedar en lista de espera."
                                                    aria-label="Información sobre lista de espera">
                                                    i
                                                </button>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            {{-- FECHAS --}}
                            <div>
                                <div class="mb-3 flex items-center gap-2">
                                    <p class="text-sm font-medium text-gray-300">
                                        Período de inscripción
                                    </p>

                                    <button type="button"
                                        class="help-button flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-white/15 text-[11px] font-bold text-gray-400"
                                        data-help="Define desde qué momento se aceptarán inscripciones y cuándo dejarán de aceptarse. No podrás seleccionar fechas que ya hayan pasado."
                                        aria-label="Información sobre período de inscripción">
                                        i
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label for="inscripcion_desde" class="mb-2 block text-xs font-medium text-gray-500">
                                            Inscripciones desde
                                        </label>

                                        <input type="datetime-local" id="inscripcion_desde" name="inscripcion_desde"
                                            value="{{ old('inscripcion_desde') }}"
                                            class="future-date block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-gray-200 outline-none focus:border-emerald-500 sm:text-sm">

                                        @error('inscripcion_desde')
                                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="inscripcion_hasta" class="mb-2 block text-xs font-medium text-gray-500">
                                            Inscripciones hasta
                                        </label>

                                        <input type="datetime-local" id="inscripcion_hasta" name="inscripcion_hasta"
                                            value="{{ old('inscripcion_hasta') }}"
                                            class="future-date block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-gray-200 outline-none focus:border-emerald-500 sm:text-sm">

                                        @error('inscripcion_hasta')
                                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="mensajeSinInscripcion" class="hidden rounded-xl border border-blue-500/20 bg-blue-500/5 p-5">
                            <p class="font-medium text-blue-300">
                                Esta actividad no tendrá inscripción general.
                            </p>

                            <p class="mt-1 text-sm leading-6 text-gray-500">
                                Las personas podrán consultar la actividad sin pasar por un proceso de registro.
                                Aun así, posteriormente podrás solicitar reservas o inscripciones en sesiones específicas.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===================================================== --}}
            {{-- PASO 4: CONFIRMACIÓN --}}
            {{-- ===================================================== --}}
            <section class="form-step hidden" data-step="4">
                <div class="space-y-5">

                    <div class="rounded-2xl border border-white/10 bg-white/[0.03]">
                        <div class="border-b border-white/10 px-4 py-5 sm:px-6">
                            <h2 class="text-lg font-semibold text-white">
                                Confirma la información
                            </h2>

                            <p class="mt-1 text-sm text-gray-500">
                                Revisa cuidadosamente los datos antes de crear la actividad.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-6 p-4 sm:p-6 lg:grid-cols-5">

                            {{-- IMAGEN --}}
                            <div class="min-w-0 lg:col-span-2">
                                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Fotografía de portada
                                </p>

                                <button type="button" id="abrirResumenPortada"
                                    class="group relative flex aspect-video w-full items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-black/20">

                                    <img id="resumenPortada" src="" alt="Portada seleccionada" class="hidden h-full w-full object-cover">

                                    <div id="resumenSinPortada" class="flex flex-col items-center px-5 text-center">
                                        <svg class="mb-2 h-8 w-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>

                                        <p class="text-sm font-medium text-gray-500">
                                            Sin fotografía
                                        </p>
                                    </div>
                                </button>

                                <p id="resumenTextoAlternativo" class="mt-2 break-words text-xs leading-5 text-gray-600"></p>
                            </div>

                            {{-- GENERAL --}}
                            <div class="min-w-0 lg:col-span-3">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                                    Actividad
                                </p>

                                <h3 id="resumenNombre" class="mt-2 break-words text-xl font-bold text-white sm:text-2xl">
                                    Sin nombre
                                </h3>

                                <p id="resumenResumen" class="mt-3 break-words text-sm leading-6 text-gray-400">
                                    Sin resumen.
                                </p>

                                <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @if ($categorias->isNotEmpty())
                                        <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                                            <p class="text-xs text-gray-600">Categoría</p>
                                            <p id="resumenCategoria" class="mt-1 break-words text-sm font-medium text-gray-300">Sin categoría</p>
                                        </div>
                                    @endif

                                    <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                                        <p class="text-xs text-gray-600">Visibilidad</p>
                                        <p id="resumenVisibilidad" class="mt-1 text-sm font-medium text-gray-300">Pública</p>
                                    </div>

                                    <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                                        <p class="text-xs text-gray-600">Prioridad</p>
                                        <p id="resumenPrioridad" class="mt-1 text-sm font-medium text-gray-300">Regular</p>
                                    </div>

                                    <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                                        <p class="text-xs text-gray-600">Destacada</p>
                                        <p id="resumenDestacada" class="mt-1 text-sm font-medium text-gray-300">No</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DESCRIPCIÓN --}}
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                            Descripción
                        </p>

                        <p id="resumenDescripcion" class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-gray-300">
                            Sin descripción.
                        </p>
                    </div>

                    {{-- REALIZACIÓN --}}
                    <div class="rounded-2xl border border-cyan-500/20 bg-cyan-500/[0.04] p-4 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-300">
                                ◷
                            </div>

                            <div class="min-w-0">
                                <h3 class="font-semibold text-white">
                                    Período de realización
                                </h3>

                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Es el período real en el que sucede la actividad y servirá como límite para las sesiones y turnos.
                                </p>

                                <p id="resumenRealizacionPeriodo" class="mt-3 break-words text-sm font-medium text-cyan-200">
                                    Sin período definido
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- DETALLES --}}
                    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-6">
                            <h3 class="font-semibold text-white">
                                Publicación
                            </h3>

                            <div class="mt-5 space-y-4 text-sm">
                                <div class="border-b border-white/5 pb-3">
                                    <p class="text-xs text-gray-600">Período de visibilidad</p>
                                    <p id="resumenVisibilidadPeriodo" class="mt-1 break-words font-medium text-gray-300">
                                        Sin fechas definidas
                                    </p>
                                </div>

                                @if ($etiquetas->isNotEmpty())
                                    <div>
                                        <p class="text-xs text-gray-600">Etiquetas</p>

                                        <div id="resumenEtiquetas" class="mt-2 flex flex-wrap gap-2">
                                            <span class="text-xs text-gray-600">Sin etiquetas</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 sm:p-6">
                            <h3 class="font-semibold text-white">
                                Inscripciones
                            </h3>

                            <div class="mt-5 space-y-4 text-sm">
                                <div class="flex items-start justify-between gap-3 border-b border-white/5 pb-3">
                                    <span class="text-gray-500">Requiere inscripción</span>
                                    <span id="resumenInscripcion" class="text-right font-medium text-gray-300">Sí</span>
                                </div>

                                <div class="flex items-start justify-between gap-3 border-b border-white/5 pb-3">
                                    <span class="text-gray-500">Cupo</span>
                                    <span id="resumenCupo" class="text-right font-medium text-gray-300">Sin límite</span>
                                </div>

                                <div class="flex items-start justify-between gap-3 border-b border-white/5 pb-3">
                                    <span class="text-gray-500">Requiere cuenta</span>
                                    <span id="resumenCuenta" class="text-right font-medium text-gray-300">No</span>
                                </div>

                                <div class="flex items-start justify-between gap-3 border-b border-white/5 pb-3">
                                    <span class="text-gray-500">Lista de espera</span>
                                    <span id="resumenEspera" class="text-right font-medium text-gray-300">No</span>
                                </div>

                                <div>
                                    <p class="text-xs text-gray-600">Período de inscripción</p>
                                    <p id="resumenPeriodoInscripcion" class="mt-1 break-words font-medium text-gray-300">
                                        Sin fechas definidas
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 sm:p-5">
                        <div class="flex items-start gap-3">
                            <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-amber-400"></div>

                            <div class="min-w-0">
                                <p class="font-semibold text-amber-300">
                                    La actividad todavía no será pública
                                </p>

                                <p class="mt-1 break-words text-sm leading-6 text-gray-500">
                                    Se guardará como borrador. Después podrás completar sesiones, responsables, formularios, productos, promociones y el proceso de publicación.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- NAVEGACIÓN --}}
            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-white/10 pt-5 sm:flex-row sm:items-center sm:justify-between">
                <button type="button" id="btnAnterior"
                    class="hidden w-full items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-semibold text-gray-300 transition hover:bg-white/10 sm:w-auto">

                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>

                    Anterior
                </button>

                <div class="ml-auto flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <button type="button" id="btnSiguiente"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-400 sm:w-auto">

                        Continuar

                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    <button type="submit" id="btnGuardar"
                        class="hidden w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/10 transition hover:bg-emerald-400 sm:w-auto">

                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>

                        Crear actividad
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- TOOLTIP GLOBAL: NO LO CORTAN LAS TARJETAS --}}
<div id="globalHelpTooltip"
    class="pointer-events-none fixed z-[200] hidden max-w-[calc(100vw-2rem)] rounded-xl border border-white/10 bg-gray-900 px-3 py-2.5 text-xs leading-5 text-gray-300 shadow-2xl sm:max-w-xs">
</div>

{{-- MODAL FOTO COMPLETA --}}
<div id="modalPortada" class="fixed inset-0 z-[300] hidden items-center justify-center bg-black/90 p-3 backdrop-blur-sm sm:p-6">
    <button type="button" id="cerrarModalPortada"
        class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20"
        aria-label="Cerrar fotografía">

        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <img id="modalPortadaImagen" src="" alt="Vista completa de la fotografía"
        class="max-h-[88vh] max-w-full rounded-xl object-contain shadow-2xl">
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let pasoActual = 1;
    const totalPasos = 4;

    const titulos = {
        1: 'Información',
        2: 'Presentación',
        3: 'Inscripciones',
        4: 'Confirmación'
    };

    const formSteps = document.querySelectorAll('.form-step');
    const desktopIndicators = document.querySelectorAll('[data-step-indicator]');
    const mobileBars = document.querySelectorAll('[data-mobile-step]');

    const btnAnterior = document.getElementById('btnAnterior');
    const btnSiguiente = document.getElementById('btnSiguiente');
    const btnGuardar = document.getElementById('btnGuardar');

    const mobileStepNumber = document.getElementById('mobileStepNumber');
    const mobileStepTitle = document.getElementById('mobileStepTitle');
    const mobilePercentage = document.getElementById('mobilePercentage');

    function mostrarPaso(numero) {
        pasoActual = numero;

        formSteps.forEach(step => {
            step.classList.toggle('hidden', Number(step.dataset.step) !== numero);
        });

        desktopIndicators.forEach(indicator => {
            const numeroIndicador = Number(indicator.dataset.stepIndicator);
            const circle = indicator.querySelector('.step-circle');
            const label = indicator.querySelector('.step-label');
            const activo = numeroIndicador === numero;
            const completado = numeroIndicador < numero;

            circle.classList.toggle('border-emerald-500', activo || completado);
            circle.classList.toggle('bg-emerald-500', activo || completado);
            circle.classList.toggle('text-white', activo || completado);

            circle.classList.toggle('border-gray-700', !activo && !completado);
            circle.classList.toggle('bg-gray-900', !activo && !completado);
            circle.classList.toggle('text-gray-400', !activo && !completado);

            if (label) {
                label.classList.toggle('text-emerald-400', activo);
                label.classList.toggle('text-gray-300', completado);
                label.classList.toggle('text-gray-500', !activo && !completado);
            }
        });

        mobileBars.forEach(bar => {
            const numeroBarra = Number(bar.dataset.mobileStep);

            bar.classList.toggle('bg-emerald-500', numeroBarra <= numero);
            bar.classList.toggle('bg-gray-800', numeroBarra > numero);
        });

        const porcentaje = Math.round((numero / totalPasos) * 100);

        mobileStepNumber.textContent = `Paso ${numero} de ${totalPasos}`;
        mobileStepTitle.textContent = titulos[numero];
        mobilePercentage.textContent = `${porcentaje}%`;

        btnAnterior.classList.toggle('hidden', numero === 1);
        btnAnterior.classList.toggle('inline-flex', numero !== 1);

        btnSiguiente.classList.toggle('hidden', numero === totalPasos);
        btnSiguiente.classList.toggle('inline-flex', numero !== totalPasos);

        btnGuardar.classList.toggle('hidden', numero !== totalPasos);
        btnGuardar.classList.toggle('inline-flex', numero === totalPasos);

        if (numero === 4) {
            actualizarResumen();
        }

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    function validarPaso() {
        const step = document.querySelector(`.form-step[data-step="${pasoActual}"]`);
        const fields = step.querySelectorAll('input, select, textarea');

        for (const field of fields) {
            if (!field.checkValidity()) {
                field.reportValidity();
                field.focus();
                return false;
            }
        }

        return true;
    }

    btnSiguiente.addEventListener('click', function () {
        if (!validarPaso()) return;

        if (pasoActual < totalPasos) {
            mostrarPaso(pasoActual + 1);
        }
    });

    btnAnterior.addEventListener('click', function () {
        if (pasoActual > 1) {
            mostrarPaso(pasoActual - 1);
        }
    });

   
    const tooltip = document.getElementById('globalHelpTooltip');
    let tooltipButton = null;

    function posicionarTooltip(button) {
        const rect = button.getBoundingClientRect();

        tooltip.style.visibility = 'hidden';
        tooltip.classList.remove('hidden');

        const tooltipRect = tooltip.getBoundingClientRect();
        const margin = 12;

        let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
        left = Math.max(margin, Math.min(left, window.innerWidth - tooltipRect.width - margin));

        let top = rect.bottom + 8;

        if (top + tooltipRect.height > window.innerHeight - margin) {
            top = rect.top - tooltipRect.height - 8;
        }

        tooltip.style.left = `${left}px`;
        tooltip.style.top = `${Math.max(margin, top)}px`;
        tooltip.style.visibility = 'visible';
    }

    function mostrarTooltip(button) {
        tooltipButton = button;
        tooltip.textContent = button.dataset.help;
        posicionarTooltip(button);
    }

    function ocultarTooltip() {
        tooltipButton = null;
        tooltip.classList.add('hidden');
        tooltip.style.visibility = '';
    }

    document.querySelectorAll('.help-button').forEach(button => {
        button.addEventListener('mouseenter', () => mostrarTooltip(button));
        button.addEventListener('mouseleave', ocultarTooltip);
        button.addEventListener('focus', () => mostrarTooltip(button));
        button.addEventListener('blur', ocultarTooltip);

        button.addEventListener('click', function (event) {
            event.stopPropagation();

            if (tooltipButton === button && !tooltip.classList.contains('hidden')) {
                ocultarTooltip();
            } else {
                mostrarTooltip(button);
            }
        });
    });

    document.addEventListener('click', ocultarTooltip);

    window.addEventListener('resize', function () {
        if (tooltipButton) posicionarTooltip(tooltipButton);
    });

    window.addEventListener('scroll', function () {
        if (tooltipButton) posicionarTooltip(tooltipButton);
    }, true);

    //Fechas
    const visibleDesde = document.getElementById('visible_desde');
    const visibleHasta = document.getElementById('visible_hasta');
    const inscripcionDesde = document.getElementById('inscripcion_desde');
    const inscripcionHasta = document.getElementById('inscripcion_hasta');
    const realizacionDesde = document.getElementById('realizacion_desde');
    const realizacionHasta = document.getElementById('realizacion_hasta');
    const mensajeRealizacionIncompleta = document.getElementById('mensajeRealizacionIncompleta');

    function localDateTimeValue(date = new Date()) {
        const pad = number => String(number).padStart(2, '0');

        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
    }

    function actualizarEstadoRealizacion() {
        const inicioDefinido = Boolean(realizacionDesde.value);
        const finDefinido = Boolean(realizacionHasta.value);
        const periodoIncompleto = inicioDefinido !== finDefinido;

        realizacionDesde.required = finDefinido;
        realizacionHasta.required = inicioDefinido;

        mensajeRealizacionIncompleta.classList.toggle('hidden', !periodoIncompleto);
    }

    function actualizarMinimosFecha() {
        const ahora = localDateTimeValue();

        document.querySelectorAll('.future-date').forEach(input => {
            input.min = ahora;
        });

        visibleHasta.min = visibleDesde.value && visibleDesde.value > ahora
            ? visibleDesde.value
            : ahora;

        inscripcionHasta.min = inscripcionDesde.value && inscripcionDesde.value > ahora
            ? inscripcionDesde.value
            : ahora;

        realizacionHasta.min = realizacionDesde.value && realizacionDesde.value > ahora
            ? realizacionDesde.value
            : ahora;

        actualizarEstadoRealizacion();
    }

    visibleDesde.addEventListener('change', function () {
        actualizarMinimosFecha();

        if (visibleHasta.value && visibleHasta.value < visibleDesde.value) {
            visibleHasta.value = '';
        }
    });

    inscripcionDesde.addEventListener('change', function () {
        actualizarMinimosFecha();

        if (inscripcionHasta.value && inscripcionHasta.value < inscripcionDesde.value) {
            inscripcionHasta.value = '';
        }
    });

    realizacionDesde.addEventListener('change', function () {
        actualizarMinimosFecha();

        if (realizacionHasta.value && realizacionHasta.value < realizacionDesde.value) {
            realizacionHasta.value = '';
        }

        actualizarEstadoRealizacion();
    });

    realizacionHasta.addEventListener('change', function () {
        actualizarMinimosFecha();
        actualizarEstadoRealizacion();
    });

    actualizarMinimosFecha();

    setInterval(actualizarMinimosFecha, 60000);

    //Portada
    const inputPortada = document.getElementById('portada');
    const portadaPreview = document.getElementById('portadaPreview');
    const portadaPlaceholder = document.getElementById('portadaPlaceholder');
    const portadaNombre = document.getElementById('portadaNombre');
    const quitarPortada = document.getElementById('quitarPortada');
    const indicadorAmpliar = document.getElementById('indicadorAmpliar');

    const resumenPortada = document.getElementById('resumenPortada');
    const resumenSinPortada = document.getElementById('resumenSinPortada');

    const abrirPreviewPortada = document.getElementById('abrirPreviewPortada');
    const abrirResumenPortada = document.getElementById('abrirResumenPortada');

    const modalPortada = document.getElementById('modalPortada');
    const modalPortadaImagen = document.getElementById('modalPortadaImagen');
    const cerrarModalPortada = document.getElementById('cerrarModalPortada');

    let portadaDataUrl = null;

    function mostrarModalPortada() {
        if (!portadaDataUrl) return;

        modalPortadaImagen.src = portadaDataUrl;
        modalPortada.classList.remove('hidden');
        modalPortada.classList.add('flex');

        document.body.classList.add('overflow-hidden');
    }

    function cerrarModal() {
        modalPortada.classList.add('hidden');
        modalPortada.classList.remove('flex');

        document.body.classList.remove('overflow-hidden');
    }

    function limpiarPortada() {
        inputPortada.value = '';
        portadaDataUrl = null;

        portadaPreview.src = '';
        portadaPreview.classList.add('hidden');
        portadaPlaceholder.classList.remove('hidden');

        resumenPortada.src = '';
        resumenPortada.classList.add('hidden');
        resumenSinPortada.classList.remove('hidden');

        portadaNombre.textContent = 'Seleccionar fotografía';

        quitarPortada.classList.add('hidden');
        quitarPortada.classList.remove('inline-flex');

        indicadorAmpliar.classList.add('hidden');
    }

    inputPortada.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) {
            limpiarPortada();
            return;
        }

        portadaNombre.textContent = file.name;

        const reader = new FileReader();

        reader.onload = function (event) {
            portadaDataUrl = event.target.result;

            portadaPreview.src = portadaDataUrl;
            portadaPreview.classList.remove('hidden');
            portadaPlaceholder.classList.add('hidden');

            quitarPortada.classList.remove('hidden');
            quitarPortada.classList.add('inline-flex');

            indicadorAmpliar.classList.remove('hidden');
        };

        reader.readAsDataURL(file);
    });

    quitarPortada.addEventListener('click', limpiarPortada);

    abrirPreviewPortada.addEventListener('click', mostrarModalPortada);
    abrirResumenPortada.addEventListener('click', mostrarModalPortada);

    cerrarModalPortada.addEventListener('click', cerrarModal);

    modalPortada.addEventListener('click', function (event) {
        if (event.target === modalPortada) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            cerrarModal();
            ocultarTooltip();
        }
    });

   
    const habilitaInscripcion = document.getElementById('habilita_inscripcion');
    const configuracionInscripcion = document.getElementById('configuracionInscripcion');
    const mensajeSinInscripcion = document.getElementById('mensajeSinInscripcion');

    function actualizarInscripcion() {
        const habilitada = habilitaInscripcion.checked;

        configuracionInscripcion.classList.toggle('hidden', !habilitada);
        mensajeSinInscripcion.classList.toggle('hidden', habilitada);
    }

    habilitaInscripcion.addEventListener('change', actualizarInscripcion);
    actualizarInscripcion();

    
    function selectedText(select) {
        if (!select) return null;

        return select.options[select.selectedIndex]?.text?.trim() || null;
    }

    function formatDate(value) {
        if (!value) return null;

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) return value;

        return date.toLocaleString('es-SV', {
            dateStyle: 'medium',
            timeStyle: 'short'
        });
    }

    function formatPeriod(from, to) {
        const start = formatDate(from);
        const end = formatDate(to);

        if (!start && !end) return 'Sin fechas definidas';
        if (start && end) return `${start} → ${end}`;
        if (start) return `Desde ${start}`;

        return `Hasta ${end}`;
    }

    function priorityLabel(value) {
        const priorities = {
            '100': 'Alta',
            '75': 'Media',
            '50': 'Regular',
            '25': 'Baja'
        };

        return priorities[String(value)] || 'Regular';
    }

    function actualizarResumen() {
        const nombre = document.getElementById('nombre').value.trim();
        const resumen = document.getElementById('resumen').value.trim();
        const descripcion = document.getElementById('descripcion').value.trim();

        const categoria = document.getElementById('id_categoria');
        const visibilidad = document.getElementById('visibilidad');

        const prioridad = document.getElementById('prioridad').value;
        const destacada = document.getElementById('destacada').checked;

        const cupo = document.getElementById('cupo_total').value;
        const requiereCuenta = document.getElementById('requiere_cuenta').checked;
        const espera = document.getElementById('permite_lista_espera').checked;

        document.getElementById('resumenNombre').textContent = nombre || 'Sin nombre';
        document.getElementById('resumenResumen').textContent = resumen || 'Sin resumen.';
        document.getElementById('resumenDescripcion').textContent = descripcion || 'Sin descripción.';
        document.getElementById('resumenVisibilidad').textContent = selectedText(visibilidad) || 'Pública';
        document.getElementById('resumenPrioridad').textContent = priorityLabel(prioridad);
        document.getElementById('resumenDestacada').textContent = destacada ? 'Sí' : 'No';

        if (categoria && document.getElementById('resumenCategoria')) {
            document.getElementById('resumenCategoria').textContent =
                categoria.value ? selectedText(categoria) : 'Sin categoría';
        }

        const alt = document.getElementById('texto_alternativo_portada').value.trim();

        document.getElementById('resumenTextoAlternativo').textContent =
            portadaDataUrl
                ? `Descripción: ${alt || nombre || 'Sin descripción adicional'}`
                : '';

        const registrationEnabled = habilitaInscripcion.checked;

        document.getElementById('resumenInscripcion').textContent =
            registrationEnabled ? 'Sí' : 'No';

        document.getElementById('resumenCupo').textContent =
            registrationEnabled
                ? (cupo ? `${cupo} personas` : 'Sin límite definido')
                : 'No aplica';

        document.getElementById('resumenCuenta').textContent =
            registrationEnabled
                ? (requiereCuenta ? 'Sí' : 'No')
                : 'No aplica';

        document.getElementById('resumenEspera').textContent =
            registrationEnabled
                ? (espera ? 'Sí' : 'No')
                : 'No aplica';

        document.getElementById('resumenPeriodoInscripcion').textContent =
            registrationEnabled
                ? formatPeriod(inscripcionDesde.value, inscripcionHasta.value)
                : 'No aplica';

        document.getElementById('resumenVisibilidadPeriodo').textContent =
            formatPeriod(visibleDesde.value, visibleHasta.value);

        document.getElementById('resumenRealizacionPeriodo').textContent =
            realizacionDesde.value || realizacionHasta.value
                ? formatPeriod(realizacionDesde.value, realizacionHasta.value)
                : 'Sin período definido';

        if (portadaDataUrl) {
            resumenPortada.src = portadaDataUrl;
            resumenPortada.classList.remove('hidden');
            resumenSinPortada.classList.add('hidden');
        } else {
            resumenPortada.src = '';
            resumenPortada.classList.add('hidden');
            resumenSinPortada.classList.remove('hidden');
        }

        const tagsContainer = document.getElementById('resumenEtiquetas');

        if (tagsContainer) {
            const selectedTags = document.querySelectorAll('input[name="etiquetas[]"]:checked');

            tagsContainer.innerHTML = '';

            if (selectedTags.length === 0) {
                const empty = document.createElement('span');
                empty.className = 'text-xs text-gray-600';
                empty.textContent = 'Sin etiquetas';

                tagsContainer.appendChild(empty);
            } else {
                selectedTags.forEach(tag => {
                    const badge = document.createElement('span');

                    badge.className =
                        'rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-400';

                    badge.textContent = tag.dataset.nombreEtiqueta;

                    tagsContainer.appendChild(badge);
                });
            }
        }
    }

    mostrarPaso(1);
});

    const tiposUbicacion=document.querySelectorAll('.tipo-ubicacion');
    const bloqueEspacioRegistrado=document.getElementById('bloqueEspacioRegistrado');
    const bloqueUbicacionExterna=document.getElementById('bloqueUbicacionExterna');
    const espacioSelect=document.getElementById('id_espacio');
    const ubicacionExterna=document.getElementById('ubicacion_externa');
    const informacionCapacidadEspacio=document.getElementById('informacionCapacidadEspacio');
    const capacidadEspacioTexto=document.getElementById('capacidadEspacioTexto');
    const cupoTotalUbicacion=document.getElementById('cupo_total');
    function actualizarCapacidadEspacio(){const option=espacioSelect?.options[espacioSelect.selectedIndex];const capacidad=option?.dataset?.capacidad||'';informacionCapacidadEspacio?.classList.toggle('hidden',!capacidad);if(capacidad){capacidadEspacioTexto.textContent=`${capacidad} personas`;if(cupoTotalUbicacion)cupoTotalUbicacion.max=capacidad;}else{if(capacidadEspacioTexto)capacidadEspacioTexto.textContent='';if(cupoTotalUbicacion)cupoTotalUbicacion.removeAttribute('max');}}
    function actualizarTipoUbicacion(){const tipo=document.querySelector('.tipo-ubicacion:checked')?.value||'';bloqueEspacioRegistrado?.classList.toggle('hidden',tipo!=='registrada');bloqueUbicacionExterna?.classList.toggle('hidden',tipo!=='externa');if(tipo==='registrada'&&ubicacionExterna)ubicacionExterna.value='';if(tipo==='externa'&&espacioSelect){espacioSelect.value='';actualizarCapacidadEspacio();}}
    tiposUbicacion.forEach(radio=>radio.addEventListener('change',actualizarTipoUbicacion));
    espacioSelect?.addEventListener('change',actualizarCapacidadEspacio);
    const ubicacionAnterior=document.getElementById('ubicacion_externa')?.value||'';
    const espacioAnterior=document.getElementById('id_espacio')?.value||'';
    if(espacioAnterior){const radio=document.querySelector('.tipo-ubicacion[value="registrada"]');if(radio)radio.checked=true;}else if(ubicacionAnterior){const radio=document.querySelector('.tipo-ubicacion[value="externa"]');if(radio)radio.checked=true;}
    actualizarTipoUbicacion();actualizarCapacidadEspacio();
</script>
@endsection