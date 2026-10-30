@php
    $usuarioHeader = request()->user();
    $rolHeader = $usuarioHeader ? strtolower((string) $usuarioHeader->rol()->value('nombre')) : null;
    $esAdminHeader = in_array($rolHeader, ['admin', 'superadmin'], true);

    $enInicioHeader = request()->routeIs('welcome');
    $enExplorarHeader = request()->routeIs('activities.*', 'sidan.inscripciones.create');
    $enInscripcionesHeader = request()->routeIs('user.inscripciones.*');
    $enOrdenesHeader = request()->routeIs('user.ordenes.*', 'sidan.ordenes.*', 'sidan.pagos.*');
    $enPerfilHeader = request()->routeIs('profile.*');
    $enCategoriasHeader = request()->routeIs('publico.categorias');
    $enComoFuncionaHeader = request()->routeIs('publico.como-funciona');
@endphp

<header x-data="{ mobileMenu: false }"
    class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-sidan-950/90">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

        {{-- LOGO --}}
        <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
            <div
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white shadow-lg shadow-sidan-900/20">
                S
            </div>

            <span class="text-xl font-black tracking-tight text-sidan-900 dark:text-white">
                SIDAN
            </span>
        </a>

        {{-- NAVEGACIÓN DESKTOP --}}
        <nav class="hidden items-center gap-1.5 text-sm font-semibold lg:flex" aria-label="Navegación principal">
            <a href="{{ route('welcome') }}" @class([
                'rounded-xl px-3 py-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-sidan-500',
                'bg-sidan-500/10 text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enInicioHeader,
                'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' => !$enInicioHeader,
            ])
                @if ($enInicioHeader) aria-current="page" @endif>
                Inicio
            </a>

            <a href="{{ route('activities.index') }}" @class([
                'rounded-xl px-3 py-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-sidan-500',
                'bg-sidan-500/10 text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enExplorarHeader,
                'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' => !$enExplorarHeader,
            ])
                @if ($enExplorarHeader) aria-current="page" @endif>
                Explorar
            </a>

            @if ($rolHeader === 'usuario')
                <a href="{{ route('user.inscripciones.index') }}" @class([
                    'rounded-xl px-3 py-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-sidan-500',
                    'bg-sidan-500/10 text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enInscripcionesHeader,
                    'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' => !$enInscripcionesHeader,
                ])
                    @if ($enInscripcionesHeader) aria-current="page" @endif>
                    Mis inscripciones
                </a>

                <a href="{{ route('user.ordenes.index') }}" @class([
                    'rounded-xl px-3 py-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-sidan-500',
                    'bg-sidan-500/10 text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enOrdenesHeader,
                    'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' => !$enOrdenesHeader,
                ])
                    @if ($enOrdenesHeader) aria-current="page" @endif>
                    Mis órdenes
                </a>
            @else
                <a href="{{ route('publico.categorias') }}" @class([
                    'rounded-xl px-3 py-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-sidan-500',
                    'bg-sidan-500/10 text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enCategoriasHeader,
                    'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' => !$enCategoriasHeader,
                ])
                    @if ($enCategoriasHeader) aria-current="page" @endif>
                    Categorías
                </a>
                <a href="{{ route('publico.como-funciona') }}" @class([
                    'rounded-xl px-3 py-2 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-sidan-500',
                    'bg-sidan-500/10 text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enComoFuncionaHeader,
                    'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white' => !$enComoFuncionaHeader,
                ])
                    @if ($enComoFuncionaHeader) aria-current="page" @endif>
                    ¿Cómo funciona?
                </a>
            @endif
        </nav>

        {{-- ACCIONES DESKTOP --}}
        <div class="hidden items-center gap-2 lg:flex">

            {{-- CAMBIAR TEMA --}}
            <button type="button" onclick="toggleTheme()"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:-translate-y-0.5 hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
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

            @auth
                @if ($esAdminHeader)
                    <a href="{{ route(\App\Support\AccesoModulos::inicio($usuarioHeader)) }}"
                        class="rounded-xl px-4 py-2.5 text-sm font-bold text-sidan-900 transition hover:bg-slate-100 dark:text-white dark:hover:bg-white/5">
                        Panel administrativo
                    </a>

                    <a href="{{ route('admin.welcome.index') }}"
                        class="rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-sidan-500/20 transition hover:-translate-y-0.5 hover:bg-sidan-600">
                        Administrar Welcome
                    </a>
                @endif

                    {{-- Menú de perfil compartido por usuarios y administradores --}}
                    <div class="relative ml-1" x-data="{ profileOpen: false }" @click.outside="profileOpen = false"
                        @keydown.escape.window="profileOpen = false">
                        <button type="button" @click="profileOpen = !profileOpen" :aria-expanded="profileOpen.toString()"
                            aria-haspopup="true" aria-controls="public-user-profile-menu" aria-label="Abrir menú de perfil"
                            @class([
                                'flex h-10 items-center gap-2 rounded-xl px-1 pr-3 transition hover:bg-slate-100 dark:hover:bg-white/5',
                                'bg-sidan-500/10 dark:bg-sidan-500/15' => $enPerfilHeader,
                            ])>
                            <img src="{{ $usuarioHeader->imagen_perfil ? asset('storage/' . $usuarioHeader->imagen_perfil) : asset('images/usuario.png') }}"
                                alt="Foto de perfil"
                                class="h-8 w-8 rounded-full border border-slate-200 object-cover dark:border-white/10">
                            <svg class="h-4 w-4 text-slate-500 dark:text-slate-400" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>

                        <div id="public-user-profile-menu" x-show="profileOpen" x-cloak x-transition
                            class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 shadow-2xl dark:border-white/10 dark:bg-slate-900">
                            <div class="border-b border-slate-100 px-4 py-3 dark:border-white/10">
                                <p class="truncate text-sm font-bold text-sidan-900 dark:text-white">
                                    {{ $usuarioHeader->nombres }} {{ $usuarioHeader->apellidos }}
                                </p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                    {{ $usuarioHeader->correo }}
                                </p>
                            </div>

                            <a href="{{ route('profile.edit') }}" @click="profileOpen = false" @class([
                                'flex items-center gap-3 px-4 py-3 text-sm transition hover:bg-slate-100 dark:hover:bg-white/5',
                                'font-bold text-sidan-600 dark:text-sidan-300' => $enPerfilHeader,
                                'text-slate-700 dark:text-slate-200' => !$enPerfilHeader,
                            ])
                                @if ($enPerfilHeader) aria-current="page" @endif>
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 21a8 8 0 0 1 16 0" stroke-linecap="round" />
                                </svg>
                                Mi perfil
                            </a>

                            <div class="my-1 border-t border-slate-100 dark:border-white/10"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex w-full items-center gap-3 px-4 py-3 text-left text-sm text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8" aria-hidden="true">
                                        <path
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                                            stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
            @else
                <a href="{{ route('login') }}"
                    class="rounded-xl px-4 py-2.5 text-sm font-bold text-sidan-900 transition hover:bg-slate-100 dark:text-white dark:hover:bg-white/5">
                    Iniciar sesión
                </a>

                <a href="{{ route('register') }}"
                    class="rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-sidan-500/20 transition hover:-translate-y-0.5 hover:bg-sidan-600">
                    Registrarse
                </a>
            @endauth
        </div>

        {{-- BOTÓN MENÚ MÓVIL --}}
        <button type="button" @click="mobileMenu = !mobileMenu"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 lg:hidden dark:border-white/10 dark:text-slate-200"
            aria-label="Abrir menú">
            <svg x-show="!mobileMenu" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2">
                <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
            </svg>

            <svg x-show="mobileMenu" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" />
            </svg>
        </button>
    </div>

    {{-- MENÚ MÓVIL --}}
    <div x-show="mobileMenu" x-cloak x-transition @click.outside="mobileMenu = false"
        class="border-t border-slate-200 bg-white px-4 py-4 lg:hidden dark:border-white/10 dark:bg-sidan-950">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 text-sm font-semibold">

            <a href="{{ route('welcome') }}" @click="mobileMenu = false" @class([
                'rounded-xl px-3 py-2.5 transition',
                'bg-sidan-500/10 font-bold text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enInicioHeader,
                'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5' => !$enInicioHeader,
            ])
                @if ($enInicioHeader) aria-current="page" @endif>
                Inicio
            </a>

            <a href="{{ route('activities.index') }}" @click="mobileMenu = false" @class([
                'rounded-xl px-3 py-2.5 transition',
                'bg-sidan-500/10 font-bold text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enExplorarHeader,
                'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5' => !$enExplorarHeader,
            ])
                @if ($enExplorarHeader) aria-current="page" @endif>
                Explorar
            </a>

            @if ($rolHeader === 'usuario')
                <a href="{{ route('user.inscripciones.index') }}" @click="mobileMenu = false"
                    @class([
                        'rounded-xl px-3 py-2.5 transition',
                        'bg-sidan-500/10 font-bold text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enInscripcionesHeader,
                        'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5' => !$enInscripcionesHeader,
                    ]) @if ($enInscripcionesHeader) aria-current="page" @endif>
                    Mis inscripciones
                </a>

                <a href="{{ route('user.ordenes.index') }}" @click="mobileMenu = false" @class([
                    'rounded-xl px-3 py-2.5 transition',
                    'bg-sidan-500/10 font-bold text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enOrdenesHeader,
                    'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5' => !$enOrdenesHeader,
                ])
                    @if ($enOrdenesHeader) aria-current="page" @endif>
                    Mis órdenes
                </a>
            @else
                <a href="{{ route('publico.categorias') }}" @click="mobileMenu = false" @class([
                    'rounded-xl px-3 py-2.5 transition',
                    'bg-sidan-500/10 font-bold text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enCategoriasHeader,
                    'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5' => !$enCategoriasHeader,
                ])
                    @if ($enCategoriasHeader) aria-current="page" @endif>
                    Categorías
                </a>
                <a href="{{ route('publico.como-funciona') }}" @click="mobileMenu = false"
                    @class([
                        'rounded-xl px-3 py-2.5 transition',
                        'bg-sidan-500/10 font-bold text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-300' => $enComoFuncionaHeader,
                        'text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5' => !$enComoFuncionaHeader,
                    ]) @if ($enComoFuncionaHeader) aria-current="page" @endif>
                    ¿Cómo funciona?
                </a>
            @endif

            <div class="mt-2 flex gap-2 border-t border-slate-200 pt-4 dark:border-white/10">

                <button type="button" onclick="toggleTheme()"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-700 dark:border-white/10 dark:text-slate-200"
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

                @auth
                    @if ($esAdminHeader)
                        <a href="{{ route(\App\Support\AccesoModulos::inicio($usuarioHeader)) }}"
                            class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-center font-bold text-slate-700 dark:border-white/10 dark:text-slate-200">
                            Panel
                        </a>

                        <a href="{{ route('admin.welcome.index') }}"
                            class="flex-1 rounded-xl bg-sidan-500 px-4 py-2.5 text-center font-bold text-white">
                            Welcome
                        </a>
                    @endif

                        <a href="{{ route('profile.edit') }}" @click="mobileMenu = false" @class([
                            'flex flex-1 items-center justify-center gap-2 rounded-xl border px-4 py-2 text-center font-bold',
                            'border-sidan-200 bg-sidan-50 text-sidan-600 dark:border-sidan-500/30 dark:bg-sidan-500/10 dark:text-sidan-300' => $enPerfilHeader,
                            'border-slate-200 bg-white text-sidan-900 dark:border-white/10 dark:bg-white/5 dark:text-white' => !$enPerfilHeader,
                        ])
                            @if ($enPerfilHeader) aria-current="page" @endif>
                            <img src="{{ $usuarioHeader->imagen_perfil ? asset('storage/' . $usuarioHeader->imagen_perfil) : asset('images/usuario.png') }}"
                                alt="" class="h-7 w-7 rounded-full object-cover">
                            Mi perfil
                        </a>
                @else
                    <a href="{{ route('login') }}"
                        class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-center font-bold text-slate-700 dark:border-white/10 dark:text-slate-200">
                        Iniciar sesión
                    </a>

                    <a href="{{ route('register') }}"
                        class="flex-1 rounded-xl bg-sidan-500 px-4 py-2.5 text-center font-bold text-white">
                        Registrarse
                    </a>
                @endauth
            </div>

            @auth
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf

                    <button type="submit"
                        class="w-full rounded-xl px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-red-50 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                        Cerrar sesión
                    </button>
                </form>
            @endauth

        </div>
    </div>
</header>
