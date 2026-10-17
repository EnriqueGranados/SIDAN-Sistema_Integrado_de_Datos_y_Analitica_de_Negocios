
@extends('layouts.public')

@section('title', 'Categorías | SIDAN')

@section('content')
    @include('partials.public-header')

    <main class="min-h-[calc(100vh-4rem)]">
        <section class="relative overflow-hidden border-b border-slate-200 bg-white dark:border-white/10 dark:bg-sidan-950">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_10%,rgba(16,185,129,0.10),transparent_35%)]"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-black uppercase tracking-wider text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Descubre SIDAN
                </div>

                <h1 class="max-w-3xl text-4xl font-black tracking-tight text-sidan-900 sm:text-5xl dark:text-white">
                    Explora por <span class="text-sidan-500">categorías.</span>
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">
                    Encuentra actividades de tu interés entre las categorías disponibles. Cada categoría te lleva a las actividades públicas que le corresponden.
                </p>

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <a href="{{ route('activities.index') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-600">
                        Explorar todas las actividades →
                    </a>

                    <a href="{{ route('publico.como-funciona') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-sidan-900 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10">
                        ¿Cómo funciona SIDAN?
                    </a>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
            <div class="mb-7 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                        Encuentra tu próxima actividad
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-sidan-900 sm:text-3xl dark:text-white">
                        Todas las categorías disponibles
                    </h2>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Únicamente aparecen categorías con actividades públicas disponibles en SIDAN.
                    </p>
                </div>

                <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                    {{ $categorias->count() }} categoría(s)
                </span>
            </div>

            @if ($categorias->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($categorias as $categoria)
                        <a href="{{ route('activities.index', ['categoria' => $categoria->nombre]) }}"
                            class="group relative flex min-h-48 flex-col rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-emerald-400 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-emerald-500/50">

                            <div class="mb-5 flex items-start justify-between gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-xl font-black text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    {{ mb_strtoupper(mb_substr($categoria->nombre, 0, 1)) }}
                                </span>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-white/10 dark:text-slate-300">
                                    {{ $categoria->actividades_publicas_count }} actividad(es)
                                </span>
                            </div>

                            <h3 class="text-lg font-black text-sidan-900 transition group-hover:text-emerald-600 dark:text-white dark:group-hover:text-emerald-300">
                                {{ $categoria->nombre }}
                            </h3>

                            <p class="mt-2 line-clamp-3 flex-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                {{ $categoria->descripcion ?: 'Descubre las actividades disponibles dentro de esta categoría.' }}
                            </p>

                            <span class="mt-5 inline-flex items-center gap-2 text-sm font-black text-emerald-600 dark:text-emerald-400">
                                Ver actividades <span aria-hidden="true">→</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center dark:border-white/20 dark:bg-white/[0.04]">
                    <h3 class="text-lg font-black text-sidan-900 dark:text-white">
                        Todavía no hay categorías con actividades públicas
                    </h3>

                    <p class="mx-auto mt-2 max-w-lg text-sm text-slate-500 dark:text-slate-400">
                        Cuando se publiquen actividades, sus categorías aparecerán automáticamente aquí.
                    </p>

                    <a href="{{ route('activities.index') }}"
                        class="mt-6 inline-flex rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white hover:bg-emerald-600">
                        Ir a Explorar
                    </a>
                </div>
            @endif
        </section>

        @if ($etiquetas->isNotEmpty())
            <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 dark:border-white/10 dark:bg-white/[0.04]">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                        Explora a tu manera
                    </p>

                    <h2 class="mt-2 text-2xl font-black text-sidan-900 dark:text-white">
                        Buscar por etiquetas
                    </h2>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        También podés descubrir actividades por temas específicos.
                    </p>

                    <div class="mt-6 flex flex-wrap gap-2.5">
                        @foreach ($etiquetas as $etiqueta)
                            <a href="{{ route('activities.index', ['etiqueta' => $etiqueta->nombre]) }}"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-700 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-300">
                                #{{ $etiqueta->nombre }}
                                <span class="ml-1 text-xs opacity-60">
                                    {{ $etiqueta->actividades_publicas_count }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>
@endsection
