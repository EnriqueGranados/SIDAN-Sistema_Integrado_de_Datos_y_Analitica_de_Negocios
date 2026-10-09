@extends('layouts.public')

@section('title', 'SIDAN | Vincular Google')

@section('content')
    <div class="flex min-h-screen flex-col bg-[#f6f8fb] dark:bg-sidan-950">
        {{-- Header --}}
        <header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-sidan-950/90">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

                {{-- Logo --}}
                <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white shadow-lg shadow-sidan-900/20">
                        S
                    </div>
                    <span class="text-xl font-black tracking-tight text-sidan-900 dark:text-white">SIDAN</span>
                </a>

                {{-- Acciones --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    {{-- Botón de cambio de tema --}}
                    <button type="button" onclick="toggleTheme()"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:-translate-y-0.5 hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
                        aria-label="Cambiar tema">
                        <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="12" cy="12" r="4" />
                            <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        {{-- Contenido --}}
        <main class="flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">

                {{-- Encabezado --}}
                <div class="mb-8 text-center">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                        Seguridad
                    </p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight text-sidan-900 dark:text-white sm:text-4xl">
                        Vincular cuenta
                    </h1>
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        Protege tu cuenta y conecta tu perfil de Google.
                    </p>
                </div>

                {{-- Tarjeta --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">

                    {{-- Alerta informativa --}}
                    <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900/30 dark:bg-blue-900/10">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-sm text-blue-800 dark:text-blue-300">
                                Hemos detectado que ya existe una cuenta con el correo <strong class="font-black">{{ session('temp_email') }}</strong>.
                            </p>
                        </div>
                    </div>

                    <p class="mb-6 text-sm leading-relaxed text-slate-600 dark:text-slate-400">
                        Para proteger tu información, ingresa tu contraseña actual. <span class="font-bold text-slate-700 dark:text-slate-300">Solo te la pediremos esta vez.</span>
                    </p>

                    {{-- Formulario --}}
                    <form method="POST" action="{{ route('vincular.cuenta.procesar') }}" class="space-y-6">
                        @csrf

                        {{-- Contraseña --}}
                        <div>
                            <label for="password" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                Contraseña actual
                            </label>

                            <input id="password" name="password" type="password" required autofocus
                                placeholder="••••••••"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror">

                            <div class="mt-1.5">
                                @error('password')
                                    <p class="text-sm font-semibold text-red-600 dark:text-red-400 leading-tight">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Botones de Acción --}}
                        <div class="flex flex-col gap-3">
                            <button type="submit"
                                class="w-full rounded-xl bg-sidan-500 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-green-500/20">
                                Confirmar y Vincular
                            </button>
                            
                            <a href="{{ route('login') }}"
                                class="flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-black text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                                Cancelar
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="border-t border-slate-200 bg-white dark:border-white/10 dark:bg-sidan-950">
            <div class="mx-auto max-w-7xl px-4 py-6 text-center text-sm text-slate-400 sm:px-6 lg:px-8">
                © {{ date('Y') }} SIDAN. Todos los derechos reservados.
            </div>
        </footer>
    </div>
@endsection