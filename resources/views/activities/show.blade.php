@extends('layouts.public')

@section('title', $actividad['titulo'].' | SIDAN')

@section('content')

<header class="border-b border-slate-200 bg-white dark:border-white/10 dark:bg-sidan-950">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

        <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sidan-900 text-sm font-black text-white">S</div>
            <span class="text-xl font-black text-sidan-900 dark:text-white">SIDAN</span>
        </a>

        <div class="flex items-center gap-2">

            <button type="button" onclick="toggleTheme()" class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 dark:border-white/10" aria-label="Cambiar tema">

                <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>

                <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.66 6.34l1.41-1.41" stroke-linecap="round"/>
                </svg>

            </button>

            @guest

                <a href="{{ route('login') }}" class="rounded-xl bg-sidan-900 px-4 py-2.5 text-sm font-black text-white dark:bg-white dark:text-sidan-950">
                    Iniciar sesión
                </a>

            @endguest

        </div>

    </div>
</header>


<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">

    <a href="{{ route('welcome') }}#actividades" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-sidan-500 dark:text-slate-400">
        ← Volver a actividades
    </a>

    <div class="mt-6 grid gap-8 lg:grid-cols-[1.15fr_.85fr]">

        <div>

            <div class="overflow-hidden rounded-[2rem]">
                <img src="{{ $actividad['imagen'] }}" alt="{{ $actividad['titulo'] }}" class="h-[360px] w-full object-cover sm:h-[480px]" />
            </div>

            <div class="mt-7">

                <div class="flex flex-wrap gap-2">

                    <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700 dark:bg-green-500/10 dark:text-green-300">
                        {{ $actividad['categoria'] }}
                    </span>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700 dark:bg-white/10 dark:text-slate-200">
                        {{ $actividad['estado'] }}
                    </span>

                </div>

                <h1 class="mt-4 text-3xl font-black tracking-tight text-sidan-900 sm:text-4xl dark:text-white">
                    {{ $actividad['titulo'] }}
                </h1>

                <p class="mt-4 max-w-3xl text-base leading-7 text-slate-600 dark:text-slate-300">
                    {{ $actividad['descripcion'] }}
                </p>

            </div>

        </div>


        <aside class="lg:pt-4">

            <div class="sticky top-24 rounded-3xl border border-slate-200 bg-white p-6 shadow-soft dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">

                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                    Información
                </p>

                <div class="mt-5 space-y-5">

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Fecha y hora</p>
                        <p class="mt-1 font-bold">{{ $actividad['fecha'] }} · {{ $actividad['hora'] }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Lugar</p>
                        <p class="mt-1 font-bold">{{ $actividad['lugar'] }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Organiza</p>
                        <p class="mt-1 font-bold">{{ $actividad['organizacion'] }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Costo</p>
                        <p class="mt-1 text-xl font-black text-sidan-500">{{ $actividad['precio'] }}</p>
                    </div>

                </div>

                @guest

                    <a href="{{ route('login') }}" class="mt-7 flex w-full items-center justify-center rounded-xl bg-sidan-500 px-5 py-3.5 text-sm font-black text-white transition hover:bg-green-600">
                        Participar
                    </a>

                    <p class="mt-3 text-center text-xs leading-5 text-slate-400">
                        Para participar necesitas iniciar sesión o crear una cuenta.
                    </p>

                @else

                    <button type="button" class="mt-7 w-full rounded-xl bg-sidan-500 px-5 py-3.5 text-sm font-black text-white">
                        Participar
                    </button>

                @endguest

            </div>

        </aside>

    </div>

</main>

@endsection