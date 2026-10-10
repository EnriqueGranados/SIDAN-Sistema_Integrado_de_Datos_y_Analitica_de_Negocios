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
        // Evitar insertar datos ficticios en producción.
        if (!app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'Este seeder solo se ejecuta con APP_ENV=local o testing.'
            );
        }

        // Buscar un administrador o cualquier usuario activo.
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

            // 1. CATEGORÍAS DE PRUEBA
            $categorias = [];

            foreach ([
                'DEMO SIDAN - Formación' =>
                    'Cursos y talleres universitarios ficticios.',
                'DEMO SIDAN - Cultura' =>
                    'Actividades culturales ficticias.',
                'DEMO SIDAN - Comercio' =>
                    'Ferias y ventas ficticias.',
            ] as $nombre => $descripcion) {
                $categoria = Categoria::firstOrCreate(
                    ['nombre' => $nombre],
                    [
                        'slug' => Str::slug($nombre),
                        'descripcion' => $descripcion,
                        'activo' => true,
                    ]
                );

                $categorias[$nombre] = $categoria->id_categoria;
            }

            // 2. ETIQUETAS DE PRUEBA
            $etiquetas = [];

            foreach ([
                'Demo SIDAN',
                'Demo gratuita',
                'Demo con pago',
                'Demo informativa',
                'Demo venta',
                'Demo restriccion',
                'Demo sesiones',
            ] as $nombre) {
                $etiqueta = Etiqueta::firstOrCreate(
                    ['nombre' => $nombre],
                    [
                        'slug' => Str::slug($nombre),
                        'activo' => true,
                    ]
                );

                $etiquetas[$nombre] = $etiqueta->id_etiqueta;
            }

            // 3. ESPACIO EXISTENTE, SI ESTÁ DISPONIBLE
            $idEspacio = DB::table('tbl_espacios')
                ->where('activo', true)
                ->where('permite_actividades', true)
                ->value('id_espacio');

            // 4. FECHAS RELATIVAS PARA QUE LAS PRUEBAS
            // PUEDAN REPETIRSE EN OTROS DÍAS.
            $ahora = now();

            $inscripcionInicio = $ahora->copy()->subDay();
            $inscripcionFin = $ahora->copy()->addDays(7);

            $eventoInicio = $ahora->copy()
                ->addDays(14)
                ->setTime(9, 0);

            $eventoFin = $eventoInicio->copy()->addHours(4);

            // 5. ESCENARIOS DE PRUEBA
            // Formato:
            // [codigo, nombre, tipo, precio, cupo, opciones]

            $escenarios = [
                [
                    '01-informativa',
                    'Charla de bienvenida FMO',
                    'informativa', null, null, []
                ],
                [
                    '02-gratis-visitante',
                    'Taller abierto de Git y GitHub',
                    'registro_gratuito', null, 40, []
                ],
                [
                    '03-gratis-cuenta',
                    'Laboratorio de Laravel para estudiantes',
                    'registro_gratuito', null, 25,
                    ['requiere_cuenta' => true]
                ],
                [
                    '04-gratis-agotada',
                    'Charla de orientación sin cupos',
                    'registro_gratuito', null, 0, []
                ],
                [
                    '05-gratis-ilimitada',
                    'Conferencia virtual de software libre',
                    'registro_gratuito', null, null, []
                ],
                [
                    '06-gratis-sesiones',
                    'Semana académica de Bases de Datos',
                    'registro_gratuito', null, 60,
                    ['sesiones' => true]
                ],
                [
                    '07-pago-5',
                    'Curso introductorio de PostgreSQL',
                    'registro_pago', '5.00', 40, []
                ],
                [
                    '08-pago-15-cuenta',
                    'Congreso estudiantil de Sistemas',
                    'registro_pago', '15.00', 100,
                    ['requiere_cuenta' => true]
                ],
                [
                    '09-pago-321',
                    'Diplomado universitario de Data Analytics',
                    'registro_pago', '321.00', 30, []
                ],
                [
                    '10-pago-ultimo-cupo',
                    'Taller exclusivo de redes (1 cupo)',
                    'registro_pago', '8.00', 1, []
                ],
                [
                    '11-pago-futura',
                    'Seminario con inscripción aún no abierta',
                    'registro_pago', '12.00', 50,
                    [
                        'inscripcion_desde' =>
                            $ahora->copy()->addDays(3),
                        'inscripcion_hasta' =>
                            $ahora->copy()->addDays(10),
                    ]
                ],
                [
                    '12-pago-cerrada',
                    'Jornada con inscripción cerrada',
                    'registro_pago', '10.00', 50,
                    [
                        'inscripcion_desde' =>
                            $ahora->copy()->subDays(10),
                        'inscripcion_hasta' =>
                            $ahora->copy()->subDay(),
                    ]
                ],
                [
                    '13-pago-suspendida',
                    'Congreso suspendido por organización',
                    'registro_pago', '20.00', 70,
                    ['estado_operativo' => 'suspendida']
                ],
                [
                    '14-pago-cancelada',
                    'Curso cancelado de manera administrativa',
                    'registro_pago', '18.00', 30,
                    ['estado_operativo' => 'cancelada']
                ],
                [
                    '15-pago-formulario',
                    'Curso que solicita carnet estudiantil',
                    'registro_pago', '7.00', 35,
                    ['formulario' => true]
                ],
                [
                    '16-venta-productos',
                    'Feria de camisetas y recuerdos FMO',
                    'venta_directa', null, null,
                    ['productos' => true]
                ],
                [
                    '17-venta-servicios',
                    'Feria de servicios universitarios',
                    'venta_directa', null, null,
                    ['servicios' => true]
                ],
                [
                    '18-venta-vacia',
                    'Feria sin artículos disponibles',
                    'venta_directa', null, null, []
                ],
                [
                    '19-borrador',
                    'Actividad pendiente de configurar (borrador)',
                    'informativa', null, null,
                    ['estado_publicacion' => 'borrador']
                ],
                [
                    '20-interna',
                    'Reunión informativa de docentes (interna)',
                    'informativa', null, null,
                    ['visibilidad' => 'interna']
                ],
                [
                    '21-publicacion-futura',
                    'Anuncio que aún no debe mostrarse',
                    'informativa', null, null,
                    [
                        'visible_desde' =>
                            $ahora->copy()->addDays(3)
                    ]
                ],
                [
                    '22-pago-mas-productos',
                    'Congreso con inscripción y camiseta opcional',
                    'registro_pago', '20.00', 70,
                    ['productos' => true]
                ],
                [
                    '23-pago-sin-precio',
                    'Actividad pagada sin precio configurado',
                    'registro_pago', null, 40, []
                ],
                [
                    '24-gratis-lista-espera',
                    'Taller lleno con lista de espera',
                    'registro_gratuito', null, 0,
                    ['permite_lista_espera' => true]
                ],
                [
                    '25-pago-agotada',
                    'Seminario de pago sin cupos',
                    'registro_pago', '10.00', 0, []
                ],
            ];

            // 6. CREAR O ACTUALIZAR ACTIVIDADES DEMO
            foreach ($escenarios as [
                $codigo,
                $nombre,
                $tipo,
                $precio,
                $cupo,
                $opciones
            ]) {
                $slug = 'demo-ues-' . $codigo;

                $requiereInscripcion = in_array(
                    $tipo,
                    ['registro_gratuito', 'registro_pago'],
                    true
                );

                $idCategoria = $categorias[
                    $tipo === 'venta_directa'
                        ? 'DEMO SIDAN - Comercio'
                        : (
                            $tipo === 'informativa'
                                ? 'DEMO SIDAN - Cultura'
                                : 'DEMO SIDAN - Formación'
                        )
                ];

                $datos = [
                    'id_categoria' => $idCategoria,
                    'id_espacio' => $idEspacio,
                    'ubicacion_externa' =>
                        'UES - FMO, San Miguel (escenario simulado)',

                    'nombre' => '[DEMO UES] ' . $nombre,
                    'resumen' =>
                        'Escenario ' . $codigo .
                        ' para probar SIDAN. No es una actividad oficial.',

                    'descripcion' =>
                        'Registro de demostración para probar ' .
                        'inscripciones, cupos, publicación y cobros. ' .
                        'No realizar pagos productivos.',

                    'estado_publicacion' =>
                        $opciones['estado_publicacion'] ?? 'publicada',

                    'estado_operativo' =>
                        $opciones['estado_operativo'] ?? 'normal',

                    'visibilidad' =>
                        $opciones['visibilidad'] ?? 'publica',

                    'revision_actual' => 1,
                    'destacada' => false,
                    'prioridad' => 25,

                    'tipo_participacion' => $tipo,
                    'habilita_inscripcion' => $requiereInscripcion,

                    'requiere_cuenta' =>
                        $opciones['requiere_cuenta'] ?? false,

                    'permite_lista_espera' =>
                        $opciones['permite_lista_espera'] ?? false,

                    'cupo_total' => $cupo,
                    'precio_inscripcion' => $precio,

                    'inscripcion_desde' => $requiereInscripcion
                        ? ($opciones['inscripcion_desde'] ?? $inscripcionInicio)
                        : null,

                    'inscripcion_hasta' => $requiereInscripcion
                        ? ($opciones['inscripcion_hasta'] ?? $inscripcionFin)
                        : null,

                    'visible_desde' =>
                        $opciones['visible_desde'] ?? null,

                    'visible_hasta' => null,
                    'realizacion_desde' => $eventoInicio,
                    'realizacion_hasta' => $eventoFin,
                    'mostrar_en_welcome' => true,

                    'actualizado_por' => $idCreador,
                    'eliminado_en' => null,
                    'updated_at' => now(),
                ];

                // Solo actualizar actividades del propio seeder.
                $anterior = DB::table('tbl_actividades')
                    ->where('slug', $slug)
                    ->first();

                if ($anterior) {
                    DB::table('tbl_actividades')
                        ->where('id_actividad', $anterior->id_actividad)
                        ->update($datos);

                    $idActividad = (int) $anterior->id_actividad;
                } else {
                    $idActividad = DB::table('tbl_actividades')
                        ->insertGetId([
                            ...$datos,
                            'slug' => $slug,
                            'creado_por' => $idCreador,
                            'created_at' => now(),
                        ], 'id_actividad');
                }

                $actividad = Actividad::findOrFail($idActividad);

                // 7. RELACIONAR ETIQUETAS
                $etiquetaTipo = match ($tipo) {
                    'registro_gratuito' => 'Demo gratuita',
                    'registro_pago' => 'Demo con pago',
                    'venta_directa' => 'Demo venta',
                    default => 'Demo informativa',
                };

                $idsEtiquetas = [
                    $etiquetas['Demo SIDAN'],
                    $etiquetas[$etiquetaTipo],
                ];

                if (!empty($opciones['sesiones'])) {
                    $idsEtiquetas[] = $etiquetas['Demo sesiones'];
                }

                if (
                    $cupo === 0 ||
                    isset($opciones['estado_operativo'])
                ) {
                    $idsEtiquetas[] = $etiquetas['Demo restriccion'];
                }

                $actividad->etiquetas()
                    ->syncWithoutDetaching($idsEtiquetas);

                // 8. CREAR SESIONES
                if (!empty($opciones['sesiones'])) {
                    $sesiones = [
                        ['Introducción a PostgreSQL', 1, 35, false],
                        ['Práctica de consultas SQL', 2, 20, true],
                    ];

                    foreach ($sesiones as [
                        $sesionNombre,
                        $orden,
                        $cupoSesion,
                        $requiereReserva
                    ]) {
                        DB::table('tbl_sesiones_actividad')
                            ->updateOrInsert(
                                [
                                    'id_actividad' => $idActividad,
                                    'nombre' => $sesionNombre,
                                ],
                                [
                                    'id_espacio' => $idEspacio,
                                    'fecha_inicio' => $eventoInicio
                                        ->copy()
                                        ->addDays($orden - 1),

                                    'fecha_fin' => $eventoInicio
                                        ->copy()
                                        ->addDays($orden - 1)
                                        ->addHours(2),

                                    'ubicacion' =>
                                        'FMO - Espacio de demostración',

                                    'cupo' => $cupoSesion,
                                    'requiere_reserva' => $requiereReserva,
                                    'obligatoria' => $orden === 1,
                                    'orden' => $orden,
                                    'estado' => 'programada',
                                ]
                            );
                    }
                }

                // 9. CREAR FORMULARIO PERSONALIZADO
                if (!empty($opciones['formulario'])) {
                    DB::table('tbl_formularios_actividad')
                        ->updateOrInsert(
                            [
                                'id_actividad' => $idActividad,
                                'version' => 1,
                            ],
                            [
                                'nombre' =>
                                    'Formulario de inscripción UES (demo)',
                                'estado' => 'publicado',
                                'creado_por' => $idCreador,
                                'publicado_en' => now(),
                            ]
                        );

                    $idFormulario = DB::table(
                        'tbl_formularios_actividad'
                    )
                        ->where('id_actividad', $idActividad)
                        ->where('version', 1)
                        ->value('id_formulario');

                    DB::table('tbl_campos_formulario')
                        ->updateOrInsert(
                            [
                                'id_formulario' => $idFormulario,
                                'clave' => 'carnet_ues',
                            ],
                            [
                                'nombre' => 'Carnet universitario',
                                'tipo_dato' => 'texto',
                                'obligatorio' => true,
                                'aplica_a' => 'inscripcion',
                                'orden' => 1,
                                'activo' => true,
                            ]
                        );
                }

                // 10. CREAR PRODUCTOS O SERVICIOS
                if (
                    !empty($opciones['productos']) ||
                    !empty($opciones['servicios'])
                ) {
                    $items = !empty($opciones['productos'])
                        ? [
                            [
                                'Camiseta conmemorativa UES (demo)',
                                'producto',
                                '10.00',
                                60
                            ],
                            [
                                'Cuaderno universitario (demo)',
                                'producto',
                                '3.50',
                                100
                            ],
                        ]
                        : [
                            [
                                'Acceso a taller opcional (demo)',
                                'servicio',
                                '6.00',
                                25
                            ]
                        ];

                    foreach ($items as $orden => [
                        $itemNombre,
                        $itemTipo,
                        $itemPrecio,
                        $stock
                    ]) {
                        DB::table('tbl_items_actividad')
                            ->updateOrInsert(
                                [
                                    'id_actividad' => $idActividad,
                                    'nombre' => $itemNombre,
                                ],
                                [
                                    'descripcion' =>
                                        'Artículo o servicio ficticio para pruebas.',

                                    'tipo' => $itemTipo,
                                    'precio' => $itemPrecio,
                                    'costo_referencia' => 0,
                                    'stock_total' => $stock,

                                    'min_por_inscripcion' => 1,
                                    'max_por_inscripcion' => 5,
                                    'requiere_participante' => false,

                                    'orden' => $orden + 1,
                                    'activo' => true,
                                ]
                            );
                    }

                    // 11. VARIANTES DE LA CAMISETA
                    if (!empty($opciones['productos'])) {
                        $idCamiseta = DB::table('tbl_items_actividad')
                            ->where('id_actividad', $idActividad)
                            ->where(
                                'nombre',
                                'Camiseta conmemorativa UES (demo)'
                            )
                            ->value('id_item_actividad');

                        foreach ([
                            ['M', '10.00', 30, 1],
                            ['XL', '12.00', 20, 2],
                        ] as [
                            $talla,
                            $precioVariante,
                            $stockVariante,
                            $orden
                        ]) {
                            DB::table('tbl_variantes_item')
                                ->updateOrInsert(
                                    [
                                        'sku' =>
                                            'SIDAN-DEMO-UES-' .
                                            $codigo . '-' . $talla,
                                    ],
                                    [
                                        'id_item_actividad' => $idCamiseta,
                                        'nombre_variante' =>
                                            'Talla ' . $talla,

                                        'precio' => $precioVariante,
                                        'costo_referencia' => 0,
                                        'stock_total' => $stockVariante,

                                        'orden' => $orden,
                                        'activo' => true,
                                    ]
                                );
                        }
                    }
                }
            }
        });

        $this->command?->info(
            '25 actividades DEMO UES disponibles. ' .
            'No se crearon inscripciones, órdenes ni pagos.'
        );
    }
}
