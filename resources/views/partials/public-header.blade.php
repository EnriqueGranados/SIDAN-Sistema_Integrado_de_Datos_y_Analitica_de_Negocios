@php
    $usuarioHeader = request()->user();
    $rolHeader = $usuarioHeader ? strtolower((string) $usuarioHeader->rol()->value('nombre')) : null;
    $esAdminHeader = in_array($rolHeader, ['admin', 'superadmin'], true);
@endphp

<header
    x-data="{ mobileMenu: false }"
    class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-sidan-950/90"
>
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

        {{-- LOGO --}}
        <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white shadow-lg shadow-sidan-900/20">
                S
            </div>

            <span class="text-xl font-black tracking-tight text-sidan-900 dark:text-white">
                SIDAN
            </span>
        </a>

        {{-- NAVEGACIÓN DESKTOP --}}
        <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 lg:flex dark:text-slate-300">
            <a
                href="{{ route('welcome') }}#inicio"
                class="transition hover:text-sidan-500"
            >
                Inicio
            </a>

            <a
                href="{{ route('welcome') }}#actividades"
                class="transition hover:text-sidan-500"
            >
                Explorar
            </a>

            <a
                href="{{ route('welcome') }}#categorias"
                class="transition hover:text-sidan-500"
            >
                Categorías
            </a>

            <a
                href="{{ route('welcome') }}#como-funciona"
                class="transition hover:text-sidan-500"
            >
                ¿Cómo funciona?
            </a>
        </nav>

        {{-- ACCIONES DESKTOP --}}
        <div class="hidden items-center gap-2 lg:flex">

            {{-- CAMBIAR TEMA --}}
            <button
                type="button"
                onclick="toggleTheme()"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:-translate-y-0.5 hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
                aria-label="Cambiar tema"
            >
                <svg
                    class="h-5 w-5 dark:hidden"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path
                        d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>

                <svg
                    class="hidden h-5 w-5 dark:block"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <circle cx="12" cy="12" r="4" />

                    <path
                        d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"
                        stroke-linecap="round"
                    />
                </svg>
            </button>

            @auth
                @if ($esAdminHeader)
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold text-sidan-900 transition hover:bg-slate-100 dark:text-white dark:hover:bg-white/5"
                    >
                        Panel administrativo
                    </a>

                    <a
                        href="{{ route('admin.welcome.index') }}"
                        class="rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600"
                    >
                        Administrar Welcome
                    </a>
                @else
                    <a
                        href="{{ route('user.dashboard') }}"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold text-sidan-900 transition hover:bg-slate-100 dark:text-white dark:hover:bg-white/5"
                    >
                        Mi cuenta
                    </a>

                    <a
                        href="{{ route('profile.edit') }}"
                        class="rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600"
                    >
                        Mi perfil
                    </a>
                @endif
            @else
                <a
                    href="{{ route('login') }}"
                    class="rounded-xl px-4 py-2.5 text-sm font-bold text-sidan-900 transition hover:bg-slate-100 dark:text-white dark:hover:bg-white/5"
                >
                    Iniciar sesión
                </a>

                <a
                    href="{{ route('register') }}"
                    class="rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600"
                >
                    Registrarse
                </a>
            @endauth
        </div>

        {{-- BOTÓN MENÚ MÓVIL --}}
        <button
            type="button"
            @click="mobileMenu = !mobileMenu"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 lg:hidden dark:border-white/10 dark:text-slate-200"
            aria-label="Abrir menú"
        >
            <svg
                x-show="!mobileMenu"
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    d="M4 7h16M4 12h16M4 17h16"
                    stroke-linecap="round"
                />
            </svg>

            <svg
                x-show="mobileMenu"
                x-cloak
                class="h-5 w-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    d="M18 6 6 18M6 6l12 12"
                    stroke-linecap="round"
                />
            </svg>
        </button>
    </div>

    {{-- MENÚ MÓVIL --}}
    <div
        x-show="mobileMenu"
        x-cloak
        x-transition
        @click.outside="mobileMenu = false"
        class="border-t border-slate-200 bg-white px-4 py-4 lg:hidden dark:border-white/10 dark:bg-sidan-950"
    >
        <div class="mx-auto flex max-w-7xl flex-col gap-2 text-sm font-semibold">

            <a
                href="{{ route('welcome') }}#inicio"
                @click="mobileMenu = false"
                class="rounded-lg px-3 py-2 text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5"
            >
                Inicio
            </a>

            <a
                href="{{ route('welcome') }}#actividades"
                @click="mobileMenu = false"
                class="rounded-lg px-3 py-2 text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5"
            >
                Explorar
            </a>

            <a
                href="{{ route('welcome') }}#categorias"
                @click="mobileMenu = false"
                class="rounded-lg px-3 py-2 text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5"
            >
                Categorías
            </a>

            <a
                href="{{ route('welcome') }}#como-funciona"
                @click="mobileMenu = false"
                class="rounded-lg px-3 py-2 text-slate-700 transition hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5"
            >
                ¿Cómo funciona?
            </a>

            <div class="mt-2 flex gap-2 border-t border-slate-200 pt-4 dark:border-white/10">

                <button
                    type="button"
                    onclick="toggleTheme()"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-700 dark:border-white/10 dark:text-slate-200"
                    aria-label="Cambiar tema"
                >
                    <svg
                        class="h-5 w-5 dark:hidden"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <svg
                        class="hidden h-5 w-5 dark:block"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <circle cx="12" cy="12" r="4" />

                        <path
                            d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41"
                            stroke-linecap="round"
                        />
                    </svg>
                </button>

                @auth
                    @if ($esAdminHeader)
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-center font-bold text-slate-700 dark:border-white/10 dark:text-slate-200"
                        >
                            Panel
                        </a>

                        <a
                            href="{{ route('admin.welcome.index') }}"
                            class="flex-1 rounded-xl bg-sidan-500 px-4 py-2.5 text-center font-bold text-white"
                        >
                            Welcome
                        </a>
                    @else
                        <a
                            href="{{ route('user.dashboard') }}"
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-center font-bold text-slate-700 dark:border-white/10 dark:text-slate-200"
                        >
                            Mi cuenta
                        </a>

                        <a
                            href="{{ route('profile.edit') }}"
                            class="flex-1 rounded-xl bg-sidan-500 px-4 py-2.5 text-center font-bold text-white"
                        >
                            Mi perfil
                        </a>
                    @endif
                @else
                    <a
                        href="{{ route('login') }}"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-center font-bold text-slate-700 dark:border-white/10 dark:text-slate-200"
                    >
                        Iniciar sesión
                    </a>

                    <a
                        href="{{ route('register') }}"
                        class="flex-1 rounded-xl bg-sidan-500 px-4 py-2.5 text-center font-bold text-white"
                    >
                        Registrarse
                    </a>
                @endauth
            </div>

            @auth
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                    class="mt-1"
                >
                    @csrf

                    <button
                        type="submit"
                        class="w-full rounded-xl px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                    >
                        Cerrar sesión
                    </button>
                </form>
            @endauth

        </div>
    </div>
</header>