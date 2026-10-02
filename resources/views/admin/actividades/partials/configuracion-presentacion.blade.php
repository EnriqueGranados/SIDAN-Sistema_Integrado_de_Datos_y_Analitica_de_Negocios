@php
    $mediosActividad = $actividad->medios
        ->whereNull('id_item_actividad')
        ->whereNull('id_sesion');

    $portada = $mediosActividad->firstWhere('es_portada', true);
    $galeria = $mediosActividad->where('es_portada', false);
    $urlMedio = function ($url) {
        if (!$url) return '';

        if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        $ruta = ltrim($url, '/');

        if (\Illuminate\Support\Str::startsWith($ruta, 'storage/')) {
            return asset($ruta);
        }

        return asset('storage/' . $ruta);
    };
@endphp

<div class="space-y-8">
    <form method="POST"
          action="{{ route('admin.actividades.presentacion.store', $actividad) }}"
          enctype="multipart/form-data"
          class="space-y-8">
        @csrf

        <div>
            <h3 class="text-lg font-semibold text-white">Imagen de portada</h3>
            <p class="mt-1 text-sm text-slate-400">
                Puedes conservar la portada actual o seleccionar una nueva antes de guardar.
            </p>
        </div>

        @if ($portada)
            <div>
                <p class="mb-2 text-sm font-medium text-slate-300">Portada actual</p>

                <button type="button"
                    class="js-ampliar-imagen group block w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 text-left"
                    data-imagen-url="{{ $urlMedio($portada->url) }}">
                    <img src="{{ $urlMedio($portada->url) }}"
                         alt="{{ $portada->texto_alternativo ?? $actividad->nombre }}"
                         class="h-64 w-full object-cover transition duration-200 group-hover:scale-[1.01] sm:h-72">

                    <div class="flex items-center justify-between px-4 py-3">
                        <span class="text-sm text-slate-400">Haz clic para ampliar</span>
                        <span class="text-xs text-slate-500">Imagen actual</span>
                    </div>
                </button>
            </div>
        @else
            <div class="rounded-xl border border-dashed border-slate-700 bg-slate-900/50 px-5 py-8 text-center">
                <p class="text-sm text-slate-400">Esta actividad todavía no tiene una imagen de portada.</p>
            </div>
        @endif

        <div>
            <label for="portada" class="mb-2 block text-sm font-medium text-slate-300">
                {{ $portada ? 'Reemplazar portada' : 'Seleccionar portada' }}
            </label>

            <input id="portada"
                   name="portada"
                   type="file"
                   accept="image/jpeg,image/png,image/webp"
                   onchange="previsualizarPortada(event)"
                   class="block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-400 hover:file:bg-emerald-500/20">

            @error('portada')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div id="previewPortadaContenedor" class="hidden">
            <div class="mb-2 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-emerald-400">Vista previa de la nueva portada</p>
                    <p id="previewPortadaNombre" class="mt-1 text-xs text-slate-500"></p>
                </div>

                <button type="button"
                        onclick="cancelarNuevaPortada()"
                        class="text-sm font-medium text-red-400 transition hover:text-red-300">
                    Quitar selección
                </button>
            </div>

            <button type="button"
                    onclick="abrirPreviewPortada()"
                    class="group block w-full overflow-hidden rounded-2xl border border-emerald-500/30 bg-slate-900 text-left">
                <img id="previewPortada"
                     src=""
                     alt="Vista previa de nueva portada"
                     class="aspect-video w-full object-cover transition duration-200 group-hover:scale-[1.01]">

                <div class="flex items-center justify-between px-4 py-3">
                    <span class="text-sm text-slate-400">Haz clic para ampliar</span>
                    <span class="text-xs font-medium text-emerald-400">Nueva portada</span>
                </div>
            </button>

            <p class="mt-2 text-xs text-slate-500">
                La portada actual no será reemplazada hasta que guardes los cambios.
            </p>
        </div>

        <div>
            <label for="texto_alternativo" class="mb-2 block text-sm font-medium text-slate-300">
                Descripción de la portada
            </label>

            <input id="texto_alternativo"
                   name="texto_alternativo"
                   type="text"
                   maxlength="255"
                   value="{{ old('texto_alternativo', $portada?->texto_alternativo) }}"
                   placeholder="Describe brevemente la imagen"
                   class="w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-white outline-none transition focus:border-emerald-500">

            @error('texto_alternativo')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="border-t border-slate-800 pt-8">
            <h3 class="text-lg font-semibold text-white">Galería de fotos</h3>
            <p class="mt-1 text-sm text-slate-400">
                Puedes seleccionar varias imágenes al mismo tiempo y revisarlas antes de guardarlas.
            </p>
        </div>

        <div>
            <label for="galeria" class="mb-2 block text-sm font-medium text-slate-300">
                Agregar imágenes
            </label>

            <input id="galeria"
                   name="galeria[]"
                   type="file"
                   multiple
                   accept="image/jpeg,image/png,image/webp"
                   onchange="previsualizarGaleria(event)"
                   class="block w-full rounded-xl border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-500/10 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-400 hover:file:bg-emerald-500/20">

            @error('galeria')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror

            @error('galeria.*')
                <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div id="previewGaleriaContenedor" class="hidden">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-emerald-400">Imágenes seleccionadas</p>
                    <p id="previewGaleriaCantidad" class="mt-1 text-xs text-slate-500"></p>
                </div>

                <button type="button"
                        onclick="cancelarGaleriaSeleccionada()"
                        class="text-sm font-medium text-red-400 transition hover:text-red-300">
                    Quitar selección
                </button>
            </div>

            <div id="previewGaleria" class="grid grid-cols-2 gap-4 md:grid-cols-3"></div>

            <p class="mt-3 text-xs text-slate-500">
                Estas imágenes todavía no han sido guardadas.
            </p>
        </div>

        @if ($galeria->isNotEmpty())
            <div>
                <div class="mb-4">
                    <h4 class="font-semibold text-white">Galería actual</h4>
                    <p class="mt-1 text-sm text-slate-400">
                        {{ $galeria->count() }} {{ $galeria->count() === 1 ? 'imagen guardada' : 'imágenes guardadas' }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                    @foreach ($galeria as $medio)
                        <div class="group block w-full max-w-2xl overflow-hidden rounded-2xl border border-emerald-500/30 bg-slate-900 text-left">
                           <button type="button"
                                class="js-ampliar-imagen group block w-full overflow-hidden"
                                data-imagen-url="{{ $urlMedio($medio->url) }}">
                                <img src="{{ $urlMedio($medio->url) }}"
                                     alt="{{ $medio->texto_alternativo ?? $actividad->nombre }}"
                                     class="h-64 w-full object-cover transition duration-200 group-hover:scale-[1.01] sm:h-72">
                            </button>

                            <div class="space-y-2 p-3">
                                <p class="truncate text-xs text-slate-500">
                                    {{ $medio->texto_alternativo ?? $actividad->nombre }}
                                </p>

                                <div class="flex gap-2">
                                    <button type="button"
                                            onclick="document.getElementById('form-portada-{{ $medio->id_medio }}').submit()"
                                            class="flex-1 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-400 transition hover:bg-emerald-500/20">
                                        Usar como portada
                                    </button>

                                   <button type="button"
                                            class="js-eliminar-medio rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-xs font-semibold text-red-400 transition hover:bg-red-500/20"
                                            data-medio-id="{{ $medio->id_medio }}">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex justify-end border-t border-slate-800 pt-6">
            <button type="submit"
                    class="rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400">
                Guardar presentación y continuar
            </button>
        </div>
    </form>

    @foreach ($galeria as $medio)
        <form id="form-portada-{{ $medio->id_medio }}"
              method="POST"
              action="{{ route('admin.actividades.medios.portada', [$actividad, $medio]) }}"
              class="hidden">
            @csrf
            @method('PATCH')
        </form>

        <form id="form-eliminar-{{ $medio->id_medio }}"
              method="POST"
              action="{{ route('admin.actividades.medios.destroy', [$actividad, $medio]) }}"
              class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</div>

<div id="modalImagen"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4"
     onclick="cerrarImagen(event)">
    <button type="button"
            onclick="cerrarImagen()"
            class="absolute right-5 top-5 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-2xl text-white transition hover:bg-white/20">
        &times;
    </button>

    <img id="modalImagenContenido"
         src=""
         alt="Imagen ampliada"
         class="max-h-[90vh] max-w-[95vw] rounded-xl object-contain shadow-2xl"
         onclick="event.stopPropagation()">
</div>

<script>
let previewPortadaUrl = null;
let previewGaleriaUrls = [];

function abrirImagen(url) {
    const modal = document.getElementById('modalImagen');
    const imagen = document.getElementById('modalImagenContenido');

    imagen.src = url;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function cerrarImagen(event = null) {
    if (event && event.target !== event.currentTarget) {
        return;
    }

    const modal = document.getElementById('modalImagen');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('modalImagenContenido').src = '';
    document.body.style.overflow = '';
}

function previsualizarPortada(event) {
    const archivo = event.target.files[0];
    const contenedor = document.getElementById('previewPortadaContenedor');
    const imagen = document.getElementById('previewPortada');
    const nombre = document.getElementById('previewPortadaNombre');

    if (!archivo) {
        contenedor.classList.add('hidden');
        return;
    }

    if (previewPortadaUrl) {
        URL.revokeObjectURL(previewPortadaUrl);
    }

    previewPortadaUrl = URL.createObjectURL(archivo);
    imagen.src = previewPortadaUrl;
    nombre.textContent = archivo.name;
    contenedor.classList.remove('hidden');
}

function abrirPreviewPortada() {
    if (previewPortadaUrl) {
        abrirImagen(previewPortadaUrl);
    }
}

function cancelarNuevaPortada() {
    const input = document.getElementById('portada');

    input.value = '';

    if (previewPortadaUrl) {
        URL.revokeObjectURL(previewPortadaUrl);
        previewPortadaUrl = null;
    }

    document.getElementById('previewPortada').src = '';
    document.getElementById('previewPortadaNombre').textContent = '';
    document.getElementById('previewPortadaContenedor').classList.add('hidden');
}

function previsualizarGaleria(event) {
    limpiarUrlsGaleria();

    const archivos = Array.from(event.target.files);
    const contenedor = document.getElementById('previewGaleriaContenedor');
    const galeria = document.getElementById('previewGaleria');
    const cantidad = document.getElementById('previewGaleriaCantidad');

    galeria.innerHTML = '';

    if (!archivos.length) {
        contenedor.classList.add('hidden');
        return;
    }

    cantidad.textContent = archivos.length === 1
        ? '1 imagen seleccionada'
        : `${archivos.length} imágenes seleccionadas`;

    archivos.forEach((archivo) => {
        const url = URL.createObjectURL(archivo);

        previewGaleriaUrls.push(url);

        const tarjeta = document.createElement('button');
        tarjeta.type = 'button';
        tarjeta.className = 'group overflow-hidden rounded-xl border border-emerald-500/30 bg-slate-900 text-left';
        tarjeta.addEventListener('click', () => abrirImagen(url));

        const imagen = document.createElement('img');
        imagen.src = url;
        imagen.alt = archivo.name;
        imagen.className = 'aspect-video w-full object-cover transition duration-200 group-hover:scale-[1.02]';

        const pie = document.createElement('div');
        pie.className = 'px-3 py-2';

        const nombre = document.createElement('p');
        nombre.className = 'truncate text-xs text-slate-400';
        nombre.textContent = archivo.name;

        const estado = document.createElement('p');
        estado.className = 'mt-1 text-xs text-emerald-400';
        estado.textContent = 'Pendiente de guardar';

        pie.appendChild(nombre);
        pie.appendChild(estado);
        tarjeta.appendChild(imagen);
        tarjeta.appendChild(pie);
        galeria.appendChild(tarjeta);
    });

    contenedor.classList.remove('hidden');
}

function cancelarGaleriaSeleccionada() {
    document.getElementById('galeria').value = '';
    document.getElementById('previewGaleria').innerHTML = '';
    document.getElementById('previewGaleriaCantidad').textContent = '';
    document.getElementById('previewGaleriaContenedor').classList.add('hidden');
    limpiarUrlsGaleria();
}

function limpiarUrlsGaleria() {
    previewGaleriaUrls.forEach(url => URL.revokeObjectURL(url));
    previewGaleriaUrls = [];
}

function eliminarMedio(id) {
    if (confirm('¿Eliminar esta imagen de la galería?')) {
        document.getElementById(`form-eliminar-${id}`).submit();
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarImagen();
    }
});

document.querySelectorAll('.js-ampliar-imagen').forEach(function (boton) {
    boton.addEventListener('click', function () {
        abrirImagen(this.dataset.imagenUrl);
    });
});

document.querySelectorAll('.js-eliminar-medio').forEach(function (boton) {
    boton.addEventListener('click', function () {
        eliminarMedio(this.dataset.medioId);
    });
});

</script>