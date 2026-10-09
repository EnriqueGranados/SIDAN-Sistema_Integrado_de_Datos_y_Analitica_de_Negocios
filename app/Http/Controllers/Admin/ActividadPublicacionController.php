<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\RevisionActividad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActividadPublicacionController extends Controller
{
    public function publicar(Request $request, Actividad $actividad): JsonResponse|RedirectResponse
    {
        $usuario = $request->user();
        $this->autorizarAdministrador($usuario);

        if (!$actividad->puedePublicarse()) {
            return $this->respuestaError($request, 'Solo una actividad aprobada puede publicarse.', 422);
        }

        try {
            $actividad = DB::transaction(function () use ($actividad, $usuario) {
                $bloqueada = Actividad::query()
                    ->whereKey($actividad->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$bloqueada->puedePublicarse()) {
                    throw new \DomainException('La actividad cambió de estado y ya no puede publicarse.');
                }

                $bloqueada->update([
                    'estado_publicacion' => Actividad::ESTADO_PUBLICADA,
                    'actualizado_por' => $usuario->id_usuario,
                ]);

                RevisionActividad::create([
                    'id_actividad' => $bloqueada->id_actividad,
                    'numero_revision' => $bloqueada->revision_actual,
                    'id_usuario' => $usuario->id_usuario,
                    'accion' => 'publicada',
                    'observacion' => 'Actividad publicada en el portal.',
                    'creado_en' => now(),
                ]);

                return $bloqueada->fresh();
            });
        } catch (\DomainException $e) {
            return $this->respuestaError($request, $e->getMessage(), 409);
        } catch (\Throwable $e) {
            report($e);

            return $this->respuestaError(
                $request,
                'No se pudo publicar la actividad. Intenta nuevamente.',
                500
            );
        }

        return $this->respuestaExitosa(
            $request,
            $actividad,
            $this->mensajePublicacion($actividad)
        );
    }

    public function retirar(Request $request, Actividad $actividad): JsonResponse|RedirectResponse
    {
        $usuario = $request->user();
        $this->autorizarAdministrador($usuario);

        if (!$actividad->puedeRetirarseDePublicacion()) {
            return $this->respuestaError($request, 'La actividad no se encuentra publicada.', 422);
        }

        try {
            $actividad = DB::transaction(function () use ($actividad, $usuario) {
                $bloqueada = Actividad::query()
                    ->whereKey($actividad->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$bloqueada->puedeRetirarseDePublicacion()) {
                    throw new \DomainException('La actividad cambió de estado y ya no se encuentra publicada.');
                }

                $bloqueada->update([
                    'estado_publicacion' => Actividad::ESTADO_APROBADA,
                    'actualizado_por' => $usuario->id_usuario,
                ]);

                RevisionActividad::create([
                    'id_actividad' => $bloqueada->id_actividad,
                    'numero_revision' => $bloqueada->revision_actual,
                    'id_usuario' => $usuario->id_usuario,
                    'accion' => 'retirada_publicacion',
                    'observacion' => 'Actividad retirada del portal público.',
                    'creado_en' => now(),
                ]);

                return $bloqueada->fresh();
            });
        } catch (\DomainException $e) {
            return $this->respuestaError($request, $e->getMessage(), 409);
        } catch (\Throwable $e) {
            report($e);

            return $this->respuestaError(
                $request,
                'No se pudo retirar la actividad de publicación. Intenta nuevamente.',
                500
            );
        }

        return $this->respuestaExitosa(
            $request,
            $actividad,
            'Actividad retirada de publicación correctamente.'
        );
    }

    private function autorizarAdministrador($usuario): void
    {
        abort_unless($usuario, 403);

        $rol = strtolower((string) $usuario->rol()->value('nombre'));
        abort_unless(in_array($rol, ['superadmin', 'admin'], true), 403);
    }

    private function mensajePublicacion(Actividad $actividad): string
    {
        if ($actividad->visibilidad !== 'publica') {
            return 'Actividad publicada, pero no será visible porque su visibilidad no está configurada como pública.';
        }

        $ahora = now();

        if ($actividad->visible_desde && $actividad->visible_desde->gt($ahora)) {
            return 'Actividad publicada correctamente. Será visible a partir del '
                . $actividad->visible_desde->locale('es')->translatedFormat('d/m/Y H:i')
                . '.';
        }

        if ($actividad->visible_hasta && $actividad->visible_hasta->lt($ahora)) {
            return 'Actividad publicada, pero su período de visibilidad ya finalizó el '
                . $actividad->visible_hasta->locale('es')->translatedFormat('d/m/Y H:i')
                . '.';
        }

        return 'Actividad publicada correctamente y visible en el portal.';
    }

    private function respuestaExitosa(
        Request $request,
        Actividad $actividad,
        string $mensaje
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $mensaje,
                'mensaje' => $mensaje,
                'actividad' => [
                    'id' => $actividad->id_actividad,
                    'slug' => $actividad->slug,
                    'estado_publicacion' => $actividad->estado_publicacion,
                    'visible_portal' => $actividad->estaVisibleEnPortal(),
                    'visibilidad' => $actividad->visibilidad,
                    'visible_desde' => $actividad->visible_desde?->toIso8601String(),
                    'visible_hasta' => $actividad->visible_hasta?->toIso8601String(),
                ],
            ]);
        }

        return back()->with('success', $mensaje);
    }

    private function respuestaError(
        Request $request,
        string $mensaje,
        int $status
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $mensaje,
                'mensaje' => $mensaje,
            ], $status);
        }

        return back()
            ->withInput()
            ->with('error', $mensaje)
            ->withErrors(['publicacion' => $mensaje]);
    }
}
