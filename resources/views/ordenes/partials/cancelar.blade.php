@if (app(\App\Services\GestionOrdenService::class)->puedeCancelar($orden))
    <form method="POST"
        action="{{ route('sidan.ordenes.cancelar', $orden) }}"
        onsubmit="return confirm('¿Cancelar y eliminar esta orden? Se liberarán sus reservas y descuentos.');">
        @csrf
        @method('DELETE')

        <button type="submit"
            class="rounded-xl border border-red-200 px-4 py-3 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10">
            Cancelar orden
        </button>
    </form>
@endif