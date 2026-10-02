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
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ActividadConfiguracionController extends Controller
{
    public function show(Request $request, Actividad $actividad)
    {
        $this->validarActividadEditable($actividad);

        $actividad->load([
            'categoria',
            'etiquetas',
            'medios',
            'items.variantes',
            'sesiones',
            'formularios.campos',
            'recursos',
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

        $presentacionCompleta = $actividad->medios
            ->whereNull('id_item_actividad')
            ->whereNull('id_sesion')
            ->contains(
                fn ($medio) => (bool) $medio->es_portada
            );

        $productosConfigurados =
            ($configuracionProductos['configured'] ?? false) === true;

        $productosHabilitados =
            ($configuracionProductos['enabled'] ?? false) === true;

        if (
            !$productosConfigurados
            && $actividad->items->isNotEmpty()
        ) {
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

        if (
            $productosConfigurados
            && !$productosHabilitados
        ) {
            $datosCompletos = true;
            $preciosCompletos = true;
        }

        if (
            $productosConfigurados
            && $productosHabilitados
            && $actividad->items->isNotEmpty()
        ) {
            $datosCompletos = $actividad->items->every(
                function ($item) use ($configuracionesDatos) {
                    return $configuracionesDatos->has(
                        (string) $item->id_item_actividad
                    );
                }
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
            [
                'presentacion',
                'productos',
                'datos',
                'precios',
                'sesiones',
            ] as $pasoEvaluado
        ) {
            if (!($estadoPasos[$pasoEvaluado] ?? false)) {
                $primerPendiente = $pasoEvaluado;
                break;
            }
        }

        $pasoSolicitado = $request->query('paso');

        if (
            !$pasoSolicitado
            || !in_array(
                $pasoSolicitado,
                $pasosValidos,
                true
            )
        ) {
            $paso = $primerPendiente;
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

            abort_unless(
                $itemSeleccionado,
                404
            );
        }

        if (
            $itemSeleccionado
            && !in_array(
                $paso,
                ['datos', 'precios'],
                true
            )
        ) {
            $itemSeleccionado = null;
        }

        $configDatosSeleccionado = null;
        $configPrecioSeleccionado = null;

        if ($itemSeleccionado) {
            $configDatosSeleccionado =
                $configuracionesDatos->get(
                    (string) $itemSeleccionado->id_item_actividad
                );

            $configPrecioSeleccionado =
                $configuracionesPrecios->get(
                    (string) $itemSeleccionado->id_item_actividad
                );
        }

        $modoSesiones =
            $configuracionSesiones['mode'] ?? null;

        if (
            !in_array(
                $modoSesiones,
                ['ninguna', 'unica', 'multiples'],
                true
            )
        ) {
            if (
                ($configuracionSesiones['configured'] ?? false)
                === true
            ) {
                if (
                    ($configuracionSesiones['enabled'] ?? false)
                    === false
                ) {
                    $modoSesiones = 'ninguna';
                } else {
                    $modoSesiones =
                        $actividad->sesiones->count() <= 1
                            ? 'unica'
                            : 'multiples';
                }
            } else {
                $modoSesiones = null;
            }
        }

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
                'modoSesiones'
            )
        );
    }

    public function guardarConfiguracionProductos(
        Request $request,
        Actividad $actividad
    ) {
        $this->validarActividadEditable($actividad);

        $validated = $request->validate([
            'ofrece_productos' => [
                'required',
                'boolean',
            ],
        ]);

        $ofreceProductos =
            (bool) $validated['ofrece_productos'];

        if (
            !$ofreceProductos
            && $actividad->items()->exists()
        ) {
            throw ValidationException::withMessages([
                'ofrece_productos' =>
                    'La actividad ya tiene productos o servicios agregados. Elimínalos antes de indicar que no ofrecerá ninguno.',
            ]);
        }

        try {
            $documento =
                $this->obtenerDocumentoActividad(
                    $actividad
                );

            $documento->product_configuration = [
                'configured' => true,
                'enabled' => $ofreceProductos,
                'updated_at' => now()->toIso8601String(),
            ];

            if (!$ofreceProductos) {
                $documento->purchase_fields = [];
                $documento->pricing_configuration = [];
            }

            $this->invalidarConfiguracionGeneral(
                $documento
            );

            $documento->save();

            $actividad->actualizado_por =
                Auth::id();

            $actividad->save();

            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' =>
                            $ofreceProductos
                                ? 'productos'
                                : 'sesiones',
                    ]
                )
                ->with(
                    'success',
                    $ofreceProductos
                        ? 'Ahora puedes agregar los productos o servicios que ofrecerá la actividad.'
                        : 'La actividad quedó configurada sin productos ni servicios. Puedes continuar con su programación.'
                );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No se pudo guardar esta decisión.'
                );
        }
    }

    public function guardarDatosPedido(
        Request $request,
        Actividad $actividad,
        ItemActividad $item
    ) {
        $this->validarItemPerteneceActividad(
            $actividad,
            $item
        );

        $this->validarActividadEditable(
            $actividad
        );

        $this->validarProductosHabilitados(
            $actividad
        );

        $validated = $request->validate([
            'requiere_datos' => [
                'required',
                'boolean',
            ],
            'campos' => [
                'nullable',
                'array',
                'max:20',
            ],
            'campos.*.label' => [
                'required_with:campos',
                'string',
                'max:100',
            ],
            'campos.*.type' => [
                'required_with:campos',
                Rule::in([
                    'texto',
                    'numero',
                    'lista',
                ]),
            ],
            'campos.*.required' => [
                'nullable',
                'boolean',
            ],
            'campos.*.options' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $requiereDatos =
            (bool) $validated['requiere_datos'];

        $camposProcesados = [];

        if ($requiereDatos) {
            $campos =
                $validated['campos'] ?? [];

            if (empty($campos)) {
                throw ValidationException::withMessages([
                    'campos' =>
                        'Agrega al menos un dato que deba proporcionar el comprador.',
                ]);
            }

            $clavesUsadas = [];

            foreach ($campos as $campo) {
                $nombre =
                    trim($campo['label']);

                $tipo =
                    $campo['type'];

                $clave =
                    Str::slug(
                        $nombre,
                        '_'
                    );

                if ($clave === '') {
                    throw ValidationException::withMessages([
                        'campos' =>
                            'Todos los campos deben tener un nombre válido.',
                    ]);
                }

                if (
                    in_array(
                        $clave,
                        $clavesUsadas,
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'campos' =>
                            "El dato '{$nombre}' está repetido.",
                    ]);
                }

                $clavesUsadas[] =
                    $clave;

                $opciones = [];

                if ($tipo === 'lista') {
                    $opciones =
                        $this->procesarOpciones(
                            $campo['options'] ?? ''
                        );

                    if (empty($opciones)) {
                        throw ValidationException::withMessages([
                            'campos' =>
                                "Agrega al menos una opción para '{$nombre}'.",
                        ]);
                    }
                }

                $camposProcesados[] = [
                    'key' => $clave,
                    'label' => $nombre,
                    'type' => $tipo,
                    'required' =>
                        (bool) (
                            $campo['required']
                            ?? false
                        ),
                    'options' => $opciones,
                ];
            }
        }

        try {
            $documento =
                $this->obtenerDocumentoActividad(
                    $actividad
                );

            $configuraciones = collect(
                $this->normalizarMongo(
                    $documento->purchase_fields
                    ?? []
                )
            )
                ->reject(
                    fn ($configuracion) =>
                        (int) (
                            $configuracion['id_item_pg']
                            ?? 0
                        )
                        ===
                        (int) $item->id_item_actividad
                )
                ->values()
                ->all();

            $configuraciones[] = [
                'id_item_pg' =>
                    (int) $item->id_item_actividad,
                'enabled' =>
                    $requiereDatos,
                'fields' =>
                    $camposProcesados,
                'updated_at' =>
                    now()->toIso8601String(),
            ];

            $documento->purchase_fields =
                $configuraciones;

            $this->invalidarConfiguracionGeneral(
                $documento
            );

            $documento->save();

            $actividad->actualizado_por =
                Auth::id();

            $actividad->save();

            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' => 'datos',
                    ]
                )
                ->with(
                    'success',
                    'La configuración fue guardada correctamente.'
                );
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No se pudo guardar la configuración.'
                );
        }
    }
        public function guardarAjustesPrecio(
        Request $request,
        Actividad $actividad,
        ItemActividad $item
    ) {
        $this->validarItemPerteneceActividad(
            $actividad,
            $item
        );

        $this->validarActividadEditable(
            $actividad
        );

        $this->validarProductosHabilitados(
            $actividad
        );

        $documento = ActivityContent::where(
            'id_actividad_pg',
            (int) $actividad->id_actividad
        )->first();

        $configuracionesDatos = collect(
            $this->normalizarMongo(
                $documento?->purchase_fields ?? []
            )
        );

        $configDatosItem =
            $configuracionesDatos->first(
                fn ($configuracion) =>
                    (int) (
                        $configuracion['id_item_pg']
                        ?? 0
                    )
                    ===
                    (int) $item->id_item_actividad
            );

        if (!$configDatosItem) {
            return back()->with(
                'error',
                'Primero debes configurar qué datos se solicitarán al comprador.'
            );
        }

        $camposLista = collect(
            $configDatosItem['fields'] ?? []
        )->filter(
            fn ($campo) =>
                ($campo['type'] ?? null) === 'lista'
                && !empty($campo['options'] ?? [])
        )->values();

        if ($camposLista->isEmpty()) {
            return back()->with(
                'error',
                'Este producto no tiene listas de opciones que puedan modificar el precio o costo.'
            );
        }

        $validated = $request->validate([
            'tiene_cambios' => [
                'required',
                'boolean',
            ],
            'campos_clave' => [
                'nullable',
                'array',
                'max:10',
            ],
            'campos_clave.*' => [
                'string',
                'max:100',
            ],
            'ajustes' => [
                'nullable',
                'array',
                'max:200',
            ],
            'ajustes.*.campo' => [
                'required_with:ajustes',
                'string',
                'max:100',
            ],
            'ajustes.*.opcion' => [
                'required_with:ajustes',
                'string',
                'max:200',
            ],
            'ajustes.*.aumento_precio' => [
                'required_with:ajustes',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],
            'ajustes.*.aumento_costo' => [
                'required_with:ajustes',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],
        ]);

        $tieneCambios =
            (bool) $validated['tiene_cambios'];

        if (!$tieneCambios) {
            try {
                $this->eliminarVariantesComerciales(
                    $item
                );

                $this->guardarConfiguracionPrecio(
                    $actividad,
                    $item,
                    false,
                    [],
                    []
                );

                $documento =
                    $this->obtenerDocumentoActividad(
                        $actividad
                    );

                $this->invalidarConfiguracionGeneral(
                    $documento
                );

                $documento->save();

                $actividad->actualizado_por =
                    Auth::id();

                $actividad->save();

                return redirect()
                    ->route(
                        'admin.actividades.configurar',
                        [
                            'actividad' =>
                                $actividad->id_actividad,
                            'paso' => 'precios',
                        ]
                    )
                    ->with(
                        'success',
                        'Todas las opciones utilizarán el precio y costo base.'
                    );
            } catch (\Throwable $e) {
                report($e);

                return back()->with(
                    'error',
                    'No se pudo guardar la configuración de precios.'
                );
            }
        }

        $camposClave = array_values(
            array_unique(
                array_filter(
                    $validated['campos_clave'] ?? []
                )
            )
        );

        if (empty($camposClave)) {
            throw ValidationException::withMessages([
                'campos_clave' =>
                    'Selecciona al menos una lista que cambie el precio o costo.',
            ]);
        }

        $camposSeleccionados = [];

        foreach ($camposClave as $clave) {
            $campo = $camposLista->first(
                fn ($campo) =>
                    ($campo['key'] ?? null) === $clave
            );

            if (!$campo) {
                throw ValidationException::withMessages([
                    'campos_clave' =>
                        'Una de las listas seleccionadas ya no es válida.',
                ]);
            }

            $camposSeleccionados[$clave] =
                $campo;
        }

        $ajustes =
            $validated['ajustes'] ?? [];

        if (empty($ajustes)) {
            throw ValidationException::withMessages([
                'ajustes' =>
                    'Agrega al menos una opción que aumente el precio o costo.',
            ]);
        }

        $reglas = [];
        $reglasUsadas = [];

        foreach ($ajustes as $ajuste) {
            $campoClave =
                trim((string) $ajuste['campo']);

            $opcion =
                trim((string) $ajuste['opcion']);

            if (
                !isset(
                    $camposSeleccionados[$campoClave]
                )
            ) {
                throw ValidationException::withMessages([
                    'ajustes' =>
                        'Una de las opciones pertenece a una lista que no seleccionaste.',
                ]);
            }

            $opcionesPermitidas = collect(
                $camposSeleccionados[$campoClave]['options']
                ?? []
            )
                ->map(
                    fn ($valor) =>
                        (string) $valor
                )
                ->values()
                ->all();

            if (
                !in_array(
                    $opcion,
                    $opcionesPermitidas,
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'ajustes' =>
                        "La opción '{$opcion}' ya no pertenece a la lista seleccionada.",
                ]);
            }

            $identificador =
                $campoClave . '|' . $opcion;

            if (
                in_array(
                    $identificador,
                    $reglasUsadas,
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'ajustes' =>
                        "La opción '{$opcion}' está repetida.",
                ]);
            }

            $reglasUsadas[] =
                $identificador;

            $aumentoPrecio =
                round(
                    (float) $ajuste['aumento_precio'],
                    2
                );

            $aumentoCosto =
                round(
                    (float) $ajuste['aumento_costo'],
                    2
                );

            if (
                $aumentoPrecio === 0.0
                && $aumentoCosto === 0.0
            ) {
                throw ValidationException::withMessages([
                    'ajustes' =>
                        "La opción '{$opcion}' no tiene ningún aumento.",
                ]);
            }

            $reglas[] = [
                'field_key' => $campoClave,
                'field_label' =>
                    $camposSeleccionados[$campoClave]['label']
                    ?? Str::headline($campoClave),
                'option' => $opcion,
                'price_increment' =>
                    $aumentoPrecio,
                'cost_increment' =>
                    $aumentoCosto,
            ];
        }

        foreach ($camposClave as $campoClave) {
            $tieneRegla =
                collect($reglas)->contains(
                    fn ($regla) =>
                        $regla['field_key']
                        === $campoClave
                );

            if (!$tieneRegla) {
                throw ValidationException::withMessages([
                    'ajustes' =>
                        "Agrega al menos una opción que cambie para '{$camposSeleccionados[$campoClave]['label']}'.",
                ]);
            }
        }

        $combinaciones =
            $this->generarCombinaciones(
                $camposSeleccionados
            );

        if (count($combinaciones) > 500) {
            throw ValidationException::withMessages([
                'campos_clave' =>
                    'La combinación generaría más de 500 posibilidades. Reduce las listas seleccionadas.',
            ]);
        }

        $precioBase =
            round(
                (float) $item->precio,
                2
            );

        $costoBase =
            round(
                (float) $item->costo_referencia,
                2
            );

        $variantesPreparadas = [];

        foreach ($combinaciones as $combinacion) {
            $aumentoPrecioTotal = 0;
            $aumentoCostoTotal = 0;

            foreach (
                $combinacion
                as $campoClave => $opcion
            ) {
                foreach ($reglas as $regla) {
                    if (
                        $regla['field_key']
                            === $campoClave
                        && $regla['option']
                            === $opcion
                    ) {
                        $aumentoPrecioTotal +=
                            $regla['price_increment'];

                        $aumentoCostoTotal +=
                            $regla['cost_increment'];
                    }
                }
            }

            $precioFinal =
                round(
                    $precioBase
                    + $aumentoPrecioTotal,
                    2
                );

            $costoFinal =
                round(
                    $costoBase
                    + $aumentoCostoTotal,
                    2
                );

            $partesNombre = [];

            foreach (
                $combinacion
                as $campoClave => $opcion
            ) {
                $partesNombre[] =
                    (
                        $camposSeleccionados[$campoClave]['label']
                        ?? Str::headline($campoClave)
                    )
                    . ': '
                    . $opcion;
            }

            $variantesPreparadas[] = [
                'nombre' =>
                    implode(
                        ' / ',
                        $partesNombre
                    ),
                'precio' =>
                    $aumentoPrecioTotal > 0
                        ? $precioFinal
                        : null,
                'costo' =>
                    $aumentoCostoTotal > 0
                        ? $costoFinal
                        : null,
                'atributos' =>
                    $combinacion,
            ];
        }

        $idsVariantesAnteriores =
            $item->variantes()
                ->pluck('id_variante_item')
                ->map(
                    fn ($id) => (int) $id
                )
                ->values()
                ->all();

        $variantesNuevas = [];
        $idsVariantesNuevas = [];

        try {
            DB::transaction(
                function () use (
                    $item,
                    $variantesPreparadas,
                    &$variantesNuevas,
                    &$idsVariantesNuevas
                ) {
                    $ordenInicial =
                        (
                            $item->variantes()
                                ->max('orden')
                            ?? -1
                        ) + 1;

                    foreach (
                        $variantesPreparadas
                        as $indice => $datos
                    ) {
                        $variante =
                            new VarianteItem();

                        $variante->id_item_actividad =
                            $item->id_item_actividad;

                        $variante->sku = null;

                        $variante->nombre_variante =
                            Str::limit(
                                $datos['nombre'],
                                180,
                                ''
                            );

                        $variante->precio =
                            $datos['precio'];

                        $variante->costo_referencia =
                            $datos['costo'];

                        $variante->stock_total =
                            null;

                        $variante->orden =
                            $ordenInicial + $indice;

                        $variante->activo =
                            true;

                        $variante->save();

                        $variantesNuevas[] = [
                            'variante' =>
                                $variante,
                            'atributos' =>
                                $datos['atributos'],
                        ];

                        $idsVariantesNuevas[] =
                            (int) $variante
                                ->id_variante_item;
                    }
                }
            );

            foreach (
                $variantesNuevas
                as $registro
            ) {
                $atributo =
                    new VariantAttribute();

                $atributo->id_variante_pg =
                    (int) $registro['variante']
                        ->id_variante_item;

                $atributo->attributes =
                    $registro['atributos'];

                $atributo->save();
            }

            $this->guardarConfiguracionPrecio(
                $actividad,
                $item,
                true,
                $camposClave,
                $reglas
            );

            if (
                !empty(
                    $idsVariantesAnteriores
                )
            ) {
                VariantAttribute::whereIn(
                    'id_variante_pg',
                    $idsVariantesAnteriores
                )->delete();

                VarianteItem::whereIn(
                    'id_variante_item',
                    $idsVariantesAnteriores
                )->delete();
            }

            $documento =
                $this->obtenerDocumentoActividad(
                    $actividad
                );

            $this->invalidarConfiguracionGeneral(
                $documento
            );

            $documento->save();

            $actividad->actualizado_por =
                Auth::id();

            $actividad->save();

            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' => 'precios',
                    ]
                )
                ->with(
                    'success',
                    'Los aumentos de precio y costo fueron guardados correctamente.'
                );
        } catch (\Throwable $e) {
            report($e);

            if (
                !empty(
                    $idsVariantesNuevas
                )
            ) {
                try {
                    VariantAttribute::whereIn(
                        'id_variante_pg',
                        $idsVariantesNuevas
                    )->delete();

                    VarianteItem::whereIn(
                        'id_variante_item',
                        $idsVariantesNuevas
                    )->delete();
                } catch (\Throwable $rollbackError) {
                    report(
                        $rollbackError
                    );
                }
            }

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No se pudo guardar la configuración de precios.'
                );
        }
    }

    public function guardarSesiones(
        Request $request,
        Actividad $actividad
    ) {
        $this->validarActividadEditable(
            $actividad
        );

        $validated = $request->validate([
            'modo_sesiones' => [
                'nullable',
                Rule::in([
                    'ninguna',
                    'unica',
                    'multiples',
                ]),
            ],
            'tiene_sesiones' => [
                'nullable',
                'boolean',
            ],
            'confirmar_solapamientos' => [
                'nullable',
                'boolean',
            ],
            'sesiones' => [
                'nullable',
                'array',
                'max:100',
            ],
            'sesiones.*.id_sesion' => [
                'nullable',
                'integer',
            ],
            'sesiones.*.nombre' => [
                'nullable',
                'string',
                'max:150',
            ],
            'sesiones.*.fecha_inicio' => [
                'nullable',
                'date',
            ],
            'sesiones.*.fecha_fin' => [
                'nullable',
                'date',
            ],
            'sesiones.*.ubicacion' => [
                'nullable',
                'string',
                'max:250',
            ],
            'sesiones.*.enlace_acceso' => [
                'nullable',
                'url',
                'max:2000',
            ],
            'sesiones.*.cupo' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'sesiones.*.requiere_reserva' => [
                'nullable',
                'boolean',
            ],
            'sesiones.*.obligatoria' => [
                'nullable',
                'boolean',
            ],
        ]);

        $modo =
            $validated['modo_sesiones']
            ?? null;

        $modoLegacy = false;

        if (
            !$modo
            && array_key_exists(
                'tiene_sesiones',
                $validated
            )
        ) {
            $modoLegacy = true;

            $modo =
                (bool) $validated['tiene_sesiones']
                    ? 'multiples'
                    : 'ninguna';
        }

        if (!$modo) {
            throw ValidationException::withMessages([
                'modo_sesiones' =>
                    'Indica si la actividad no tendrá sesiones, tendrá una sola o tendrá varias.',
            ]);
        }

        $sesionesPreparadas = [];
        $rango = null;

        if ($modo !== 'ninguna') {
            $rango =
                $this->obtenerRangoRealizacion(
                    $actividad
                );
        }

        if ($modo === 'unica') {
            $sesionesPreparadas = [
                $this->prepararSesionUnica(
                    $actividad,
                    $rango
                ),
            ];
        }

        if ($modo === 'multiples') {
            $sesionesRecibidas =
                $validated['sesiones']
                ?? [];

            $minimo =
                $modoLegacy
                    ? 1
                    : 2;

            if (
                count($sesionesRecibidas)
                < $minimo
            ) {
                throw ValidationException::withMessages([
                    'sesiones' =>
                        $modoLegacy
                            ? 'Agrega al menos una fecha, horario o turno.'
                            : 'Si seleccionaste varias sesiones, agrega al menos dos.',
                ]);
            }

            $sesionesPreparadas =
                $this->prepararSesionesMultiples(
                    $actividad,
                    $sesionesRecibidas,
                    $rango
                );

            $solapamientos =
                $this->detectarSolapamientos(
                    $sesionesPreparadas
                );

            if (
                !empty($solapamientos)
                && !(bool) (
                    $validated['confirmar_solapamientos']
                    ?? false
                )
            ) {
                throw ValidationException::withMessages([
                    'confirmar_solapamientos' =>
                        'Hay horarios que se realizan al mismo tiempo: '
                        . implode(
                            '; ',
                            array_slice(
                                $solapamientos,
                                0,
                                5
                            )
                        )
                        . '. Si es intencional, confirma que se realizarán simultáneamente.',
                ]);
            }
        }
                try {
            DB::transaction(
                function () use (
                    $actividad,
                    $modo,
                    $sesionesPreparadas
                ) {
                    if ($modo === 'ninguna') {
                        $actividad->sesiones()
                            ->delete();
                    } elseif ($modo === 'unica') {
                        $existente =
                            $actividad->sesiones()
                                ->orderBy('orden')
                                ->orderBy('id_sesion')
                                ->first();

                        $sesion =
                            $existente
                            ?? new SesionActividad();

                        $sesion->id_actividad =
                            $actividad->id_actividad;

                        $sesion->nombre =
                            $sesionesPreparadas[0]['nombre'];

                        $sesion->fecha_inicio =
                            $sesionesPreparadas[0]['fecha_inicio'];

                        $sesion->fecha_fin =
                            $sesionesPreparadas[0]['fecha_fin'];

                        $sesion->ubicacion =
                            $sesionesPreparadas[0]['ubicacion'];

                        $sesion->enlace_acceso =
                            $sesionesPreparadas[0]['enlace_acceso'];

                        $sesion->cupo =
                            $sesionesPreparadas[0]['cupo'];

                        $sesion->requiere_reserva =
                            false;

                        $sesion->obligatoria =
                            true;

                        $sesion->orden =
                            0;

                        $sesion->estado =
                            'programada';

                        $sesion->save();

                        $actividad->sesiones()
                            ->where(
                                'id_sesion',
                                '!=',
                                $sesion->id_sesion
                            )
                            ->delete();
                    } else {
                        $idsConservados = [];

                        foreach (
                            $sesionesPreparadas
                            as $indice => $datos
                        ) {
                            $sesion = null;

                            if (
                                !empty(
                                    $datos['id_sesion']
                                )
                            ) {
                                $sesion =
                                    $actividad->sesiones()
                                        ->where(
                                            'id_sesion',
                                            (int) $datos['id_sesion']
                                        )
                                        ->first();

                                if (!$sesion) {
                                    throw ValidationException::withMessages([
                                        'sesiones' =>
                                            'Una de las sesiones ya no existe o no pertenece a esta actividad.',
                                    ]);
                                }
                            }

                            if (!$sesion) {
                                $sesion =
                                    new SesionActividad();

                                $sesion->id_actividad =
                                    $actividad->id_actividad;
                            }

                            $sesion->nombre =
                                $datos['nombre'];

                            $sesion->fecha_inicio =
                                $datos['fecha_inicio'];

                            $sesion->fecha_fin =
                                $datos['fecha_fin'];

                            $sesion->ubicacion =
                                $datos['ubicacion'];

                            $sesion->enlace_acceso =
                                $datos['enlace_acceso'];

                            $sesion->cupo =
                                $datos['cupo'];

                            $sesion->requiere_reserva =
                                $datos['requiere_reserva'];

                            $sesion->obligatoria =
                                $datos['obligatoria'];

                            $sesion->orden =
                                $indice;

                            $sesion->estado =
                                'programada';

                            $sesion->save();

                            $idsConservados[] =
                                (int) $sesion->id_sesion;
                        }

                        $actividad->sesiones()
                            ->whereNotIn(
                                'id_sesion',
                                $idsConservados
                            )
                            ->delete();
                    }

                    $actividad->actualizado_por =
                        Auth::id();

                    $actividad->save();
                }
            );

            $documento =
                $this->obtenerDocumentoActividad(
                    $actividad
                );

            $documento->session_configuration = [
                'configured' => true,
                'enabled' =>
                    $modo !== 'ninguna',
                'mode' =>
                    $modo,
                'inherits_general_capacity' =>
                    $modo === 'unica'
                    && $actividad->habilita_inscripcion
                    && $actividad->cupo_total !== null,
                'updated_at' =>
                    now()->toIso8601String(),
            ];

            $this->invalidarConfiguracionGeneral(
                $documento
            );

            $documento->save();

            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' => 'resumen',
                    ]
                )
                ->with(
                    'success',
                    'Programación guardada correctamente. Revisa el resumen antes de finalizar.'
                );
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No se pudo guardar la configuración de fechas y horarios.'
                );
        }
    }

    public function guardarPresentacion(
        Request $request,
        Actividad $actividad
    ) {
        $this->validarActividadEditable(
            $actividad
        );

        $validated = $request->validate([
            'portada' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'texto_alternativo' => [
                'nullable',
                'string',
                'max:255',
            ],
            'galeria' => [
                'nullable',
                'array',
                'max:20',
            ],
            'galeria.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $actividad->load('medios');

        $portadaActual =
            $actividad->medios
                ->whereNull('id_item_actividad')
                ->whereNull('id_sesion')
                ->firstWhere(
                    'es_portada',
                    true
                );

        $archivosNuevos = [];
        $archivoAnterior = null;

        try {
            DB::transaction(
                function () use (
                    $request,
                    $validated,
                    $actividad,
                    $portadaActual,
                    &$archivosNuevos,
                    &$archivoAnterior
                ) {
                    if (
                        $request->hasFile(
                            'portada'
                        )
                    ) {
                        $ruta =
                            $request
                                ->file('portada')
                                ->store(
                                    "actividades/{$actividad->id_actividad}",
                                    'public'
                                );

                        $archivosNuevos[] =
                            $ruta;

                        if ($portadaActual) {
                            $archivoAnterior =
                                $this->rutaStorageDesdeUrl(
                                    $portadaActual->url
                                );

                            $portadaActual->url =
                                $ruta;

                            $portadaActual->texto_alternativo =
                                $validated['texto_alternativo']
                                ?: $actividad->nombre;

                            $portadaActual->tipo =
                                'imagen';

                            $portadaActual->es_portada =
                                true;

                            $portadaActual->orden =
                                0;

                            $portadaActual->save();
                        } else {
                            $medio =
                                new MedioActividad();

                            $medio->id_actividad =
                                $actividad->id_actividad;

                            $medio->id_item_actividad =
                                null;

                            $medio->id_sesion =
                                null;

                            $medio->tipo =
                                'imagen';

                            $medio->url =
                                $ruta;

                            $medio->texto_alternativo =
                                $validated['texto_alternativo']
                                ?: $actividad->nombre;

                            $medio->es_portada =
                                true;

                            $medio->orden =
                                0;

                            $medio->save();
                        }
                    } elseif (
                        $portadaActual
                        && array_key_exists(
                            'texto_alternativo',
                            $validated
                        )
                    ) {
                        $portadaActual
                            ->texto_alternativo =
                            $validated['texto_alternativo']
                            ?: $actividad->nombre;

                        $portadaActual->save();
                    }

                    if (
                        $request->hasFile(
                            'galeria'
                        )
                    ) {
                        $ultimoOrden =
                            (int) MedioActividad::query()
                                ->where(
                                    'id_actividad',
                                    $actividad->id_actividad
                                )
                                ->whereNull(
                                    'id_item_actividad'
                                )
                                ->whereNull(
                                    'id_sesion'
                                )
                                ->max('orden');

                        foreach (
                            $request->file(
                                'galeria'
                            )
                            as $archivo
                        ) {
                            $ruta =
                                $archivo->store(
                                    "actividades/{$actividad->id_actividad}",
                                    'public'
                                );

                            $archivosNuevos[] =
                                $ruta;

                            $ultimoOrden++;

                            $medio =
                                new MedioActividad();

                            $medio->id_actividad =
                                $actividad->id_actividad;

                            $medio->id_item_actividad =
                                null;

                            $medio->id_sesion =
                                null;

                            $medio->tipo =
                                'imagen';

                            $medio->url =
                                $ruta;

                            $medio->texto_alternativo =
                                $actividad->nombre;

                            $medio->es_portada =
                                false;

                            $medio->orden =
                                $ultimoOrden;

                            $medio->save();
                        }
                    }

                    $actividad->actualizado_por =
                        Auth::id();

                    $actividad->save();
                }
            );

            if (
                $archivoAnterior
                && !in_array(
                    $archivoAnterior,
                    $archivosNuevos,
                    true
                )
                && Storage::disk('public')
                    ->exists(
                        $archivoAnterior
                    )
            ) {
                Storage::disk('public')
                    ->delete(
                        $archivoAnterior
                    );
            }

            $documento =
                $this->obtenerDocumentoActividad(
                    $actividad
                );

            $this->invalidarConfiguracionGeneral(
                $documento
            );

            $documento->save();

            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' => 'productos',
                    ]
                )
                ->with(
                    'success',
                    'Presentación guardada correctamente. Continúa con productos o servicios.'
                );
        } catch (\Throwable $e) {
            foreach (
                $archivosNuevos
                as $ruta
            ) {
                if (
                    Storage::disk('public')
                        ->exists($ruta)
                ) {
                    Storage::disk('public')
                        ->delete($ruta);
                }
            }

            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No se pudo guardar la presentación.'
                );
        }
    }

    public function establecerPortada(
        Actividad $actividad,
        MedioActividad $medio
    ) {
        $this->validarActividadEditable(
            $actividad
        );

        if (
            (int) $medio->id_actividad
                !==
                (int) $actividad->id_actividad
            || $medio->id_item_actividad !== null
            || $medio->id_sesion !== null
        ) {
            abort(404);
        }

        if ($medio->es_portada) {
            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' =>
                            'presentacion',
                    ]
                );
        }

        DB::transaction(
            function () use (
                $actividad,
                $medio
            ) {
                MedioActividad::query()
                    ->where(
                        'id_actividad',
                        $actividad->id_actividad
                    )
                    ->whereNull(
                        'id_item_actividad'
                    )
                    ->whereNull(
                        'id_sesion'
                    )
                    ->where(
                        'es_portada',
                        true
                    )
                    ->update([
                        'es_portada' => false,
                    ]);

                $medio->es_portada =
                    true;

                $medio->orden =
                    0;

                $medio->save();
            }
        );

        $documento =
            $this->obtenerDocumentoActividad(
                $actividad
            );

        $this->invalidarConfiguracionGeneral(
            $documento
        );

        $documento->save();

        return redirect()
            ->route(
                'admin.actividades.configurar',
                [
                    'actividad' =>
                        $actividad->id_actividad,
                    'paso' =>
                        'presentacion',
                ]
            )
            ->with(
                'success',
                'La imagen fue establecida como portada.'
            );
    }

    public function eliminarMedio(
        Actividad $actividad,
        MedioActividad $medio
    ) {
        $this->validarActividadEditable(
            $actividad
        );

        if (
            (int) $medio->id_actividad
                !==
                (int) $actividad->id_actividad
            || $medio->id_item_actividad !== null
            || $medio->id_sesion !== null
        ) {
            abort(404);
        }

        if ($medio->es_portada) {
            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' =>
                            'presentacion',
                    ]
                )
                ->with(
                    'error',
                    'La portada no puede eliminarse directamente. Primero selecciona otra imagen como portada.'
                );
        }

        $ruta =
            $this->rutaStorageDesdeUrl(
                $medio->url
            );

        $medio->delete();

        if (
            $ruta
            && Storage::disk('public')
                ->exists($ruta)
        ) {
            Storage::disk('public')
                ->delete($ruta);
        }

        $documento =
            $this->obtenerDocumentoActividad(
                $actividad
            );

        $this->invalidarConfiguracionGeneral(
            $documento
        );

        $documento->save();

        return redirect()
            ->route(
                'admin.actividades.configurar',
                [
                    'actividad' =>
                        $actividad->id_actividad,
                    'paso' =>
                        'presentacion',
                ]
            )
            ->with(
                'success',
                'Imagen eliminada correctamente.'
            );
    }

    public function finalizarConfiguracion(
        Actividad $actividad
    ) {
        $this->validarActividadEditable(
            $actividad
        );

        $actividad->load([
            'medios',
            'items.variantes',
            'sesiones',
        ]);

        $documento =
            $this->obtenerDocumentoActividad(
                $actividad
            );

        $configuracionProductos =
            $this->normalizarMongo(
                $documento->product_configuration
                ?? []
            );

        $configuracionesDatos = collect(
            $this->normalizarMongo(
                $documento->purchase_fields
                ?? []
            )
        )->keyBy(
            fn ($configuracion) =>
                (string) (
                    $configuracion['id_item_pg']
                    ?? ''
                )
        );

        $configuracionesPrecios = collect(
            $this->normalizarMongo(
                $documento->pricing_configuration
                ?? []
            )
        )->keyBy(
            fn ($configuracion) =>
                (string) (
                    $configuracion['id_item_pg']
                    ?? ''
                )
        );

        $configuracionSesiones =
            $this->normalizarMongo(
                $documento->session_configuration
                ?? []
            );

        $portadaConfigurada =
            $actividad->medios
                ->whereNull(
                    'id_item_actividad'
                )
                ->whereNull(
                    'id_sesion'
                )
                ->contains(
                    fn ($medio) =>
                        (bool) $medio->es_portada
                );

        if (!$portadaConfigurada) {
            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' =>
                            'presentacion',
                    ]
                )
                ->with(
                    'error',
                    'Debes configurar una portada antes de finalizar.'
                );
        }

        $productosConfigurados =
            ($configuracionProductos['configured']
                ?? false) === true;

        $productosHabilitados =
            ($configuracionProductos['enabled']
                ?? false) === true;

        if (!$productosConfigurados) {
            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' =>
                            'productos',
                    ]
                )
                ->with(
                    'error',
                    'Debes indicar si la actividad ofrecerá productos o servicios.'
                );
        }

        if (
            $productosHabilitados
            && $actividad->items->isEmpty()
        ) {
            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' =>
                            'productos',
                    ]
                )
                ->with(
                    'error',
                    'Agrega al menos un producto o servicio antes de finalizar.'
                );
        }

        if ($productosHabilitados) {
            foreach (
                $actividad->items
                as $item
            ) {
                $configDatos =
                    $configuracionesDatos->get(
                        (string) $item
                            ->id_item_actividad
                    );

                if (!$configDatos) {
                    return redirect()
                        ->route(
                            'admin.actividades.configurar',
                            [
                                'actividad' =>
                                    $actividad->id_actividad,
                                'paso' =>
                                    'datos',
                                'item' =>
                                    $item->id_item_actividad,
                            ]
                        )
                        ->with(
                            'error',
                            "Falta configurar los datos solicitados para '{$item->nombre}'."
                        );
                }

                $camposLista = collect(
                    $configDatos['fields']
                    ?? []
                )->filter(
                    fn ($campo) =>
                        ($campo['type'] ?? null)
                            === 'lista'
                        && !empty(
                            $campo['options']
                            ?? []
                        )
                );

                if (
                    $camposLista->isNotEmpty()
                    && !$configuracionesPrecios->has(
                        (string) $item
                            ->id_item_actividad
                    )
                ) {
                    return redirect()
                        ->route(
                            'admin.actividades.configurar',
                            [
                                'actividad' =>
                                    $actividad->id_actividad,
                                'paso' =>
                                    'precios',
                                'item' =>
                                    $item->id_item_actividad,
                            ]
                        )
                        ->with(
                            'error',
                            "Falta configurar los precios y costos para '{$item->nombre}'."
                        );
                }
            }
        }

        if (
            ($configuracionSesiones['configured']
                ?? false) !== true
        ) {
            return redirect()
                ->route(
                    'admin.actividades.configurar',
                    [
                        'actividad' =>
                            $actividad->id_actividad,
                        'paso' =>
                            'sesiones',
                    ]
                )
                ->with(
                    'error',
                    'Debes completar la programación antes de finalizar.'
                );
        }

        $documento->configuration = [
            'configured' => true,
            'completed_at' =>
                now()->toIso8601String(),
            'updated_at' =>
                now()->toIso8601String(),
            'completed_by' =>
                Auth::id(),
        ];

        $documento->save();

        $actividad->actualizado_por =
            Auth::id();

        $actividad->save();

        return redirect()
            ->route(
                'admin.actividades.index'
            )
            ->with(
                'success',
                'Configuración finalizada correctamente. La actividad ya puede enviarse a revisión.'
            );
    }
        private function rutaStorageDesdeUrl(
        ?string $url
    ): ?string {
        if (!$url) {
            return null;
        }

        $appUrl =
            rtrim(
                (string) config('app.url'),
                '/'
            );

        if (
            Str::startsWith(
                $url,
                ['http://', 'https://']
            )
            && !Str::startsWith(
                $url,
                $appUrl
            )
        ) {
            return null;
        }

        $path =
            parse_url(
                $url,
                PHP_URL_PATH
            ) ?: $url;

        $path =
            ltrim(
                $path,
                '/'
            );

        if (
            Str::startsWith(
                $path,
                'storage/'
            )
        ) {
            $path =
                substr(
                    $path,
                    8
                );
        }

        return $path ?: null;
    }

    private function obtenerRangoRealizacion(
        Actividad $actividad
    ): array {
        if (
            !$actividad->realizacion_desde
            || !$actividad->realizacion_hasta
        ) {
            throw ValidationException::withMessages([
                'modo_sesiones' =>
                    'Para configurar sesiones primero debes definir el período de realización de la actividad.',
            ]);
        }

        $desde =
            Carbon::parse(
                $actividad->realizacion_desde
            );

        $hasta =
            Carbon::parse(
                $actividad->realizacion_hasta
            );

        if ($hasta->lt($desde)) {
            throw ValidationException::withMessages([
                'modo_sesiones' =>
                    'El período de realización de la actividad no es válido.',
            ]);
        }

        return [
            'desde' => $desde,
            'hasta' => $hasta,
        ];
    }

    private function prepararSesionUnica(
        Actividad $actividad,
        array $rango
    ): array {
        $existente =
            $actividad->sesiones()
                ->orderBy('orden')
                ->orderBy('id_sesion')
                ->first();

        if (
            !$existente
            && $rango['desde']->lt(now())
        ) {
            throw ValidationException::withMessages([
                'modo_sesiones' =>
                    'No puedes crear una sesión nueva con una fecha de inicio que ya pasó.',
            ]);
        }

        $cupo =
            $actividad->habilita_inscripcion
            && $actividad->cupo_total !== null
                ? (int) $actividad->cupo_total
                : null;

        return [
            'id_sesion' =>
                $existente?->id_sesion,
            'nombre' =>
                'Sesión principal',
            'fecha_inicio' =>
                $rango['desde']->format(
                    'Y-m-d H:i:sP'
                ),
            'fecha_fin' =>
                $rango['hasta']->format(
                    'Y-m-d H:i:sP'
                ),
            'ubicacion' =>
                $existente?->ubicacion,
            'enlace_acceso' =>
                $existente?->enlace_acceso,
            'cupo' =>
                $cupo,
            'requiere_reserva' =>
                false,
            'obligatoria' =>
                true,
        ];
    }

    private function prepararSesionesMultiples(
        Actividad $actividad,
        array $sesiones,
        array $rango
    ): array {
        $cupoGeneral =
            $actividad->habilita_inscripcion
            && $actividad->cupo_total !== null
                ? (int) $actividad->cupo_total
                : null;

        $preparadas = [];
        $ahora = now();

        foreach (
            $sesiones
            as $indice => $datos
        ) {
            $nombre =
                trim(
                    (string) (
                        $datos['nombre']
                        ?? ''
                    )
                );

            if ($nombre === '') {
                throw ValidationException::withMessages([
                    'sesiones' =>
                        'Cada sesión debe tener un nombre.',
                ]);
            }

            if (
                empty(
                    $datos['fecha_inicio']
                )
            ) {
                throw ValidationException::withMessages([
                    'sesiones' =>
                        "La sesión '{$nombre}' debe tener una fecha y hora de inicio.",
                ]);
            }

            $inicio =
                Carbon::parse(
                    $datos['fecha_inicio']
                );

            $fin =
                !empty(
                    $datos['fecha_fin']
                )
                    ? Carbon::parse(
                        $datos['fecha_fin']
                    )
                    : null;

            if (
                $fin
                && $fin->lt($inicio)
            ) {
                throw ValidationException::withMessages([
                    'sesiones' =>
                        "La finalización de '{$nombre}' no puede ser anterior a su inicio.",
                ]);
            }

            if (
                $inicio->lt(
                    $rango['desde']
                )
                || $inicio->gt(
                    $rango['hasta']
                )
            ) {
                throw ValidationException::withMessages([
                    'sesiones' =>
                        "La sesión '{$nombre}' debe comenzar dentro del período de realización de la actividad.",
                ]);
            }

            if (
                $fin
                && $fin->gt(
                    $rango['hasta']
                )
            ) {
                throw ValidationException::withMessages([
                    'sesiones' =>
                        "La sesión '{$nombre}' debe finalizar dentro del período de realización de la actividad.",
                ]);
            }

            $idSesion =
                !empty(
                    $datos['id_sesion']
                )
                    ? (int) $datos['id_sesion']
                    : null;

            if ($idSesion) {
                $pertenece =
                    $actividad->sesiones()
                        ->where(
                            'id_sesion',
                            $idSesion
                        )
                        ->exists();

                if (!$pertenece) {
                    throw ValidationException::withMessages([
                        'sesiones' =>
                            'Una de las sesiones ya no existe o no pertenece a esta actividad.',
                    ]);
                }
            } elseif (
                $inicio->lt($ahora)
            ) {
                throw ValidationException::withMessages([
                    'sesiones' =>
                        "No puedes crear '{$nombre}' con una fecha de inicio que ya pasó.",
                ]);
            }

            $cupo =
                array_key_exists(
                    'cupo',
                    $datos
                )
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
                    'sesiones' =>
                        "El cupo de '{$nombre}' no puede superar el cupo general de {$cupoGeneral} personas.",
                ]);
            }

            $preparadas[] = [
                'id_sesion' =>
                    $idSesion,
                'nombre' =>
                    $nombre,
                'fecha_inicio' =>
                    $inicio->format(
                        'Y-m-d H:i:sP'
                    ),
                'fecha_fin' =>
                    $fin?->format(
                        'Y-m-d H:i:sP'
                    ),
                'ubicacion' =>
                    isset($datos['ubicacion'])
                    && trim(
                        (string) $datos['ubicacion']
                    ) !== ''
                        ? trim(
                            (string) $datos['ubicacion']
                        )
                        : null,
                'enlace_acceso' =>
                    isset($datos['enlace_acceso'])
                    && trim(
                        (string) $datos['enlace_acceso']
                    ) !== ''
                        ? trim(
                            (string) $datos['enlace_acceso']
                        )
                        : null,
                'cupo' =>
                    $cupo,
                'requiere_reserva' =>
                    (bool) (
                        $datos['requiere_reserva']
                        ?? false
                    ),
                'obligatoria' =>
                    (bool) (
                        $datos['obligatoria']
                        ?? false
                    ),
                '_indice' =>
                    $indice,
            ];
        }

        return $preparadas;
    }

    private function detectarSolapamientos(
        array $sesiones
    ): array {
        $solapamientos = [];
        $total = count($sesiones);

        for (
            $i = 0;
            $i < $total;
            $i++
        ) {
            for (
                $j = $i + 1;
                $j < $total;
                $j++
            ) {
                $a =
                    $sesiones[$i];

                $b =
                    $sesiones[$j];

                $inicioA =
                    Carbon::parse(
                        $a['fecha_inicio']
                    );

                $finA =
                    !empty(
                        $a['fecha_fin']
                    )
                        ? Carbon::parse(
                            $a['fecha_fin']
                        )
                        : null;

                $inicioB =
                    Carbon::parse(
                        $b['fecha_inicio']
                    );

                $finB =
                    !empty(
                        $b['fecha_fin']
                    )
                        ? Carbon::parse(
                            $b['fecha_fin']
                        )
                        : null;

                $seCruzan = false;

                if (
                    $finA
                    && $finB
                ) {
                    $seCruzan =
                        $inicioA->lt($finB)
                        && $finA->gt($inicioB);
                } elseif (
                    !$finA
                    && !$finB
                ) {
                    $seCruzan =
                        $inicioA->eq($inicioB);
                } elseif (
                    !$finA
                    && $finB
                ) {
                    $seCruzan =
                        $inicioA->eq($inicioB)
                        || (
                            $inicioA->gt($inicioB)
                            && $inicioA->lt($finB)
                        );
                } elseif (
                    $finA
                    && !$finB
                ) {
                    $seCruzan =
                        $inicioB->eq($inicioA)
                        || (
                            $inicioB->gt($inicioA)
                            && $inicioB->lt($finA)
                        );
                }

                if ($seCruzan) {
                    $solapamientos[] =
                        "{$a['nombre']} con {$b['nombre']}";
                }
            }
        }

        return array_values(
            array_unique(
                $solapamientos
            )
        );
    }

    private function generarCombinaciones(
        array $camposSeleccionados
    ): array {
        $combinaciones = [[]];

        foreach (
            $camposSeleccionados
            as $clave => $campo
        ) {
            $nuevas = [];

            foreach (
                $combinaciones
                as $combinacion
            ) {
                foreach (
                    $campo['options']
                    ?? []
                    as $opcion
                ) {
                    $nueva =
                        $combinacion;

                    $nueva[$clave] =
                        (string) $opcion;

                    $nuevas[] =
                        $nueva;
                }
            }

            $combinaciones =
                $nuevas;
        }

        return $combinaciones;
    }

    private function guardarConfiguracionPrecio(
        Actividad $actividad,
        ItemActividad $item,
        bool $enabled,
        array $camposClave,
        array $reglas
    ): void {
        $documento =
            $this->obtenerDocumentoActividad(
                $actividad
            );

        $configuraciones = collect(
            $this->normalizarMongo(
                $documento->pricing_configuration
                ?? []
            )
        )
            ->reject(
                fn ($configuracion) =>
                    (int) (
                        $configuracion['id_item_pg']
                        ?? 0
                    )
                    ===
                    (int) $item->id_item_actividad
            )
            ->values()
            ->all();

        $configuraciones[] = [
            'id_item_pg' =>
                (int) $item->id_item_actividad,
            'enabled' =>
                $enabled,
            'pricing_fields' =>
                $camposClave,
            'rules' =>
                $reglas,
            'updated_at' =>
                now()->toIso8601String(),
        ];

        $documento->pricing_configuration =
            $configuraciones;

        $documento->save();
    }

    private function eliminarVariantesComerciales(
        ItemActividad $item
    ): void {
        $ids =
            $item->variantes()
                ->pluck(
                    'id_variante_item'
                )
                ->map(
                    fn ($id) => (int) $id
                )
                ->values()
                ->all();

        if (empty($ids)) {
            return;
        }

        VariantAttribute::whereIn(
            'id_variante_pg',
            $ids
        )->delete();

        VarianteItem::whereIn(
            'id_variante_item',
            $ids
        )->delete();
    }

    private function procesarOpciones(
        string $texto
    ): array {
        $partes =
            preg_split(
                '/[\r\n,]+/',
                $texto
            );

        $opciones = [];

        foreach ($partes as $parte) {
            $opcion =
                trim($parte);

            if (
                $opcion !== ''
                && !in_array(
                    $opcion,
                    $opciones,
                    true
                )
            ) {
                $opciones[] =
                    $opcion;
            }
        }

        return $opciones;
    }

    private function obtenerDocumentoActividad(
        Actividad $actividad
    ): ActivityContent {
        $documento =
            ActivityContent::where(
                'id_actividad_pg',
                (int) $actividad->id_actividad
            )->first();

        if (!$documento) {
            $documento =
                new ActivityContent();

            $documento->id_actividad_pg =
                (int) $actividad->id_actividad;
        }

        return $documento;
    }

    private function invalidarConfiguracionGeneral(
        ActivityContent $documento
    ): void {
        $configuracion =
            $this->normalizarMongo(
                $documento->configuration
                ?? []
            );

        if (
            ($configuracion['configured']
                ?? false) !== true
        ) {
            return;
        }

        $documento->configuration = [
            'configured' => false,
            'completed_at' => null,
            'updated_at' =>
                now()->toIso8601String(),
            'completed_by' => null,
        ];
    }

    private function validarProductosHabilitados(
        Actividad $actividad
    ): void {
        $documento =
            ActivityContent::where(
                'id_actividad_pg',
                (int) $actividad->id_actividad
            )->first();

        $configuracion =
            $this->normalizarMongo(
                $documento?->product_configuration
                ?? []
            );

        if (
            ($configuracion['configured']
                ?? false)
            && !(
                $configuracion['enabled']
                ?? false
            )
        ) {
            throw ValidationException::withMessages([
                'productos' =>
                    'Esta actividad fue configurada sin productos ni servicios.',
            ]);
        }
    }

    private function validarItemPerteneceActividad(
        Actividad $actividad,
        ItemActividad $item
    ): void {
        abort_unless(
            (int) $item->id_actividad
                ===
                (int) $actividad->id_actividad,
            404
        );
    }

    private function validarActividadEditable(
        Actividad $actividad
    ): void {
        abort_unless(
            in_array(
                $actividad->estado_publicacion,
                [
                    'borrador',
                    'cambios_solicitados',
                ],
                true
            ),
            403
        );
    }

    private function normalizarMongo(
        mixed $valor
    ): array {
        if (empty($valor)) {
            return [];
        }

        return json_decode(
            json_encode($valor),
            true
        ) ?? [];
    }
}