@extends('layouts.navbars')

@section('title', 'SIDAN | Administrar Welcome')

@section('content')
<div
    id="welcomeAdmin"
    class="min-h-full bg-[#f6f8fb] dark:bg-sidan-950"
    data-search-url="{{ route('admin.welcome.actividades.buscar') }}"
    data-create-url="{{ route('admin.welcome.espacios.store') }}"
>
    <main class="mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">Portal público</p>
                <h1 class="mt-2 text-3xl font-black text-sidan-900 dark:text-white">Administrar Welcome</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Organiza qué actividades aparecen en el inicio sin cargar listas gigantes. Puedes programar actividades futuras y comprobar el resultado con la vista previa administrativa.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('welcome') }}"
                    target="_blank"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 transition hover:border-sidan-500 hover:text-sidan-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-200"
                >
                    Ver público
                </a>
                <a
                    href="{{ route('welcome', ['preview' => 1]) }}"
                    target="_blank"
                    class="rounded-xl bg-sidan-500 px-4 py-3 text-sm font-black text-white transition hover:bg-green-600"
                >
                    Vista previa real
                </a>
            </div>
        </div>

        @if ($errors->has('welcome'))
            <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">
                {{ $errors->first('welcome') }}
            </div>
        @endif

        @php
            $destacada = $destacada ?? $destacadaConfigurada ?? $destacadaAutomatica ?? null;
            $destacadaEsManual = ($destacadaConfigurada ?? null) !== null;
            $estadoDestacada = $destacada?->estado_welcome;
        @endphp

        <section class="mt-10 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/[0.04] sm:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="max-w-2xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="text-xs font-black uppercase tracking-[0.16em] text-sidan-500">Actividad destacada</p>
                        <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $destacadaEsManual ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300' : 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300' }}">
                            {{ $destacadaEsManual ? 'Selección manual' : 'Selección automática' }}
                        </span>
                    </div>
                    <h2 class="mt-2 text-xl font-black text-sidan-900 dark:text-white">Controla qué actividad recibe el destaque principal</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Puedes escogerla manualmente y cambiar su prioridad. Si quitas la selección manual, SIDAN volverá a elegir automáticamente la actividad visible con mayor prioridad.
                    </p>
                </div>

                <button
                    type="button"
                    data-assign-url="{{ route('admin.welcome.destacada.actualizar') }}"
                    data-slot-name="Actividad destacada"
                    class="js-cambiar-actividad shrink-0 rounded-xl bg-sidan-500 px-4 py-3 text-sm font-black text-white transition hover:bg-green-600"
                >
                    {{ $destacadaEsManual ? 'Cambiar destacada' : 'Elegir destacada' }}
                </button>
            </div>

            @if ($destacada)
                <div class="mt-6 grid gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-white/[0.03] lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                    <div class="flex min-w-0 items-center gap-4">
                        <img src="{{ $destacada->imagen_welcome }}" alt="{{ $destacada->nombre }}" class="h-20 w-28 shrink-0 rounded-xl object-cover">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[11px] font-black uppercase tracking-wide text-sidan-500">{{ $destacada->categoria?->nombre ?? 'Sin categoría' }}</span>
                                @if ($estadoDestacada)
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-black text-slate-600 shadow-sm dark:bg-white/10 dark:text-slate-300">
                                        {{ $estadoDestacada['texto'] }}
                                    </span>
                                @endif
                            </div>
                            <p class="mt-1 truncate text-base font-black text-sidan-900 dark:text-white">{{ $destacada->nombre }}</p>
                            @if ($estadoDestacada)
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $estadoDestacada['detalle'] }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                        <label class="block">
                            <span class="mb-1 block text-[11px] font-black uppercase tracking-wide text-slate-500">Prioridad</span>
                            <select id="welcomeFeaturedPriority" class="min-w-[150px] rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 outline-none focus:border-sidan-500 dark:border-white/10 dark:bg-sidan-950 dark:text-slate-200">
                                <option value="25" @selected((int) $destacada->prioridad === 25)>Baja</option>
                                <option value="50" @selected((int) $destacada->prioridad === 50)>Regular</option>
                                <option value="75" @selected((int) $destacada->prioridad === 75)>Media</option>
                                <option value="100" @selected((int) $destacada->prioridad === 100)>Alta</option>
                            </select>
                        </label>

                        <button
                            type="button"
                            data-url="{{ route('admin.welcome.destacada.prioridad', $destacada) }}"
                            data-select-id="welcomeFeaturedPriority"
                            class="js-guardar-prioridad rounded-xl border border-sidan-500 px-4 py-2.5 text-sm font-black text-sidan-600 transition hover:bg-sidan-500 hover:text-white dark:text-green-300"
                        >
                            Guardar prioridad
                        </button>

                        @if ($destacadaEsManual)
                            <button
                                type="button"
                                data-url="{{ route('admin.welcome.destacada.quitar') }}"
                                class="js-quitar-destacada rounded-xl border border-red-200 px-4 py-2.5 text-sm font-black text-red-600 transition hover:bg-red-500 hover:text-white dark:border-red-500/20 dark:text-red-300"
                            >
                                Volver a automática
                            </button>
                        @endif
                    </div>
                </div>
            @else
                <div class="mt-6 rounded-2xl border border-dashed border-slate-300 px-5 py-8 text-center dark:border-white/10">
                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Todavía no hay una actividad pública y publicada disponible para destacar.</p>
                </div>
            @endif
        </section>

        <section class="mt-10">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">Portada principal</p>
                    <h2 class="mt-1 text-2xl font-black text-sidan-900 dark:text-white">Hero</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $espaciosHero->count() }} de {{ $maxHero }} espacios. Hasta 3 usan el mosaico principal; con más, el Welcome cambia automáticamente a una presentación desplazable.
                    </p>
                </div>

                <button
                    type="button"
                    data-create-section="hero"
                    @disabled($espaciosHero->count() >= $maxHero)
                    class="js-crear-espacio rounded-xl bg-sidan-500 px-4 py-3 text-sm font-black text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    + Agregar espacio Hero
                </button>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($espaciosHero as $espacio)
                    @include('admin.welcome.partials.espacio-dinamico', ['espacio' => $espacio])
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-7 text-center dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">No hay espacios Hero configurados.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="mt-12">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sidan-500">Selección editorial</p>
                    <h2 class="mt-1 text-2xl font-black text-sidan-900 dark:text-white">Explorar</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ $espaciosExplorar->count() }} de {{ $maxExplorar }} espacios. El catálogo completo sigue siendo paginado; aquí solo curas una selección para la portada.
                    </p>
                </div>

                <button
                    type="button"
                    data-create-section="explorar"
                    @disabled($espaciosExplorar->count() >= $maxExplorar)
                    class="js-crear-espacio rounded-xl bg-sidan-500 px-4 py-3 text-sm font-black text-white transition hover:bg-green-600 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    + Agregar espacio
                </button>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($espaciosExplorar as $espacio)
                    @include('admin.welcome.partials.espacio-dinamico', ['espacio' => $espacio])
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-7 text-center dark:border-white/10 dark:bg-white/[0.03]">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">No hay actividades seleccionadas para esta sección.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

    <div id="welcomeActivityModal" class="fixed inset-0 z-[2147482000] hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
        <div class="flex max-h-[88vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-sidan-950">
            <div class="flex items-center justify-between gap-4 border-b border-slate-200 px-5 py-4 dark:border-white/10 sm:px-6">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.15em] text-sidan-500">Seleccionar actividad</p>
                    <h3 id="welcomeModalTitle" class="mt-1 text-xl font-black text-sidan-900 dark:text-white">Cambiar actividad</h3>
                </div>
                <button id="welcomeModalClose" type="button" class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 text-xl font-black text-slate-500 hover:border-red-300 hover:text-red-500 dark:border-white/10">×</button>
            </div>

            <div class="border-b border-slate-200 p-5 dark:border-white/10 sm:p-6">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="m20 20-3.5-3.5" stroke-linecap="round"></path>
                    </svg>
                    <input id="welcomeActivitySearch" type="search" autocomplete="off" placeholder="Busca por nombre o categoría..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3.5 pl-12 pr-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-sidan-500 focus:ring-4 focus:ring-green-500/10 dark:border-white/10 dark:bg-white/5 dark:text-white">
                </div>
                <p class="mt-2 text-xs text-slate-400">Se consultan solo resultados coincidentes; no cargamos todas las actividades del sistema en el navegador.</p>
            </div>

            <div id="welcomeActivityResults" class="min-h-[220px] flex-1 overflow-y-auto p-5 sm:p-6"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById('welcomeAdmin');
        if (!root) return;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const modal = document.getElementById('welcomeActivityModal');
        const closeButton = document.getElementById('welcomeModalClose');
        const searchInput = document.getElementById('welcomeActivitySearch');
        const results = document.getElementById('welcomeActivityResults');
        const modalTitle = document.getElementById('welcomeModalTitle');
        let assignUrl = '';
        let searchTimer = null;
        let searchController = null;

        const toast = (type, message) => {
            if (window.SIDANToast && typeof window.SIDANToast[type] === 'function') {
                window.SIDANToast[type](message);
                return;
            }
            alert(message);
        };

        const request = async (url, method = 'POST', body = null) => {
            let requestMethod = String(method || 'POST').toUpperCase();

            if (
                body instanceof FormData
                && ['PUT', 'PATCH', 'DELETE'].includes(requestMethod)
            ) {
                if (!body.has('_method')) {
                    body.append('_method', requestMethod);
                }
                requestMethod = 'POST';
            }

            const options = {
                method: requestMethod,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
            };

            if (body) {
                options.body = body;
            }

            const response = await fetch(url, options);
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const validation = data.errors
                    ? Object.values(data.errors).flat().find(Boolean)
                    : null;
                throw new Error(validation || data.message || 'No se pudo completar la operación.');
            }

            return data;
        };

        const reloadAfterSuccess = (message) => {
            toast('success', message || 'Cambios guardados.');
            window.setTimeout(() => window.location.reload(), 350);
        };

        const badgeClasses = (estado) => {
            if (estado === 'visible') return 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300';
            if (estado === 'programada') return 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300';
            return 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-300';
        };

        const emptyResults = (message) => {
            results.replaceChildren();
            const box = document.createElement('div');
            box.className = 'rounded-2xl border border-dashed border-slate-300 px-5 py-10 text-center text-sm font-bold text-slate-500 dark:border-white/10 dark:text-slate-400';
            box.textContent = message;
            results.appendChild(box);
        };

        const renderResults = (items) => {
            results.replaceChildren();

            if (!items.length) {
                emptyResults('No encontramos actividades publicadas con esa búsqueda.');
                return;
            }

            const list = document.createElement('div');
            list.className = 'space-y-3';

            items.forEach((item) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'flex w-full items-center gap-4 rounded-2xl border border-slate-200 p-3 text-left transition hover:border-sidan-500 hover:bg-green-50/40 dark:border-white/10 dark:hover:bg-white/5';

                const image = document.createElement('img');
                image.src = item.imagen;
                image.alt = item.nombre;
                image.className = 'h-20 w-24 shrink-0 rounded-xl object-cover';

                const content = document.createElement('div');
                content.className = 'min-w-0 flex-1';

                const meta = document.createElement('div');
                meta.className = 'flex flex-wrap items-center gap-2';

                const category = document.createElement('span');
                category.className = 'text-xs font-black uppercase tracking-wide text-sidan-500';
                category.textContent = item.categoria;

                const status = document.createElement('span');
                status.className = 'rounded-full px-2.5 py-1 text-[11px] font-black ' + badgeClasses(item.estado);
                status.textContent = item.estado_texto;

                meta.append(category, status);

                const title = document.createElement('p');
                title.className = 'mt-1 truncate text-sm font-black text-sidan-900 dark:text-white';
                title.textContent = item.nombre;

                const detail = document.createElement('p');
                detail.className = 'mt-1 text-xs text-slate-500 dark:text-slate-400';
                detail.textContent = item.detalle;

                content.append(meta, title, detail);
                button.append(image, content);

                button.addEventListener('click', async () => {
                    const form = new FormData();
                    form.append('id_actividad', item.id);
                    try {
                        const data = await request(assignUrl, 'PUT', form);
                        reloadAfterSuccess(data.message);
                    } catch (error) {
                        toast('error', error.message);
                    }
                });

                list.appendChild(button);
            });

            results.appendChild(list);
        };

        const loadActivities = async () => {
            const q = searchInput.value.trim();

            if (searchController) searchController.abort();
            searchController = new AbortController();

            results.innerHTML = '<div class="py-10 text-center text-sm font-bold text-slate-400">Buscando actividades…</div>';

            try {
                const url = new URL(root.dataset.searchUrl, window.location.origin);
                if (q) url.searchParams.set('q', q);

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: searchController.signal,
                });

                if (!response.ok) throw new Error('No se pudo buscar actividades.');
                const data = await response.json();
                renderResults(Array.isArray(data.items) ? data.items : []);
            } catch (error) {
                if (error.name === 'AbortError') return;
                emptyResults(error.message);
            }
        };

        const openModal = (button) => {
            assignUrl = button.dataset.assignUrl || '';
            modalTitle.textContent = button.dataset.slotName
                ? 'Asignar a ' + button.dataset.slotName
                : 'Seleccionar actividad';
            searchInput.value = '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            loadActivities();
            window.setTimeout(() => searchInput.focus(), 50);
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            assignUrl = '';
            if (searchController) searchController.abort();
        };

        document.querySelectorAll('.js-toggle-welcome-slot').forEach((button) => {
            button.addEventListener('click', () => {
                const article = button.closest('[data-welcome-slot]');
                const panel = article?.querySelector('.js-welcome-slot-panel');
                const chevron = button.querySelector('.js-slot-chevron');
                if (!article || !panel) return;

                const willOpen = panel.classList.contains('hidden');

                document.querySelectorAll('[data-welcome-slot]').forEach((otherArticle) => {
                    if (otherArticle === article) return;
                    const otherPanel = otherArticle.querySelector('.js-welcome-slot-panel');
                    const otherButton = otherArticle.querySelector('.js-toggle-welcome-slot');
                    const otherChevron = otherArticle.querySelector('.js-slot-chevron');
                    otherPanel?.classList.add('hidden');
                    otherButton?.setAttribute('aria-expanded', 'false');
                    otherChevron?.classList.remove('rotate-180');
                });

                panel.classList.toggle('hidden', !willOpen);
                button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                chevron?.classList.toggle('rotate-180', willOpen);
            });
        });

        document.querySelectorAll('.js-cambiar-actividad').forEach((button) => {
            button.addEventListener('click', () => openModal(button));
        });

        closeButton.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });

        searchInput.addEventListener('input', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(loadActivities, 180);
        });

        document.querySelectorAll('.js-guardar-prioridad').forEach((button) => {
            button.addEventListener('click', async () => {
                const select = document.getElementById(button.dataset.selectId || '');
                if (!select) return;

                const form = new FormData();
                form.append('prioridad', select.value);

                try {
                    const data = await request(button.dataset.url, 'PATCH', form);
                    reloadAfterSuccess(data.message);
                } catch (error) {
                    toast('error', error.message);
                }
            });
        });

        document.querySelectorAll('.js-quitar-destacada').forEach((button) => {
            button.addEventListener('click', async () => {
                if (!confirm('¿Volver a la selección automática de actividad destacada?')) return;

                try {
                    const data = await request(button.dataset.url, 'DELETE');
                    reloadAfterSuccess(data.message);
                } catch (error) {
                    toast('error', error.message);
                }
            });
        });

        document.querySelectorAll('.js-crear-espacio').forEach((button) => {
            button.addEventListener('click', async () => {
                const form = new FormData();
                form.append('seccion', button.dataset.createSection || '');
                try {
                    const data = await request(root.dataset.createUrl, 'POST', form);
                    reloadAfterSuccess(data.message);
                } catch (error) {
                    toast('error', error.message);
                }
            });
        });

        document.querySelectorAll('.js-quitar-actividad').forEach((button) => {
            button.addEventListener('click', async () => {
                if (!confirm('¿Quitar esta actividad del espacio?')) return;
                try {
                    const data = await request(button.dataset.url, 'DELETE');
                    reloadAfterSuccess(data.message);
                } catch (error) {
                    toast('error', error.message);
                }
            });
        });

        document.querySelectorAll('.js-mover-espacio').forEach((button) => {
            button.addEventListener('click', async () => {
                const form = new FormData();
                form.append('direccion', button.dataset.direction || '');
                try {
                    const data = await request(button.dataset.url, 'PATCH', form);
                    reloadAfterSuccess(data.message);
                } catch (error) {
                    toast('error', error.message);
                }
            });
        });

        document.querySelectorAll('.js-eliminar-espacio').forEach((button) => {
            button.addEventListener('click', async () => {
                if (!confirm('¿Eliminar este espacio del Welcome?')) return;
                try {
                    const data = await request(button.dataset.url, 'DELETE');
                    reloadAfterSuccess(data.message);
                } catch (error) {
                    toast('error', error.message);
                }
            });
        });
    });
</script>
@endsection
