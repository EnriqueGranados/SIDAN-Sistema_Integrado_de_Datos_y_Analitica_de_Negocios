@extends('layouts.navbars')

@section('title', 'SIDAN | Mi perfil')

@section('content')
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

                {{-- Formulario --}}
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
                                {{-- Foto de perfil --}}
                                <div class="relative">
                                    <img
                                        x-ref="imagenPreview"
                                        src="{{ $user->imagen_perfil ? asset('storage/' . $user->imagen_perfil) : asset('images/usuario.png') }}"
                                        alt="Foto de perfil"
                                        class="h-32 w-32 rounded-full object-cover ring-4 ring-white shadow-lg dark:ring-sidan-800"
                                    >

                                    {{-- Botón cambiar foto --}}
                                    <button
                                        type="button"
                                        class="absolute bottom-1 right-1 flex h-10 w-10 items-center justify-center rounded-full bg-sidan-500 text-white shadow-lg transition hover:bg-white hover:text-sidan-500 hover:ring-2 hover:ring-sidan-500 focus:outline-none focus:ring-2 focus:ring-sidan-500 focus:ring-offset-2"
                                        @click="$refs.imagenPerfil.click()"
                                        aria-label="Cambiar foto de perfil"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h2l2-3h10l2 3h2a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/>

                                            <circle cx="12" cy="13" r="3"/>
                                        </svg>
                                    </button>

                                    {{-- Input oculto para la imagen --}}
                                    <input
                                        x-ref="imagenPerfil"
                                        type="file"
                                        name="imagen_perfil"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="hidden"
                                        @change="cambiarImagen"
                                    >
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
                        </div>
                    </section>

                    {{-- Contraseña --}}
                    <section class="mt-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="border-b border-slate-200 p-6 sm:p-8 dark:border-white/10">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                                Seguridad
                            </p>

                            <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white" x-text="tienePassword ? 'Cambiar contraseña' : 'Establecer contraseña'"></h2>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400" x-show="tienePassword">
                                Introduce tu contraseña actual y establece una nueva contraseña.
                            </p>

                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400" x-show="!tienePassword">
                                Tu cuenta utiliza Google para iniciar sesión. Puedes establecer una contraseña para habilitar también el inicio de sesión con correo electrónico y contraseña.
                            </p>
                        </div>

                        {{-- Sección de contraseña --}}
                        <div x-ref="passwordSection" class="p-6 sm:p-8">
                            <div class="space-y-5">
                                {{-- Contraseña actual --}}                          
                                <div x-show="tienePassword" x-cloak>

                                    <label for="current_password" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                        Contraseña actual
                                    </label>

                                    <div class="grid gap-5 sm:grid-cols-2">
                                        <input
                                            id="current_password"
                                            name="current_password"
                                            type="password"
                                            placeholder="********"
                                            minlength="8"
                                            x-model="currentPassword"
                                            @input="clearPasswordError('current_password')"
                                            autocomplete="current-password"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >
                                    </div>
                                    
                                    {{-- Error de validación --}}
                                    <p x-show="passwordErrors.current_password" x-text="passwordErrors.current_password" class="mt-2 text-sm font-semibold text-red-600"></p>

                                    @error('current_password')
                                        <p class="mt-2 text-sm font-semibold text-red-600">
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                {{-- Nueva contraseña y confirmación --}}
                                <div class="grid gap-5 sm:grid-cols-2">
                                    {{-- Nueva contraseña --}}
                                    <div>
                                        <label for="password" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                            Nueva contraseña
                                        </label>

                                        <input
                                            id="password"
                                            name="password"
                                            type="password"
                                            x-model="password"
                                            placeholder="********"
                                            @input="clearPasswordError('password')"
                                            autocomplete="new-password"
                                            minlength="8"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >

                                        {{-- Error de Alpine --}}
                                        <p x-show="passwordErrors.password" x-text="passwordErrors.password" class="mt-2 text-sm font-semibold text-red-600"></p>

                                        {{-- Error de Laravel --}}
                                        @error('password')
                                            <p class="mt-2 text-sm font-semibold text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>

                                    {{-- Confirmación --}}
                                    <div>
                                        <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                            Confirmar nueva contraseña
                                        </label>

                                        <input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            minlength="8"
                                            placeholder="********"
                                            x-model="passwordConfirmation"
                                            @input="clearPasswordError('password_confirmation')"
                                            autocomplete="new-password"
                                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                        >

                                        {{-- Error de validación --}}
                                        <p x-show="passwordErrors.password_confirmation" x-text="passwordErrors.password_confirmation" class="mt-2 text-sm font-semibold text-red-600"></p>

                                        {{-- Error de Laravel --}}
                                        @error('password_confirmation')
                                            <p class="mt-2 text-sm font-semibold text-red-600">
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Botones --}}
                    <div class="mt-8 flex items-center justify-end gap-3">
                        <a href="{{ url()->previous() }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
                            Cancelar
                        </a>

                        <button type="submit" class="rounded-xl bg-sidan-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600">
                            Guardar cambios
                        </button>
                    </div>
                </form>

                {{-- Separador --}}
                <div class="my-7 flex items-center gap-4">
                    <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>

                    <span class="text-xs font-semibold text-slate-400">
                        SIDAN
                    </span>

                    <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>
                </div>
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
                currentPassword: '',
                password: '',
                passwordConfirmation: '',

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

                // Funciones para formatear la ubicación
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

                // Funciones para manejar la imagen de perfil
                cambiarImagen(event) {

                    const archivo = event.target.files[0];

                    if (!archivo) {
                        return;
                    }

                    // Tipo de archivo
                    const tiposPermitidos = [
                        'image/jpeg',
                        'image/png',
                        'image/webp'
                    ];

                    if (!tiposPermitidos.includes(archivo.type)) {
                        alert('Selecciona una imagen JPG, PNG o WebP.');
                        event.target.value = '';
                        return;
                    }

                    // Tamaño máximo: 5 MB
                    const maximo = 5 * 1024 * 1024;

                    if (archivo.size > maximo) {
                        alert('La imagen no puede superar los 5 MB.');
                        event.target.value = '';
                        return;
                    }

                    // Preview
                    const url = URL.createObjectURL(archivo);

                    this.$refs.imagenPreview.src = url;
                },

                // Errores de validación para los campos de contraseña
                passwordErrors: {
                    current_password: '',
                    password: '',
                    password_confirmation: '',
                },

                // Función para validar los campos de contraseña
                validatePasswords() {
                    this.passwordErrors = {
                        current_password: '',
                        password: '',
                        password_confirmation: '',
                    };

                    const current = this.currentPassword.trim();
                    const password = this.password.trim();
                    const confirmation = this.passwordConfirmation.trim();

                    // No se está intentando cambiar o establecer contraseña.
                    if (!current && !password && !confirmation) {
                        return true;
                    }

                    let valid = true;

                    if (this.tienePassword && !current) {
                        this.passwordErrors.current_password =
                            'La contraseña actual es obligatoria.';
                        valid = false;
                    }

                    if (!password) {
                        this.passwordErrors.password =
                            'La nueva contraseña es obligatoria.';
                        valid = false;
                    }

                    if (!confirmation) {
                        this.passwordErrors.password_confirmation =
                            'Debes confirmar la nueva contraseña.';
                        valid = false;
                    }

                    if (password && confirmation && password !== confirmation) {
                        this.passwordErrors.password_confirmation =
                            'Las contraseñas no coinciden.';
                        valid = false;
                    }

                    return valid;
                },

                // Función para inicializar el componente y hacer scroll a la sección de contraseña si hay errores del servidor
                init() {
                    const hasPasswordServerError = @js(
                        $errors->has('current_password') ||
                        $errors->has('password') ||
                        $errors->has('password_confirmation')
                    );

                    if (hasPasswordServerError) {
                        this.scrollToPassword();
                    }
                },

                // Función para hacer scroll a la sección de contraseña
                scrollToPassword() {
                    this.$nextTick(() => {
                        this.$refs.passwordSection?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    });
                },

                // Función para limpiar errores de validación para los campos de contraseña
                clearPasswordError(field) {
                    this.passwordErrors[field] = '';    
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

                    if (!this.validatePasswords()) {
                        this.scrollToPassword();
                        return;
                    }

                    const confirmado = await this.showConfirm(
                        '¿Actualizar información?',
                        'Verifica que toda la información proporcionada sea correcta antes de actualizar la información.'
                    );

                    if (confirmado) {
                        event.target.submit();
                    }
                },

                // Función para mostrar un modal de confirmación
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
@endsection