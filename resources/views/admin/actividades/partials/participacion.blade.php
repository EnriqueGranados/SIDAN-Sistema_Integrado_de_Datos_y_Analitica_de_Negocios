@php
    $actividadParticipacion = $actividadParticipacion ?? null;
    $tipoGuardado = $actividadParticipacion?->tipo_participacion;

    if (!$tipoGuardado) {
        $tipoGuardado = $actividadParticipacion?->habilita_inscripcion
            ? 'registro_gratuito'
            : 'informativa';
    }

    $tipoParticipacionActual = old('tipo_participacion', $tipoGuardado ?: 'registro_gratuito');
    $requiereInscripcionActual = in_array(
        $tipoParticipacionActual,
        ['registro_gratuito', 'registro_pago'],
        true
    );
    $requiereCuentaActual = old(
        'requiere_cuenta',
        $actividadParticipacion?->requiere_cuenta ? '1' : '0'
    ) == '1';
    $permiteEsperaActual = old(
        'permite_lista_espera',
        $actividadParticipacion?->permite_lista_espera ? '1' : '0'
    ) == '1';
    $cupoActual = old('cupo_total', $actividadParticipacion?->cupo_total);
    $precioInscripcionActual = old('precio_inscripcion', $actividadParticipacion?->precio_inscripcion);

    $formatearFechaParticipacion = function ($valor) {
        if (!$valor) {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($valor)->format('Y-m-d\\TH:i');
        } catch (\Throwable $e) {
            return (string) $valor;
        }
    };
@endphp

