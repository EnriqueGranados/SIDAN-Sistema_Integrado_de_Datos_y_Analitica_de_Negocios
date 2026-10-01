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

        /*
        |--------------------------------------------------------------------------
        | Búsqueda
        |--------------------------------------------------------------------------
        */
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

        /*
        |--------------------------------------------------------------------------
        | Obtener actividades
        |--------------------------------------------------------------------------
        */
        $actividades = $query
            ->paginate(15)
            ->withQueryString();

        
        foreach ($actividades as $actividad) {

            $actividad->configuracion_completa = false;

            try {

                $documento = ActivityContent::where(
                    'id_actividad_pg',
                    (int) $actividad->id_actividad
                )->first();

                if ($documento) {

                    $configuracionSesiones =
                        $documento->session_configuration ?? [];

                    if (is_object($configuracionSesiones)) {
                        $configuracionSesiones =
                            (array) $configuracionSesiones;
                    }

                    $actividad->configuracion_completa =
                        (bool) (
                            $configuracionSesiones['configured']
                            ?? false
                        );
                }

            } catch (\Throwable $e) {

                report($e);

                $actividad->configuracion_completa = false;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Categorías para filtros
        |--------------------------------------------------------------------------
        */
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
        $categorias = Categoria::where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $etiquetas = Etiqueta::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('admin.actividades.crear', compact('categorias', 'etiquetas'));
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

        $contenidoFlexible = \App\Models\Mongo\ActivityContent::where(
            'id_actividad_pg',
            $actividad->id_actividad
        )->first();

        return view('admin.actividades.configurar', compact(
            'actividad',
            'contenidoFlexible'
        ));
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
        public function actualizarItem(Request $request, Actividad $actividad, ItemActividad $item) {
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

    public function eliminarItem(
        Actividad $actividad,
        ItemActividad $item
    ) {
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

                $actividad->slug =
                    $this->generarSlugUnico(
                        $validated['nombre']
                    );

                $actividad->estado_publicacion =
                    'borrador';

                $actividad->estado_operativo =
                    'normal';

                $actividad->revision_actual =
                    1;

                $actividad->creado_por =
                    Auth::id();

                $actividad->actualizado_por =
                    Auth::id();

                $actividad->save();

                $actividad->etiquetas()->sync(
                    $request->input(
                        'etiquetas',
                        []
                    )
                );

                if ($request->hasFile('portada')) {
                    $rutaNueva =
                        $request->file('portada')->store(
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
                        'texto_alternativo' =>
                            $validated['texto_alternativo_portada']
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
                    $actividad
                )
                ->with(
                    'success',
                    'La información inicial fue guardada. Ahora completa la configuración necesaria antes de enviar la actividad a revisión.'
                );
        } catch (\Throwable $e) {
            if ($rutaNueva) {
                Storage::disk('public')
                    ->delete($rutaNueva);
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
        if (!in_array($actividad->estado_publicacion, ['borrador', 'cambios_solicitados'])) return redirect()
            ->route('admin.actividades.index')
            ->with('error', 'Esta actividad no puede editarse en su estado actual.');

        $categorias = Categoria::where(
            'activo',
            true
        )
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $etiquetas = Etiqueta::where(
            'activo',
            true
        )
            ->orderBy('nombre')
            ->get();

        $actividad->load([
            'etiquetas',
            'portada',
        ]);

        return view(
            'admin.actividades.editar',
            compact(
                'actividad',
                'categorias',
                'etiquetas'
            )
        );
    }

    public function update(Request $request, Actividad $actividad) {
        if (!in_array($actividad->estado_publicacion, ['borrador', 'cambios_solicitados'])) return redirect()
                    ->route('admin.actividades.index')
                    ->with('error', 'Esta actividad no puede editarse en su estado actual.');

        $validated =
            $this->validarActividad($request);

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
                $nombreAnterior =
                    $actividad->nombre;

                $this->asignarDatos(
                    $actividad,
                    $validated
                );

                if (
                    $nombreAnterior
                    !== $validated['nombre']
                ) {
                    $actividad->slug =
                        $this->generarSlugUnico(
                            $validated['nombre'],
                            $actividad->id_actividad
                        );
                }

                $actividad->actualizado_por =
                    Auth::id();

                $actividad->save();

                $actividad->etiquetas()->sync(
                    $request->input(
                        'etiquetas',
                        []
                    )
                );

                $portadaAnterior =
                    $actividad->medios()
                        ->where(
                            'es_portada',
                            true
                        )
                        ->first();

                $debeEliminarPortada =
                    $request->boolean(
                        'eliminar_portada'
                    )
                    || $request->hasFile(
                        'portada'
                    );

                if (
                    $portadaAnterior
                    && $debeEliminarPortada
                ) {
                    $rutaAnterior =
                        $portadaAnterior->url;

                    $portadaAnterior->delete();
                }

                if ($request->hasFile('portada')) {
                    $rutaNueva =
                        $request->file('portada')->store(
                            "actividades/{$actividad->id_actividad}",
                            'public'
                        );

                    if (!$rutaNueva) {
                        throw new \RuntimeException(
                            'No se pudo almacenar la nueva imagen de portada.'
                        );
                    }

                    $actividad->medios()->create([
                        'tipo' => 'imagen',
                        'url' => $rutaNueva,
                        'texto_alternativo' =>
                            $validated['texto_alternativo_portada']
                            ?? $actividad->nombre,
                        'es_portada' => true,
                        'orden' => 0,
                    ]);
                } elseif (
                    $portadaAnterior
                    && !$debeEliminarPortada
                ) {
                    $portadaAnterior->texto_alternativo =
                        $validated['texto_alternativo_portada']
                        ?? $portadaAnterior->texto_alternativo;

                    $portadaAnterior->save();
                }
            });

            if ($rutaAnterior) {
                Storage::disk('public')
                    ->delete($rutaAnterior);
            }

            return redirect()
                ->route(
                    'admin.actividades.index'
                )
                ->with(
                    'success',
                    'Actividad actualizada correctamente.'
                );
        } catch (\Throwable $e) {
            if ($rutaNueva) {
                Storage::disk('public')
                    ->delete($rutaNueva);
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
        if (!in_array($actividad->estado_publicacion, ['borrador', 'cambios_solicitados'], true)) {
            return redirect()->route('admin.actividades.index')->with('error', 'Esta actividad no puede enviarse a revisión en su estado actual.');
        }

        try {
            $documento = ActivityContent::where(
                'id_actividad_pg',
                (int) $actividad->id_actividad
            )->first();

            $configuracionSesiones = $documento?->session_configuration ?? [];

            if (is_object($configuracionSesiones)) {
                $configuracionSesiones = (array) $configuracionSesiones;
            }

            $configuracionCompleta = (bool) (
                $configuracionSesiones['configured'] ?? false
            );

            if (!$configuracionCompleta) {
                return redirect()
                    ->route('admin.actividades.index')
                    ->with('error', 'Debes completar la configuración antes de enviar la actividad a revisión.');
            }

            $esReenvio = $actividad->estado_publicacion === 'cambios_solicitados';
            DB::transaction(function () use ($actividad, $esReenvio) {
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
                    'accion' => $esReenvio ? 'reenviada_revision' : 'enviada_revision',
                    'observacion' => $esReenvio ? 'Actividad reenviada después de realizar los cambios solicitados.' : 'Actividad enviada a revisión.',
                    'creado_en' => now(),
                ]);
            });

            return redirect()
                ->route('admin.actividades.index')
                ->with('success', 'Actividad enviada a revisión correctamente.');

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.actividades.index')
                ->with('error', 'No se pudo enviar la actividad a revisión.');
        }
    }

    public function retirarRevision(Actividad $actividad)
    {
        if ($actividad->estado_publicacion !== 'pendiente_revision') return redirect()->route('admin.actividades.index')->with('error', 'Esta actividad no está pendiente de revisión.');
        try {
            DB::transaction(function () use ($actividad) {
                $actividad->update(['estado_publicacion' => 'borrador', 'actualizado_por' => Auth::id()]);
                DB::table('tbl_revisiones_actividad')->insert([
                    'id_actividad' => $actividad->id_actividad,
                    'numero_revision' => $actividad->revision_actual,
                    'id_usuario' => Auth::id(),
                    'accion' => 'revision_retirada',
                    'observacion' => 'Solicitud de revisión retirada por el creador.',
                    'creado_en' => now(),
                ]);
            });
            return redirect()->route('admin.actividades.index')->with('success', 'La actividad fue retirada de revisión y puede modificarse nuevamente.');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('admin.actividades.index')->with('error', 'No se pudo retirar la actividad de revisión.');
        }
    }

    public function destroy(Actividad $actividad)
    {
        if ($actividad->estado_publicacion !== 'borrador') {
            return back()->with('error', 'Solo pueden eliminarse actividades en estado borrador.');
        }

        try {
            $actividad->delete();
            return redirect()->route('admin.actividades.index')->with('success', 'Actividad eliminada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo eliminar la actividad.');
        }
    }

    private function validarActividad(
        Request $request
    ): array {
        return $request->validate([
            'id_categoria' => [
                'nullable',
                'integer',
                'exists:tbl_categorias,id_categoria',
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
    }

    private function asignarDatos(
        Actividad $actividad,
        array $validated
    ): void {
        $actividad->id_categoria =
            $validated['id_categoria']
            ?? null;

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

    private function generarSlugUnico(
        string $nombre,
        ?int $ignorarId = null
    ): string {
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
            $slug =
                "{$base}-{$contador}";

            $contador++;
        }

        return $slug;
    }
}