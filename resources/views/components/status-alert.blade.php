@php
    $type = null;
    $message = null;

    foreach (['success', 'error', 'warning', 'info'] as $alertType) {
        if (session()->has($alertType)) {
            $type = $alertType;
            $message = session($alertType);
            break;
        }
    }

    $config = [
        'success' => [
            'title' => 'Operación exitosa',
            'border' => 'border-emerald-200 dark:border-emerald-500/20',
            'shadow' => 'shadow-emerald-500/10',
            'icon' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
            'progressBackground' => 'bg-emerald-100 dark:bg-emerald-950/50',
            'progress' => 'bg-emerald-500',
        ],

        'error' => [
            'title' => 'No se pudo completar',
            'border' => 'border-red-200 dark:border-red-500/20',
            'shadow' => 'shadow-red-500/10',
            'icon' => 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
            'progressBackground' => 'bg-red-100 dark:bg-red-950/50',
            'progress' => 'bg-red-500',
        ],

        'warning' => [
            'title' => 'Atención',
            'border' => 'border-amber-200 dark:border-amber-500/20',
            'shadow' => 'shadow-amber-500/10',
            'icon' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            'progressBackground' => 'bg-amber-100 dark:bg-amber-950/50',
            'progress' => 'bg-amber-500',
        ],

        'info' => [
            'title' => 'Información',
            'border' => 'border-blue-200 dark:border-blue-500/20',
            'shadow' => 'shadow-blue-500/10',
            'icon' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
            'progressBackground' => 'bg-blue-100 dark:bg-blue-950/50',
            'progress' => 'bg-blue-500',
        ],
    ];

    $alert = $type ? $config[$type] : null;
@endphp

@if ($type && $message)
    <div
        x-data="{
            visible: false,
            timeout: null,

            init() {
                this.$nextTick(() => {
                    this.visible = true;
                });

                this.timeout = setTimeout(() => {
                    this.close();
                }, 5000);
            },

            close() {
                if (this.timeout) {
                    clearTimeout(this.timeout);
                    this.timeout = null;
                }

                this.visible = false;
            }
        }"
        x-init="init()"
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-3"
        x-cloak
        role="alert"
        aria-live="assertive"
        class="fixed right-4 top-20 z-[100] w-[calc(100%-2rem)] max-w-md"
    >
        <div
            class="relative overflow-hidden rounded-2xl border bg-white shadow-2xl dark:bg-sidan-900
                   {{ $alert['border'] }}
                   {{ $alert['shadow'] }}"
        >
            <div class="flex items-start gap-3 p-4 pr-12">

                {{-- Icono --}}
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                           {{ $alert['icon'] }}"
                >
                    {{-- Success --}}
                    @if ($type === 'success')
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>

                    {{-- Error --}}
                    @elseif ($type === 'error')
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="12" cy="12" r="9" />

                            <path
                                stroke-linecap="round"
                                d="M9 9l6 6M15 9l-6 6"
                            />
                        </svg>

                    {{-- Warning --}}
                    @elseif ($type === 'warning')
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15.28A2 2 0 0 0 3.18 22h17.64a2 2 0 0 0 1.71-2.86L13.71 3.86a2 2 0 0 0-3.42 0Z"
                            />
                        </svg>

                    {{-- Info --}}
                    @else
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <circle cx="12" cy="12" r="9" />

                            <path
                                stroke-linecap="round"
                                d="M12 11v5m0-8h.01"
                            />
                        </svg>
                    @endif
                </div>

                {{-- Texto --}}
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-black text-slate-900 dark:text-white">
                        {{ $alert['title'] }}
                    </p>

                    <p class="mt-1 text-sm leading-5 text-slate-600 dark:text-slate-400">
                        {{ $message }}
                    </p>
                </div>
            </div>

            {{-- Botón cerrar --}}
            <button
                type="button"
                @click="close()"
                class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center
                       rounded-lg text-slate-400 transition
                       hover:bg-slate-100 hover:text-slate-700
                       dark:hover:bg-white/10 dark:hover:text-white"
                aria-label="Cerrar alerta"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18 18 6M6 6l12 12"
                    />
                </svg>
            </button>

            {{-- Barra de progreso --}}
            <div class="h-1 w-full {{ $alert['progressBackground'] }}">
                <div
                    class="h-full w-full origin-left {{ $alert['progress'] }}"
                    style="animation: sidan-alert-progress 5s linear forwards;"
                ></div>
            </div>
        </div>
    </div>

    @once
        <style>
            @keyframes sidan-alert-progress {
                from {
                    transform: scaleX(1);
                }

                to {
                    transform: scaleX(0);
                }
            }
        </style>
    @endonce
@endif