<div class="rounded-2xl border border-white/10 bg-white/[0.03]">
    <div class="border-b border-white/10 px-4 py-5 sm:px-6">
        <h2 class="text-lg font-semibold text-white">Participación y acceso</h2>
        <p class="mt-1 text-sm leading-6 text-gray-500">
            Define qué significa “participar” en esta actividad. Los productos y servicios opcionales se manejan por separado.
        </p>
    </div>

    <div class="space-y-6 p-4 sm:p-6">
        <div>
            <p class="text-sm font-semibold text-white">¿Cómo interactuará el público con esta actividad?</p>
            <p class="mt-1 text-xs leading-5 text-gray-500">
                Elige la opción que mejor represente el objetivo principal. Después podrás vender productos aunque la participación general sea gratuita.
            </p>

            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach ([
                    'informativa' => [
                        'titulo' => 'Solo informativa / acceso libre',
                        'texto' => 'Las personas pueden consultar el detalle y el cronograma sin una inscripción general.',
                        'icono' => '👀',
                    ],
                    'registro_gratuito' => [
                        'titulo' => 'Inscripción gratuita',
                        'texto' => 'La persona se registra para participar, pero la inscripción no tiene costo. Los productos siguen siendo opcionales.',
                        'icono' => '✓',
                    ],
                    'registro_pago' => [
                        'titulo' => 'Inscripción con pago',
                        'texto' => 'Participar requiere una inscripción pagada. Ideal para excursiones, congresos, entradas o actividades con cuota.',
                        'icono' => '$',
                    ],
                    'venta_directa' => [
                        'titulo' => 'Venta de productos o servicios',
                        'texto' => 'No existe inscripción general: la acción principal es comprar los productos o servicios publicados.',
                        'icono' => '🛍',
                    ],
                ] as $valorTipo => $opcionTipo)
                    <label class="cursor-pointer">
                        <input
                            type="radio"
                            name="tipo_participacion"
                            value="{{ $valorTipo }}"
                            class="peer sr-only tipo-participacion-radio"
                            @checked($tipoParticipacionActual === $valorTipo)
                        >
                        <span class="flex h-full gap-3 rounded-2xl border border-white/10 bg-black/10 p-4 transition hover:border-white/20 peer-checked:border-emerald-500/70 peer-checked:bg-emerald-500/10 peer-checked:ring-1 peer-checked:ring-emerald-500/30">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 text-lg">{{ $opcionTipo['icono'] }}</span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-white">{{ $opcionTipo['titulo'] }}</span>
                                <span class="mt-1 block text-xs leading-5 text-gray-500">{{ $opcionTipo['texto'] }}</span>
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>

            @error('tipo_participacion')
                <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <input type="hidden" name="habilita_inscripcion" value="0">
        <input
            type="checkbox"
            id="habilita_inscripcion"
            name="habilita_inscripcion"
            value="1"
            class="hidden"
            tabindex="-1"
            aria-hidden="true"
            @checked($requiereInscripcionActual)
        >

        <div id="configuracionInscripcion" class="space-y-6 {{ $requiereInscripcionActual ? '' : 'hidden' }}">
            <div class="rounded-2xl border border-emerald-500/15 bg-emerald-500/[0.04] p-4 sm:p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-white">Configuración de la inscripción general</p>
                        <p class="mt-1 max-w-2xl text-xs leading-5 text-gray-500">
                            Esto controla el registro a la actividad completa. Las reservas de sesiones específicas pueden configurarse aparte.
                        </p>
                    </div>
                    <span id="etiquetaTipoInscripcion" class="w-fit rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-300"></span>
                </div>

                <div id="bloquePrecioInscripcion" class="mt-5 {{ $tipoParticipacionActual === 'registro_pago' ? '' : 'hidden' }}">
                    <label for="precio_inscripcion" class="mb-2 block text-sm font-medium text-gray-300">Precio de la inscripción</label>
                    <div class="relative max-w-sm">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">$</span>
                        <input
                            type="number"
                            id="precio_inscripcion"
                            name="precio_inscripcion"
                            min="0.01"
                            step="0.01"
                            value="{{ $precioInscripcionActual }}"
                            placeholder="0.00"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 py-3 pl-8 pr-4 text-base text-white outline-none placeholder:text-gray-600 focus:border-emerald-500 sm:text-sm"
                        >
                    </div>
                    <p class="mt-2 text-xs leading-5 text-gray-500">Este monto corresponde a participar en la actividad. No incluye productos opcionales.</p>
                    @error('precio_inscripcion')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="cupo_total" class="mb-2 block text-sm font-medium text-gray-300">Cupo máximo</label>
                    <input
                        type="number"
                        id="cupo_total"
                        name="cupo_total"
                        value="{{ $cupoActual }}"
                        min="0"
                        placeholder="Ej. 100"
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-white outline-none placeholder:text-gray-600 focus:border-emerald-500 sm:text-sm"
                    >
                    <p class="mt-2 text-xs text-gray-500">Déjalo vacío si no existe límite general.</p>
                </div>

                <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                    <input type="hidden" name="permite_lista_espera" value="0">
                    <label class="flex cursor-pointer items-start gap-3">
                        <input
                            type="checkbox"
                            id="permite_lista_espera"
                            name="permite_lista_espera"
                            value="1"
                            @checked($permiteEsperaActual)
                            class="mt-1 h-4 w-4 shrink-0 rounded border-gray-600 bg-gray-800 text-emerald-500 focus:ring-emerald-500"
                        >
                        <span>
                            <span class="block text-sm font-medium text-gray-300">Permitir lista de espera</span>
                            <span class="mt-1 block text-xs leading-5 text-gray-500">Cuando el cupo se complete, otras personas podrán registrar su interés.</span>
                        </span>
                    </label>
                </div>
            </div>

            <div>
                <p class="mb-3 text-sm font-medium text-gray-300">Período de inscripción</p>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="inscripcion_desde" class="mb-2 block text-xs font-medium text-gray-500">Inscripciones desde</label>
                        <input
                            type="datetime-local"
                            id="inscripcion_desde"
                            name="inscripcion_desde"
                            value="{{ $formatearFechaParticipacion(old('inscripcion_desde', $actividadParticipacion?->inscripcion_desde)) }}"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-gray-200 outline-none focus:border-emerald-500 sm:text-sm"
                        >
                        @error('inscripcion_desde')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="inscripcion_hasta" class="mb-2 block text-xs font-medium text-gray-500">Inscripciones hasta</label>
                        <input
                            type="datetime-local"
                            id="inscripcion_hasta"
                            name="inscripcion_hasta"
                            value="{{ $formatearFechaParticipacion(old('inscripcion_hasta', $actividadParticipacion?->inscripcion_hasta)) }}"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-base text-gray-200 outline-none focus:border-emerald-500 sm:text-sm"
                        >
                        @error('inscripcion_hasta')
                            <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div id="configuracionCuenta" class="{{ $tipoParticipacionActual === 'informativa' ? 'hidden' : '' }}">
            <div class="rounded-xl border border-white/10 bg-black/10 p-4">
                <input type="hidden" name="requiere_cuenta" value="0">
                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        type="checkbox"
                        id="requiere_cuenta"
                        name="requiere_cuenta"
                        value="1"
                        @checked($requiereCuentaActual)
                        class="mt-1 h-4 w-4 shrink-0 rounded border-gray-600 bg-gray-800 text-emerald-500 focus:ring-emerald-500"
                    >
                    <span>
                        <span class="block text-sm font-medium text-gray-300">Requerir una cuenta para continuar</span>
                        <span class="mt-1 block text-xs leading-5 text-gray-500">Aplícalo cuando quieras identificar al participante o comprador antes de completar la acción.</span>
                    </span>
                </label>
            </div>
        </div>

        <div id="mensajeSinInscripcion" class="{{ $requiereInscripcionActual ? 'hidden' : '' }} rounded-xl border border-blue-500/20 bg-blue-500/5 p-5">
            <div id="mensajeParticipacionInformativa" class="{{ $tipoParticipacionActual === 'informativa' ? '' : 'hidden' }}">
                <p class="font-medium text-blue-300">La actividad será principalmente informativa o de acceso libre.</p>
                <p class="mt-1 text-sm leading-6 text-gray-500">El público podrá explorar el detalle y cronograma sin una inscripción general.</p>
            </div>
            <div id="mensajeParticipacionVenta" class="{{ $tipoParticipacionActual === 'venta_directa' ? '' : 'hidden' }}">
                <p class="font-medium text-blue-300">La acción principal será comprar.</p>
                <p class="mt-1 text-sm leading-6 text-gray-500">No habrá inscripción general. Los productos y servicios se configuran en el módulo comercial de la actividad.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const radiosTipo = Array.from(document.querySelectorAll('.tipo-participacion-radio'));
    const habilitaInscripcion = document.getElementById('habilita_inscripcion');
    const configuracionInscripcion = document.getElementById('configuracionInscripcion');
    const configuracionCuenta = document.getElementById('configuracionCuenta');
    const mensajeSinInscripcion = document.getElementById('mensajeSinInscripcion');
    const mensajeInformativa = document.getElementById('mensajeParticipacionInformativa');
    const mensajeVenta = document.getElementById('mensajeParticipacionVenta');
    const bloquePrecio = document.getElementById('bloquePrecioInscripcion');
    const precio = document.getElementById('precio_inscripcion');
    const etiqueta = document.getElementById('etiquetaTipoInscripcion');

    function tipoSeleccionado() {
        return radiosTipo.find(radio => radio.checked)?.value || 'informativa';
    }

    function sincronizarParticipacion() {
        const tipo = tipoSeleccionado();
        const requiereInscripcion = tipo === 'registro_gratuito' || tipo === 'registro_pago';
        const esPago = tipo === 'registro_pago';

        if (habilitaInscripcion) {
            habilitaInscripcion.checked = requiereInscripcion;
        }

        configuracionInscripcion?.classList.toggle('hidden', !requiereInscripcion);
        configuracionCuenta?.classList.toggle('hidden', tipo === 'informativa');
        mensajeSinInscripcion?.classList.toggle('hidden', requiereInscripcion);
        mensajeInformativa?.classList.toggle('hidden', tipo !== 'informativa');
        mensajeVenta?.classList.toggle('hidden', tipo !== 'venta_directa');
        bloquePrecio?.classList.toggle('hidden', !esPago);

        if (precio) {
            precio.required = esPago;
        }

        if (etiqueta) {
            etiqueta.textContent = esPago ? 'Inscripción con pago' : 'Inscripción gratuita';
        }

        habilitaInscripcion?.dispatchEvent(new Event('change'));
    }

    radiosTipo.forEach(radio => radio.addEventListener('change', sincronizarParticipacion));
    sincronizarParticipacion();
});
</script>
