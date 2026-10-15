<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\DisponibilidadService;

class CompraActividadController extends Controller
{
    public function store(
        Request $request,
        Actividad $actividad
    ) {
        $usuario = $request->user();

        if (!$usuario) {
            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Debés iniciar sesión para realizar una compra.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar actividad
        |--------------------------------------------------------------------------
        */

        if (
            $actividad->estado_publicacion !== 'publicada' ||
            $actividad->visibilidad !== 'publica'
        ) {
            throw ValidationException::withMessages([
                'compra' =>
                    'La actividad no está disponible.',
            ]);
        }

        if (
            $actividad->estado_operativo !== 'normal'
        ) {
            throw ValidationException::withMessages([
                'compra' =>
                    'La actividad no acepta compras en este momento.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Recibir selecciones
        |--------------------------------------------------------------------------
        */

        $selecciones = json_decode(
            (string) $request->input(
                'selecciones',
                ''
            ),
            true
        );

        if (
            !is_array($selecciones) ||
            count($selecciones) === 0
        ) {
            throw ValidationException::withMessages([
                'compra' =>
                    'Agregá al menos un producto antes de continuar.',
            ]);
        }

        Validator::make(
            [
                'selecciones' =>
                    $selecciones,
            ],
            [
                'selecciones' => [
                    'required',
                    'array',
                    'min:1',
                    'max:50',
                ],

                'selecciones.*.id_item' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'selecciones.*.id_variante' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],

                'selecciones.*.cantidad' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'selecciones.*.campos' => [
                    'nullable',
                    'array',
                    'max:20',
                ],
            ]
        )->validate();

        /*
        |--------------------------------------------------------------------------
        | Datos del usuario
        |--------------------------------------------------------------------------
        */

        $usuario->loadMissing(
            'informacion_personal'
        );

        $correoUsuario = strtolower(
            trim(
                (string) $usuario->correo
            )
        );

        $idUsuario =
            (int) $usuario->id_usuario;

        /*
        |--------------------------------------------------------------------------
        | Crear orden
        |--------------------------------------------------------------------------
        */

        $resultado = DB::transaction(
            function () use ($actividad, $selecciones, $correoUsuario, $idUsuario) {
                $ahoraUtc =
                    now('UTC');

                /*
                |--------------------------------------------------------------------------
                | Bloquear actividad durante la operación
                |--------------------------------------------------------------------------
                */

                $actividadBloqueada =
                    DB::table(
                        'tbl_actividades'
                    )
                        ->where(
                            'id_actividad',
                            $actividad->id_actividad
                        )
                        ->lockForUpdate()
                        ->first();

                if (!$actividadBloqueada) {
                    throw ValidationException::withMessages([
                        'compra' =>
                            'La actividad ya no existe.',
                    ]);
                }

                if (
                    $actividadBloqueada->estado_publicacion !==
                    'publicada' ||
                    $actividadBloqueada->estado_operativo !==
                    'normal'
                ) {
                    throw ValidationException::withMessages([
                        'compra' =>
                            'La actividad ya no acepta compras.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Buscar inscripción CONFIRMADA
                |--------------------------------------------------------------------------
                |
                | La compra de productos nunca crea una inscripción nueva.
                |
                | Primero debe existir una inscripción:
                |
                | confirmada
                | confirmada_prueba
                |
                */
                $inscripcion = null;

                $requiereInscripcion = $actividad->requiereInscripcionGeneral();

                if ($requiereInscripcion) {
                    $inscripcion =
                        DB::table(
                            'tbl_inscripciones'
                        )
                            ->where(
                                'id_actividad',
                                $actividad->id_actividad
                            )
                            ->where(
                                function ($query) use ($idUsuario, $correoUsuario) {
                                    $query
                                        ->where(
                                            'id_usuario',
                                            $idUsuario
                                        )
                                        ->orWhereRaw(
                                            'lower(correo_participante) = ?',
                                            [
                                                $correoUsuario,
                                            ]
                                        );
                                }
                            )
                            ->whereIn(
                                'estado',
                                [
                                    'confirmada',
                                    'confirmada_prueba',
                                ]
                            )
                            ->orderByDesc(
                                'id_inscripcion'
                            )
                            ->lockForUpdate()
                            ->first();

                    /*
                    |--------------------------------------------------------------------------
                    | Si todavía no está confirmada
                    |--------------------------------------------------------------------------
                    */

                    if (!$inscripcion) {
                        $inscripcionPendiente =
                            DB::table(
                                'tbl_inscripciones'
                            )
                                ->where(
                                    'id_actividad',
                                    $actividad->id_actividad
                                )
                                ->where(
                                    function ($query) use ($idUsuario, $correoUsuario) {
                                        $query
                                            ->where(
                                                'id_usuario',
                                                $idUsuario
                                            )
                                            ->orWhereRaw(
                                                'lower(correo_participante) = ?',
                                                [
                                                    $correoUsuario,
                                                ]
                                            );
                                    }
                                )
                                ->whereIn(
                                    'estado',
                                    [
                                        'pendiente_pago',
                                        'revision_cupo',
                                    ]
                                )
                                ->orderByDesc(
                                    'id_inscripcion'
                                )
                                ->first();

                        if (
                            $inscripcionPendiente &&
                            $inscripcionPendiente->estado ===
                            'pendiente_pago'
                        ) {
                            throw ValidationException::withMessages([
                                'compra' =>
                                    'Primero debés completar el pago de tu inscripción antes de comprar productos.',
                            ]);
                        }

                        if (
                            $inscripcionPendiente &&
                            $inscripcionPendiente->estado ===
                            'revision_cupo'
                        ) {
                            throw ValidationException::withMessages([
                                'compra' =>
                                    'Tu inscripción está en revisión. Esperá su confirmación antes de comprar productos.',
                            ]);
                        }

                        throw ValidationException::withMessages([
                            'compra' =>
                                'Primero debés inscribirte en la actividad antes de comprar productos.',
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Marcar órdenes de productos vencidas
                |--------------------------------------------------------------------------
                |
                | Esto permite volver a realizar una compra cuando la orden anterior
                | ya superó sus 30 minutos.
                |
                */

                DB::table(
                    'tbl_ordenes'
                )
                    ->where('id_actividad', $actividad->id_actividad)
                    ->where('id_usuario', $idUsuario)
                    ->where('tipo_orden', 'productos')
                    ->when(
                        $inscripcion !== null,
                        fn($query) => $query->where(
                            'id_inscripcion',
                            $inscripcion->id_inscripcion
                        ),
                        fn($query) => $query->whereNull('id_inscripcion')
                    )
                    ->where(
                        'estado',
                        'pendiente_pago'
                    )
                    ->whereNotNull(
                        'expira_en'
                    )
                    ->where(
                        'expira_en',
                        '<=',
                        $ahoraUtc
                    )
                    ->update([
                        'estado' =>
                            'vencida',

                        'updated_at' =>
                            $ahoraUtc,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | Revisar si ya existe una compra pendiente
                |--------------------------------------------------------------------------
                |
                | Evitamos crear dos órdenes de productos activas simultáneamente
                | para la misma inscripción.
                |
                */

                $ordenPendiente = DB::table('tbl_ordenes')
                    ->where('id_actividad', $actividad->id_actividad)
                    ->where('id_usuario', $idUsuario)
                    ->where('tipo_orden', 'productos')
                    ->when(
                        $inscripcion !== null,
                        fn($query) => $query->where(
                            'id_inscripcion',
                            $inscripcion->id_inscripcion
                        ),
                        fn($query) => $query->whereNull('id_inscripcion')
                    )
                    ->where('estado', 'pendiente_pago')
                    ->where(function ($query) use ($ahoraUtc) {
                        $query
                            ->whereNull('expira_en')
                            ->orWhere('expira_en', '>', $ahoraUtc);
                    })
                    ->orderByDesc('id_orden')
                    ->first();

                if ($ordenPendiente) {
                    return [
                        'id_orden' =>
                            $ordenPendiente->id_orden,

                        'existente' =>
                            true,
                    ];
                }


                $contenido = DB::connection('mongodb')
                    ->table('activity_content')
                    ->where('id_actividad_pg', (int) $actividad->id_actividad)
                    ->first();

                // Campos personalizados
                $purchaseFieldsRaw = $contenido->purchase_fields ?? [];

                $purchaseFields = is_array($purchaseFieldsRaw)
                    ? $purchaseFieldsRaw
                    : json_decode((string) $purchaseFieldsRaw, true);

                $purchaseFields = is_array($purchaseFields)
                    ? $purchaseFields
                    : [];

                // Reglas de incremento de precios
                $pricingConfigurationRaw = $contenido->pricing_configuration ?? [];

                $pricingConfiguration = is_array($pricingConfigurationRaw)
                    ? $pricingConfigurationRaw
                    : json_decode((string) $pricingConfigurationRaw, true);

                $pricingConfiguration = is_array($pricingConfiguration)
                    ? $pricingConfiguration
                    : [];


                /*
                |--------------------------------------------------------------------------
                | Obtener productos
                |--------------------------------------------------------------------------
                */

                $idsItems = collect($selecciones)
                    ->pluck('id_item')
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                $items =
                    DB::table(
                        'tbl_items_actividad'
                    )
                        ->where(
                            'id_actividad',
                            $actividad->id_actividad
                        )
                        ->whereIn(
                            'id_item_actividad',
                            $idsItems
                        )
                        ->where(
                            'activo',
                            true
                        )
                        ->orderBy('id_item_actividad')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy(
                            'id_item_actividad'
                        );

                if (
                    $items->count() !==
                    count($idsItems)
                ) {
                    throw ValidationException::withMessages([
                        'compra' =>
                            'Uno de los productos seleccionados ya no está disponible.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Obtener variantes
                |--------------------------------------------------------------------------
                */

                $variantes =
                    DB::table(
                        'tbl_variantes_item'
                    )
                        ->whereIn(
                            'id_item_actividad',
                            $idsItems
                        )
                        ->where(
                            'activo',
                            true
                        )
                        ->orderBy('id_variante_item')
                        ->lockForUpdate()
                        ->get()
                        ->groupBy(
                            'id_item_actividad'
                        );

                $lineas = [];

                $cantidadesPorItem = [];

                $cantidadesPorVariante = [];

                /*
                |--------------------------------------------------------------------------
                | Validar cada producto seleccionado
                |--------------------------------------------------------------------------
                */

                foreach (
                    $selecciones as $seleccion
                ) {
                    $idItem =
                        (int) $seleccion[
                            'id_item'
                        ];

                    $cantidad =
                        (int) $seleccion[
                            'cantidad'
                        ];

                    $item =
                        $items->get(
                            $idItem
                        );

                    if (!$item) {
                        throw ValidationException::withMessages([
                            'compra' =>
                                'Producto inválido.',
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Fechas de venta
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $item->venta_desde &&
                        $ahoraUtc->lt(
                            Carbon::parse(
                                $item->venta_desde
                            )
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'compra' =>
                                "El producto {$item->nombre} todavía no está a la venta.",
                        ]);
                    }

                    if (
                        $item->venta_hasta &&
                        $ahoraUtc->gt(
                            Carbon::parse(
                                $item->venta_hasta
                            )
                        )
                    ) {
                        throw ValidationException::withMessages([
                            'compra' =>
                                "La venta de {$item->nombre} ya finalizó.",
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Cantidad mínima
                    |--------------------------------------------------------------------------
                    */

                    $minimo = max(
                        1,
                        (int) (
                            $item->min_por_inscripcion
                            ?? 1
                        )
                    );

                    if (
                        $cantidad <
                        $minimo
                    ) {
                        throw ValidationException::withMessages([
                            'compra' =>
                                "{$item->nombre} requiere al menos {$minimo} unidad(es).",
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Acumular cantidad del producto
                    |--------------------------------------------------------------------------
                    */

                    $cantidadesPorItem[
                        $idItem
                    ] =
                        (
                            $cantidadesPorItem[
                                $idItem
                            ]
                            ?? 0
                        ) +
                        $cantidad;

                    /**
                     * |--------------------------------------------------------------------------
                     * | Variante
                     * |--------------------------------------------------------------------------
                     *
                     * Si el producto utiliza campos dinámicos de MongoDB,
                     * no exigimos una variante adicional de PostgreSQL.
                     *
                     * Si no tiene campos dinámicos, mantenemos el
                     * comportamiento original de variantes.
                     */

                    $variantesItem = $variantes->get(
                        $idItem,
                        collect()
                    );

                    $idVariante = !empty($seleccion['id_variante'])
                        ? (int) $seleccion['id_variante']
                        : null;

                    $variante = null;

                    /**
                     * Buscamos la configuración dinámica del producto.
                     *
                     * $purchaseFields debe contener el arreglo decodificado
                     * de activity_content.purchase_fields.
                     */
                    $configuracionCampos = collect($purchaseFields)
                        ->first(function ($configuracion) use ($idItem) {
                            return (int) ($configuracion['id_item_pg'] ?? 0)
                                === (int) $idItem
                                && !empty($configuracion['enabled']);
                        });

                    $tieneCamposDinamicos = !empty(
                        $configuracionCampos['fields']
                    );

                    /**
                     * Productos con campos dinámicos:
                     * no deben utilizar una variante adicional.
                     */
                    if ($tieneCamposDinamicos) {

                        if ($idVariante !== null) {
                            throw ValidationException::withMessages([
                                'compra' =>
                                    "{$item->nombre} utiliza opciones personalizadas y no requiere seleccionar una variante.",
                            ]);
                        }

                        $idVariante = null;
                        $variante = null;

                    } elseif ($variantesItem->isNotEmpty()) {

                        /**
                         * Productos sin campos dinámicos:
                         * mantenemos las variantes tradicionales.
                         */
                        if (!$idVariante) {
                            throw ValidationException::withMessages([
                                'compra' =>
                                    "Seleccioná una variante para {$item->nombre}.",
                            ]);
                        }

                        $variante = $variantesItem->firstWhere(
                            'id_variante_item',
                            $idVariante
                        );

                        if (!$variante) {
                            throw ValidationException::withMessages([
                                'compra' =>
                                    "La variante seleccionada para {$item->nombre} no es válida.",
                            ]);
                        }

                        $cantidadesPorVariante[$idVariante] =
                            ($cantidadesPorVariante[$idVariante] ?? 0)
                            + $cantidad;

                    } elseif ($idVariante !== null) {

                        throw ValidationException::withMessages([
                            'compra' =>
                                "{$item->nombre} no utiliza variantes.",
                        ]);
                    }
                    /*
                    |--------------------------------------------------------------------------
                    | Campos configurables
                    |--------------------------------------------------------------------------
                    */

                    $campos = $this->validarCamposProducto(
                        $seleccion['campos'] ?? [],
                        $configuracionCampos,
                        $item->nombre
                    );

                    // Obtener las reglas de precio del producto actual.
                    $configuracionPrecio = collect($pricingConfiguration)
                        ->first(function ($configuracion) use ($idItem) {
                        return is_array($configuracion)
                            && (int) ($configuracion['id_item_pg'] ?? 0) === $idItem
                            && !empty($configuracion['enabled']);
                    });

                    // Solamente aplicar reglas de campos habilitados.
                    $camposConPrecio = $configuracionPrecio['pricing_fields'] ?? [];

                    $definicionesCampos = collect(
                        $configuracionCampos['fields'] ?? []
                    )
                        ->filter(
                            fn($campo) =>
                                is_array($campo)
                                && !empty($campo['key'])
                        )
                        ->keyBy('key');

                    $reglasPrecio = collect(
                        $configuracionPrecio['rules'] ?? []
                    )
                        ->filter(function ($regla) use ($camposConPrecio, $definicionesCampos) {
                            if (!is_array($regla)) {
                                return false;
                            }

                            $clave = (string) ($regla['field_key'] ?? '');
                            $opcion = (string) ($regla['option'] ?? '');

                            if (
                                !in_array($clave, $camposConPrecio, true)
                                || !$definicionesCampos->has($clave)
                            ) {
                                return false;
                            }

                            $definicion = $definicionesCampos->get($clave);

                            if (($definicion['type'] ?? '') !== 'lista') {
                                return false;
                            }

                            $opciones = array_map(
                                'strval',
                                is_array($definicion['options'] ?? null)
                                ? $definicion['options']
                                : []
                            );

                            return in_array($opcion, $opciones, true);
                        })
                        ->values()
                        ->all();

                    /*
                    |--------------------------------------------------------------------------
                    | Precio REAL
                    |--------------------------------------------------------------------------
                    |
                    | No usamos ningún precio enviado por JavaScript.
                    |
                    | El precio sale de:
                    |
                    | tbl_variantes_item
                    | o
                    | tbl_items_actividad
                    |
                    */

                    $precioBase = $variante && $variante->precio !== null
                        ? (float) $variante->precio
                        : (float) $item->precio;

                    $precioUnitario = $this->calcularPrecioConfigurado(
                        $precioBase,
                        $campos,
                        $reglasPrecio
                    );

                    $subtotal = round($precioUnitario * $cantidad, 2);

                    /*
                    |--------------------------------------------------------------------------
                    | Nombre del concepto
                    |--------------------------------------------------------------------------
                    */

                    $nombreConcepto =
                        $item->nombre;

                    if ($variante) {
                        $nombreConcepto .=
                            ' - ' .
                            $variante
                                ->nombre_variante;
                    }

                    if (
                        count($campos) > 0
                    ) {
                        $descripcion =
                            collect($campos)
                                ->map(
                                    fn(
                                    $valor,
                                    $clave
                                ) =>
                                        $clave .
                                        ': ' .
                                        $valor
                                )
                                ->implode(
                                    ', '
                                );

                        $nombreConcepto .=
                            ' (' .
                            $descripcion .
                            ')';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Tipo de concepto
                    |--------------------------------------------------------------------------
                    */

                    $tipoConcepto =
                        in_array(
                            $item->tipo,
                            [
                                'producto',
                                'servicio',
                            ],
                            true
                        )
                        ? $item->tipo
                        : 'producto';

                    /*
                    |--------------------------------------------------------------------------
                    | Preparar línea
                    |--------------------------------------------------------------------------
                    */

                    $lineas[] = [
                        'id_item_actividad' =>
                            $idItem,

                        'id_variante_item' =>
                            $variante
                            ? (int) $variante
                                ->id_variante_item
                            : null,

                        'tipo_concepto' =>
                            $tipoConcepto,

                        'nombre_concepto' =>
                            $nombreConcepto,

                        'cantidad' =>
                            $cantidad,

                        'precio_unitario' =>
                            number_format(
                                $precioUnitario,
                                2,
                                '.',
                                ''
                            ),

                        'subtotal' =>
                            number_format(
                                $subtotal,
                                2,
                                '.',
                                ''
                            ),

                        'configuracion' =>
                            count($campos) > 0
                            ? json_encode(
                                $campos,
                                JSON_UNESCAPED_UNICODE
                            )
                            : null,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Validar máximos y stock
                |--------------------------------------------------------------------------
                */

                foreach (
                    $cantidadesPorItem
                    as $idItem =>
                    $cantidadSolicitada
                ) {
                    $item =
                        $items->get(
                            $idItem
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Máximo acumulado de la inscripción
                    |--------------------------------------------------------------------------
                    |
                    | Si el usuario compra el mismo producto en varias órdenes,
                    | seguimos respetando max_por_inscripcion.
                    |
                    */

                    $compradoEnInscripcion = 0;

                    if ($inscripcion !== null) {
                        $compradoEnInscripcion =
                            $this->cantidadComprometidaInscripcionItem(
                                (int) $inscripcion->id_inscripcion,
                                (int) $idItem
                            );
                    }

                    if (
                        $item->max_por_inscripcion !==
                        null &&
                        (
                            $compradoEnInscripcion +
                            $cantidadSolicitada
                        ) >
                        (int) $item
                            ->max_por_inscripcion
                    ) {
                        throw ValidationException::withMessages([
                            'compra' =>
                                "Superaste el máximo permitido de {$item->nombre} para esta inscripción.",
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Stock global del producto
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $item->stock_total !==
                        null
                    ) {
                        $disponible = app(DisponibilidadService::class)
                            ->stockDisponibleItem((int) $idItem);

                        if (
                            $disponible !== null &&
                            $cantidadSolicitada > $disponible
                        ) {
                            throw ValidationException::withMessages([
                                'compra' =>
                                    "No hay suficiente stock de {$item->nombre}. "
                                    . "Disponibles: {$disponible}.",
                            ]);
                        }

                        if (
                            $cantidadSolicitada >
                            $disponible
                        ) {
                            throw ValidationException::withMessages([
                                'compra' =>
                                    "No hay suficiente stock de {$item->nombre}.",
                            ]);
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Stock de variantes
                |--------------------------------------------------------------------------
                */

                foreach (
                    $cantidadesPorVariante
                    as $idVariante =>
                    $cantidadSolicitada
                ) {
                    $variante =
                        $variantes
                            ->flatten(1)
                            ->firstWhere(
                                'id_variante_item',
                                $idVariante
                            );

                    if (
                        $variante &&
                        $variante->stock_total !==
                        null
                    ) {
                        $disponible = app(DisponibilidadService::class)
                            ->stockDisponibleVariante((int) $idVariante);

                        if (
                            $disponible !== null &&
                            $cantidadSolicitada > $disponible
                        ) {
                            throw ValidationException::withMessages([
                                'compra' =>
                                    "No hay suficiente stock de {$variante->nombre_variante}. "
                                    . "Disponibles: {$disponible}.",
                            ]);
                        }

                        if (
                            $cantidadSolicitada >
                            $disponible
                        ) {
                            throw ValidationException::withMessages([
                                'compra' =>
                                    "No hay suficiente stock de {$variante->nombre_variante}.",
                            ]);
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Calcular total
                |--------------------------------------------------------------------------
                */

                $subtotalOrden =
                    round(
                        collect(
                            $lineas
                        )->sum(
                                fn($linea) =>
                                    (float) $linea[
                                        'subtotal'
                                    ]
                            ),
                        2
                    );

                if (
                    $subtotalOrden <= 0
                ) {
                    throw ValidationException::withMessages([
                        'compra' =>
                            'La compra no tiene un monto válido.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Vencimiento
                |--------------------------------------------------------------------------
                |
                | MUY IMPORTANTE:
                |
                | Utilizamos UTC para que coincida con el tratamiento de
                | timestampTz y con el flujo de inscripciones.
                |
                */

                $expiraEn =
                    $ahoraUtc
                        ->copy()
                        ->addMinutes(
                            30
                        );

                /*
                |--------------------------------------------------------------------------
                | Crear orden de productos
                |--------------------------------------------------------------------------
                */

                $idOrden =
                    DB::table(
                        'tbl_ordenes'
                    )
                        ->insertGetId(
                            [
                                'id_actividad' =>
                                    $actividad
                                        ->id_actividad,

                                /*
                                 * Relacionamos esta compra
                                 * con la inscripción existente.
                                 */
                                'id_inscripcion' => $inscripcion?->id_inscripcion,

                                'id_usuario' =>
                                    $idUsuario,

                                'tipo_orden' =>
                                    'productos',

                                'correo_contacto' =>
                                    $correoUsuario,

                                'moneda' =>
                                    'USD',

                                'subtotal' =>
                                    number_format(
                                        $subtotalOrden,
                                        2,
                                        '.',
                                        ''
                                    ),

                                'descuento_total' =>
                                    '0.00',

                                'total' =>
                                    number_format(
                                        $subtotalOrden,
                                        2,
                                        '.',
                                        ''
                                    ),

                                'estado' =>
                                    'pendiente_pago',

                                'expira_en' =>
                                    $expiraEn,

                                'created_at' =>
                                    $ahoraUtc,

                                'updated_at' =>
                                    $ahoraUtc,
                            ],
                            'id_orden'
                        );

                /*
                |--------------------------------------------------------------------------
                | Crear detalles de la orden
                |--------------------------------------------------------------------------
                */

                foreach (
                    $lineas as $linea
                ) {
                    DB::table(
                        'tbl_detalles_orden'
                    )->insert([
                                'id_orden' =>
                                    $idOrden,

                                'id_item_actividad' =>
                                    $linea[
                                        'id_item_actividad'
                                    ],

                                'id_variante_item' =>
                                    $linea[
                                        'id_variante_item'
                                    ],

                                'tipo_concepto' =>
                                    $linea[
                                        'tipo_concepto'
                                    ],

                                'nombre_concepto' =>
                                    $linea[
                                        'nombre_concepto'
                                    ],

                                'cantidad' =>
                                    $linea[
                                        'cantidad'
                                    ],

                                'precio_unitario' =>
                                    $linea[
                                        'precio_unitario'
                                    ],

                                'subtotal' =>
                                    $linea[
                                        'subtotal'
                                    ],

                                'configuracion' =>
                                    $linea[
                                        'configuracion'
                                    ],

                                'created_at' =>
                                    $ahoraUtc,

                                'updated_at' =>
                                    $ahoraUtc,
                            ]);
                }

                return [
                    'id_orden' =>
                        $idOrden,

                    'existente' =>
                        false,
                ];
            },
            3
        );

        /*
        |--------------------------------------------------------------------------
        | Ir al resumen de compra
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'sidan.ordenes.show',
                $resultado[
                    'id_orden'
                ]
            )
            ->with(
                'success',
                $resultado[
                    'existente'
                ]
                ? 'Ya tenés una compra pendiente para esta actividad.'
                : 'Compra preparada correctamente. Revisá tu orden antes de pagar.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Limpiar configuraciones recibidas
    |--------------------------------------------------------------------------
    */

    private function limpiarCampos(
        mixed $campos
    ): array {
        if (!is_array($campos)) {
            return [];
        }

        $resultado = [];

        foreach (
            array_slice(
                $campos,
                0,
                20,
                true
            ) as $clave => $valor
        ) {
            if (
                !is_string($clave) ||
                (
                    !is_string($valor) &&
                    !is_numeric($valor)
                )
            ) {
                continue;
            }

            $claveLimpia =
                mb_substr(
                    trim($clave),
                    0,
                    100
                );

            $valorLimpio =
                mb_substr(
                    trim(
                        (string) $valor
                    ),
                    0,
                    200
                );

            if (
                $claveLimpia === '' ||
                $valorLimpio === ''
            ) {
                continue;
            }

            $resultado[
                $claveLimpia
            ] =
                $valorLimpio;
        }

        return $resultado;
    }

    /*
    |--------------------------------------------------------------------------
    | Stock comprometido de un producto
    |--------------------------------------------------------------------------
    */

    private function cantidadComprometidaItem(
        int $idItem
    ): int {
        $ahoraUtc =
            now('UTC');

        return (int) DB::table(
            'tbl_detalles_orden as detalle'
        )
            ->join(
                'tbl_ordenes as orden',
                'orden.id_orden',
                '=',
                'detalle.id_orden'
            )
            ->where(
                'detalle.id_item_actividad',
                $idItem
            )
            ->where(
                function ($query) use ($ahoraUtc) {
                    $query
                        ->whereIn(
                            'orden.estado',
                            [
                                'pagada',
                                'pagada_prueba',
                            ]
                        )
                        ->orWhere(
                            function ($query) use ($ahoraUtc) {
                                $query
                                    ->where(
                                        'orden.estado',
                                        'pendiente_pago'
                                    )
                                    ->where(
                                        function ($query) use ($ahoraUtc) {
                                            $query
                                                ->whereNull(
                                                    'orden.expira_en'
                                                )
                                                ->orWhere(
                                                    'orden.expira_en',
                                                    '>',
                                                    $ahoraUtc
                                                );
                                        }
                                    );
                            }
                        );
                }
            )
            ->sum(
                'detalle.cantidad'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Stock comprometido de una variante
    |--------------------------------------------------------------------------
    */

    private function cantidadComprometidaVariante(
        int $idVariante
    ): int {
        $ahoraUtc =
            now('UTC');

        return (int) DB::table(
            'tbl_detalles_orden as detalle'
        )
            ->join(
                'tbl_ordenes as orden',
                'orden.id_orden',
                '=',
                'detalle.id_orden'
            )
            ->where(
                'detalle.id_variante_item',
                $idVariante
            )
            ->where(
                function ($query) use ($ahoraUtc) {
                    $query
                        ->whereIn(
                            'orden.estado',
                            [
                                'pagada',
                                'pagada_prueba',
                            ]
                        )
                        ->orWhere(
                            function ($query) use ($ahoraUtc) {
                                $query
                                    ->where(
                                        'orden.estado',
                                        'pendiente_pago'
                                    )
                                    ->where(
                                        function ($query) use ($ahoraUtc) {
                                            $query
                                                ->whereNull(
                                                    'orden.expira_en'
                                                )
                                                ->orWhere(
                                                    'orden.expira_en',
                                                    '>',
                                                    $ahoraUtc
                                                );
                                        }
                                    );
                            }
                        );
                }
            )
            ->sum(
                'detalle.cantidad'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Cantidad comprada dentro de una inscripción
    |--------------------------------------------------------------------------
    |
    | Ejemplo:
    |
    | Inscripción #5
    |
    | Orden #10
    | Camiseta x1
    |
    | Orden #18
    | Camiseta x1
    |
    | Total acumulado = 2
    |
    | Esto permite que max_por_inscripcion se respete entre diferentes órdenes.
    |
    */

    private function cantidadComprometidaInscripcionItem(
        int $idInscripcion,
        int $idItem
    ): int {
        $ahoraUtc =
            now('UTC');

        return (int) DB::table(
            'tbl_detalles_orden as detalle'
        )
            ->join(
                'tbl_ordenes as orden',
                'orden.id_orden',
                '=',
                'detalle.id_orden'
            )
            ->where(
                'orden.id_inscripcion',
                $idInscripcion
            )
            ->where(
                'orden.tipo_orden',
                'productos'
            )
            ->where(
                'detalle.id_item_actividad',
                $idItem
            )
            ->where(
                function ($query) use ($ahoraUtc) {
                    $query
                        ->whereIn(
                            'orden.estado',
                            [
                                'pagada',
                                'pagada_prueba',
                            ]
                        )
                        ->orWhere(
                            function ($query) use ($ahoraUtc) {
                                $query
                                    ->where(
                                        'orden.estado',
                                        'pendiente_pago'
                                    )
                                    ->where(
                                        function ($query) use ($ahoraUtc) {
                                            $query
                                                ->whereNull(
                                                    'orden.expira_en'
                                                )
                                                ->orWhere(
                                                    'orden.expira_en',
                                                    '>',
                                                    $ahoraUtc
                                                );
                                        }
                                    );
                            }
                        );
                }
            )
            ->sum(
                'detalle.cantidad'
            );
    }

    private function calcularPrecioConfigurado(
        float $precioBase,
        array $campos,
        array $reglas
    ): float {
        $precio = $precioBase;

        foreach ($reglas as $regla) {
            $clave = (string) ($regla['field_key'] ?? '');
            $opcion = (string) ($regla['option'] ?? '');

            if (
                $clave !== '' &&
                array_key_exists($clave, $campos) &&
                (string) $campos[$clave] === $opcion
            ) {
                $precio += (float) ($regla['price_increment'] ?? 0);
            }
        }

        return round($precio, 2);
    }

    private function validarCamposProducto(
        mixed $camposRecibidos,
        mixed $configuracion,
        string $nombreProducto
    ): array {
        if (!is_array($camposRecibidos)) {
            throw ValidationException::withMessages([
                'compra' => "Las opciones de {$nombreProducto} no son válidas.",
            ]);
        }

        $camposConfigurados = [];

        if (
            is_array($configuracion)
            && !empty($configuracion['enabled'])
            && is_array($configuracion['fields'] ?? null)
        ) {
            $camposConfigurados = $configuracion['fields'];
        }

        // Si el producto no tiene campos personalizados,
        // tampoco debe recibir valores adicionales.
        if (empty($camposConfigurados)) {
            if (!empty($camposRecibidos)) {
                throw ValidationException::withMessages([
                    'compra' =>
                        "{$nombreProducto} no admite opciones personalizadas.",
                ]);
            }

            return [];
        }

        $definiciones = [];

        foreach ($camposConfigurados as $definicion) {
            if (!is_array($definicion)) {
                continue;
            }

            $clave = trim((string) ($definicion['key'] ?? ''));

            if ($clave === '') {
                continue;
            }

            if (array_key_exists($clave, $definiciones)) {
                throw ValidationException::withMessages([
                    'compra' =>
                        "La configuración de {$nombreProducto} contiene campos duplicados.",
                ]);
            }

            $definiciones[$clave] = $definicion;
        }

        if (empty($definiciones)) {
            throw ValidationException::withMessages([
                'compra' =>
                    "La configuración de {$nombreProducto} no es válida.",
            ]);
        }

        // Rechazar claves que no estén definidas en MongoDB.
        foreach ($camposRecibidos as $clave => $valor) {
            if (
                !is_string($clave)
                || !array_key_exists($clave, $definiciones)
            ) {
                throw ValidationException::withMessages([
                    'compra' =>
                        "Se recibieron opciones no autorizadas para {$nombreProducto}.",
                ]);
            }
        }

        $resultado = [];

        foreach ($definiciones as $clave => $definicion) {
            $etiqueta = trim(
                (string) ($definicion['label'] ?? $clave)
            );

            $tipo = strtolower(
                trim((string) ($definicion['type'] ?? ''))
            );

            $obligatorio = filter_var(
                $definicion['required'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            $valor = $camposRecibidos[$clave] ?? null;

            if (
                $valor !== null
                && !is_string($valor)
                && !is_int($valor)
                && !is_float($valor)
            ) {
                throw ValidationException::withMessages([
                    'compra' =>
                        "El campo {$etiqueta} de {$nombreProducto} tiene un valor inválido.",
                ]);
            }

            $valor = trim((string) ($valor ?? ''));

            if ($valor === '') {
                if ($obligatorio) {
                    throw ValidationException::withMessages([
                        'compra' =>
                            "Debés completar {$etiqueta} para {$nombreProducto}.",
                    ]);
                }

                continue;
            }

            if (mb_strlen($valor) > 200) {
                throw ValidationException::withMessages([
                    'compra' =>
                        "El campo {$etiqueta} supera los 200 caracteres permitidos.",
                ]);
            }

            if ($tipo === 'lista') {

                $opciones = $definicion['options'] ?? [];

                if (!is_array($opciones)) {
                    throw ValidationException::withMessages([
                        'compra' =>
                            "Las opciones de {$etiqueta} no están configuradas correctamente.",
                    ]);
                }

                $opcionesPermitidas = array_map(
                    'strval',
                    array_filter(
                        $opciones,
                        fn($opcion) =>
                            is_string($opcion)
                            || is_int($opcion)
                            || is_float($opcion)
                    )
                );

                if (!in_array($valor, $opcionesPermitidas, true)) {
                    throw ValidationException::withMessages([
                        'compra' =>
                            "La opción seleccionada en {$etiqueta} para {$nombreProducto} no está permitida.",
                    ]);
                }

            } elseif ($tipo === 'texto') {

                // El texto ya fue validado como escalar
                // y limitado a 200 caracteres.

            } elseif ($tipo === 'numero') {

                /*
                 * Aceptamos números enteros y decimales.
                 * Ejemplos: 10, 25, 3.5, -2.
                 *
                 * Conservamos el valor como cadena para
                 * no alterar innecesariamente su representación.
                 */

                if (
                    !preg_match(
                        '/^[+-]?(?:\d+)(?:\.\d+)?$/D',
                        $valor
                    )
                ) {
                    throw ValidationException::withMessages([
                        'compra' =>
                            "El campo {$etiqueta} de {$nombreProducto} debe contener un número válido.",
                    ]);
                }

                if (!is_finite((float) $valor)) {
                    throw ValidationException::withMessages([
                        'compra' =>
                            "El campo {$etiqueta} de {$nombreProducto} contiene un número fuera del rango permitido.",
                    ]);
                }

            } else {

                throw ValidationException::withMessages([
                    'compra' =>
                        "El campo {$etiqueta} de {$nombreProducto} tiene un tipo no admitido.",
                ]);
            }

            $resultado[$clave] = $valor;
        }

        return $resultado;
    }
}