@extends('layouts.navbars')

@section('title', 'Editar recurso')

@section('content')
    <div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
        <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('admin.recursos.index') }}"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a recursos
                </a>

                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Editar recurso</h1>

                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Actualiza la información de
                    <span class="font-medium text-slate-700 dark:text-slate-300">
                        {{ $recurso->nombre }}
                    </span>.
                </p>
            </div>

            <div
                class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a]">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">
                            Uso actual del recurso
                        </p>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Este recurso está asociado actualmente a
                            <span class="font-semibold text-slate-700 dark:text-slate-300">
                                {{ $recurso->espacios_count }}
                            </span>
                            {{ $recurso->espacios_count === 1 ? 'espacio' : 'espacios' }}.
                        </p>
                    </div>

                    @if ($recurso->espacios_count > 0)
                        <span
                            class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            En uso
                        </span>
                    @else
                        <span
                            class="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-600 dark:border-white/5 dark:bg-slate-800 dark:text-slate-400">
                            Sin espacios asociados
                        </span>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('admin.recursos.update', $recurso) }}">
                @csrf
                @method('PUT')

                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-white/5">
                        <h2 class="font-semibold text-slate-900 dark:text-white">
                            Información del recurso
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Modifica únicamente los datos que necesites actualizar.
                        </p>
                    </div>

                    <div class="space-y-6 p-5 sm:p-6">
                        <div>
                            <label for="nombre" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Nombre del recurso
                                <span class="text-red-600 dark:text-red-400">*</span>
                            </label>

                            <input id="nombre" name="nombre" type="text" maxlength="120" required autofocus
                                value="{{ old('nombre', $recurso->nombre) }}" placeholder="Ej. Proyector multimedia"
                                class="w-full rounded-xl border bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-white/10
                                {{ $errors->has('nombre')
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                    : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-white/10' }}">

                            @error('nombre')
                                <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="descripcion"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Descripción
                            </label>

                            <textarea id="descripcion" name="descripcion" rows="3" maxlength="1000"
                                placeholder="Describe brevemente el recurso si es necesario..."
                                class="w-full resize-y rounded-xl border bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-white/10
                                {{ $errors->has('descripcion')
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                    : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-white/10' }}">{{ old('descripcion', $recurso->descripcion) }}</textarea>

                            @error('descripcion')
                                <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label for="categoria"
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Categoría
                                </label>

                                <div class="relative">
                                    <input id="categoria" name="categoria" type="text" maxlength="80"
                                        value="{{ old('categoria', $recurso->categoria) }}" placeholder="Ej. Audiovisual"
                                        autocomplete="off"
                                        class="w-full rounded-xl border bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-white/10
                                        {{ $errors->has('categoria')
                                            ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                            : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-white/10' }}">

                                    <div id="sugerenciasCategoria"
                                        class="absolute z-20 mt-2 hidden max-h-52 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">
                                    </div>
                                </div>

                                <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                    Agrupa recursos similares. Por ejemplo: Audiovisual, Audio,
                                    Mobiliario, Tecnología o Conectividad. Empieza a escribir para ver opciones.
                                </p>

                                @error('categoria')
                                    <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="unidad_medida"
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                    Forma de medir la cantidad
                                    <span class="text-red-600 dark:text-red-400">*</span>
                                </label>

                                <select id="unidad_medida" name="unidad_medida" required
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:bg-white/10 dark:[color-scheme:dark]">
                                    <option value="unidad" @selected(old('unidad_medida', $recurso->unidad_medida) === 'unidad')>
                                        Unidades
                                    </option>

                                    <option value="par" @selected(old('unidad_medida', $recurso->unidad_medida) === 'par')>
                                        Pares
                                    </option>

                                    <option value="juego" @selected(old('unidad_medida', $recurso->unidad_medida) === 'juego')>
                                        Juegos / conjuntos
                                    </option>

                                    <option value="metro" @selected(old('unidad_medida', $recurso->unidad_medida) === 'metro')>
                                        Metros
                                    </option>
                                </select>

                                <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                    Para proyectores, micrófonos, sillas, mesas y similares utiliza “Unidades”.
                                </p>

                                @error('unidad_medida')
                                    <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="border-t border-slate-200 pt-6 dark:border-white/5">
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                ¿Este recurso puede trasladarse entre diferentes espacios?
                            </p>

                            <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                Un recurso móvil puede utilizarse en distintos lugares. Si normalmente
                                forma parte de un espacio determinado, déjalo desmarcado.
                            </p>

                            <label
                                class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-white/20">
                                <input type="checkbox" name="es_movil" value="1" @checked(old('es_movil', $recurso->es_movil ? '1' : '0') == '1')
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 bg-white text-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900">

                                <div>
                                    <span class="block text-sm font-medium text-slate-900 dark:text-white">
                                        Sí, es un recurso móvil
                                    </span>

                                    <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                                        Puede trasladarse y utilizarse en diferentes espacios o actividades.
                                    </span>
                                </div>
                            </label>
                        </div>

                        <div class="border-t border-slate-200 pt-6 dark:border-white/5">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" name="activo" value="1" @checked(old('activo', $recurso->activo ? '1' : '0') == '1')
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 bg-white text-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900">

                                <div>
                                    <span class="block text-sm font-medium text-slate-900 dark:text-white">
                                        Recurso activo
                                    </span>

                                    <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                                        Los recursos activos estarán disponibles para nuevas configuraciones.
                                        Desactivarlo no elimina las asociaciones existentes.
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-white/5 dark:bg-white/[0.02]">
                        <a href="{{ route('admin.recursos.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Guardar cambios
                        </button>
                    </div>
                </div>
            </form>

            <div
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5 sm:p-6 dark:border-red-500/20 dark:bg-red-500/5">
                <h2 class="font-semibold text-red-700 dark:text-red-300">
                    Eliminar recurso
                </h2>

                @if ($recurso->espacios_count > 0)
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        Este recurso no puede eliminarse porque está asociado a
                        {{ $recurso->espacios_count }}
                        {{ $recurso->espacios_count === 1 ? 'espacio' : 'espacios' }}.
                        Si ya no debe utilizarse en nuevas configuraciones, puedes desactivarlo.
                    </p>

                    <button type="button" disabled
                        class="mt-4 cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400 dark:border-white/5 dark:bg-white/[0.02] dark:text-slate-600">
                        Eliminar recurso
                    </button>
                @else
                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        Puedes eliminar este recurso porque todavía no está asociado a ningún espacio.
                        Esta acción no se puede deshacer.
                    </p>

                    <form id="formEliminarRecurso" method="POST"
                        action="{{ route('admin.recursos.destroy', $recurso) }}" class="mt-4"
                        data-nombre="{{ $recurso->nombre }}">
                        @csrf
                        @method('DELETE')

                        <button type="submit"
                            class="rounded-xl border border-red-200 bg-red-100 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-200 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-500/20">
                            Eliminar recurso
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div id="datosCategorias" data-categorias="{{ $categorias->values()->toJson() }}" class="hidden">
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

                setTimeout(() => {
                    notification.remove();
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

        function showDeleteConfirm(resourceName) {
            if (document.getElementById('resource-confirm-overlay')) {
                return Promise.resolve(false);
            }

            return new Promise(resolve => {
                const overlay = document.createElement('div');

                overlay.id = 'resource-confirm-overlay';
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
                        Eliminar recurso
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                        ¿Deseas eliminar
                        <span class="resource-name font-semibold text-slate-900 dark:text-white"></span>?
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

                modal.querySelector('.resource-name').textContent = resourceName || 'este recurso';

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
                    if (event.target === overlay) {
                        close(false);
                    }
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
                    'Revisa los datos del recurso',
                    @json(collect($errors->all())->implode("\n"))
                );
            @endif

            @if (session('error'))
                showAppNotification(
                    'error',
                    'No se pudo actualizar el recurso',
                    @json(session('error'))
                );
            @endif

            @if (session('success'))
                showAppNotification(
                    'success',
                    'Recurso actualizado correctamente',
                    @json(session('success'))
                );
            @endif

            const categoria = document.getElementById('categoria');
            const sugerenciasCategoria = document.getElementById('sugerenciasCategoria');
            const datosCategorias = document.getElementById('datosCategorias');

            const categoriasDisponibles = JSON.parse(
                datosCategorias.dataset.categorias
            );

            function normalizarTexto(texto) {
                return texto
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase();
            }

            function ocultarSugerencias() {
                sugerenciasCategoria.innerHTML = '';
                sugerenciasCategoria.classList.add('hidden');
            }

            function mostrarSugerencias() {
                const termino = categoria.value.trim();

                if (termino === '') {
                    ocultarSugerencias();
                    return;
                }

                const terminoNormalizado = normalizarTexto(termino);

                const coincidencias = categoriasDisponibles
                    .filter(function(item) {
                        return normalizarTexto(item).includes(terminoNormalizado);
                    })
                    .slice(0, 6);

                if (coincidencias.length === 0) {
                    ocultarSugerencias();
                    return;
                }

                sugerenciasCategoria.innerHTML = '';

                coincidencias.forEach(function(item) {
                    const boton = document.createElement('button');

                    boton.type = 'button';
                    boton.className =
                        'flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white';

                    const texto = document.createElement('span');
                    texto.textContent = item;

                    const indicador = document.createElement('span');
                    indicador.className = 'text-xs text-slate-400 dark:text-slate-500';
                    indicador.textContent = 'Usar';

                    boton.appendChild(texto);
                    boton.appendChild(indicador);

                    boton.addEventListener('click', function() {
                        categoria.value = item;
                        ocultarSugerencias();
                        categoria.focus();
                    });

                    sugerenciasCategoria.appendChild(boton);
                });

                sugerenciasCategoria.classList.remove('hidden');
            }

            categoria.addEventListener('input', mostrarSugerencias);

            categoria.addEventListener('focus', function() {
                if (categoria.value.trim() !== '') {
                    mostrarSugerencias();
                }
            });

            document.addEventListener('click', function(event) {
                if (
                    !categoria.contains(event.target) &&
                    !sugerenciasCategoria.contains(event.target)
                ) {
                    ocultarSugerencias();
                }
            });

            const deleteForm = document.getElementById('formEliminarRecurso');

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
