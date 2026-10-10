<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Inscripcion;
use App\Models\Orden;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InscripcionService
{
    public function verificar(Actividad $actividad): void
    {
        $ahora = now();

        if (
            !$actividad->estaVisibleEnPortal() ||
            $actividad->estado_operativo !== 'normal' ||
            !$actividad->requiereInscripcionGeneral() ||
            (
                $actividad->inscripcion_desde &&
                $ahora->lt($actividad->inscripcion_desde)
            ) ||
            (
                $actividad->inscripcion_hasta &&
                $ahora->gt($actividad->inscripcion_hasta)
            )
        ) {
            throw ValidationException::withMessages([
                'actividad' => 'La inscripción no está disponible.',
            ]);
        }

        if (
            $actividad->participacionConPago() &&
            (
                !$actividad->precio_inscripcion ||
                (float) $actividad->precio_inscripcion <= 0
            )
        ) {
            throw ValidationException::withMessages([
                'actividad' => 'La actividad no tiene un precio válido.',
            ]);
        }
    }

    public function registrar(
        Actividad $actividad,
        ?int $idUsuario,
        array $datos
    ): Inscripcion {
        return DB::transaction(function () use (
            $actividad,
            $idUsuario,
            $datos
        ) {
            // Bloqueamos la actividad para proteger el último cupo.
            $actividad = Actividad::query()
                ->whereKey($actividad->id_actividad)
                ->lockForUpdate()
                ->firstOrFail();

            $this->verificar($actividad);

            // Vencer reservas antiguas que no tienen pago confirmado.
            $vencidas = Inscripcion::query()
                ->where('id_actividad', $actividad->id_actividad)
                ->where('estado', 'pendiente_pago')
                ->where('expira_en', '<=', now())
                ->pluck('id_inscripcion');

            if ($vencidas->isNotEmpty()) {
                Inscripcion::whereIn('id_inscripcion', $vencidas)
                    ->update(['estado' => 'vencida']);

                Orden::whereIn('id_inscripcion', $vencidas)
                    ->where('estado', 'pendiente_pago')
                    ->update(['estado' => 'vencida']);
            }

            // Comprobamos que el participante no esté inscrito.
            $duplicada = Inscripcion::query()
                ->where('id_actividad', $actividad->id_actividad)
                ->whereIn('estado', [
                    'confirmada',
                    'confirmada_prueba',
                    'pendiente_pago',
                    'revision_cupo',
                ])
                ->where(function ($query) use ($datos, $idUsuario) {
                    $query->whereRaw(
                        'lower(correo_participante) = ?',
                        [mb_strtolower($datos['correo'])]
                    );

                    if ($idUsuario !== null) {
                        $query->orWhere('id_usuario', $idUsuario);
                    }
                })
                ->exists();

            if ($duplicada) {
                throw ValidationException::withMessages([
                    'correo' => 'Ya existe una inscripción activa para esta actividad.',
                ]);
            }

            // Contamos inscripciones confirmadas y reservas vigentes.
            $ocupados = Inscripcion::query()
                ->where('id_actividad', $actividad->id_actividad)
                ->where(function ($query) {
                    $query->whereIn('estado', [
                        'confirmada',
                        'confirmada_prueba',
                    ])->orWhere(function ($pendiente) {
                        $pendiente
                            ->where('estado', 'pendiente_pago')
                            ->where('expira_en', '>', now());
                    });
                })
                ->count();

            if (
                $actividad->cupo_total !== null &&
                $ocupados >= $actividad->cupo_total
            ) {
                throw ValidationException::withMessages([
                    'actividad' => 'No hay cupos disponibles.',
                ]);
            }

            // Decidimos si se requiere cobro.
            $conPago = $actividad->participacionConPago();

            // Una inscripción pagada reserva cupo durante 30 minutos.
            $expira = $conPago ? now()->addMinutes(30) : null;

            $inscripcion = Inscripcion::create([
                'id_actividad' => $actividad->id_actividad,
                'id_usuario' => $idUsuario,
                'nombre_participante' => $datos['nombre'],
                'correo_participante' => mb_strtolower($datos['correo']),
                'telefono_participante' => $datos['telefono'] ?? null,
                'estado' => $conPago
                    ? 'pendiente_pago'
                    : 'confirmada',
                'fecha_confirmacion' => $conPago ? null : now(),
                'expira_en' => $expira,
            ]);

            // Las gratuitas terminan aquí.
            if (!$conPago) {
                return $inscripcion;
            }

            // Las pagadas generan una orden comercial.
            $orden = $inscripcion->orden()->create([
                'id_actividad' => $actividad->id_actividad,
                'id_usuario' => $idUsuario,
                'correo_contacto' => $inscripcion->correo_participante,
                'moneda' => 'USD',
                'subtotal' => $actividad->precio_inscripcion,
                'descuento_total' => 0,
                'total' => $actividad->precio_inscripcion,
                'estado' => 'pendiente_pago',
                'expira_en' => $expira,
            ]);

            // Guardamos el precio histórico de la inscripción.
            $orden->detalles()->create([
                'tipo_concepto' => 'inscripcion',
                'nombre_concepto' => 'Inscripción: ' . $actividad->nombre,
                'cantidad' => 1,
                'precio_unitario' => $actividad->precio_inscripcion,
                'subtotal' => $actividad->precio_inscripcion,
            ]);

            return $inscripcion->load('orden');
        }, 3);
    }
}
