@extends('layouts.navbars')

@section('title', 'SIDAN | Mi perfil')

@push('styles')
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css"
    >
@endpush

@section('content')
    <x-status-alert />
    
    <div id="edit-profile-container" class="relative min-h-full">
        <div
            x-data="profileForm()"
            x-init="cargarPaises()"
            class="flex w-full flex-col bg-[#f6f8fb] dark:bg-sidan-950"
        >
            <main class="mx-auto w-full max-w-4xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
                {{-- Encabezado --}}
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="text-3xl font-black tracking-tight text-sidan-900 sm:text-4xl dark:text-white">
                        Mi perfil
                    </h1>

                    <p class="mt-3 text-slate-600 dark:text-slate-400">
                        Administra tu información personal, datos de cuenta y contraseña.
                    </p>
                </div>

                {{-- Formulario de Actualización --}}
                <form
                    action="{{ route('profile.update') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="mx-auto mt-10 max-w-3xl"
                    @submit="submitForm"
                >
                    @csrf
                    @method('PATCH')
                    {{-- Foto de perfil y nombre completo --}}
                    <div class="mb-8 overflow-hidden rounded-2xl bg-white border border-slate-200 shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="px-6 py-8 sm:px-8">
                            <div class="flex flex-col items-center text-center">
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

                                {{-- Nombre completo --}}
                                <h1 class="mt-5 text-2xl font-black text-sidan-900 dark:text-white">
                                    {{ $user->informacion_personal->nombres }}
                                    {{ $user->informacion_personal->apellidos }}
                                </h1>

                                {{-- Correo --}}
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->correo }}
                                </p>

                                <p class="mt-3 text-xs text-gray-400">
                                    Haz clic en la cámara para cambiar tu foto de perfil.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Información del usuario --}}
                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        {{-- Encabezado --}}
                        <div class="border-b border-slate-200 p-6 sm:p-8 dark:border-white/10">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                                Información personal
                            </p>

                            <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">
                                Datos personales
                            </h2>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                Mantén actualizada tu información personal.
                            </p>
                        </div>

                        {{-- Información personal --}}
                        <div class="p-6 sm:p-8">
                            {{-- Datos personales --}}
                            <div class="grid gap-5 sm:grid-cols-2">
                                {{-- Nombres --}}
                                <div>
                                    <label for="nombres" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Nombres
                                    </label>

                                    <input
                                        id="nombres"
                                        name="nombres"
                                        type="text"
                                        value="{{ old('nombres', $user->informacion_personal->nombres ?? '') }}"
                                        required
                                        autocomplete="given-name"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('nombres')
                                        <p class="mt-2 text-sm font-semibold text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>

                                {{-- Apellidos --}}
                                <div>
                                    <label for="apellidos" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Apellidos
                                    </label>

                                    <input
                                        id="apellidos"
                                        name="apellidos"
                                        type="text"
                                        value="{{ old('apellidos', $user->informacion_personal->apellidos ?? '') }}"
                                        required
                                        autocomplete="family-name"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('apellidos')
                                        <p class="mt-2 text-sm font-semibold text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- DUI --}}
                                <div>
                                    <label for="documento" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Documento de identidad (DUI)
                                    </label>

                                    <input
                                        id="documento"
                                        name="documento"
                                        type="text"
                                        value="{{ old('documento', $user->informacion_personal->documento ?? '') }}"
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
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('documento')
                                        <p x-show="serverErrors.documento" class="mt-2 text-sm font-semibold text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Teléfono --}}
                                <div>
                                    <label for="telefono" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Teléfono
                                    </label>

                                    {{-- Input oculto para enviar el teléfono formateado --}}
                                    <input type="hidden" name="telefono" :value="telefonoFormateado()">

                                    <input
                                        id="telefono"
                                        type="tel"
                                        value="{{ old('telefono', $user->informacion_personal->telefono ?? '') }}"
                                        autocomplete="tel"
                                        @input="clearError('telefono')"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('telefono')
                                        <p x-show="serverErrors.telefono" class="mt-2 text-sm font-semibold text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Fecha de nacimiento --}}
                                <div>
                                    <label for="fecha_nacimiento" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Fecha de nacimiento
                                    </label>

                                    <input
                                        id="fecha_nacimiento"
                                        name="fecha_nacimiento"
                                        type="date"
                                        value="{{ old('fecha_nacimiento', $user->informacion_personal->fecha_nacimiento ?? '') }}"
                                        max="{{ now()->subYears(18)->format('Y-m-d') }}"
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >

                                    @error('fecha_nacimiento')
                                        <p class="mt-2 text-sm font-semibold text-red-600">
                                            {{ $message }}
                                        </p>
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
                                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    >
                                        <option value="">
                                            Selecciona una opción
                                        </option>

                                        <option value="M" @selected(old('genero', $user->informacion_personal->genero ?? '') === 'M')>
                                            Masculino
                                        </option>

                                        <option value="F" @selected(old('genero', $user->informacion_personal->genero ?? '') === 'F')>
                                            Femenino
                                        </option>
                                    </select>

                                    @error('genero')
                                        <p class="mt-2 text-sm font-semibold text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Ubicación --}}
                            <div class="mt-6">
                                {{-- Input oculto para enviar la ubicación --}}
                                <input type="hidden" name="ubicacion" :value="ubicacionFormateada">

                                {{-- Selectores de país, departamento y ciudad --}}
                                <div class="grid gap-4 sm:grid-cols-3">
                                    {{-- País --}}
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                            País
                                        </label>

                                        <select
                                            x-model="paisSeleccionado"
                                            @change="cargarEstados()"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >
                                            <option value="">
                                                Selecciona un país...
                                            </option>

                                            <template x-for="pais in paises" :key="pais.iso2">
                                                <option :value="pais.iso2" x-text="pais.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Departamento --}}
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                            Departamento
                                        </label>

                                        <select
                                            x-model="estadoSeleccionado"
                                            @change="cargarCiudades()"
                                            :disabled="!paisSeleccionado"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >
                                            <option value="">
                                                Selecciona un depto...
                                            </option>

                                            <template x-for="estado in estados" :key="estado.iso2">
                                                <option
                                                    :value="estado.iso2"
                                                    x-text="estado.name"
                                                ></option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Ciudad --}}
                                    <div>
                                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                            Ciudad
                                        </label>

                                        <select x-model="ciudadSeleccionada" :disabled="!estadoSeleccionado" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 disabled:opacity-50 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                            <option value="">
                                                Selecciona una ciudad...
                                            </option>

                                            <template x-for="ciudad in ciudades" :key="ciudad.name">
                                                <option
                                                    :value="ciudad.name"
                                                    x-text="ciudad.name"
                                                ></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Cuenta --}}
                    <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="border-b border-slate-200 p-6 sm:p-8 dark:border-white/10">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                                Cuenta
                            </p>

                            <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">
                                Datos de acceso
                            </h2>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                Administra el correo electrónico asociado a tu cuenta.
                            </p>
                        </div>
                    
                        {{-- Correo electrónico --}}
                        <div class="p-6 sm:p-8">
                            <div>
                                <label for="email" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Correo electrónico
                                </label>

                                {{-- Usuario Google --}}
                                <template x-if="tieneGoogle && !tienePassword">
                                    <div>
                                        <input
                                            type="hidden"
                                            name="email"
                                            value="{{ $user->correo }}"
                                        >

                                        <div class="relative">
                                            <input
                                                id="email"
                                                type="email"
                                                value="{{ $user->correo }}"
                                                disabled
                                                class="block w-full cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 pr-11 text-sm text-slate-500 outline-none opacity-80 dark:border-white/10 dark:bg-white/5 dark:text-slate-400"
                                            >

                                            {{-- Candado --}}
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    class="h-5 w-5 text-slate-400"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                >
                                                    <rect
                                                        x="5"
                                                        y="11"
                                                        width="14"
                                                        height="9"
                                                        rx="2"
                                                    />
                                                    <path d="M8 11V7a4 4 0 018 0v4" />
                                                </svg>
                                            </div>
                                        </div>

                                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                            Establece una contraseña de SIDAN para poder cambiar tu correo electrónico.
                                        </p>
                                    </div>
                                </template>

                                {{-- Usuario normal --}}
                                <template x-if="!tieneGoogle || tienePassword">
                                    <div>
                                        <input
                                            id="email"
                                            name="email"
                                            type="email"
                                            value="{{ old('email', $user->correo ?? '') }}"
                                            required
                                            autocomplete="email"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >

                                        @error('email')
                                            <p class="mt-2 text-sm font-semibold text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Cuenta de Google --}}
                        <div class="border-t border-slate-200 p-6 sm:p-8 dark:border-white/10">
                            @if ($user->googleAccount)
                                {{-- Google vinculado --}}
                                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                                Cuenta de Google
                                            </h3>

                                            <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-700 dark:bg-green-500/10 dark:text-green-400">
                                                Vinculada
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                            {{ $user->googleAccount->correo_google }}
                                        </p>

                                        @if (is_null($user->password_hash))
                                            <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                                                Establece una contraseña de SIDAN antes de desvincular Google.
                                            </p>
                                        @endif
                                    </div>

                                    <button
                                        type="button"
                                        @if (is_null($user->password_hash))
                                            disabled
                                        @else
                                            @click="confirmarDesvinculacionGoogle()"
                                        @endif
                                        class="shrink-0 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-500/20 dark:bg-white/5 dark:text-red-400 dark:hover:bg-red-500/10"
                                    >
                                        Desvincular Google
                                    </button>
                                </div>
                            @else
                                {{-- Google no vinculado --}}
                                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                                Cuenta de Google
                                            </h3>

                                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                                No vinculada
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                            Vincula una cuenta de Google para poder iniciar sesión con ella.
                                        </p>
                                    </div>

                                    <a
                                        href="{{ route('profile.google.link') }}"
                                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-sidan-500 hover:bg-green-50 hover:text-sidan-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:border-sidan-500 dark:hover:bg-green-500/10 dark:hover:text-green-400"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path d="M21.35 11.1h-9.18v3.71h5.27c-.23 1.2-.91 2.22-1.94 2.9v2.41h3.14c1.84-1.69 2.9-4.19 2.9-7.12 0-.66-.06-1.3-.19-1.9Z"/>
                                            <path d="M12.17 20.62c2.63 0 4.84-.87 6.46-2.36l-3.14-2.41c-.87.58-1.98.92-3.32.92-2.54 0-4.7-1.71-5.47-4.01H3.46v2.49a9.76 9.76 0 0 0 8.71 5.37Z"/>
                                            <path d="M6.7 12.76a5.86 5.86 0 0 1 0-3.75V6.52H3.46a9.77 9.77 0 0 0 0 8.73l3.24-2.49Z"/>
                                            <path d="M12.17 4.99c1.43 0 2.72.49 3.73 1.45l2.8-2.79C17 2.07 14.8 1.14 12.17 1.14a9.76 9.76 0 0 0-8.71 5.38L6.7 9.01c.77-2.3 2.93-4.02 5.47-4.02Z"/>
                                        </svg>

                                        Vincular con Google
                                    </a>

                                </div>
                            @endif

                        </div>
                    </section>

                    {{-- Contraseña --}}
                    <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">

                        <div class="border-b border-slate-200 p-6 sm:p-8 dark:border-white/10">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                                Seguridad
                            </p>

                            <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">
                                {{ is_null($user->password_hash)
                                    ? 'Establecer contraseña'
                                    : 'Cambiar contraseña' }}
                            </h2>

                            @if (is_null($user->password_hash))
                                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                    Aún no tienes una contraseña de SIDAN.
                                    Establece una para poder iniciar sesión también con tu correo electrónico y contraseña.
                                </p>
                            @else
                                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                    Por seguridad, te enviaremos un enlace a tu correo electrónico para cambiar tu contraseña.
                                </p>
                            @endif
                        </div>

                        <div class="p-6 sm:p-8">
                            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

                                <div>
                                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                        {{ is_null($user->password_hash)
                                            ? 'Contraseña no establecida'
                                            : 'Contraseña configurada' }}
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                                        El enlace será enviado a
                                        <span class="font-bold text-slate-700 dark:text-slate-200">
                                            {{ $user->correo }}
                                        </span>
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    @click="solicitarCambioPassword()"
                                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600"
                                >
                                    @if (is_null($user->password_hash))
                                        Establecer contraseña
                                    @else
                                        Cambiar contraseña
                                    @endif
                                </button>
                            </div>
                        </div>
                    </section>

                    {{-- Botones --}}
                    <div class="mt-8 flex items-center justify-end gap-3">
                        <a href="{{ route('welcome') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
                            Cancelar
                        </a>

                        <button type="submit" class="rounded-xl bg-sidan-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600">
                            Guardar cambios
                        </button>
                    </div>
                </form>
                
                {{-- Formulario para solicitar el cambio de contraseña --}}
                <form
                    id="password-link-form"
                    action="{{ route('profile.password.email') }}"
                    method="POST"
                    class="hidden"
                >
                    @csrf
                </form>

                {{-- Formulario para desvincular Google --}}
                @if ($user->googleAccount && !is_null($user->password_hash))
                    <form
                        id="unlink-google-form"
                        action="{{ route('profile.google.unlink') }}"
                        method="POST"
                        class="hidden"
                    >
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </main>
        </div>
    </div>

    {{-- Script que maneja el formulario de perfil --}}
    <script>
        function profileForm() {
            return {
                // Errores del servidor (Laravel)
                serverErrors: {
                    documento: {{ $errors->has('documento') ? 'true' : 'false' }},
                    telefono: {{ $errors->has('telefono') ? 'true' : 'false' }},
                    email: {{ $errors->has('email') ? 'true' : 'false' }},
                },
                
                // Variables del formulario
                nombres: @js(old('nombres', $user->informacion_personal->nombres ?? '')),
                apellidos: @js(old('apellidos', $user->informacion_personal->apellidos ?? '')),
                documento: @js(old('documento', $user->informacion_personal->documento ?? '')),
                telefono: @js(old('telefono', $user->informacion_personal->telefono ?? '')),
                fechaNacimiento: @js(old('fecha_nacimiento', $user->informacion_personal->fecha_nacimiento ?? '')),
                genero: @js(old('genero', $user->informacion_personal->genero ?? '')),
                email: @js(old('email', $user->correo ?? '')),
                tienePassword: @js(!is_null($user->password_hash)),
                tieneGoogle: @js($user->googleAccount()->exists()),
                
                imagenPreview: @js($user->imagen_perfil ? asset('storage/' . $user->imagen_perfil) : asset('images/usuario.png')),
                eliminarImagenPerfil: false,
                tieneImagenPerfil: @js(!empty($user->imagen_perfil)),
                imagenParaRecortar: null,
                cropper: null,
                editorImagenAbierto: false,

                // Variables para la ubicación
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
                apiKey: 'd94a880db2815eabb6a7a7af69fd5abd19c577d0020386e7b0bb04201f1b761a', 
                baseUrl: 'https://api.countrystatecity.in/v1/countries',

                // Funciones para formatear la ubicación.
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

                // Función para solicitar el cambio o establecimiento de contraseña.
                async solicitarCambioPassword() {
                    const tienePassword = @js(!is_null($user->password_hash));

                    const title = tienePassword
                        ? '¿Cambiar contraseña?'
                        : '¿Establecer contraseña?';

                    const message = tienePassword
                        ? 'Te enviaremos un enlace de seguridad a tu correo electrónico para que puedas establecer una nueva contraseña.'
                        : 'Te enviaremos un enlace a tu correo electrónico para que puedas establecer tu contraseña de SIDAN.';

                    const confirmText = tienePassword
                        ? 'Enviar enlace'
                        : 'Establecer contraseña';

                    const confirmado = await this.showConfirm(
                        title,
                        message,
                        confirmText
                    );

                    if (confirmado) {
                        document.getElementById('password-link-form')?.submit();
                    }
                },

                // Función para limpiar errores del servidor
                clearError(field) {
                    this.serverErrors[field] = false;
                },

                // Función para animar el input del documento
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

                // Función para manejar el envío del formulario
                async submitForm(event) {
                    event.preventDefault();

                    const confirmado = await this.showConfirm(
                        '¿Actualizar información?',
                        'Verifica que toda la información proporcionada sea correcta antes de actualizar la información.'
                    );

                    if (confirmado) {
                        event.target.submit();
                    }
                },

                // Función para confirmar la desvinculación de Google.
                async confirmarDesvinculacionGoogle() {
                    const confirmado = await this.showConfirm(
                        '¿Desvincular cuenta de Google?',
                        'Ya no podrás iniciar sesión con esta cuenta de Google. Podrás seguir accediendo a SIDAN con tu correo electrónico y contraseña.',
                        'Desvincular Google'
                    );

                    if (confirmado) {
                        document.getElementById('unlink-google-form')?.submit();
                    }
                },

                // Función para mostrar un modal de confirmación
                async showConfirm(title, message, confirmText = 'Confirmar') {
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
                                                ${confirmText}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        document.getElementById('edit-profile-container').appendChild(overlay);

                        const cancelButton = overlay.querySelector('[data-cancel]');
                        const confirmButton = overlay.querySelector('[data-confirm]');

                        const handleKeydown = (event) => {
                            if (event.key === 'Escape') {
                                close(false);
                            }

                            if (event.key === 'Enter') {
                                close(true);
                            }
                        };

                        const close = (value) => {
                            cancelButton.disabled = true;
                            confirmButton.disabled = true;

                            document.removeEventListener('keydown', handleKeydown);

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

                        document.addEventListener('keydown', handleKeydown);
                    });
                },

                // Función para formatear el teléfono
                telefonoFormateado() {
                    if (!this.telefono) return '';
                    if (!window.telefonoIti) return this.telefono;
                    return window.telefonoIti.getNumber() || this.telefono;
                },

                // Funciones para cargar países, estados y ciudades
                cargarPaises() {
                    // Si existe un valor old(), lo dividimos en sus 3 partes (Ciudad-Estado-País)
                    if (this.oldUbicacion) {
                        const partes = this.oldUbicacion.split('-');
                        if (partes.length === 3) {
                            this._oldCiudad = partes[0];
                            this._oldEstado = partes[1];
                            this._oldPais = partes[2];
                        }
                    }

                    fetch(this.baseUrl, { headers: { 'X-CSCAPI-KEY': this.apiKey } })
                        .then(res => res.json())
                        .then(data => {
                            this.paises = data;
                            
                            this.$nextTick(() => {
                                // Asigna el país antiguo si existe, si no, por defecto 'SV'
                                this.paisSeleccionado = this._oldPais || 'SV';
                                this.cargarEstados();
                            });
                        });
                },

                cargarEstados() {
                    this.estados = [];
                    this.ciudades = [];
                    this.estadoSeleccionado = '';
                    this.ciudadSeleccionada = '';
                    
                    if (this.paisSeleccionado) {
                        fetch(`${this.baseUrl}/${this.paisSeleccionado}/states`, { headers: { 'X-CSCAPI-KEY': this.apiKey } })
                            .then(res => res.json())
                            .then(data => {
                                this.estados = data;
                                
                                // Si hay un estado guardado en old(), lo seleccionamos
                                if (this._oldEstado) {
                                    this.$nextTick(() => {
                                        this.estadoSeleccionado = this._oldEstado;
                                        this._oldEstado = '';
                                        this.cargarCiudades();
                                    });
                                }
                            });
                    }
                },

                cargarCiudades() {
                    this.ciudades = [];
                    this.ciudadSeleccionada = '';

                    if (this.paisSeleccionado && this.estadoSeleccionado) {
                        fetch(`${this.baseUrl}/${this.paisSeleccionado}/states/${this.estadoSeleccionado}/cities`, { headers: { 'X-CSCAPI-KEY': this.apiKey } })
                            .then(res => res.json())
                            .then(data => {
                                this.ciudades = data;
                                
                                // Si hay una ciudad guardada en old(), la seleccionamos
                                if (this._oldCiudad) {
                                    this.$nextTick(() => {
                                        this.ciudadSeleccionada = this._oldCiudad;
                                        this._oldCiudad = '';
                                    });
                                }
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

