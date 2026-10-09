@once
    @php
        $mensajeToast = session('success') ?? session('error') ?? session('warning') ?? session('info');
        $tipoToast = session('success')
            ? 'success'
            : (session('error')
                ? 'error'
                : (session('warning') ? 'warning' : 'info'));

        if (!$mensajeToast && $errors->any()) {
            $mensajeToast = 'Revisa los campos marcados e intenta nuevamente.';
            $tipoToast = 'error';
        }

        $titulosToast = [
            'success' => 'Listo',
            'error' => 'Ocurrió un problema',
            'warning' => 'Atención',
            'info' => 'Información',
        ];
    @endphp

    <div
        id="sidanToastRoot"
        class="pointer-events-none fixed right-4 top-4 flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3 sm:right-6 sm:top-6"
        style="z-index: 2147483000;"
        aria-live="polite"
        aria-atomic="false">
        @if ($mensajeToast)
            <div
                class="sidan-toast pointer-events-auto translate-y-0 opacity-100 transition-all duration-300"
                data-toast-type="{{ $tipoToast }}"
                role="status">
                <div class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900 shadow-2xl shadow-black/30">
                    <div class="flex items-start gap-3 p-4">
                        <div
                            data-toast-icon
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl">
                        </div>

                        <div class="min-w-0 flex-1">
                            <p data-toast-title class="text-sm font-semibold text-white">
                                {{ $titulosToast[$tipoToast] ?? $titulosToast['info'] }}
                            </p>
                            <p data-toast-message class="mt-1 break-words text-sm leading-5 text-gray-300">
                                {{ $mensajeToast }}
                            </p>
                        </div>

                        <button
                            type="button"
                            data-toast-close
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-white/10 hover:text-white"
                            aria-label="Cerrar notificación">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="h-1 bg-white/5">
                        <div data-toast-progress class="h-full w-full"></div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        (() => {
            const root = document.getElementById('sidanToastRoot');
            if (!root || window.SIDANToast) {
                return;
            }

            const estilos = {
                success: {
                    icon: 'bg-emerald-500/15 text-emerald-400',
                    progress: 'bg-emerald-400',
                    title: 'Listo',
                    svg: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7" /></svg>',
                },
                error: {
                    icon: 'bg-red-500/15 text-red-400',
                    progress: 'bg-red-400',
                    title: 'Ocurrió un problema',
                    svg: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>',
                },
                warning: {
                    icon: 'bg-amber-500/15 text-amber-400',
                    progress: 'bg-amber-400',
                    title: 'Atención',
                    svg: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86A2 2 0 0020.66 16L13.73 4a2 2 0 00-3.46 0L3.34 16A2 2 0 005.07 19z" /></svg>',
                },
                info: {
                    icon: 'bg-blue-500/15 text-blue-400',
                    progress: 'bg-blue-400',
                    title: 'Información',
                    svg: '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 3a9 9 0 110 18 9 9 0 010-18z" /></svg>',
                },
            };

            const normalizarTipo = tipo => estilos[tipo] ? tipo : 'info';

            const configurarToast = (toast, opciones = {}) => {
                const tipo = normalizarTipo(opciones.type || toast.dataset.toastType || 'info');
                const estilo = estilos[tipo];
                const icono = toast.querySelector('[data-toast-icon]');
                const titulo = toast.querySelector('[data-toast-title]');
                const mensaje = toast.querySelector('[data-toast-message]');
                const progreso = toast.querySelector('[data-toast-progress]');
                const cerrar = toast.querySelector('[data-toast-close]');
                const duracion = Number(opciones.duration) > 0 ? Number(opciones.duration) : 4500;
                let timeout = null;
                let cerrado = false;

                toast.dataset.toastType = tipo;

                if (icono) {
                    icono.className = `flex h-9 w-9 shrink-0 items-center justify-center rounded-xl ${estilo.icon}`;
                    icono.innerHTML = estilo.svg;
                }

                if (titulo) {
                    titulo.textContent = opciones.title || titulo.textContent?.trim() || estilo.title;
                }

                if (mensaje && opciones.message !== undefined) {
                    mensaje.textContent = String(opciones.message);
                }

                if (progreso) {
                    progreso.className = `h-full w-full ${estilo.progress}`;
                    progreso.style.transition = `width ${duracion}ms linear`;
                    progreso.style.width = '100%';
                }

                const ocultar = () => {
                    if (cerrado) {
                        return;
                    }

                    cerrado = true;
                    toast.classList.add('-translate-y-3', 'opacity-0');

                    window.setTimeout(() => {
                        toast.remove();
                    }, 300);
                };

                cerrar?.addEventListener('click', () => {
                    if (timeout) {
                        window.clearTimeout(timeout);
                    }
                    ocultar();
                });

                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        if (progreso) {
                            progreso.style.width = '0%';
                        }
                    });
                });

                timeout = window.setTimeout(ocultar, duracion);
            };

            const crearToast = opciones => {
                const toast = document.createElement('div');
                toast.className = 'sidan-toast pointer-events-auto translate-y-0 opacity-100 transition-all duration-300';
                toast.setAttribute('role', 'status');
                toast.innerHTML = `
                    <div class="overflow-hidden rounded-2xl border border-white/10 bg-gray-900 shadow-2xl shadow-black/30">
                        <div class="flex items-start gap-3 p-4">
                            <div data-toast-icon class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"></div>
                            <div class="min-w-0 flex-1">
                                <p data-toast-title class="text-sm font-semibold text-white"></p>
                                <p data-toast-message class="mt-1 break-words text-sm leading-5 text-gray-300"></p>
                            </div>
                            <button type="button" data-toast-close class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-white/10 hover:text-white" aria-label="Cerrar notificación">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="h-1 bg-white/5">
                            <div data-toast-progress class="h-full w-full"></div>
                        </div>
                    </div>
                `;

                root.appendChild(toast);
                configurarToast(toast, opciones);
                return toast;
            };

            window.SIDANToast = {
                show(message, type = 'info', title = null, duration = 4500) {
                    if (!message) {
                        return null;
                    }

                    return crearToast({
                        message,
                        type,
                        title,
                        duration,
                    });
                },
                success(message, title = null) {
                    return this.show(message, 'success', title);
                },
                error(message, title = null) {
                    return this.show(message, 'error', title);
                },
                warning(message, title = null) {
                    return this.show(message, 'warning', title);
                },
                info(message, title = null) {
                    return this.show(message, 'info', title);
                },
            };

            root.querySelectorAll('.sidan-toast').forEach(toast => {
                configurarToast(toast);
            });

            window.addEventListener('sidan:toast', event => {
                const detalle = event.detail || {};
                const mensaje = detalle.message || detalle.mensaje;

                if (!mensaje) {
                    return;
                }

                window.SIDANToast.show(
                    mensaje,
                    detalle.type || detalle.tipo || 'info',
                    detalle.title || detalle.titulo || null,
                    detalle.duration || detalle.duracion || 4500
                );
            });
        })();
    </script>
@endonce
