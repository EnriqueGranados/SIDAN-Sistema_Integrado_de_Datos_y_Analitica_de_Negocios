@php
    $idEspacioInicial = old(
        'id_espacio',
        isset($actividad) ? $actividad->id_espacio : null
    );

    $espacioInicial = null;

    if ($idEspacioInicial && isset($espacios)) {
        $espacioInicial = $espacios->firstWhere(
            'id_espacio',
            (int) $idEspacioInicial
        );
    }

    if (
        !$espacioInicial
        && isset($actividad)
        && $actividad->relationLoaded('espacio')
    ) {
        $espacioInicial = $actividad->espacio;
    }
@endphp

<div
    id="selectorEspacioActividad"
    class="rounded-2xl border border-white/10 bg-white/[0.03]"
>
    <div class="border-b border-white/10 px-4 py-5 sm:px-6">
        <h2 class="text-lg font-semibold text-white">
            Ubicación general
        </h2>

        <p class="mt-1 text-sm leading-6 text-gray-500">
            Busca el lugar principal de la actividad o registra uno nuevo si todavía no existe.
        </p>
    </div>

    <div class="space-y-4 p-4 sm:p-6">
        <input
            type="hidden"
            id="id_espacio"
            name="id_espacio"
            value="{{ $idEspacioInicial }}"
        >

        <input
            type="hidden"
            name="ubicacion_externa"
            value=""
        >

        <div>
            <label
                for="buscarEspacioGeneral"
                class="mb-2 block text-sm font-medium text-gray-300"
            >
                Lugar de la actividad
            </label>

            <div class="relative">
                <div class="relative">
                    <svg
                        class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>

                    <input
                        type="text"
                        id="buscarEspacioGeneral"
                        autocomplete="off"
                        placeholder="Escribe para buscar un espacio..."
                        class="block w-full rounded-xl border border-white/10 bg-black/20 py-3 pl-12 pr-12 text-base text-white outline-none placeholder:text-gray-600 focus:border-emerald-500 sm:text-sm"
                    >

                    <div
                        id="cargandoEspacios"
                        class="pointer-events-none absolute right-4 top-1/2 hidden -translate-y-1/2"
                    >
                        <svg
                            class="h-5 w-5 animate-spin text-emerald-400"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            ></path>
                        </svg>
                    </div>
                </div>

                <div
                    id="resultadosEspacios"
                    class="absolute left-0 right-0 top-full z-40 mt-2 hidden max-h-80 overflow-y-auto rounded-xl border border-white/10 bg-[#111827] p-2 shadow-2xl"
                ></div>
            </div>

            <p class="mt-2 text-xs text-gray-600">
                Puedes buscar por nombre, dirección o espacio contenedor.
            </p>

            @error('id_espacio')
                <p class="mt-2 text-xs text-red-400">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div
            id="espacioSeleccionadoCard"
            class="{{ $espacioInicial ? '' : 'hidden' }} rounded-xl border border-emerald-500/20 bg-emerald-500/[0.06] p-4"
        >
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-emerald-400">
                            Espacio seleccionado
                        </span>

                        <span
                            id="espacioPendienteBadge"
                            class="{{ $espacioInicial && method_exists($espacioInicial, 'estaPendienteValidacion') && $espacioInicial->estaPendienteValidacion() ? '' : 'hidden' }} rounded-full border border-amber-500/20 bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-300"
                        >
                            Pendiente de validación
                        </span>
                    </div>

                    <p
                        id="espacioSeleccionadoNombre"
                        class="mt-2 break-words text-base font-semibold text-white"
                    >
                        {{ $espacioInicial?->nombre }}
                    </p>

                    <p
                        id="espacioSeleccionadoRuta"
                        class="mt-1 break-words text-xs leading-5 text-gray-400"
                    >
                        @if($espacioInicial)
                            {{ method_exists($espacioInicial, 'rutaJerarquica') ? $espacioInicial->rutaJerarquica() : '' }}
                        @endif
                    </p>

                    <p
                        id="espacioSeleccionadoDireccion"
                        class="mt-2 break-words text-xs leading-5 text-gray-500"
                    >
                        {{ $espacioInicial?->direccion }}
                    </p>
                    <p
                        id="espacioSeleccionadoCapacidad"
                        class="mt-2 {{ $espacioInicial?->capacidad ? '' : 'hidden' }} text-xs font-medium text-cyan-300"
                    >
                        @if($espacioInicial?->capacidad)
                            Capacidad registrada: {{ $espacioInicial->capacidad }} personas
                        @endif
                    </p>
                </div>

                <button
                    type="button"
                    id="quitarEspacioSeleccionado"
                    class="shrink-0 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs font-medium text-gray-400 transition hover:border-red-500/20 hover:bg-red-500/10 hover:text-red-300"
                >
                    Cambiar
                </button>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-white/10 pt-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs leading-5 text-gray-500">
                Si el lugar todavía no está registrado puedes agregarlo sin salir de la actividad.
            </p>

            <button
                type="button"
                id="abrirCrearEspacio"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-300 transition hover:bg-emerald-500/15"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 4v16m8-8H4"
                    />
                </svg>

                Registrar nuevo espacio
            </button>
        </div>
    </div>
