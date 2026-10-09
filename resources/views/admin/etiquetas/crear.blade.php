@extends('layouts.navbars')

@section('title', 'Nueva etiqueta')

@section('content')
<div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.etiquetas.index') }}"
                class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a etiquetas
            </a>

            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                Nueva etiqueta
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                Crea una etiqueta para identificar una característica específica que pueda
                aplicarse a una o varias actividades.
            </p>
        </div>

        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
            <button
                type="button"
                id="btnGuiaEtiquetas"
                aria-expanded="false"
                aria-controls="guiaEtiquetas"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-slate-50 dark:hover:bg-white/[0.03]">

                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                            ¿No sabes qué etiqueta crear?
                        </p>

                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                            Consulta algunos criterios y ejemplos antes de crearla.
                        </p>
                    </div>
                </div>

                <svg
                    id="iconoGuiaEtiquetas"
                    class="h-5 w-5 shrink-0 text-slate-400 transition-transform duration-200 dark:text-slate-500"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div id="guiaEtiquetas" class="hidden border-t border-slate-200 dark:border-white/5">
                <div class="space-y-5 px-5 py-5">
                    <div>
                        <h2 class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                            ¿Cuándo conviene crear una etiqueta?
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            Utiliza una etiqueta cuando necesites describir una característica
                            adicional que pueda compartirse entre distintas actividades.
                            Una misma actividad puede tener varias etiquetas.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/5 dark:bg-white/[0.02]">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Buenos ejemplos
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="rounded-lg bg-slate-200 px-2.5 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    Presencial
                                </span>
                                <span class="rounded-lg bg-slate-200 px-2.5 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    Virtual
                                </span>
                                <span class="rounded-lg bg-slate-200 px-2.5 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    Principiantes
                                </span>
                                <span class="rounded-lg bg-slate-200 px-2.5 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    Excel
                                </span>
                                <span class="rounded-lg bg-slate-200 px-2.5 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    Emprendimiento
                                </span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/5 dark:bg-white/[0.02]">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Diferencia con una categoría
                            </p>

                            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                La
                                <strong class="font-semibold text-slate-800 dark:text-slate-300">
                                    categoría
                                </strong>
                                indica la clasificación principal de la actividad.
                                Las
                                <strong class="font-semibold text-slate-800 dark:text-slate-300">
                                    etiquetas
                                </strong>
                                agregan características más específicas.
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/20 dark:bg-amber-500/5">
                        <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                            <strong class="font-semibold text-amber-700 dark:text-amber-300">
                                Ejemplo:
                            </strong>
                            una actividad puede pertenecer a la categoría
                            <span class="font-medium text-slate-800 dark:text-slate-300">
                                Capacitación
                            </span>
                            y utilizar las etiquetas
                            <span class="font-medium text-slate-800 dark:text-slate-300">Excel</span>,
                            <span class="font-medium text-slate-800 dark:text-slate-300">Principiantes</span>
                            y
                            <span class="font-medium text-slate-800 dark:text-slate-300">Presencial</span>.
                        </p>
                    </div>

                    <p class="text-xs leading-5 text-slate-500">
                        Procura utilizar nombres cortos y concretos. Antes de crear una nueva
                        etiqueta, verifica que no exista otra con el mismo significado.
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.etiquetas.store') }}">
            @csrf

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                <div class="border-b border-slate-200 px-6 py-5 dark:border-white/5">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                        Información de la etiqueta
                    </h2>

                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Utiliza un nombre corto, claro y fácil de reconocer.
                    </p>
                </div>

                <div class="space-y-6 p-6">
                    <div>
                        <label for="nombre"
                            class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Nombre de la etiqueta
                            <span class="text-red-600 dark:text-red-400">*</span>
                        </label>

                        <input
                            id="nombre"
                            name="nombre"
                            type="text"
                            maxlength="120"
                            required
                            autofocus
                            value="{{ old('nombre') }}"
                            placeholder="Ej. Principiantes"
                            class="w-full rounded-xl border bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-600 dark:focus:bg-white/10
                                {{ $errors->has('nombre')
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                    : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-white/10' }}">

                        @error('nombre')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror

                        <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                            Describe una característica concreta. Evita nombres demasiado amplios
                            que deberían utilizarse como categoría.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/5 dark:bg-white/[0.02]">
                        <label class="flex cursor-pointer items-start gap-3">
                            <input type="hidden" name="activo" value="0">

                            <input
                                id="activo"
                                name="activo"
                                type="checkbox"
                                value="1"
                                @checked(old('activo', '1') == '1')
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 bg-white text-emerald-500 focus:ring-emerald-500 focus:ring-offset-white dark:border-slate-600 dark:bg-slate-900 dark:focus:ring-offset-[#0f172a]">

                            <span>
                                <span class="block text-sm font-medium text-slate-900 dark:text-white">
                                    Disponible para utilizar
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                                    Si está activa podrá seleccionarse al configurar actividades.
                                    Puedes desactivarla posteriormente sin eliminarla.
                                </span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4 sm:flex-row sm:justify-end dark:border-white/5 dark:bg-white/[0.02]">
                    <a href="{{ route('admin.etiquetas.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Crear etiqueta
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function showAppNotification(type, title, message) {
    const previous = document.getElementById('app-notification');
    if (previous) previous.remove();

    const styles = {
        success: {
            iconColor: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
            borderColor: 'border-emerald-200 dark:border-emerald-500/20',
            progressColor: 'bg-emerald-500',
            progressBackground: 'bg-emerald-100 dark:bg-emerald-950/50',
            iconPath: 'M5 13l4 4L19 7'
        },
        error: {
            iconColor: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
            borderColor: 'border-red-200 dark:border-red-500/20',
            progressColor: 'bg-red-500',
            progressBackground: 'bg-red-100 dark:bg-red-950/50',
            iconPath: 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z'
        },
        warning: {
            iconColor: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            borderColor: 'border-amber-200 dark:border-amber-500/20',
            progressColor: 'bg-amber-500',
            progressBackground: 'bg-amber-100 dark:bg-amber-950/50',
            iconPath: 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z'
        },
        info: {
            iconColor: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            borderColor: 'border-blue-200 dark:border-blue-500/20',
            progressColor: 'bg-blue-500',
            progressBackground: 'bg-blue-100 dark:bg-blue-950/50',
            iconPath: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
        }
    };

    const style = styles[type] || styles.info;
    const notification = document.createElement('div');

    notification.id = 'app-notification';
    notification.className = 'fixed right-4 bottom-4 sm:right-6 sm:bottom-6 z-[150] w-[calc(100%-2rem)] max-w-sm translate-y-6 opacity-0 transition-all duration-300 ease-out';

    notification.innerHTML = `
        <div class="relative overflow-hidden rounded-2xl border ${style.borderColor} bg-white shadow-2xl dark:bg-[#0f172a]">
            <div class="flex items-start gap-3 p-4 pr-12">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${style.iconColor}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${style.iconPath}"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="notification-title text-sm font-black text-slate-900 dark:text-white"></p>
                    <p class="notification-message mt-1 whitespace-pre-line text-sm leading-5 text-slate-600 dark:text-slate-400"></p>
                </div>
            </div>
            <button type="button"
                class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                </svg>
            </button>
            <div class="h-1 w-full ${style.progressBackground}">
                <div class="notification-progress h-full w-full origin-left ${style.progressColor}"></div>
            </div>
        </div>
    `;

    notification.querySelector('.notification-title').textContent = title;
    notification.querySelector('.notification-message').textContent = message;
    document.body.appendChild(notification);

    const progress = notification.querySelector('.notification-progress');
    const closeButton = notification.querySelector('.notification-close');
    let timeout;

    const closeNotification = () => {
        clearTimeout(timeout);
        notification.classList.remove('opacity-100', 'translate-y-0');
        notification.classList.add('opacity-0', 'translate-y-6');
        setTimeout(() => notification.remove(), 300);
    };

    closeButton.addEventListener('click', closeNotification);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            notification.classList.remove('opacity-0', 'translate-y-6');
            notification.classList.add('opacity-100', 'translate-y-0');
            progress.style.transition = 'transform 5s linear';
            progress.style.transform = 'scaleX(0)';
        });
    });

    timeout = setTimeout(closeNotification, 5000);
}

document.addEventListener('DOMContentLoaded', function () {
    @if ($errors->any())
        showAppNotification(
            'warning',
            'Revisa los datos de la etiqueta',
            @json(collect($errors->all())->implode("\n"))
        );
    @endif

    @if (session('error'))
        showAppNotification(
            'error',
            'No se pudo crear la etiqueta',
            @json(session('error'))
        );
    @endif

    const boton = document.getElementById('btnGuiaEtiquetas');
    const contenido = document.getElementById('guiaEtiquetas');
    const icono = document.getElementById('iconoGuiaEtiquetas');

    if (!boton || !contenido || !icono) return;

    boton.addEventListener('click', function () {
        const abierto = boton.getAttribute('aria-expanded') === 'true';
        boton.setAttribute('aria-expanded', abierto ? 'false' : 'true');
        contenido.classList.toggle('hidden', abierto);
        icono.classList.toggle('rotate-180', !abierto);
    });
});
</script>
@endsection