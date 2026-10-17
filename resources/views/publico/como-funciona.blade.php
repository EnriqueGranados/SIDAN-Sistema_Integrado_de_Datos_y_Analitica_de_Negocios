
@extends('layouts.public')

@section('title', '¿Cómo funciona? | SIDAN')

@section('content')
    @include('partials.public-header')

    <main class="min-h-[calc(100vh-4rem)]">
        <section class="relative overflow-hidden border-b border-slate-200 bg-white dark:border-white/10 dark:bg-sidan-950">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(16,185,129,0.12),transparent_35%)]"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 md:py-20 lg:px-8">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                    Guía para usuarios
                </p>

                <h1 class="mt-3 max-w-3xl text-4xl font-black tracking-tight text-sidan-900 sm:text-5xl dark:text-white">
                    ¿Cómo funciona <span class="text-sidan-500">SIDAN?</span>
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">
                    Descubrí actividades, participá en eventos y adquirí productos desde un mismo lugar. Te explicamos el proceso de principio a fin.
                </p>

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('activities.index') }}"
                        class="rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-600">
                        Comenzar a explorar →
                    </a>

                    <a href="{{ route('publico.categorias') }}"
                        class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-sidan-900 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10">
                        Ver categorías
                    </a>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
            <div class="mb-8">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                    Empezar es sencillo
                </p>

                <h2 class="mt-2 text-2xl font-black text-sidan-900 sm:text-3xl dark:text-white">
                    Tu experiencia en cuatro pasos
                </h2>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['numero' => '01', 'titulo' => 'Explorá', 'texto' => 'Revisá actividades y utilizá sus filtros para encontrar algo que te interese.'],
                    ['numero' => '02', 'titulo' => 'Elegí', 'texto' => 'Consultá fechas, ubicación, modalidad, condiciones y productos disponibles.'],
                    ['numero' => '03', 'titulo' => 'Inscribite o comprá', 'texto' => 'Cuando la actividad lo requiera, completá tu inscripción o generá una orden de productos.'],
                    ['numero' => '04', 'titulo' => 'Consultá tu estado', 'texto' => 'Seguí tus órdenes y tus inscripciones desde tu cuenta SIDAN.'],
                ] as $paso)
                    <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <span class="text-4xl font-black tracking-tight text-sidan-500">
                            {{ $paso['numero'] }}
                        </span>

                        <h3 class="mt-5 text-lg font-black text-sidan-900 dark:text-white">
                            {{ $paso['titulo'] }}
                        </h3>

                        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            {{ $paso['texto'] }}
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="border-y border-slate-200 bg-white py-14 dark:border-white/10 dark:bg-white/[0.025]">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                    Compras y pagos
                </p>

                <h2 class="mt-2 text-2xl font-black text-sidan-900 sm:text-3xl dark:text-white">
                    Elegí cómo pagar tu orden
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-400">
                    Las opciones disponibles pueden depender de la actividad. Cada método tiene su propio proceso de confirmación.
                </p>

                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6 dark:border-white/10 dark:bg-sidan-950/70">
                        <div class="inline-flex rounded-xl bg-emerald-100 px-3 py-2 text-sm font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                            Wompi
                        </div>

                        <h3 class="mt-4 text-lg font-black text-sidan-900 dark:text-white">
                            Pago en línea
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            El sistema te dirige a Wompi. Cuando la plataforma confirma el pago, SIDAN actualiza automáticamente tu orden.
                        </p>
                    </article>

                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6 dark:border-white/10 dark:bg-sidan-950/70">
                        <div class="inline-flex rounded-xl bg-emerald-100 px-3 py-2 text-sm font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                            Transferencia
                        </div>

                        <h3 class="mt-4 text-lg font-black text-sidan-900 dark:text-white">
                            Comprobante de pago
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            Consultá los datos bancarios, realizá la transferencia y adjuntá el comprobante. Un administrador revisará tu solicitud.
                        </p>
                    </article>

                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6 dark:border-white/10 dark:bg-sidan-950/70">
                        <div class="inline-flex rounded-xl bg-emerald-100 px-3 py-2 text-sm font-black text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                            Efectivo
                        </div>

                        <h3 class="mt-4 text-lg font-black text-sidan-900 dark:text-white">
                            Pago presencial
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            Tu solicitud queda pendiente hasta que entregués el efectivo y el administrador confirme la recepción.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-sidan-500">
                Preguntas frecuentes
            </p>

            <h2 class="mt-2 text-2xl font-black text-sidan-900 sm:text-3xl dark:text-white">
                Todo lo que necesitás saber
            </h2>

            <div class="mt-7 space-y-3">
                @foreach ([
                    ['pregunta' => '¿Necesito crear una cuenta?', 'respuesta' => 'Podés descubrir actividades sin iniciar sesión. Para generar compras y gestionar tus inscripciones u órdenes, SIDAN te solicitará una cuenta.'],
                    ['pregunta' => '¿Qué ocurre si una orden vence sin pagar?', 'respuesta' => 'Las órdenes pendientes pueden vencer según las reglas de reserva del sistema. En ese caso, deberás generar una nueva orden si todavía querés participar o comprar.'],
                    ['pregunta' => '¿Qué pasa si pago con transferencia o efectivo?', 'respuesta' => 'La orden permanece pendiente de verificación hasta que un administrador confirme el pago. Podés consultar su estado desde Mis órdenes.'],
                    ['pregunta' => '¿Dónde reviso mis compras e inscripciones?', 'respuesta' => 'Cuando iniciés sesión, encontrarás Mis órdenes y Mis inscripciones en la navegación de tu cuenta.'],
                ] as $faq)
                    <details class="group rounded-2xl border border-slate-200 bg-white p-5 dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-bold text-sidan-900 dark:text-white">
                            <span>{{ $faq['pregunta'] }}</span>
                            <span class="text-lg text-sidan-500 transition group-open:rotate-45">+</span>
                        </summary>

                        <p class="mt-3 pr-5 text-sm leading-7 text-slate-600 dark:text-slate-400">
                            {{ $faq['respuesta'] }}
                        </p>
                    </details>
                @endforeach
            </div>

            <div class="mt-10 rounded-3xl bg-sidan-900 px-6 py-8 text-white sm:px-9 dark:border dark:border-white/10">
                <h2 class="text-2xl font-black">
                    ¿Listo para encontrar tu próxima actividad?
                </h2>

                <p class="mt-2 text-sm leading-6 text-white/70">
                    Explorá el catálogo público y descubrí qué hay disponible en SIDAN.
                </p>

                <a href="{{ route('activities.index') }}"
                    class="mt-6 inline-flex rounded-xl bg-sidan-500 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-600">
                    Explorar actividades →
                </a>
            </div>
        </section>
    </main>
@endsection
