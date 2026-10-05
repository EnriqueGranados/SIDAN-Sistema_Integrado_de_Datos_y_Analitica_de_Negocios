@extends('layouts.navbars')

@section('title', 'Nuevo recurso')

@section('content')
    <div class="min-h-screen bg-[#f6f8fb] text-slate-900 transition-colors duration-200 dark:bg-transparent dark:text-white">
        <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('admin.recursos.index') }}"
                    class="mb-4 inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver a recursos
                </a>

                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                    Nuevo recurso
                </h1>

                <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Registra un equipo, mobiliario, infraestructura u otro elemento que pueda utilizarse
                    en los espacios y actividades.
                </p>
            </div>

            <div
                class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a]">
                <button id="btnAyuda" type="button"
                    class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-slate-50 dark:hover:bg-white/[0.03]"
                    aria-expanded="false" aria-controls="contenidoAyuda">

                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                ¿Qué debo registrar como recurso?
                            </p>

                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                Consulta algunos ejemplos y cómo clasificarlos.
                            </p>
                        </div>
                    </div>

                    <svg id="iconoAyuda" class="h-5 w-5 shrink-0 text-slate-400 transition-transform dark:text-slate-500"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="contenidoAyuda" class="hidden border-t border-slate-200 px-5 py-5 dark:border-white/5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-white/[0.02]">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                Recursos del espacio
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                Son elementos que normalmente forman parte de un lugar, como
                                sillas, tomacorrientes, pantalla de proyección o sistema de sonido.
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-4 dark:bg-white/[0.02]">
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                Recursos móviles
                            </p>

                            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-400">
                                Pueden trasladarse y utilizarse en diferentes lugares, como
                                proyectores portátiles, micrófonos, canopies o roll-ups.
                            </p>
                        </div>
                    </div>

                    <p class="mt-4 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        Aquí solo estás creando el recurso. Más adelante podrás indicar qué recursos
                        están disponibles en cada espacio.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.recursos.store') }}">
                @csrf

                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-colors dark:border-white/5 dark:bg-[#0f172a] dark:shadow-black/10">
                    <div class="border-b border-slate-200 px-5 py-4 sm:px-6 dark:border-white/5">
                        <h2 class="font-semibold text-slate-900 dark:text-white">
                            Información del recurso
                        </h2>

                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            Ingresa la información que permitirá identificarlo y utilizarlo posteriormente.
                        </p>
                    </div>

                    <div class="space-y-6 p-5 sm:p-6">
                        <div>
                            <label for="nombre" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Nombre del recurso
                                <span class="text-red-600 dark:text-red-400">*</span>
                            </label>

                            <input id="nombre" name="nombre" type="text" maxlength="120" required autofocus
                                value="{{ old('nombre') }}" placeholder="Ej. Proyector multimedia"
                                class="w-full rounded-xl border bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-white/10
                                {{ $errors->has('nombre')
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                    : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-white/10' }}">

                            @error('nombre')
                                <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="descripcion"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Descripción
                            </label>

                            <textarea id="descripcion" name="descripcion" rows="3" maxlength="1000"
                                placeholder="Describe brevemente el recurso si es necesario..."
                                class="w-full resize-y rounded-xl border bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-white/10
                                {{ $errors->has('descripcion')
                                    ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                    : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-white/10' }}">{{ old('descripcion') }}</textarea>

                            @error('descripcion')
                                <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="categoria"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Categoría
                            </label>

                            <div class="relative" id="contenedorCategoria">
                                <input id="categoria" name="categoria" type="text" maxlength="80"
                                    value="{{ old('categoria') }}" placeholder="Ej. Audiovisual" autocomplete="off"
                                    class="w-full rounded-xl border bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 dark:bg-white/5 dark:text-white dark:placeholder:text-slate-500 dark:focus:bg-white/10
                                    {{ $errors->has('categoria')
                                        ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20'
                                        : 'border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-white/10' }}">

                                <div id="sugerenciasCategoria"
                                    class="absolute left-0 right-0 top-full z-50 mt-2 hidden max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">
                                </div>
                            </div>

                            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                Agrupa recursos similares. Por ejemplo: Audiovisual, Audio, Mobiliario,
                                Tecnología o Conectividad. Empieza a escribir para ver opciones.
                            </p>

                            @error('categoria')
                                <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="unidad_medida"
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Forma de medir la cantidad
                                <span class="text-red-600 dark:text-red-400">*</span>
                            </label>

                            <select id="unidad_medida" name="unidad_medida" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:bg-white/10 dark:[color-scheme:dark]">
                                <option value="unidad" @selected(old('unidad_medida', 'unidad') === 'unidad')>
                                    Unidades
                                </option>

                                <option value="par" @selected(old('unidad_medida') === 'par')>
                                    Pares
                                </option>

                                <option value="juego" @selected(old('unidad_medida') === 'juego')>
                                    Juegos / conjuntos
                                </option>

                                <option value="metro" @selected(old('unidad_medida') === 'metro')>
                                    Metros
                                </option>
                            </select>

                            <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                Indica cómo se expresará la cantidad disponible. Para proyectores,
                                micrófonos, sillas, mesas y similares utiliza “Unidades”.
                            </p>

                            @error('unidad_medida')
                                <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="border-t border-slate-200 pt-6 dark:border-white/5">
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                ¿Este recurso puede trasladarse entre diferentes espacios?
                            </p>

                            <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">
                                Esto permitirá distinguir los recursos que pertenecen normalmente a un
                                espacio de aquellos que pueden utilizarse en distintos lugares.
                            </p>

                            <label
                                class="mt-4 flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-white/20">
                                <input type="checkbox" name="es_movil" value="1" @checked(old('es_movil', '1') == '1')
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 bg-white text-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900">

                                <div>
                                    <span class="block text-sm font-medium text-slate-900 dark:text-white">
                                        Sí, es un recurso móvil
                                    </span>

                                    <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                                        Puede trasladarse y utilizarse en diferentes espacios o actividades.
                                    </span>
                                </div>
                            </label>
                        </div>

                        <div class="border-t border-slate-200 pt-6 dark:border-white/5">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" name="activo" value="1" @checked(old('activo', '1') == '1')
                                    class="mt-0.5 h-4 w-4 rounded border-slate-300 bg-white text-emerald-500 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-900">

                                <div>
                                    <span class="block text-sm font-medium text-slate-900 dark:text-white">
                                        Recurso activo
                                    </span>

                                    <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-400">
                                        Los recursos activos estarán disponibles para nuevas configuraciones.
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50/70 px-5 py-4 sm:flex-row sm:justify-end sm:px-6 dark:border-white/5 dark:bg-white/[0.02]">
                        <a href="{{ route('admin.recursos.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Guardar recurso
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script id="categorias-data" type="application/json">
{!! json_encode($categorias->values()->all(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>

    <script>
        function showAppNotification(type, title, message) {
            const previous = document.getElementById('app-notification');
            if (previous) previous.remove();

            const styles = {
                success: {
                    iconColor: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
                    borderColor: 'border-emerald-200 dark:border-emerald-500/20',
                    progressColor: 'bg-emerald-500',
                    progressBackground: 'bg-emerald-100 dark:bg-emerald-950/50',
                    iconPath: 'M5 13l4 4L19 7'
                },
                error: {
                    iconColor: 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
                    borderColor: 'border-red-200 dark:border-red-500/20',
                    progressColor: 'bg-red-500',
                    progressBackground: 'bg-red-100 dark:bg-red-950/50',
                    iconPath: 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z'
                },
                warning: {
                    iconColor: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
                    borderColor: 'border-amber-200 dark:border-amber-500/20',
                    progressColor: 'bg-amber-500',
                    progressBackground: 'bg-amber-100 dark:bg-amber-950/50',
                    iconPath: 'M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3Z'
                },
                info: {
                    iconColor: 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
                    borderColor: 'border-blue-200 dark:border-blue-500/20',
                    progressColor: 'bg-blue-500',
                    progressBackground: 'bg-blue-100 dark:bg-blue-950/50',
                    iconPath: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                }
            };

            const style = styles[type] || styles.info;
            const notification = document.createElement('div');

            notification.id = 'app-notification';
            notification.className =
                'fixed right-4 bottom-4 sm:right-6 sm:bottom-6 z-[150] w-[calc(100%-2rem)] max-w-sm translate-y-6 opacity-0 transition-all duration-300 ease-out';

            notification.innerHTML = `
        <div class="relative overflow-hidden rounded-2xl border ${style.borderColor} bg-white shadow-2xl dark:bg-[#0f172a]">
            <div class="flex items-start gap-3 p-4 pr-12">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${style.iconColor}">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${style.iconPath}"></path>
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <p class="notification-title text-sm font-black text-slate-900 dark:text-white"></p>
                    <p class="notification-message mt-1 whitespace-pre-line text-sm leading-5 text-slate-600 dark:text-slate-400"></p>
                </div>
            </div>

            <button type="button"
                class="notification-close absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-white">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div class="h-1 w-full ${style.progressBackground}">
                <div class="notification-progress h-full w-full origin-left ${style.progressColor}"></div>
            </div>
        </div>
    `;

            notification.querySelector('.notification-title').textContent = title;
            notification.querySelector('.notification-message').textContent = message;

            document.body.appendChild(notification);

            const progress = notification.querySelector('.notification-progress');
            const closeButton = notification.querySelector('.notification-close');

            let timeout;

            const closeNotification = () => {
                clearTimeout(timeout);

                notification.classList.remove('opacity-100', 'translate-y-0');
                notification.classList.add('opacity-0', 'translate-y-6');

                setTimeout(() => {
                    notification.remove();
                }, 300);
            };

            closeButton.addEventListener('click', closeNotification);

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    notification.classList.remove('opacity-0', 'translate-y-6');
                    notification.classList.add('opacity-100', 'translate-y-0');

                    progress.style.transition = 'transform 5s linear';
                    progress.style.transform = 'scaleX(0)';
                });
            });

            timeout = setTimeout(closeNotification, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->any())
                showAppNotification(
                    'warning',
                    'Revisa los datos del recurso',
                    @json(collect($errors->all())->implode("\n"))
                );
            @endif

            @if (session('error'))
                showAppNotification(
                    'error',
                    'No se pudo crear el recurso',
                    @json(session('error'))
                );
            @endif

            const boton = document.getElementById('btnAyuda');
            const contenido = document.getElementById('contenidoAyuda');
            const icono = document.getElementById('iconoAyuda');

            const categoria = document.getElementById('categoria');
            const contenedorCategoria = document.getElementById('contenedorCategoria');
            const sugerenciasCategoria = document.getElementById('sugerenciasCategoria');
            const categoriasData = document.getElementById('categorias-data');

            let categoriasDisponibles = [];

            try {
                categoriasDisponibles = JSON.parse(categoriasData.textContent);
            } catch (error) {
                console.error('No se pudieron cargar las categorías.', error);
            }

            if (boton && contenido && icono) {
                boton.addEventListener('click', function() {
                    const abierto = !contenido.classList.contains('hidden');

                    contenido.classList.toggle('hidden');
                    icono.classList.toggle('rotate-180');

                    boton.setAttribute(
                        'aria-expanded',
                        abierto ? 'false' : 'true'
                    );
                });
            }

            function normalizar(texto) {
                return String(texto)
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();
            }

            function cerrarSugerencias() {
                sugerenciasCategoria.innerHTML = '';
                sugerenciasCategoria.classList.add('hidden');
            }

            function seleccionarCategoria(nombre) {
                categoria.value = nombre;
                cerrarSugerencias();
                categoria.focus();
            }

            function buscarCategorias() {
                const valor = categoria.value.trim();

                sugerenciasCategoria.innerHTML = '';

                if (valor === '') {
                    cerrarSugerencias();
                    return;
                }

                const busqueda = normalizar(valor);

                const coincidencias = categoriasDisponibles
                    .filter(function(nombre) {
                        return normalizar(nombre).includes(busqueda);
                    })
                    .slice(0, 8);

                if (coincidencias.length === 0) {
                    cerrarSugerencias();
                    return;
                }

                coincidencias.forEach(function(nombre) {
                    const botonOpcion = document.createElement('button');

                    botonOpcion.type = 'button';

                    botonOpcion.className =
                        'flex w-full items-center justify-between gap-3 rounded-lg px-3 py-2.5 text-left transition hover:bg-slate-100 dark:hover:bg-white/5';

                    const bloqueTexto = document.createElement('div');

                    const nombreCategoria = document.createElement('p');
                    nombreCategoria.className =
                        'text-sm font-medium text-slate-800 dark:text-slate-200';
                    nombreCategoria.textContent = nombre;

                    const descripcion = document.createElement('p');
                    descripcion.className =
                        'mt-0.5 text-xs text-slate-500';
                    descripcion.textContent = 'Categoría existente';

                    bloqueTexto.appendChild(nombreCategoria);
                    bloqueTexto.appendChild(descripcion);

                    const usar = document.createElement('span');
                    usar.className =
                        'shrink-0 rounded-lg bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400';
                    usar.textContent = 'Usar';

                    botonOpcion.appendChild(bloqueTexto);
                    botonOpcion.appendChild(usar);

                    botonOpcion.addEventListener('mousedown', function(event) {
                        event.preventDefault();
                        seleccionarCategoria(nombre);
                    });

                    sugerenciasCategoria.appendChild(botonOpcion);
                });

                sugerenciasCategoria.classList.remove('hidden');
            }

            categoria.addEventListener('input', buscarCategorias);

            categoria.addEventListener('focus', function() {
                if (categoria.value.trim() !== '') {
                    buscarCategorias();
                }
            });

            document.addEventListener('mousedown', function(event) {
                if (!contenedorCategoria.contains(event.target)) {
                    cerrarSugerencias();
                }
            });
        });
    </script>
@endsection
