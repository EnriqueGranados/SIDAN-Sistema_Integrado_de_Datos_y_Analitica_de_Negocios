<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\Categoria;
use App\Models\Etiqueta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ActividadesPruebaUESSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'Este seeder solo se ejecuta con APP_ENV=local o testing.'
            );
        }

        $idCreador = DB::table('tbl_usuarios')
            ->where('correo', 'admin@sidan.test')
            ->where('estado_activo', true)
            ->value('id_usuario');

        $idCreador ??= DB::table('tbl_usuarios')
            ->where('estado_activo', true)
            ->orderBy('id_usuario')
            ->value('id_usuario');

        if (!$idCreador) {
            throw new RuntimeException(
                'Crea un usuario activo antes de ejecutar el seeder.'
            );
        }

        DB::transaction(function () use ($idCreador) {
            $ahora = now();

            $categorias = [];

            foreach ([
                'DEMO SIDAN - Comercio' =>
                    'Actividades comerciales ficticias para pruebas.',

                'DEMO SIDAN - Excursiones' =>
                    'Excursiones ficticias para pruebas.',

                'DEMO SIDAN - Formación' =>
                    'Congresos y actividades académicas ficticias.',

                'DEMO SIDAN - Cultura' =>
                    'Actividades culturales ficticias para pruebas.',
            ] as $nombre => $descripcion) {
                $categoria = Categoria::firstOrCreate(
                    [
                        'nombre' => $nombre,
                    ],
                    [
                        'slug' => Str::slug($nombre),
                        'descripcion' => $descripcion,
                        'activo' => true,
                    ]
                );

                $categorias[$nombre] =
                    $categoria->id_categoria;
            }

            $etiquetas = [];

            foreach ([
                'Demo SIDAN',
                'Demo inscripción obligatoria',
                'Demo inscripción gratuita',
                'Demo con pago',
                'Demo con productos',
            ] as $nombre) {
                $etiqueta = Etiqueta::firstOrCreate(
                    [
                        'nombre' => $nombre,
                    ],
                    [
                        'slug' => Str::slug($nombre),
                        'activo' => true,
                    ]
                );

                $etiquetas[$nombre] =
                    $etiqueta->id_etiqueta;
            }

            $idEspacio = DB::table('tbl_espacios')
                ->where('activo', true)
                ->where('permite_actividades', true)
                ->value('id_espacio');

            $visibleDesde =
                $ahora->copy()->subMinute();

            $visibleHasta =
                $ahora->copy()->addDay();

            $inscripcionDesde =
                $ahora->copy()->subMinute();

            $inscripcionHasta =
                $ahora->copy()->addDay();

            $actividades = [
                [
                    'codigo' =>
                        'camisetas',

                    'nombre' =>
                        'Feria y venta de camisetas universitarias',

                    'tipo_participacion' =>
                        'registro_pago',

                    'categoria' =>
                        'DEMO SIDAN - Comercio',

                    'precio_inscripcion' =>
                        '3.00',

                    'cupo_total' =>
                        100,

                    'duracion_horas' =>
                        4,

                    'resumen' =>
                        'Actividad de prueba con inscripción obligatoria y venta de camisetas y recuerdos.',

                    'descripcion' =>
                        'Actividad ficticia creada para probar el flujo de inscripción, productos, órdenes y pagos de SIDAN.',

                    'items' => [
                        [
                            'nombre' =>
                                'Camiseta SIDAN clásica',

                            'descripcion' =>
                                'Camiseta oficial de demostración SIDAN.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '10.00',

                            'stock' =>
                                80,

                            'maximo' =>
                                3,

                            'variantes' => [
                                [
                                    'nombre' =>
                                        'Talla S',

                                    'codigo' =>
                                        'S',

                                    'precio' =>
                                        '10.00',

                                    'stock' =>
                                        20,
                                ],

                                [
                                    'nombre' =>
                                        'Talla M',

                                    'codigo' =>
                                        'M',

                                    'precio' =>
                                        '10.00',

                                    'stock' =>
                                        25,
                                ],

                                [
                                    'nombre' =>
                                        'Talla L',

                                    'codigo' =>
                                        'L',

                                    'precio' =>
                                        '10.00',

                                    'stock' =>
                                        25,
                                ],

                                [
                                    'nombre' =>
                                        'Talla XL',

                                    'codigo' =>
                                        'XL',

                                    'precio' =>
                                        '11.00',

                                    'stock' =>
                                        10,
                                ],
                            ],
                        ],

                        [
                            'nombre' =>
                                'Gorra SIDAN',

                            'descripcion' =>
                                'Gorra universitaria de demostración.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '6.00',

                            'stock' =>
                                40,

                            'maximo' =>
                                2,
                        ],

                        [
                            'nombre' =>
                                'Bolsa reutilizable SIDAN',

                            'descripcion' =>
                                'Bolsa de tela reutilizable de demostración.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '4.00',

                            'stock' =>
                                50,

                            'maximo' =>
                                3,
                        ],
                    ],
                ],

                [
                    'codigo' =>
                        'excursion',

                    'nombre' =>
                        'Excursión académica universitaria',

                    'tipo_participacion' =>
                        'registro_pago',

                    'categoria' =>
                        'DEMO SIDAN - Excursiones',

                    'precio_inscripcion' =>
                        '15.00',

                    'cupo_total' =>
                        40,

                    'duracion_horas' =>
                        8,

                    'resumen' =>
                        'Excursión académica de prueba con inscripción obligatoria y productos adicionales.',

                    'descripcion' =>
                        'Actividad ficticia para probar inscripción de pago, productos opcionales y cobro mediante SIDAN.',

                    'items' => [
                        [
                            'nombre' =>
                                'Camiseta de la excursión',

                            'descripcion' =>
                                'Camiseta conmemorativa de la excursión.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '8.00',

                            'stock' =>
                                40,

                            'maximo' =>
                                2,

                            'variantes' => [
                                [
                                    'nombre' =>
                                        'Talla M',

                                    'codigo' =>
                                        'M',

                                    'precio' =>
                                        '8.00',

                                    'stock' =>
                                        15,
                                ],

                                [
                                    'nombre' =>
                                        'Talla L',

                                    'codigo' =>
                                        'L',

                                    'precio' =>
                                        '8.00',

                                    'stock' =>
                                        15,
                                ],

                                [
                                    'nombre' =>
                                        'Talla XL',

                                    'codigo' =>
                                        'XL',

                                    'precio' =>
                                        '9.00',

                                    'stock' =>
                                        10,
                                ],
                            ],
                        ],

                        [
                            'nombre' =>
                                'Kit de viaje',

                            'descripcion' =>
                                'Kit con refrigerio y artículos básicos para la excursión.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '5.00',

                            'stock' =>
                                40,

                            'maximo' =>
                                1,
                        ],

                        [
                            'nombre' =>
                                'Botella reutilizable',

                            'descripcion' =>
                                'Botella reutilizable para la excursión.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '6.00',

                            'stock' =>
                                30,

                            'maximo' =>
                                2,
                        ],
                    ],
                ],

                [
                    'codigo' =>
                        'congreso',

                    'nombre' =>
                        'Congreso estudiantil de Sistemas 2026',

                    'tipo_participacion' =>
                        'registro_pago',

                    'categoria' =>
                        'DEMO SIDAN - Formación',

                    'precio_inscripcion' =>
                        '20.00',

                    'cupo_total' =>
                        120,

                    'duracion_horas' =>
                        6,

                    'resumen' =>
                        'Congreso de prueba con inscripción obligatoria y productos opcionales.',

                    'descripcion' =>
                        'Congreso ficticio utilizado para probar inscripciones de pago, productos, órdenes y Wompi.',

                    'items' => [
                        [
                            'nombre' =>
                                'Camiseta oficial del congreso',

                            'descripcion' =>
                                'Camiseta oficial de demostración del congreso.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '12.00',

                            'stock' =>
                                100,

                            'maximo' =>
                                2,

                            'variantes' => [
                                [
                                    'nombre' =>
                                        'Talla S',

                                    'codigo' =>
                                        'S',

                                    'precio' =>
                                        '12.00',

                                    'stock' =>
                                        20,
                                ],

                                [
                                    'nombre' =>
                                        'Talla M',

                                    'codigo' =>
                                        'M',

                                    'precio' =>
                                        '12.00',

                                    'stock' =>
                                        30,
                                ],

                                [
                                    'nombre' =>
                                        'Talla L',

                                    'codigo' =>
                                        'L',

                                    'precio' =>
                                        '12.00',

                                    'stock' =>
                                        30,
                                ],

                                [
                                    'nombre' =>
                                        'Talla XL',

                                    'codigo' =>
                                        'XL',

                                    'precio' =>
                                        '13.00',

                                    'stock' =>
                                        20,
                                ],
                            ],
                        ],

                        [
                            'nombre' =>
                                'Kit del congresista',

                            'descripcion' =>
                                'Paquete con artículos promocionales para asistentes.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '7.50',

                            'stock' =>
                                120,

                            'maximo' =>
                                1,
                        ],

                        [
                            'nombre' =>
                                'Cuaderno del congreso',

                            'descripcion' =>
                                'Cuaderno oficial de demostración.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '3.50',

                            'stock' =>
                                100,

                            'maximo' =>
                                3,
                        ],
                    ],
                ],

                [
                    'codigo' =>
                        'expo-tecnologica',

                    'nombre' =>
                        'Expo tecnológica universitaria',

                    'tipo_participacion' =>
                        'registro_gratuito',

                    'categoria' =>
                        'DEMO SIDAN - Formación',

                    'precio_inscripcion' =>
                        null,

                    'cupo_total' =>
                        150,

                    'duracion_horas' =>
                        5,

                    'resumen' =>
                        'Expo tecnológica gratuita con productos promocionales opcionales.',

                    'descripcion' =>
                        'Actividad ficticia para probar una inscripción gratuita con posibilidad de adquirir productos adicionales mediante SIDAN.',

                    'items' => [
                        [
                            'nombre' =>
                                'Camiseta Expo Tecnología',

                            'descripcion' =>
                                'Camiseta conmemorativa de la Expo Tecnológica.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '10.00',

                            'stock' =>
                                80,

                            'maximo' =>
                                2,

                            'variantes' => [
                                [
                                    'nombre' =>
                                        'Talla S',

                                    'codigo' =>
                                        'S',

                                    'precio' =>
                                        '10.00',

                                    'stock' =>
                                        15,
                                ],

                                [
                                    'nombre' =>
                                        'Talla M',

                                    'codigo' =>
                                        'M',

                                    'precio' =>
                                        '10.00',

                                    'stock' =>
                                        25,
                                ],

                                [
                                    'nombre' =>
                                        'Talla L',

                                    'codigo' =>
                                        'L',

                                    'precio' =>
                                        '10.00',

                                    'stock' =>
                                        25,
                                ],

                                [
                                    'nombre' =>
                                        'Talla XL',

                                    'codigo' =>
                                        'XL',

                                    'precio' =>
                                        '11.00',

                                    'stock' =>
                                        15,
                                ],
                            ],
                        ],

                        [
                            'nombre' =>
                                'Taza Expo Tecnología',

                            'descripcion' =>
                                'Taza conmemorativa de la actividad.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '5.00',

                            'stock' =>
                                60,

                            'maximo' =>
                                2,
                        ],

                        [
                            'nombre' =>
                                'Paquete de stickers tecnológicos',

                            'descripcion' =>
                                'Paquete de stickers temáticos de programación y tecnología.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '2.50',

                            'stock' =>
                                100,

                            'maximo' =>
                                3,
                        ],
                    ],
                ],

                [
                    'codigo' =>
                        'festival-cultural',

                    'nombre' =>
                        'Festival cultural universitario',

                    'tipo_participacion' =>
                        'registro_gratuito',

                    'categoria' =>
                        'DEMO SIDAN - Cultura',

                    'precio_inscripcion' =>
                        null,

                    'cupo_total' =>
                        200,

                    'duracion_horas' =>
                        6,

                    'resumen' =>
                        'Festival universitario gratuito con productos conmemorativos disponibles para compra.',

                    'descripcion' =>
                        'Actividad ficticia para probar el registro gratuito junto con compras opcionales realizadas dentro de la actividad.',

                    'items' => [
                        [
                            'nombre' =>
                                'Camiseta Festival Cultural',

                            'descripcion' =>
                                'Camiseta conmemorativa del Festival Cultural.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '9.00',

                            'stock' =>
                                100,

                            'maximo' =>
                                2,

                            'variantes' => [
                                [
                                    'nombre' =>
                                        'Talla S',

                                    'codigo' =>
                                        'S',

                                    'precio' =>
                                        '9.00',

                                    'stock' =>
                                        20,
                                ],

                                [
                                    'nombre' =>
                                        'Talla M',

                                    'codigo' =>
                                        'M',

                                    'precio' =>
                                        '9.00',

                                    'stock' =>
                                        30,
                                ],

                                [
                                    'nombre' =>
                                        'Talla L',

                                    'codigo' =>
                                        'L',

                                    'precio' =>
                                        '9.00',

                                    'stock' =>
                                        30,
                                ],

                                [
                                    'nombre' =>
                                        'Talla XL',

                                    'codigo' =>
                                        'XL',

                                    'precio' =>
                                        '10.00',

                                    'stock' =>
                                        20,
                                ],
                            ],
                        ],

                        [
                            'nombre' =>
                                'Pulsera conmemorativa',

                            'descripcion' =>
                                'Pulsera oficial del Festival Cultural.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '2.00',

                            'stock' =>
                                150,

                            'maximo' =>
                                3,
                        ],

                        [
                            'nombre' =>
                                'Bolsa de tela Festival Cultural',

                            'descripcion' =>
                                'Bolsa reutilizable con diseño conmemorativo.',

                            'tipo' =>
                                'producto',

                            'precio' =>
                                '4.50',

                            'stock' =>
                                80,

                            'maximo' =>
                                2,
                        ],
                    ],
                ],
            ];

            $slugsActuales = collect(
                $actividades
            )
                ->map(
                    fn ($actividad) =>
                        'demo-ues-' .
                        $actividad['codigo']
                )
                ->all();

            DB::table('tbl_actividades')
                ->where(
                    'slug',
                    'like',
                    'demo-ues-%'
                )
                ->whereNotIn(
                    'slug',
                    $slugsActuales
                )
                ->update([
                    'mostrar_en_welcome' =>
                        false,

                    'eliminado_en' =>
                        $ahora,

                    'updated_at' =>
                        $ahora,
                ]);

            foreach (
                $actividades as $indice => $configuracion
            ) {
                $slug =
                    'demo-ues-' .
                    $configuracion['codigo'];

                $realizacionDesde =
                    $ahora
                        ->copy()
                        ->addDay()
                        ->addHours(
                            $indice + 1
                        );

                $realizacionHasta =
                    $realizacionDesde
                        ->copy()
                        ->addHours(
                            $configuracion[
                                'duracion_horas'
                            ]
                        );

                $datosActividad = [
                    'id_categoria' =>
                        $categorias[
                            $configuracion[
                                'categoria'
                            ]
                        ],

                    'id_espacio' =>
                        $idEspacio,

                    'ubicacion_externa' =>
                        'UES - FMO, San Miguel (actividad de prueba)',

                    'nombre' =>
                        '[DEMO UES] ' .
                        $configuracion[
                            'nombre'
                        ],

                    'resumen' =>
                        $configuracion[
                            'resumen'
                        ],

                    'descripcion' =>
                        $configuracion[
                            'descripcion'
                        ],

                    'estado_publicacion' =>
                        'publicada',

                    'estado_operativo' =>
                        'normal',

                    'visibilidad' =>
                        'publica',

                    'revision_actual' =>
                        1,

                    'destacada' =>
                        false,

                    'prioridad' =>
                        25,

                    'tipo_participacion' =>
                        $configuracion[
                            'tipo_participacion'
                        ],

                    'habilita_inscripcion' =>
                        true,

                    'requiere_cuenta' =>
                        false,

                    'permite_lista_espera' =>
                        false,

                    'cupo_total' =>
                        $configuracion[
                            'cupo_total'
                        ],

                    'precio_inscripcion' =>
                        $configuracion[
                            'precio_inscripcion'
                        ],

                    'inscripcion_desde' =>
                        $inscripcionDesde,

                    'inscripcion_hasta' =>
                        $inscripcionHasta,

                    'visible_desde' =>
                        $visibleDesde,

                    'visible_hasta' =>
                        $visibleHasta,

                    'realizacion_desde' =>
                        $realizacionDesde,

                    'realizacion_hasta' =>
                        $realizacionHasta,

                    'mostrar_en_welcome' =>
                        true,

                    'actualizado_por' =>
                        $idCreador,

                    'eliminado_en' =>
                        null,

                    'updated_at' =>
                        $ahora,
                ];

                $actividadAnterior =
                    DB::table(
                        'tbl_actividades'
                    )
                        ->where(
                            'slug',
                            $slug
                        )
                        ->first();

                if ($actividadAnterior) {
                    DB::table(
                        'tbl_actividades'
                    )
                        ->where(
                            'id_actividad',
                            $actividadAnterior
                                ->id_actividad
                        )
                        ->update(
                            $datosActividad
                        );

                    $idActividad =
                        (int) $actividadAnterior
                            ->id_actividad;
                } else {
                    $idActividad =
                        DB::table(
                            'tbl_actividades'
                        )
                            ->insertGetId(
                                [
                                    ...$datosActividad,

                                    'slug' =>
                                        $slug,

                                    'creado_por' =>
                                        $idCreador,

                                    'created_at' =>
                                        $ahora,
                                ],
                                'id_actividad'
                            );
                }

                $actividad =
                    Actividad::findOrFail(
                        $idActividad
                    );

                $idsEtiquetas = [
                    $etiquetas[
                        'Demo SIDAN'
                    ],

                    $etiquetas[
                        'Demo con productos'
                    ],
                ];

                if (
                    $configuracion[
                        'tipo_participacion'
                    ] === 'registro_pago'
                ) {
                    $idsEtiquetas[] =
                        $etiquetas[
                            'Demo inscripción obligatoria'
                        ];

                    $idsEtiquetas[] =
                        $etiquetas[
                            'Demo con pago'
                        ];
                } else {
                    $idsEtiquetas[] =
                        $etiquetas[
                            'Demo inscripción gratuita'
                        ];
                }

                $actividad
                    ->etiquetas()
                    ->sync(
                        $idsEtiquetas
                    );

                $nombresItemsActuales =
                    collect(
                        $configuracion[
                            'items'
                        ]
                    )
                        ->pluck(
                            'nombre'
                        )
                        ->all();

                DB::table(
                    'tbl_items_actividad'
                )
                    ->where(
                        'id_actividad',
                        $idActividad
                    )
                    ->whereNotIn(
                        'nombre',
                        $nombresItemsActuales
                    )
                    ->update([
                        'activo' =>
                            false,
                    ]);

                foreach (
                    $configuracion['items']
                    as $ordenItem => $item
                ) {
                    DB::table(
                        'tbl_items_actividad'
                    )
                        ->updateOrInsert(
                            [
                                'id_actividad' =>
                                    $idActividad,

                                'nombre' =>
                                    $item[
                                        'nombre'
                                    ],
                            ],
                            [
                                'descripcion' =>
                                    $item[
                                        'descripcion'
                                    ],

                                'tipo' =>
                                    $item[
                                        'tipo'
                                    ],

                                'precio' =>
                                    $item[
                                        'precio'
                                    ],

                                'costo_referencia' =>
                                    0,

                                'stock_total' =>
                                    $item[
                                        'stock'
                                    ],

                                'min_por_inscripcion' =>
                                    1,

                                'max_por_inscripcion' =>
                                    $item[
                                        'maximo'
                                    ],

                                'requiere_participante' =>
                                    false,

                                'orden' =>
                                    $ordenItem + 1,

                                'activo' =>
                                    true,
                            ]
                        );

                    if (
                        empty(
                            $item[
                                'variantes'
                            ]
                        )
                    ) {
                        continue;
                    }

                    $idItem =
                        DB::table(
                            'tbl_items_actividad'
                        )
                            ->where(
                                'id_actividad',
                                $idActividad
                            )
                            ->where(
                                'nombre',
                                $item[
                                    'nombre'
                                ]
                            )
                            ->value(
                                'id_item_actividad'
                            );

                    $skusActuales = [];

                    foreach (
                        $item['variantes']
                        as $ordenVariante => $variante
                    ) {
                        $sku =
                            'SIDAN-DEMO-' .
                            strtoupper(
                                $configuracion[
                                    'codigo'
                                ]
                            ) .
                            '-' .
                            $variante[
                                'codigo'
                            ];

                        $skusActuales[] =
                            $sku;

                        DB::table(
                            'tbl_variantes_item'
                        )
                            ->updateOrInsert(
                                [
                                    'sku' =>
                                        $sku,
                                ],
                                [
                                    'id_item_actividad' =>
                                        $idItem,

                                    'nombre_variante' =>
                                        $variante[
                                            'nombre'
                                        ],

                                    'precio' =>
                                        $variante[
                                            'precio'
                                        ],

                                    'costo_referencia' =>
                                        0,

                                    'stock_total' =>
                                        $variante[
                                            'stock'
                                        ],

                                    'orden' =>
                                        $ordenVariante + 1,

                                    'activo' =>
                                        true,
                                ]
                            );
                    }

                    DB::table(
                        'tbl_variantes_item'
                    )
                        ->where(
                            'id_item_actividad',
                            $idItem
                        )
                        ->whereNotIn(
                            'sku',
                            $skusActuales
                        )
                        ->update([
                            'activo' =>
                                false,
                        ]);
                }
            }
        });
    }
}