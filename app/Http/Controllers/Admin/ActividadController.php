<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Mongo\ActivityContent;
use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\ItemActividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\Espacio;


class ActividadController extends Controller
{
    public function index(Request $request)
    {
        $query = Actividad::query()
            ->with([
                'categoria',
                'creador',
                'actualizador',
            ])
            ->orderByDesc('created_at');

        if ($request->filled('buscar')) {
            $buscar = trim($request->buscar);

            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'ILIKE', "%{$buscar}%")
                    ->orWhere('resumen', 'ILIKE', "%{$buscar}%")
                    ->orWhere('slug', 'ILIKE', "%{$buscar}%");
            });
        }

        if ($request->filled('estado_publicacion')) {
            $query->where('estado_publicacion', $request->estado_publicacion);
        }

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        $actividades = $query
            ->paginate(15)
            ->withQueryString();

        $idsActividades = $actividades
            ->getCollection()
            ->pluck('id_actividad')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $configuraciones = collect();

        if (!empty($idsActividades)) {
            try {
                $configuraciones = ActivityContent::whereIn(
                    'id_actividad_pg',
                    $idsActividades
                )
                    ->get()
                    ->keyBy(
                        fn ($documento) =>
                            (int) $documento->id_actividad_pg
                    );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        foreach ($actividades as $actividad) {
            $actividad->configuracion_completa = false;

            $documento = $configuraciones->get(
                (int) $actividad->id_actividad
            );

            if (!$documento) {
                continue;
            }

            $configuracion = $this->normalizarMongo(
                $documento->configuration ?? []
            );

            $actividad->configuracion_completa = (bool) (
                $configuracion['configured'] ?? false
            );
        }

        $categorias = Categoria::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.actividades.listado',
            compact(
                'actividades',
                'categorias'
            )
        );
    }

    public function create()
    {
        $categorias = Categoria::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $etiquetas = Etiqueta::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        $espacios = Espacio::query()
            ->where('activo', true)
            ->with('contenedor')
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.actividades.crear',
            compact(
                'categorias',
                'etiquetas',
                'espacios'
            )
        );
    }

    public function configurar(Actividad $actividad)
    {
        $actividad->load([
            'categoria',
            'etiquetas',
            'medios',
            'items.variantes',
            'items.imagenPrincipal',
            'sesiones',
            'formularios.campos',
            'recursos',
            'promociones.items',
        ]);

        $contenidoFlexible = ActivityContent::where(
            'id_actividad_pg',
            $actividad->id_actividad
        )->first();

        return view(
            'admin.actividades.configurar',
            compact(
                'actividad',
                'contenidoFlexible'
            )
        );
    }

    private function validarItem(Request $request): array
    {
        return $request->validate([
            'item_nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'item_descripcion' => [
                'nullable',
                'string',
            ],
            'item_tipo' => [
                'required',
                Rule::in([
                    'producto',
                    'servicio',
                    'acceso',
                    'donacion',
                    'reserva',
                    'otro',
                ]),
            ],
            'item_precio' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],
            'item_costo_referencia' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],
            'item_stock_total' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'item_venta_desde' => [
                'nullable',
                'date',
            ],
            'item_venta_hasta' => [
                'nullable',
                'date',
                'after_or_equal:item_venta_desde',
            ],
            'item_min_por_inscripcion' => [
                'required',
                'integer',
                'min:1',
            ],
            'item_max_por_inscripcion' => [
                'nullable',
                'integer',
                'min:1',
                'gte:item_min_por_inscripcion',
            ],
            'item_requiere_participante' => [
                'nullable',
                'boolean',
            ],
            'item_imagen' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'eliminar_item_imagen' => [
                'nullable',
                'boolean',
            ],
        ]);
    }
        public function guardarItem(Request $request, Actividad $actividad)
    {
        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados'],
            true
        )) {
            return back()->with(
                'error',
                'Esta actividad ya no puede modificar su configuración comercial en su estado actual.'
            );
        }

        $validated = $this->validarItem($request);
        $rutaImagenNueva = null;

        try {
            DB::transaction(function () use (
                $request,
                $actividad,
                $validated,
                &$rutaImagenNueva
            ) {
                $orden = ($actividad->items()->max('orden') ?? -1) + 1;

                $item = $actividad->items()->create([
                    'nombre' => $validated['item_nombre'],
                    'descripcion' => $validated['item_descripcion'] ?? null,
                    'tipo' => $validated['item_tipo'],
                    'precio' => $validated['item_precio'],
                    'costo_referencia' => $validated['item_costo_referencia'],
                    'stock_total' => $validated['item_stock_total'] ?? null,
                    'venta_desde' => $validated['item_venta_desde'] ?? null,
                    'venta_hasta' => $validated['item_venta_hasta'] ?? null,
                    'min_por_inscripcion' => $validated['item_min_por_inscripcion'],
                    'max_por_inscripcion' => $validated['item_max_por_inscripcion'] ?? null,
                    'requiere_participante' => (bool) ($validated['item_requiere_participante'] ?? false),
                    'orden' => $orden,
                    'activo' => true,
                ]);

                if ($request->hasFile('item_imagen')) {
                    $rutaImagenNueva = $request->file('item_imagen')->store(
                        "actividades/{$actividad->id_actividad}/items/{$item->id_item_actividad}",
                        'public'
                    );

                    if (!$rutaImagenNueva) {
                        throw new \RuntimeException(
                            'No se pudo almacenar la imagen del producto o servicio.'
                        );
                    }

                    $item->medios()->create([
                        'id_actividad' => $actividad->id_actividad,
                        'id_sesion' => null,
                        'tipo' => 'imagen',
                        'url' => $rutaImagenNueva,
                        'texto_alternativo' => $item->nombre,
                        'es_portada' => true,
                        'orden' => 0,
                    ]);
                }

                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                $this->invalidarConfiguracionGeneral($actividad);
            });

            return back()->with(
                'success',
                'Producto o servicio agregado correctamente.'
            );
        } catch (\Throwable $e) {
            if ($rutaImagenNueva) {
                Storage::disk('public')->delete($rutaImagenNueva);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No se pudo agregar el producto o servicio.'
                );
        }
    }

    public function actualizarItem( Request $request, Actividad $actividad, ItemActividad $item) {
        abort_unless(
            $item->id_actividad === $actividad->id_actividad,
            404
        );

        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados'],
            true
        )) {
            return back()->with(
                'error',
                'Esta actividad ya no puede modificar su configuración comercial en su estado actual.'
            );
        }

        $validated = $this->validarItem($request);
        $rutaImagenNueva = null;
        $rutaImagenAnterior = null;

        try {
            DB::transaction(function () use (
                $request,
                $actividad,
                $item,
                $validated,
                &$rutaImagenNueva,
                &$rutaImagenAnterior
            ) {
                $imagenAnterior = $item->imagenPrincipal()->first();

                $item->update([
                    'nombre' => $validated['item_nombre'],
                    'descripcion' => $validated['item_descripcion'] ?? null,
                    'tipo' => $validated['item_tipo'],
                    'precio' => $validated['item_precio'],
                    'costo_referencia' => $validated['item_costo_referencia'],
                    'stock_total' => $validated['item_stock_total'] ?? null,
                    'venta_desde' => $validated['item_venta_desde'] ?? null,
                    'venta_hasta' => $validated['item_venta_hasta'] ?? null,
                    'min_por_inscripcion' => $validated['item_min_por_inscripcion'],
                    'max_por_inscripcion' => $validated['item_max_por_inscripcion'] ?? null,
                    'requiere_participante' => (bool) ($validated['item_requiere_participante'] ?? false),
                ]);

                $debeEliminarImagen = $request->boolean(
                    'eliminar_item_imagen'
                ) || $request->hasFile('item_imagen');

                if ($imagenAnterior && $debeEliminarImagen) {
                    $rutaImagenAnterior = $imagenAnterior->url;
                    $imagenAnterior->delete();
                }

                if ($request->hasFile('item_imagen')) {
                    $rutaImagenNueva = $request->file('item_imagen')->store(
                        "actividades/{$actividad->id_actividad}/items/{$item->id_item_actividad}",
                        'public'
                    );

                    if (!$rutaImagenNueva) {
                        throw new \RuntimeException(
                            'No se pudo almacenar la nueva imagen del producto o servicio.'
                        );
                    }

                    $item->medios()->create([
                        'id_actividad' => $actividad->id_actividad,
                        'id_sesion' => null,
                        'tipo' => 'imagen',
                        'url' => $rutaImagenNueva,
                        'texto_alternativo' => $item->nombre,
                        'es_portada' => true,
                        'orden' => 0,
                    ]);
                } elseif ($imagenAnterior && !$debeEliminarImagen) {
                    $imagenAnterior->texto_alternativo = $item->nombre;
                    $imagenAnterior->save();
                }

                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                $this->invalidarConfiguracionGeneral($actividad);
            });

            if ($rutaImagenAnterior) {
                Storage::disk('public')->delete($rutaImagenAnterior);
            }

            return back()->with(
                'success',
                'Producto o servicio actualizado correctamente.'
            );
        } catch (\Throwable $e) {
            if ($rutaImagenNueva) {
                Storage::disk('public')->delete($rutaImagenNueva);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No se pudo actualizar el producto o servicio.'
                );
        }
    }

    public function eliminarItem( Actividad $actividad, ItemActividad $item) {
        abort_unless(
            $item->id_actividad === $actividad->id_actividad,
            404
        );

        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados'],
            true
        )) {
            return back()->with(
                'error',
                'Esta actividad ya no puede modificar su configuración comercial en su estado actual.'
            );
        }

        if ($item->variantes()->exists()) {
            return back()->with(
                'error',
                'Primero debes eliminar las variantes asociadas a este elemento.'
            );
        }

        $rutasImagenes = [];

        try {
            DB::transaction(function () use (
                $actividad,
                $item,
                &$rutasImagenes
            ) {
                $medios = $item->medios()->get();

                $rutasImagenes = $medios
                    ->pluck('url')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $item->medios()->delete();
                $item->delete();

                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                $this->invalidarConfiguracionGeneral($actividad);
            });

            foreach ($rutasImagenes as $rutaImagen) {
                Storage::disk('public')->delete($rutaImagen);
            }

            Storage::disk('public')->deleteDirectory(
                "actividades/{$actividad->id_actividad}/items/{$item->id_item_actividad}"
            );

            return back()->with(
                'success',
                'Producto o servicio eliminado correctamente.'
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'No se pudo eliminar el producto o servicio.'
            );
        }
    }
    public function store(Request $request)
    {
        $validated = $this->validarActividad($request);
        $rutaNueva = null;

        try {
            $actividad = DB::transaction(function () use (
                $validated,
                $request,
                &$rutaNueva
            ) {
                $actividad = new Actividad();

                $this->asignarDatos(
                    $actividad,
                    $validated
                );

                $actividad->slug = $this->generarSlugUnico(
                    $validated['nombre']
                );

                $actividad->estado_publicacion = 'borrador';
                $actividad->estado_operativo = 'normal';
                $actividad->revision_actual = 1;
                $actividad->creado_por = Auth::id();
                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                $actividad->etiquetas()->sync(
                    $request->input('etiquetas', [])
                );

                if ($request->hasFile('portada')) {
                    $rutaNueva = $request->file('portada')->store(
                        "actividades/{$actividad->id_actividad}",
                        'public'
                    );

                    if (!$rutaNueva) {
                        throw new \RuntimeException(
                            'No se pudo almacenar la imagen de portada.'
                        );
                    }

                    $actividad->medios()->create([
                        'tipo' => 'imagen',
                        'url' => $rutaNueva,
                        'texto_alternativo' => $validated['texto_alternativo_portada']
                            ?? $actividad->nombre,
                        'es_portada' => true,
                        'orden' => 0,
                    ]);
                }

                return $actividad;
            });

            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' => $actividad,
                        'paso' => 'presentacion',
                    ]
                )
                ->with(
                    'success',
                    'La información inicial fue guardada. Ahora completa la configuración necesaria antes de enviar la actividad a revisión.'
                );
        } catch (\Throwable $e) {
            if ($rutaNueva) {
                Storage::disk('public')->delete($rutaNueva);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No se pudo crear la actividad.'
                );
        }
    }

    public function show(Actividad $actividad)
    {
        $actividad->load([
            'categoria',
            'etiquetas',
            'creador',
            'actualizador',
            'responsables.usuario',
            'medios',
            'formularios.campos',
            'sesiones',
            'revisiones.usuario',
            'items.variantes',
            'items.imagenPrincipal',
            'recursos',
            'promociones.items',
        ]);

        return view(
            'admin.actividades.ver',
            compact('actividad')
        );
    }

    public function edit(Actividad $actividad)
    {
        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados'],
            true
        )) {
            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'error',
                    'Esta actividad no puede editarse en su estado actual.'
                );
        }

        $actividad->load([
            'categoria',
            'espacio.contenedor',
            'etiquetas',
            'portada',
        ]);

        $categorias = Categoria::query()
            ->where(function ($query) use ($actividad) {
                $query->where('activo', true);

                if ($actividad->id_categoria) {
                    $query->orWhere(
                        'id_categoria',
                        $actividad->id_categoria
                    );
                }
            })
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $idsEtiquetasActuales = $actividad->etiquetas
            ->pluck('id_etiqueta')
            ->all();

        $etiquetas = Etiqueta::query()
            ->where(function ($query) use ($idsEtiquetasActuales) {
                $query->where('activo', true);

                if (!empty($idsEtiquetasActuales)) {
                    $query->orWhereIn(
                        'id_etiqueta',
                        $idsEtiquetasActuales
                    );
                }
            })
            ->orderBy('nombre')
            ->get();

        $espacios = Espacio::query()
            ->where(function ($query) use ($actividad) {
                $query->where('activo', true);

                if ($actividad->id_espacio) {
                    $query->orWhere(
                        'id_espacio',
                        $actividad->id_espacio
                    );
                }
            })
            ->with('contenedor')
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.actividades.editar',
            compact(
                'actividad',
                'categorias',
                'etiquetas',
                'espacios'
            )
        );
    }

    public function update( Request $request, Actividad $actividad) {
        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados'],
            true
        )) {
            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'error',
                    'Esta actividad no puede editarse en su estado actual.'
                );
        }

        $validated = $this->validarActividad($request);
        $rutaNueva = null;
        $rutaAnterior = null;

        try {
            DB::transaction(function () use (
                $validated,
                $request,
                $actividad,
                &$rutaNueva,
                &$rutaAnterior
            ) {
                $nombreAnterior = $actividad->nombre;

                $this->asignarDatos(
                    $actividad,
                    $validated
                );

                if ($nombreAnterior !== $validated['nombre']) {
                    $actividad->slug = $this->generarSlugUnico(
                        $validated['nombre'],
                        $actividad->id_actividad
                    );
                }

                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                $actividad->etiquetas()->sync(
                    $request->input('etiquetas', [])
                );

                $portadaAnterior = $actividad->medios()
                    ->whereNull('id_item_actividad')
                    ->whereNull('id_sesion')
                    ->where('es_portada', true)
                    ->first();

                $debeEliminarPortada = $request->boolean(
                    'eliminar_portada'
                ) || $request->hasFile('portada');

                if ($request->hasFile('portada')) {
                    $rutaNueva = $request->file('portada')->store(
                        "actividades/{$actividad->id_actividad}",
                        'public'
                    );

                    if (!$rutaNueva) {
                        throw new \RuntimeException(
                            'No se pudo almacenar la nueva imagen de portada.'
                        );
                    }

                    if ($portadaAnterior) {
                        $rutaAnterior = $portadaAnterior->url;

                        $portadaAnterior->update([
                            'tipo' => 'imagen',
                            'url' => $rutaNueva,
                            'texto_alternativo' => $validated['texto_alternativo_portada']
                                ?? $actividad->nombre,
                            'es_portada' => true,
                            'orden' => 0,
                        ]);
                    } else {
                        $actividad->medios()->create([
                            'tipo' => 'imagen',
                            'url' => $rutaNueva,
                            'texto_alternativo' => $validated['texto_alternativo_portada']
                                ?? $actividad->nombre,
                            'es_portada' => true,
                            'orden' => 0,
                        ]);
                    }
                } elseif (
                    $portadaAnterior
                    && $request->boolean('eliminar_portada')
                ) {
                    $rutaAnterior = $portadaAnterior->url;
                    $portadaAnterior->delete();
                } elseif ($portadaAnterior && !$debeEliminarPortada) {
                    $portadaAnterior->texto_alternativo =
                        $validated['texto_alternativo_portada']
                        ?? $portadaAnterior->texto_alternativo;

                    $portadaAnterior->save();
                }

                $this->invalidarConfiguracionGeneral($actividad);
            });

            if ($rutaAnterior) {
                $rutaStorage = $this->rutaStorageDesdeUrl(
                    $rutaAnterior
                );

                if ($rutaStorage) {
                    Storage::disk('public')->delete($rutaStorage);
                }
            }

            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'success',
                    'Actividad actualizada correctamente. Revisa la configuración antes de volver a enviarla a revisión.'
                );
        } catch (\Throwable $e) {
            if ($rutaNueva) {
                Storage::disk('public')->delete($rutaNueva);
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No se pudo actualizar la actividad.'
                );
        }
    }
    public function enviarRevision(Actividad $actividad)
    {
        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados'],
            true
        )) {
            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'error',
                    'Esta actividad no puede enviarse a revisión en su estado actual.'
                );
        }

        try {
            $documento = ActivityContent::where(
                'id_actividad_pg',
                (int) $actividad->id_actividad
            )->first();

            $configuracion = $this->normalizarMongo(
                $documento?->configuration ?? []
            );

            $configuracionCompleta = (bool) (
                $configuracion['configured'] ?? false
            );

            if (!$configuracionCompleta) {
                return redirect()
                    ->route('admin.actividades.index')
                    ->with(
                        'error',
                        'Debes completar y finalizar la configuración antes de enviar la actividad a revisión.'
                    );
            }

            $esReenvio = $actividad->estado_publicacion === 'cambios_solicitados';

            DB::transaction(function () use (
                $actividad,
                $esReenvio
            ) {
                if ($esReenvio) {
                    $actividad->revision_actual++;
                }

                $actividad->estado_publicacion = 'pendiente_revision';
                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                DB::table('tbl_revisiones_actividad')->insert([
                    'id_actividad' => $actividad->id_actividad,
                    'numero_revision' => $actividad->revision_actual,
                    'id_usuario' => Auth::id(),
                    'accion' => 'enviada_revision',
                    'observacion' => $esReenvio
                        ? 'Actividad reenviada después de realizar los cambios solicitados.'
                        : 'Actividad enviada a revisión.',
                    'creado_en' => now(),
                ]);
            });

            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'success',
                    $esReenvio
                        ? 'Actividad reenviada a revisión correctamente.'
                        : 'Actividad enviada a revisión correctamente.'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'error',
                    'No se pudo enviar la actividad a revisión.'
                );
        }
    }

    public function retirarRevision(Actividad $actividad)
    {
        if ($actividad->estado_publicacion !== 'pendiente_revision') {
            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'error',
                    'Esta actividad ya no está pendiente de revisión.'
                );
        }

        try {
            DB::transaction(function () use ($actividad) {
                $actividad->estado_publicacion = 'borrador';
                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                DB::table('tbl_revisiones_actividad')->insert([
                    'id_actividad' => $actividad->id_actividad,
                    'numero_revision' => $actividad->revision_actual,
                    'id_usuario' => Auth::id(),
                    'accion' => 'retirada',
                    'observacion' => 'Solicitud de revisión retirada por el creador.',
                    'creado_en' => now(),
                ]);
            });

            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'success',
                    'La actividad fue retirada de revisión correctamente.'
                );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'error',
                    'No se pudo retirar la actividad de revisión.'
                );
        }
    }

    public function destroy(Actividad $actividad)
    {
        if ($actividad->estado_publicacion !== 'borrador') {
            return back()->with(
                'error',
                'Solo pueden eliminarse actividades en estado borrador.'
            );
        }

        try {
            $actividad->delete();

            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'success',
                    'Actividad eliminada correctamente.'
                );
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'No se pudo eliminar la actividad.'
            );
        }
    }

    private function validarActividad(Request $request): array
    {
        $validated = $request->validate([
            'id_categoria' => [
                'nullable',
                'integer',
                'exists:tbl_categorias,id_categoria',
            ],
            'id_espacio' => [
                'nullable',
                'integer',
                'exists:tbl_espacios,id_espacio',
            ],
            'ubicacion_externa' => [
                'nullable',
                'string',
                'max:300',
            ],
            'nombre' => [
                'required',
                'string',
                'max:180',
            ],
            'resumen' => [
                'nullable',
                'string',
                'max:300',
            ],
            'descripcion' => [
                'nullable',
                'string',
            ],
            'visibilidad' => [
                'required',
                Rule::in([
                    'publica',
                    'no_listada',
                    'interna',
                ]),
            ],
            'destacada' => [
                'nullable',
                'boolean',
            ],
            'prioridad' => [
                'required',
                'integer',
                Rule::in([
                    25,
                    50,
                    75,
                    100,
                ]),
            ],
            'habilita_inscripcion' => [
                'nullable',
                'boolean',
            ],
            'requiere_cuenta' => [
                'nullable',
                'boolean',
            ],
            'permite_lista_espera' => [
                'nullable',
                'boolean',
            ],
            'cupo_total' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'inscripcion_desde' => [
                'nullable',
                'date',
            ],
            'inscripcion_hasta' => [
                'nullable',
                'date',
                'after_or_equal:inscripcion_desde',
            ],
            'visible_desde' => [
                'nullable',
                'date',
            ],
            'visible_hasta' => [
                'nullable',
                'date',
                'after_or_equal:visible_desde',
            ],
            'realizacion_desde' => [
                'nullable',
                'date',
                'required_with:realizacion_hasta',
            ],
            'realizacion_hasta' => [
                'nullable',
                'date',
                'required_with:realizacion_desde',
                'after_or_equal:realizacion_desde',
            ],
            'etiquetas' => [
                'nullable',
                'array',
            ],
            'etiquetas.*' => [
                'integer',
                'exists:tbl_etiquetas,id_etiqueta',
            ],
            'portada' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'texto_alternativo_portada' => [
                'nullable',
                'string',
                'max:180',
            ],
            'eliminar_portada' => [
                'nullable',
                'boolean',
            ],
        ]);
        if (
                !empty($validated['id_espacio'])
                && !empty($validated['ubicacion_externa'])
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'ubicacion_externa' =>
                        'Selecciona un espacio registrado o escribe una ubicación externa, no ambas opciones.',
                ]);
            }

            if (!empty($validated['id_espacio'])) {
                $espacio = Espacio::query()
                    ->find($validated['id_espacio']);

                if (
                    $espacio
                    && $espacio->capacidad !== null
                    && isset($validated['cupo_total'])
                    && $validated['cupo_total'] !== null
                    && (int) $validated['cupo_total'] > (int) $espacio->capacidad
                ) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'cupo_total' =>
                            "El cupo no puede superar la capacidad del espacio seleccionado ({$espacio->capacidad} personas).",
                    ]);
                }
            }

            return $validated;
    }
    private function asignarDatos( Actividad $actividad, array $validated): void {
        $actividad->id_categoria =
            $validated['id_categoria']
            ?? null;

        $actividad->id_espacio =
            $validated['id_espacio']
            ?? null;

        $actividad->ubicacion_externa =
            !empty($validated['id_espacio'])
                ? null
                : ($validated['ubicacion_externa'] ?? null);

        $actividad->nombre =
            $validated['nombre'];

        $actividad->resumen =
            $validated['resumen']
            ?? null;

        $actividad->descripcion =
            $validated['descripcion']
            ?? null;

        $actividad->visibilidad =
            $validated['visibilidad'];

        $actividad->destacada =
            (bool) (
                $validated['destacada']
                ?? false
            );

        $actividad->prioridad =
            $validated['prioridad']
            ?? 50;

        $actividad->habilita_inscripcion =
            (bool) (
                $validated['habilita_inscripcion']
                ?? false
            );

        $actividad->requiere_cuenta =
            (bool) (
                $validated['requiere_cuenta']
                ?? false
            );

        $actividad->permite_lista_espera =
            (bool) (
                $validated['permite_lista_espera']
                ?? false
            );

        $actividad->cupo_total =
            $validated['cupo_total']
            ?? null;

        $actividad->inscripcion_desde =
            $validated['inscripcion_desde']
            ?? null;

        $actividad->inscripcion_hasta =
            $validated['inscripcion_hasta']
            ?? null;

        $actividad->visible_desde =
            $validated['visible_desde']
            ?? null;

        $actividad->visible_hasta =
            $validated['visible_hasta']
            ?? null;

        $actividad->realizacion_desde =
            $validated['realizacion_desde']
            ?? null;

        $actividad->realizacion_hasta =
            $validated['realizacion_hasta']
            ?? null;
    }

    private function generarSlugUnico( string $nombre, ?int $ignorarId = null): string {
        $base = Str::slug($nombre);

        $base =
            $base !== ''
                ? $base
                : 'actividad';

        $slug = $base;
        $contador = 2;

        while (
            Actividad::withTrashed()
                ->where(
                    'slug',
                    $slug
                )
                ->when(
                    $ignorarId,
                    fn ($query) =>
                        $query->where(
                            'id_actividad',
                            '!=',
                            $ignorarId
                        )
                )
                ->exists()
        ) {
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }

    private function invalidarConfiguracionGeneral(Actividad $actividad): void {
        $documento = ActivityContent::where(
            'id_actividad_pg',
            (int) $actividad->id_actividad
        )->first();

        if (!$documento) {
            return;
        }

        $configuracion = $this->normalizarMongo(
            $documento->configuration ?? []
        );

        if (($configuracion['configured'] ?? false) !== true) {
            return;
        }

        $documento->configuration = [
            'configured' => false,
            'completed_at' => null,
            'updated_at' => now()->toIso8601String(),
            'completed_by' => null,
        ];

        $documento->save();
    }

    private function normalizarMongo(mixed $valor): array
    {
        if ($valor === null) {
            return [];
        }

        if (is_array($valor)) {
            return $valor;
        }

        if ($valor instanceof \Illuminate\Support\Collection) {
            return $valor->toArray();
        }

        if ($valor instanceof \JsonSerializable) {
            $valor = $valor->jsonSerialize();

            return is_array($valor)
                ? $valor
                : (array) $valor;
        }

        if (is_object($valor)) {
            return json_decode(
                json_encode($valor),
                true
            ) ?: [];
        }

        return [];
    }

    private function rutaStorageDesdeUrl(?string $url): ?string {
        if (!$url) {
            return null;
        }

        $ruta = trim($url);

        if ($ruta === '') {
            return null;
        }

        if (
            Str::startsWith(
                $ruta,
                ['http://', 'https://']
            )
        ) {
            $path = parse_url(
                $ruta,
                PHP_URL_PATH
            );

            if (!$path) {
                return null;
            }

            $ruta = $path;
        }

        $ruta = ltrim($ruta, '/');

        if (Str::startsWith($ruta, 'storage/')) {
            $ruta = Str::after(
                $ruta,
                'storage/'
            );
        }

        return $ruta !== ''
            ? $ruta
            : null;
    }
}
