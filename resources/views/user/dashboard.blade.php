@extends('layouts.navbars')

@section('content')
    <h1 class="text-2xl font-bold mb-4">Mi Panel</h1>
    <div class=" p-6 rounded-lg shadow">
        <p>Bienvenido, {{ auth()->user()->nombres }}</p>
    </div>

    {{-- Mensaje de éxito --}}
    @if (session('success'))
        <div
            id="success-status-alert"
            role="alert"
            aria-live="assertive"
            class="fixed right-4 top-20 z-[100] w-[calc(100%-2rem)] max-w-md -translate-y-3 opacity-0 transition-all duration-300 ease-out">

            <div class="relative overflow-hidden rounded-2xl border border-emerald-200 bg-white shadow-2xl shadow-emerald-500/10 dark:border-emerald-500/20 dark:bg-sidan-900">

                <div class="flex items-start gap-3 p-4 pr-12">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-black text-slate-900 dark:text-white">
                            ¡Bienvenido a SIDAN!
                        </p>
                        <p class="mt-1 text-sm leading-5 text-slate-600 dark:text-slate-400">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onclick="closeSuccessAlert()"
                    class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white"
                    aria-label="Cerrar alerta">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="h-1 w-full bg-emerald-100 dark:bg-emerald-950/50">
                    <div id="success-alert-progress" class="h-full w-full origin-left bg-emerald-500"></div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const alert = document.getElementById('success-status-alert');
                const progress = document.getElementById('success-alert-progress');

                if (!alert) return;

                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        alert.classList.remove('opacity-0', '-translate-y-3');
                        alert.classList.add('opacity-100', 'translate-y-0');
                    });
                });

                if (progress) {
                    progress.style.transition = 'transform 5s linear';
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            progress.style.transform = 'scaleX(0)';
                        });
                    });
                }

                window.successAlertTimeout = setTimeout(() => {
                    closeSuccessAlert();
                }, 5000);
            });

            function closeSuccessAlert() {
                const alert = document.getElementById('success-status-alert');
                
                if (!alert) return;

                if (window.successAlertTimeout) {
                    clearTimeout(window.successAlertTimeout);
                    window.successAlertTimeout = null;
                }

                alert.classList.remove('opacity-100', 'translate-y-0');
                alert.classList.add('opacity-0', '-translate-y-3');

                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        </script>
    @endif
@endsection