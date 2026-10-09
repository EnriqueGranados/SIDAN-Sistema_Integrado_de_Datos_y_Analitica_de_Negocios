<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\ItemActividad;
use App\Models\MedioActividad;
use App\Models\SesionActividad;
use App\Models\VarianteItem;
use App\Models\Mongo\ActivityContent;
use App\Models\Mongo\VariantAttribute;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Espacio;
use App\Models\Recurso;
use App\Models\RecursoActividad;
use App\Models\ObservacionRevisionActividad;
use App\Models\RevisionActividad;
use App\Services\ActividadRevisionSnapshotService;
class ActividadConfiguracionController extends Controller {
    public function __construct(
        private readonly ActividadRevisionSnapshotService $snapshotService
    ) {
    }


    public function show(Request $request, Actividad $actividad)
    {
        $this->validarActividadEditable($actividad);

        $actividad->load([
            'categoria',
            'etiquetas',
            'espacio.contenedor',
            'medios',
            'items.variantes',
            'sesiones.espacio.contenedor',
            'sesiones.recursos.recurso',
            'formularios.campos',
            'recursos.recurso',
            'promociones.items',
        ]);

        $contenidoFlexible = ActivityContent::where(
            'id_actividad_pg',
            (int) $actividad->id_actividad
        )->first();

        $configuracionProductos = $this->normalizarMongo(
            $contenidoFlexible?->product_configuration ?? []
        );

        $configuracionesDatos = collect(
            $this->normalizarMongo(
                $contenidoFlexible?->purchase_fields ?? []
            )
        )->keyBy(
            fn ($configuracion) =>
                (string) ($configuracion['id_item_pg'] ?? '')
        );

        $configuracionesPrecios = collect(
            $this->normalizarMongo(
                $contenidoFlexible?->pricing_configuration ?? []
            )
        )->keyBy(
            fn ($configuracion) =>
                (string) ($configuracion['id_item_pg'] ?? '')
        );

        $configuracionSesiones = $this->normalizarMongo(
            $contenidoFlexible?->session_configuration ?? []
        );

        $configuracionGeneral = $this->normalizarMongo(
            $contenidoFlexible?->configuration ?? []
        );

        $revisionCorrecciones = null;
        $decisionCorrecciones = null;
        $observacionesCorreccion = collect();
        $modoCorreccion =
            $actividad->estado_publicacion === 'cambios_solicitados';

        if ($modoCorreccion) {
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

            $revisionCorrecciones = RevisionActividad::query()
                ->where('id_actividad', $actividad->id_actividad)
                ->where('numero_revision', $actividad->revision_actual)
                ->where('accion', 'enviada_revision')
                ->latest('id_revision')
                ->first();

            $decisionCorrecciones = RevisionActividad::query()
                ->where('id_actividad', $actividad->id_actividad)
                ->where('numero_revision', $actividad->revision_actual)
                ->where('accion', 'cambios_solicitados')
                ->latest('id_revision')
                ->first();
        }

        $presentacionCompleta = $actividad->medios
            ->whereNull('id_item_actividad')
            ->whereNull('id_sesion')
            ->contains(fn ($medio) => (bool) $medio->es_portada);

        $productosConfigurados =
            ($configuracionProductos['configured'] ?? false) === true;

        $productosHabilitados =
            ($configuracionProductos['enabled'] ?? false) === true;

        if (!$productosConfigurados && $actividad->items->isNotEmpty()) {
            $productosConfigurados = true;
            $productosHabilitados = true;

            $configuracionProductos = array_merge(
                $configuracionProductos,
                [
                    'configured' => true,
                    'enabled' => true,
                ]
            );
        }

        if (
            $productosConfigurados
            && $productosHabilitados
            && $actividad->items->isEmpty()
        ) {
            $productosConfigurados = false;
        }

        $datosCompletos = false;
        $preciosCompletos = false;

        if ($productosConfigurados && !$productosHabilitados) {
            $datosCompletos = true;
            $preciosCompletos = true;
        }

        if (
            $productosConfigurados
            && $productosHabilitados
            && $actividad->items->isNotEmpty()
        ) {
            $datosCompletos = $actividad->items->every(
                fn ($item) => $configuracionesDatos->has(
                    (string) $item->id_item_actividad
                )
            );

            if ($datosCompletos) {
                $preciosCompletos = $actividad->items->every(
                    function ($item) use (
                        $configuracionesDatos,
                        $configuracionesPrecios
                    ) {
                        $configDatos = $configuracionesDatos->get(
                            (string) $item->id_item_actividad
                        );

                        $camposLista = collect(
                            $configDatos['fields'] ?? []
                        )->filter(
                            fn ($campo) =>
                                ($campo['type'] ?? null) === 'lista'
                                && !empty($campo['options'] ?? [])
                        );

                        if ($camposLista->isEmpty()) {
                            return true;
                        }

                        return $configuracionesPrecios->has(
                            (string) $item->id_item_actividad
                        );
                    }
                );
            }
        }

        $sesionesConfiguradas =
            ($configuracionSesiones['configured'] ?? false) === true;

        $estadoPasos = [
            'presentacion' => $presentacionCompleta,
            'productos' => $productosConfigurados,
            'datos' => $productosConfigurados && $datosCompletos,
            'precios' => $productosConfigurados
                && $datosCompletos
                && $preciosCompletos,
            'sesiones' => $sesionesConfiguradas,
            'resumen' => false,
        ];

        $pasosValidos = [
            'presentacion',
            'productos',
            'datos',
            'precios',
            'sesiones',
            'resumen',
        ];

        $primerPendiente = 'resumen';

        foreach (
            ['presentacion', 'productos', 'datos', 'precios', 'sesiones']
            as $pasoEvaluado
        ) {
            if (!($estadoPasos[$pasoEvaluado] ?? false)) {
                $primerPendiente = $pasoEvaluado;
                break;
            }
        }

        $seccionesConfiguracion = [
            'presentacion' => 'presentacion',
            'productos' => 'productos',
            'datos_solicitados' => 'datos',
            'precios_costos' => 'precios',
            'programacion' => 'sesiones',
        ];

        $primerPasoCorreccion = 'resumen';

        if ($modoCorreccion) {
            foreach ($seccionesConfiguracion as $seccion => $pasoConfig) {
                if (
                    $observacionesCorreccion
                        ->get($seccion, collect())
                        ->isNotEmpty()
                ) {
                    $primerPasoCorreccion = $pasoConfig;
                    break;
                }
            }
        }

        $pasoSolicitado = $request->query('paso');

        if (
            !$pasoSolicitado
            || !in_array($pasoSolicitado, $pasosValidos, true)
        ) {
            $paso = $modoCorreccion
                ? $primerPasoCorreccion
                : $primerPendiente;
        } elseif ($modoCorreccion) {
            $paso = $pasoSolicitado;
        } else {
            $indiceSolicitado = array_search(
                $pasoSolicitado,
                $pasosValidos,
                true
            );

            $indicePendiente = array_search(
                $primerPendiente,
                $pasosValidos,
                true
            );

            $paso = $indiceSolicitado > $indicePendiente
                ? $primerPendiente
                : $pasoSolicitado;
        }

        $itemSeleccionado = null;

        if ($request->filled('item')) {
            $itemSeleccionado = $actividad->items->firstWhere(
                'id_item_actividad',
                (int) $request->query('item')
            );

            abort_unless($itemSeleccionado, 404);
        }

        if (
            $itemSeleccionado
            && !in_array($paso, ['datos', 'precios'], true)
        ) {
            $itemSeleccionado = null;
        }

        $configDatosSeleccionado = null;
        $configPrecioSeleccionado = null;

        if ($itemSeleccionado) {
            $configDatosSeleccionado = $configuracionesDatos->get(
                (string) $itemSeleccionado->id_item_actividad
            );

            $configPrecioSeleccionado = $configuracionesPrecios->get(
                (string) $itemSeleccionado->id_item_actividad
            );
        }

        $modoSesiones = $configuracionSesiones['mode'] ?? null;

        if (
            !in_array(
                $modoSesiones,
                ['ninguna', 'unica', 'multiples'],
                true
            )
        ) {
            if (($configuracionSesiones['configured'] ?? false) === true) {
                if (($configuracionSesiones['enabled'] ?? false) === false) {
                    $modoSesiones = 'ninguna';
                } else {
                    $modoSesiones = $actividad->sesiones->count() <= 1
                        ? 'unica'
                        : 'multiples';
                }
            } else {
                $modoSesiones = null;
            }
        }

        $espacios = Espacio::query()
            ->where(function ($query) use ($actividad) {
                $query->where('activo', true);

                if ($actividad->id_espacio) {
                    $query->orWhere(
                        'id_espacio',
                        $actividad->id_espacio
                    );
                }

                $idsSesiones = $actividad->sesiones
                    ->pluck('id_espacio')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($idsSesiones)) {
                    $query->orWhereIn('id_espacio', $idsSesiones);
                }
            })
            ->with([
                'contenedor',
                'recursos' => fn ($query) => $query
                    ->where('tbl_recursos.activo', true)
                    ->orderBy('tbl_recursos.nombre'),
            ])
            ->orderBy('nombre')
            ->get();

