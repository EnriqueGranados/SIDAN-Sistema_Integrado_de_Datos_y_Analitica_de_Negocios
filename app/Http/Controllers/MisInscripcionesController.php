<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Orden;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MisInscripcionesController extends Controller
{
    public function index(Request $request): View
    {
        $inscripciones = Inscripcion::query()
            ->with(['actividad.portada', 'orden'])
            ->where('id_usuario', $request->user()->id_usuario)
            ->latest('created_at')
            ->get();

        // Solo presentación; no modificar estados de la BD al consultar.
        $inscripciones->each(function (Inscripcion $inscripcion) {
            $fin = $inscripcion->actividad?->realizacion_hasta
                ?? $inscripcion->actividad?->realizacion_desde;

            $inscripcion->estado_vista = match (true) {
                $inscripcion->estado === 'cancelada' => 'cancelada',
                $inscripcion->estado === 'vencida' => 'vencida',
                $inscripcion->estado === 'revision_cupo' => 'pendiente_revision',
                $inscripcion->estado === 'pendiente_pago' &&
                    $inscripcion->expira_en?->isPast() => 'vencida',
                $inscripcion->estado === 'pendiente_pago' => 'pendiente',
                in_array($inscripcion->estado, ['confirmada', 'confirmada_prueba'], true)
                    && $fin?->isPast() => 'finalizada',
                in_array($inscripcion->estado, ['confirmada', 'confirmada_prueba'], true) => 'activa',
                default => 'pendiente_revision',
            };
        });

        return view('inscripciones.index', compact('inscripciones'));
    }

    public function detalle(Request $request, Inscripcion $inscripcion): View
    {
        // No exponer datos de otros participantes mediante un ID adivinado.
        abort_unless(
            $inscripcion->id_usuario !== null &&
            (int) $inscripcion->id_usuario === (int) $request->user()->id_usuario,
            403
        );

        $inscripcion->load([
            'actividad.categoria',
            'actividad.espacio',
            'actividad.portada',
            'actividad.sesiones' => fn ($q) => $q->orderBy('fecha_inicio'),
            'actividad.items' => fn ($q) => $q->where('activo', true)->orderBy('orden'),
            'actividad.items.variantes' => fn ($q) => $q->where('activo', true)->orderBy('orden'),
            'actividad.items.imagenPrincipal',
            'orden.pago',
        ]);

        $actividad = $inscripcion->actividad;
        $fin = $actividad->realizacion_hasta ?? $actividad->realizacion_desde;
        $estadoVista = match (true) {
            $inscripcion->estado === 'cancelada' => 'cancelada',
            $inscripcion->estado === 'vencida' => 'vencida',
            $inscripcion->estado === 'revision_cupo' => 'en revisión',
            $inscripcion->estado === 'pendiente_pago' && $inscripcion->expira_en?->isPast() => 'vencida',
            $inscripcion->estado === 'pendiente_pago' => 'pendiente',
            in_array($inscripcion->estado, ['confirmada', 'confirmada_prueba'], true) && $fin?->isPast() => 'finalizada',
            in_array($inscripcion->estado, ['confirmada', 'confirmada_prueba'], true) => 'activa',
            default => 'en revisión',
        };

        // La compra de adicionales requiere un flujo de órdenes independiente.
        // Nunca considerar como adquiridos los artículos mostrados en el catálogo.
        $puedeComprar = in_array($inscripcion->estado, ['confirmada', 'confirmada_prueba'], true)
            && ! ($fin?->isPast());

        return view('activities.show', compact(
            'inscripcion', 'actividad', 'estadoVista', 'puedeComprar'
        ));
    }

    public function cancelar(Request $request, Inscripcion $inscripcion): RedirectResponse
    {
        abort_unless(
            (int) $inscripcion->id_usuario === (int) $request->user()->id_usuario,
            403
        );

        DB::transaction(function () use ($inscripcion, $request) {
            // Primero bloquear la orden, mismo orden de bloqueo que la confirmación de Wompi.
            $orden = Orden::where('id_inscripcion', $inscripcion->id_inscripcion)
                ->lockForUpdate()->first();
            $actual = Inscripcion::whereKey($inscripcion->id_inscripcion)
                ->lockForUpdate()->firstOrFail();

            abort_unless(
                (int) $actual->id_usuario === (int) $request->user()->id_usuario,
                403
            );

            if (!in_array($actual->estado, [
                'confirmada', 'confirmada_prueba', 'pendiente_pago',
            ], true)) {
                throw ValidationException::withMessages([
                    'inscripcion' => 'Esta inscripción ya no se puede cancelar automáticamente.',
                ]);
            }

            // Evitar que una inscripción finalizada se cancele retroactivamente.
            $actividad = $actual->actividad;
            $fin = $actividad->realizacion_hasta ?? $actividad->realizacion_desde;
            if ($fin?->isPast()) {
                throw ValidationException::withMessages([
                    'inscripcion' => 'La actividad ya finalizó.',
                ]);
            }

            $actual->update(['estado' => 'cancelada']);

            // Nunca anular un cobro confirmado: se conserva la trazabilidad.
            if ($orden && $orden->estado === 'pendiente_pago') {
                $orden->update(['estado' => 'cancelada']);
            }
        }, 3);

        return back()->with('success', 'Tu inscripción ha sido cancelada. Recuerda que los pagos realizados no son reembolsables.');
    }
}
