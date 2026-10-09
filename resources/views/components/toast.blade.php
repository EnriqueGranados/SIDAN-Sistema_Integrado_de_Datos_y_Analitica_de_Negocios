@php
    $mensaje = session('success') ?? session('error') ?? session('warning') ?? session('info');

    $tipo = session('success')
        ? 'success'
        : (session('error')
            ? 'error'
            : (session('warning') ? 'warning' : 'info'));

    $estilos = [
        'success' => [
            'border' => 'border-emerald-500/30',
            'bg' => 'bg-gray-900',
            'iconBg' => 'bg-emerald-500/15',
            'icon' => 'text-emerald-400',
            'title' => 'Listo',
        ],
        'error' => [
            'border' => 'border-red-500/30',
            'bg' => 'bg-gray-900',
            'iconBg' => 'bg-red-500/15',
            'icon' => 'text-red-400',
            'title' => 'Ocurrió un problema',
        ],
        'warning' => [
            'border' => 'border-amber-500/30',
            'bg' => 'bg-gray-900',
            'iconBg' => 'bg-amber-500/15',
            'icon' => 'text-amber-400',
            'title' => 'Atención',
        ],
        'info' => [
            'border' => 'border-blue-500/30',
            'bg' => 'bg-gray-900',
            'iconBg' => 'bg-blue-500/15',
            'icon' => 'text-blue-400',
            'title' => 'Información',
        ],
    ];

    $estilo = $estilos[$tipo];
@endphp

@if ($mensaje)
    <div id="sidanToast" class="fixed right-4 top-4 z-[500] w-[calc(100%-2rem)] max-w-sm translate-y-0 opacity-100 transition-all duration-300 sm:right-6 sm:top-6">
        <div class="overflow-hidden rounded-2xl border {{ $estilo['border'] }} {{ $estilo['bg'] }} shadow-2xl shadow-black/30">
            <div class="flex items-start gap-3 p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $estilo['iconBg'] }} {{ $estilo['icon'] }}">
                    @if ($tipo === 'success')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 13l4 4L19 7" />
                        </svg>
                    @elseif ($tipo === 'error')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @elseif ($tipo === 'warning')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86A2 2 0 0020.66 16L13.73 4a2 2 0 00-3.46 0L3.34 16A2 2 0 005.07 19z" />
                        </svg>
                    @else
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 3a9 9 0 110 18 9 9 0 010-18z" />
                        </svg>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-white">
                        {{ $estilo['title'] }}
                    </p>

                    <p class="mt-1 break-words text-sm leading-5 text-gray-400">
                        {{ $mensaje }}
                    </p>
                </div>

                <button type="button" id="cerrarSidanToast" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-gray-500 transition hover:bg-white/5 hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="h-1 bg-white/5">
                <div id="sidanToastProgress" class="h-full w-full bg-current {{ $estilo['icon'] }}"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toast = document.getElementById('sidanToast');
            const cerrar = document.getElementById('cerrarSidanToast');
            const progress = document.getElementById('sidanToastProgress');

            if (!toast) return;

            const duracion = 4500;
            let timeout;

            function ocultarToast() {
                toast.classList.add('-translate-y-3', 'opacity-0');

                setTimeout(() => {
                    toast.remove();
                }, 300);
            }

            progress.style.transition = `width ${duracion}ms linear`;

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    progress.style.width = '0%';
                });
            });

            timeout = setTimeout(ocultarToast, duracion);

            cerrar?.addEventListener('click', function () {
                clearTimeout(timeout);
                ocultarToast();
            });
        });
    </script>
@endif