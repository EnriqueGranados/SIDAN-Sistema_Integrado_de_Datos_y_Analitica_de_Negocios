<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Mongo\ActivityContent;
use App\Models\ObservacionRevisionActividad;
use App\Models\RevisionActividad;
use App\Services\ActividadRevisionSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActividadRevisionController extends Controller
{
    public function __construct(
        private readonly ActividadRevisionSnapshotService $snapshotService
    ) {
    }

    public function index(Request $request): View
    {
        $query = Actividad::query()
            ->with([
                'categoria',
                'creador.informacion_personal',
            ])
            ->where('estado_publicacion', 'pendiente_revision')
            ->orderBy('updated_at');

        if ($request->filled('buscar')) {
            $buscar = trim((string) $request->input('buscar'));

            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'ilike', "%{$buscar}%")
                    ->orWhere('resumen', 'ilike', "%{$buscar}%");
            });
        }

        $actividades = $query
            ->paginate(15)
            ->withQueryString();

        return view(
            'admin.actividades.revision.listado',
            compact('actividades')
        );
    }

    public function show(Actividad $actividad): View
    {
        abort_unless(
            $actividad->estado_publicacion === 'pendiente_revision',
            404
        );

        $actividad->load([
            'categoria',
            'etiquetas',
            'espacio.contenedor',
            'creador.informacion_personal',
            'medios',
            'items.variantes',
            'items.medios',
            'sesiones.espacio.contenedor',
            'sesiones.recursos.recurso',
            'formularios.campos',
            'recursos.recurso',
            'promociones.items',
            'responsables.usuario.informacion_personal',
            'revisiones.usuario.informacion_personal',
            'revisiones.observaciones',
        ]);

        $revisionActual = $this->obtenerRevisionActual($actividad);
        abort_unless($revisionActual, 404);

        $observacionesPendientes = $this->obtenerObservacionesPendientes(
            $actividad
        );

        $contenido = ActivityContent::query()
            ->where('id_actividad_pg', (int) $actividad->id_actividad)
            ->first();

        $configuracionProductos = $this->normalizarListaConfiguraciones(
            $contenido?->product_configuration ?? [],
            ['id_item_pg', 'id_item_actividad', 'item_id'],
            'id_item_pg'
        );

        $camposCompra = $this->normalizarListaConfiguraciones(
            $contenido?->purchase_fields ?? [],
            ['id_item_pg', 'id_item_actividad', 'item_id'],
            'id_item_pg'
        );

        $configuracionesPrecios = $this->indexarConfiguraciones(
            $contenido?->pricing_configuration ?? [],
            ['id_item_pg', 'id_item_actividad', 'item_id']
        );

        $configuracionesSesiones = $this->indexarConfiguraciones(
            $contenido?->session_configuration ?? [],
            ['id_sesion_pg', 'id_sesion', 'session_id']
        );

        $configuracionGeneral = $this->normalizarMongo(
            $contenido?->configuration ?? []
        );

        $cambiosDesdeAprobacion = $this->snapshotService
            ->obtenerCambiosDesdeAprobacion($actividad);

        return view(
            'admin.actividades.revision.detalle',
            compact(
                'actividad',
                'revisionActual',
                'observacionesPendientes',
                'configuracionProductos',
                'camposCompra',
                'configuracionesPrecios',
                'configuracionesSesiones',
                'configuracionGeneral',
                'cambiosDesdeAprobacion'
            )
        );
    }

    public function guardarObservacion(
        Request $request,
        Actividad $actividad
    ): JsonResponse|RedirectResponse {
        abort_unless(
            $actividad->estado_publicacion === 'pendiente_revision',
            404
        );

        $data = $request->validate([
            'seccion' => [
                'required',
                'string',
                Rule::in([
                    'informacion_general',
                    'presentacion',
                    'productos',
                    'datos_solicitados',
                    'precios_costos',
                    'programacion',
                    'inscripcion',
                ]),
            ],
            'referencia_tipo' => [
                'nullable',
                'string',
                Rule::in([
                    'item',
                    'sesion',
                    'formulario',
                    'campo',
                ]),
            ],
            'referencia_id' => [
                'nullable',
                'integer',
            ],
            'observacion' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ], [
            'seccion.required' => 'Debes indicar la sección observada.',
            'seccion.in' => 'La sección seleccionada no es válida.',
            'referencia_tipo.in' => 'El tipo de elemento observado no es válido.',
            'observacion.required' => 'Debes escribir la observación.',
            'observacion.min' => 'La observación debe contener al menos 5 caracteres.',
        ]);

        $tipo = $data['referencia_tipo'] ?? null;
        $referenciaId = $data['referencia_id'] ?? null;

        if (($tipo === null) xor ($referenciaId === null)) {
            return $this->respuestaError(
                $request,
                'La referencia de la observación está incompleta.'
            );
        }

        $revisionActual = $this->obtenerRevisionActual($actividad);
        abort_unless($revisionActual, 404);

        if ($tipo !== null) {
            $this->validarReferencia(
                $actividad,
                $tipo,
                (int) $referenciaId
            );
        }

        $observacion = $revisionActual->observaciones()->create([
            'seccion' => $data['seccion'],
            'referencia_tipo' => $tipo,
            'referencia_id' => $referenciaId,
            'observacion' => trim($data['observacion']),
        ]);

        $totalObservaciones = $this
            ->consultaObservacionesPendientes($actividad)
            ->count();

        $observacionesSeccion = $this
            ->consultaObservacionesPendientes($actividad)
            ->where('seccion', $data['seccion'])
            ->count();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Observación agregada correctamente.',
                'observacion' => [
                    'id_observacion' => $observacion->id_observacion,
                    'seccion' => $observacion->seccion,
                    'referencia_tipo' => $observacion->referencia_tipo,
                    'referencia_id' => $observacion->referencia_id,
                    'observacion' => $observacion->observacion,
                    'numero_revision' => (int) $revisionActual->numero_revision,
                    'es_revision_actual' => true,
                    'created_at' => $observacion->created_at?->toISOString(),
                ],
                'conteos' => [
                    'total' => $totalObservaciones,
                    'seccion' => $observacionesSeccion,
                ],
                'puede_aprobar' => $totalObservaciones === 0,
            ], 201);
        }

        return back()->with(
            'success',
            'Observación agregada correctamente.'
        );
    }

    public function eliminarObservacion(
        Request $request,
        Actividad $actividad,
        ObservacionRevisionActividad $observacion
    ): JsonResponse|RedirectResponse {
        abort_unless(
            $actividad->estado_publicacion === 'pendiente_revision',
            404
        );

        $revisionActual = $this->obtenerRevisionActual($actividad);
        abort_unless($revisionActual, 404);

        $observacion->load('revision');

        abort_unless(
            $observacion->revision
            && (int) $observacion->revision->id_actividad
                === (int) $actividad->id_actividad
            && !$observacion->resuelta,
            404
        );

        $seccion = $observacion->seccion;
        $esObservacionRevisionActual =
            (int) $observacion->id_revision
            === (int) $revisionActual->id_revision;

        if ($esObservacionRevisionActual) {
            $observacion->delete();
            $mensaje = 'Observación eliminada correctamente.';
            $accion = 'eliminada';
        } else {
            $observacion->update([
                'resuelta' => true,
                'resuelta_por' => Auth::id(),
                'id_revision_resuelta' => $revisionActual->id_revision,
                'resuelta_en' => now(),
            ]);

            $mensaje = 'Observación marcada como corregida.';
            $accion = 'resuelta';
        }

        $totalObservaciones = $this
            ->consultaObservacionesPendientes($actividad)
            ->count();

        $observacionesSeccion = $this
            ->consultaObservacionesPendientes($actividad)
            ->where('seccion', $seccion)
            ->count();

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $mensaje,
                'action' => $accion,
                'conteos' => [
                    'total' => $totalObservaciones,
                    'seccion' => $observacionesSeccion,
                ],
                'puede_aprobar' => $totalObservaciones === 0,
            ]);
        }

        return back()->with('success', $mensaje);
    }

    public function aprobar(
        Request $request,
        Actividad $actividad
    ): JsonResponse|RedirectResponse {
        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($actividad, $data) {
                $bloqueada = Actividad::query()
                    ->whereKey($actividad->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($bloqueada->estado_publicacion !== Actividad::ESTADO_PENDIENTE_REVISION) {
                    throw new \DomainException('Esta actividad ya no está pendiente de revisión.');
                }

                $revisionActual = $this->obtenerRevisionActual($bloqueada);

                if (!$revisionActual) {
                    throw new \DomainException('No se encontró la revisión activa de esta actividad.');
                }

                if ($this->consultaObservacionesPendientes($bloqueada)->exists()) {
                    throw new \DomainException('No puedes aprobar mientras existan observaciones pendientes.');
                }

                $bloqueada->estado_publicacion = Actividad::ESTADO_APROBADA;
                $bloqueada->actualizado_por = Auth::id();
                $bloqueada->save();

                $this->registrarRevision(
                    $bloqueada,
                    'aprobada',
                    filled($data['observacion'] ?? null)
                        ? trim($data['observacion'])
                        : 'Actividad aprobada.'
                );
            });
        } catch (\DomainException $e) {
            return $this->respuestaError($request, $e->getMessage(), 409);
        } catch (\Throwable $e) {
            report($e);

            return $this->respuestaError(
                $request,
                'No se pudo aprobar la actividad. Intenta nuevamente.',
                500
            );
        }

        try {
            $actividad->refresh();
            $this->snapshotService->limpiarTrasAprobacion($actividad);
        } catch (\Throwable $e) {
            report($e);
        }

        $redirect = route('admin.actividades.revision.index');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Actividad aprobada correctamente.',
                'redirect' => $redirect,
            ]);
        }

        return redirect()
            ->route('admin.actividades.revision.index')
            ->with('success', 'Actividad aprobada correctamente.');
    }

    public function solicitarCambios(
        Request $request,
        Actividad $actividad
    ): JsonResponse|RedirectResponse {
        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        $observacionGeneral = trim((string) ($data['observacion'] ?? ''));

        try {
            DB::transaction(function () use ($actividad, $observacionGeneral) {
                $bloqueada = Actividad::query()
                    ->whereKey($actividad->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($bloqueada->estado_publicacion !== Actividad::ESTADO_PENDIENTE_REVISION) {
                    throw new \DomainException('Esta actividad ya no está pendiente de revisión.');
                }

                $revisionActual = $this->obtenerRevisionActual($bloqueada);

                if (!$revisionActual) {
                    throw new \DomainException('No se encontró la revisión activa de esta actividad.');
                }

                $tieneEspecificas = $this
                    ->consultaObservacionesPendientes($bloqueada)
                    ->exists();

                if (!$tieneEspecificas && $observacionGeneral === '') {
                    throw new \DomainException(
                        'Agrega una observación general o al menos una observación específica para solicitar cambios.'
                    );
                }

                $bloqueada->estado_publicacion = Actividad::ESTADO_CAMBIOS_SOLICITADOS;
                $bloqueada->actualizado_por = Auth::id();
                $bloqueada->save();

                $this->registrarRevision(
                    $bloqueada,
                    'cambios_solicitados',
                    $observacionGeneral !== ''
                        ? $observacionGeneral
                        : 'Se solicitaron correcciones específicas.'
                );
            });
        } catch (\DomainException $e) {
            return $this->respuestaError($request, $e->getMessage(), 409);
        } catch (\Throwable $e) {
            report($e);

            return $this->respuestaError(
                $request,
                'No se pudieron solicitar los cambios. Intenta nuevamente.',
                500
            );
        }

        $redirect = route('admin.actividades.revision.index');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Los cambios fueron solicitados correctamente.',
                'redirect' => $redirect,
            ]);
        }

        return redirect()
            ->route('admin.actividades.revision.index')
            ->with('success', 'Los cambios fueron solicitados correctamente.');
    }

    public function rechazar(
        Request $request,
        Actividad $actividad
    ): JsonResponse|RedirectResponse {
        $data = $request->validate([
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        $observacionGeneral = trim((string) ($data['observacion'] ?? ''));

        try {
            DB::transaction(function () use ($actividad, $observacionGeneral) {
                $bloqueada = Actividad::query()
                    ->whereKey($actividad->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($bloqueada->estado_publicacion !== Actividad::ESTADO_PENDIENTE_REVISION) {
                    throw new \DomainException('Esta actividad ya no está pendiente de revisión.');
                }

                $revisionActual = $this->obtenerRevisionActual($bloqueada);

                if (!$revisionActual) {
                    throw new \DomainException('No se encontró la revisión activa de esta actividad.');
                }

                $tieneEspecificas = $this
                    ->consultaObservacionesPendientes($bloqueada)
                    ->exists();

                if (!$tieneEspecificas && $observacionGeneral === '') {
                    throw new \DomainException(
                        'Debes justificar el rechazo con una observación general o específica.'
                    );
                }

                $bloqueada->estado_publicacion = Actividad::ESTADO_RECHAZADA;
                $bloqueada->actualizado_por = Auth::id();
                $bloqueada->save();

                $this->registrarRevision(
                    $bloqueada,
                    'rechazada',
                    $observacionGeneral !== ''
                        ? $observacionGeneral
                        : 'Actividad rechazada con observaciones específicas.'
                );
            });
        } catch (\DomainException $e) {
            return $this->respuestaError($request, $e->getMessage(), 409);
        } catch (\Throwable $e) {
            report($e);

            return $this->respuestaError(
                $request,
                'No se pudo rechazar la actividad. Intenta nuevamente.',
                500
            );
        }

        $redirect = route('admin.actividades.revision.index');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Actividad rechazada.',
                'redirect' => $redirect,
            ]);
        }

        return redirect()
            ->route('admin.actividades.revision.index')
            ->with('success', 'Actividad rechazada.');
    }

    private function consultaObservacionesPendientes(
        Actividad $actividad
    ): Builder {
        return ObservacionRevisionActividad::query()
            ->where('resuelta', false)
            ->whereHas('revision', function (Builder $query) use ($actividad) {
                $query->where(
                    'id_actividad',
                    $actividad->id_actividad
                );
            });
    }

    private function obtenerObservacionesPendientes(
        Actividad $actividad
    ): Collection {
        return $this->consultaObservacionesPendientes($actividad)
            ->with([
                'revision:id_revision,id_actividad,numero_revision,accion,creado_en',
            ])
            ->orderBy('id_observacion')
            ->get();
    }

    private function obtenerRevisionActual(
        Actividad $actividad
    ): ?RevisionActividad {
        return RevisionActividad::query()
            ->where('id_actividad', $actividad->id_actividad)
            ->where('numero_revision', $actividad->revision_actual)
            ->where('accion', 'enviada_revision')
            ->with('observaciones')
            ->latest('id_revision')
            ->first();
    }

    private function validarReferencia(
        Actividad $actividad,
        string $tipo,
        int $id
    ): void {
        $valida = match ($tipo) {
            'item' => $actividad
                ->items()
                ->where('id_item_actividad', $id)
                ->exists(),

            'sesion' => $actividad
                ->sesiones()
                ->where('id_sesion', $id)
                ->exists(),

            'formulario' => $actividad
                ->formularios()
                ->where('id_formulario', $id)
                ->exists(),

            'campo' => DB::table('tbl_campos_formulario')
                ->join(
                    'tbl_formularios_actividad',
                    'tbl_formularios_actividad.id_formulario',
                    '=',
                    'tbl_campos_formulario.id_formulario'
                )
                ->where(
                    'tbl_formularios_actividad.id_actividad',
                    $actividad->id_actividad
                )
                ->where('tbl_campos_formulario.id_campo', $id)
                ->exists(),

            default => false,
        };

        abort_unless(
            $valida,
            422,
            'El elemento observado no pertenece a esta actividad.'
        );
    }

    private function registrarRevision(
        Actividad $actividad,
        string $accion,
        ?string $observacion = null
    ): void {
        DB::table('tbl_revisiones_actividad')->insert([
            'id_actividad' => $actividad->id_actividad,
            'numero_revision' => $actividad->revision_actual,
            'id_usuario' => Auth::id(),
            'accion' => $accion,
            'observacion' => $observacion,
            'creado_en' => now(),
        ]);
    }

    private function normalizarMongo(mixed $valor): array
    {
        if ($valor instanceof Collection) {
            return $valor->all();
        }

        if (is_array($valor)) {
            return $valor;
        }

        if (is_object($valor)) {
            $normalizado = json_decode(
                json_encode($valor),
                true
            );

            return is_array($normalizado)
                ? $normalizado
                : [];
        }

        return [];
    }

    private function normalizarListaConfiguraciones(
        mixed $valor,
        array $clavesId,
        string $claveCanonica
    ): array {
        $datos = $this->normalizarMongo($valor);
        $resultado = [];

        foreach ($datos as $clave => $configuracion) {
            if (is_object($configuracion)) {
                $configuracion = $this->normalizarMongo($configuracion);
            }

            if (!is_array($configuracion)) {
                continue;
            }

            $id = null;

            foreach ($clavesId as $claveId) {
                if (
                    array_key_exists($claveId, $configuracion)
                    && $configuracion[$claveId] !== null
                    && $configuracion[$claveId] !== ''
                ) {
                    $id = $configuracion[$claveId];
                    break;
                }
            }

            if (
                $id === null
                && (is_int($clave) || ctype_digit((string) $clave))
            ) {
                $id = $clave;
            }

            if ($id !== null) {
                $configuracion[$claveCanonica] = $id;
            }

            $resultado[] = $configuracion;
        }

        return $resultado;
    }

    private function indexarConfiguraciones(
        mixed $valor,
        array $clavesId
    ): Collection {
        $datos = $this->normalizarMongo($valor);
        $resultado = collect();

        foreach ($datos as $clave => $configuracion) {
            if (is_object($configuracion)) {
                $configuracion = $this->normalizarMongo($configuracion);
            }

            if (!is_array($configuracion)) {
                continue;
            }

            $id = null;

            foreach ($clavesId as $claveId) {
                if (
                    array_key_exists($claveId, $configuracion)
                    && $configuracion[$claveId] !== null
                    && $configuracion[$claveId] !== ''
                ) {
                    $id = $configuracion[$claveId];
                    break;
                }
            }

            if (
                $id === null
                && (is_int($clave) || ctype_digit((string) $clave))
            ) {
                $id = $clave;
            }

            if ($id === null) {
                continue;
            }

            $resultado->put((string) $id, $configuracion);
        }

        return $resultado;
    }

    private function respuestaError(
        Request $request,
        string $mensaje,
        int $status = 422
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $mensaje,
            ], $status);
        }

        return back()
            ->withInput()
            ->with('error', $mensaje)
            ->withErrors([
                'observacion' => $mensaje,
            ]);
    }
}
