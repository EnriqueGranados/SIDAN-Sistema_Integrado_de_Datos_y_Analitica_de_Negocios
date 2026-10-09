<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Mongo\ActivityContent;
use App\Models\Categoria;
use App\Models\Etiqueta;
use App\Models\Espacio;
use App\Models\ItemActividad;
use App\Models\ObservacionRevisionActividad;
use App\Models\RevisionActividad;
use App\Services\ActividadRevisionSnapshotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ActividadController extends Controller
{
    public function __construct(
        private readonly ActividadRevisionSnapshotService $snapshotService
    ) {
    }

    public function index(Request $request)
    {
        $query = Actividad::query()
            ->visiblesPara($request->user())
            ->with([
                'categoria',
                'creador',
                'actualizador',
            ])
            ->withCount([
                'observacionesRevision as correcciones_pendientes_count' =>
                    fn ($query) => $query->where('resuelta', false),

                'observacionesRevision as correcciones_informacion_count' =>
                    fn ($query) => $query
                        ->where('resuelta', false)
                        ->whereIn('seccion', [
                            'informacion_general',
                            'presentacion',
                            'inscripcion',
                        ]),

                'observacionesRevision as correcciones_configuracion_count' =>
                    fn ($query) => $query
                        ->where('resuelta', false)
                        ->whereIn('seccion', [
                            'presentacion',
                            'productos',
                            'datos_solicitados',
                            'precios_costos',
                            'programacion',
                        ]),
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
        $categorias = Categoria::where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $etiquetas = Etiqueta::where('activo', true)
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
            'recursos.recurso',
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

    private function validarItem(Request $request, Actividad $actividad): array
    {
        $validated = $request->validate([
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
        ], [
            'item_nombre.required' => 'Ingresa el nombre del producto o servicio.',
            'item_nombre.max' => 'El nombre no puede superar los 150 caracteres.',
            'item_tipo.required' => 'Selecciona el tipo de elemento.',
            'item_tipo.in' => 'El tipo seleccionado no es válido.',
            'item_precio.required' => 'Ingresa el precio de venta.',
            'item_precio.numeric' => 'El precio debe ser un valor numérico.',
            'item_precio.min' => 'El precio no puede ser negativo.',
            'item_costo_referencia.required' => 'Ingresa el costo de referencia.',
            'item_costo_referencia.numeric' => 'El costo debe ser un valor numérico.',
            'item_costo_referencia.min' => 'El costo no puede ser negativo.',
            'item_stock_total.integer' => 'La cantidad disponible debe ser un número entero.',
            'item_stock_total.min' => 'La cantidad disponible no puede ser negativa.',
            'item_venta_desde.date' => 'La fecha de disponibilidad inicial no es válida.',
            'item_venta_hasta.date' => 'La fecha de disponibilidad final no es válida.',
            'item_min_por_inscripcion.required' => 'Ingresa el mínimo por compra.',
            'item_min_por_inscripcion.integer' => 'El mínimo por compra debe ser un número entero.',
            'item_min_por_inscripcion.min' => 'El mínimo por compra debe ser al menos 1.',
            'item_max_por_inscripcion.integer' => 'El máximo por compra debe ser un número entero.',
            'item_max_por_inscripcion.min' => 'El máximo por compra debe ser al menos 1.',
            'item_max_por_inscripcion.gte' => 'El máximo por compra no puede ser menor que el mínimo.',
            'item_imagen.image' => 'El archivo seleccionado debe ser una imagen.',
            'item_imagen.mimes' => 'La imagen debe ser JPG, JPEG, PNG o WEBP.',
            'item_imagen.max' => 'La imagen no puede superar los 5 MB.',
        ]);

        $zonaHoraria = config('app.timezone');

        $ventaDesde = !empty($validated['item_venta_desde'])
            ? \Illuminate\Support\Carbon::parse(
                $validated['item_venta_desde'],
                $zonaHoraria
            )
            : null;

        $ventaHasta = !empty($validated['item_venta_hasta'])
            ? \Illuminate\Support\Carbon::parse(
                $validated['item_venta_hasta'],
                $zonaHoraria
            )
            : null;

        if ($ventaDesde && $ventaHasta && $ventaHasta->lt($ventaDesde)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'item_venta_hasta' => [
                    'La disponibilidad hasta no puede ser anterior a la disponibilidad desde.',
                ],
            ]);
        }

        return $validated;
    }

    public function guardarItem(Request $request, Actividad $actividad)
    {
        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados', 'aprobada'],
            true
        )) {
            $mensaje = 'Esta actividad ya no puede modificar su configuración comercial en su estado actual.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $mensaje,
                ], 422);
            }

            return back()->with('error', $mensaje);
        }

        $validated = $this->validarItem($request, $actividad);
        $rutaImagenNueva = null;

        try {
            if ($actividad->estado_publicacion === 'aprobada') {
                $this->snapshotService->capturarSiAprobada($actividad);
            }

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

                if ($actividad->estado_publicacion === 'aprobada') {
                    $actividad->estado_publicacion = 'borrador';
                }

                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                $this->invalidarConfiguracionGeneral($actividad);
            });

            $mensaje = 'Producto o servicio agregado correctamente.';

            if ($request->expectsJson()) {
                session()->flash('success', $mensaje);

                return response()->json([
                    'message' => $mensaje,
                ]);
            }

            return back()->with('success', $mensaje);
        } catch (\Throwable $e) {
            if ($rutaImagenNueva) {
                Storage::disk('public')->delete($rutaImagenNueva);
            }

            report($e);

            $mensaje = 'No se pudo agregar el producto o servicio.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $mensaje,
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', $mensaje);
        }
    }

    public function actualizarItem(
        Request $request,
        Actividad $actividad,
        ItemActividad $item
    ) {
        abort_unless(
            $item->id_actividad === $actividad->id_actividad,
            404
        );

        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados', 'aprobada'],
            true
        )) {
            $mensaje = 'Esta actividad ya no puede modificar su configuración comercial en su estado actual.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $mensaje,
                ], 422);
            }

            return back()->with('error', $mensaje);
        }

        $validated = $this->validarItem($request, $actividad);
        $rutaImagenNueva = null;
        $rutaImagenAnterior = null;

        try {
            if ($actividad->estado_publicacion === 'aprobada') {
                $this->snapshotService->capturarSiAprobada($actividad);
            }

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

                if ($actividad->estado_publicacion === 'aprobada') {
                    $actividad->estado_publicacion = 'borrador';
                }

                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                $this->invalidarConfiguracionGeneral($actividad);
            });

            if ($rutaImagenAnterior) {
                Storage::disk('public')->delete($rutaImagenAnterior);
            }

            $mensaje = 'Producto o servicio actualizado correctamente.';

            if ($request->expectsJson()) {
                session()->flash('success', $mensaje);

                return response()->json([
                    'message' => $mensaje,
                ]);
            }

            return back()->with('success', $mensaje);
        } catch (\Throwable $e) {
            if ($rutaImagenNueva) {
                Storage::disk('public')->delete($rutaImagenNueva);
            }

            report($e);

            $mensaje = 'No se pudo actualizar el producto o servicio.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $mensaje,
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', $mensaje);
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
            ['borrador', 'cambios_solicitados', 'aprobada'],
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

        if ($item->promociones()->exists()) {
            $cantidadPromociones = $item->promociones()->count();

            return back()->with(
                'error',
                $cantidadPromociones === 1
                    ? 'Este producto o servicio está vinculado a una promoción. Quita primero esa relación desde la configuración de promociones.'
                    : "Este producto o servicio está vinculado a {$cantidadPromociones} promociones. Quita primero esas relaciones desde la configuración de promociones."
            );
        }

        if ($actividad->estado_publicacion === 'aprobada') {
            $this->snapshotService->capturarSiAprobada($actividad);
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

                if ($actividad->estado_publicacion === 'aprobada') {
                    $actividad->estado_publicacion = 'borrador';
                }

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
            'creador.informacion_personal',
            'actualizador.informacion_personal',
            'responsables.usuario.informacion_personal',
            'espacio.contenedor',
            'medios',
            'formularios.campos',
            'sesiones.espacio.contenedor',
            'sesiones.recursos.recurso',
            'revisiones.usuario.informacion_personal',
            'items.variantes',
            'items.medios',
            'recursos.recurso',
            'promociones.items',
        ]);

        $contenido = ActivityContent::query()
            ->where('id_actividad_pg', (int) $actividad->id_actividad)
            ->first();

        $configuracionProductosGeneral = $this->normalizarMongo(
            $contenido?->product_configuration ?? []
        );

        $configuracionProductos = $this->normalizarListaConfiguraciones(
            $contenido?->product_configuration ?? [],
            ['id_item_pg', 'id_item_actividad', 'item_id'],
            'id_item_pg'
        );

        $camposCompra = $this->normalizarListaConfiguraciones(
            $contenido?->purchase_fields ?? [],
            ['id_item_pg', 'id_item_actividad', 'item_id'],
            'id_item_pg'
        );

        $configuracionesPrecios = $this->indexarConfiguraciones(
            $contenido?->pricing_configuration ?? [],
            ['id_item_pg', 'id_item_actividad', 'item_id']
        );

        $configuracionSesionesGeneral = $this->normalizarMongo(
            $contenido?->session_configuration ?? []
        );

        $configuracionesSesiones = $this->indexarConfiguraciones(
            $contenido?->session_configuration ?? [],
            ['id_sesion_pg', 'id_sesion', 'session_id']
        );

        $configuracionGeneral = $this->normalizarMongo(
            $contenido?->configuration ?? []
        );

        return view(
            'admin.actividades.ver',
            compact(
                'actividad',
                'contenido',
                'configuracionProductosGeneral',
                'configuracionProductos',
                'camposCompra',
                'configuracionesPrecios',
                'configuracionSesionesGeneral',
                'configuracionesSesiones',
                'configuracionGeneral'
            )
        );
    }

    public function edit(Actividad $actividad)
    {
        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados', 'aprobada'],
            true
        )) {
            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'error',
                    'Esta actividad no puede editarse en su estado actual.'
                );
        }

        $categorias = Categoria::where('activo', true)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        $etiquetas = Etiqueta::where('activo', true)
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

        $actividad->load([
            'etiquetas',
            'portada',
            'espacio.contenedor',
        ]);

        $observacionesCorreccion = collect();
        $decisionCorrecciones = null;

        if ($actividad->estado_publicacion === 'cambios_solicitados') {
            $observacionesCorreccion = ObservacionRevisionActividad::query()
                ->where('resuelta', false)
                ->whereHas('revision', function ($query) use ($actividad) {
                    $query->where(
                        'id_actividad',
                        $actividad->id_actividad
                    );
                })
                ->with('revision')
                ->orderBy('id_observacion')
                ->get()
                ->groupBy('seccion');

            $decisionCorrecciones = RevisionActividad::query()
                ->where('id_actividad', $actividad->id_actividad)
                ->where('numero_revision', $actividad->revision_actual)
                ->where('accion', 'cambios_solicitados')
                ->latest('id_revision')
                ->first();
        }

        return view(
            'admin.actividades.editar',
            compact(
                'actividad',
                'categorias',
                'etiquetas',
                'espacios',
                'observacionesCorreccion',
                'decisionCorrecciones'
            )
        );
    }

    public function update(
        Request $request,
        Actividad $actividad
    ) {
        $eraAprobada = $actividad->estado_publicacion === 'aprobada';
        if (!in_array(
            $actividad->estado_publicacion,
            ['borrador', 'cambios_solicitados', 'aprobada'],
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

        if ($eraAprobada) {
            $this->snapshotService->capturarSiAprobada($actividad);
        }

        $rutaNueva = null;
        $rutaAnterior = null;

        try {
            DB::transaction(function () use (
                $validated,
                $request,
                $actividad,
                &$rutaNueva,
                &$rutaAnterior,
                $eraAprobada
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

                if ($eraAprobada) {
                    $actividad->estado_publicacion = 'borrador';
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
                    $eraAprobada
                        ? 'Cambios guardados. La actividad volvió a borrador y ya puede enviarse a una nueva revisión si su configuración estaba finalizada.'
                        : 'Actividad actualizada correctamente. Si la configuración ya estaba finalizada, puedes enviarla o reenviarla directamente a revisión.'
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

            $esReenvioCorrecciones =
                $actividad->estado_publicacion === 'cambios_solicitados';

            $veniaDeAprobada =
                $actividad->estado_publicacion === 'borrador'
                && RevisionActividad::query()
                    ->where('id_actividad', $actividad->id_actividad)
                    ->where('numero_revision', $actividad->revision_actual)
                    ->where('accion', 'aprobada')
                    ->exists();

            if (!$esReenvioCorrecciones && !$configuracionCompleta) {
                return redirect()
                    ->route('admin.actividades.index')
                    ->with(
                        'error',
                        'Debes completar y finalizar la configuración antes de enviar la actividad a revisión.'
                    );
            }

            $crearNuevaRevision =
                $esReenvioCorrecciones || $veniaDeAprobada;

            DB::transaction(function () use (
                $actividad,
                $esReenvioCorrecciones,
                $veniaDeAprobada,
                $crearNuevaRevision
            ) {
                if ($crearNuevaRevision) {
                    $actividad->revision_actual++;
                }

                $actividad->estado_publicacion = 'pendiente_revision';
                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                $observacion = match (true) {
                    $esReenvioCorrecciones =>
                        'Actividad reenviada después de realizar los cambios solicitados.',
                    $veniaDeAprobada =>
                        'Actividad aprobada previamente y modificada por su creador. Se envía una nueva versión a revisión.',
                    default =>
                        'Actividad enviada a revisión.',
                };

                DB::table('tbl_revisiones_actividad')->insert([
                    'id_actividad' => $actividad->id_actividad,
                    'numero_revision' => $actividad->revision_actual,
                    'id_usuario' => Auth::id(),
                    'accion' => 'enviada_revision',
                    'observacion' => $observacion,
                    'creado_en' => now(),
                ]);
            });

            $mensaje = match (true) {
                $esReenvioCorrecciones =>
                    'Actividad reenviada a revisión correctamente.',
                $veniaDeAprobada =>
                    "Nueva revisión #{$actividad->revision_actual} enviada correctamente.",
                default =>
                    'Actividad enviada a revisión correctamente.',
            };

            return redirect()
                ->route('admin.actividades.index')
                ->with('success', $mensaje);
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
            $veniaDeCorrecciones = $actividad->revision_actual > 1
                && RevisionActividad::query()
                    ->where('id_actividad', $actividad->id_actividad)
                    ->where('numero_revision', $actividad->revision_actual - 1)
                    ->where('accion', 'cambios_solicitados')
                    ->exists();

            DB::transaction(function () use ($actividad, $veniaDeCorrecciones) {
                $actividad->estado_publicacion = $veniaDeCorrecciones
                    ? 'cambios_solicitados'
                    : 'borrador';
                $actividad->actualizado_por = Auth::id();
                $actividad->save();

                DB::table('tbl_revisiones_actividad')->insert([
                    'id_actividad' => $actividad->id_actividad,
                    'numero_revision' => $actividad->revision_actual,
                    'id_usuario' => Auth::id(),
                    'accion' => 'retirada',
                    'observacion' => $veniaDeCorrecciones
                        ? 'Reenvío retirado por el creador para continuar corrigiendo la actividad.'
                        : 'Solicitud de revisión retirada por el creador.',
                    'creado_en' => now(),
                ]);
            });

            return redirect()
                ->route('admin.actividades.index')
                ->with(
                    'success',
                    $veniaDeCorrecciones
                        ? 'La actividad fue retirada de revisión. Puedes continuar realizando las correcciones y reenviarla cuando quieras.'
                        : 'La actividad fue retirada de revisión correctamente. Puedes modificarla y volver a enviarla cuando quieras.'
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
            'tipo_participacion' => [
                'required',
                Rule::in([
                    Actividad::PARTICIPACION_INFORMATIVA,
                    Actividad::PARTICIPACION_REGISTRO_GRATUITO,
                    Actividad::PARTICIPACION_REGISTRO_PAGO,
                    Actividad::PARTICIPACION_VENTA_DIRECTA,
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
            'precio_inscripcion' => [
                'nullable',
                'numeric',
                'gt:0',
                Rule::requiredIf(
                    fn () => $request->input('tipo_participacion')
                        === Actividad::PARTICIPACION_REGISTRO_PAGO
                ),
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

        $tipoParticipacion =
            $validated['tipo_participacion']
            ?? Actividad::PARTICIPACION_INFORMATIVA;

        $requiereInscripcionGeneral = in_array(
            $tipoParticipacion,
            [
                Actividad::PARTICIPACION_REGISTRO_GRATUITO,
                Actividad::PARTICIPACION_REGISTRO_PAGO,
            ],
            true
        );

        $actividad->tipo_participacion = $tipoParticipacion;
        $actividad->habilita_inscripcion = $requiereInscripcionGeneral;

        $actividad->precio_inscripcion =
            $tipoParticipacion === Actividad::PARTICIPACION_REGISTRO_PAGO
                ? (float) ($validated['precio_inscripcion'] ?? 0)
                : null;

        $actividad->requiere_cuenta =
            $tipoParticipacion === Actividad::PARTICIPACION_INFORMATIVA
                ? false
                : (bool) ($validated['requiere_cuenta'] ?? false);

        $actividad->permite_lista_espera =
            $requiereInscripcionGeneral
                ? (bool) ($validated['permite_lista_espera'] ?? false)
                : false;

        $actividad->cupo_total =
            $requiereInscripcionGeneral
                ? ($validated['cupo_total'] ?? null)
                : null;

        $actividad->inscripcion_desde =
            $requiereInscripcionGeneral
                ? ($validated['inscripcion_desde'] ?? null)
                : null;

        $actividad->inscripcion_hasta =
            $requiereInscripcionGeneral
                ? ($validated['inscripcion_hasta'] ?? null)
                : null;

        $actividad->visible_desde =
            $validated['visible_desde']
            ?? null;

        $actividad->visible_hasta =
            $validated['visible_hasta']
            ?? null;

        if (
            array_key_exists('id_espacio', $validated)
            || array_key_exists('ubicacion_externa', $validated)
        ) {
            $actividad->id_espacio = !empty($validated['id_espacio'])
                ? (int) $validated['id_espacio']
                : null;

            $actividad->ubicacion_externa = $actividad->id_espacio
                ? null
                : ($validated['ubicacion_externa'] ?? null);
        }

        if (array_key_exists('realizacion_desde', $validated)) {
            $actividad->realizacion_desde = $validated['realizacion_desde'];
        }

        if (array_key_exists('realizacion_hasta', $validated)) {
            $actividad->realizacion_hasta = $validated['realizacion_hasta'];
        }
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
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }

    private function invalidarConfiguracionGeneral(
        Actividad $actividad
    ): void {
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

    private function normalizarListaConfiguraciones(
        mixed $valor,
        array $clavesId,
        string $claveCanonica
    ): array {
        $datos = $this->normalizarMongo($valor);
        $resultado = [];

        foreach ($datos as $clave => $configuracion) {
            if (is_object($configuracion)) {
                $configuracion = $this->normalizarMongo($configuracion);
            }

            if (!is_array($configuracion)) {
                continue;
            }

            $id = null;

            foreach ($clavesId as $claveId) {
                if (
                    array_key_exists($claveId, $configuracion)
                    && $configuracion[$claveId] !== null
                    && $configuracion[$claveId] !== ''
                ) {
                    $id = $configuracion[$claveId];
                    break;
                }
            }

            if (
                $id === null
                && (is_int($clave) || ctype_digit((string) $clave))
            ) {
                $id = $clave;
            }

            if ($id !== null) {
                $configuracion[$claveCanonica] = $id;
            }

            $resultado[] = $configuracion;
        }

        return $resultado;
    }

    private function indexarConfiguraciones(
        mixed $valor,
        array $clavesId
    ): array {
        $datos = $this->normalizarMongo($valor);
        $resultado = [];

        foreach ($datos as $clave => $configuracion) {
            if (is_object($configuracion)) {
                $configuracion = $this->normalizarMongo($configuracion);
            }

            if (!is_array($configuracion)) {
                continue;
            }

            $id = null;

            foreach ($clavesId as $claveId) {
                if (
                    array_key_exists($claveId, $configuracion)
                    && $configuracion[$claveId] !== null
                    && $configuracion[$claveId] !== ''
                ) {
                    $id = $configuracion[$claveId];
                    break;
                }
            }

            if (
                $id === null
                && (is_int($clave) || ctype_digit((string) $clave))
            ) {
                $id = $clave;
            }

            if ($id !== null) {
                $resultado[(string) $id] = $configuracion;
            }
        }

        return $resultado;
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

    private function rutaStorageDesdeUrl(
        ?string $url
    ): ?string {
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
