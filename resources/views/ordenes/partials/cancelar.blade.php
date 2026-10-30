@if (app(\App\Services\GestionOrdenService::class)->puedeCancelar($orden))
    @php($idModalCancelar = 'cancelar-orden-' . $orden->id_orden)

    <button type="button"
        data-sidan-cancel-open="{{ $idModalCancelar }}"
        aria-haspopup="dialog"
        aria-controls="{{ $idModalCancelar }}"
        class="rounded-xl border border-red-200 px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10">
        Cancelar orden
    </button>

    {{-- El script traslada este overlay directamente al body para evitar los contenedores de la vista. --}}
    <div id="{{ $idModalCancelar }}"
        data-sidan-cancel-modal
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $idModalCancelar }}-titulo"
        aria-describedby="{{ $idModalCancelar }}-descripcion"
        aria-hidden="true"
        tabindex="-1"
        class="fixed inset-0 z-[99999] hidden items-center justify-center overflow-y-auto bg-slate-950/60 px-4 py-6">
        <div class="my-auto w-full max-w-md shrink-0 overflow-hidden rounded-2xl border border-slate-200 bg-white text-slate-900 shadow-2xl dark:border-white/10 dark:bg-slate-900 dark:text-white"
            data-sidan-cancel-panel>
            <div class="border-b border-slate-200 p-5 dark:border-white/10">
                <h3 id="{{ $idModalCancelar }}-titulo" class="text-lg font-black">
                    ¿Cancelar la orden #{{ $orden->id_orden }}?
                </h3>
                <p id="{{ $idModalCancelar }}-descripcion" class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Se cancelará la solicitud y se liberará su reserva. La orden conservará su historial,
                    pero no generará un saldo por cobrar. No se puede cancelar si ya existe dinero recibido
                    o comprobantes enviados.
                </p>
            </div>

            <form method="POST" action="{{ route('sidan.ordenes.cancelar', $orden) }}" class="space-y-4 p-5">
                @csrf
                @method('DELETE')
                <label class="block text-sm">
                    <strong>Motivo (opcional)</strong>
                    <textarea name="motivo" maxlength="300" rows="2"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white p-3 text-slate-900 dark:border-white/20 dark:bg-slate-800 dark:text-white"
                        placeholder="No continuaré con esta compra"></textarea>
                </label>
                <div class="flex justify-end gap-3">
                    <button type="button" data-sidan-cancel-close
                        class="rounded-xl border border-slate-300 px-4 py-2 font-bold dark:border-white/20">
                        Volver
                    </button>
                    <button type="submit"
                        class="rounded-xl bg-red-600 px-4 py-2 font-bold text-white hover:bg-red-700">
                        Sí, cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@once
    @push('scripts')
        <script>
            (() => {
                // El parcial puede repetirse en "Mis órdenes"; el controlador JS se instala una sola vez.
                document.querySelectorAll('[data-sidan-cancel-modal]').forEach((modal) => {
                    // Evita que un contenedor con transform, overflow o sticky altere el centrado.
                    document.body.appendChild(modal);
                });

                let modalActivo = null;
                let botonOrigen = null;
                let overflowAnterior = '';

                function cerrar() {
                    if (!modalActivo) return;
                    const modal = modalActivo;
                    modalActivo = null;
                    modal.classList.remove('flex');
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = overflowAnterior;
                    botonOrigen?.focus();
                    botonOrigen = null;
                }

                function abrir(id, boton) {
                    const modal = document.getElementById(id);
                    if (!modal || !modal.matches('[data-sidan-cancel-modal]')) return;
                    if (modalActivo) cerrar();

                    modalActivo = modal;
                    botonOrigen = boton;
                    overflowAnterior = document.body.style.overflow;
                    document.body.style.overflow = 'hidden';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.removeAttribute('aria-hidden');
                    // Reestablecemos el foco dentro del diálogo, sin depender de <dialog>.showModal().
                    requestAnimationFrame(() => (modal.querySelector('textarea') || modal).focus());
                }

                document.addEventListener('click', (event) => {
                    if (!(event.target instanceof Element)) return;
                    const boton = event.target.closest('[data-sidan-cancel-open]');
                    if (boton) {
                        abrir(boton.dataset.sidanCancelOpen, boton);
                        return;
                    }
                    if (!modalActivo) return;
                    if (event.target.closest('[data-sidan-cancel-close]') || event.target === modalActivo) {
                        cerrar();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (!modalActivo) return;
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        cerrar();
                        return;
                    }
                    if (event.key !== 'Tab') return;

                    const elementos = [...modalActivo.querySelectorAll(
                        'button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])'
                    )].filter((el) => el.getClientRects().length);
                    if (!elementos.length) {
                        event.preventDefault();
                        modalActivo.focus();
                        return;
                    }
                    const primero = elementos[0];
                    const ultimo = elementos[elementos.length - 1];
                    if (event.shiftKey && document.activeElement === primero) {
                        event.preventDefault();
                        ultimo.focus();
                    } else if (!event.shiftKey && document.activeElement === ultimo) {
                        event.preventDefault();
                        primero.focus();
                    }
                });
            })();
        </script>
    @endpush
@endonce
