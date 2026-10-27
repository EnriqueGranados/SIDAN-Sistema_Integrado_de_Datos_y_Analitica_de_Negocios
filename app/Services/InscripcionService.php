<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Inscripcion;
use App\Models\Orden;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InscripcionService
{
    // Protección contra ciclos de inscribir/cancelar que distorsionan indicadores.
    // Los registros anteriores se conservan intactos para trazabilidad.
    public const HORAS_ESPERA_REINSCRIPCION = 24;
    public const MAX_CANCELACIONES_POR_ACTIVIDAD = 2;

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
        return DB::transaction(function () use ($actividad, $idUsuario, $datos) {
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
                ->where('expira_en', '<=', now('UTC'))
                ->pluck('id_inscripcion');

            if ($vencidas->isNotEmpty()) {
                Inscripcion::whereIn('id_inscripcion', $vencidas)
                    ->update(['estado' => 'vencida']);

                Orden::whereIn('id_inscripcion', $vencidas)
                    ->where('estado', 'pendiente_pago')
                    ->update(['estado' => 'vencida']);
            }

            // Revisar cancelaciones anteriores de la misma identidad en esta actividad.
            // Se utiliza tanto el usuario como el correo normalizado para impedir
            // que una cuenta antigua sin vínculo de usuario evada el límite.
            $identidad = mb_strtolower(trim((string) $datos['correo']));
            $canceladas = Inscripcion::query()
                ->where('id_actividad', $actividad->id_actividad)
                ->where('estado', 'cancelada')
                ->where(function ($query) use ($identidad, $idUsuario) {
                    $query->whereRaw('lower(correo_participante) = ?', [$identidad]);
                    if ($idUsuario !== null) {
                        $query->orWhere('id_usuario', $idUsuario);
                    }
                })
                ->orderByDesc('updated_at')
                ->get(['id_inscripcion', 'updated_at']);

            if ($canceladas->count() >= self::MAX_CANCELACIONES_POR_ACTIVIDAD) {
                throw ValidationException::withMessages([
                    'actividad' => 'Ya realizaste dos cancelaciones de esta actividad. Para volver a inscribirte, comunicate con administración.',
                ]);
            }

            if ($canceladas->isNotEmpty()) {
                $ultimaCancelacion = $canceladas->first()->updated_at;
                $disponibleDesde = $ultimaCancelacion?->copy()
                    ->addHours(self::HORAS_ESPERA_REINSCRIPCION);

                if ($disponibleDesde && now()->lt($disponibleDesde)) {
                    throw ValidationException::withMessages([
                        'actividad' => 'Para evitar inscripciones repetidas, podrás inscribirte nuevamente después del '
                            . $disponibleDesde->timezone('America/El_Salvador')->format('d/m/Y H:i')
                            . ' (hora de El Salvador).',
                    ]);
                }
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
            $disponibles = app(DisponibilidadService::class)
                ->cuposDisponibles((int) $actividad->id_actividad);

            if ($disponibles !== null && $disponibles < 1) {
                throw ValidationException::withMessages([
                    'actividad' => 'No hay cupos disponibles.',
                ]);
            }

            // Decidimos si se requiere cobro.
            $conPago = $actividad->participacionConPago();

            // Una inscripción pagada reserva cupo durante 30 minutos.
            // Guardar el vencimiento en UTC para PostgreSQL.
            $expira = $conPago ? now('UTC')->addMinutes(30) : null;

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
