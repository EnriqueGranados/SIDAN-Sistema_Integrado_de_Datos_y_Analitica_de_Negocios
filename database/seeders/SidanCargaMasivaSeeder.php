<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SidanCargaMasivaSeeder extends Seeder
{
    /*
     * CONFIGURACIÓN
     *
     * Prueba pequeña:
     *   ACTIVIDADES = 30
     *   PERSONAS_POR_ACTIVIDAD = 40
     *
     * Carga inicial:
     *   ACTIVIDADES = 240
     *   PERSONAS_POR_ACTIVIDAD = 500
     *
     * Carga grande:
     *   ACTIVIDADES = 600
     *   PERSONAS_POR_ACTIVIDAD = 1500
     *
     * Cada décima actividad comercial recibe cuatro veces
     * el volumen normal.
     */

    private const ACTIVIDADES = 120;
    private const PERSONAS_POR_ACTIVIDAD = 250;
    private const LOTE_PERSONAS = 100;

    // Período de análisis histórico.
    private const FECHA_INICIO = '2025-01-01 00:00:00';
    private const FECHA_FIN = '2026-10-28 23:59:59';

    // Mes de carga concentrada.
    private const MES_PRUEBA_INICIO = '2026-06-01 00:00:00';
    private const MES_PRUEBA_FIN = '2026-06-30 23:59:59';


    /*
     * Orden de inserción: padres antes que hijos.
     * null significa tabla sin PK autoincremental.
     */
    private const TABLAS = [
        'tbl_roles' => 'id_rol',
        'tbl_informacion_personal' => 'id_informacion_personal',
        'tbl_usuarios' => 'id_usuario',
        'tbl_categorias' => 'id_categoria',
        'tbl_etiquetas' => 'id_etiqueta',
        'tbl_actividades' => 'id_actividad',
        'tbl_actividades_etiquetas' => null,
        'tbl_responsables_actividad' => null,
        'tbl_revisiones_actividad' => 'id_revision',
        'tbl_sesiones_actividad' => 'id_sesion',
        'tbl_items_actividad' => 'id_item_actividad',
        'tbl_variantes_item' => 'id_variante_item',
        'tbl_items_sesiones' => null,
        'tbl_promociones' => 'id_promocion',
        'tbl_promociones_items' => null,
        'tbl_inscripciones' => 'id_inscripcion',
        'tbl_ordenes' => 'id_orden',
        'tbl_detalles_orden' => 'id_detalle_orden',
        'tbl_pagos' => 'id_pago',
        'tbl_intentos_pago_wompi' => 'id_intento',
        'tbl_abonos_transferencia' => 'id_abono',
        'tbl_aplicaciones_abono' => 'id_aplicacion',
        'tbl_entregas_detalle' => 'id_entrega',
        'tbl_comprobantes' => 'id_comprobante',
        'tbl_usos_promociones' => 'id',
        'tbl_cierres_orden' => 'id_cierre',
    ];

    private array $filas = [];
    private array $reservas = [];
    private array $pagadas = [];
    private array $conteos = [];
    private array $usuarios = [];

    private int $admin;
    private CarbonImmutable $ahora;
    private CarbonImmutable $inicio;
    private CarbonImmutable $finHistorico;

    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'Este seeder solo funciona con APP_ENV=local o testing.'
            );
        }

        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Este seeder requiere PostgreSQL.');
        }

        $base = DB::selectOne(
            'SELECT current_database() AS nombre'
        )->nombre;

        if ($base !== 'db_sidan') {
            throw new RuntimeException(
                "Base actual: {$base}. Se requiere db_sidan."
            );
        }

        /*
         * Impide ejecutar dos veces sobre la misma carga.
         * No borra datos existentes ni reinicia secuencias.
         */
        if (
            DB::table('tbl_roles')
                ->where('nombre', 'seed_carga')
                ->exists()
        ) {
            throw new RuntimeException(
                'Ya existe una carga. Usá una base de pruebas nueva '
                . 'o reconstruí exclusivamente db_sidan.'
            );
        }


        $this->ahora = CarbonImmutable::now('UTC');

        $this->inicio = CarbonImmutable::parse(
            self::FECHA_INICIO,
            'UTC'
        );

        $this->finHistorico = CarbonImmutable::parse(
            self::FECHA_FIN,
            'UTC'
        );

        if ($this->ahora->lessThan($this->finHistorico)) {
            throw new RuntimeException(
                'La fecha final de prueba todavía está en el futuro.'
            );
        }


        if ($this->ahora->lessThan($this->inicio->addDays(30))) {
            throw new RuntimeException('Revisá la fecha inicial del seeder.');
        }

        DB::connection()->disableQueryLog();

        /*
         * Un bloqueo por conexión evita dos ejecuciones simultáneas
         * del mismo seeder.
         */
        $bloqueo = DB::selectOne(
            'SELECT CASE WHEN pg_try_advisory_lock(726431) '
            . 'THEN 1 ELSE 0 END AS adquirido'
        );

        if ((int) $bloqueo->adquirido !== 1) {
            throw new RuntimeException('Ya hay otra carga ejecutándose.');
        }

        try {
            $catalogos = DB::transaction(
                fn() => $this->crearBase()
            );

            for ($a = 0; $a < self::ACTIVIDADES; $a++) {
                DB::transaction(function () use ($a, $catalogos) {
                    $this->crearActividad($a, $catalogos);
                    $this->guardar();
                });

                $this->command?->info(
                    'Actividad ' . ($a + 1) . '/'
                    . self::ACTIVIDADES . ' completada.'
                );
            }

            foreach (array_keys($this->conteos) as $tabla) {
                DB::statement('ANALYZE "' . $tabla . '"');
            }

            $this->command?->table(
                ['Tabla', 'Filas insertadas'],
                array_map(
                    fn($tabla, $cantidad) => [$tabla, $cantidad],
                    array_keys($this->conteos),
                    array_values($this->conteos)
                )
            );

            $this->command?->info(
                'Carga finalizada. Todos los datos son sintéticos.'
            );
        } finally {
            DB::select('SELECT pg_advisory_unlock(726431)');
        }
    }

    private function crearBase(): array
    {
        $t = $this->inicio->subDays(30);

        $rol = 3;

        /*
         * Una sola operación de hashing.
         * Las cuentas generadas están inactivas.
         */
        $hash = Hash::make('Solo-Pruebas-SIDAN-2026!');

        $cantidad = self::PERSONAS_POR_ACTIVIDAD * 4;

        for ($i = 0; $i <= $cantidad; $i++) {
            $persona = $this->agregar('tbl_informacion_personal', [
                'nombres' => "Persona sintética {$i}",
                'apellidos' => 'Prueba SIDAN',
                'documento' => "SEED-{$i}",
                'telefono' => null,
                'fecha_nacimiento' => '1995-01-01',
                'ubicacion' => ['San Miguel', 'San Salvador', 'Santa Ana'][$i % 3],
            ] + $this->fechas($t));

            $id = $this->agregar('tbl_usuarios', [
                'id_informacion_personal' => $persona,
                'id_rol' => $rol,
                'correo' => "seed.{$i}@example.test",
                'password_hash' => $hash,
                'estado_activo' => true,
                'eliminado' => false,
            ] + $this->fechas($t));

            if ($i === 0) {
                $this->admin = $id;
            } else {
                $this->usuarios[] = [
                    'id' => $id,
                    'correo' => "seed.{$i}@example.test",
                ];
            }

            if ($i % 500 === 0) {
                $this->guardar();
            }
        }

        $categorias = [];
        $etiquetas = [];

        foreach ([
            'Tecnología',
            'Formación',
            'Deportes',
            'Cultura',
            'Emprendimiento',
            'Solidaridad',
        ] as $i => $nombre) {
            $categorias[] = $this->agregar('tbl_categorias', [
                'nombre' => "SEED {$nombre}",
                'slug' => "seed-categoria-{$i}",
            ] + $this->fechas($t));

            $etiquetas[] = $this->agregar('tbl_etiquetas', [
                'nombre' => "SEED etiqueta {$i}",
                'slug' => "seed-etiqueta-{$i}",
            ] + $this->fechas($t));
        }

        $this->guardar();

        return compact('categorias', 'etiquetas');
    }

    private function crearActividad(int $a, array $catalogos): void
    {
        $tipo = match ($a % 10) {
            0 => 'informativa',
            1 => 'venta_directa',
            2, 3 => 'registro_gratuito',
            default => 'registro_pago',
        };

        $estados = [
            'borrador',
            'pendiente_revision',
            'cambios_solicitados',
            'aprobada',
            'rechazada',
            'retirada',
        ];

        /*
         * Algunas actividades son únicamente casos de catálogo.
         * Las demás tienen flujo comercial.
         */
        $publicacion = $a % 20 < 6
            ? $estados[$a % 20]
            : 'publicada';

        $operativo = $a % 29 === 0
            ? 'suspendida'
            : ($a % 31 === 0 ? 'cancelada' : 'normal');

        $n = self::PERSONAS_POR_ACTIVIDAD
            * ($a % 10 === 6 ? 4 : 1);

        $registro = in_array(
            $tipo,
            ['registro_gratuito', 'registro_pago'],
            true
        );

        $diasActividad = max(
            1,
            (int) $this->inicio->diffInDays(
                $this->finHistorico
            )
        );

        $fechaActividad = $this->inicio->addDays(
            ($a * 37) % ($diasActividad + 1)
        );

        $actividad = $this->agregar('tbl_actividades', [
            'id_categoria' => $catalogos['categorias'][$a % 6],
            'nombre' => "SEED Campaña {$a}",
            'slug' => "seed-campana-{$a}",
            'descripcion' => 'Campaña sintética multianual para pruebas.',
            'estado_publicacion' => $publicacion,
            'estado_operativo' => $operativo,
            'visibilidad' => ['publica', 'publica', 'no_listada', 'interna'][$a % 4],
            'destacada' => $a % 7 === 0,
            'prioridad' => $a % 101,
            'tipo_participacion' => $tipo,
            'habilita_inscripcion' => $registro,
            'requiere_cuenta' => false,
            'permite_lista_espera' => true,
            'cupo_total' => $a % 3 === 0 ? null : $n + 100,
            'precio_inscripcion' => $tipo === 'registro_pago' ? '25.00' : null,
            'inscripcion_desde' => $this->inicio,
            'inscripcion_hasta' => $this->ahora->addDays(30),
            'visible_desde' => $this->inicio,
            'visible_hasta' => $this->ahora->addDays(60),
            'realizacion_desde' => $this->ahora->addDays(31),
            'realizacion_hasta' => $this->ahora->addDays(33),
            'creado_por' => $this->admin,
            'actualizado_por' => $this->admin,
        ] + $this->fechas($fechaActividad));

        $this->agregar('tbl_actividades_etiquetas', [
            'id_actividad' => $actividad,
            'id_etiqueta' => $catalogos['etiquetas'][$a % 6],
        ]);

        $this->agregar('tbl_responsables_actividad', [
            'id_actividad' => $actividad,
            'id_usuario' => $this->admin,
            'rol_en_actividad' => 'responsable',
        ] + $this->fechas($this->inicio));

        if ($publicacion === 'publicada') {
            foreach (['enviada_revision', 'aprobada', 'publicada'] as $k => $accion) {
                $this->agregar('tbl_revisiones_actividad', [
                    'id_actividad' => $actividad,
                    'numero_revision' => 1,
                    'id_usuario' => $this->admin,
                    'accion' => $accion,
                    'observacion' => 'Historial sintético.',
                    'creado_en' => $this->inicio->addMinutes($k),
                ]);
            }
        }

        $sesion = $this->agregar('tbl_sesiones_actividad', [
            'id_actividad' => $actividad,
            'nombre' => 'Sesión principal',
            'fecha_inicio' => $this->ahora->addDays(31),
            'fecha_fin' => $this->ahora->addDays(31)->addHours(4),
            'cupo' => $n + 100,
            'estado' => 'programada',
        ]);

        $items = [];

        foreach ([
            'producto',
            'producto',
            'servicio',
            'acceso',
            'donacion',
            'reserva',
            'otro',
            'producto',
            'producto',
            'producto',
        ] as $k => $tipoItem) {
            $precio = $k === 9 ? 0 : 1000 + $k * 500;
            $costo = $k === 9 ? 0 : 300 + $k * 100;
            $anticipo = $tipoItem === 'producto' && $k < 2;

            $id = $this->agregar('tbl_items_actividad', [
                'id_actividad' => $actividad,
                'nombre' => "SEED artículo {$a}-{$k}",
                'tipo' => $tipoItem,
                'precio' => $this->dinero($precio),
                'costo_referencia' => $this->dinero($costo),
                'stock_total' => $k === 8 ? 0 : null,
                'activo' => $k !== 7,
                'venta_desde' => $this->inicio,
                'venta_hasta' => $this->ahora->addDays(30),
                'min_por_inscripcion' => 1,
                'max_por_inscripcion' => null,
                'permite_anticipo' => $anticipo,
                'tipo_anticipo' => $anticipo ? 'porcentaje' : null,
                'valor_anticipo' => $anticipo ? '50.00' : null,
                'alcance_sesiones' => $k === 3 ? 'sesiones' : 'actividad',
                'creado_en' => $this->inicio,
            ]);

            $variante = null;

            if ($k < 2) {
                foreach (['M', 'L'] as $v => $talla) {
                    $vid = $this->agregar('tbl_variantes_item', [
                        'id_item_actividad' => $id,
                        'sku' => "SEED-{$a}-{$k}-{$talla}",
                        'nombre_variante' => "Talla {$talla}",
                        'precio' => $this->dinero($precio + $v * 200),
                        'costo_referencia' => $this->dinero($costo + $v * 100),
                        'stock_total' => null,
                        'activo' => true,
                    ]);

                    if ($v === 0) {
                        $variante = $vid;
                    }
                }
            }

            if ($k === 3) {
                $this->agregar('tbl_items_sesiones', [
                    'id_item_actividad' => $id,
                    'id_sesion' => $sesion,
                ]);
            }

            $items[] = compact(
                'id',
                'variante',
                'precio',
                'costo',
                'anticipo',
                'tipoItem'
            );
        }

        $codigo = "SEED10-{$a}";

        $promocion = $this->agregar('tbl_promociones', [
            'id_actividad' => $actividad,
            'codigo' => $codigo,
            'nombre' => 'Descuento sintético 10%',
            'tipo_descuento' => 'porcentaje',
            'valor' => '10.00',
            'vigente_desde' => $this->inicio,
            'vigente_hasta' => $this->ahora->addDays(30),
            'activo' => true,
            'creado_por' => $this->admin,
            'creado_en' => $this->inicio,
        ]);

        foreach (array_slice($items, 0, 7) as $item) {
            $this->agregar('tbl_promociones_items', [
                'id_promocion' => $promocion,
                'id_item_actividad' => $item['id'],
            ]);
        }

        $this->guardar();

        if (
            $publicacion !== 'publicada'
            || $operativo !== 'normal'
            || $tipo === 'informativa'
        ) {
            return;
        }

        for ($j = 0; $j < $n; $j++) {
            $persona = $this->usuarios[
                ($j + $a * 37) % count($this->usuarios)
            ];

            $usuario = $j % 5 === 0 ? null : $persona['id'];

            $correo = $usuario === null
                ? "invitado.{$a}.{$j}@example.test"
                : $persona['correo'];

            $caso = $j % 16;

            /*
             * Distribución histórica:
             *
             * 60%: enero 2025 - octubre 2026.
             * 40%: junio 2026.
             *
             * Los casos pendientes recientes se ajustan
             * posteriormente, conservando la lógica original.
             */

            $inicioMes = CarbonImmutable::parse(
                self::MES_PRUEBA_INICIO,
                'UTC'
            );

            $finMes = CarbonImmutable::parse(
                self::MES_PRUEBA_FIN,
                'UTC'
            );

            $concentrarEnJunio = ($j % 5) < 2;

            if ($concentrarEnJunio) {

                $segundosMes = $inicioMes->diffInSeconds($finMes);

                $desplazamiento = (
                    $a * 86417 + $j * 3719
                ) % ($segundosMes + 1);

                $t = $inicioMes->addSeconds($desplazamiento);

            } else {

                $segundosPeriodo = $this->inicio
                    ->diffInSeconds($this->finHistorico);

                $desplazamiento = (
                    $a * 173489 + $j * 7919
                ) % ($segundosPeriodo + 1);

                $t = $this->inicio->addSeconds(
                    $desplazamiento
                );
            }
            /*
             * Pendientes y anticipos abiertos son recientes.
             * Los demás casos tienen fechas históricas.
             */
            if (in_array($caso, [3, 4, 5, 6, 14], true)) {
                $t = $this->ahora->subMinutes(10);
            }

            $inscripcion = null;
            $estadoInscripcion = 'confirmada';

            if ($registro) {
                $estadoInscripcion = $tipo === 'registro_gratuito'
                    ? match ($caso) {
                        7 => 'vencida',
                        8 => 'cancelada',
                        default => 'confirmada',
                    }
                    : match ($caso) {
                        3, 4, 5, 6, 14 => 'pendiente_pago',
                        7 => 'vencida',
                        8 => 'cancelada',
                        11 => 'confirmada_prueba',
                        15 => 'revision_cupo',
                        default => 'confirmada',
                    };

                $confirmada = in_array(
                    $estadoInscripcion,
                    ['confirmada', 'confirmada_prueba'],
                    true
                );

                $inscripcion = $this->agregar('tbl_inscripciones', [
                    'id_actividad' => $actividad,
                    'id_usuario' => $usuario,
                    'nombre_participante' => "Participante {$a}-{$j}",
                    'correo_participante' => $correo,
                    'estado' => $estadoInscripcion,
                    'fecha_confirmacion' => $confirmada
                        ? $t->addMinutes(5)
                        : null,
                    'expira_en' => $confirmada
                        ? null
                        : $t->addMinutes(30),
                ] + $this->fechas($t));

                if ($tipo === 'registro_pago') {
                    /*
                     * Las inscripciones no reciben anticipos.
                     * Los escenarios parciales se convierten aquí
                     * en un pago pendiente.
                     */
                    $casoInscripcion = in_array(
                        $caso,
                        [5, 6],
                        true
                    ) ? 3 : (
                        in_array($caso, [9, 10, 12, 13], true)
                        ? 0
                        : $caso
                    );

                    $this->crearOrden(
                        $actividad,
                        $inscripcion,
                        $usuario,
                        $correo,
                        $t,
                        $casoInscripcion,
                        [
                            [
                                'id' => null,
                                'variante' => null,
                                'precio' => 2500,
                                'costo' => null,
                                'anticipo' => false,
                                'tipoItem' => 'inscripcion',
                                'cantidad' => 1,
                            ]
                        ],
                        null,
                        null,
                        true
                    );
                }
            }

            /*
             * Compra directa o compra posterior a inscripción confirmada.
             * Las inscripciones pendientes no compran productos aquí.
             */
            if (
                !$registro
                || in_array(
                    $estadoInscripcion,
                    ['confirmada', 'confirmada_prueba'],
                    true
                )
            ) {
                for ($compra = 0; $compra < 2; $compra++) {
                    $casoCompra = $estadoInscripcion === 'confirmada_prueba'
                        ? 11
                        : ($caso + $compra * 5) % 16;

                    /*
                     * Dos productos físicos.
                     * En órdenes totalmente pagadas, la tercera línea
                     * alterna servicios, accesos, donaciones, reservas y otros.
                     */
                    $tercero = in_array(
                        $casoCompra,
                        [0, 1, 2, 12, 13],
                        true
                    ) ? 2 + ($j % 5) : 0;

                    $lineas = [
                        $items[0] + ['cantidad' => 1 + ($j % 3)],
                        $items[1] + ['cantidad' => 1],
                        $items[$tercero] + ['cantidad' => 2],
                    ];

                    $usarPromo = $usuario !== null && $j % 3 === 0;

                    $fechaCompra = in_array(
                        $casoCompra,
                        [3, 4, 5, 6, 14],
                        true
                    )
                        ? $this->ahora->subMinutes(5)
                        : $t->addMinutes(10 + $compra);

                    $this->crearOrden(
                        $actividad,
                        $inscripcion,
                        $usuario,
                        $correo,
                        $fechaCompra,
                        $casoCompra,
                        $lineas,
                        $usarPromo ? $promocion : null,
                        $usarPromo ? $codigo : null,
                        false
                    );
                }
            }

            if (($j + 1) % self::LOTE_PERSONAS === 0) {
                $this->guardar();
            }
        }
    }

    private function crearOrden(
        int $actividad,
        ?int $inscripcion,
        ?int $usuario,
        string $correo,
        CarbonImmutable $t,
        int $caso,
        array $lineas,
        ?int $promocion,
        ?string $codigo,
        bool $esInscripcion
    ): void {
        /*
         * CASOS:
         *  0: Wompi aprobado
         *  1: transferencia completa en dos abonos
         *  2: efectivo completo
         *  3: transferencia pendiente
         *  4: transferencia rechazada y nuevo intento pendiente
         *  5: anticipo sin entrega
         *  6: anticipo con un detalle pagado y entregado
         *  7: vencida sin cobro
         *  8: cancelada sin cobro
         *  9: vencida con anticipo, pendiente de clasificación
         * 10: vencida con anticipo, ingreso no ejercido
         * 11: Wompi de prueba
         * 12: pagada sin retirar productos
         * 13: pagada con algunos detalles entregados
         * 14: Wompi rechazado
         * 15: pago tardío de inscripción / transferencia completa
         */

        $prueba = $caso === 11;
        $parcial = !$esInscripcion
            && in_array($caso, [5, 6, 9, 10], true);

        $tardio = $esInscripcion && $caso === 15;

        $completo = in_array(
            $caso,
            [0, 1, 2, 11, 12, 13, 15],
            true
        );

        $proveedor = match ($caso) {
            0, 7, 11, 14 => 'wompi',
            2, 6 => 'efectivo',
            default => 'transferencia',
        };

        $estado = match (true) {
            $prueba => 'pagada_prueba',
            $tardio => 'revision_cupo',
            $completo => 'pagada',
            in_array($caso, [7, 9, 10], true) => 'vencida',
            $caso === 8 => 'cancelada',
            default => 'pendiente_pago',
        };

        $fechaPago = $t->addMinutes($tardio ? 60 : 2);
        $fechaEntrega = $fechaPago->addMinutes(1);
        $fechaCierre = $t->addDays(3);

        $subtotal = 0;
        $descuento = 0;
        $minimo = 0;

        foreach ($lineas as &$linea) {
            $linea['bruto'] = $linea['precio'] * $linea['cantidad'];
            $linea['descuento'] = $promocion === null
                ? 0
                : intdiv($linea['bruto'], 10);

            $linea['neto'] = $linea['bruto'] - $linea['descuento'];

            $subtotal += $linea['bruto'];
            $descuento += $linea['descuento'];

            $minimo += $linea['anticipo']
                ? intdiv($linea['neto'] + 1, 2)
                : $linea['neto'];
        }
        unset($linea);

        $total = $subtotal - $descuento;
        $cierre = in_array($caso, [9, 10], true);

        $orden = $this->agregar('tbl_ordenes', [
            'id_actividad' => $actividad,
            'id_inscripcion' => $inscripcion,
            'id_usuario' => $usuario,
            'correo_contacto' => $correo,
            'moneda' => 'USD',
            'tipo_orden' => $esInscripcion ? 'inscripcion' : 'productos',
            'subtotal' => $this->dinero($subtotal),
            'descuento_total' => $this->dinero($descuento),
            'total' => $this->dinero($total),

            /*
             * Las pagadas se actualizan DESPUÉS de insertar pagos
             * y detalles para ejecutar correctamente el trigger.
             */
            'estado' => $estado === 'pagada'
                ? 'pendiente_pago'
                : $estado,

            'canal_venta' => $proveedor === 'efectivo'
                ? 'presencial'
                : 'portal',
            'creado_por' => $proveedor === 'efectivo'
                ? $this->admin
                : null,

            'modalidad_pago' => $parcial ? 'anticipo' : 'completo',
            'minimo_inicial' => $this->dinero($parcial ? $minimo : $total),
            'expira_en' => in_array(
                $estado,
                ['pagada', 'pagada_prueba'],
                true
            ) ? null : (
                $esInscripcion
                ? $t->addMinutes(30)
                : $t->addDays(2)
            ),
            'limite_saldo_en' => $parcial ? $t->addDays(2) : null,
            'cierre_financiero_estado' => $caso === 10
                ? 'finalizada'
                : ($caso === 9 ? 'regularizacion' : 'no_aplica'),
            'cancelada_en' => $caso === 8 ? $t->addMinutes(3) : null,
            'cancelada_por' => $caso === 8 ? $this->admin : null,
            'motivo_cancelacion' => $caso === 8
                ? 'Cancelación sintética sin cobros.'
                : null,
            'finalizada_en' => $caso === 10 ? $fechaCierre : null,
            'finalizada_por' => $caso === 10 ? $this->admin : null,
            'motivo_finalizacion' => $caso === 10
                ? 'Cierre sintético del saldo pendiente.'
                : null,
            'created_at' => $t,
            'updated_at' => $cierre ? $fechaCierre : $fechaEntrega,
        ]);

        if ($estado === 'pagada') {
            $this->pagadas[] = $orden;
        }

        $detalles = [];
        $recibido = 0;
        $importeEntregado = 0;

        foreach ($lineas as $k => $linea) {
            $fisico = $linea['tipoItem'] === 'producto';

            $aplicado = match (true) {
                $prueba => 0,
                $completo => $linea['neto'],
                $parcial && $caso === 6 && $k === 0 => $linea['neto'],
                $parcial => intdiv($linea['neto'] + 1, 2),
                default => 0,
            };

            $entregado = !$prueba && $fisico && (
                ($completo && $caso !== 12 && ($caso !== 13 || $k === 0))
                || ($caso === 6 && $k === 0)
            );

            $costoTotal = $linea['costo'] === null
                ? null
                : $linea['costo'] * $linea['cantidad'];

            $detalle = $this->agregar('tbl_detalles_orden', [
                'id_orden' => $orden,
                'id_item_actividad' => $linea['id'],
                'id_variante_item' => $linea['variante'],
                'tipo_concepto' => $linea['tipoItem'],
                'nombre_concepto' => $esInscripcion
                    ? 'Inscripción sintética'
                    : 'Artículo sintético ' . $linea['id'],
                'cantidad' => $linea['cantidad'],
                'precio_unitario' => $this->dinero($linea['precio']),
                'subtotal' => $this->dinero($linea['bruto']),
                'descuento' => $this->dinero($linea['descuento']),
                'unidades_descuento' => $promocion === null
                    ? 0
                    : $linea['cantidad'],
                'permite_anticipo' => $linea['anticipo'],
                'tipo_anticipo' => $linea['anticipo']
                    ? 'porcentaje'
                    : null,
                'valor_anticipo' => $linea['anticipo'] ? '50.00' : null,
                'cantidad_entregada' => $entregado
                    ? $linea['cantidad']
                    : 0,

                /*
                 * El trigger existente copia el costo histórico.
                 * Para físicos entregados registramos el reconocimiento
                 * equivalente al servicio de entregas.
                 * Los servicios pagados los reconoce el trigger de orden.
                 */
                'venta_neta_confirmada' => $entregado
                    ? $this->dinero($linea['neto'])
                    : null,
                'costo_total_confirmado' => $entregado && $costoTotal !== null
                    ? $this->dinero($costoTotal)
                    : null,
                'ganancia_bruta_confirmada' => $entregado && $costoTotal !== null
                    ? $this->dinero($linea['neto'] - $costoTotal)
                    : null,
                'fecha_confirmacion_financiera' => $entregado
                    ? $fechaEntrega
                    : null,
                'created_at' => $t,
                'updated_at' => $fechaEntrega,
            ]);

            $detalles[] = [
                'id' => $detalle,
                'aplicado' => $aplicado,
            ];

            $recibido += $aplicado;

            if ($entregado) {
                $importeEntregado += $linea['neto'];

                $this->agregar('tbl_entregas_detalle', [
                    'id_detalle_orden' => $detalle,
                    'cantidad' => $linea['cantidad'],
                    'entregado_por' => $this->admin,
                    'observacion' => 'Entrega sintética.',
                ] + $this->fechas($fechaEntrega));
            }
        }

        $estadoPago = match (true) {
            $prueba => 'confirmado_prueba',
            $completo => 'confirmado',
            $parcial => 'parcial',
            $caso === 8 => 'cancelado',
            $caso === 14 => 'rechazado',
            default => 'pendiente',
        };

        $pago = $this->agregar('tbl_pagos', [
            'id_orden' => $orden,
            'proveedor' => $proveedor,
            'monto' => $this->dinero($total),
            'estado' => $estadoPago,
            'fecha_confirmacion' => $completo ? $fechaPago : null,
            'revisado_por' => $proveedor !== 'wompi' && $recibido > 0
                ? $this->admin
                : null,
            'revisado_en' => $proveedor !== 'wompi' && $recibido > 0
                ? $fechaPago
                : null,
            'created_at' => $t,
            'updated_at' => $fechaPago,
        ]);

        if ($proveedor === 'wompi') {
            $this->agregar('tbl_intentos_pago_wompi', [
                'id_pago' => $pago,
                'referencia_comercio' => "SEED-PAGO-{$pago}",
                'id_enlace_wompi' => null,
                'id_transaccion_wompi' => $completo
                    ? "SEED-TX-{$pago}"
                    : null,
                'monto_solicitado' => $this->dinero($total),
                'url_enlace' => null,
                'es_productiva' => !$prueba,
                'estado' => match ($caso) {
                    11 => 'aprobado_prueba',
                    14 => 'rechazado',
                    7 => 'expirado',
                    default => 'aprobado',
                },
                'expira_en' => $t->addMinutes(30),
                'created_at' => $t,
                'updated_at' => $fechaPago,
            ]);
        } else {
            if ($recibido > 0) {
                $partes = $caso === 1 ? 2 : 1;
                $repartos = array_fill(0, $partes, []);

                foreach ($detalles as $detalle) {
                    $primero = $partes === 2
                        ? intdiv($detalle['aplicado'], 2)
                        : $detalle['aplicado'];

                    $repartos[0][] = [
                        'id' => $detalle['id'],
                        'monto' => $primero,
                    ];

                    if ($partes === 2) {
                        $repartos[1][] = [
                            'id' => $detalle['id'],
                            'monto' => $detalle['aplicado'] - $primero,
                        ];
                    }
                }

                foreach ($repartos as $parte => $reparto) {
                    $monto = array_sum(array_column($reparto, 'monto'));
                    $fechaAbono = $fechaPago->subSeconds(
                        $partes - 1 - $parte
                    );

                    $abono = $this->agregar('tbl_abonos_transferencia', [
                        'id_pago' => $pago,
                        'metodo' => $proveedor,
                        'comprobante_path' => null,
                        'monto_reportado' => $this->dinero($monto),
                        'monto_recibido' => $this->dinero($monto),
                        'estado' => 'confirmado',
                        'revisado_por' => $this->admin,
                        'revisado_en' => $fechaAbono,
                        'observacion_revision' => 'Abono sintético; sin archivo bancario.',
                    ] + $this->fechas($fechaAbono));

                    if (!$esInscripcion) {
                        foreach ($reparto as $aplicacion) {
                            if ($aplicacion['monto'] <= 0) {
                                continue;
                            }

                            $this->agregar('tbl_aplicaciones_abono', [
                                'id_abono' => $abono,
                                'id_detalle_orden' => $aplicacion['id'],
                                'monto' => $this->dinero($aplicacion['monto']),
                            ] + $this->fechas($fechaAbono));
                        }
                    }
                }
            }

            if (in_array($caso, [3, 4], true)) {
                if ($caso === 4) {
                    $this->agregar('tbl_abonos_transferencia', [
                        'id_pago' => $pago,
                        'metodo' => 'transferencia',
                        'comprobante_path' => null,
                        'monto_reportado' => $this->dinero($total),
                        'monto_recibido' => null,
                        'estado' => 'rechazado',
                        'revisado_por' => $this->admin,
                        'revisado_en' => $t->addMinute(),
                        'observacion_revision' => 'Rechazo sintético.',
                    ] + $this->fechas($t));
                }

                $this->agregar('tbl_abonos_transferencia', [
                    'id_pago' => $pago,
                    'metodo' => 'transferencia',
                    'comprobante_path' => null,
                    'monto_reportado' => $this->dinero($total),
                    'monto_recibido' => null,
                    'estado' => 'pendiente',
                    'observacion_revision' => 'Pendiente sintético; sin archivo bancario.',
                ] + $this->fechas($fechaPago));
            }
        }

        if ($completo) {
            $this->agregar('tbl_comprobantes', [
                'id_pago' => $pago,
                'numero' => "SEED-REC-{$pago}",
                'tipo' => 'recibo_interno',
                'monto' => $this->dinero($total),
                'fecha_emision' => $fechaPago,
                'es_productiva' => !$prueba,
            ] + $this->fechas($fechaPago));
        }

        if ($promocion !== null) {
            $this->agregar('tbl_usos_promociones', [
                'id_promocion' => $promocion,
                'id_orden' => $orden,
                'id_usuario' => $usuario,
                'unidades' => array_sum(array_column($lineas, 'cantidad')),
                'codigo' => $codigo,
                'descuento' => $this->dinero($descuento),
            ] + $this->fechas($t));
        }

        if ($cierre) {
            $this->agregar('tbl_cierres_orden', [
                'id_orden' => $orden,
                'autorizado_por' => $this->admin,
                'dinero_recibido' => $this->dinero($recibido),
                'importe_productos_entregados' => $this->dinero($importeEntregado),
                'anticipo_sin_entrega' => $this->dinero(
                    $recibido - $importeEntregado
                ),
                'clasificacion' => $caso === 10
                    ? 'ingreso_no_ejercido'
                    : 'pendiente_clasificacion',
                'justificacion' => 'Cierre sintético para conciliación BI.',
            ] + $this->fechas($fechaCierre));
        }
    }

    private function agregar(string $tabla, array $fila): ?int
    {
        $pk = self::TABLAS[$tabla];
        $id = null;

        if ($pk !== null) {
            if (empty($this->reservas[$tabla])) {
                /*
                 * Reserva IDs usando la secuencia real.
                 * No usa MAX(id)+1 ni reinicia secuencias.
                 */
                $reserva = DB::select(
                    'SELECT nextval('
                    . 'pg_get_serial_sequence(?, ?)::regclass'
                    . ') AS id FROM generate_series(1, 512)',
                    [$tabla, $pk]
                );

                $this->reservas[$tabla] = array_map(
                    fn($r) => (int) $r->id,
                    $reserva
                );
            }

            $id = array_pop($this->reservas[$tabla]);
            $fila[$pk] = $id;
        }

        /*
         * Agrupa filas con las mismas columnas.
         * Permite omitir columnas y respetar defaults del esquema.
         */
        ksort($fila);
        $firma = implode('|', array_keys($fila));

        $this->filas[$tabla][$firma][] = $fila;

        return $id;
    }

    private function guardar(): void
    {
        foreach (self::TABLAS as $tabla => $pk) {
            foreach ($this->filas[$tabla] ?? [] as $grupo) {
                foreach (array_chunk($grupo, 500) as $lote) {
                    DB::table($tabla)->insert($lote);

                    $this->conteos[$tabla] =
                        ($this->conteos[$tabla] ?? 0) + count($lote);
                }
            }
        }

        $this->filas = [];

        /*
         * Aquí ya existen los pagos confirmados.
         * Se conservan los triggers reales del proyecto.
         */
        foreach (array_chunk($this->pagadas, 500) as $ids) {
            DB::table('tbl_ordenes')
                ->whereIn('id_orden', $ids)
                ->update(['estado' => 'pagada']);
        }

        $this->pagadas = [];
    }

    private function fechas(CarbonImmutable $fecha): array
    {
        return [
            'created_at' => $fecha,
            'updated_at' => $fecha,
        ];
    }

    private function dinero(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }
}