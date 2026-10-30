<?php

namespace App\Services;

use App\Models\IntentoPagoWompi;
use App\Models\Orden;
use App\Models\Pago;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PagoService
{
    public function __construct(
        private readonly WompiService $wompi
    ) {
    }

    public function iniciar(
        Orden $orden
    ): string {
        $preparado = DB::transaction(
            function () use ($orden) {
                $orden = Orden::whereKey(
                    $orden->id_orden
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                $pagoExistente = Pago::where('id_orden', $orden->id_orden)
                    ->lockForUpdate()
                    ->first();

                if (
                    $pagoExistente
                    && (
                        $pagoExistente->proveedor !== 'wompi'
                        || $pagoExistente->estado !== 'pendiente'
                    )
                ) {
                    throw ValidationException::withMessages([
                        'pago' => 'Esta orden ya tiene otro pago registrado. Revisá su estado.',
                    ]);
                }

                if ((float) $orden->total <= 0) {
                    throw ValidationException::withMessages([
                        'pago' => 'Esta orden no requiere cobro. Confirmala desde el resumen.',
                    ]);
                }

                if (
                    $pagoExistente
                    && $this->centavos($pagoExistente->monto) !== $this->centavos($orden->total)
                ) {
                    throw ValidationException::withMessages([
                        'pago' => 'El importe del pago no coincide con la orden. Se requiere revisión.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Estado de orden
                |--------------------------------------------------------------------------
                */

                if (
                    $orden->estado !==
                    'pendiente_pago' ||
                    (
                        $orden->expira_en &&
                        $orden->expira_en
                            ->isPast()
                    )
                ) {
                    throw ValidationException::withMessages([
                        'pago' =>
                            'Esta orden ya no acepta pagos.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Validar tipo de orden
                |--------------------------------------------------------------------------
                */

                if (
                    !in_array(
                        $orden->tipo_orden,
                        [
                            'inscripcion',
                            'productos',
                        ],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'pago' =>
                            'El tipo de orden no es válido.',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Orden de productos
                |--------------------------------------------------------------------------
                |
                | Una actividad que exige inscripción general requiere
                | una inscripción confirmada.
                |
                | Una actividad que no exige inscripción general permite
                | comprar productos sin inscripción.
                |
                */

                if ($orden->tipo_orden === 'productos') {

                    $actividad = $orden->actividad;

                    if (!$actividad) {
                        throw ValidationException::withMessages([
                            'pago' => 'La actividad de esta orden no existe.',
                        ]);
                    }

                    if ($actividad->requiereInscripcionGeneral()) {

                        $inscripcion = $orden->inscripcion;

                        if (
                            !$inscripcion ||
                            !in_array(
                                $inscripcion->estado,
                                ['confirmada', 'confirmada_prueba'],
                                true
                            )
                        ) {
                            throw ValidationException::withMessages([
                                'pago' =>
                                    'Necesitás una inscripción confirmada para comprar productos de esta actividad.',
                            ]);
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Orden de inscripción
                |--------------------------------------------------------------------------
                */

                if (
                    $orden->tipo_orden ===
                    'inscripcion'
                ) {
                    $inscripcion =
                        $orden->inscripcion;

                    if (
                        !$inscripcion ||
                        $inscripcion->estado !==
                        'pendiente_pago'
                    ) {
                        throw ValidationException::withMessages([
                            'pago' =>
                                'Esta inscripción ya no está pendiente de pago.',
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Un pago lógico por orden
                |--------------------------------------------------------------------------
                */

                $pago =
                    Pago::firstOrCreate(
                        [
                            'id_orden' =>
                                $orden->id_orden,
                        ],
                        [
                            'monto' =>
                                $orden->total,

                            'proveedor' =>
                                'wompi',

                            'estado' =>
                                'pendiente',
                        ]
                    );

                /*
                |--------------------------------------------------------------------------
                | Reutilizar enlace vigente
                |--------------------------------------------------------------------------
                */

                $existente =
                    IntentoPagoWompi::query()
                        ->where(
                            'id_pago',
                            $pago->id_pago
                        )
                        ->whereIn(
                            'estado',
                            [
                                'creando',
                                'pendiente',
                            ]
                        )
                        ->orderByDesc(
                            'id_intento'
                        )
                        ->first();

                if (
                    $existente &&
                    $existente->url_enlace &&
                    (
                        !$existente->expira_en ||
                        $existente->expira_en
                            ->isFuture()
                    )
                ) {
                    return [
                        'url' =>
                            $existente
                                ->url_enlace,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Evitar enlaces simultáneos
                |--------------------------------------------------------------------------
                */

                if (
                    $existente &&
                    $existente->estado ===
                    'creando' &&
                    $existente->created_at
                        ->gt(
                            now()
                                ->subMinutes(2)
                        )
                ) {
                    throw ValidationException::withMessages([
                        'pago' =>
                            'Estamos preparando el enlace. Espera unos segundos.',
                    ]);
                }

                if ($existente) {
                    $existente->update([
                        'estado' =>
                            'fallido',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Crear intento
                |--------------------------------------------------------------------------
                */

                $intento =
                    IntentoPagoWompi::create([
                        'id_pago' =>
                            $pago->id_pago,

                        'referencia_comercio' =>
                            'SIDAN-ORD-' .
                            $orden->id_orden .
                            '-' .
                            Str::upper(
                                Str::random(14)
                            ),

                        'monto_solicitado' =>
                            $orden->total,

                        'estado' =>
                            'creando',

                        'expira_en' =>
                            $orden->expira_en,
                    ]);

                return [
                    'intento' =>
                        $intento
                            ->id_intento,
                ];
            }
        );

        if (
            isset(
            $preparado['url']
        )
        ) {
            return $preparado[
                'url'
            ];
        }

        $intento =
            IntentoPagoWompi::findOrFail(
                $preparado[
                    'intento'
                ]
            );

        $orden =
            Orden::with(
                'actividad'
            )
                ->findOrFail(
                    $orden->id_orden
                );

        try {
            /*
            |--------------------------------------------------------------------------
            | Webhook
            |--------------------------------------------------------------------------
            */

            $webhookUrl =
                config(
                    'services.wompi.webhook_url'
                );

            if (
                !$webhookUrl ||
                !filter_var(
                    $webhookUrl,
                    FILTER_VALIDATE_URL
                )
            ) {
                throw new RuntimeException(
                    'La URL del webhook no está configurada.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Nombre enviado a Wompi
            |--------------------------------------------------------------------------
            */

            $nombreProducto =
                $orden->tipo_orden ===
                'productos'
                ? 'Compra: ' .
                $orden->actividad
                    ->nombre
                : 'Inscripción: ' .
                $orden->actividad
                    ->nombre;

            /*
            |--------------------------------------------------------------------------
            | Crear enlace Wompi
            |--------------------------------------------------------------------------
            */

            $respuesta =
                $this->wompi
                    ->crearEnlacePago([
                        'identificadorEnlaceComercio' =>
                            $intento
                                ->referencia_comercio,

                        'monto' =>
                            (float) 
                            $orden->total,

                        'nombreProducto' =>
                            $nombreProducto,

                        'configuracion' => [
                            'urlWebhook' =>
                                $webhookUrl,

                            'notificarTransaccionCliente' =>
                                false,
                        ],
                    ]);

            if (
                !isset(
                $respuesta[
                    'idEnlace'
                ],
                $respuesta[
                    'urlEnlace'
                ]
            )
            ) {
                throw new RuntimeException(
                    'Wompi no devolvió el enlace completo.'
                );
            }

            $intento->update([
                'id_enlace_wompi' =>
                    $respuesta[
                        'idEnlace'
                    ],

                'url_enlace' =>
                    $respuesta[
                        'urlEnlace'
                    ],

                'estado' =>
                    'pendiente',
            ]);

            return $respuesta[
                'urlEnlace'
            ];
        } catch (Throwable $e) {
            $intento->update([
                'estado' =>
                    'fallido',
            ]);

            report($e);

            throw ValidationException::withMessages([
                'pago' =>
                    'No se pudo generar el enlace de Wompi.',
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Webhook confirmado
    |--------------------------------------------------------------------------
    */

    public function confirmarWebhook(
        array $datos
    ): void {
        if (
            (
                $datos[
                    'ResultadoTransaccion'
                ]
                ?? null
            ) !==
            'ExitosaAprobada'
        ) {
            return;
        }

        $referencia =
            $datos[
                'EnlacePago'
            ][
                'IdentificadorEnlaceComercio'
            ]
            ?? null;

        $idEnlace =
            $datos[
                'EnlacePago'
            ][
                'Id'
            ]
            ?? null;

        $idTransaccion =
            $datos[
                'IdTransaccion'
            ]
            ?? null;

        if (
            !is_string(
                $referencia
            ) ||
            !is_scalar(
                $idEnlace
            ) ||
            !is_string(
                $idTransaccion
            ) ||
            $idTransaccion === ''
        ) {
            throw new RuntimeException(
                'La notificación de Wompi está incompleta.'
            );
        }

        DB::transaction(
            function () use ($datos, $referencia, $idEnlace, $idTransaccion) {
                /*
                |--------------------------------------------------------------------------
                | Recuperar intento, pago y orden
                |--------------------------------------------------------------------------
                */

                $intento =
                    IntentoPagoWompi::query()
                        ->where(
                            'referencia_comercio',
                            $referencia
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                $pago =
                    Pago::whereKey(
                        $intento->id_pago
                    )
                        ->lockForUpdate()
                        ->firstOrFail();

                $orden =
                    Orden::whereKey(
                        $pago->id_orden
                    )
                        ->lockForUpdate()
                        ->firstOrFail();


                if ($pago->proveedor !== 'wompi') {
                    throw new RuntimeException('El pago no pertenece a Wompi.');
                }

                if (
                    $this->centavos($intento->monto_solicitado)
                    !== $this->centavos($orden->total)
                ) {
                    throw new RuntimeException('El importe del enlace no coincide con la orden.');
                }
                
                $inscripcion =
                    $orden->inscripcion;

                /*
                |--------------------------------------------------------------------------
                | Validar enlace
                |--------------------------------------------------------------------------
                */

                if (
                    !$intento
                        ->id_enlace_wompi ||
                    (string) 
                    $intento
                        ->id_enlace_wompi !==
                    (string) 
                    $idEnlace
                ) {
                    throw new RuntimeException(
                        'El identificador del enlace no coincide.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Validar monto
                |--------------------------------------------------------------------------
                */

                if (
                    $this->centavos(
                        $datos[
                            'Monto'
                        ]
                        ?? null
                    ) !==
                    $this->centavos(
                        $intento
                            ->monto_solicitado
                    )
                    ||
                    $this->centavos(
                        $pago->monto
                    ) !==
                    $this->centavos(
                        $orden->total
                    )
                ) {
                    throw new RuntimeException(
                        'El monto recibido no coincide con la orden.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Ambiente
                |--------------------------------------------------------------------------
                */

                if (
                    !is_bool(
                        $datos[
                            'EsProductiva'
                        ]
                        ?? null
                    )
                ) {
                    throw new RuntimeException(
                        'No es posible verificar el ambiente de Wompi.'
                    );
                }

                $productiva =
                    $datos[
                        'EsProductiva'
                    ];

                if (
                    app()->environment(
                        'production'
                    ) &&
                    !$productiva
                ) {
                    throw new RuntimeException(
                        'No se aceptan pagos de prueba en producción.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Evitar otra transacción
                |--------------------------------------------------------------------------
                */

                if (
                    $intento
                        ->id_transaccion_wompi !==
                    null &&
                    $intento
                        ->id_transaccion_wompi !==
                    $idTransaccion
                ) {
                    throw new RuntimeException(
                        'El enlace ya tiene otra transacción registrada.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Webhook repetido
                |--------------------------------------------------------------------------
                */

                if (
                    in_array(
                        $intento->estado,
                        [
                            'aprobado',
                            'aprobado_prueba',
                        ],
                        true
                    )
                ) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Confirmar intento y pago
                |--------------------------------------------------------------------------
                */

                $intento->update([
                    'id_transaccion_wompi' =>
                        $idTransaccion,

                    'estado' =>
                        $productiva
                        ? 'aprobado'
                        : 'aprobado_prueba',

                    'es_productiva' =>
                        $productiva,
                ]);

                $pago->update([
                    'estado' =>
                        $productiva
                        ? 'confirmado'
                        : 'confirmado_prueba',

                    'fecha_confirmacion' =>
                        now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | ORDEN DE INSCRIPCIÓN
                |--------------------------------------------------------------------------
                |
                | Esta sí confirma la inscripción.
                |
                */

                if (
                    $orden->tipo_orden ===
                    'inscripcion'
                ) {
                    $puedeConfirmar =
                        $orden->estado ===
                        'pendiente_pago'
                        &&
                        $inscripcion !==
                        null
                        &&
                        $inscripcion->estado ===
                        'pendiente_pago'
                        &&
                        $orden->expira_en !==
                        null
                        &&
                        !$orden->expira_en
                            ->isPast();

                    if ($puedeConfirmar) {
                        $orden->update([
                            'estado' =>
                                $productiva
                                ? 'pagada'
                                : 'pagada_prueba',
                        ]);

                        $inscripcion->update([
                            'estado' =>
                                $productiva
                                ? 'confirmada'
                                : 'confirmada_prueba',

                            'fecha_confirmacion' =>
                                now(),
                        ]);
                    } else {
                        /*
                        |--------------------------------------------------------------------------
                        | Pago tardío de inscripción
                        |--------------------------------------------------------------------------
                        */

                        $orden->update([
                            'estado' =>
                                'revision_cupo',
                        ]);

                        if ($inscripcion) {
                            $inscripcion->update([
                                'estado' =>
                                    'revision_cupo',
                            ]);
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | ORDEN DE PRODUCTOS
                |--------------------------------------------------------------------------
                |
                | Esta NO modifica la inscripción.
                |
                */ elseif (
                    $orden->tipo_orden ===
                    'productos'
                ) {
                    $actividad = $orden->actividad;

                    if (!$actividad) {
                        throw new RuntimeException(
                            'La actividad asociada a la orden no existe.'
                        );
                    }

                    $requiereInscripcion =
                        $actividad->requiereInscripcionGeneral();

                    $inscripcionValida =
                        !$requiereInscripcion
                        ||
                        (
                            $inscripcion !== null
                            &&
                            in_array(
                                $inscripcion->estado,
                                [
                                    'confirmada',
                                    'confirmada_prueba',
                                ],
                                true
                            )
                        );

                    $puedeConfirmar =
                        $orden->estado === 'pendiente_pago'
                        &&
                        $orden->expira_en !== null
                        &&
                        !$orden->expira_en->isPast()
                        &&
                        $inscripcionValida;

                    if ($puedeConfirmar) {
                        $orden->update([
                            'estado' =>
                                $productiva
                                ? 'pagada'
                                : 'pagada_prueba',
                        ]);

                        /*
                         * La inscripción NO se toca.
                         */
                    } else {
                        /*
                        |--------------------------------------------------------------------------
                        | Pago tardío / stock a revisar
                        |--------------------------------------------------------------------------
                        |
                        | Reutilizamos revision_cupo como estado administrativo
                        | existente para revisión manual.
                        |
                        */

                        $orden->update([
                            'estado' =>
                                'revision_cupo',
                        ]);

                        /*
                         * La inscripción NO se modifica.
                         */
                    }
                } else {
                    throw new RuntimeException(
                        'Tipo de orden desconocido.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Comprobante interno
                |--------------------------------------------------------------------------
                */

                DB::table(
                    'tbl_comprobantes'
                )->insertOrIgnore([
                            'id_pago' =>
                                $pago->id_pago,

                            'numero' =>
                                'SIDAN-REC-' .
                                $pago->id_pago,

                            'tipo' =>
                                'recibo_interno',

                            'monto' =>
                                $pago->monto,

                            'fecha_emision' =>
                                now(),

                            'es_productiva' =>
                                $productiva,

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);
            },
            3
        );
    }

    private function centavos(
        mixed $monto
    ): int {
        if (
            !is_string($monto) &&
            !is_int($monto) &&
            !is_float($monto)
        ) {
            throw new RuntimeException(
                'Monto inválido.'
            );
        }

        $valor =
            (string) $monto;

        if (
            !preg_match(
                '/^\d+(?:\.\d{1,2})?$/',
                $valor
            )
        ) {
            throw new RuntimeException(
                'Formato monetario inválido.'
            );
        }

        [
            $entero,
            $decimales,
        ] = array_pad(
            explode(
                '.',
                $valor,
                2
            ),
            2,
            ''
        );

        return (
            (int) $entero *
            100
        ) +
            (int) str_pad(
                $decimales,
                2,
                '0'
            );
    }
}