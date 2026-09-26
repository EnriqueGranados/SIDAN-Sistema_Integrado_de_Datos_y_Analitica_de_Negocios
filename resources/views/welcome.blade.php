@extends('layouts.public')

@section('title', 'SIDAN | Descubre actividades')

<head>
    <style>
        /* ===== LOADER ===== */

        #loader {
            position: fixed;
            inset: 0;
            z-index: 9999;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                radial-gradient(
                    circle at center,
                    rgba(34, 197, 94, 0.08),
                    transparent 45%
                ),
                #07111f;

            transition:
                opacity 0.8s ease,
                visibility 0.8s ease;
        }

        #loader.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.05em;
        }

        .loader-letter {
            display: inline-block;

            font-family: 'Figtree', sans-serif;
            font-size: clamp(4rem, 12vw, 10rem);
            font-weight: 800;
            letter-spacing: -0.06em;

            opacity: 0;
            transform: translateY(25px);

            animation:
                letterIn 0.65s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        /* Azul institucional */
        .loader-letter:nth-child(1),
        .loader-letter:nth-child(2),
        .loader-letter:nth-child(3),
        .loader-letter:nth-child(4),
        .loader-letter:nth-child(5) {
            color: #ffffff;
            text-shadow:
                0 0 30px rgba(15, 45, 91, 0.45);
        }

        .loader-letter:nth-child(1) {
            animation-delay: 0.05s;
        }

        .loader-letter:nth-child(2) {
            animation-delay: 0.15s;
        }

        .loader-letter:nth-child(3) {
            animation-delay: 0.25s;
        }

        .loader-letter:nth-child(4) {
            animation-delay: 0.35s;
        }

        .loader-letter:nth-child(5) {
            animation-delay: 0.45s;
        }

        @keyframes letterIn {
            from {
                opacity: 0;
                transform: translateY(25px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
</head>

@section('content')

{{-- ===== LOADER ===== --}}
    <div id="loader">
        <div class="flex gap-2">
            <span class="loader-letter">S</span>
            <span class="loader-letter">I</span>
            <span class="loader-letter">D</span>
            <span class="loader-letter">A</span>
            <span class="loader-letter">N</span>
        </div>
    </div>

<header x-data="{ mobileMenu: false }" class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl dark:border-white/10 dark:bg-sidan-950/90">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

        <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white shadow-lg shadow-sidan-900/20">S</div>
            <span class="text-xl font-black tracking-tight text-sidan-900 dark:text-white">SIDAN</span>
        </a>

        <nav class="hidden items-center gap-7 text-sm font-semibold text-slate-600 lg:flex dark:text-slate-300">
            <a href="#inicio" class="transition hover:text-sidan-500">Inicio</a>
            <a href="#actividades" class="transition hover:text-sidan-500">Explorar</a>
            <a href="#categorias" class="transition hover:text-sidan-500">Categorías</a>
            <a href="#como-funciona" class="transition hover:text-sidan-500">¿Cómo funciona?</a>
        </nav>

        <div class="hidden items-center gap-2 lg:flex">

            <button type="button" onclick="toggleTheme()" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:-translate-y-0.5 hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200" aria-label="Cambiar tema">
                <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>

                <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41" stroke-linecap="round"/>
                </svg>
            </button>

            <a href="{{ route('login') }}" class="rounded-xl px-4 py-2.5 text-sm font-bold text-sidan-900 transition hover:bg-slate-100 dark:text-white dark:hover:bg-white/5">
                Iniciar sesión
            </a>

            <a href="{{ route('register') }}" class="rounded-xl bg-sidan-500 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-green-500/20 transition hover:-translate-y-0.5 hover:bg-green-600">
                Registrarse
            </a>

        </div>

        <button type="button" @click="mobileMenu = !mobileMenu" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 lg:hidden dark:border-white/10" aria-label="Abrir menú">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
            </svg>
        </button>

    </div>

    <div x-show="mobileMenu" x-cloak x-transition class="border-t border-slate-200 bg-white px-4 py-4 lg:hidden dark:border-white/10 dark:bg-sidan-950">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 text-sm font-semibold">

            <a href="#inicio" @click="mobileMenu = false" class="rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-white/5">Inicio</a>

            <a href="#actividades" @click="mobileMenu = false" class="rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-white/5">Explorar</a>

            <a href="#categorias" @click="mobileMenu = false" class="rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-white/5">Categorías</a>

            <a href="#como-funciona" @click="mobileMenu = false" class="rounded-lg px-3 py-2 hover:bg-slate-100 dark:hover:bg-white/5">¿Cómo funciona?</a>

            <div class="mt-2 flex gap-2 border-t border-slate-200 pt-4 dark:border-white/10">

                <button type="button" onclick="toggleTheme()" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 dark:border-white/10" aria-label="Cambiar tema">
                    <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>

                    <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41" stroke-linecap="round"/>
                    </svg>
                </button>

                <a href="{{ route('login') }}" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-center font-bold dark:border-white/10">
                    Iniciar sesión
                </a>

                <a href="{{ route('register') }}" class="flex-1 rounded-xl bg-sidan-500 px-4 py-2.5 text-center font-bold text-white">
                    Registrarse
                </a>

            </div>
        </div>
    </div>
</header>

<main>

    <section id="inicio" class="relative overflow-hidden">

        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-[520px] bg-[radial-gradient(circle_at_20%_20%,rgba(34,197,94,0.13),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(15,45,91,0.14),transparent_35%)] dark:bg-[radial-gradient(circle_at_20%_20%,rgba(34,197,94,0.10),transparent_30%),radial-gradient(circle_at_80%_10%,rgba(23,69,127,0.35),transparent_35%)]"></div>

        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:py-16 lg:grid-cols-[1.04fr_.96fr] lg:items-center lg:px-8 lg:py-20">

            <div>

                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-sm font-bold text-green-700 dark:border-green-500/20 dark:bg-green-500/10 dark:text-green-300">
                    <span class="h-2 w-2 rounded-full bg-sidan-500"></span>
                    Explora. Descubre. Participa.
                </div>

                <h1 class="max-w-3xl text-4xl font-black leading-[1.05] tracking-tight text-sidan-900 sm:text-5xl md:text-6xl dark:text-white">
                    Encuentra algo que <span class="text-sidan-500">valga la pena vivir.</span>
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg dark:text-slate-300">
                    Congresos, talleres, excursiones, ventas, actividades académicas y mucho más. Mira qué está pasando y encuentra algo que conecte contigo.
                </p>

                <form action="{{ route('welcome') }}#actividades" method="GET" class="mt-8 grid gap-2 rounded-2xl border border-slate-200 bg-white p-2 shadow-soft sm:grid-cols-[1.3fr_1fr_.85fr_auto] dark:border-white/10 dark:bg-white/5 dark:shadow-none">

                    <label class="flex items-center gap-2 rounded-xl px-3 py-2.5">
                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m20 20-3.5-3.5" stroke-linecap="round"/>
                        </svg>

                        <input name="q" type="search" placeholder="¿Qué quieres encontrar?" class="w-full border-0 bg-transparent p-0 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white" />
                    </label>

                    <label class="flex items-center gap-2 rounded-xl border-t border-slate-100 px-3 py-2.5 sm:border-l sm:border-t-0 dark:border-white/10">

                        <svg class="h-5 w-5 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                            <circle cx="12" cy="10" r="2.5"/>
                        </svg>

                        <input name="ubicacion" type="text" placeholder="Ubicación" class="w-full border-0 bg-transparent p-0 text-sm text-slate-900 placeholder:text-slate-400 focus:ring-0 dark:text-white" />
                    </label>

                    <select name="categoria" class="rounded-xl border-0 bg-slate-50 px-3 py-2.5 text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-sidan-500 dark:bg-white/10 dark:text-slate-200">

                        <option value="">Categoría</option>

                        @foreach ($categorias as $categoria)
                            @if ($categoria !== 'Todas')
                                <option value="{{ $categoria }}">{{ $categoria }}</option>
                            @endif
                        @endforeach

                    </select>

                    <button type="submit" class="rounded-xl bg-sidan-500 px-5 py-2.5 text-sm font-black text-white transition hover:bg-green-600">
                        Buscar
                    </button>

                </form>

                <div class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500 dark:text-slate-400">

                    <span class="font-bold text-slate-700 dark:text-slate-200">
                        Popular:
                    </span>

                    <a href="#actividades" class="hover:text-sidan-500">Congresos</a>
                    <a href="#actividades" class="hover:text-sidan-500">Excursiones</a>
                    <a href="#actividades" class="hover:text-sidan-500">Talleres</a>
                    <a href="#actividades" class="hover:text-sidan-500">Ventas</a>

                </div>

            </div>

            <div class="grid h-[430px] grid-cols-2 grid-rows-2 gap-3 sm:h-[500px]">

                @foreach (array_slice($actividades, 0, 3) as $actividad)

                    <a href="{{ route('activities.show', $actividad['slug']) }}" class="group relative overflow-hidden rounded-3xl {{ $loop->first ? 'row-span-2' : '' }}">

                        <img src="{{ $actividad['imagen'] }}" alt="{{ $actividad['titulo'] }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" />

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/25 to-transparent"></div>

                        <div class="absolute left-4 top-4 rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-black text-slate-900 backdrop-blur">
                            {{ $actividad['categoria'] }}
                        </div>

                        <div class="absolute inset-x-0 bottom-0 p-4 sm:p-5">

                            <p class="text-xs font-bold uppercase tracking-wider text-green-300">
                                {{ $actividad['fecha'] }} · {{ $actividad['hora'] }}
                            </p>

                            <h3 class="mt-1 text-lg font-black leading-tight text-white sm:text-xl">
                                {{ $actividad['titulo'] }}
                            </h3>

                            <p class="mt-2 hidden text-sm text-white/70 sm:block">
                                {{ $actividad['lugar'] }}
                            </p>

                        </div>

                    </a>

                @endforeach

            </div>

        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">

        <div class="mb-5 flex items-end justify-between gap-4">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                    No te lo pierdas
                </p>

                <h2 class="mt-2 text-2xl font-black tracking-tight text-sidan-900 sm:text-3xl dark:text-white">
                    Destacado en SIDAN
                </h2>
            </div>

            <a href="#actividades" class="hidden text-sm font-black text-slate-500 transition hover:text-sidan-500 sm:inline-flex dark:text-slate-400">
                Ver todas las actividades →
            </a>
        </div>

        <article class="group relative overflow-hidden rounded-[2rem] bg-sidan-900 text-white shadow-2xl shadow-sidan-900/15 dark:border dark:border-white/10 dark:shadow-none">

            <img src="{{ $destacada['imagen'] }}" alt="{{ $destacada['titulo'] }}" class="absolute inset-0 h-full w-full object-cover opacity-35 transition duration-700 group-hover:scale-105" />

            <div class="absolute inset-0 bg-gradient-to-r from-sidan-950 via-sidan-900/95 to-sidan-900/40"></div>

            <div class="relative grid min-h-[320px] gap-8 px-6 py-10 sm:px-10 md:grid-cols-[1.2fr_.8fr] md:items-end lg:px-14 lg:py-14">

                <div>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex rounded-full bg-sidan-500 px-3 py-1 text-xs font-black uppercase tracking-wider">
                            Destacada
                        </span>

                        <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-black text-white backdrop-blur-md">
                            {{ $destacada['categoria'] }}
                        </span>
                    </div>

                    <h2 class="mt-4 max-w-2xl text-3xl font-black leading-tight sm:text-4xl lg:text-5xl">
                        {{ $destacada['titulo'] }}
                    </h2>

                    <p class="mt-4 max-w-2xl text-sm leading-6 text-white/75 sm:text-base">
                        {{ $destacada['descripcion'] }}
                    </p>

                    <div class="mt-5 flex flex-wrap gap-x-5 gap-y-2 text-sm font-semibold text-white/85">
                        <span>{{ $destacada['fecha'] }} · {{ $destacada['hora'] }}</span>
                        <span>{{ $destacada['lugar'] }}</span>
                        <span>{{ $destacada['precio'] }}</span>
                    </div>

                </div>

                <div class="flex md:justify-end">

                    <a href="{{ route('activities.show', $destacada['slug']) }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3.5 text-sm font-black text-sidan-900 transition hover:-translate-y-0.5 hover:bg-green-50">
                        Ver actividad →
                    </a>

                </div>

            </div>

        </article>

    </section>

    <section id="categorias" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col gap-4 border-y border-slate-200 py-6 dark:border-white/10 md:flex-row md:items-center md:justify-between">

            <div>
                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                    Explora a tu manera
                </p>

                <h2 class="mt-1 text-xl font-black text-sidan-900 dark:text-white">
                    ¿Qué te gustaría hacer?
                </h2>
            </div>

            <div class="flex flex-wrap gap-2">

                @foreach ($categorias as $categoria)

                    <a href="#actividades" class="rounded-full border px-4 py-2 text-sm font-bold transition {{ $loop->first ? 'border-sidan-900 bg-sidan-900 text-white dark:border-sidan-500 dark:bg-sidan-500' : 'border-slate-200 bg-white text-slate-600 hover:-translate-y-0.5 hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300' }}">
                        {{ $categoria }}
                    </a>

                @endforeach

            </div>

        </div>
    </section>


    <section id="actividades" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">

        <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

            <div>

                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                    Ahora mismo
                </p>

                <h2 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 sm:text-4xl dark:text-white">
                    Algo podría interesarte 👀
                </h2>

                <p class="mt-2 text-slate-600 dark:text-slate-400">
                    Descubre actividades activas y mira cuál te provoca decir “voy”.
                </p>

            </div>

            <a href="#actividades" class="inline-flex items-center gap-2 text-sm font-black text-sidan-900 transition hover:text-sidan-500 dark:text-white">
                Ver todas →
            </a>

        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

            @foreach ($actividades as $actividad)

                <article class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1.5 hover:border-green-300 hover:shadow-soft dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-green-500/30 dark:hover:shadow-none">

                    <a href="{{ route('activities.show', $actividad['slug']) }}" class="relative block h-52 overflow-hidden">

                        <img src="{{ $actividad['imagen'] }}" alt="{{ $actividad['titulo'] }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" />

                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent"></div>

                        <div class="absolute left-4 top-4 flex flex-wrap gap-2">

                            <span class="rounded-full bg-white/95 px-2.5 py-1 text-[11px] font-black text-sidan-900 shadow-sm">
                                {{ $actividad['categoria'] }}
                            </span>

                            <span class="rounded-full bg-sidan-500 px-2.5 py-1 text-[11px] font-black text-white shadow-sm">
                                {{ $actividad['estado'] }}
                            </span>

                        </div>

                        <div class="absolute bottom-4 left-4 rounded-xl bg-slate-950/70 px-3 py-2 text-center text-white backdrop-blur-md">
                            <span class="block text-xs font-bold tracking-wide">
                                {{ $actividad['fecha'] }}
                            </span>
                        </div>

                    </a>

                    <div class="p-5">

                        <div class="flex items-start justify-between gap-4">

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-sidan-500">
                                    {{ $actividad['organizacion'] }}
                                </p>

                                <h3 class="mt-1 text-xl font-black leading-snug text-sidan-900 transition group-hover:text-sidan-500 dark:text-white">
                                    {{ $actividad['titulo'] }}
                                </h3>
                            </div>

                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700 dark:bg-white/10 dark:text-slate-200">
                                {{ $actividad['precio'] }}
                            </span>

                        </div>

                        <p class="mt-3 min-h-[48px] text-sm leading-6 text-slate-600 dark:text-slate-400">
                            {{ $actividad['descripcion'] }}
                        </p>

                        <div class="mt-4 space-y-2 text-sm text-slate-500 dark:text-slate-400">

                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-sidan-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 7v5l3 2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>

                                <span>{{ $actividad['hora'] }}</span>
                            </div>

                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-sidan-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                    <circle cx="12" cy="10" r="2.5"/>
                                </svg>

                                <span>{{ $actividad['lugar'] }}</span>
                            </div>

                        </div>

                        <a href="{{ route('activities.show', $actividad['slug']) }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sidan-900 px-4 py-3 text-sm font-black text-white transition hover:bg-sidan-700 dark:bg-white dark:text-sidan-950 dark:hover:bg-slate-200">
                            Ver detalles →
                        </a>

                    </div>

                </article>

            @endforeach

        </div>

    </section>


    <section id="como-funciona" class="border-y border-slate-200 bg-white/70 dark:border-white/10 dark:bg-white/[0.025]">

        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">

            <div class="mx-auto max-w-2xl text-center">

                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">
                    Sin complicaciones
                </p>

                <h2 class="mt-2 text-3xl font-black tracking-tight text-sidan-900 sm:text-4xl dark:text-white">
                    De curioso a participante en 3 pasos.
                </h2>

                <p class="mt-3 text-slate-600 dark:text-slate-400">
                    Puedes explorar todo sin iniciar sesión. Solo te pediremos entrar cuando realmente quieras participar.
                </p>

            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-3">

                @foreach ([
                    ['01', 'Explora', 'Busca por categoría, ubicación o simplemente curiosea lo que está disponible.'],
                    ['02', 'Descubre', 'Abre una actividad y revisa fecha, lugar, costo, cupos y todos los detalles.'],
                    ['03', 'Participa', 'Cuando encuentres algo para ti, inicia sesión o crea tu cuenta y únete.']
                ] as $paso)

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04]">

                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-green-50 text-sm font-black text-green-700 dark:bg-green-500/10 dark:text-green-300">
                            {{ $paso[0] }}
                        </div>

                        <h3 class="mt-5 text-xl font-black text-sidan-900 dark:text-white">
                            {{ $paso[1] }}
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            {{ $paso[2] }}
                        </p>

                    </div>

                @endforeach

            </div>

        </div>

    </section>


    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">

        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-sidan-900 via-sidan-900 to-[#0d6a43] px-6 py-12 text-center text-white sm:px-10 lg:py-16">

            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-sidan-500/25 blur-3xl"></div>

            <div class="relative mx-auto max-w-2xl">

                <p class="text-sm font-black uppercase tracking-[0.18em] text-green-300">
                    Tu próxima experiencia
                </p>

                <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">
                    Puede estar a un clic de distancia.
                </h2>

                <p class="mt-4 text-white/75">
                    Explora primero. Decide después. En SIDAN siempre puedes ver lo que hay antes de crear una cuenta.
                </p>

                <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">

                    <a href="#actividades" class="rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white transition hover:bg-green-600">
                        Explorar actividades
                    </a>

                    <a href="{{ route('register') }}" class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white/15">
                        Crear mi cuenta
                    </a>

                </div>

            </div>

        </div>

    </section>

</main>


<footer class="border-t border-slate-200 bg-white dark:border-white/10 dark:bg-sidan-950">

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

        <div class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between">

            <div>

                <div class="flex items-center gap-2.5">

                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white">
                        S
                    </div>

                    <span class="text-xl font-black text-sidan-900 dark:text-white">
                        SIDAN
                    </span>

                </div>

                <p class="mt-3 max-w-sm text-sm text-slate-500 dark:text-slate-400">
                    Sistema Integrado de Datos y Analítica de Negocios.
                </p>

            </div>

            <div class="flex flex-wrap gap-x-6 gap-y-3 text-sm font-semibold text-slate-500 dark:text-slate-400">

                <a href="#actividades" class="hover:text-sidan-500">Explorar</a>
                <a href="#categorias" class="hover:text-sidan-500">Categorías</a>
                <a href="#como-funciona" class="hover:text-sidan-500">Cómo funciona</a>
                <a href="{{ route('login') }}" class="hover:text-sidan-500">Iniciar sesión</a>

            </div>

        </div>

        <div class="mt-8 border-t border-slate-200 pt-6 text-sm text-slate-400 dark:border-white/10">
            © {{ date('Y') }} SIDAN. Todos los derechos reservados.
        </div>

    </div>

</footer>

<script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('hidden');
                document.querySelector('.main-content').classList.add('visible');
                initCounters();
            }, 2200);
        });
    </script>

@endsection