@extends('layouts.public')

@section('title', 'SIDAN | Nueva contraseña')

@section('content')
    <div class="flex min-h-screen flex-col bg-[#f6f8fb] dark:bg-sidan-950">

        {{-- Header --}}
        <header
            class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-sidan-950/90">

            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

                <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white shadow-lg shadow-sidan-900/20">
                        S
                    </div>

                    <span class="text-xl font-black tracking-tight text-sidan-900 dark:text-white">
                        SIDAN
                    </span>
                </a>

                <div class="flex items-center gap-2 sm:gap-3">

                    <button
                        type="button"
                        onclick="toggleTheme()"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:-translate-y-0.5 hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
                        aria-label="Cambiar tema">

                        <svg
                            class="h-5 w-5 dark:hidden"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8">
                            <path
                                d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"
                                stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>

                        <svg
                            class="hidden h-5 w-5 dark:block"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8">
                            <circle cx="12" cy="12" r="4" />
                            <path
                                d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"
                                stroke-linecap="round" />
                        </svg>
                    </button>

                    <div class="mx-0.5 h-5 w-px bg-slate-200 dark:bg-white/10"></div>

                    <a
                        href="{{ route('login') }}"
                        class="rounded-xl bg-sidan-500 px-3.5 py-2 text-sm font-bold text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600 sm:px-4 sm:py-2.5">
                        Iniciar sesión
                    </a>
                </div>
            </div>
        </header>

        <main class="flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8">

            <div class="w-full max-w-md">

                <div class="mb-8 text-center">

                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                        Recupera tu acceso
                    </p>

                    <h1 class="mt-3 text-3xl font-black tracking-tight text-sidan-900 dark:text-white sm:text-4xl">
                        Nueva contraseña
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Establece una nueva contraseña segura para volver a acceder a tu cuenta en SIDAN.
                    </p>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">

                    <form
                        method="POST"
                        action="{{ route('password.store') }}"
                        class="space-y-5">

                        @csrf

                        {{-- Token --}}
                        <input
                            type="hidden"
                            name="token"
                            value="{{ $request->route('token') }}">

                        {{-- Correo --}}
                        <div>
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                Cuenta
                            </label>

                            <div
                                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3
                                    dark:border-white/10 dark:bg-white/5">

                                <div
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                        bg-sidan-500/10 text-sidan-500">

                                    <svg
                                        class="h-5 w-5"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3 6.75A2.25 2.25 0 0 1 5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 17.25V6.75Z" />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m3.75 6 7.006 5.254a2.075 2.075 0 0 0 2.488 0L20.25 6" />
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <p class="text-xs font-semibold text-slate-400">
                                        Correo electrónico
                                    </p>

                                    <p class="truncate text-sm font-bold text-slate-700 dark:text-slate-200">
                                        {{ old('email', $request->email) }}
                                    </p>
                                </div>
                            </div>

                            <input
                                type="hidden"
                                name="email"
                                value="{{ old('email', $request->email) }}">

                            @error('email')
                                <p class="mt-2 text-sm font-semibold text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Nueva contraseña --}}
                        <div>
                            <label
                                for="password"
                                class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                Nueva contraseña
                            </label>

                            <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500">

                            @error('password')
                                <p class="mt-2 text-sm font-semibold text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Confirmación --}}
                        <div>
                            <label
                                for="password_confirmation"
                                class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                Confirmar contraseña
                            </label>

                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500">
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-sidan-500 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-green-500/20">
                            Restablecer contraseña
                        </button>
                    </form>

                    <div class="mt-7 border-t border-slate-200 pt-6 text-center dark:border-white/10">
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 text-sm font-black text-slate-600 transition hover:text-sidan-500 dark:text-slate-300 dark:hover:text-sidan-500">

                            <svg
                                class="h-4 w-4"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m15 18-6-6 6-6" />
                            </svg>

                            Volver a iniciar sesión
                        </a>
                    </div>
                </div>

                <p class="mt-6 text-center text-xs leading-5 text-slate-400">
                    Utiliza una contraseña segura que no hayas utilizado anteriormente.
                </p>
            </div>
        </main>

        <footer class="border-t border-slate-200 bg-white dark:border-white/10 dark:bg-sidan-950">
            <div class="mx-auto max-w-7xl px-4 py-6 text-center text-sm text-slate-400 sm:px-6 lg:px-8">
                © {{ date('Y') }} SIDAN. Todos los derechos reservados.
            </div>
        </footer>
    </div>
@endsection