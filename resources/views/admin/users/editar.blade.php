@extends('layouts.navbars')

@section('title', 'SIDAN | Actualizar usuario')

@push('styles')
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css"
    >
@endpush


@section('content')

<div id="edit-profile-container" class="relative min-h-full">
    <div
        x-data="registerForm()"
        x-init="cargarPaises()"
        class="flex w-full flex-col bg-[#f6f8fb] dark:bg-sidan-950"
    >
        {{-- Contenido --}}
        <main class="mx-auto max-w-4xl w-full items-center px-4 pt-4 lg:px-8 lg:pt-8 py-10 sm:px-6 lg:py-14">

            {{-- Encabezado --}}
            <div class="mx-auto max-w-2xl text-center">
                <h1 class="mt-5 text-3xl font-black tracking-tight text-sidan-900 sm:text-4xl dark:text-white">
                    Actualizar información del usuario
                </h1>

                <p class="mt-3 text-slate-600 dark:text-slate-400">
                    Modifique la información necesaria para actualizar los datos del usuario.
                </p>
            </div>

            {{-- Stepper --}}
            <div class="mx-auto mt-10 max-w-3xl">

                <ol class="flex items-center w-full">
        
                    {{-- Paso 1 --}}
                    <li class="flex w-full items-center">

                        <div class="flex items-center">

                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 text-sm font-black transition"
                                :class="step >= 1
                                    ? 'border-sidan-500 bg-sidan-500 text-white'
                                    : 'border-slate-300 bg-white text-slate-400 dark:border-white/20 dark:bg-white/5'"
                            >
                                {{-- Número mientras está activo --}}
                                <span x-show="step === 1">1</span>

                                {{-- Check cuando ya fue completado --}}
                                <svg
                                    x-show="step > 1"
                                    x-cloak
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="m5 12 4 4L19 6"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </span>

                            {{-- Texto del paso --}}
                            <span
                                class="ml-3 hidden text-sm font-bold transition sm:block"
                                :class="step >= 1
                                    ? 'text-sidan-500'
                                    : 'text-slate-400'"
                            >
                                Datos personales
                            </span>

                        </div>


                        {{-- Línea hacia el paso 2 --}}
                        <div class="mx-4 h-0.5 flex-1 bg-slate-200 dark:bg-white/10">

                            <div
                                class="h-full bg-sidan-500 transition-all duration-300"
                                :class="step > 1 ? 'w-full' : 'w-0'"
                            ></div>

                        </div>

                    </li>


                    {{-- Paso 2 --}}
                    <li class="flex w-full items-center">

                        <div class="flex items-center">

                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 text-sm font-black transition"
                                :class="step >= 2
                                    ? 'border-sidan-500 bg-sidan-500 text-white'
                                    : 'border-slate-300 bg-white text-slate-400 dark:border-white/20 dark:bg-white/5'"
                            >

                                {{-- Número mientras está activo --}}
                                <span x-show="step <= 2">2</span>

                                <svg
                                    x-show="step > 2"
                                    x-cloak
                                    class="h-5 w-5"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        d="m5 12 4 4L19 6"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>

                            </span>

                            {{-- Texto del paso --}}
                            <span
                                class="ml-3 hidden text-sm font-bold transition sm:block"
                                :class="step >= 2
                                    ? 'text-sidan-500'
                                    : 'text-slate-400'"
                            >
                                Credenciales
                            </span>

                        </div>


                        {{-- Línea hacia el paso 3 --}}
                        <div class="mx-4 h-0.5 flex-1 bg-slate-200 dark:bg-white/10">

                            <div
                                class="h-full bg-sidan-500 transition-all duration-300"
                                :class="step > 2 ? 'w-full' : 'w-0'"
                            ></div>

                        </div>

                    </li>


                    {{-- Paso 3 --}}
                    <li class="flex items-center">

                        <div class="flex items-center">

                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 text-sm font-black transition"
                                :class="step >= 3
                                    ? 'border-sidan-500 bg-sidan-500 text-white'
                                    : 'border-slate-300 bg-white text-slate-400 dark:border-white/20 dark:bg-white/5'"
                            >
                                3
                            </span>

                            {{-- Texto del paso --}}
                            <span
                                class="ml-3 hidden text-sm font-bold transition sm:block"
                                :class="step >= 3
                                    ? 'text-sidan-500'
                                    : 'text-slate-400'"
                            >
                                Confirmación
                            </span>

                        </div>

                    </li>

                </ol>

            </div>

            <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
                {{-- Formulario --}}
                <form
                    action="{{ route('admin.users.update', $user) }}"
                    method="POST"
                    class="mx-auto mt-8 max-w-3xl"
                    enctype="multipart/form-data"
                    @submit="submitForm"
                >

                    @csrf
                    @method('PUT')

                    {{-- TARJETA --}}
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">

                        {{-- Paso 1 --}}
                        <section id="register-step-1" x-show="step === 1" x-cloak class="p-6 sm:p-8">

                            <div class="mb-8">

                                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                                    Paso 01
                                </p>

                                <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">
                                    Información del usuario
                                </h2>

                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                   Actualice la información del usuario.
                                </p>

                            </div>

                            {{-- Foto de perfil del usuario --}}
                            <div class="flex flex-col items-center text-center px-6 pb-8 sm:px-8">
                                {{-- Foto de perfil del usuario --}}
                                <div class="relative" x-data="{ menuFotoAbierto: false }">
                                    {{-- Foto de perfil --}}
                                    <img
                                        :src="imagenPreview"
                                        alt="Foto de perfil"
                                        class="h-32 w-32 rounded-full object-cover ring-4 ring-slate-200 shadow-lg dark:ring-slate-500"
                                    >

                                    {{-- Botón cámara --}}
                                    <button
                                        type="button"
                                        @click="menuFotoAbierto = !menuFotoAbierto"
                                        class="absolute bottom-1 right-1 flex h-10 w-10 items-center justify-center rounded-full bg-sidan-500 text-white shadow-lg transition hover:bg-white hover:text-sidan-500 hover:ring-2 hover:ring-sidan-500 focus:outline-none focus:ring-2 focus:ring-sidan-500 focus:ring-offset-2"
                                        title="Opciones de foto"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M3 9a2 2 0 0 1 2-2h1.586a2 2 0 0 0 1.414-.586l.828-.828A2 2 0 0 1 10.243 5h3.514a2 2 0 0 1 1.415.586l.828.828A2 2 0 0 0 17.414 7H19a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"
                                            />
                                            <circle cx="12" cy="13" r="3" />
                                        </svg>
                                    </button>

                                    {{-- Menú de opciones --}}
                                    <div
                                        x-show="menuFotoAbierto"
                                        x-transition
                                        @click.outside="menuFotoAbierto = false"
                                        x-cloak
                                        class="absolute left-1/2 top-full z-30 mt-3 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl dark:border-white/10 dark:bg-slate-800"
                                    >
                                        {{-- Colocar / cambiar foto --}}
                                        <button
                                            type="button"
                                            @click="
                                                menuFotoAbierto = false;
                                                $refs.imagenPerfil.click();
                                            "
                                            class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-white/5"
                                        >
                                            <span
                                                x-text="tieneImagenPerfil ? 'Cambiar foto' : 'Colocar foto'"
                                            ></span>
                                        </button>

                                        {{-- Quitar foto --}}
                                        <button
                                            x-show="tieneImagenPerfil"
                                            x-transition
                                            type="button"
                                            @click="
                                                menuFotoAbierto = false;
                                                quitarImagenPerfil();
                                            "
                                            class="flex w-full items-center gap-3 border-t border-slate-100 px-4 py-3 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-white/10 dark:text-red-400 dark:hover:bg-red-500/10"
                                        >
                                            Quitar foto
                                        </button>
                                    </div>

                                    {{-- Input oculto para seleccionar imagen --}}
                                    <input
                                        x-ref="imagenPerfil"
                                        type="file"
                                        name="imagen_perfil"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="hidden"
                                        @change="cambiarImagen"
                                    >

                                    {{-- Indica si se desea eliminar la imagen --}}
                                    <input
                                        type="hidden"
                                        name="eliminar_imagen_perfil"
                                        :value="eliminarImagenPerfil ? '1' : '0'"
                                    >

                                    {{-- Modal para recortar foto de perfil --}}
                                    <div
                                        x-show="editorImagenAbierto"
                                        x-cloak
                                        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                                        @keydown.escape.window="cancelarRecorteImagen()"
                                    >
                                        {{-- Fondo --}}
                                        <div
                                            class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"
                                            @click="cancelarRecorteImagen()"
                                        ></div>

                                        {{-- Modal --}}
                                        <div
                                            x-show="editorImagenAbierto"
                                            x-transition
                                            @click.stop
                                            class="relative z-10 w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-800 lg:left-32"
                                        >
                                            {{-- Encabezado --}}
                                            <div class="flex w-full items-start justify-between border-b border-slate-200 px-6 py-4 dark:border-white/10">
                                                <div>
                                                    <h3 class="text-left text-lg font-bold text-slate-900 dark:text-white">
                                                        Ajustar foto
                                                    </h3>

                                                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                                        Mueve y ajusta la imagen para seleccionar la parte que deseas mostrar.
                                                    </p>
                                                </div>

                                                <button
                                                    type="button"
                                                    @click="cancelarRecorteImagen()"
                                                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white"
                                                    aria-label="Cerrar"
                                                >
                                                    <svg
                                                        xmlns="http://www.w3.org/2000/svg"
                                                        class="h-5 w-5"
                                                        fill="none"
                                                        viewBox="0 0 24 24"
                                                        stroke="currentColor"
                                                        stroke-width="2"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            d="M6 18 18 6M6 6l12 12"
                                                        />
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Editor --}}
                                            <div class="bg-slate-100 p-4 sm:p-6 dark:bg-slate-900">
                                                <div class="mx-auto h-[420px] max-h-[60vh] overflow-hidden rounded-xl bg-black">
                                                    <img
                                                        x-ref="imagenCropper"
                                                        :src="imagenParaRecortar"
                                                        @load="inicializarCropper()"
                                                        alt="Imagen para recortar"
                                                        class="block max-w-full"
                                                    >
                                                </div>
                                            </div>

                                            {{-- Controles --}}
                                            <div class="flex flex-col gap-4 border-t border-slate-200 p-5 dark:border-white/10">

                                                {{-- Zoom --}}
                                                <div class="flex items-center justify-center gap-3">
                                                    <button
                                                        type="button"
                                                        @click="alejarImagen()"
                                                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 font-bold text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5"
                                                        title="Alejar"
                                                    >
                                                        −
                                                    </button>

                                                    <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                                                        Zoom
                                                    </span>

                                                    <button
                                                        type="button"
                                                        @click="acercarImagen()"
                                                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 font-bold text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5"
                                                        title="Acercar"
                                                    >
                                                        +
                                                    </button>
                                                </div>

                                                {{-- Acciones --}}
                                                <div class="flex justify-end gap-3">
                                                    <button
                                                        type="button"
                                                        @click="cancelarRecorteImagen()"
                                                        class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5"
                                                    >
                                                        Cancelar
                                                    </button>

                                                    <button
                                                        type="button"
                                                        @click="aplicarRecorteImagen()"
                                                        class="rounded-xl bg-sidan-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600"
                                                    >
                                                        Aplicar cambios
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <p class="mt-3 text-xs text-gray-400">
                                    Haz clic en la cámara para cambiar la imagen.
                                </p>
                            </div>


                            <div class="grid gap-5 sm:grid-cols-2">

                                {{-- Nombres --}}
                                <div>

                                    <label for="nombres" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Nombres *
                                    </label>

                                    <input
                                        id="nombres"
                                        name="nombres"
                                        type="text"
                                        x-model="nombres"
                                        value="{{ old('nombres') }}"
                                        placeholder="Juan Carlos"
                                        required
                                        autocomplete="given-name"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('nombres')
                                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                    @enderror

                                </div>


                                {{-- Apellidos --}}
                                <div>

                                    <label for="apellidos" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Apellidos *
                                    </label>

                                    <input
                                        id="apellidos"
                                        name="apellidos"
                                        type="text"
                                        x-model="apellidos"
                                        value="{{ old('apellidos') }}"
                                        placeholder="Pérez García"
                                        required
                                        autocomplete="family-name"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('apellidos')
                                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                    @enderror

                                </div>


                                {{-- Documento --}}
                                <div>

                                    <label for="documento" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Documento de identidad (DUI)
                                    </label>

                                    <input
                                        id="documento"
                                        name="documento"
                                        type="text"
                                        x-model="documento"
                                        value="{{ old('documento') }}"
                                        maxlength="10"
                                        pattern="[0-9]{8}-[0-9]"
                                        placeholder="00000000-0"
                                        @input="clearError('documento')"
                                        @keydown="
                                            console.log(
                                                'KEY:',
                                                $event.key,
                                                'LENGTH:',
                                                $event.target.value.length
                                            );

                                            if (
                                                $event.target.value.length >= 10 &&
                                                $event.key.length === 1 &&
                                                $event.target.selectionStart === $event.target.selectionEnd
                                            ) {
                                                $event.preventDefault();
                                                shakeDocumento();
                                            }
                                        "
                                        autocomplete="off"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('documento')
                                        <p
                                            x-show="serverErrors.documento"
                                            class="mt-2 text-sm font-semibold text-red-600"
                                        >
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>


                                {{-- Teléfono --}}
                                <div>

                                    <label for="telefono" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Teléfono 
                                    </label>

                                    <input type="hidden" name="telefono" :value="telefonoFormateado()">

                                    <input
                                        id="telefono"
                                        type="tel"
                                        autocomplete="tel"
                                        value="{{ old('telefono', $user->informacion_personal->telefono ?? '') }}"
                                        @input="telefono = $event.target.value; clearError('telefono')"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('telefono')
                                        <p
                                            x-show="serverErrors.telefono"
                                            class="mt-2 text-sm font-semibold text-red-600"
                                        >
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>


                                {{-- Fecha --}}
                                <div>

                                    <label for="fecha_nacimiento" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Fecha de nacimiento 
                                    </label>

                                    <input
                                        id="fecha_nacimiento"
                                        name="fecha_nacimiento"
                                        type="date"
                                        x-model="fechaNacimiento"
                                        value="{{ old('fecha_nacimiento') }}"
                                        max="{{ now()->subYears(10)->format('Y-m-d') }}"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('fecha_nacimiento')
                                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                    @enderror

                                </div>


                                {{-- Género --}}
                                <div>

                                    <label for="genero" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Género 
                                    </label>

                                    <select
                                        id="genero"
                                        name="genero"
                                        x-model="genero"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >
                                        <option value="">Selecciona una opción</option>
                                        <option value="M" @selected(old('genero') === 'M')>Masculino</option>
                                        <option value="F" @selected(old('genero') === 'F')>Femenino</option>
                                    </select>

                                    @error('genero')
                                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                    @enderror

                                </div>

                                
                            </div>

                            <div class="mt-6">
                                <!-- Configuramos Alpine y llamamos a la API de países al iniciar -->
                                <div class="grid gap-4 sm:grid-cols-3">
                                    
                                    <!-- Alpine actualizará automáticamente el 'value' con el formato SV-US-Berlín -->
                                    <input type="hidden" name="ubicacion" :value="ubicacionFormateada">

                                    <!-- 1. Selector de País -->
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">País</label>
                                        <select 
                                            x-model="paisSeleccionado" 
                                            @change="cargarEstados()"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >
                                            <option value="">Selecciona un país...</option>
                                            <template x-for="pais in paises" :key="pais.iso2">
                                                <!-- Guardamos el código ISO2 (Ej. SV) como valor -->
                                                <option :value="pais.iso2" x-text="pais.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- 2. Selector de Estado/Departamento -->
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Departamento</label>
                                        <select 
                                            x-model="estadoSeleccionado" 
                                            @change="cargarCiudades()"
                                            :disabled="!paisSeleccionado"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >
                                            <option value="">Selecciona un depto...</option>
                                            <template x-for="estado in estados" :key="estado.iso2">
                                                <!-- Guardamos el código ISO2 del estado (Ej. US para Usulután) -->
                                                <option :value="estado.iso2" x-text="estado.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <!-- 3. Selector de Ciudad -->
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Ciudad</label>
                                        <select 
                                            x-model="ciudadSeleccionada"
                                            :disabled="!estadoSeleccionado"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >
                                            <option value="">Selecciona una ciudad...</option>
                                            <template x-for="ciudad in ciudades" :key="ciudad.name">
                                                <!-- La mayoría de APIs no dan código ISO para ciudades, así que usamos el nombre (Ej. Berlin) -->
                                                <option :value="ciudad.name" x-text="ciudad.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="mt-8 flex justify-end">

                                <button
                                    type="button"
                                    @click="next()"
                                    class="rounded-xl bg-sidan-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600"
                                >
                                    Continuar →
                                </button>

                            </div>

                        </section>


                        {{-- Paso 2 --}}
                        <section id="register-step-2" x-show="step === 2" x-cloak class="p-6 sm:p-8">

                            <div class="mb-8">

                                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                                    Paso 02
                                </p>

                                <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">
                                    Credenciales y acceso
                                </h2>

                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    Si no desea cambiar la contraseña, deje los campos en blanco.
                                </p>

                            </div>


                            <div class="space-y-5">

                                <div class="grid gap-5 sm:grid-cols-2">

                                    <div>
                                        <label for="email" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                            Correo electrónico *
                                        </label>

                                        <input
                                            id="email"
                                            name="email"
                                            type="email"
                                            x-model="email"
                                            value="{{ old('email') }}"
                                            placeholder="ejemplo@ejemplo.com"
                                            required
                                            @input="clearError('email')"
                                            autocomplete="email"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >

                                        @error('email')
                                            <p
                                                x-show="serverErrors.email"
                                                class="mt-2 text-sm font-semibold text-red-600"
                                            >
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                </div>


                                <div class="grid gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="password" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                            Contraseña
                                        </label>

                                        <input
                                            id="password"
                                            name="password"
                                            type="password"
                                            x-model="password"
                                            placeholder="********"
                                            @input="validatePasswordMatch()"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >

                                    </div>

                                    <div>
                                        <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                            Confirmar contraseña
                                        </label>

                                        <input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            x-model="passwordConfirmation"
                                            placeholder="********"
                                            @input="validatePasswordMatch()"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >

                                    </div>
                                </div>

                                {{-- Rol --}}
                                <div class="grid gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="rol" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                            Rol *
                                        </label>

                                        <select
                                            id="rol"
                                            name="rol"
                                            x-model="rol"
                                            required
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >
                                            <option value="">Selecciona un rol...</option>
                                            @foreach($roles as $r)
                                                <option value="{{ $r->id_rol }}">
                                                    {{ ucfirst($r->nombre) }} - {{ $r->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('rol')
                                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mt-8 flex items-center justify-between">

                                <button
                                    type="button"
                                    @click="previous()"
                                    class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
                                >
                                    ← Atrás
                                </button>

                                <button
                                    type="button"
                                    @click="next()"
                                    class="rounded-xl bg-sidan-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600"
                                >
                                    Continuar →
                                </button>

                            </div>

                        </section>


                        {{-- Paso 3 --}}
                        <section id="register-step-3" x-show="step === 3" x-cloak class="p-6 sm:p-8">

                            <div class="mb-8">

                                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                                    Paso 03
                                </p>

                                <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">
                                    Revisa la información
                                </h2>

                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                    Comprueba que todo esté correcto antes de actualizar el usuario.
                                </p>

                            </div>


                            <div class="space-y-4">

                                <div class="rounded-2xl bg-slate-50 p-5 dark:bg-white/5">

                                    <div class="flex items-center justify-between">
                                        <h3 class="font-black text-sidan-900 dark:text-white">
                                            Datos personales
                                        </h3>

                                        <button
                                            type="button"
                                            @click="step = 1"
                                            class="text-sm font-bold text-sidan-500 hover:text-green-600"
                                        >
                                            Editar
                                        </button>
                                    </div>

                                    <div class="mt-5 grid gap-4 text-sm sm:grid-cols-2">

                                        <div>
                                            <span class="text-slate-400">
                                                Nombre completo
                                            </span>
                                            <p
                                                class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                                x-text="fullName()"
                                            ></p>
                                        </div>

                                        <!-- Se oculta si 'documento' está vacío -->
                                        <div x-show="documento">
                                            <span class="text-slate-400">
                                                Documento
                                            </span>
                                            <p
                                                class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                                x-text="documento"
                                            ></p>
                                        </div>

                                        <!-- Se oculta si la función no retorna un teléfono válido -->
                                        <div x-show="telefonoFormateado()">
                                            <span class="text-slate-400">
                                                Teléfono
                                            </span>
                                            <p
                                                class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                                x-text="telefonoFormateado()"
                                            ></p>
                                        </div>

                                        <!-- Se oculta si 'fechaNacimiento' está vacía -->
                                        <div x-show="fechaNacimiento">
                                            <span class="text-slate-400">
                                                Fecha de nacimiento
                                            </span>
                                            <p
                                                class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                                x-text="fechaNacimiento"
                                            ></p>
                                        </div>

                                        <!-- Se oculta si la función no retorna un género válido -->
                                        <div x-show="generoTexto()">
                                            <span class="text-slate-400">
                                                Género
                                            </span>
                                            <p
                                                class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                                x-text="generoTexto()"
                                            ></p>
                                        </div>

                                        <div x-show="ubicacionAmigable">
                                            <span class="text-slate-400">
                                                Ubicación
                                            </span>

                                            <p
                                                class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                                x-text="ubicacionAmigable"
                                            ></p>
                                        </div>
                                    </div>
                                </div>


                                <div class="rounded-2xl bg-slate-50 p-5 dark:bg-white/5">

                                    <div class="flex items-center justify-between">

                                        <h3 class="font-black text-sidan-900 dark:text-white">
                                            Cuenta
                                        </h3>

                                        <button
                                            type="button"
                                            @click="step = 2"
                                            class="text-sm font-bold text-sidan-500 hover:text-green-600"
                                        >
                                            Editar
                                        </button>

                                    </div>

                                    <div class="text-sm">                                      
                                        <div class="mt-4">

                                            <span class="text-sm text-slate-400">
                                                Correo electrónico
                                            </span>

                                            <p
                                                class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                                x-text="email"
                                            ></p>

                                        </div>

                                        <div class="mt-4">

                                            <span class="text-sm text-slate-400">
                                                Contraseña
                                            </span>
                                            
                                            <p class="mt-1 font-bold text-slate-700 dark:text-slate-200" x-text="password ? 'Actualizada' : 'No modificada'"></p>
                                        </div>

                                        <div class="mt-4">

                                            <span class="text-sm text-slate-400">
                                                Rol
                                            </span>

                                            <p
                                                class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                                x-text="obtenerTextoRol()"
                                            ></p>

                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="mt-8 flex items-center justify-between">

                                <button
                                    type="button"
                                    @click="previous()"
                                    class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
                                >
                                    ← Atrás
                                </button>

                                <button
                                    type="submit"
                                    class="rounded-xl bg-sidan-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600"
                                >
                                    Actualizar usuario
                                </button>

                            </div>

                        </section>

                    </div>

                </form>

                <div class="my-7 flex items-center gap-4">

                    <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>

                    <span class="text-xs font-semibold text-slate-400">
                        SIDAN
                    </span>

                    <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>

                </div>

                <span class="text-xs font-semibold text-slate-400">
                    <a href="{{ route('admin.users.index') }}" class="flex w-fit mx-auto items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
                        Cancelar
                    </a>
                </span>
            </main>
        </main>
    </div>
</div>
    <script>
        function registerForm() {
            return {
                // ==========================================
                // 1. VARIABLES ORIGINALES DEL FORMULARIO
                // ==========================================
                step: {{
                    $errors->hasAny(['documento', 'telefono'])
                        ? 1
                        : ($errors->hasAny([
                            'email',
                            'password',
                            'password_confirmation'
                        ]) ? 2 : 1)
                }},

                serverErrors: {
                    documento: {{ $errors->has('documento') ? 'true' : 'false' }},
                    telefono: {{ $errors->has('telefono') ? 'true' : 'false' }},
                    email: {{ $errors->has('email') ? 'true' : 'false' }},
                },

                nombres: @js(old('nombres', $user->informacion_personal->nombres ?? '')),
                apellidos: @js(old('apellidos', $user->informacion_personal->apellidos ?? '')),
                documento: @js(old('documento', $user->informacion_personal->documento ?? '')),
                telefono: @js(old('telefono', $user->informacion_personal->telefono ?? '')),
                fechaNacimiento: @js(old('fecha_nacimiento', $user->informacion_personal->fecha_nacimiento ?? '')),
                genero: @js(old('genero', $user->informacion_personal->genero ?? '')),
                email: @js(old('email', $user->correo ?? '')),
                rol: @js(old('rol', $user->id_rol ?? '')),

                password: '',
                passwordConfirmation: '',

                imagenPreview: @js($user->imagen_perfil ? asset('storage/' . $user->imagen_perfil) : asset('images/usuario.png')),
                eliminarImagenPerfil: false,
                tieneImagenPerfil: @js(!empty($user->imagen_perfil)),
                imagenParaRecortar: null,
                cropper: null,
                editorImagenAbierto: false,

                // ==========================================
                // 2. VARIABLES DE UBICACIÓN
                // ==========================================
                oldUbicacion: @js(old('ubicacion', $user->informacion_personal->ubicacion ?? '')),
                _oldPais: '',
                _oldEstado: '',
                _oldCiudad: '',

                paises: [],
                estados: [],
                ciudades: [],
                paisSeleccionado: '',
                estadoSeleccionado: '',
                ciudadSeleccionada: '',
                baseUrl: @js(url('/ubicaciones')),

                // ==========================================
                // 3. PROPIEDADES COMPUTADAS (GETTERS)
                // ==========================================
                get ubicacionFormateada() {
                    if (this.ciudadSeleccionada && this.estadoSeleccionado && this.paisSeleccionado) {
                        return `${this.ciudadSeleccionada}-${this.estadoSeleccionado}-${this.paisSeleccionado}`;
                    }
                    return '';
                },

                get ubicacionAmigable() {
                    if (this.ciudadSeleccionada && this.estadoSeleccionado && this.paisSeleccionado) {
                        let paisObj = this.paises.find(p => p.iso2 === this.paisSeleccionado);
                        let estadoObj = this.estados.find(e => e.iso2 === this.estadoSeleccionado);
                        
                        let nombrePais = paisObj ? paisObj.name : '';
                        let nombreEstado = estadoObj ? estadoObj.name : '';
                        
                        return `${this.ciudadSeleccionada}, ${nombreEstado}, ${nombrePais}`;
                    }
                    return '';
                },

                // Funciones para manejar la imagen de perfil.
                cambiarImagen(event) {
                    const input = event.target;
                    const archivo = input.files?.[0];

                    if (!archivo) {
                        return;
                    }

                    const tiposPermitidos = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    if (!tiposPermitidos.includes(archivo.type)) {
                        input.value = '';

                        this.showAlert(
                            'Imagen no válida',
                            'Selecciona una imagen en formato JPG, PNG o WebP.'
                        );

                        return;
                    }

                    const maxSize = 5 * 1024 * 1024;

                    if (archivo.size > maxSize) {
                        input.value = '';

                        this.showAlert(
                            'Imagen demasiado grande',
                            'La imagen no puede superar los 5 MB.'
                        );

                        return;
                    }

                    this.imagenParaRecortar = URL.createObjectURL(archivo);
                    this.abrirEditorImagen();
                },
                
                // Función para quitar la imagen de perfil.
                quitarImagenPerfil() {
                    this.eliminarImagenPerfil = true;
                    this.tieneImagenPerfil = false;

                    if (this.$refs.imagenPerfil) {
                        this.$refs.imagenPerfil.value = '';
                    }

                    this.imagenPreview = @js(asset('images/usuario.png'));
                },

                // Función para abrir el editor de imagen.
                abrirEditorImagen() {
                    this.editorImagenAbierto = true;
                },

                // Funciones para inicializar y manejar el cropper de la imagen.
                inicializarCropper() {
                    if (!this.editorImagenAbierto) {
                        return;
                    }

                    if (!this.$refs.imagenCropper) {
                        return;
                    }

                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }

                    this.cropper = new Cropper(this.$refs.imagenCropper, {
                        aspectRatio: 1,
                        viewMode: 1,

                        dragMode: 'move',

                        autoCropArea: 0.85,

                        responsive: true,
                        restore: false,

                        background: false,
                        guides: true,
                        center: true,
                        highlight: false,

                        movable: true,
                        zoomable: true,
                        zoomOnTouch: true,
                        zoomOnWheel: true,

                        cropBoxMovable: false,
                        cropBoxResizable: false,

                        toggleDragModeOnDblclick: false,
                    });
                },

                aplicarRecorteImagen() {
                    if (!this.cropper) {
                        return;
                    }

                    const canvas = this.cropper.getCroppedCanvas({
                        width: 512,
                        height: 512,

                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    canvas.toBlob((blob) => {
                        if (!blob) {
                            return;
                        }

                        const archivoRecortado = new File(
                            [blob],
                            'perfil.jpg',
                            {
                                type: 'image/jpeg',
                                lastModified: Date.now(),
                            }
                        );

                        const dataTransfer = new DataTransfer();

                        dataTransfer.items.add(archivoRecortado);

                        this.$refs.imagenPerfil.files = dataTransfer.files;

                        // Actualizamos el preview principal.
                        this.imagenPreview = URL.createObjectURL(blob);

                        this.eliminarImagenPerfil = false;
                        this.tieneImagenPerfil = true;

                        this.cerrarEditorImagen();
                    }, 'image/jpeg', 0.9);
                },

                cancelarRecorteImagen() {
                    // La imagen seleccionada no se enviará.
                    if (this.$refs.imagenPerfil) {
                        this.$refs.imagenPerfil.value = '';
                    }

                    this.cerrarEditorImagen();
                },

                cerrarEditorImagen() {
                    this.editorImagenAbierto = false;

                    if (this.cropper) {
                        this.cropper.destroy();
                        this.cropper = null;
                    }

                    if (this.imagenParaRecortar) {
                        URL.revokeObjectURL(this.imagenParaRecortar);
                        this.imagenParaRecortar = null;
                    }
                },

                acercarImagen() {
                    if (!this.cropper) {
                        return;
                    }

                    this.cropper.zoom(0.1);
                },

                alejarImagen() {
                    if (!this.cropper) {
                        return;
                    }

                    this.cropper.zoom(-0.1);
                },

                // ==========================================
                // 4. MÉTODOS DEL FORMULARIO
                // ==========================================
                next() {
                    if (this.validateCurrentStep()) {
                        this.step++;
                    }
                },

                previous() {
                    if (this.step > 1) {
                        this.step--;
                    }
                },

                validateCurrentStep() {
                    const section = document.getElementById(`register-step-${this.step}`);
                    if (!section) return false;

                    if (this.step === 2) {
                        const password = document.getElementById('password');
                        const confirmation = document.getElementById('password_confirmation');
                        confirmation.setCustomValidity('');

                        if (password.value !== confirmation.value) {
                            confirmation.setCustomValidity('Las contraseñas no coinciden.');
                            confirmation.reportValidity();
                            confirmation.focus();
                            return false;
                        }
                    }

                    const fields = section.querySelectorAll('input, select, textarea');
                    for (const field of fields) {
                        if (!field.checkValidity()) {
                            field.reportValidity();
                            field.focus();
                            return false;
                        }
                    }
                    return true;
                },

                validatePasswordMatch() {
                    const password = document.getElementById('password');
                    const confirmation = document.getElementById('password_confirmation');
                    confirmation.setCustomValidity('');

                    if (confirmation.value && password.value !== confirmation.value) {
                        confirmation.setCustomValidity('Las contraseñas no coinciden.');
                    }
                },

                clearError(field) {
                    this.serverErrors[field] = false;
                },

                shakeDocumento() {
                    const input = document.getElementById('documento');
                    if (!input) return;

                    input.animate([
                        { transform: 'translateX(-1px)' },
                        { transform: 'translateX(2px)' },
                        { transform: 'translateX(-3px)' },
                        { transform: 'translateX(3px)' },
                        { transform: 'translateX(0)' }
                    ], { duration: 300, easing: 'cubic-bezier(0.36, 0.07, 0.19, 0.97)' });
                },

                async submitForm(event) {
                    if (!this.validateCurrentStep()) {
                        event.preventDefault();
                        return;
                    }

                    event.preventDefault();

                    const confirmado = await this.showConfirm(
                        '¿Actualizar usuario?',
                        'Verifica que toda la información proporcionada sea correcta antes de actualizar el usuario.'
                    );

                    if (confirmado) {
                        event.target.submit();
                    }
                },

                async showConfirm(title, message) {
                    return new Promise((resolve) => {
                        const overlay = document.createElement('div');

                        overlay.className =
                            'fixed inset-0 z-[9999] bg-black/50 p-4 backdrop-blur-sm';

                        overlay.innerHTML = `
                            <div class="absolute inset-0 flex items-center justify-center lg:left-64">
                                <div
                                    class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-sidan-900"
                                    role="dialog"
                                    aria-modal="true"
                                >
                                    <div class="p-6">
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-100 text-sidan-500 dark:bg-green-500/10">
                                                <svg
                                                    class="h-6 w-6"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <path
                                                        d="M12 9v4m0 4h.01"
                                                        stroke-linecap="round"
                                                    />
                                                    <circle cx="12" cy="12" r="9" />
                                                </svg>
                                            </div>

                                            <div>
                                                <h3 class="text-lg font-black text-sidan-900 dark:text-white">
                                                    ${title}
                                                </h3>

                                                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                                    ${message}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-6 flex justify-end gap-3">
                                            <button
                                                type="button"
                                                data-cancel
                                                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
                                            >
                                                Cancelar
                                            </button>

                                            <button
                                                type="button"
                                                data-confirm
                                                class="rounded-xl bg-sidan-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-green-500/20 transition hover:bg-green-600"
                                            >
                                                Actualizar usuario
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        document.getElementById('edit-profile-container').appendChild(overlay);

                        const cancelButton = overlay.querySelector('[data-cancel]');
                        const confirmButton = overlay.querySelector('[data-confirm]');

                        const close = (value) => {
                            cancelButton.disabled = true;
                            confirmButton.disabled = true;

                            overlay.remove();

                            resolve(value);
                        };

                        cancelButton.addEventListener('click', () => {
                            close(false);
                        });

                        confirmButton.addEventListener('click', () => {
                            close(true);
                        });

                        overlay.addEventListener('click', (event) => {
                            if (event.target === overlay) {
                                close(false);
                            }
                        });

                        const handleKeydown = (event) => {
                            if (event.key === 'Escape') {
                                close(false);
                            }

                            if (event.key === 'Enter') {
                                close(true);
                            }
                        };

                        document.addEventListener('keydown', handleKeydown);
                    });
                },

                fullName() {
                    return `${this.nombres} ${this.apellidos}`.trim();
                },

                generoTexto() {
                    if (this.genero === 'M') return 'Masculino';
                    if (this.genero === 'F') return 'Femenino';
                    return '';
                },

                telefonoFormateado() {
                    if (!this.telefono) return '';
                    if (!window.telefonoIti) return this.telefono;
                    return window.telefonoIti.getNumber() || this.telefono;
                },

                obtenerTextoRol() {
                    if (!this.rol) {
                        return 'No seleccionado';
                    }

                    const select = document.getElementById('rol');
                    
                    if (select && select.selectedIndex > 0) {
                        return select.options[select.selectedIndex].text;
                    }

                    return '';
                },

                // ==========================================
                // 5. MÉTODOS DE UBICACIÓN
                // ==========================================
                cargarPaises() {
                    // Restaurar ubicación anterior.
                    if (this.oldUbicacion) {
                        const partes = this.oldUbicacion.split('-');

                        if (partes.length === 3) {
                            this._oldCiudad = partes[0];
                            this._oldEstado = partes[1];
                            this._oldPais = partes[2];
                        }
                    }

                    fetch(`${this.baseUrl}/paises`)
                        .then(res => {
                            if (!res.ok) throw new Error('Error al cargar países');
                            return res.json();
                        })
                        .then(data => {
                            this.paises = data;

                            this.$nextTick(() => {
                                // Mantener país anterior o El Salvador por defecto.
                                this.paisSeleccionado = this._oldPais || 'SV';
                                this.cargarEstados();
                            });
                        })
                        .catch(error => {
                            console.error('No fue posible cargar los países:', error);
                        });
                },

                cargarEstados() {
                    this.estados = [];
                    this.ciudades = [];
                    this.estadoSeleccionado = '';
                    this.ciudadSeleccionada = '';

                    if (this.paisSeleccionado) {

                        const pais = encodeURIComponent(this.paisSeleccionado);

                        fetch(`${this.baseUrl}/estados/${pais}`)
                            .then(res => {
                                if (!res.ok) throw new Error('Error al cargar estados');
                                return res.json();
                            })
                            .then(data => {
                                this.estados = data;

                                if (this._oldEstado) {
                                    this.$nextTick(() => {
                                        this.estadoSeleccionado = this._oldEstado;
                                        this._oldEstado = '';
                                        this.cargarCiudades();
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('No fue posible cargar los estados:', error);
                            });
                    }
                },

                cargarCiudades() {
                    this.ciudades = [];
                    this.ciudadSeleccionada = '';

                    if (this.paisSeleccionado && this.estadoSeleccionado) {

                        const pais = encodeURIComponent(this.paisSeleccionado);
                        const estado = encodeURIComponent(this.estadoSeleccionado);

                        fetch(`${this.baseUrl}/ciudades/${pais}/${estado}`)
                            .then(res => {
                                if (!res.ok) throw new Error('Error al cargar ciudades');
                                return res.json();
                            })
                            .then(data => {
                                this.ciudades = data;

                                if (this._oldCiudad) {
                                    this.$nextTick(() => {
                                        this.ciudadSeleccionada = this._oldCiudad;
                                        this._oldCiudad = '';
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('No fue posible cargar las ciudades:', error);
                            });
                    }
                }
            };
        }
    </script>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    @endpush
@endsection