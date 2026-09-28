@extends('layouts.public')

@section('title', 'SIDAN | Iniciar sesión')

@section('content')

    <div class="flex min-h-screen flex-col bg-[#f6f8fb] dark:bg-sidan-950">
        {{-- Header --}}
        <header
            class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-sidan-950/90">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

                {{-- Logo --}}
                <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white shadow-lg shadow-sidan-900/20">
                        S</div>
                    <span class="text-xl font-black tracking-tight text-sidan-900 dark:text-white">SIDAN</span>
                </a>

                {{-- Acciones (visibles directamente en móvil y escritorio) --}}
                <div class="flex items-center gap-2 sm:gap-3">

                    {{-- Botón de cambio de tema --}}
                    <button type="button" onclick="toggleTheme()"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:-translate-y-0.5 hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
                        aria-label="Cambiar tema">
                        <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>

                        <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8">
                            <circle cx="12" cy="12" r="4" />
                            <path
                                d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"
                                stroke-linecap="round" />
                        </svg>
                    </button>

                    {{-- Divisor sutil --}}
                    <div class="mx-0.5 h-5 w-px bg-slate-200 dark:bg-white/10"></div>

                    {{-- Texto de ayuda (solo visible desde sm hacia arriba para no apretar el móvil) --}}
                    <span class="hidden text-sm font-semibold text-slate-500 sm:inline-block dark:text-slate-400">
                        ¿No tienes una cuenta?
                    </span>

                    {{-- Botón Crear cuenta --}}
                    <a href="{{ route('register') }}"
                        class="rounded-xl bg-sidan-500 px-3.5 py-2 text-sm font-bold text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600 sm:px-4 sm:py-2.5">
                        Crear cuenta
                    </a>
                </div>
            </div>
        </header>

        {{-- Mensajes de error por estado de cuenta --}}
        @if (session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg mb-6 max-w-md mx-auto">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            </div>
        @endif
        
        {{-- Contenido --}}
        <main class="flex flex-1 items-center justify-center px-4 py-12 sm:px-6 lg:px-8">

            <div class="w-full max-w-md">

                {{-- Encabezado --}}
                <div class="mb-8 text-center">

                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                        Bienvenido
                    </p>

                    <h1 class="mt-3 text-3xl font-black tracking-tight text-sidan-900 dark:text-white sm:text-4xl">
                        Inicia sesión
                    </h1>

                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        Accede a tu espacio en SIDAN.
                    </p>

                </div>


                {{-- Tarjeta --}}
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-8">

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">

                        @csrf


                        {{-- Correo --}}
                        <div>

                            <label for="email" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">
                                Correo electrónico
                            </label>

                            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                autofocus autocomplete="email" placeholder="correo@ejemplo.com"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500">

                            @error('email')
                                <p class="mt-2 text-sm font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Contraseña --}}
                        <div>

                            <div class="mb-2 flex items-center justify-between">

                                <label for="password" class="block text-sm font-bold text-slate-700 dark:text-slate-200">
                                    Contraseña
                                </label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"
                                        class="text-xs font-bold text-sidan-500 transition hover:text-green-600">
                                        ¿Olvidaste tu contraseña?
                                    </a>
                                @endif

                            </div>

                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                placeholder="••••••••"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-sidan-500 focus:ring-2 focus:ring-green-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500">

                            @error('password')
                                <p class="mt-2 text-sm font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Recordarme --}}
                        <div>

                            <label class="flex cursor-pointer items-center gap-3">

                                <input id="remember" name="remember" type="checkbox" value="1"
                                    class="h-4 w-4 rounded border-slate-300 text-sidan-500 focus:ring-sidan-500 dark:border-white/20 dark:bg-white/5">

                                <span class="text-sm text-slate-600 dark:text-slate-400">
                                    Mantener mi sesión iniciada
                                </span>

                            </label>

                        </div>


                        {{-- Botón --}}
                        <button type="submit"
                            class="w-full rounded-xl bg-sidan-500 px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600 focus:outline-none focus:ring-4 focus:ring-green-500/20">
                            Iniciar sesión
                        </button>

                    </form>

                    <div class="my-7 flex items-center gap-4">

                        <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>

                        <span class="text-xs font-semibold text-slate-400">
                            O
                        </span>

                        <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>

                    </div>

                    <a href="{{ route('google.login') }}"
                        class="flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">
                        
                        <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12.037 21.998a10.313 10.313 0 0 1-7.168-3.049 9.888 9.888 0 0 1-2.868-7.118 9.947 9.947 0 0 1 3.064-6.949A10.37 10.37 0 0 1 12.212 2h.176a9.935 9.935 0 0 1 6.614 2.564L16.457 6.88a6.187 6.187 0 0 0-4.131-1.566 6.9 6.9 0 0 0-4.794 1.913 6.618 6.618 0 0 0-2.045 4.657 6.608 6.608 0 0 0 1.882 4.723 6.891 6.891 0 0 0 4.725 2.07h.143c1.41.072 2.8-.354 3.917-1.2a5.77 5.77 0 0 0 2.172-3.41l.043-.117H12.22v-3.41h9.678c.075.617.109 1.238.1 1.859-.099 5.741-4.017 9.6-9.746 9.6l-.215-.002Z" clip-rule="evenodd"/>
                        </svg>

                        Iniciar sesión con Google
                    </a>

                    {{-- Separador --}}
                    <div class="my-7 flex items-center justify-center gap-2 text-sm">
                        <span class="font-semibold text-slate-400">
                            ¿Primera vez en SIDAN?
                        </span>
                        <a href="{{ route('register') }}" class="font-black text-slate-700 transition hover:underline dark:text-slate-200">
                            Crear una cuenta
                        </a>
                    </div>

                </div>


                {{-- Texto inferior --}}
                <p class="mt-6 text-center text-xs leading-5 text-slate-400">
                    Al iniciar sesión, podrás acceder a las funciones disponibles
                    según tu cuenta y rol dentro de SIDAN.
                </p>

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