        $recursos = Recurso::query()
            ->where(function ($query) use ($actividad) {
                $query->where('activo', true);

                $idsUsados = $actividad->recursos
                    ->pluck('id_recurso')
                    ->merge(
                        $actividad->sesiones->flatMap(
                            fn ($sesion) =>
                                $sesion->recursos->pluck('id_recurso')
                        )
                    )
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($idsUsados)) {
                    $query->orWhereIn('id_recurso', $idsUsados);
                }
            })
            ->with('espacios')
            ->orderBy('nombre')
            ->get();

        return view(
            'admin.actividades.configurar',
            compact(
                'actividad',
                'paso',
                'itemSeleccionado',
                'configuracionProductos',
                'configuracionesDatos',
                'configuracionesPrecios',
                'configDatosSeleccionado',
                'configPrecioSeleccionado',
                'configuracionSesiones',
                'configuracionGeneral',
                'modoSesiones',
                'espacios',
                'recursos',
                'modoCorreccion',
                'revisionCorrecciones',
                'decisionCorrecciones',
                'observacionesCorreccion'
            )
        );
    }

    public function guardarConfiguracionProductos(Request $request,Actividad $actividad) {
        $this->validarActividadEditable($actividad);
        $validated = $request->validate(['ofrece_productos' => ['required', 'boolean', ], ]);
        $ofreceProductos = (bool) $validated['ofrece_productos'];
        if (!$ofreceProductos && $actividad->items()->exists() ) {
            throw ValidationException::withMessages(['ofrece_productos' => 'La actividad ya tiene productos o servicios agregados. Elimínalos antes de indicar que no ofrecerá ninguno.', ]);
        }
        if ($actividad->estado_publicacion === 'aprobada') {
            $this->snapshotService->capturarSiAprobada($actividad);
        }
        try {
            $documento = $this->obtenerDocumentoActividad($actividad);
            $documento->product_configuration = ['configured' => true, 'enabled' => $ofreceProductos, 'updated_at' => now()->toIso8601String(),];
            if (!$ofreceProductos) {
                $documento->purchase_fields = [];
                $documento->pricing_configuration = [];
            }
            $this->invalidarConfiguracionGeneral($documento);
            $documento->save();
            $actividad->actualizado_por = Auth::id();
            $actividad->save();
            $this->marcarComoBorradorSiAprobada($actividad);
        return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => $ofreceProductos ? 'productos' : 'sesiones', ] )->with('success', $ofreceProductos ? 'Ahora puedes agregar los productos o servicios que ofrecerá la actividad.' : 'La actividad quedó configurada sin productos ni servicios. Puedes continuar con su programación.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo guardar esta decisión.');
        }
    }

    public function guardarDatosPedido(Request $request,Actividad $actividad,ItemActividad $item) {
        $this->validarItemPerteneceActividad($actividad, $item);
        $this->validarActividadEditable($actividad);
        $this->validarProductosHabilitados($actividad);
        $validated = $request->validate(['requiere_datos' => ['required', 'boolean', ], 'campos' => ['nullable', 'array', 'max:20', ], 'campos.*.label' => ['required_with:campos', 'string', 'max:100', ], 'campos.*.type' => ['required_with:campos', Rule::in(['texto', 'numero', 'lista', ]), ], 'campos.*.required' => ['nullable', 'boolean', ], 'campos.*.options' => ['nullable', 'string', 'max:5000', ], ]);
        $requiereDatos = (bool) $validated['requiere_datos'];
        $camposProcesados = [];
        if ($requiereDatos) {
            $campos = $validated['campos'] ?? [];
            if (empty($campos)) {
                throw ValidationException::withMessages(['campos' => 'Agrega al menos un dato que deba proporcionar el comprador.', ]);
            }
            $clavesUsadas = [];
            foreach ($campos as $campo) {
                $nombre = trim($campo['label']);
                $tipo = $campo['type'];
                $clave = Str::slug($nombre, '_');
                if ($clave === '') {
                    throw ValidationException::withMessages(['campos' => 'Todos los campos deben tener un nombre válido.', ]);
                }
                if (in_array($clave, $clavesUsadas, true) ) {
                    throw ValidationException::withMessages(['campos' => "El dato '{$nombre}' está repetido.", ]);
                }
                $clavesUsadas[] = $clave;
                $opciones = [];
                if ($tipo === 'lista') {
                    $opciones = $this->procesarOpciones($campo['options'] ?? '');
                    if (empty($opciones)) {
                        throw ValidationException::withMessages(['campos' => "Agrega al menos una opción para '{$nombre}'.", ]);
                    }
                }
                $camposProcesados[] = ['key' => $clave, 'label' => $nombre, 'type' => $tipo, 'required' => (bool) ($campo['required'] ?? false), 'options' => $opciones,];
            }
        }
        if ($actividad->estado_publicacion === 'aprobada') {
            $this->snapshotService->capturarSiAprobada($actividad);
        }

        try {
            $documento = $this->obtenerDocumentoActividad($actividad);
            $configuraciones = collect($this->normalizarMongo($documento->purchase_fields ?? []) )->reject(fn ($configuracion) => (int) ($configuracion['id_item_pg'] ?? 0 ) === (int) $item->id_item_actividad )->values()->all();
            $configuraciones[] = ['id_item_pg' => (int) $item->id_item_actividad, 'enabled' => $requiereDatos, 'fields' => $camposProcesados, 'updated_at' => now()->toIso8601String(),];
            $documento->purchase_fields = $configuraciones;
            $this->invalidarConfiguracionGeneral($documento);
            $documento->save();
            $actividad->actualizado_por = Auth::id();
            $actividad->save();
            $this->marcarComoBorradorSiAprobada($actividad);
        return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'datos', ] )->with('success', 'La configuración fue guardada correctamente.');
        } catch (\Throwable $e) {
            report($e);
            return back()->withInput()->with('error', 'No se pudo guardar la configuración.');
        }
    }

    public function guardarAjustesPrecio(Request $request, Actividad $actividad, ItemActividad $item)
    {
        $this->validarItemPerteneceActividad($actividad, $item);
        $this->validarActividadEditable($actividad);
        $this->validarProductosHabilitados($actividad);

        $documentoActual = ActivityContent::where(
            'id_actividad_pg',
            (int) $actividad->id_actividad
        )->first();

        $configuracionesDatos = collect(
            $this->normalizarMongo($documentoActual?->purchase_fields ?? [])
        );

        $configDatosItem = $configuracionesDatos->first(
            fn ($configuracion) =>
                (int) ($configuracion['id_item_pg'] ?? 0)
                === (int) $item->id_item_actividad
        );

        if (!$configDatosItem) {
            throw ValidationException::withMessages([
                'ajustes' => 'Primero debes configurar qué datos se solicitarán al comprador.',
            ]);
        }

        $camposLista = collect($configDatosItem['fields'] ?? [])
            ->filter(
                fn ($campo) =>
                    ($campo['type'] ?? null) === 'lista'
                    && !empty($campo['options'] ?? [])
            )
            ->values();

        if ($camposLista->isEmpty()) {
            throw ValidationException::withMessages([
                'ajustes' => 'Este producto no tiene listas de opciones que puedan modificar el precio o costo.',
            ]);
        }

        $validated = $request->validate([
            'tiene_cambios' => ['required', 'boolean'],
            'ajustes_payload' => ['nullable', 'string', 'max:200000'],
        ], [
            'tiene_cambios.required' => 'Indica si alguna opción modifica el precio o costo.',
            'tiene_cambios.boolean' => 'La selección de cambios de precio no es válida.',
            'ajustes_payload.max' => 'La configuración enviada es demasiado grande.',
        ]);

        $tieneCambios = (bool) $validated['tiene_cambios'];
        $ajustes = [];

        if ($tieneCambios) {
            $payload = trim((string) ($validated['ajustes_payload'] ?? ''));

            if ($payload === '') {
                throw ValidationException::withMessages([
                    'ajustes' => 'Agrega al menos una variante con aumento.',
                ]);
            }

            try {
                $ajustes = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw ValidationException::withMessages([
                    'ajustes' => 'No se pudo interpretar la configuración de variantes. Vuelve a intentarlo.',
                ]);
            }

            if (!is_array($ajustes)) {
                throw ValidationException::withMessages([
                    'ajustes' => 'La configuración de variantes no es válida.',
                ]);
            }

            $validator = Validator::make(
                ['ajustes' => $ajustes],
                [
                    'ajustes' => ['required', 'array', 'min:1', 'max:200'],
                    'ajustes.*.campo' => ['required', 'string', 'max:100'],
                    'ajustes.*.opcion' => ['required', 'string', 'max:200'],
                    'ajustes.*.aumento_precio' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
                    'ajustes.*.aumento_costo' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
                ],
                [
                    'ajustes.required' => 'Agrega al menos una variante con aumento.',
                    'ajustes.min' => 'Agrega al menos una variante con aumento.',
                    'ajustes.max' => 'Puedes configurar hasta 200 aumentos por producto.',
                    'ajustes.*.campo.required' => 'Selecciona el dato de cada variante.',
                    'ajustes.*.opcion.required' => 'Selecciona la opción de cada variante.',
                    'ajustes.*.aumento_precio.required' => 'Indica el aumento de precio.',
                    'ajustes.*.aumento_precio.numeric' => 'El aumento de precio debe ser numérico.',
                    'ajustes.*.aumento_precio.min' => 'El aumento de precio no puede ser negativo.',
                    'ajustes.*.aumento_costo.required' => 'Indica el aumento de costo.',
                    'ajustes.*.aumento_costo.numeric' => 'El aumento de costo debe ser numérico.',
                    'ajustes.*.aumento_costo.min' => 'El aumento de costo no puede ser negativo.',
                ]
            );

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $ajustes = $validator->validated()['ajustes'];
        }

        if ($actividad->estado_publicacion === 'aprobada') {
            $this->snapshotService->capturarSiAprobada($actividad);
        }

        $documento = $this->obtenerDocumentoActividad($actividad);
        $mongoOriginal = $this->capturarEstadoMongo($documento, [
            'pricing_configuration',
            'configuration',
        ]);

        $idsVariantesAnteriores = VarianteItem::query()
            ->where('id_item_actividad', $item->id_item_actividad)
            ->pluck('id_variante_item')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (!$tieneCambios) {
            $mongoActualizado = false;

            try {
                DB::transaction(function () use (
                    $actividad,
                    $item,
                    $documento,
                    $idsVariantesAnteriores,
                    &$mongoActualizado
                ) {
                    if (!empty($idsVariantesAnteriores)) {
                        VarianteItem::whereIn('id_variante_item', $idsVariantesAnteriores)->delete();
                    }

                    $this->establecerConfiguracionPrecio(
                        $documento,
                        $item,
                        false,
                        [],
                        []
                    );
                    $this->invalidarConfiguracionGeneral($documento);
                    $documento->save();
                    $mongoActualizado = true;

                    $actividad->actualizado_por = Auth::id();
                    $actividad->save();
                });

                $this->eliminarAtributosMongoVariantes($idsVariantesAnteriores);
                $this->marcarComoBorradorSiAprobada($actividad);

                $mensaje = 'Todas las opciones utilizarán el precio y costo base.';
                $redirect = route('admin.actividades.configurar', [
                    'actividad' => $actividad->id_actividad,
                    'paso' => 'precios',
                ]);

                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => true,
                        'message' => $mensaje,
                        'redirect' => $redirect,
                    ]);
                }

                return redirect($redirect)->with('success', $mensaje);
            } catch (\Throwable $e) {
                report($e);

                if ($mongoActualizado) {
                    $this->restaurarEstadoMongo($actividad, $mongoOriginal);
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'No se pudo guardar la configuración de precios.',
                    ], 500);
                }

                return back()
                    ->withInput()
                    ->with('error', 'No se pudo guardar la configuración de precios.');
            }
        }

        $reglas = [];
        $reglasUsadas = [];
        $camposSeleccionados = [];

        foreach ($ajustes as $ajuste) {
            $campoClave = trim((string) $ajuste['campo']);
            $opcion = trim((string) $ajuste['opcion']);

            $campo = $camposLista->first(
                fn ($campoDisponible) =>
                    (string) ($campoDisponible['key'] ?? '') === $campoClave
            );

            if (!$campo) {
                throw ValidationException::withMessages([
                    'ajustes' => 'Uno de los datos seleccionados ya no es válido.',
                ]);
            }

            $opcionesPermitidas = collect($campo['options'] ?? [])
                ->map(fn ($valor) => (string) $valor)
                ->values()
                ->all();

            if (!in_array($opcion, $opcionesPermitidas, true)) {
                throw ValidationException::withMessages([
                    'ajustes' => "La opción '{$opcion}' ya no pertenece a '{$campo['label']}'.",
                ]);
            }

            $identificador = $campoClave . '|' . $opcion;

            if (in_array($identificador, $reglasUsadas, true)) {
                throw ValidationException::withMessages([
                    'ajustes' => "La opción '{$opcion}' de '{$campo['label']}' está repetida.",
                ]);
            }

            $reglasUsadas[] = $identificador;

            $aumentoPrecio = round((float) $ajuste['aumento_precio'], 2);
            $aumentoCosto = round((float) $ajuste['aumento_costo'], 2);

            if ($aumentoPrecio <= 0 && $aumentoCosto <= 0) {
                throw ValidationException::withMessages([
                    'ajustes' => "La opción '{$opcion}' debe tener al menos un aumento mayor que cero.",
                ]);
            }

            $camposSeleccionados[$campoClave] = $campo;

            $reglas[] = [
                'field_key' => $campoClave,
                'field_label' => $campo['label'] ?? Str::headline($campoClave),
                'option' => $opcion,
                'price_increment' => $aumentoPrecio,
                'cost_increment' => $aumentoCosto,
            ];
        }

        $camposClave = array_keys($camposSeleccionados);
        $combinaciones = $this->generarCombinaciones($camposSeleccionados);

        if (count($combinaciones) > 500) {
            throw ValidationException::withMessages([
                'ajustes' => 'Las listas involucradas generarían más de 500 combinaciones comerciales. Reduce las opciones involucradas.',
            ]);
        }

        $precioBase = round((float) $item->precio, 2);
        $costoBase = round((float) $item->costo_referencia, 2);
        $variantesPreparadas = [];

        foreach ($combinaciones as $combinacion) {
            $aumentoPrecioTotal = 0;
            $aumentoCostoTotal = 0;

            foreach ($combinacion as $campoClave => $opcion) {
                foreach ($reglas as $regla) {
                    if (
                        $regla['field_key'] === $campoClave
                        && $regla['option'] === $opcion
                    ) {
                        $aumentoPrecioTotal += $regla['price_increment'];
                        $aumentoCostoTotal += $regla['cost_increment'];
                    }
                }
            }

            $precioFinal = round($precioBase + $aumentoPrecioTotal, 2);
            $costoFinal = round($costoBase + $aumentoCostoTotal, 2);
            $partesNombre = [];

            foreach ($combinacion as $campoClave => $opcion) {
                $partesNombre[] =
                    ($camposSeleccionados[$campoClave]['label'] ?? Str::headline($campoClave))
                    . ': '
                    . $opcion;
            }

            $variantesPreparadas[] = [
                'nombre' => implode(' / ', $partesNombre),
                'precio' => $aumentoPrecioTotal > 0 ? $precioFinal : null,
                'costo' => $aumentoCostoTotal > 0 ? $costoFinal : null,
                'atributos' => $combinacion,
            ];
        }

        $idsVariantesNuevas = [];
        $mongoActualizado = false;

        try {
            DB::transaction(function () use (
                $actividad,
                $item,
                $documento,
                $variantesPreparadas,
                $camposClave,
                $reglas,
                $idsVariantesAnteriores,
                &$idsVariantesNuevas,
                &$mongoActualizado
            ) {
                foreach ($variantesPreparadas as $indice => $datos) {
                    $variante = new VarianteItem();
                    $variante->id_item_actividad = $item->id_item_actividad;
                    $variante->sku = null;
                    $variante->nombre_variante = Str::limit($datos['nombre'], 180, '');
                    $variante->precio = $datos['precio'];
                    $variante->costo_referencia = $datos['costo'];
                    $variante->stock_total = null;
                    $variante->orden = $indice;
                    $variante->activo = true;
                    $variante->save();

                    $idNueva = (int) $variante->id_variante_item;
                    $idsVariantesNuevas[] = $idNueva;

                    $atributo = new VariantAttribute();
                    $atributo->id_variante_pg = $idNueva;
                    $atributo->attributes = $datos['atributos'];
                    $atributo->save();
                }

                $this->verificarAtributosMongoVariantes($idsVariantesNuevas);

                $this->establecerConfiguracionPrecio(
                    $documento,
                    $item,
                    true,
                    $camposClave,
                    $reglas
                );
                $this->invalidarConfiguracionGeneral($documento);
                $documento->save();
                $mongoActualizado = true;

                if (!empty($idsVariantesAnteriores)) {
                    VarianteItem::whereIn(
                        'id_variante_item',
                        $idsVariantesAnteriores
                    )->delete();
                }

                $actividad->actualizado_por = Auth::id();
                $actividad->save();
            });

            $this->eliminarAtributosMongoVariantes($idsVariantesAnteriores);
            $this->marcarComoBorradorSiAprobada($actividad);

            $mensaje = 'Los aumentos de precio y costo fueron guardados correctamente.';
            $redirect = route('admin.actividades.configurar', [
                'actividad' => $actividad->id_actividad,
                'paso' => 'precios',
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => $mensaje,
                    'redirect' => $redirect,
                ]);
            }

            return redirect($redirect)->with('success', $mensaje);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            $this->eliminarAtributosMongoVariantes($idsVariantesNuevas);

            if ($mongoActualizado) {
                $this->restaurarEstadoMongo($actividad, $mongoOriginal);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se pudo guardar la configuración de precios. Se conservaron las variantes anteriores.',
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'No se pudo guardar la configuración de precios. Se conservaron las variantes anteriores.');
        }
    }

    public function guardarSesiones(Request $request, Actividad $actividad)
    {
        $this->validarActividadEditable($actividad);

        $validated = $request->validate([
            'modo_sesiones' => ['nullable', Rule::in(['ninguna', 'unica', 'multiples'])],
            'tiene_sesiones' => ['nullable', 'boolean'],
            'confirmar_solapamientos' => ['nullable', 'boolean'],
            'sesion_unica_modalidad' => ['nullable', Rule::in(['presencial', 'virtual'])],
            'sesion_unica_enlace_acceso' => ['nullable', 'url', 'max:2000'],
            'sesion_unica_id_espacio' => ['nullable', 'integer'],
            'sesion_unica_ubicacion' => ['nullable', 'string', 'max:250'],
            'sesiones' => ['nullable', 'array', 'max:100'],
            'sesiones.*.id_sesion' => ['nullable', 'integer'],
            'sesiones.*.nombre' => ['nullable', 'string', 'max:150'],
            'sesiones.*.fecha_inicio' => ['nullable', 'date'],
            'sesiones.*.fecha_fin' => ['nullable', 'date'],
            'sesiones.*.modalidad' => ['nullable', Rule::in(['presencial', 'virtual'])],
            'sesiones.*.id_espacio' => ['nullable', 'integer'],
            'sesiones.*.ubicacion' => ['nullable', 'string', 'max:250'],
            'sesiones.*.enlace_acceso' => ['nullable', 'url', 'max:2000'],
            'sesiones.*.cupo' => ['nullable', 'integer', 'min:0'],
            'sesiones.*.requiere_reserva' => ['nullable', 'boolean'],
            'sesiones.*.obligatoria' => ['nullable', 'boolean'],
            'sesiones.*.recursos' => ['nullable', 'array'],
            'sesiones.*.recursos.*.seleccionado' => ['nullable', 'boolean'],
            'sesiones.*.recursos.*.cantidad' => ['nullable', 'integer', 'min:1'],
            'sesiones.*.recursos.*.observacion' => ['nullable', 'string', 'max:300'],
        ]);

        $modo = $validated['modo_sesiones'] ?? null;
        $modoLegacy = false;

        if (!$modo && array_key_exists('tiene_sesiones', $validated)) {
            $modoLegacy = true;
            $modo = (bool) $validated['tiene_sesiones'] ? 'multiples' : 'ninguna';
        }

        if (!$modo) {
            throw ValidationException::withMessages([
                'modo_sesiones' => 'Indica si la actividad no tendrá sesiones, tendrá una sola o tendrá varias.',
            ]);
        }

        $sesionesPreparadas = [];
        $rango = null;

        if ($modo !== 'ninguna') {
            $rango = $this->obtenerRangoRealizacion($actividad);
        }

        if ($modo === 'unica') {
            $sesionesPreparadas = [
                $this->prepararSesionUnica(
                    $actividad,
                    $rango,
                    [
                        'modalidad' => $validated['sesion_unica_modalidad'] ?? 'presencial',
                        'enlace_acceso' => $validated['sesion_unica_enlace_acceso'] ?? null,
                        'id_espacio' => $validated['sesion_unica_id_espacio'] ?? null,
                        'ubicacion' => $validated['sesion_unica_ubicacion'] ?? null,
                    ]
                ),
            ];
        }

        if ($modo === 'multiples') {
            $sesionesRecibidas = $validated['sesiones'] ?? [];
            $minimo = $modoLegacy ? 1 : 2;

            if (count($sesionesRecibidas) < $minimo) {
                throw ValidationException::withMessages([
                    'sesiones' => $modoLegacy
                        ? 'Agrega al menos una fecha, horario o turno.'
                        : 'Si seleccionaste varias sesiones, agrega al menos dos.',
                ]);
            }

            $sesionesPreparadas = $this->prepararSesionesMultiples(
                $actividad,
                $sesionesRecibidas,
                $rango
            );

            $solapamientos = $this->detectarSolapamientos($sesionesPreparadas);

            if (
                !empty($solapamientos)
                && !(bool) ($validated['confirmar_solapamientos'] ?? false)
            ) {
                throw ValidationException::withMessages([
                    'confirmar_solapamientos' =>
                        'Hay horarios que se realizan al mismo tiempo: '
                        . implode('; ', array_slice($solapamientos, 0, 5))
                        . '. Si es intencional, confirma que se realizarán simultáneamente.',
                ]);
            }
        }

        if ($actividad->estado_publicacion === 'aprobada') {
            $this->snapshotService->capturarSiAprobada($actividad);
        }

        $documento = $this->obtenerDocumentoActividad($actividad);
        $mongoOriginal = $this->capturarEstadoMongo($documento, [
            'session_configuration',
            'configuration',
        ]);
        $mongoActualizado = false;

        try {
            DB::transaction(function () use (
                $actividad,
                $modo,
                $sesionesPreparadas,
                $documento,
                &$mongoActualizado
            ) {
                if ($modo === 'ninguna') {
                    $actividad->sesiones()->delete();
                } elseif ($modo === 'unica') {
                    $existente = $actividad->sesiones()
                        ->orderBy('orden')
                        ->orderBy('id_sesion')
                        ->first();

                    $sesion = $existente ?? new SesionActividad();
                    $sesion->id_actividad = $actividad->id_actividad;
                    $sesion->nombre = $sesionesPreparadas[0]['nombre'];
                    $sesion->fecha_inicio = $sesionesPreparadas[0]['fecha_inicio'];
                    $sesion->fecha_fin = $sesionesPreparadas[0]['fecha_fin'];
                    $sesion->id_espacio = $sesionesPreparadas[0]['id_espacio'];
                    $sesion->ubicacion = $sesionesPreparadas[0]['ubicacion'];
                    $sesion->enlace_acceso = $sesionesPreparadas[0]['enlace_acceso'];
                    $sesion->cupo = $sesionesPreparadas[0]['cupo'];
                    $sesion->requiere_reserva = false;
                    $sesion->obligatoria = true;
                    $sesion->orden = 0;
                    $sesion->estado = 'programada';
                    $sesion->save();

                    $this->sincronizarRecursosSesion(
                        $actividad,
                        $sesion,
                        $sesionesPreparadas[0]['recursos'] ?? []
                    );

                    $actividad->sesiones()
                        ->where('id_sesion', '!=', $sesion->id_sesion)
                        ->delete();
                } else {
                    $idsConservados = [];

                    foreach ($sesionesPreparadas as $indice => $datos) {
                        $sesion = null;

                        if (!empty($datos['id_sesion'])) {
                            $sesion = $actividad->sesiones()
                                ->where('id_sesion', (int) $datos['id_sesion'])
                                ->first();

                            if (!$sesion) {
                                throw ValidationException::withMessages([
                                    'sesiones' => 'Una de las sesiones ya no existe o no pertenece a esta actividad.',
                                ]);
                            }
                        }

                        if (!$sesion) {
                            $sesion = new SesionActividad();
                            $sesion->id_actividad = $actividad->id_actividad;
                        }

                        $sesion->nombre = $datos['nombre'];
                        $sesion->fecha_inicio = $datos['fecha_inicio'];
                        $sesion->fecha_fin = $datos['fecha_fin'];
                        $sesion->id_espacio = $datos['id_espacio'];
                        $sesion->ubicacion = $datos['ubicacion'];
                        $sesion->enlace_acceso = $datos['enlace_acceso'];
                        $sesion->cupo = $datos['cupo'];
                        $sesion->requiere_reserva = $datos['requiere_reserva'];
                        $sesion->obligatoria = $datos['obligatoria'];
                        $sesion->orden = $indice;
                        $sesion->estado = 'programada';
                        $sesion->save();

                        $this->sincronizarRecursosSesion(
                            $actividad,
                            $sesion,
                            $datos['recursos'] ?? []
                        );

                        $idsConservados[] = (int) $sesion->id_sesion;
                    }

                    $actividad->sesiones()
                        ->whereNotIn('id_sesion', $idsConservados)
                        ->delete();
                }

                $documento->session_configuration = [
                    'configured' => true,
                    'enabled' => $modo !== 'ninguna',
                    'mode' => $modo,
                    'inherits_general_capacity' =>
                        $modo === 'unica'
                        && $actividad->habilita_inscripcion
                        && $actividad->cupo_total !== null,
                    'updated_at' => now()->toIso8601String(),
                ];
                $this->invalidarConfiguracionGeneral($documento);
                $documento->save();
                $mongoActualizado = true;

                $actividad->actualizado_por = Auth::id();
                $actividad->save();
            });

            $this->marcarComoBorradorSiAprobada($actividad);

            $redirect = route('admin.actividades.configurar', [
                'actividad' => $actividad->id_actividad,
                'paso' => 'resumen',
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'Programación guardada correctamente.',
                    'redirect' => $redirect,
                ]);
            }

            return redirect($redirect)
                ->with('success', 'Programación guardada correctamente. Revisa el resumen antes de finalizar.');
        } catch (ValidationException $e) {
            if ($mongoActualizado) {
                $this->restaurarEstadoMongo($actividad, $mongoOriginal);
            }

            throw $e;
        } catch (\Throwable $e) {
            report($e);

            if ($mongoActualizado) {
                $this->restaurarEstadoMongo($actividad, $mongoOriginal);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'No se pudo guardar la configuración de fechas y horarios. Se conservó la configuración anterior.',
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'No se pudo guardar la configuración de fechas y horarios. Se conservó la configuración anterior.');
        }
    }

    public function guardarPresentacion(Request $request,Actividad $actividad) {
        $this->validarActividadEditable($actividad);
        $validated = $request->validate(['portada' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', ], 'texto_alternativo' => ['nullable', 'string', 'max:255', ], 'galeria' => ['nullable', 'array', 'max:20', ], 'galeria.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', ], ]);
        $actividad->load('medios');
        if ($actividad->estado_publicacion === 'aprobada') {
            $this->snapshotService->capturarSiAprobada($actividad);
        }
        $portadaActual = $actividad->medios->whereNull('id_item_actividad')->whereNull('id_sesion')->firstWhere('es_portada', true);
        $archivosNuevos = [];
        $archivoAnterior = null;
        try {
            DB::transaction(function () use ($request, $validated, $actividad, $portadaActual, &$archivosNuevos, &$archivoAnterior ) {
                    if ($request->hasFile('portada')) {
                        $ruta = $request->file('portada')->store("actividades/{$actividad->id_actividad}", 'public');
                        $archivosNuevos[] = $ruta;
                        if ($portadaActual) {
                            $archivoAnterior = $this->rutaStorageDesdeUrl($portadaActual->url);
                            $portadaActual->url = $ruta;
                            $portadaActual->texto_alternativo = $validated['texto_alternativo'] ?: $actividad->nombre;
                            $portadaActual->tipo = 'imagen';
                            $portadaActual->es_portada = true;
                            $portadaActual->orden = 0;
                            $portadaActual->save();
                        } else {
                            $medio = new MedioActividad();
                            $medio->id_actividad = $actividad->id_actividad;
                            $medio->id_item_actividad = null;
                            $medio->id_sesion = null;
                            $medio->tipo = 'imagen';
                            $medio->url = $ruta;
                            $medio->texto_alternativo = $validated['texto_alternativo'] ?: $actividad->nombre;
                            $medio->es_portada = true;
                            $medio->orden = 0;
                            $medio->save();
                        }
                    } elseif (
                        $portadaActual && array_key_exists('texto_alternativo', $validated)) {
                        $portadaActual->texto_alternativo = $validated['texto_alternativo'] ?: $actividad->nombre;
                        $portadaActual->save();
                    }
                    if ($request->hasFile('galeria')) {
                        $ultimoOrden = (int) MedioActividad::query()->where('id_actividad', $actividad->id_actividad )->whereNull('id_item_actividad' )->whereNull('id_sesion' )->max('orden');
                        foreach ($request->file('galeria' ) as $archivo ) {
                            $ruta = $archivo->store("actividades/{$actividad->id_actividad}", 'public');
                            $archivosNuevos[] = $ruta;
                            $ultimoOrden++;
                            $medio = new MedioActividad();
                            $medio->id_actividad = $actividad->id_actividad;
                            $medio->id_item_actividad = null;
                            $medio->id_sesion = null;
                            $medio->tipo = 'imagen';
                            $medio->url = $ruta;
                            $medio->texto_alternativo = $actividad->nombre;
                            $medio->es_portada = false;
                            $medio->orden = $ultimoOrden;
                            $medio->save();
                        }
                    }
                    $actividad->actualizado_por = Auth::id();
                    $actividad->save();
                }
            );
            if ($archivoAnterior && !in_array($archivoAnterior, $archivosNuevos, true) && Storage::disk('public')->exists($archivoAnterior)) {
                Storage::disk('public')->delete($archivoAnterior);
            }
            $documento = $this->obtenerDocumentoActividad($actividad);
            $this->invalidarConfiguracionGeneral($documento);
            $documento->save();
            $this->marcarComoBorradorSiAprobada($actividad);
            return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'productos', ] )->with('success', 'Presentación guardada correctamente. Continúa con productos o servicios.');
        } catch (\Throwable $e) {
            foreach ($archivosNuevos as $ruta ) {
                if (Storage::disk('public')->exists($ruta) ) {
                    Storage::disk('public')->delete($ruta);
                }
            }
            report($e);
            return back()->withInput()->with('error', 'No se pudo guardar la presentación.');
        }
    }

    public function establecerPortada(Actividad $actividad,MedioActividad $medio) {
        $this->validarActividadEditable($actividad);
        if ((int) $medio->id_actividad !== (int) $actividad->id_actividad || $medio->id_item_actividad !== null || $medio->id_sesion !== null ) {
            abort(404);
        }
        if ($medio->es_portada) {
            return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'presentacion', ]);
        }
        if ($actividad->estado_publicacion === 'aprobada') {
            $this->snapshotService->capturarSiAprobada($actividad);
        }
        DB::transaction(function () use ($actividad, $medio ) {
                MedioActividad::query()->where('id_actividad', $actividad->id_actividad )->whereNull('id_item_actividad' )->whereNull('id_sesion' )->where('es_portada', true)->update(['es_portada' => false, ]);
                $medio->es_portada = true;
                $medio->orden = 0;
                $medio->save();
            }
        );
        $documento = $this->obtenerDocumentoActividad($actividad);
        $this->invalidarConfiguracionGeneral($documento);
        $documento->save();
        $this->marcarComoBorradorSiAprobada($actividad);
        return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'presentacion', ] )->with('success', 'La imagen fue establecida como portada.');
    }

    public function eliminarMedio(Actividad $actividad,MedioActividad $medio) {
        $this->validarActividadEditable($actividad);
        if ((int) $medio->id_actividad !== (int) $actividad->id_actividad || $medio->id_item_actividad !== null || $medio->id_sesion !== null ) {
            abort(404);
        }
        if ($medio->es_portada) {
            return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'presentacion', ] )->with('error', 'La portada no puede eliminarse directamente. Primero selecciona otra imagen como portada.');
        }
        if ($actividad->estado_publicacion === 'aprobada') {
            $this->snapshotService->capturarSiAprobada($actividad);
        }
        $ruta = $this->rutaStorageDesdeUrl($medio->url);
        $medio->delete();
        if ($ruta && Storage::disk('public')->exists($ruta) ) {
            Storage::disk('public')->delete($ruta);
        }
        $documento = $this->obtenerDocumentoActividad($actividad);
        $this->invalidarConfiguracionGeneral($documento);
        $documento->save();
        $this->marcarComoBorradorSiAprobada($actividad);
        return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'presentacion', ] )->with('success', 'Imagen eliminada correctamente.');
    }

    public function finalizarConfiguracion(Actividad $actividad) {
        $this->validarActividadEditable($actividad);
        $actividad->load(['medios', 'items.variantes', 'sesiones', ]);
        $documento = $this->obtenerDocumentoActividad($actividad);
        $configuracionProductos = $this->normalizarMongo($documento->product_configuration ?? []);
        $configuracionesDatos = collect($this->normalizarMongo($documento->purchase_fields ?? []) )->keyBy(fn ($configuracion) => (string) ($configuracion['id_item_pg'] ?? '' ));
        $configuracionesPrecios = collect($this->normalizarMongo($documento->pricing_configuration ?? []) )->keyBy(fn ($configuracion) => (string) ($configuracion['id_item_pg'] ?? '' ));
        $configuracionSesiones = $this->normalizarMongo($documento->session_configuration ?? []);
        $portadaConfigurada = $actividad->medios->whereNull('id_item_actividad' )->whereNull('id_sesion' )->contains(fn ($medio) => (bool) $medio->es_portada);
        if (!$portadaConfigurada) {
            return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'presentacion', ] )->with('error', 'Debes configurar una portada antes de finalizar.');
        }
        $productosConfigurados = ($configuracionProductos['configured'] ?? false) === true;
        $productosHabilitados = ($configuracionProductos['enabled'] ?? false) === true;
        if (!$productosConfigurados) {
            return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'productos', ] )->with('error', 'Debes indicar si la actividad ofrecerá productos o servicios.');
        }
        if ($productosHabilitados && $actividad->items->isEmpty() ) {
            return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'productos', ] )->with('error', 'Agrega al menos un producto o servicio antes de finalizar.');
        }
        if ($productosHabilitados) {
            foreach ($actividad->items as $item ) {
                $configDatos = $configuracionesDatos->get((string) $item->id_item_actividad);
                if (!$configDatos) {
                    return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'datos', 'item' => $item->id_item_actividad, ] )->with('error', "Falta configurar los datos solicitados para '{$item->nombre}'.");
                }
                $camposLista = collect($configDatos['fields'] ?? [])->filter(fn ($campo) => ($campo['type'] ?? null) === 'lista' && !empty($campo['options'] ?? []));
                if ($camposLista->isNotEmpty() && !$configuracionesPrecios->has((string) $item->id_item_actividad)) {
                    return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'precios', 'item' => $item->id_item_actividad, ] )->with('error', "Falta configurar los precios y costos para '{$item->nombre}'.");
                }
            }
        }
        if (($configuracionSesiones['configured'] ?? false) !== true) {
            return redirect()->route('admin.actividades.configurar', ['actividad' => $actividad->id_actividad, 'paso' => 'sesiones', ] )->with('error', 'Debes completar la programación antes de finalizar.');
        }
        $documento->configuration = ['configured' => true, 'completed_at' => now()->toIso8601String(), 'updated_at' => now()->toIso8601String(), 'completed_by' => Auth::id(),];
        $documento->save();
        $actividad->actualizado_por = Auth::id();
        $actividad->save();
        return redirect()->route('admin.actividades.index' )->with('success', 'Configuración finalizada correctamente. La actividad ya puede enviarse a revisión.');
    }

    private function rutaStorageDesdeUrl(?string $url): ?string {
        if (!$url) {
            return null;
        }
        $appUrl = rtrim((string) config('app.url'), '/');
        if (Str::startsWith($url, ['http://', 'https://'] ) && !Str::startsWith($url, $appUrl)) {
            return null;
        }
        $path = parse_url($url, PHP_URL_PATH ) ?: $url;
        $path = ltrim($path, '/');
        if (Str::startsWith($path, 'storage/')) {
            $path = substr($path, 8);
        }
        return $path ?: null;
    }

    private function obtenerRangoRealizacion(Actividad $actividad): array {
        if (!$actividad->realizacion_desde || !$actividad->realizacion_hasta ) {
            throw ValidationException::withMessages(['modo_sesiones' => 'Para configurar sesiones primero debes definir el período de realización de la actividad.', ]);
        }
        $desde = Carbon::parse($actividad->realizacion_desde);
        $hasta = Carbon::parse($actividad->realizacion_hasta);
        if ($hasta->lt($desde)) {
            throw ValidationException::withMessages(['modo_sesiones' => 'El período de realización de la actividad no es válido.', ]);
        }
        return ['desde' => $desde, 'hasta' => $hasta,];
    }

    private function prepararSesionUnica(
        Actividad $actividad,
        array $rango,
        array $datos = []
    ): array {
        $existente = $actividad->sesiones()
            ->orderBy('orden')
            ->orderBy('id_sesion')
            ->first();

        if (!$existente && $rango['desde']->lt(now())) {
            throw ValidationException::withMessages([
                'modo_sesiones' => 'No puedes crear una sesión nueva con una fecha de inicio que ya pasó.',
            ]);
        }

        $modalidad = $datos['modalidad']
            ?? ($existente?->enlace_acceso ? 'virtual' : 'presencial');

        if (!in_array($modalidad, ['presencial', 'virtual'], true)) {
            throw ValidationException::withMessages([
                'sesion_unica_modalidad' => 'Selecciona si la sesión principal será presencial o virtual.',
            ]);
        }

        $cupo = $actividad->habilita_inscripcion
            && $actividad->cupo_total !== null
                ? (int) $actividad->cupo_total
                : null;

        if ($modalidad === 'virtual') {
            $enlace = trim((string) ($datos['enlace_acceso'] ?? ''));

            if ($enlace === '') {
                throw ValidationException::withMessages([
                    'sesion_unica_enlace_acceso' => 'Escribe el enlace de acceso para la sesión virtual.',
                ]);
            }

            return [
                'id_sesion' => $existente?->id_sesion,
                'nombre' => 'Sesión principal',
                'fecha_inicio' => $rango['desde']->format('Y-m-d H:i:sP'),
                'fecha_fin' => $rango['hasta']->format('Y-m-d H:i:sP'),
                'id_espacio' => null,
                'ubicacion' => null,
                'recursos' => [],
                'enlace_acceso' => $enlace,
                'cupo' => $cupo,
                'requiere_reserva' => false,
                'obligatoria' => true,
                'modalidad' => 'virtual',
            ];
        }

        $idEspacioSolicitado = !empty($datos['id_espacio'])
            ? (int) $datos['id_espacio']
            : null;

        $ubicacionSolicitada = isset($datos['ubicacion'])
            ? trim((string) $datos['ubicacion'])
            : '';

        $idEspacio = $idEspacioSolicitado;

        if (!$idEspacio && $existente?->id_espacio) {
            $idExistente = (int) $existente->id_espacio;
            $espacioExistente = Espacio::query()->find($idExistente);

            if (
                $espacioExistente
                && $espacioExistente->activo
                && $espacioExistente->permite_actividades
                && $this->espacioPermitidoParaSesion($actividad, $idExistente)
            ) {
                $idEspacio = $idExistente;
            }
        }

        if (!$idEspacio && $actividad->id_espacio) {
            $general = Espacio::query()->find((int) $actividad->id_espacio);

            if (
                $general
                && $general->activo
                && $general->permite_actividades
            ) {
                $idEspacio = (int) $general->id_espacio;
            }
        }

        if ($idEspacio) {
            $espacio = Espacio::query()->find($idEspacio);

            if (!$espacio || !$espacio->activo) {
                throw ValidationException::withMessages([
                    'sesion_unica_id_espacio' =>
                        'La ubicación presencial de la sesión principal ya no está disponible.',
                ]);
            }

            if (!$espacio->permite_actividades) {
                throw ValidationException::withMessages([
                    'sesion_unica_id_espacio' =>
                        'Selecciona un espacio habilitado para recibir actividades, como un auditorio, aula o centro de cómputo.',
                ]);
            }

            if (!$this->espacioPermitidoParaSesion($actividad, $idEspacio)) {
                throw ValidationException::withMessages([
                    'sesion_unica_id_espacio' =>
                        'El espacio de la sesión principal debe pertenecer al lugar general de la actividad.',
                ]);
            }

            if (
                $espacio->capacidad !== null
                && $cupo !== null
                && $cupo > (int) $espacio->capacidad
            ) {
                throw ValidationException::withMessages([
                    'sesion_unica_id_espacio' =>
                        "El cupo general no puede superar la capacidad del espacio seleccionado ({$espacio->capacidad} personas).",
                ]);
            }
        }

        $ubicacion = $idEspacio
            ? null
            : ($ubicacionSolicitada !== ''
                ? $ubicacionSolicitada
                : (!$actividad->id_espacio ? ($actividad->ubicacion_externa ?: ($existente?->ubicacion ?? null)) : null));

        if (!$idEspacio && !$ubicacion) {
            throw ValidationException::withMessages([
                'sesion_unica_id_espacio' =>
                    'Selecciona un espacio habilitado dentro del lugar general para la sesión presencial.',
            ]);
        }

        return [
            'id_sesion' => $existente?->id_sesion,
            'nombre' => 'Sesión principal',
            'fecha_inicio' => $rango['desde']->format('Y-m-d H:i:sP'),
            'fecha_fin' => $rango['hasta']->format('Y-m-d H:i:sP'),
            'id_espacio' => $idEspacio,
            'ubicacion' => $ubicacion,
            'recursos' => $existente && !$existente->enlace_acceso
                ? $existente->recursos()
                    ->get()
                    ->map(fn ($uso) => [
                        'id_recurso' => (int) $uso->id_recurso,
                        'cantidad' => (int) $uso->cantidad,
                        'observacion' => $uso->observacion,
                    ])
                    ->all()
                : [],
            'enlace_acceso' => null,
            'cupo' => $cupo,
            'requiere_reserva' => false,
            'obligatoria' => true,
            'modalidad' => 'presencial',
        ];
    }

    private function prepararSesionesMultiples(
        Actividad $actividad,
        array $sesiones,
        array $rango
    ): array {
        $cupoGeneral = $actividad->habilita_inscripcion
            && $actividad->cupo_total !== null
                ? (int) $actividad->cupo_total
                : null;

        $preparadas = [];
        $ahora = now();

        foreach ($sesiones as $indice => $datos) {
            $nombre = trim((string) ($datos['nombre'] ?? ''));

            if ($nombre === '') {
                throw ValidationException::withMessages([
                    'sesiones' => 'Cada sesión debe tener un nombre.',
                ]);
            }

            if (empty($datos['fecha_inicio'])) {
                throw ValidationException::withMessages([
                    'sesiones' => "La sesión '{$nombre}' debe tener una fecha y hora de inicio.",
                ]);
            }

            $inicio = Carbon::parse($datos['fecha_inicio']);
            $fin = !empty($datos['fecha_fin'])
                ? Carbon::parse($datos['fecha_fin'])
                : null;

            if ($fin && $fin->lt($inicio)) {
                throw ValidationException::withMessages([
                    'sesiones' => "La finalización de '{$nombre}' no puede ser anterior a su inicio.",
                ]);
            }

            if (
                $inicio->lt($rango['desde'])
                || $inicio->gt($rango['hasta'])
            ) {
                throw ValidationException::withMessages([
                    'sesiones' => "La sesión '{$nombre}' debe comenzar dentro del período de realización de la actividad.",
                ]);
            }

            if ($fin && $fin->gt($rango['hasta'])) {
                throw ValidationException::withMessages([
                    'sesiones' => "La sesión '{$nombre}' debe finalizar dentro del período de realización de la actividad.",
                ]);
            }

            $idSesion = !empty($datos['id_sesion'])
                ? (int) $datos['id_sesion']
                : null;

            if ($idSesion) {
                $pertenece = $actividad->sesiones()
                    ->where('id_sesion', $idSesion)
                    ->exists();

                if (!$pertenece) {
                    throw ValidationException::withMessages([
                        'sesiones' => 'Una de las sesiones ya no existe o no pertenece a esta actividad.',
                    ]);
                }
            } elseif ($inicio->lt($ahora)) {
                throw ValidationException::withMessages([
                    'sesiones' => "No puedes crear '{$nombre}' con una fecha de inicio que ya pasó.",
                ]);
            }

            $cupo = array_key_exists('cupo', $datos)
                && $datos['cupo'] !== null
                && $datos['cupo'] !== ''
                    ? (int) $datos['cupo']
                    : $cupoGeneral;

            if (
                $cupoGeneral !== null
                && $cupo !== null
                && $cupo > $cupoGeneral
            ) {
                throw ValidationException::withMessages([
                    'sesiones' => "El cupo de '{$nombre}' no puede superar el cupo general de {$cupoGeneral} personas.",
                ]);
            }

            $modalidad = $datos['modalidad']
                ?? (!empty($datos['enlace_acceso']) ? 'virtual' : 'presencial');

            if (!in_array($modalidad, ['presencial', 'virtual'], true)) {
                throw ValidationException::withMessages([
                    'sesiones' => "Selecciona si '{$nombre}' será presencial o virtual.",
                ]);
            }

            $idEspacio = null;
            $ubicacionExterna = null;
            $enlaceAcceso = null;
            $recursos = [];

            if ($modalidad === 'virtual') {
                $enlaceAcceso = trim((string) ($datos['enlace_acceso'] ?? ''));

                if ($enlaceAcceso === '') {
                    throw ValidationException::withMessages([
                        'sesiones' => "Escribe el enlace de acceso para la sesión virtual '{$nombre}'.",
                    ]);
                }
            } else {
                $idEspacio = !empty($datos['id_espacio'])
                    ? (int) $datos['id_espacio']
                    : null;

                $ubicacionExterna = !$idEspacio
                    && isset($datos['ubicacion'])
                    && trim((string) $datos['ubicacion']) !== ''
                        ? trim((string) $datos['ubicacion'])
                        : null;

                if ($idEspacio) {
                    $espacio = Espacio::query()->find($idEspacio);

                    if (!$espacio) {
                        throw ValidationException::withMessages([
                            'sesiones' => "El espacio seleccionado para '{$nombre}' ya no existe.",
                        ]);
                    }

                    if (!$espacio->activo) {
                        throw ValidationException::withMessages([
                            'sesiones' => "El espacio seleccionado para '{$nombre}' está inactivo.",
                        ]);
                    }

                    if (!$espacio->permite_actividades) {
                        throw ValidationException::withMessages([
                            'sesiones' => "El espacio seleccionado para '{$nombre}' es un contenedor general y no está habilitado para recibir actividades directamente. Selecciona un aula, auditorio u otro espacio habilitado.",
                        ]);
                    }

                    if (!$this->espacioPermitidoParaSesion($actividad, $idEspacio)) {
                        throw ValidationException::withMessages([
                            'sesiones' => "El espacio seleccionado para '{$nombre}' no pertenece al lugar general de la actividad.",
                        ]);
                    }

                    if (
                        $espacio->capacidad !== null
                        && $cupo !== null
                        && $cupo > (int) $espacio->capacidad
                    ) {
                        throw ValidationException::withMessages([
                            'sesiones' => "El cupo de '{$nombre}' no puede superar la capacidad del espacio seleccionado ({$espacio->capacidad} personas).",
                        ]);
                    }
                }

                if (!$idEspacio && !$ubicacionExterna) {
                    throw ValidationException::withMessages([
                        'sesiones' => "Selecciona un espacio para la sesión presencial '{$nombre}'.",
                    ]);
                }

                $recursos = $this->prepararRecursosSesion(
                    $datos['recursos'] ?? [],
                    $idEspacio
                );
            }

            $preparadas[] = [
                'id_sesion' => $idSesion,
                'nombre' => $nombre,
                'fecha_inicio' => $inicio->format('Y-m-d H:i:sP'),
                'fecha_fin' => $fin?->format('Y-m-d H:i:sP'),
                'id_espacio' => $idEspacio,
                'ubicacion' => $ubicacionExterna,
                'enlace_acceso' => $enlaceAcceso,
                'cupo' => $cupo,
                'requiere_reserva' => (bool) ($datos['requiere_reserva'] ?? false),
                'obligatoria' => (bool) ($datos['obligatoria'] ?? false),
                'recursos' => $recursos,
                'modalidad' => $modalidad,
                '_indice' => $indice,
            ];
        }

        return $preparadas;
    }

    private function espacioPermitidoParaSesion(Actividad $actividad, int $idEspacio): bool {
        if (!$actividad->id_espacio) {
            return true;
        }
        $idGeneral = (int) $actividad->id_espacio;
        if ($idEspacio === $idGeneral) {
            return true;
        }
        $actual = Espacio::query()->find($idEspacio);
        $visitados = [];
        while ($actual && $actual->id_espacio_contenedor) {
            $idActual = (int) $actual->id_espacio;
            if (in_array($idActual, $visitados, true)) {
                return false;
            }
            $visitados[] = $idActual;
            $idContenedor = (int) $actual->id_espacio_contenedor;
            if ($idContenedor === $idGeneral) {
                return true;
            }
            $actual = Espacio::query()->find($idContenedor);
        }
        return false;
    }

    private function prepararRecursosSesion(array $recursos,?int $idEspacio): array {
        $preparados = [];
        foreach ($recursos as $idRecurso => $datos) {
            if (!(bool) ($datos['seleccionado'] ?? false)) continue;
            $recurso = Recurso::query()->find((int) $idRecurso);
            if (!$recurso) throw ValidationException::withMessages(['sesiones' => 'Uno de los recursos seleccionados ya no existe.']);
            $disponible = (bool) $recurso->es_movil;
            if ($idEspacio) $disponible = $disponible || $recurso->espacios()->where('tbl_espacios.id_espacio',$idEspacio)->exists();
            if (!$disponible) throw ValidationException::withMessages(['sesiones' => "El recurso '{$recurso->nombre}' no está disponible para el espacio seleccionado."]);
            $cantidad = max(1,(int) ($datos['cantidad'] ?? 1));
            if ($idEspacio && !$recurso->es_movil) {
                $asignacion = $recurso->espacios()->where('tbl_espacios.id_espacio',$idEspacio)->first();
                $cantidadDisponible = $asignacion ? (int) $asignacion->pivot->cantidad : 0;
                if ($cantidad > $cantidadDisponible) throw ValidationException::withMessages(['sesiones' => "La cantidad solicitada de '{$recurso->nombre}' supera las {$cantidadDisponible} unidades registradas en el espacio."]);
            }
            $preparados[] = ['id_recurso' => (int) $recurso->id_recurso,'cantidad' => $cantidad,'observacion' => !empty($datos['observacion']) ? trim((string) $datos['observacion']) : null];
        }
        return $preparados;
    }

    private function sincronizarRecursosSesion(Actividad $actividad,SesionActividad $sesion,array $recursos): void {
        RecursoActividad::query()->where('id_actividad',$actividad->id_actividad)->where('id_sesion',$sesion->id_sesion)->delete();
        foreach ($recursos as $datos) {
            RecursoActividad::query()->create(['id_actividad' => $actividad->id_actividad,'id_sesion' => $sesion->id_sesion,'id_recurso' => $datos['id_recurso'],'cantidad' => $datos['cantidad'],'observacion' => $datos['observacion']]);
        }
    }

    private function detectarSolapamientos(array $sesiones): array {
        $solapamientos = [];
        $total = count($sesiones);
        for ($i = 0;
            $i < $total;
            $i++ ) {
            for ($j = $i + 1;
                $j < $total;
                $j++ ) {
                $a = $sesiones[$i];
                $b = $sesiones[$j];
                $inicioA = Carbon::parse($a['fecha_inicio']);
                $finA = !empty($a['fecha_fin'] ) ? Carbon::parse($a['fecha_fin'] ) : null;
                $inicioB = Carbon::parse($b['fecha_inicio']);
                $finB = !empty($b['fecha_fin'] ) ? Carbon::parse($b['fecha_fin'] ) : null;
                $seCruzan = false;
                if ($finA && $finB ) {
                    $seCruzan = $inicioA->lt($finB) && $finA->gt($inicioB);
                } elseif (
                    !$finA && !$finB ) {
                    $seCruzan = $inicioA->eq($inicioB);
                } elseif (
                    !$finA && $finB ) {
                    $seCruzan = $inicioA->eq($inicioB) || ($inicioA->gt($inicioB) && $inicioA->lt($finB));
                } elseif (
                    $finA && !$finB ) {
                    $seCruzan = $inicioB->eq($inicioA) || ($inicioB->gt($inicioA) && $inicioB->lt($finA));
                }
                if ($seCruzan) {
                    $solapamientos[] = "{$a['nombre']} con {$b['nombre']}";
                }
            }
        }
        return array_values(array_unique($solapamientos ));
    }

    private function generarCombinaciones(array $camposSeleccionados): array {
        $combinaciones = [[]];
        foreach ($camposSeleccionados as $clave => $campo ) {
            $nuevas = [];
            foreach ($combinaciones as $combinacion ) {
                foreach ($campo['options'] ?? [] as $opcion ) {
                    $nueva = $combinacion;
                    $nueva[$clave] = (string) $opcion;
                    $nuevas[] = $nueva;
                }
            }
            $combinaciones = $nuevas;
        }
        return $combinaciones;
    }

    private function establecerConfiguracionPrecio(
        ActivityContent $documento,
        ItemActividad $item,
        bool $enabled,
        array $camposClave,
        array $reglas
    ): void {
        $configuraciones = collect(
            $this->normalizarMongo($documento->pricing_configuration ?? [])
        )
            ->reject(
                fn ($configuracion) =>
                    (int) ($configuracion['id_item_pg'] ?? 0)
                    === (int) $item->id_item_actividad
            )
            ->values()
            ->all();

        $configuraciones[] = [
            'id_item_pg' => (int) $item->id_item_actividad,
            'enabled' => $enabled,
            'pricing_fields' => $camposClave,
            'rules' => $reglas,
            'updated_at' => now()->toIso8601String(),
        ];

        $documento->pricing_configuration = $configuraciones;
    }

    private function verificarAtributosMongoVariantes(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        $encontrados = VariantAttribute::whereIn('id_variante_pg', $ids)
            ->get()
            ->pluck('id_variante_pg')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        sort($encontrados);
        $esperados = array_values(array_unique(array_map('intval', $ids)));
        sort($esperados);

        if ($encontrados !== $esperados) {
            throw new \RuntimeException('No se pudieron verificar todos los atributos de las variantes en MongoDB.');
        }
    }

    private function eliminarAtributosMongoVariantes(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        try {
            VariantAttribute::whereIn('id_variante_pg', $ids)->delete();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function capturarEstadoMongo(ActivityContent $documento, array $campos): array
    {
        $estado = [];

        foreach ($campos as $campo) {
            $estado[$campo] = $this->normalizarMongo($documento->{$campo} ?? []);
        }

        return $estado;
    }

    private function restaurarEstadoMongo(Actividad $actividad, array $estado): void
    {
        try {
            $documento = $this->obtenerDocumentoActividad($actividad);

            foreach ($estado as $campo => $valor) {
                $documento->{$campo} = $valor;
            }

            $documento->save();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function eliminarVariantesComerciales(ItemActividad $item): void
    {
        $ids = VarianteItem::query()
            ->where('id_item_actividad', $item->id_item_actividad)
            ->pluck('id_variante_item')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            VarianteItem::whereIn('id_variante_item', $ids)->delete();
        });

        $this->eliminarAtributosMongoVariantes($ids);
    }

    private function procesarOpciones(string $texto): array {
        $partes = preg_split('/[\r\n,]+/', $texto);
        $opciones = [];
        foreach ($partes as $parte) {
            $opcion = trim($parte);
            if ($opcion !== '' && !in_array($opcion, $opciones, true) ) {
                $opciones[] = $opcion;
            }
        }
        return $opciones;
    }

    private function obtenerDocumentoActividad(Actividad $actividad): ActivityContent {
        $documento = ActivityContent::where('id_actividad_pg', (int) $actividad->id_actividad )->first();
        if (!$documento) {
            $documento = new ActivityContent();
            $documento->id_actividad_pg = (int) $actividad->id_actividad;
        }
        return $documento;
    }

    private function invalidarConfiguracionGeneral(ActivityContent $documento): void {
        $configuracion = $this->normalizarMongo($documento->configuration ?? []);
        if (($configuracion['configured'] ?? false) !== true) {
            return;
        }
        $documento->configuration = ['configured' => false, 'completed_at' => null, 'updated_at' => now()->toIso8601String(), 'completed_by' => null,];
    }

    private function validarProductosHabilitados(Actividad $actividad): void {
        $documento = ActivityContent::where('id_actividad_pg', (int) $actividad->id_actividad )->first();
        $configuracion = $this->normalizarMongo($documento?->product_configuration ?? []);
        if (($configuracion['configured'] ?? false) && !($configuracion['enabled'] ?? false) ) {
            throw ValidationException::withMessages(['productos' => 'Esta actividad fue configurada sin productos ni servicios.', ]);
        }
    }

    private function validarItemPerteneceActividad(Actividad $actividad,ItemActividad $item): void {
        abort_unless((int) $item->id_actividad === (int) $actividad->id_actividad, 404);
    }

    private function marcarComoBorradorSiAprobada(Actividad $actividad): void {
        if ($actividad->estado_publicacion !== 'aprobada') {
            return;
        }

        $actividad->estado_publicacion = 'borrador';
        $actividad->actualizado_por = Auth::id();
        $actividad->save();
    }

    private function validarActividadEditable(Actividad $actividad): void {
        abort_unless(in_array($actividad->estado_publicacion, ['borrador', 'cambios_solicitados', 'aprobada', ], true), 403);
    }

    private function normalizarMongo(mixed $valor): array {
        if (empty($valor)) {
            return [];
        }
        return json_decode(json_encode($valor), true) ?? [];
    }
}
