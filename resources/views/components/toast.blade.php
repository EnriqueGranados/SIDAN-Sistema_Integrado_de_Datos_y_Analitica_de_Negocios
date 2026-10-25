@once
    @php
        // Un solo mensaje de sesión por navegación: el resto se muestra junto al formulario.
        $tipoToast = null;
        $mensajeToast = null;
        foreach (['success', 'error', 'warning', 'info'] as $tipo) {
            if (session()->has($tipo) && filled(session($tipo))) {
                $tipoToast = $tipo;
                $mensajeToast = (string) session($tipo);
                break;
            }
        }
        if (!$mensajeToast && $errors->any()) {
            $tipoToast = 'error';
            $mensajeToast = $errors->first() ?: 'Revisá la información ingresada.';
        }
    @endphp

    {{-- Notificación global única para vistas administrativas y públicas.
         La barra administrativa termina a ~80px desde el borde superior. --}}
    <div id="sidanToastRoot"
        class="pointer-events-none fixed right-4 top-24 w-[calc(100%-2rem)] max-w-md sm:right-6"
        style="z-index: 2147483000;"
        data-initial-type="{{ $tipoToast }}"
        data-initial-message="{{ $mensajeToast }}"
        aria-live="polite" aria-atomic="true"></div>

    <script>
        (() => {
            const root = document.getElementById('sidanToastRoot');
            if (!root || window.SIDANToast) return;

            const estilos = {
                success: {
                    titulo: 'Listo',
                    borde: 'border-sidan-200 dark:border-sidan-500/30',
                    icono: 'bg-sidan-50 text-sidan-600 dark:bg-sidan-500/15 dark:text-sidan-400',
                    barra: 'bg-sidan-500',
                    svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />'
                },
                error: {
                    titulo: 'Ocurrió un problema',
                    borde: 'border-red-200 dark:border-red-500/30',
                    icono: 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400',
                    barra: 'bg-red-500',
                    svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />'
                },
                warning: {
                    titulo: 'Atención',
                    borde: 'border-amber-200 dark:border-amber-500/30',
                    icono: 'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400',
                    barra: 'bg-amber-500',
                    svg: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9l-8 14a2 2 0 001.7 3h16a2 2 0 001.7-3l-8-14a2 2 0 00-3.4 0z" />'
                },
                info: {
                    titulo: 'Información',
                    borde: 'border-blue-200 dark:border-blue-500/30',
                    icono: 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
                    barra: 'bg-blue-500',
                    svg: '<circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 11v6m0-10h.01" />'
                }
            };

            let actual = null;
            let temporizador = null;
            let ultimaClave = '';
            let ultimoMomento = 0;
            const normalizar = tipo => estilos[tipo] ? tipo : 'info';

            const cerrar = () => {
                if (!actual) return;
                window.clearTimeout(temporizador);
                const elemento = actual;
                actual = null;
                elemento.classList.add('opacity-0', '-translate-y-3');
                window.setTimeout(() => elemento.remove(), 250);
            };

            const mostrar = (mensaje, tipo = 'info', titulo = null, duracion = 5000) => {
                if (mensaje === null || mensaje === undefined || String(mensaje).trim() === '') return null;
                tipo = normalizar(String(tipo));
                mensaje = String(mensaje).trim();
                const clave = `${tipo}:${mensaje.replace(/\s+/g, ' ').toLocaleLowerCase()}`;
                const ahora = Date.now();

                // Si el mensaje es el mismo que el de la sesión o la vista, no duplicarlo.
                if (clave === ultimaClave && (actual || ahora - ultimoMomento < 1200)) return actual;
                ultimaClave = clave;
                ultimoMomento = ahora;
                cerrar();

                const estilo = estilos[tipo];
                const tiempo = Number.isFinite(Number(duracion)) && Number(duracion) > 0
                    ? Math.max(1500, Number(duracion)) : 5000;

                const alerta = document.createElement('div');
                alerta.className = 'sidan-toast pointer-events-auto translate-y-0 opacity-100 transition-all duration-250 ease-out';
                alerta.setAttribute('role', tipo === 'error' ? 'alert' : 'status');
                alerta.dataset.toastType = tipo;
                alerta.innerHTML = `
                    <div class="overflow-hidden rounded-2xl border ${estilo.borde} bg-white shadow-2xl shadow-slate-900/10 dark:bg-[#0f172a] dark:shadow-black/40">
                        <div class="flex items-start gap-3 p-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${estilo.icono}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">${estilo.svg}</svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p data-titulo class="text-sm font-bold text-sidan-900 dark:text-white"></p>
                                <p data-mensaje class="mt-1 whitespace-pre-line break-words text-sm leading-5 text-slate-600 dark:text-slate-300"></p>
                            </div>
                            <button type="button" data-cerrar class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-sidan-500 dark:hover:bg-white/10 dark:hover:text-white" aria-label="Cerrar notificación">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <div class="h-1 bg-slate-100 dark:bg-white/10"><div data-progreso class="h-full w-full ${estilo.barra}"></div></div>
                    </div>`;
                alerta.querySelector('[data-titulo]').textContent = titulo || estilo.titulo;
                alerta.querySelector('[data-mensaje]').textContent = mensaje;
                alerta.querySelector('[data-cerrar]').addEventListener('click', cerrar);
                root.replaceChildren(alerta);
                actual = alerta;
                const progreso = alerta.querySelector('[data-progreso]');
                progreso.style.transformOrigin = 'left';
                progreso.style.transition = `transform ${tiempo}ms linear`;
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    if (actual === alerta) progreso.style.transform = 'scaleX(0)';
                }));
                temporizador = window.setTimeout(cerrar, tiempo);
                return alerta;
            };

            window.SIDANToast = {
                show: mostrar,
                success: (mensaje, titulo) => mostrar(mensaje, 'success', titulo),
                error: (mensaje, titulo) => mostrar(mensaje, 'error', titulo),
                warning: (mensaje, titulo) => mostrar(mensaje, 'warning', titulo),
                info: (mensaje, titulo) => mostrar(mensaje, 'info', titulo),
                close: cerrar
            };
            // Compatibilidad con páginas que envían este evento sin llamar SIDANToast.
            window.addEventListener('sidan:toast', event => {
                const detalle = event.detail || {};
                mostrar(detalle.message || detalle.mensaje, detalle.type || detalle.tipo || 'info',
                    detalle.title || detalle.titulo || null, detalle.duration || detalle.duracion || 5000);
            });

            if (root.dataset.initialMessage) {
                mostrar(root.dataset.initialMessage, root.dataset.initialType || 'info');
            }
        })();
    </script>
@endonce
