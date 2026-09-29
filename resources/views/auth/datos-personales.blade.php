@extends('layouts.public')

@section('content')

<div
    x-data="registerForm()"
    x-init="cargarPaises()"
    class="flex min-h-screen w-full flex-col bg-[#f6f8fb] dark:bg-sidan-950"
>

    {{-- Header --}}
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-sidan-950/90">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

            {{-- Logo --}}
            <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white shadow-lg shadow-sidan-900/20">S</div>
                <span class="text-xl font-black tracking-tight text-sidan-900 dark:text-white">SIDAN</span>
            </a>

            {{-- Acciones --}}
            <div class="flex items-center gap-2 sm:gap-3">
                {{-- Botón de cambio de tema --}}
                <button type="button" onclick="toggleTheme()" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:-translate-y-0.5 hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200" aria-label="Cambiar tema">
                    <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

        </div>
    </header>


    {{-- Contenido --}}
    <main class="mx-auto max-w-4xl w-full items-center px-4 py-10 sm:px-6 lg:px-8 lg:py-10">

        {{-- Encabezado --}}
        <div class="mx-auto max-w-2xl text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-green-50 text-sidan-500 dark:bg-green-500/10">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M19 8v6M22 11h-6" stroke-linecap="round"/>
                </svg>
            </div>

            <h1 class="mt-5 text-3xl font-black tracking-tight text-sidan-900 sm:text-4xl dark:text-white">
                Completa tu perfil
            </h1>

            <p class="mt-3 text-slate-600 dark:text-slate-400">
                Agrega información adicional para sacarle el máximo provecho a SIDAN. <br class="hidden sm:block">
                <strong>Esta información es opcional</strong> y puedes llenarlo más adelante.
            </p>
        </div>


        {{-- Stepper --}}
        <div class="mx-auto mt-10 max-w-3xl">
            <ol class="flex w-full items-center">
                {{-- Paso 1 --}}
                <li class="flex w-full items-center">
                    <div class="flex items-center">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 text-sm font-black transition" :class="step >= 1 ? 'border-sidan-500 bg-sidan-500 text-white' : 'border-slate-300 bg-white text-slate-400 dark:border-white/20 dark:bg-white/5'">
                            <span x-show="step === 1">1</span>
                            <svg x-show="step > 1" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="ml-3 hidden text-sm font-bold transition sm:block" :class="step >= 1 ? 'text-sidan-500' : 'text-slate-400'">Datos personales</span>
                    </div>
                    <div class="mx-4 h-0.5 flex-1 bg-slate-200 dark:bg-white/10">
                        <div class="h-full bg-sidan-500 transition-all duration-300" :class="step > 1 ? 'w-full' : 'w-0'"></div>
                    </div>
                </li>

                {{-- Paso 2 --}}
                <li class="flex w-full items-center">
                    <div class="flex items-center">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 text-sm font-black transition" :class="step >= 2 ? 'border-sidan-500 bg-sidan-500 text-white' : 'border-slate-300 bg-white text-slate-400 dark:border-white/20 dark:bg-white/5'">
                            <span x-show="step <= 2">2</span>
                            <svg x-show="step > 2" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m5 12 4 4L19 6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="ml-3 hidden text-sm font-bold transition sm:block" :class="step >= 2 ? 'text-sidan-500' : 'text-slate-400'">Contraseña</span>
                    </div>
                    <div class="mx-4 h-0.5 flex-1 bg-slate-200 dark:bg-white/10">
                        <div class="h-full bg-sidan-500 transition-all duration-300" :class="step > 2 ? 'w-full' : 'w-0'"></div>
                    </div>
                </li>

                {{-- Paso 3 --}}
                <li class="flex items-center">
                    <div class="flex items-center">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 text-sm font-black transition" :class="step >= 3 ? 'border-sidan-500 bg-sidan-500 text-white' : 'border-slate-300 bg-white text-slate-400 dark:border-white/20 dark:bg-white/5'">3</span>
                        <span class="ml-3 hidden text-sm font-bold transition sm:block" :class="step >= 3 ? 'text-sidan-500' : 'text-slate-400'">Confirmación</span>
                    </div>
                </li>
            </ol>
        </div>

        <main class="mx-auto w-full max-w-4xl flex-1 px-4 py-10 sm:px-6 lg:px-8 lg:py-10">
            {{-- Formulario --}}
            {{-- Asegúrate de cambiar la ruta a donde guardarás estos datos (ej. route('perfil.guardar')) --}}
            <form method="POST" action="{{ route('perfil.guardar') }}" class="mx-auto mt-8 max-w-3xl" @submit="submitForm">
                @csrf
                {{-- Si es una actualización de datos, podría ser necesario un @method('PUT') --}}

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">

                    {{-- ============================== --}}
                    {{-- PASO 1: DATOS ADICIONALES      --}}
                    {{-- ============================== --}}
                    <section id="register-step-1" x-show="step === 1" x-cloak class="p-6 sm:p-8">
                        <div class="mb-8">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">Paso 01</p>
                            <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">Información adicional</h2>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Siéntete libre de llenar solo lo que desees compartir.</p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            {{-- Documento --}}
                            <div>
                                <label for="documento" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Documento de identidad (DUI)</label>
                                <input id="documento" name="documento" type="text" x-model="documento" value="{{ old('documento') }}" maxlength="10" pattern="[0-9]{8}-[0-9]" placeholder="00000000-0" @input="clearError('documento')" autocomplete="off" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                @error('documento')
                                    <p x-show="serverErrors.documento" class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Teléfono --}}
                            <div>
                                <label for="telefono" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Teléfono</label>
                                <input id="telefono" name="telefono" type="tel" value="{{ old('telefono') }}" autocomplete="tel" @input="clearError('telefono')" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                @error('telefono')
                                    <p x-show="serverErrors.telefono" class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Fecha --}}
                            <div>
                                <label for="fecha_nacimiento" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Fecha de nacimiento</label>
                                <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" x-model="fechaNacimiento" value="{{ old('fecha_nacimiento') }}" max="{{ now()->subYears(10)->format('Y-m-d') }}" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                @error('fecha_nacimiento')
                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Género --}}
                            <div>
                                <label for="genero" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Género</label>
                                <select id="genero" name="genero" x-model="genero" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                    <option value="">Selecciona una opción</option>
                                    <option value="M" @selected(old('genero') === 'M')>Masculino</option>
                                    <option value="F" @selected(old('genero') === 'F')>Femenino</option>
                                </select>
                                @error('genero')
                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Ubicación --}}
                        <div class="mt-6">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <input type="hidden" name="ubicacion" :value="ubicacionFormateada">
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">País</label>
                                    <select x-model="paisSeleccionado" @change="cargarEstados()" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                        <option value="">Selecciona un país...</option>
                                        <template x-for="pais in paises" :key="pais.iso2"><option :value="pais.iso2" x-text="pais.name"></option></template>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Departamento</label>
                                    <select x-model="estadoSeleccionado" @change="cargarCiudades()" :disabled="!paisSeleccionado" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                        <option value="">Selecciona un depto...</option>
                                        <template x-for="estado in estados" :key="estado.iso2"><option :value="estado.iso2" x-text="estado.name"></option></template>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Ciudad</label>
                                    <select x-model="ciudadSeleccionada" :disabled="!estadoSeleccionado" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                        <option value="">Selecciona una ciudad...</option>
                                        <template x-for="ciudad in ciudades" :key="ciudad.name"><option :value="ciudad.name" x-text="ciudad.name"></option></template>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex items-center justify-end">
                            <button type="button" @click="next()" class="rounded-xl bg-sidan-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600">
                                Continuar →
                            </button>
                        </div>
                    </section>

                    {{-- ============================== --}}
                    {{-- PASO 2: CONTRASEÑA OPCIONAL    --}}
                    {{-- ============================== --}}
                    <section id="register-step-2" x-show="step === 2" x-cloak class="p-6 sm:p-8">
                        <div class="mb-8">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">Paso 02</p>
                            <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">Contraseña (Opcional)</h2>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Si agregas una contraseña, podrás iniciar sesión usando tu correo de Google y esta contraseña. Puedes también usar simplemente el botón de Google para iniciar sesión.</p>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="password" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Contraseña</label>
                                {{-- Se eliminó el "required" --}}
                                <input id="password" name="password" type="password" x-model="password" @input="validatePasswordMatch()" placeholder="Dejar en blanco para omitir" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            </div>
                            <div>
                                <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Confirmar contraseña</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" x-model="passwordConfirmation" @input="validatePasswordMatch()" placeholder="Repite la contraseña" class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white">
                            </div>
                        </div>

                        <div class="mt-8 flex items-center justify-between">
                            <button type="button" @click="previous()" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200">
                                ← Atrás
                            </button>
                            <div class="flex gap-3">
                                <button type="button" @click="next()" class="rounded-xl bg-sidan-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600">
                                    Continuar →
                                </button>
                            </div>
                        </div>
                    </section>

                    {{-- ============================== --}}
                    {{-- PASO 3: CONFIRMACIÓN           --}}
                    {{-- ============================== --}}
                    <section id="register-step-3" x-show="step === 3" x-cloak class="p-6 sm:p-8">
                        <div class="mb-8">
                            <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">Paso 03</p>
                            <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">Revisa tu información</h2>
                            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Verifica que los datos que proporcionaste sean correctos.</p>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl bg-slate-50 p-5 dark:bg-white/5">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-black text-sidan-900 dark:text-white">Datos adicionales</h3>
                                    <button type="button" @click="step = 1" class="text-sm font-bold text-sidan-500 hover:text-green-600">Editar</button>
                                </div>
                                <div class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
                                    <div x-show="documento">
                                        <span class="text-slate-400">Documento</span>
                                        <p class="mt-1 font-bold text-slate-700 dark:text-slate-200" x-text="documento"></p>
                                    </div>
                                    <div x-show="telefonoFormateado()">
                                        <span class="text-slate-400">Teléfono</span>
                                        <p class="mt-1 font-bold text-slate-700 dark:text-slate-200" x-text="telefonoFormateado()"></p>
                                    </div>
                                    <div x-show="fechaNacimiento">
                                        <span class="text-slate-400">Fecha de nacimiento</span>
                                        <p class="mt-1 font-bold text-slate-700 dark:text-slate-200" x-text="fechaNacimiento"></p>
                                    </div>
                                    <div x-show="generoTexto()">
                                        <span class="text-slate-400">Género</span>
                                        <p class="mt-1 font-bold text-slate-700 dark:text-slate-200" x-text="generoTexto()"></p>
                                    </div>
                                    <div x-show="ubicacionAmigable">
                                        <span class="text-slate-400">Ubicación</span>
                                        <p class="mt-1 font-bold text-slate-700 dark:text-slate-200" x-text="ubicacionAmigable"></p>
                                    </div>
                                    {{-- Mensaje por si no llenó nada --}}
                                    <div x-show="!documento && !telefonoFormateado() && !fechaNacimiento && !generoTexto() && !ubicacionAmigable" class="col-span-2 text-slate-500 italic">
                                        Has decidido omitir tus datos personales.
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-5 dark:bg-white/5 text-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-black text-sidan-900 dark:text-white">Credenciales adicionales</h3>
                                    <button type="button" @click="step = 2" class="text-sm font-bold text-sidan-500 hover:text-green-600">Editar</button>
                                </div>
                                <div class="mt-4">
                                    <span class="text-sm text-slate-400">Contraseña alternativa</span>
                                    <p class="mt-1 font-bold text-slate-700 dark:text-slate-200" x-text="password ? 'Configurada (Oculta por seguridad)' : 'No configurada'"></p>
                                </div>
                            </div>

                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 dark:border-white/10">
                                <input
                                    type="checkbox"
                                    required
                                    class="mt-1 h-4 w-4 rounded border-slate-300 text-sidan-500 focus:ring-sidan-500"
                                >

                                <span class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                                    Confirmo que la información proporcionada es correcta y acepto el manejo de mis datos.
                                </span>
                            </label>
                        </div>

                        <div class="mt-8 flex items-center justify-between">
                            <button type="button" @click="previous()" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200">
                                ← Atrás
                            </button>
                            <button type="submit" class="rounded-xl bg-sidan-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600">
                                Guardar y continuar
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

            <a href="{{ route('google.login') }}"
                class="flex w-fit mx-auto items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
            Omitir por ahora
            </a>

            <p class="mt-6 text-center text-xs text-slate-400">
                Tus datos serán utilizados únicamente para gestionar tu cuenta y participación en las actividades.
            </p>
        </main>
    </main>

    {{-- Footer --}}
    <footer class="mt-auto w-full border-t border-slate-200 bg-white dark:border-white/10 dark:bg-sidan-950">
        <div class="mx-auto max-w-7xl px-4 py-6 text-center text-sm text-slate-400 sm:px-6 lg:px-8">
            © {{ date('Y') }} SIDAN. Todos los derechos reservados.
        </div>
    </footer>
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

            nombres: @js(old('nombres', '')),
            apellidos: @js(old('apellidos', '')),
            documento: @js(old('documento', '')),
            telefono: @js(old('telefono', '')),
            fechaNacimiento: @js(old('fecha_nacimiento', '')),
            genero: @js(old('genero', '')),
            email: @js(old('email', '')),

            password: '',
            passwordConfirmation: '',

            // ==========================================
            // 2. VARIABLES DE UBICACIÓN
            // ==========================================
            oldUbicacion: @js(old('ubicacion', '')),
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

                // Validación específica de contraseñas (Paso 2)
                if (this.step === 2) {
                    const password = document.getElementById('password');
                    const confirmation = document.getElementById('password_confirmation');
                    
                    confirmation.setCustomValidity('');

                    // SOLO validamos si el usuario decidió escribir una contraseña
                    if (password.value !== '' || confirmation.value !== '') {
                        if (password.value !== confirmation.value) {
                            confirmation.setCustomValidity('Las contraseñas no coinciden.');
                            confirmation.reportValidity();
                            confirmation.focus();
                            return false;
                        }
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

            submitForm(event) {
                if (!this.validateCurrentStep()) {
                    event.preventDefault();
                }
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

            // ==========================================
            // 5. MÉTODOS DE UBICACIÓN
            // ==========================================
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
                                    this._oldEstado = ''; // Limpiamos
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
                                    this._oldCiudad = ''; // Limpiamos
                                });
                            }
                        });
                }
            }
        };
    }
</script>
@endsection