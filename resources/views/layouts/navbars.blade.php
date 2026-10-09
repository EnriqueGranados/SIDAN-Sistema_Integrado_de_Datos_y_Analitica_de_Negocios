<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0F2D5B">

    <title>@yield('title', 'SIDAN - Dashboard')</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Figtree', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 999px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #10b981;
        }
    </style>

    <script>
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
    x-cloak>

    <x-toast />

    <div class="min-h-screen p-2 lg:flex lg:gap-2">

        <aside
            class="fixed top-2 bottom-2 left-2 z-50 flex w-64 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white text-slate-700 shadow-sm transition-all duration-300 dark:border-white/5 dark:bg-[#0f172a] dark:text-white lg:sticky lg:top-2 lg:h-[calc(100vh-1rem)] lg:shrink-0 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-[calc(100%+1rem)]'">

            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-slate-100 px-6 dark:border-white/5">

                <div
                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500 text-lg font-bold text-white shadow-lg shadow-emerald-500/20">
                    S
                </div>

                <span class="text-xl font-bold tracking-tight text-sidan-900 dark:text-white">
                    SIDAN
                </span>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-6">

                @php
                    $rol = auth()->user()->rol->nombre ?? 'usuario';
                    $is_admin = in_array($rol, ['admin', 'superadmin']);
                    $dashboardRoute = $is_admin ? route('admin.dashboard') : route('user.dashboard');
                @endphp

                <a href="{{ $dashboardRoute }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all
                    {{ request()->routeIs('*.dashboard')
                        ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}">

                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>

                    Dashboard
                </a>

                @if ($is_admin)

                    <div class="mt-6 border-t border-slate-100 pt-6 dark:border-white/5">

                        <p class="mb-3 px-4 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-gray-500">
                            Administración
                        </p>

                        <a href="{{ route('admin.actividades.index') }}"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all
                            {{ request()->routeIs('admin.actividades.*') && !request()->routeIs('admin.actividades.revision.*')
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>

                            Actividades
                        </a>

                        <a href="{{ route('admin.actividades.revision.index') }}"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all
                            {{ request()->routeIs('admin.actividades.revision.*')
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                </path>
                            </svg>

                            Revisión de actividades
                        </a>

                        <a href="{{ route('admin.categorias.index') }}"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all
                            {{ request()->routeIs('admin.categorias.*')
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h10">
                                </path>
                            </svg>

                            Categorías
                        </a>

                        <a href="{{ route('admin.etiquetas.index') }}"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all
                            {{ request()->routeIs('admin.etiquetas.*')
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M3 11l8.586-8.586A2 2 0 0113 2h5a2 2 0 012 2v5a2 2 0 01-.586 1.414L10.828 19a2 2 0 01-2.828 0L3 14a2 2 0 010-3z">
                                </path>
                            </svg>
                            Etiquetas
                        </a>

                        <a href="{{ route('admin.recursos.index') }}"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all
                                {{ request()->routeIs('admin.recursos.*')
                                    ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7h-9m9 5h-9m9 5h-9M7 7H4m3 5H4m3 5H4" />
                            </svg>
                            Recursos
                        </a>

                        <a href="{{ route('admin.espacios.index') }}"
                            class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-all
                                {{ request()->routeIs('admin.espacios.*')
                                    ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 21s6-4.35 6-10a6 6 0 10-12 0c0 5.65 6 10 6 10z" />
                                <circle cx="12" cy="11" r="2" stroke-width="2" />
                            </svg>
                            Espacios
                        </a>

                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-600 transition-all hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>

                            Pagos
                        </a>

                        <a href="#"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-600 transition-all hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>

                            Reportes
                        </a>

                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all
                            {{ request()->routeIs('admin.users.*')
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}">

                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>

                            Usuarios
                        </a>

                        @if (auth()->user()->rol->nombre === 'superadmin')

                            <a href="{{ route('admin.roles.index') }}"
                                class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all
                                {{ request()->routeIs('admin.roles.*')
                                    ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white' }}">

                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>

                                Roles del Sistema
                            </a>

                            <a href="{{ route('admin.welcome.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition {{ request()->routeIs('admin.welcome.*') ? 'bg-sidan-500 text-white' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-white/5' }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5Z"/>
                                    <path d="M3 9h18M8 9v12"/>
                                </svg>
                                <span>Administrar Welcome</span>
                            </a>

                        @endif
                    </div>
                @endif
            </nav>

            
        </aside>

        <div
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            x-transition.opacity
            class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden">
        </div>

        <div class="flex min-h-[calc(100vh-1rem)] min-w-0 flex-1 flex-col gap-2">

            <header
                class="sticky top-2 z-30 flex h-16 shrink-0 items-center justify-between rounded-2xl border border-slate-200/80 bg-white/95 px-4 shadow-sm backdrop-blur-xl transition-colors duration-150 dark:border-white/5 dark:bg-[#0f172a]/95 lg:px-8">

                <div class="flex min-w-0 flex-1 items-center gap-4">

                    <button
                        @click="sidebarOpen = true"
                        class="shrink-0 text-slate-500 transition hover:text-sidan-900 dark:text-gray-400 dark:hover:text-white lg:hidden">

                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16">
                            </path>
                        </svg>
                    </button>

                    <div class="hidden max-w-xl flex-1 items-center gap-3 md:flex">

                        <div class="relative flex-1">

                            <svg
                                class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-gray-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
                                </path>
                            </svg>

                            <input
                                type="text"
                                placeholder="Buscar actividades, estudiantes, pagos..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 transition focus:border-emerald-500/50 focus:bg-white focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder-gray-500 dark:focus:bg-white/10">
                        </div>
                    </div>
                </div>

                <div class="flex h-16 shrink-0 items-center gap-1 sm:gap-2">

                    <button
                        type="button"
                        onclick="toggleTheme()"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:-translate-y-0.5 hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
                        aria-label="Cambiar tema"
                        title="Cambiar tema">

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

                    <div class="mx-0.5 hidden h-5 w-px bg-slate-200 dark:bg-white/10 sm:block"></div>

                    <button
                        class="relative shrink-0 rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">

                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>

                        <span
                            class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#0f172a]">
                        </span>
                    </button>

                    <button
                        class="hidden shrink-0 rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white sm:block">

                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
                            </path>
                        </svg>
                    </button>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="m-0 flex shrink-0 items-center p-0">

                        @csrf

                        <button
                            type="submit"
                            class="group flex h-9 items-center gap-2 rounded-xl px-3 text-sm text-slate-500 transition hover:bg-red-50 hover:text-red-500 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-400">

                            <svg
                                class="h-4 w-4 transition-transform duration-500 group-hover:rotate-180"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>

                            <span class="hidden font-medium leading-none sm:inline">
                                Cerrar sesión
                            </span>
                        </button>
                    </form>

                    <div
                        class="relative ml-1 shrink-0"
                        x-data="{ profileOpen: false }">

                        <button
                            @click="profileOpen = !profileOpen"
                            @click.outside="profileOpen = false"
                            class="flex h-9 items-center gap-2 rounded-xl px-1 pr-3 transition hover:bg-slate-100 dark:hover:bg-white/5">

                            <img 
                                src="{{ auth()->user()->imagen_perfil ? asset('storage/' . auth()->user()->imagen_perfil) : asset('images/usuario.png') }}" 
                                alt="Foto de perfil" 
                                class="h-7 w-7 shrink-0 rounded-full object-cover border border-slate-200 dark:border-white/10"
                            >

                            <svg
                                class="h-4 w-4 shrink-0 text-slate-400 dark:text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div
                            x-show="profileOpen"
                            x-transition
                            class="absolute right-0 z-50 mt-2 w-56 rounded-2xl border border-slate-200 bg-white py-2 shadow-2xl dark:border-white/10 dark:bg-[#0f172a]"
                            style="display: none;">

                            <div class="border-b border-slate-100 px-4 py-3 dark:border-white/5">

                                <p class="text-sm font-semibold text-sidan-900 dark:text-white">
                                    {{ auth()->user()->nombres }}
                                    {{ auth()->user()->apellidos }}
                                </p>

                                <p class="truncate text-xs text-slate-500 dark:text-gray-500">
                                    {{ auth()->user()->correo }}
                                </p>
                            </div>

                            <a
                                href="{{ route('profile.edit') }}"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-sidan-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white">

                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                    </path>
                                </svg>

                                Mi perfil
                            </a>

                            <div class="my-2 border-t border-slate-100 dark:border-white/5"></div>

                            <form method="POST" action="{{ route('logout') }}">

                                @csrf

                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10 dark:hover:text-red-300">

                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                        </path>
                                    </svg>

                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <section
                class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-[#f6f8fb] shadow-sm transition-colors duration-150 dark:border-white/5 dark:bg-sidan-950">

                <main class="flex-1 overflow-y-auto px-4 py-4 lg:px-8 lg:py-8">
                    @yield('content')
                </main>

                <footer
                    class="shrink-0 border-t border-slate-200/80 bg-white/50 transition-colors duration-150 dark:border-white/5 dark:bg-white/[0.015]">

                    <div
                        class="px-4 py-5 text-center text-sm text-slate-400 sm:px-6 lg:px-8 dark:text-slate-500">

                        © {{ date('Y') }} SIDAN. Todos los derechos reservados.
                    </div>
                </footer>
            </section>
        </div>
    </div>

    @stack('scripts')
</body>

</html>