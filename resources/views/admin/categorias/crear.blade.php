@extends('layouts.navbars')

@section('title', 'Nueva categoría')

@section('content')
    <div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
        <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('admin.categorias.index') }}"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 18-6-6 6-6" />
                    </svg>
                    Volver a categorías
                </a>

                <h1 class="text-2xl font-black text-sidan-900 dark:text-white">Nueva categoría</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Crea una categoría para organizar las actividades
                    del sistema.</p>
            </div>

            <form method="POST" action="{{ route('admin.categorias.store') }}"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                @csrf

                <div class="border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-white/5">
                    <h2 class="font-semibold text-slate-900 dark:text-white">Información de la categoría</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-500">
                        El slug y la posición se administrarán automáticamente.
                    </p>
                </div>

                <div class="space-y-6 p-5 sm:p-6">
                    <div>
                        <label for="nombre" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-300">
                            Nombre
                            <span class="text-red-600 dark:text-red-400">*</span>
                        </label>

                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" maxlength="100"
                            required autofocus placeholder="Ej. Cursos y talleres"
                            class="h-11 w-full rounded-xl border bg-slate-50 px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-600 dark:focus:bg-white/10
                            @error('nombre')
                                border-red-500 focus:border-red-500 focus:ring-red-500/20
                            @else
                                border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-white/10
                            @enderror">

                        @error('nombre')
                            <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-500">
                            Utiliza un nombre claro que permita identificar fácilmente el tipo de actividad.
                        </p>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="descripcion" class="block text-sm font-semibold text-slate-700 dark:text-gray-300">
                                Descripción
                            </label>

                            <span class="text-xs text-slate-400 dark:text-slate-600">Opcional</span>
                        </div>

                        <textarea id="descripcion" name="descripcion" rows="5"
                            placeholder="Describe brevemente qué tipo de actividades pertenecen a esta categoría..."
                            class="w-full resize-y rounded-xl border bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-600 dark:focus:bg-white/10
                            @error('descripcion')
                                border-red-500 focus:border-red-500 focus:ring-red-500/20
                            @else
                                border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-white/10
                            @enderror">{{ old('descripcion') }}</textarea>

                        @error('descripcion')
                            <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="activo" class="mb-2 block text-sm font-semibold text-slate-700 dark:text-gray-300">
                            Estado
                            <span class="text-red-600 dark:text-red-400">*</span>
                        </label>

                        <select id="activo" name="activo" required
                            class="h-11 w-full rounded-xl border bg-slate-50 px-3 text-sm text-slate-900 outline-none transition focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:focus:bg-white/10
                            @error('activo')
                                border-red-500 focus:border-red-500 focus:ring-red-500/20
                            @else
                                border-slate-200 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-white/10
                            @enderror">
                            <option value="1" @selected(old('activo', '1') === '1')>
                                Activa
                            </option>
                            <option value="0" @selected(old('activo') === '0')>
                                Inactiva
                            </option>
                        </select>

                        @error('activo')
                            <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-500">
                            Puedes desactivar una categoría para conservarla sin tener que eliminarla.
                        </p>
                    </div>

                    <div
                        class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-500/20 dark:bg-indigo-500/5">
                        <div class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>

                            <div>
                                <p class="text-sm font-semibold text-indigo-700 dark:text-indigo-300">
                                    Posición automática
                                </p>
                                <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                    La nueva categoría se colocará al final de la lista. Después podrás cambiar su posición
                                    directamente desde el listado de categorías.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-white/5 dark:bg-white/[0.02]">
                    <a href="{{ route('admin.categorias.index') }}"
                        class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12l4 4L19 6" />
                        </svg>
                        Crear categoría
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAppNotification(type, title, message) {
            const previous = document.getElementById('app-notification');
            if (previous) {
                previous.remove();
            }

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
            notification.className =
                'fixed right-4 bottom-4 sm:right-6 sm:bottom-6 z-[150] w-[calc(100%-2rem)] max-w-sm translate-y-6 opacity-0 transition-all duration-300 ease-out';
            notification.innerHTML = `
        <div class="relative overflow-hidden rounded-2xl border ${style.borderColor} bg-white shadow-2xl dark:bg-[#0f172a]">
            <div class="flex items-start gap-3 p-4 pr-12">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${style.iconColor}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${style.iconPath}"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="notification-title text-sm font-black text-sidan-900 dark:text-white"></p>
                    <p class="notification-message mt-1 whitespace-pre-line text-sm leading-5 text-slate-600 dark:text-gray-400"></p>
                </div>
            </div>
            <button type="button"
                class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                aria-label="Cerrar notificación">
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

                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
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

        document.addEventListener('DOMContentLoaded', () => {
            @if ($errors->any())
                showAppNotification(
                    'warning',
                    'Revisa los datos de la categoría',
                    @json(collect($errors->all())->implode("\n"))
                );
            @endif

            @if (session('error'))
                showAppNotification(
                    'error',
                    'No se pudo crear la categoría',
                    @json(session('error'))
                );
            @endif

            @if (session('success'))
                showAppNotification(
                    'success',
                    'Categoría creada correctamente',
                    @json(session('success'))
                );
            @endif
        });
    </script>
@endsection
