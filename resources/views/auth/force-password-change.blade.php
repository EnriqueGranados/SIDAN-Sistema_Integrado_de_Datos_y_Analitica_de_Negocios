@extends('layouts.public')

@section('title', 'SIDAN | Cambio obligatorio de contraseña')

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
                <div class="mb-8 text-center">
                    <h1 class="text-2xl font-black text-sidan-900 dark:text-white">
                        Actualiza tu contraseña
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        Se ha generado una contraseña temporal para tu cuenta.
                        Por seguridad, debes establecer una contraseña personal
                        antes de continuar utilizando SIDAN.
                    </p>
                </div>

                {{-- Tarjeta --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">
                    <form method="POST"
                        action="{{ route('password.force.update') }}"
                        class="space-y-5">

                        @csrf

                        <div>
                            <label for="current_password"
                                class="mb-2 block text-sm font-bold dark:text-white">
                                Contraseña temporal
                            </label>

                            <input
                                id="current_password"
                                type="password"
                                name="current_password"
                                placeholder="••••••••"
                                required
                                autocomplete="current-password"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror"
                            >

                            @error('current_password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password"
                                class="mb-2 block text-sm font-bold dark:text-white">
                                Nueva contraseña
                            </label>

                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror"
                            >

                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation"
                                class="mb-2 block text-sm font-bold dark:text-white">
                                Confirmar nueva contraseña
                            </label>

                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/20 @enderror"
                            >
                        </div>

                        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 dark:border-green-500/20 dark:bg-green-500/10">
                            <div class="flex items-start gap-3">
                                <svg
                                    class="mt-0.5 h-5 w-5 shrink-0 text-green-600 dark:text-green-400"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"
                                    />
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m9 12 2 2 4-4"
                                    />
                                </svg>

                                <p class="text-xs leading-5 text-green-800 dark:text-green-300">
                                    Tu nueva contraseña reemplazará la contraseña temporal.
                                    Procura utilizar una contraseña segura que no hayas usado
                                    anteriormente y no la compartas con otras personas.
                                </p>
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-sidan-500 px-5 py-3 font-bold text-white transition hover:bg-green-600"
                        >
                            Guardar nueva contraseña
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="mt-5 text-center">
                        @csrf

                        <button type="submit"
                                class="text-sm font-semibold text-slate-500 hover:underline">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

@endsection