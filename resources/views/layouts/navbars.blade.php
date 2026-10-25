<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F2D5B">
    <title>@yield('title', 'SIDAN - Dashboard')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Figtree', sans-serif; }
        *::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 999px; }
        .dark *::-webkit-scrollbar-thumb { background: #334155; }
        ::-webkit-scrollbar-thumb:hover { background: #22c55e; }
    </style>

    <script>
        // Recuperar el tema guardado y permitir cambiarlo desde la barra superior.
        (() => {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const useDark = savedTheme === 'dark' || (!savedTheme && prefersDark);

            document.documentElement.classList.toggle('dark', useDark);

            window.toggleTheme = () => {
                const isDark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            };
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body
    class="min-h-screen bg-slate-200 font-sans text-slate-900 antialiased dark:bg-[#080c14] dark:text-white"
    x-data="{ sidebarOpen: false }"
    @keydown.escape.window="sidebarOpen = false"
    x-cloak
>
    <x-toast />

    @php
        $usuarioPanel = auth()->user();

        $menuPanel = collect(\App\Support\AccesoModulos::catalogo())
            ->filter(fn ($modulo, $clave) => \App\Support\AccesoModulos::permite($usuarioPanel, $clave)
                && ($clave !== 'ventas_presenciales' || in_array($usuarioPanel?->rol?->nombre, ['admin', 'superadmin'], true)));

        $mostrarPanel = $menuPanel->isNotEmpty();
        $rutaActual = request()->route()?->getName() ?? '';

        $iconosPanel = [
            'dashboard' => 'M3 3h7v9H3z M14 3h7v5h-7z M14 12h7v9h-7z M3 16h7v5H3z',
            'actividades' => 'M8 3v4m8-4v4M3 10h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z',
            'revision_actividades' => 'M9 12l2 2 4-4M12 3l9 4v5c0 5-9 9-9 9s-9-4-9-9V7l9-4z',
            'categorias' => 'M4 6h16M4 12h16M4 18h10',
            'etiquetas' => 'M3 12l9-9h8v8l-9 9-8-8zM16 7h.01',
            'recursos' => 'M10 6h10M10 12h10M10 18h10M4 6h2M4 12h2M4 18h2',
            'espacios' => 'M12 21s6-5 6-11a6 6 0 10-12 0c0 6 6 11 6 11zM12 8a2 2 0 100 4 2 2 0 000-4z',
            'inscripciones' => 'M9 11a4 4 0 100-8 4 4 0 000 8M2 21v-2a5 5 0 015-5h4a5 5 0 015 5v2M20 8v6M17 11h6',
            'ordenes' => 'M3 7h18l-2 13H5L3 7zM8 7V5a4 4 0 018 0v2',
            'consolidado' => 'M4 6h16M4 12h16M4 18h16M7 4v4M7 10v4M7 16v4',
            'pagos' => 'M3 5h14v10H3zM7 9h14v11H7zM14 12a2 2 0 100 4 2 2 0 000-4z',
            'ventas_presenciales' => 'M3 7h18v13H3V7zm3-4h12l3 4H3l3-4zm4 8h4m-2-2v4',
            'usuarios' => 'M9 11a4 4 0 100-8 4 4 0 000 8M2 21v-2a6 6 0 0112 0v2M16 3a4 4 0 010 8M17 15a5 5 0 014 5v1',
            'welcome' => 'M3 3h18v18H3zM3 9h18M8 9v12',
            'roles' => 'M12 3l9 4v5c0 5-9 9-9 9s-9-4-9-9V7l9-4zM9 12l2 2 4-4',
        ];
    @endphp

    <div class="min-h-screen p-2 lg:flex lg:gap-2">
        @if ($mostrarPanel)
            <aside
                class="fixed bottom-2 left-2 top-2 z-50 flex w-64 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-slate-700 shadow-sm transition-all duration-300 dark:border-white/5 dark:bg-[#0f172a] dark:text-white lg:sticky lg:top-2 lg:h-[calc(100vh-1rem)] lg:shrink-0 lg:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-[calc(100%+1rem)]'"
            >
                <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-100 px-6 dark:border-white/5">
                    <a href="{{ route('welcome') }}" class="group flex items-center gap-3" title="Ir al inicio de SIDAN">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-500 text-lg font-bold text-white shadow-lg shadow-sidan-500/20">
                            S
                        </span>
                        <span class="text-xl font-bold tracking-tight text-sidan-900 group-hover:text-sidan-500 dark:text-white">
                            SIDAN
                        </span>
                    </a>
                </div>

                <nav aria-label="Módulos administrativos" class="flex-1 space-y-1 overflow-y-auto px-3 py-5">
                    <p class="mb-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Administración
                    </p>

                    @foreach ($menuPanel as $clave => $modulo)
                        @php
                            $activo = match ($clave) {
                                'dashboard' => $rutaActual === 'admin.dashboard',
                                'revision_actividades' => str_starts_with($rutaActual, 'admin.actividades.revision.'),
                                'actividades' => str_starts_with($rutaActual, 'admin.actividades.')
                                    && !str_starts_with($rutaActual, 'admin.actividades.revision.'),
                                'inscripciones' => $rutaActual === 'admin.control.inscripciones',
                                'ordenes' => $rutaActual === 'admin.control.ordenes',
                                'consolidado' => str_starts_with($rutaActual, 'admin.control.consolidado'),
                                'pagos' => str_starts_with($rutaActual, 'admin.pagos.')
                                    || str_starts_with($rutaActual, 'admin.ordenes.'),
                                'ventas_presenciales' => str_starts_with($rutaActual, 'admin.ventas-presenciales.'),
                                'usuarios' => str_starts_with($rutaActual, 'admin.users.'),
                                default => str_starts_with($rutaActual, 'admin.' . $clave . '.'),
                            };
                        @endphp

                        <a
                            href="{{ route($modulo['ruta']) }}"
                            @if ($activo) aria-current="page" @endif
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm transition-all {{ $activo
                                ? 'bg-sidan-500 font-bold text-white shadow-lg shadow-sidan-500/20'
                                : 'font-medium text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}"
                        >
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                viewBox="0 0 24 24" aria-hidden="true">
                                <path d="{{ $iconosPanel[$clave] }}" />
                            </svg>
                            <span>{{ $modulo['nombre'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="border-t border-slate-100 px-5 py-4 dark:border-white/5">
                    <p class="text-xs text-slate-400">Rol actual</p>
                    <p class="mt-1 text-sm font-semibold">
                        {{ ucfirst($usuarioPanel?->rol?->nombre ?? '') }}
                    </p>
                </div>
            </aside>

            <div
                x-show="sidebarOpen"
                @click="sidebarOpen = false"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
            ></div>
        @endif

        <div class="flex min-h-[calc(100vh-1rem)] min-w-0 flex-1 flex-col gap-2">
            @if ($mostrarPanel)
                <header class="sticky top-2 z-30 flex h-16 shrink-0 items-center justify-between rounded-2xl border border-slate-200/80 bg-white/95 px-4 shadow-sm backdrop-blur-xl dark:border-white/5 dark:bg-[#0f172a]/95 lg:px-8">
                    <div class="flex min-w-0 flex-1 items-center gap-4">
                        <button type="button" @click="sidebarOpen = true"
                            aria-label="Abrir menú"
                            class="shrink-0 text-slate-500 dark:text-gray-400 lg:hidden">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <div class="hidden max-w-xl flex-1 md:block">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    type="text"
                                    aria-label="Buscar"
                                    placeholder="Buscar actividades, estudiantes, pagos..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-900 focus:border-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <button type="button" onclick="toggleTheme()" aria-label="Cambiar tema" title="Cambiar tema"
                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-700 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200">
                            <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79Z" />
                            </svg>
                            <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <circle cx="12" cy="12" r="4" />
                                <path d="M12 2v2M12 20v2M2 12h2M20 12h2M5 5l2 2M17 17l2 2M5 19l2-2M17 7l2-2" />
                            </svg>
                        </button>

                        <button type="button" aria-label="Notificaciones"
                            class="relative rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:text-gray-400 dark:hover:bg-white/5">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-2-3v-3a6 6 0 00-12 0v3l-2 3h11zm-6 0v1a3 3 0 006 0v-1" />
                            </svg>
                            <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-sidan-500"></span>
                        </button>

                        <div class="relative" x-data="{ profileOpen: false }" @keydown.escape.window="profileOpen = false">
                            <button type="button" @click="profileOpen = !profileOpen"
                                @click.outside="profileOpen = false"
                                :aria-expanded="profileOpen"
                                aria-label="Abrir opciones de cuenta"
                                class="flex items-center gap-2 rounded-xl px-2 py-1 hover:bg-slate-100 dark:hover:bg-white/5">
                                <img
                                    src="{{ $usuarioPanel->imagen_perfil ? asset('storage/' . $usuarioPanel->imagen_perfil) : asset('images/usuario.png') }}"
                                    alt="Foto de perfil"
                                    class="h-8 w-8 rounded-full border border-slate-200 object-cover dark:border-white/10"
                                >
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="profileOpen" x-transition
                                class="absolute right-0 z-50 mt-2 w-60 rounded-2xl border border-slate-200 bg-white py-2 shadow-2xl dark:border-white/10 dark:bg-[#0f172a]"
                                style="display: none;">
                                <div class="border-b border-slate-100 px-4 py-3 dark:border-white/5">
                                    <p class="text-sm font-semibold">
                                        {{ $usuarioPanel->informacion_personal?->nombres }}
                                        {{ $usuarioPanel->informacion_personal?->apellidos }}
                                    </p>
                                    <p class="mt-1 truncate text-xs text-slate-500">
                                        {{ $usuarioPanel->correo }}
                                    </p>
                                </div>

                                <a href="{{ route('profile.edit') }}"
                                    class="block px-4 py-3 text-sm hover:bg-slate-100 dark:hover:bg-white/5">
                                    Mi perfil
                                </a>

                                <a href="{{ route('welcome') }}"
                                    class="block px-4 py-3 text-sm hover:bg-slate-100 dark:hover:bg-white/5">
                                    Ir al inicio
                                </a>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full px-4 py-3 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>
            @endif

            <section class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-[#f6f8fb] shadow-sm dark:border-white/5 dark:bg-sidan-950">
                <main class="flex-1 overflow-y-auto px-4 py-4 lg:px-8 lg:py-8">
                    @yield('content')
                </main>

                <footer class="shrink-0 border-t border-slate-200/80 bg-white/50 dark:border-white/5 dark:bg-white/[0.015]">
                    <div class="px-4 py-5 text-center text-sm text-slate-400 dark:text-slate-500">
                        © {{ date('Y') }} SIDAN. Todos los derechos reservados.
                    </div>
                </footer>
            </section>
        </div>
    </div>

    @stack('scripts')
</body>
</html>