</div>

<div
    id="modalCrearEspacio"
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/70 p-4 backdrop-blur-sm"
>
    <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-white/10 bg-[#111827] shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-white/10 px-5 py-5 sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-white">
                    Registrar nuevo espacio
                </h3>

                <p class="mt-1 text-sm leading-6 text-gray-500">
                    El espacio podrá utilizarse inmediatamente y quedará pendiente de revisión administrativa.
                </p>
            </div>

            <button
                type="button"
                id="cerrarCrearEspacio"
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-white/10 text-gray-400 transition hover:bg-white/5 hover:text-white"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>

        <div class="max-h-[75vh] overflow-y-auto p-5 sm:p-6">
            <div
                id="errorCrearEspacio"
                class="mb-5 hidden rounded-xl border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-300"
            ></div>

            <div class="space-y-5">
                <div>
                    <label
                        for="nuevoEspacioNombre"
                        class="mb-2 block text-sm font-medium text-gray-300"
                    >
                        Nombre
                    </label>

                    <input
                        type="text"
                        id="nuevoEspacioNombre"
                        maxlength="150"
                        placeholder="Ej. Centro Cultural Universitario"
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none placeholder:text-gray-600 focus:border-emerald-500"
                    >
                </div>

                <div>
                    <label
                        for="nuevoEspacioDescripcion"
                        class="mb-2 block text-sm font-medium text-gray-300"
                    >
                        Descripción breve
                    </label>

                    <textarea
                        id="nuevoEspacioDescripcion"
                        rows="3"
                        maxlength="1000"
                        placeholder="Describe brevemente el espacio"
                        class="block w-full resize-none rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none placeholder:text-gray-600 focus:border-emerald-500"
                    ></textarea>
                </div>

                <div>
                    <label
                        for="nuevoEspacioDireccion"
                        class="mb-2 block text-sm font-medium text-gray-300"
                    >
                        Dirección o referencia
                    </label>

                    <input
                        type="text"
                        id="nuevoEspacioDireccion"
                        maxlength="300"
                        placeholder="Ej. San Miguel, frente a..."
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none placeholder:text-gray-600 focus:border-emerald-500"
                    >
                </div>

                <div>
                    <label
                        for="nuevoEspacioCapacidad"
                        class="mb-2 block text-sm font-medium text-gray-300"
                    >
                        Capacidad máxima
                    </label>

                    <input
                        type="number"
                        id="nuevoEspacioCapacidad"
                        min="1"
                        step="1"
                        placeholder="Ej. 150"
                        class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none placeholder:text-gray-600 focus:border-emerald-500"
                    >
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label
                            for="nuevoEspacioLatitud"
                            class="mb-2 block text-sm font-medium text-gray-300"
                        >
                            Latitud
                        </label>

                        <input
                            type="number"
                            id="nuevoEspacioLatitud"
                            step="any"
                            min="-90"
                            max="90"
                            placeholder="13.7000"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none placeholder:text-gray-600 focus:border-emerald-500"
                        >
                    </div>

                    <div>
                        <label
                            for="nuevoEspacioLongitud"
                            class="mb-2 block text-sm font-medium text-gray-300"
                        >
                            Longitud
                        </label>

                        <input
                            type="number"
                            id="nuevoEspacioLongitud"
                            step="any"
                            min="-180"
                            max="180"
                            placeholder="-88.1000"
                            class="block w-full rounded-xl border border-white/10 bg-black/20 px-4 py-3 text-white outline-none placeholder:text-gray-600 focus:border-emerald-500"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-white/10 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
            <button
                type="button"
                id="cancelarCrearEspacio"
                class="rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-semibold text-gray-300 transition hover:bg-white/10"
            >
                Cancelar
            </button>

            <button
                type="button"
                id="guardarNuevoEspacio"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-500 disabled:cursor-not-allowed disabled:opacity-50"
            >
                <svg
                    id="spinnerGuardarEspacio"
                    class="hidden h-4 w-4 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                    ></path>
                </svg>

                <span id="textoGuardarEspacio">
                    Guardar y seleccionar
                </span>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const buscarUrl = "{{ route('admin.espacios.buscar') }}";
    const crearUrl = "{{ route('admin.espacios.crear-rapido') }}";
    const csrf = "{{ csrf_token() }}";

    const inputId = document.getElementById('id_espacio');
    const buscador = document.getElementById('buscarEspacioGeneral');
    const resultados = document.getElementById('resultadosEspacios');
    const cargando = document.getElementById('cargandoEspacios');

    const card = document.getElementById('espacioSeleccionadoCard');
    const seleccionadoNombre = document.getElementById('espacioSeleccionadoNombre');
    const seleccionadoRuta = document.getElementById('espacioSeleccionadoRuta');
    const seleccionadoDireccion = document.getElementById('espacioSeleccionadoDireccion');
    const seleccionadoCapacidad = document.getElementById('espacioSeleccionadoCapacidad');
    const pendienteBadge = document.getElementById('espacioPendienteBadge');
    const quitar = document.getElementById('quitarEspacioSeleccionado');

    const modal = document.getElementById('modalCrearEspacio');
    const abrirModal = document.getElementById('abrirCrearEspacio');
    const cerrarModal = document.getElementById('cerrarCrearEspacio');
    const cancelarModal = document.getElementById('cancelarCrearEspacio');
    const guardar = document.getElementById('guardarNuevoEspacio');
    const spinner = document.getElementById('spinnerGuardarEspacio');
    const textoGuardar = document.getElementById('textoGuardarEspacio');
    const error = document.getElementById('errorCrearEspacio');

    const nuevoNombre = document.getElementById('nuevoEspacioNombre');
    const nuevaDescripcion = document.getElementById('nuevoEspacioDescripcion');
    const nuevaDireccion = document.getElementById('nuevoEspacioDireccion');
    const nuevaCapacidad = document.getElementById('nuevoEspacioCapacidad');
    const nuevaLatitud = document.getElementById('nuevoEspacioLatitud');
    const nuevaLongitud = document.getElementById('nuevoEspacioLongitud');

    let temporizador = null;
    let peticionActual = null;

    function limpiarResultados() {
        resultados.innerHTML = '';
        resultados.classList.add('hidden');
    }

    function mostrarSeleccion(espacio) {
        inputId.value = espacio.id ?? '';

        seleccionadoNombre.textContent = espacio.nombre ?? '';
        seleccionadoRuta.textContent = espacio.ruta ?? espacio.contexto ?? '';
        seleccionadoDireccion.textContent = espacio.direccion ?? '';

        if (espacio.capacidad) {
            seleccionadoCapacidad.textContent =
                `Capacidad registrada: ${espacio.capacidad} personas`;

            seleccionadoCapacidad.classList.remove('hidden');
        } else {
            seleccionadoCapacidad.textContent = '';
            seleccionadoCapacidad.classList.add('hidden');
        }

        pendienteBadge.classList.toggle(
            'hidden',
            !espacio.pendiente_validacion
        );

        card.classList.remove('hidden');
        buscador.value = '';
        limpiarResultados();

        document.dispatchEvent(
            new CustomEvent('sidan:espacio-seleccionado', {
                detail: espacio
            })
        );
    }

    function crearResultado(espacio) {
        const boton = document.createElement('button');

        boton.type = 'button';
        boton.className =
            'flex w-full items-start gap-3 rounded-lg px-3 py-3 text-left transition hover:bg-white/5';

        const icono = document.createElement('div');

        icono.className =
            'mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400';

        icono.innerHTML = `
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        `;

        const contenido = document.createElement('div');
        contenido.className = 'min-w-0 flex-1';

        const filaNombre = document.createElement('div');
        filaNombre.className = 'flex flex-wrap items-center gap-2';

        const nombre = document.createElement('p');
        nombre.className = 'break-words text-sm font-semibold text-white';
        nombre.textContent = espacio.nombre ?? '';

        filaNombre.appendChild(nombre);

        if (espacio.pendiente_validacion) {
            const badge = document.createElement('span');

            badge.className =
                'rounded-full bg-amber-500/10 px-2 py-0.5 text-[10px] font-semibold text-amber-300';

            badge.textContent = 'Pendiente';

            filaNombre.appendChild(badge);
        }

        contenido.appendChild(filaNombre);

        if (espacio.contexto) {
            const contexto = document.createElement('p');

            contexto.className =
                'mt-1 break-words text-xs leading-5 text-gray-500';

            contexto.textContent = espacio.contexto;

            contenido.appendChild(contexto);
        }

        if (espacio.direccion) {
            const direccion = document.createElement('p');

            direccion.className =
                'mt-1 break-words text-xs leading-5 text-gray-600';

            direccion.textContent = espacio.direccion;

            contenido.appendChild(direccion);
        }

        if (espacio.capacidad) {
            const capacidad = document.createElement('p');

            capacidad.className =
                'mt-1 text-[11px] font-medium text-cyan-400';

            capacidad.textContent =
                `Capacidad: ${espacio.capacidad}`;

            contenido.appendChild(capacidad);
        }

        boton.appendChild(icono);
        boton.appendChild(contenido);

        boton.addEventListener('click', () => {
            mostrarSeleccion(espacio);
        });

        return boton;
    }

    async function buscarEspacios() {
        const q = buscador.value.trim();

        peticionActual?.abort();

        const controller = new AbortController();
        peticionActual = controller;

        cargando.classList.remove('hidden');

        try {
            const url = new URL(buscarUrl, window.location.origin);

            url.searchParams.set('modo', 'general');
            url.searchParams.set('limite', '12');

            if (q !== '') {
                url.searchParams.set('q', q);
            }

            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json'
                },
                signal: controller.signal
            });

            if (!response.ok) {
                throw new Error('No se pudieron cargar los espacios.');
            }

            const data = await response.json();
            const espacios = data.data ?? [];

            resultados.innerHTML = '';

            if (espacios.length === 0) {
                const vacio = document.createElement('div');

                vacio.className =
                    'px-3 py-4 text-center text-sm text-gray-500';

                vacio.textContent =
                    'No encontramos espacios con esa búsqueda.';

                resultados.appendChild(vacio);
            } else {
                espacios.forEach(espacio => {
                    resultados.appendChild(
                        crearResultado(espacio)
                    );
                });
            }

            resultados.classList.remove('hidden');
        } catch (e) {
            if (e.name !== 'AbortError') {
                limpiarResultados();
            }
        } finally {
            if (peticionActual === controller) {
                cargando.classList.add('hidden');
            }
        }
    }

    function programarBusqueda() {
        clearTimeout(temporizador);

        temporizador = setTimeout(
            buscarEspacios,
            250
        );
    }

    function abrirCrear() {
        error.classList.add('hidden');
        error.textContent = '';

        if (buscador.value.trim() !== '') {
            nuevoNombre.value = buscador.value.trim();
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            nuevoNombre.focus();
        }, 50);
    }

    function cerrarCrear() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function limpiarFormularioNuevo() {
        nuevoNombre.value = '';
        nuevaDescripcion.value = '';
        nuevaDireccion.value = '';
        nuevaCapacidad.value = '';
        nuevaLatitud.value = '';
        nuevaLongitud.value = '';

        error.classList.add('hidden');
        error.textContent = '';
    }

    function mostrarErrorCrear(mensaje) {
        error.textContent = mensaje;
        error.classList.remove('hidden');
    }

    async function guardarNuevoEspacio() {
        const nombre = nuevoNombre.value.trim();
        const direccion = nuevaDireccion.value.trim();

        if (!nombre) {
            mostrarErrorCrear('Escribe el nombre del espacio.');
            nuevoNombre.focus();
            return;
        }

        if (!direccion) {
            mostrarErrorCrear('Escribe la dirección o referencia del espacio.');
            nuevaDireccion.focus();
            return;
        }

        guardar.disabled = true;
        spinner.classList.remove('hidden');
        textoGuardar.textContent = 'Guardando...';

        error.classList.add('hidden');
        error.textContent = '';

        try {
            const response = await fetch(crearUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({
                    origen: 'actividad',
                    id_espacio_contenedor: null,
                    nombre,
                    descripcion:
                        nuevaDescripcion.value.trim() || null,
                    direccion,
                    capacidad:
                        nuevaCapacidad.value !== ''
                            ? Number(nuevaCapacidad.value)
                            : null,
                    latitud:
                        nuevaLatitud.value !== ''
                            ? Number(nuevaLatitud.value)
                            : null,
                    longitud:
                        nuevaLongitud.value !== ''
                            ? Number(nuevaLongitud.value)
                            : null
                })
            });

            const data = await response.json();

            if (!response.ok) {
                if (data.espacio) {
                    mostrarSeleccion(data.espacio);
                    cerrarCrear();
                    limpiarFormularioNuevo();
                    return;
                }

                const errores = data.errors
                    ? Object.values(data.errors)
                        .flat()
                        .join(' ')
                    : null;

                throw new Error(
                    errores
                    || data.message
                    || 'No se pudo registrar el espacio.'
                );
            }

            mostrarSeleccion(data.espacio);

            cerrarCrear();
            limpiarFormularioNuevo();
        } catch (e) {
            mostrarErrorCrear(
                e.message || 'No se pudo registrar el espacio.'
            );
        } finally {
            guardar.disabled = false;
            spinner.classList.add('hidden');
            textoGuardar.textContent = 'Guardar y seleccionar';
        }
    }

    buscador.addEventListener('input', programarBusqueda);

    buscador.addEventListener('focus', () => {
        buscarEspacios();
    });

    quitar.addEventListener('click', () => {
        inputId.value = '';
        card.classList.add('hidden');
        buscador.value = '';
        buscador.focus();

        document.dispatchEvent(
            new CustomEvent('sidan:espacio-seleccionado', {
                detail: null
            })
        );

        buscarEspacios();
    });

    abrirModal.addEventListener('click', abrirCrear);
    cerrarModal.addEventListener('click', cerrarCrear);
    cancelarModal.addEventListener('click', cerrarCrear);
    guardar.addEventListener('click', guardarNuevoEspacio);

    modal.addEventListener('click', event => {
        if (event.target === modal) {
            cerrarCrear();
        }
    });

    document.addEventListener('click', event => {
        if (
            !resultados.contains(event.target)
            && event.target !== buscador
        ) {
            limpiarResultados();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') {
            return;
        }

        limpiarResultados();

        if (!modal.classList.contains('hidden')) {
            cerrarCrear();
        }
    });
});
</script>