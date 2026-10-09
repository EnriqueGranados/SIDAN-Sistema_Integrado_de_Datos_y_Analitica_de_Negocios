<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Promocion;
use App\Services\ActividadRevisionSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ActividadPromocionController extends Controller
{
    public function __construct(
        private readonly ActividadRevisionSnapshotService $snapshotService
    ) {
    }

    public function index(Actividad $actividad): View
    {
        $this->validarActividadEditable($actividad);

        $actividad->load([
            'items' => fn ($query) => $query
                ->where('activo', true)
                ->orderBy('orden')
                ->orderBy('nombre'),
            'promociones' => fn ($query) => $query
                ->with('items')
                ->orderByDesc('activo')
                ->orderByDesc('creado_en')
                ->orderByDesc('id_promocion'),
        ]);

        return view('admin.actividades.promociones.index', compact('actividad'));
    }

    public function store(Request $request, Actividad $actividad): JsonResponse|RedirectResponse
    {
        $this->validarActividadEditable($actividad);
        $datos = $this->validarDatos($request, $actividad);

        try {
            DB::transaction(function () use ($actividad, $datos) {
                if ($actividad->estado_publicacion === 'aprobada') {
                    $this->snapshotService->capturarSiAprobada($actividad);
                }

                $promocion = new Promocion();
                $this->rellenarPromocion($promocion, $actividad, $datos);
                $promocion->creado_por = Auth::id();
                $promocion->save();

                $this->sincronizarItems($promocion, $datos);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return $this->respuestaError(
                $request,
                'No se pudo crear la promoción. Intenta nuevamente.'
            );
        }

        return $this->respuestaExito(
            $request,
            'Promoción creada correctamente.'
        );
    }

    public function update(
        Request $request,
        Actividad $actividad,
        Promocion $promocion
    ): JsonResponse|RedirectResponse {
        $this->validarActividadEditable($actividad);
        $this->validarPertenencia($actividad, $promocion);

        $datos = $this->validarDatos($request, $actividad, $promocion);

        try {
            DB::transaction(function () use ($actividad, $promocion, $datos) {
                if ($actividad->estado_publicacion === 'aprobada') {
                    $this->snapshotService->capturarSiAprobada($actividad);
                }

                $this->rellenarPromocion($promocion, $actividad, $datos);
                $promocion->save();

                $this->sincronizarItems($promocion, $datos);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return $this->respuestaError(
                $request,
                'No se pudo actualizar la promoción. Intenta nuevamente.'
            );
        }

        return $this->respuestaExito(
            $request,
            'Promoción actualizada correctamente.'
        );
    }

    public function toggle(
        Request $request,
        Actividad $actividad,
        Promocion $promocion
    ): JsonResponse|RedirectResponse {
        $this->validarActividadEditable($actividad);
        $this->validarPertenencia($actividad, $promocion);

        $datos = $request->validate([
            'activo' => ['required', 'boolean'],
        ]);

        try {
            DB::transaction(function () use ($actividad, $promocion, $datos) {
                if ($actividad->estado_publicacion === 'aprobada') {
                    $this->snapshotService->capturarSiAprobada($actividad);
                }

                $promocion->activo = (bool) $datos['activo'];
                $promocion->save();
            });
        } catch (\Throwable $exception) {
            report($exception);

            return $this->respuestaError(
                $request,
                'No se pudo cambiar el estado de la promoción.'
            );
        }

        return $this->respuestaExito(
            $request,
            $promocion->activo
                ? 'Promoción activada.'
                : 'Promoción desactivada.'
        );
    }

    public function destroy(
        Request $request,
        Actividad $actividad,
        Promocion $promocion
    ): JsonResponse|RedirectResponse {
        $this->validarActividadEditable($actividad);
        $this->validarPertenencia($actividad, $promocion);

        try {
            DB::transaction(function () use ($actividad, $promocion) {
                if ($actividad->estado_publicacion === 'aprobada') {
                    $this->snapshotService->capturarSiAprobada($actividad);
                }

                $promocion->items()->detach();
                $promocion->delete();
            });
        } catch (\Throwable $exception) {
            report($exception);

            return $this->respuestaError(
                $request,
                'No se pudo eliminar la promoción.'
            );
        }

        return $this->respuestaExito(
            $request,
            'Promoción eliminada correctamente.'
        );
    }

    private function validarDatos(
        Request $request,
        Actividad $actividad,
        ?Promocion $promocion = null
    ): array {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:140'],
            'codigo' => ['nullable', 'string', 'max:60'],
            'tipo_descuento' => ['required', 'in:porcentaje,monto'],
            'valor' => ['required', 'numeric', 'gt:0'],
            'vigente_desde' => ['nullable', 'date'],
            'vigente_hasta' => ['nullable', 'date'],
            'limite_usos' => ['nullable', 'integer', 'min:1'],
            'limite_por_persona' => ['nullable', 'integer', 'min:1'],
            'monto_minimo' => ['nullable', 'numeric', 'min:0'],
            'activo' => ['required', 'boolean'],
            'aplica_todos' => ['required', 'boolean'],
            'items' => ['nullable', 'array'],
            'items.*' => ['integer'],
        ]);

        if (
            $datos['tipo_descuento'] === 'porcentaje'
            && (float) $datos['valor'] > 100
        ) {
            throw ValidationException::withMessages([
                'valor' => 'El descuento porcentual no puede superar 100%.',
            ]);
        }

        if (
            !empty($datos['vigente_desde'])
            && !empty($datos['vigente_hasta'])
            && strtotime($datos['vigente_hasta']) < strtotime($datos['vigente_desde'])
        ) {
            throw ValidationException::withMessages([
                'vigente_hasta' => 'La fecha final no puede ser anterior a la fecha inicial.',
            ]);
        }

        $codigo = trim((string) ($datos['codigo'] ?? ''));
        $datos['codigo'] = $codigo !== '' ? strtoupper($codigo) : null;

        if ($datos['codigo']) {
            $duplicada = Promocion::query()
                ->where('id_actividad', $actividad->id_actividad)
                ->whereRaw('LOWER(codigo) = ?', [strtolower($datos['codigo'])])
                ->when(
                    $promocion,
                    fn ($query) => $query->where(
                        'id_promocion',
                        '<>',
                        $promocion->id_promocion
                    )
                )
                ->exists();

            if ($duplicada) {
                throw ValidationException::withMessages([
                    'codigo' => 'Ya existe una promoción con ese código en esta actividad.',
                ]);
            }
        }

        $aplicaTodos = (bool) $datos['aplica_todos'];
        $idsItems = collect($datos['items'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if (!$aplicaTodos && $idsItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Selecciona al menos un producto o indica que aplica a todos.',
            ]);
        }

        if (!$aplicaTodos) {
            $cantidadValidos = $actividad->items()
                ->whereIn('id_item_actividad', $idsItems->all())
                ->count();

            if ($cantidadValidos !== $idsItems->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Uno o más productos seleccionados no pertenecen a esta actividad.',
                ]);
            }
        }

        $datos['items'] = $idsItems->all();
        $datos['aplica_todos'] = $aplicaTodos;

        return $datos;
    }

    private function rellenarPromocion(
        Promocion $promocion,
        Actividad $actividad,
        array $datos
    ): void {
        $promocion->id_actividad = $actividad->id_actividad;
        $promocion->nombre = trim($datos['nombre']);
        $promocion->codigo = $datos['codigo'];
        $promocion->tipo_descuento = $datos['tipo_descuento'];
        $promocion->valor = $datos['valor'];
        $promocion->vigente_desde = ($datos['vigente_desde'] ?? null) ?: null;
        $promocion->vigente_hasta = ($datos['vigente_hasta'] ?? null) ?: null;
        $promocion->limite_usos = ($datos['limite_usos'] ?? null) ?: null;
        $promocion->limite_por_persona = ($datos['limite_por_persona'] ?? null) ?: null;
        $montoMinimo = $datos['monto_minimo'] ?? null;
        $promocion->monto_minimo = $montoMinimo !== null && $montoMinimo !== ''
            ? $montoMinimo
            : null;
        $promocion->activo = (bool) $datos['activo'];
    }

    private function sincronizarItems(Promocion $promocion, array $datos): void
    {
        $promocion->items()->sync(
            $datos['aplica_todos'] ? [] : $datos['items']
        );
    }

    private function validarPertenencia(
        Actividad $actividad,
        Promocion $promocion
    ): void {
        abort_unless(
            (int) $promocion->id_actividad === (int) $actividad->id_actividad,
            404
        );
    }

    private function validarActividadEditable(Actividad $actividad): void
    {
        abort_unless(
            in_array(
                $actividad->estado_publicacion,
                ['borrador', 'cambios_solicitados', 'aprobada'],
                true
            ),
            403
        );
    }

    private function respuestaExito(
        Request $request,
        string $mensaje
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $mensaje,
            ]);
        }

        return back()->with('success', $mensaje);
    }

    private function respuestaError(
        Request $request,
        string $mensaje
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $mensaje,
            ], 500);
        }

        return back()->with('error', $mensaje);
    }
}
