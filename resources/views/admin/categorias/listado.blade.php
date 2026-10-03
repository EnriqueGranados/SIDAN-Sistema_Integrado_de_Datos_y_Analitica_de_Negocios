@extends('layouts.navbars')

@section('title', 'Categorías')

@section('content')
<div class="min-h-screen bg-slate-950">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Categorías</h1>
                <p class="mt-1 text-sm text-slate-400">Administra y organiza las categorías disponibles para las actividades.</p>
            </div>

            <a href="{{ route('admin.categorias.create') }}"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva categoría
            </a>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                {{ session('error') }}
            </div>
        @endif

        <div id="mensajeOrden" class="mb-5 hidden rounded-xl border px-4 py-3 text-sm"></div>

        <div class="mb-6 rounded-2xl border border-slate-800 bg-slate-900/70 p-4 shadow-xl shadow-black/10">
            <form id="formFiltrosCategorias" method="GET" action="{{ route('admin.categorias.index') }}">
                <div class="grid gap-4 md:grid-cols-[1fr_220px_auto]">
                    <div>
                        <label for="buscar" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Buscar
                        </label>

                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                </svg>
                            </div>

                            <input type="text"
                                id="buscar"
                                name="buscar"
                                value="{{ $buscar }}"
                                autocomplete="off"
                                placeholder="Buscar por nombre, slug o descripción..."
                                class="h-11 w-full rounded-xl border border-slate-700 bg-slate-950/80 pl-10 pr-10 text-sm text-white outline-none transition placeholder:text-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">

                            <div id="indicadorBusqueda"
                                class="pointer-events-none absolute inset-y-0 right-0 hidden items-center pr-3">
                                <svg class="h-4 w-4 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="estado" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-400">
                            Estado
                        </label>

                        <select id="estado"
                            name="estado"
                            class="h-11 w-full rounded-xl border border-slate-700 bg-slate-950/80 px-3 text-sm text-white outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            <option value="">Todos</option>
                            <option value="activo" @selected($estado === 'activo')>Activas</option>
                            <option value="inactivo" @selected($estado === 'inactivo')>Inactivas</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button type="button"
                            id="limpiarFiltros"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-700 px-4 text-sm font-semibold text-slate-300 transition hover:bg-slate-800 hover:text-white">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                            Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div id="resultadosCategorias" class="transition-opacity duration-150">
            @if ($categorias->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/40 px-6 py-14 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-800 text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </div>

                    <h2 class="mt-4 text-base font-semibold text-white">
                        @if ($buscar !== '' || $estado)
                            No se encontraron categorías
                        @else
                            Todavía no hay categorías
                        @endif
                    </h2>

                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-400">
                        @if ($buscar !== '' || $estado)
                            Prueba modificando los filtros de búsqueda.
                        @else
                            Crea la primera categoría para comenzar a organizar las actividades.
                        @endif
                    </p>

                    @if ($buscar === '' && !$estado)
                        <a href="{{ route('admin.categorias.create') }}"
                            class="mt-5 inline-flex h-10 items-center justify-center rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            Crear categoría
                        </a>
                    @endif
                </div>
            @else
                @if ($buscar === '' && !$estado)
                    <div class="mb-4 flex items-start gap-3 rounded-xl border border-indigo-500/20 bg-indigo-500/5 p-4">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6-4 4-4-4" />
                        </svg>

                        <div>
                            <p class="text-sm font-semibold text-indigo-300">Organiza las categorías</p>
                            <p class="mt-1 text-sm text-slate-400">Usa las flechas para cambiar la prioridad. Las categorías superiores aparecerán primero.</p>
                        </div>
                    </div>
                @endif

                <div id="listaCategorias" class="space-y-3">
                    @foreach ($categorias as $categoria)
                        <div class="categoria-item rounded-2xl border border-slate-800 bg-slate-900/70 p-4 shadow-lg shadow-black/10 transition"
                            data-id="{{ $categoria->id_categoria }}">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                                @if ($buscar === '' && !$estado)
                                    <div class="flex shrink-0 items-center gap-1 lg:flex-col">
                                        <button type="button"
                                            class="btn-subir inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 bg-slate-800 text-slate-400 transition hover:border-indigo-500/40 hover:bg-indigo-500/10 hover:text-indigo-300 disabled:cursor-not-allowed disabled:opacity-30"
                                            title="Subir categoría">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m18 15-6-6-6 6" />
                                            </svg>
                                        </button>

                                        <button type="button"
                                            class="btn-bajar inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 bg-slate-800 text-slate-400 transition hover:border-indigo-500/40 hover:bg-indigo-500/10 hover:text-indigo-300 disabled:cursor-not-allowed disabled:opacity-30"
                                            title="Bajar categoría">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="font-semibold text-white">{{ $categoria->nombre }}</h2>

                                        @if ($categoria->activo)
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/20 bg-emerald-500/10 px-2.5 py-1 text-xs font-medium text-emerald-300">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                                Activa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-600 bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-400">
                                                <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>
                                                Inactiva
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-xs text-slate-500">{{ $categoria->slug }}</p>

                                    @if ($categoria->descripcion)
                                        <p class="mt-2 line-clamp-2 max-w-2xl text-sm leading-6 text-slate-400">
                                            {{ $categoria->descripcion }}
                                        </p>
                                    @else
                                        <p class="mt-2 text-sm italic text-slate-600">Sin descripción.</p>
                                    @endif
                                </div>

                                <div class="flex shrink-0 flex-wrap items-center gap-3">
                                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 px-4 py-2 text-center">
                                        <p class="text-xs text-slate-500">Actividades</p>
                                        <p class="mt-0.5 text-sm font-bold text-white">{{ $categoria->actividades_count }}</p>
                                    </div>

                                    <a href="{{ route('admin.categorias.edit', $categoria) }}"
                                        class="inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 text-xs font-semibold text-amber-300 transition hover:bg-amber-500/20">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 3.487 3.651 3.651M5 19l4.5-1 11-11a2.582 2.582 0 0 0-3.651-3.651l-11 11L5 19Z" />
                                        </svg>
                                        Editar
                                    </a>

                                    @if ($categoria->actividades_count === 0)
                                        <form method="POST"
                                            action="{{ route('admin.categorias.destroy', $categoria) }}"
                                            onsubmit="return confirm('¿Eliminar esta categoría? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="inline-flex h-9 items-center justify-center rounded-lg border border-red-500/20 bg-red-500/10 px-3 text-xs font-semibold text-red-300 transition hover:bg-red-500/20"
                                                title="Eliminar categoría">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button"
                                            disabled
                                            class="inline-flex h-9 cursor-not-allowed items-center justify-center rounded-lg border border-slate-700 bg-slate-800 px-3 text-xs text-slate-600"
                                            title="No se puede eliminar porque tiene actividades asociadas">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0 1 13h6l1-13M9 7V4h6v3" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($categorias->hasPages())
                    <div class="mt-6 categoria-paginacion">
                        {{ $categorias->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formFiltrosCategorias');
    const buscar = document.getElementById('buscar');
    const estado = document.getElementById('estado');
    const limpiar = document.getElementById('limpiarFiltros');
    const resultados = document.getElementById('resultadosCategorias');
    const indicador = document.getElementById('indicadorBusqueda');
    const mensaje = document.getElementById('mensajeOrden');

    if (!form || !buscar || !estado || !resultados) {
        return;
    }

    const urlIndex = "{{ route('admin.categorias.index') }}";
    const urlReordenar = "{{ route('admin.categorias.reorder') }}";
    const csrfToken = "{{ csrf_token() }}";

    let temporizadorBusqueda = null;
    let controladorBusqueda = null;
    let guardandoOrden = false;

    function construirUrl(pagina = null) {
        const url = new URL(urlIndex, window.location.origin);
        const texto = buscar.value.trim();

        if (texto !== '') {
            url.searchParams.set('buscar', texto);
        }

        if (estado.value !== '') {
            url.searchParams.set('estado', estado.value);
        }

        if (pagina) {
            url.searchParams.set('page', pagina);
        }

        return url;
    }

    function mostrarCargando(activo) {
        indicador.classList.toggle('hidden', !activo);
        indicador.classList.toggle('flex', activo);
        resultados.classList.toggle('opacity-50', activo);
        resultados.classList.toggle('pointer-events-none', activo);
    }

    function mostrarMensaje(texto, tipo = 'success') {
        if (!mensaje) {
            return;
        }

        mensaje.className = 'mb-5 rounded-xl border px-4 py-3 text-sm';

        if (tipo === 'success') {
            mensaje.classList.add(
                'border-emerald-500/20',
                'bg-emerald-500/10',
                'text-emerald-300'
            );
        } else {
            mensaje.classList.add(
                'border-red-500/20',
                'bg-red-500/10',
                'text-red-300'
            );
        }

        mensaje.textContent = texto;
        mensaje.classList.remove('hidden');

        window.clearTimeout(mensaje._timeout);

        mensaje._timeout = window.setTimeout(() => {
            mensaje.classList.add('hidden');
        }, 3000);
    }

    async function cargarResultados(url, actualizarHistorial = true) {
        if (controladorBusqueda) {
            controladorBusqueda.abort();
        }

        controladorBusqueda = new AbortController();
        mostrarCargando(true);

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'text/html',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controladorBusqueda.signal
            });

            if (!response.ok) {
                throw new Error('No se pudieron cargar las categorías.');
            }

            const html = await response.text();
            const documento = new DOMParser().parseFromString(html, 'text/html');
            const nuevosResultados = documento.getElementById('resultadosCategorias');

            if (!nuevosResultados) {
                throw new Error('No se pudieron procesar los resultados.');
            }

            resultados.innerHTML = nuevosResultados.innerHTML;

            if (actualizarHistorial) {
                const ruta = url.pathname + url.search;
                window.history.replaceState({}, '', ruta);
            }

            actualizarBotonesOrden();
        } catch (error) {
            if (error.name !== 'AbortError') {
                mostrarMensaje(
                    error.message || 'Ocurrió un error al buscar categorías.',
                    'error'
                );
            }
        } finally {
            mostrarCargando(false);
        }
    }

    function buscarCategorias() {
        cargarResultados(construirUrl());
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        clearTimeout(temporizadorBusqueda);
        buscarCategorias();
    });

    buscar.addEventListener('input', () => {
        clearTimeout(temporizadorBusqueda);

        temporizadorBusqueda = window.setTimeout(() => {
            buscarCategorias();
        }, 350);
    });

    estado.addEventListener('change', () => {
        clearTimeout(temporizadorBusqueda);
        buscarCategorias();
    });

    limpiar.addEventListener('click', () => {
        clearTimeout(temporizadorBusqueda);
        buscar.value = '';
        estado.value = '';
        buscar.focus();
        buscarCategorias();
    });

    resultados.addEventListener('click', event => {
        const enlace = event.target.closest('.categoria-paginacion a');

        if (!enlace) {
            return;
        }

        event.preventDefault();

        const url = new URL(enlace.href);
        cargarResultados(url);
    });

    function obtenerLista() {
        return resultados.querySelector('#listaCategorias');
    }

    function obtenerItems() {
        const lista = obtenerLista();

        if (!lista) {
            return [];
        }

        return [...lista.querySelectorAll('.categoria-item')];
    }

    function actualizarBotonesOrden() {
        const items = obtenerItems();

        items.forEach((item, index) => {
            const subir = item.querySelector('.btn-subir');
            const bajar = item.querySelector('.btn-bajar');

            if (subir) {
                subir.disabled = index === 0 || guardandoOrden;
            }

            if (bajar) {
                bajar.disabled = index === items.length - 1 || guardandoOrden;
            }
        });
    }

    async function guardarOrden() {
        const categorias = obtenerItems().map(item => Number(item.dataset.id));

        if (categorias.length === 0) {
            return;
        }

        guardandoOrden = true;
        actualizarBotonesOrden();

        try {
            const response = await fetch(urlReordenar, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    categorias
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'No se pudo guardar el nuevo orden.'
                );
            }

            mostrarMensaje(
                data.message || 'Orden actualizado correctamente.'
            );
        } catch (error) {
            mostrarMensaje(
                error.message || 'No se pudo guardar el nuevo orden.',
                'error'
            );

            window.setTimeout(() => {
                cargarResultados(construirUrl(), false);
            }, 1000);
        } finally {
            guardandoOrden = false;
            actualizarBotonesOrden();
        }
    }

    resultados.addEventListener('click', async event => {
        if (guardandoOrden) {
            return;
        }

        const subir = event.target.closest('.btn-subir');
        const bajar = event.target.closest('.btn-bajar');

        if (!subir && !bajar) {
            return;
        }

        const item = event.target.closest('.categoria-item');
        const lista = obtenerLista();

        if (!item || !lista) {
            return;
        }

        if (subir) {
            const anterior = item.previousElementSibling;

            if (!anterior) {
                return;
            }

            lista.insertBefore(item, anterior);
        }

        if (bajar) {
            const siguiente = item.nextElementSibling;

            if (!siguiente) {
                return;
            }

            lista.insertBefore(siguiente, item);
        }

        actualizarBotonesOrden();
        await guardarOrden();
    });

    window.addEventListener('popstate', () => {
        const url = new URL(window.location.href);
        buscar.value = url.searchParams.get('buscar') ?? '';
        estado.value = url.searchParams.get('estado') ?? '';
        cargarResultados(url, false);
    });

    actualizarBotonesOrden();
});
</script>
@endsection