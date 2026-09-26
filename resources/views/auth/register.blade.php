@extends('layouts.app')

@section('title', 'SIDAN | Crear cuenta')

@section('content')

<div
    x-data="registerForm()"
    class="flex min-h-screen w-full flex-col bg-[#f6f8fb] dark:bg-sidan-950"
>

    {{-- Header --}}
    <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-sidan-950/90">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

            {{-- Logo --}}
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white shadow-lg shadow-sidan-900/20">S</div>
                <span class="text-xl font-black tracking-tight text-sidan-900 dark:text-white">SIDAN</span>
            </a>

            {{-- Acciones (visibles directamente en móvil y escritorio) --}}
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

                {{-- Divisor sutil --}}
                <div class="mx-0.5 h-5 w-px bg-slate-200 dark:bg-white/10"></div>

                {{-- Texto de ayuda (solo visible desde sm hacia arriba para no apretar el móvil) --}}
                <span class="hidden text-sm font-semibold text-slate-500 sm:inline-block dark:text-slate-400">
                    ¿Ya tienes una cuenta?
                </span>

                {{-- Botón Iniciar Sesión --}}
                <a href="{{ route('login') }}" class="rounded-xl bg-sidan-500 px-3.5 py-2 text-sm font-bold text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600 sm:px-4 sm:py-2.5">
                    Iniciar sesión
                </a>

            </div>

        </div>
    </header>


    {{-- Contenido --}}
    <main class="mx-auto max-w-4xl w-full items-center px-4 py-10 sm:px-6 lg:px-8 lg:py-14">

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
                Crea tu cuenta
            </h1>

            <p class="mt-3 text-slate-600 dark:text-slate-400">
                Completa tus datos para comenzar a participar en las actividades de SIDAN.
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
            method="POST"
            action="{{ route('register') }}"
            class="mx-auto mt-8 max-w-3xl"
            @submit="submitForm"
        >

            @csrf


            {{-- TARJETA --}}
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">

                {{-- Paso 1 --}}
                <section id="register-step-1" x-show="step === 1" x-cloak class="p-6 sm:p-8">

                    <div class="mb-8">

                        <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                            Paso 01
                        </p>

                        <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">
                            Cuéntanos sobre ti
                        </h2>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Necesitamos algunos datos personales para crear tu perfil.
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
                                Documento *
                            </label>

                            <input
                                id="documento"
                                name="documento"
                                type="text"
                                x-model="documento"
                                value="{{ old('documento') }}"
                                required
                                autocomplete="off"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                            >

                            @error('documento')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- Teléfono --}}
                        <div>

                            <label for="telefono" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                Teléfono *
                            </label>

                            <input
                                id="telefono"
                                name="telefono"
                                type="tel"
                                x-model="telefono"
                                value="{{ old('telefono') }}"
                                required
                                autocomplete="tel"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                            >

                            @error('telefono')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- Fecha --}}
                        <div>

                            <label for="fecha_nacimiento" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                Fecha de nacimiento *
                            </label>

                            <input
                                id="fecha_nacimiento"
                                name="fecha_nacimiento"
                                type="date"
                                x-model="fechaNacimiento"
                                value="{{ old('fecha_nacimiento') }}"
                                required
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                            >

                            @error('fecha_nacimiento')
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- Género --}}
                        <div>

                            <label for="genero" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                Género *
                            </label>

                            <select
                                id="genero"
                                name="genero"
                                x-model="genero"
                                required
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
                            Crea tus credenciales
                        </h2>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Utilizarás estos datos para iniciar sesión en SIDAN.
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
                                    required
                                    autocomplete="email"
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                >

                                @error('email')
                                    <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>


                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="password" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Contraseña *
                                </label>

                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    x-model="password"
                                    @input="validatePasswordMatch()"
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                >

                            </div>

                            <div>
                                <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Confirmar contraseña *
                                </label>

                                <input
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    type="password"
                                    required
                                    x-model="passwordConfirmation"
                                    @input="validatePasswordMatch()"
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                >

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
                            Revisa tu información
                        </h2>

                        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            Comprueba que todo esté correcto antes de crear tu cuenta.
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


                                <div>
                                    <span class="text-slate-400">
                                        Documento
                                    </span>

                                    <p
                                        class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                        x-text="documento"
                                    ></p>
                                </div>


                                <div>
                                    <span class="text-slate-400">
                                        Teléfono
                                    </span>

                                    <p
                                        class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                        x-text="telefono"
                                    ></p>
                                </div>


                                <div>
                                    <span class="text-slate-400">
                                        Fecha de nacimiento
                                    </span>

                                    <p
                                        class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                        x-text="fechaNacimiento"
                                    ></p>
                                </div>


                                <div>
                                    <span class="text-slate-400">
                                        Género
                                    </span>

                                    <p
                                        class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                        x-text="generoTexto()"
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

                            <div class="mt-4">

                                <span class="text-sm text-slate-400">
                                    Correo electrónico
                                </span>

                                <p
                                    class="mt-1 font-bold text-slate-700 dark:text-slate-200"
                                    x-text="email"
                                ></p>

                            </div>

                            <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">
                                Tu contraseña será almacenada de forma segura y no será mostrada.
                            </p>

                        </div>


                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 dark:border-white/10">

                            <input
                                type="checkbox"
                                required
                                class="mt-1 h-4 w-4 rounded border-slate-300 text-sidan-500 focus:ring-sidan-500"
                            >

                            <span class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                                Confirmo que la información proporcionada es correcta y acepto crear mi cuenta en SIDAN.
                            </span>

                        </label>

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
                            Crear mi cuenta
                        </button>

                    </div>

                </section>

            </div>

        </form>
    </main>

        <p class="mt-6 text-center text-xs text-slate-400">
            Tus datos serán utilizados únicamente para gestionar tu cuenta y participación en las actividades.
        </p>

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
            step: 1,

            nombres: @js(old('nombres', '')),
            apellidos: @js(old('apellidos', '')),
            documento: @js(old('documento', '')),
            telefono: @js(old('telefono', '')),
            fechaNacimiento: @js(old('fecha_nacimiento', '')),
            genero: @js(old('genero', '')),

            email: @js(old('email', '')),

            password: '',
            passwordConfirmation: '',

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
                const section = document.getElementById(
                    `register-step-${this.step}`
                );

                if (!section) {
                    return false;
                }

                // Validación específica de contraseñas
                if (this.step === 2) {
                    const password = document.getElementById('password');
                    const confirmation = document.getElementById(
                        'password_confirmation'
                    );

                    // Primero limpiamos cualquier error anterior
                    confirmation.setCustomValidity('');

                    // Luego comparamos
                    if (password.value !== confirmation.value) {
                        confirmation.setCustomValidity(
                            'Las contraseñas no coinciden.'
                        );

                        confirmation.reportValidity();
                        confirmation.focus();

                        return false;
                    }
                }

                // Validación HTML de los demás campos
                const fields = section.querySelectorAll(
                    'input, select, textarea'
                );

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

                if (
                    confirmation.value &&
                    password.value !== confirmation.value
                ) {
                    confirmation.setCustomValidity(
                        'Las contraseñas no coinciden.'
                    );
                }
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
                if (this.genero === 'M') {
                    return 'Masculino';
                }

                if (this.genero === 'F') {
                    return 'Femenino';
                }

                return '';
            }
        };
    }
</script>
@endsection