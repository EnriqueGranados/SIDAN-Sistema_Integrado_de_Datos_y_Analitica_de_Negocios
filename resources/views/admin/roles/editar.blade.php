@extends('layouts.navbars')

@section('title', 'Editar Rol')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-black text-sidan-900 dark:text-white">
                Editar Rol:
                <span class="text-emerald-600 dark:text-emerald-400">
                    {{ ucfirst($rol->nombre) }}
                </span>
            </h1>

            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Actualiza la información del perfil de acceso.
            </p>
        </div>

        <form action="{{ route('admin.roles.update', $rol) }}" method="POST"
            class="bg-white border border-slate-200 rounded-2xl p-6 space-y-6 shadow-sm transition-colors dark:bg-[#0f172a] dark:border-white/5">

            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">
                    Nombre del Rol *
                </label>

                <input type="text" name="nombre" value="{{ old('nombre', $rol->nombre) }}"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 focus:bg-white dark:bg-white/5 dark:border-white/10 dark:text-white dark:focus:bg-white/10"
                    required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-gray-300 mb-2">
                    Descripción *
                </label>

                <textarea name="descripcion" rows="4"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/10 focus:bg-white dark:bg-white/5 dark:border-white/10 dark:text-white dark:focus:bg-white/10"
                    required>{{ old('descripcion', $rol->descripcion) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-white/5">

                <a href="{{ route('admin.roles.index') }}"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded-xl hover:bg-slate-100 hover:text-sidan-900 transition dark:text-gray-300 dark:bg-white/5 dark:border-white/10 dark:hover:bg-white/10 dark:hover:text-white">

                    Cancelar
                </a>

                <button type="submit"
                    class="px-6 py-2 text-sm font-bold text-white bg-emerald-500 rounded-xl hover:bg-emerald-600 shadow-lg shadow-emerald-500/20 transition">

                    Actualizar Rol
                </button>
            </div>
        </form>
    </div>

    <script>
        // Mostrar notificaciones de éxito, error, advertencia o información
        function showAppNotification(type, title, message) {
            const previous = document.getElementById('app-notification');

            // Evitar mostrar varias notificaciones al mismo tiempo
            if (previous) {
                previous.remove();
            }

            // Definir la apariencia de la notificación según su tipo
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

            // Construir el contenido visual de la notificación
            notification.innerHTML = `
            <div class="relative overflow-hidden rounded-2xl border ${style.borderColor} bg-white shadow-2xl dark:bg-[#0f172a]">
                <div class="flex items-start gap-3 p-4 pr-12">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${style.iconColor}">
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="${style.iconPath}">
                            </path>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="notification-title text-sm font-black text-sidan-900 dark:text-white">
                        </p>

                        <p
                            class="notification-message mt-1 text-sm leading-5 text-slate-600 dark:text-gray-400 whitespace-pre-line">
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                    aria-label="Cerrar notificación">

                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18 18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>

                <div class="h-1 w-full ${style.progressBackground}">
                    <div
                        class="notification-progress h-full w-full origin-left ${style.progressColor}">
                    </div>
                </div>
            </div>
        `;

            notification.querySelector('.notification-title').textContent = title;
            notification.querySelector('.notification-message').textContent = message;

            document.body.appendChild(notification);

            const progress = notification.querySelector('.notification-progress');
            const closeButton = notification.querySelector('.notification-close');
            let timeout;

            // Cerrar y eliminar la notificación
            const closeNotification = () => {
                clearTimeout(timeout);

                notification.classList.remove(
                    'opacity-100',
                    'translate-y-0'
                );

                notification.classList.add(
                    'opacity-0',
                    'translate-y-6'
                );

                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            };

            // Permitir cerrar manualmente la notificación
            closeButton.addEventListener(
                'click',
                closeNotification
            );

            // Mostrar la notificación con una animación de entrada
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    notification.classList.remove(
                        'opacity-0',
                        'translate-y-6'
                    );

                    notification.classList.add(
                        'opacity-100',
                        'translate-y-0'
                    );

                    progress.style.transition = 'transform 5s linear';
                    progress.style.transform = 'scaleX(0)';
                });
            });

            // Cerrar automáticamente la notificación después de 5 segundos
            timeout = setTimeout(
                closeNotification,
                5000
            );
        }

        // Mostrar los mensajes generados al intentar actualizar el rol
        document.addEventListener('DOMContentLoaded', function() {

            // Mostrar los errores de validación recibidos desde Laravel
            @if ($errors->any())

                @php
                    $mensajes = collect($errors->all())
                        ->map(function ($error) {
                            return match ($error) {
                                'The nombre field is required.' => 'El nombre del rol es obligatorio.',

                                'The nombre has already been taken.' => 'Ya existe otro rol con ese nombre.',

                                'The nombre must be a string.' => 'El nombre del rol debe ser un texto válido.',

                                'The nombre may not be greater than 255 characters.' => 'El nombre del rol no puede superar los 255 caracteres.',

                                'The descripcion field is required.' => 'La descripción del rol es obligatoria.',

                                'The descripcion must be a string.' => 'La descripción debe ser un texto válido.',

                                default => $error,
                            };
                        })
                        ->implode("\n");
                @endphp

                showAppNotification(
                    'warning',
                    'Revisa los datos del rol',
                    @json($mensajes)
                );
            @endif

            // Mostrar un error general si el rol no pudo ser actualizado
            @if (session('error'))

                showAppNotification(
                    'error',
                    'No se pudo actualizar el rol',
                    @json(session('error'))
                );
            @endif

            // Mostrar confirmación cuando el rol fue actualizado correctamente
            @if (session('success'))

                showAppNotification(
                    'success',
                    'Rol actualizado correctamente',
                    @json(session('success'))
                );
            @endif
        });
    </script>
@endsection