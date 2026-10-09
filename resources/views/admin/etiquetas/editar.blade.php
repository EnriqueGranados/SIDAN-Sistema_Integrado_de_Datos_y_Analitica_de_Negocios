@extends('layouts.navbars')

@section('title', 'Editar etiqueta')

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

                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                            Editar etiqueta
                        </h1>

                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                            Modifica el nombre o la disponibilidad de esta etiqueta.
                            Los cambios se reflejarán en las actividades donde se utilice.
                        </p>
                    </div>

                    @if ($etiqueta->activo)
                        <span
                            class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Activa
                        </span>
                    @else
                        <span
                            class="inline-flex w-fit shrink-0 items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 dark:border-white/5 dark:bg-slate-800 dark:text-slate-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                            Inactiva
                        </span>
                    @endif
                </div>
            </div>

            <div
                class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/20 dark:bg-emerald-500/5">
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                            Recuerda para qué sirve una etiqueta
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">
                            Una etiqueta describe una característica adicional de una actividad,
                            como su modalidad, temática, público o nivel. Una actividad puede
                            utilizar varias etiquetas al mismo tiempo.
                        </p>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            No debe confundirse con la categoría, que representa la clasificación
                            principal de la actividad.
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Uso actual
                        </p>

                        <p class="mt-1 text-base font-semibold text-slate-900 dark:text-white">
                            {{ $etiqueta->nombre }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-white/[0.03]">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2a4 4 0 014-4h6m0 0-3-3m3 3-3 3M5 7h5a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500">
                                Actividades asociadas
                            </p>

                            <p class="text-lg font-bold text-slate-900 dark:text-white">
                                {{ $etiqueta->actividades_count }}
                            </p>
                        </div>
                    </div>
                </div>

                @if ($etiqueta->actividades_count > 0)
                    <div class="mt-4 border-t border-slate-200 pt-4 dark:border-white/5">
                        <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                            Esta etiqueta está siendo utilizada por
                            <strong class="font-semibold text-slate-800 dark:text-slate-300">
                                {{ $etiqueta->actividades_count }}
                                {{ $etiqueta->actividades_count === 1 ? 'actividad' : 'actividades' }}.
                            </strong>
                            Puedes modificarla o desactivarla, pero no eliminarla mientras mantenga estas asociaciones.
                        </p>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.etiquetas.update', $etiqueta) }}">
                @csrf
                @method('PUT')

                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                    <div class="border-b border-slate-200 px-6 py-5 dark:border-white/5">
                        <h2 class="text-base font-semibold text-slate-900 dark:text-white">
                            Información de la etiqueta
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Mantén un nombre corto y específico que describa claramente una característica.
                        </p>
                    </div>

                    <div class="space-y-6 p-6">
                        <div>
                            <label for="nombre" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Nombre de la etiqueta
                                <span class="text-red-600 dark:text-red-400">*</span>
                            </label>

                            <input id="nombre" name="nombre" type="text" maxlength="120" required autofocus
                                value="{{ old('nombre', $etiqueta->nombre) }}" placeholder="Ej. Principiantes"
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
                                Si cambias el nombre, el cambio también se verá reflejado cuando
                                esta etiqueta aparezca asociada a una actividad.
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/5 dark:bg-white/[0.02]">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="hidden" name="activo" value="0">

                                <input id="activo" name="activo" type="checkbox" value="1"
                                    @checked(old('activo', $etiqueta->activo ? '1' : '0') == '1')
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 bg-white text-emerald-500 focus:ring-emerald-500 focus:ring-offset-white dark:border-slate-600 dark:bg-slate-900 dark:focus:ring-offset-[#0f172a]">

                                <span>
                                    <span class="block text-sm font-medium text-slate-900 dark:text-white">
                                        Disponible para utilizar
                                    </span>

                                    <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                                        Si está activa podrá seleccionarse al configurar actividades.
                                        Si la desactivas, conservará sus datos y asociaciones existentes,
                                        pero dejará de estar disponible para nuevas asignaciones.
                                    </span>
                                </span>
                            </label>
                        </div>

                        @if ($etiqueta->actividades_count > 0)
                            <div
                                class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/20 dark:bg-amber-500/10">
                                <div class="flex items-start gap-3">
                                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01M10.29 3.86 1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    </svg>

                                    <div>
                                        <p class="text-sm font-semibold text-amber-700 dark:text-amber-300">
                                            Esta etiqueta está en uso
                                        </p>

                                        <p class="mt-1 text-sm leading-6 text-amber-700/80 dark:text-amber-200/70">
                                            Antes de cambiar su nombre, considera que el nuevo nombre
                                            se utilizará para todas las actividades que actualmente
                                            tienen asociada esta etiqueta.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div
                        class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50/70 px-6 py-4 sm:flex-row sm:justify-end dark:border-white/5 dark:bg-white/[0.02]">
                        <a href="{{ route('admin.etiquetas.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </form>

            @if ($etiqueta->actividades_count === 0)
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-6 dark:border-red-500/20 dark:bg-red-500/5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-red-700 dark:text-red-300">
                                Eliminar etiqueta
                            </h2>

                            <p class="mt-1 max-w-xl text-sm leading-6 text-slate-600 dark:text-slate-400">
                                Esta etiqueta no está asociada a ninguna actividad y puede eliminarse.
                                La eliminación es permanente y no se puede deshacer.
                            </p>
                        </div>

                        <form id="formEliminarEtiqueta" method="POST"
                            action="{{ route('admin.etiquetas.destroy', $etiqueta) }}"
                            data-nombre="{{ $etiqueta->nombre }}">
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-100 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-200 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                </svg>
                                Eliminar etiqueta
                            </button>
                        </form>
                    </div>
                </div>
            @endif
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

        function showDeleteConfirm(etiquetaName) {
            if (document.getElementById('etiqueta-confirm-overlay')) {
                return Promise.resolve(false);
            }

            return new Promise(resolve => {
                const overlay = document.createElement('div');
                overlay.id = 'etiqueta-confirm-overlay';
                overlay.className =
                    'fixed inset-0 z-[200] flex items-center justify-center bg-slate-950/60 p-4 opacity-0 backdrop-blur-sm transition-opacity duration-200';

                const modal = document.createElement('div');
                modal.setAttribute('role', 'dialog');
                modal.setAttribute('aria-modal', 'true');
                modal.setAttribute('tabindex', '-1');
                modal.className =
                    'w-full max-w-md translate-y-4 scale-95 rounded-2xl border border-slate-200 bg-white p-6 opacity-0 shadow-2xl outline-none transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]';

                modal.innerHTML = `
            <div class="flex items-start gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">
                        Eliminar etiqueta
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        ¿Deseas eliminar
                        <span class="etiqueta-name font-semibold text-slate-900 dark:text-white"></span>?
                        Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                    class="cancel-delete inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                    Cancelar
                </button>

                <button type="button"
                    class="confirm-delete inline-flex h-10 items-center justify-center rounded-xl bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-500">
                    Eliminar
                </button>
            </div>
        `;

                modal.querySelector('.etiqueta-name').textContent = etiquetaName || 'esta etiqueta';
                overlay.appendChild(modal);
                document.body.appendChild(overlay);

                const cancelButton = modal.querySelector('.cancel-delete');
                const confirmButton = modal.querySelector('.confirm-delete');
                let resolved = false;

                const close = result => {
                    if (resolved) return;
                    resolved = true;

                    cancelButton.disabled = true;
                    confirmButton.disabled = true;
                    document.removeEventListener('keydown', handleKeydown, true);

                    overlay.classList.remove('opacity-100');
                    modal.classList.remove('translate-y-0', 'scale-100', 'opacity-100');
                    modal.classList.add('translate-y-4', 'scale-95', 'opacity-0');

                    setTimeout(() => {
                        overlay.remove();
                        resolve(result);
                    }, 200);
                };

                const handleKeydown = event => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        event.stopPropagation();
                        if (event.repeat || resolved) return;
                        close(true);
                    }

                    if (event.key === 'Escape') {
                        event.preventDefault();
                        event.stopPropagation();
                        if (resolved) return;
                        close(false);
                    }
                };

                cancelButton.addEventListener('click', () => close(false));
                confirmButton.addEventListener('click', () => close(true));

                overlay.addEventListener('click', event => {
                    if (event.target === overlay) close(false);
                });

                document.addEventListener('keydown', handleKeydown, true);

                requestAnimationFrame(() => {
                    overlay.classList.add('opacity-100');
                    modal.classList.remove('translate-y-4', 'scale-95', 'opacity-0');
                    modal.classList.add('translate-y-0', 'scale-100', 'opacity-100');
                    modal.focus();
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
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
                    'No se pudo actualizar la etiqueta',
                    @json(session('error'))
                );
            @endif

            @if (session('success'))
                showAppNotification(
                    'success',
                    'Etiqueta actualizada correctamente',
                    @json(session('success'))
                );
            @endif

            const deleteForm = document.getElementById('formEliminarEtiqueta');

            if (deleteForm) {
                deleteForm.addEventListener('submit', async function(event) {
                    event.preventDefault();

                    const confirmed = await showDeleteConfirm(
                        deleteForm.dataset.nombre
                    );

                    if (!confirmed) return;

                    deleteForm.submit();
                });
            }
        });
    </script>
@endsection
