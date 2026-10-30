<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Espacio;
use App\Models\Recurso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EspacioController extends Controller
{
    public function index(Request $request): View
    {
        $buscar = trim((string) $request->input('buscar', ''));
        $estado = $request->input('estado', '');
        $uso = $request->input('uso', '');

        $espacios = Espacio::query()
            ->with('contenedor')
            ->withCount([
                'espaciosInternos',
                'recursos',
            ])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($subquery) use ($buscar) {
                    $subquery
                        ->where('nombre', 'ilike', "%{$buscar}%")
                        ->orWhere('descripcion', 'ilike', "%{$buscar}%")
                        ->orWhere('direccion', 'ilike', "%{$buscar}%")
                        ->orWhere('indicaciones', 'ilike', "%{$buscar}%")
                        ->orWhereHas('contenedor', function ($contenedor) use ($buscar) {
                            $contenedor->where(
                                'nombre',
                                'ilike',
                                "%{$buscar}%"
                            );
                        });
                });
            })
            ->when(
                $estado === 'activo',
                fn ($query) => $query->where('activo', true)
            )
            ->when(
                $estado === 'inactivo',
                fn ($query) => $query->where('activo', false)
            )
            ->when(
                $uso === 'directo',
                fn ($query) => $query->where('permite_actividades', true)
            )
            ->when(
                $uso === 'organizacion',
                fn ($query) => $query->where('permite_actividades', false)
            )
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('admin.espacios.listado', compact(
            'espacios',
            'buscar',
            'estado',
            'uso'
        ));
    }

    public function create(): View
    {
        $espaciosDisponibles = Espacio::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id_espacio',
                'id_espacio_contenedor',
                'nombre',
                'direccion',
                'indicaciones',
                'latitud',
                'longitud',
            ]);

        $recursos = Recurso::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        return view('admin.espacios.crear', compact(
            'espaciosDisponibles',
            'recursos'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $this->validar($request);

        $datos = $this->prepararDatos(
            $request,
            $datos
        );

        $datos['estado_validacion'] = Espacio::VALIDACION_VALIDADO;
        $datos['origen_registro'] = Espacio::ORIGEN_ADMINISTRACION;
        $datos['creado_por'] = $request->user()?->id_usuario;
        $datos['validado_por'] = $request->user()?->id_usuario;
        $datos['validado_en'] = now();

        DB::transaction(function () use ($request, $datos) {
            $espacio = Espacio::create($datos);

            $this->sincronizarRecursos(
                $espacio,
                $request->input('recursos', [])
            );
        });

        return redirect()
            ->route('admin.espacios.index')
            ->with(
                'success',
                'El espacio se creó correctamente.'
            );
    }

    public function edit(Espacio $espacio): View
    {
        $espacio->load([
            'contenedor',
            'recursos',
        ]);

        $espacio->loadCount([
            'espaciosInternos',
            'recursos',
        ]);

        $idsNoDisponibles = $this->obtenerIdsDescendientes(
            $espacio
        );

        $idsNoDisponibles[] = $espacio->id_espacio;

        $espaciosDisponibles = Espacio::query()
            ->where(function ($query) use ($espacio) {
                $query
                    ->where('activo', true)
                    ->when(
                        $espacio->id_espacio_contenedor,
                        fn ($subquery) => $subquery->orWhere(
                            'id_espacio',
                            $espacio->id_espacio_contenedor
                        )
                    );
            })
            ->whereNotIn(
                'id_espacio',
                array_unique($idsNoDisponibles)
            )
            ->orderBy('nombre')
            ->get([
                'id_espacio',
                'id_espacio_contenedor',
                'nombre',
                'direccion',
                'indicaciones',
                'latitud',
                'longitud',
            ]);

        $idsRecursosActuales = $espacio->recursos
            ->pluck('id_recurso')
            ->all();

        $recursos = Recurso::query()
            ->where(function ($query) use ($idsRecursosActuales) {
                $query
                    ->where('activo', true)
                    ->when(
                        !empty($idsRecursosActuales),
                        fn ($subquery) => $subquery->orWhereIn(
                            'id_recurso',
                            $idsRecursosActuales
                        )
                    );
            })
            ->orderBy('nombre')
            ->get();

        return view('admin.espacios.editar', compact(
            'espacio',
            'espaciosDisponibles',
            'recursos'
        ));
    }

    public function update(
        Request $request,
        Espacio $espacio
    ): RedirectResponse {
        $datos = $this->validar(
            $request,
            $espacio
        );

        $this->validarJerarquia(
            $espacio,
            $datos['id_espacio_contenedor'] ?? null
        );

        $datos = $this->prepararDatos(
            $request,
            $datos
        );

        if ($espacio->estaPendienteValidacion()) {
            $datos['estado_validacion'] = Espacio::VALIDACION_VALIDADO;
            $datos['validado_por'] = $request->user()?->id_usuario;
            $datos['validado_en'] = now();
        }

        DB::transaction(function () use (
            $request,
            $espacio,
            $datos
        ) {
            $espacio->update($datos);

            $this->sincronizarRecursos(
                $espacio,
                $request->input('recursos', [])
            );
        });

        return redirect()
            ->route('admin.espacios.index')
            ->with(
                'success',
                'El espacio se actualizó correctamente.'
            );
    }

    public function destroy(Espacio $espacio): RedirectResponse
    {
        if ($espacio->espaciosInternos()->exists()) {
            return redirect()
                ->route('admin.espacios.index')
                ->with(
                    'error',
                    'No se puede eliminar este espacio porque contiene otros espacios. Reubícalos o elimínalos primero.'
                );
        }

        DB::transaction(function () use ($espacio) {
            $espacio->recursos()->detach();
            $espacio->delete();
        });

        return redirect()
            ->route('admin.espacios.index')
            ->with(
                'success',
                'El espacio se eliminó correctamente.'
            );
    }

    public function buscar(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],

            'modo' => [
                'nullable',
                Rule::in([
                    'general',
                    'sesion',
                    'contenedor_sesion',
                    'todos',
                ]),
            ],

            'contexto' => [
                'nullable',
                'integer',
                Rule::exists(
                    'tbl_espacios',
                    'id_espacio'
                ),
            ],

            'limite' => [
                'nullable',
                'integer',
                'min:5',
                'max:30',
            ],
        ]);

        $buscar = Str::squish(
            (string) ($datos['q'] ?? '')
        );

        $modo = $datos['modo'] ?? 'todos';

        $limite = (int) ($datos['limite'] ?? 12);

        $idContexto = isset($datos['contexto'])
            ? (int) $datos['contexto']
            : null;

        $query = Espacio::query()
            ->where('activo', true)
            ->with('contenedor');

        if ($modo === 'sesion') {
            $query->where('permite_actividades', true);
        }

        if (
            in_array($modo, ['sesion', 'contenedor_sesion'], true)
            && $idContexto
        ) {
            $contexto = Espacio::query()
                ->where('activo', true)
                ->findOrFail($idContexto);

            $idsPermitidos = $this->obtenerIdsDescendientes(
                $contexto
            );

            $idsPermitidos[] = (int) $contexto->id_espacio;

            $query->whereIn(
                'id_espacio',
                array_unique($idsPermitidos)
            );
        }

        if ($buscar !== '') {
            $query->where(function ($subquery) use ($buscar) {
                $subquery
                    ->where(
                        'nombre',
                        'ilike',
                        "%{$buscar}%"
                    )
                    ->orWhere(
                        'direccion',
                        'ilike',
                        "%{$buscar}%"
                    )
                    ->orWhere(
                        'descripcion',
                        'ilike',
                        "%{$buscar}%"
                    )
                    ->orWhereHas(
                        'contenedor',
                        function ($contenedor) use ($buscar) {
                            $contenedor->where(
                                'nombre',
                                'ilike',
                                "%{$buscar}%"
                            );
                        }
                    );
            });

            $query->orderByRaw(
                '
                    CASE
                        WHEN nombre ILIKE ? THEN 0
                        WHEN nombre ILIKE ? THEN 1
                        ELSE 2
                    END
                ',
                [
                    $buscar,
                    $buscar . '%',
                ]
            );
        }

        $espacios = $query
            ->orderBy('nombre')
            ->limit($limite)
            ->get();

        return response()->json([
            'data' => $espacios
                ->map(
                    fn (Espacio $espacio) =>
                    $espacio->paraSelector()
                )
                ->values(),
        ]);
    }

    public function crearRapido(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'origen' => [
                'required',
                Rule::in([
                    Espacio::ORIGEN_ACTIVIDAD,
                    Espacio::ORIGEN_SESION,
                ]),
            ],

            'id_espacio_contenedor' => [
                'nullable',
                'integer',
                Rule::exists(
                    'tbl_espacios',
                    'id_espacio'
                )->where(
                    fn ($query) =>
                    $query->where('activo', true)
                ),
            ],

            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'direccion' => [
                'required',
                'string',
                'max:300',
            ],

            'capacidad' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'latitud' => [
                'nullable',
                'numeric',
                'between:-90,90',
                'required_with:longitud',
            ],

            'longitud' => [
                'nullable',
                'numeric',
                'between:-180,180',
                'required_with:latitud',
            ],
        ], [
            'origen.required' =>
                'No se pudo determinar desde dónde se está creando el espacio.',

            'id_espacio_contenedor.exists' =>
                'El espacio contenedor seleccionado ya no está disponible.',

            'nombre.required' =>
                'Escribe el nombre del espacio.',

            'nombre.max' =>
                'El nombre no puede superar los 150 caracteres.',

            'descripcion.max' =>
                'La descripción no puede superar los 1000 caracteres.',

            'direccion.required' =>
                'Escribe la dirección o referencia del espacio.',

            'direccion.max' =>
                'La dirección no puede superar los 300 caracteres.',

            'capacidad.min' =>
                'La capacidad debe ser de al menos una persona.',

            'latitud.between' =>
                'La latitud debe estar entre -90 y 90.',

            'longitud.between' =>
                'La longitud debe estar entre -180 y 180.',

            'latitud.required_with' =>
                'Debes indicar también la latitud.',

            'longitud.required_with' =>
                'Debes indicar también la longitud.',
        ]);

        $nombre = $this->limpiarTexto(
            $datos['nombre']
        );

        $idContenedor = isset(
            $datos['id_espacio_contenedor']
        )
            ? (int) $datos['id_espacio_contenedor']
            : null;

        $duplicado = Espacio::query()
            ->whereRaw(
                'LOWER(nombre) = ?',
                [mb_strtolower($nombre)]
            )
            ->where(function ($query) use ($idContenedor) {
                if ($idContenedor === null) {
                    $query->whereNull(
                        'id_espacio_contenedor'
                    );

                    return;
                }

                $query->where(
                    'id_espacio_contenedor',
                    $idContenedor
                );
            })
            ->first();

        if ($duplicado) {
            return response()->json([
                'message' =>
                    'Ya existe un espacio con ese nombre dentro del lugar seleccionado.',

                'espacio' =>
                    $duplicado->paraSelector(),
            ], 422);
        }

        $espacio = DB::transaction(function () use (
            $request,
            $datos,
            $nombre,
            $idContenedor
        ) {
            return Espacio::create([
                'id_espacio_contenedor' =>
                    $idContenedor,

                'nombre' =>
                    $nombre,

                'descripcion' =>
                    $this->limpiarOpcional(
                        $datos['descripcion'] ?? null
                    ),

                'direccion' =>
                    $this->limpiarTexto(
                        $datos['direccion']
                    ),

                'indicaciones' =>
                    null,

                'latitud' =>
                    $datos['latitud'] ?? null,

                'longitud' =>
                    $datos['longitud'] ?? null,

                'capacidad' =>
                    $datos['capacidad'] ?? null,

                'permite_actividades' =>
                    true,

                'activo' =>
                    true,

                'estado_validacion' =>
                    Espacio::VALIDACION_PENDIENTE,

                'origen_registro' =>
                    $datos['origen'],

                'creado_por' =>
                    $request->user()?->id_usuario,

                'validado_por' =>
                    null,

                'validado_en' =>
                    null,
            ]);
        });

        $espacio->load('contenedor');

        return response()->json([
            'message' =>
                'El espacio fue registrado y quedó pendiente de validación administrativa.',

            'espacio' =>
                $espacio->paraSelector(),
        ], 201);
    }

    public function descendientes(
        Espacio $espacio,
        Request $request
    ): JsonResponse {
        $datos = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],

            'limite' => [
                'nullable',
                'integer',
                'min:5',
                'max:50',
            ],
        ]);

        $buscar = Str::squish(
            (string) ($datos['q'] ?? '')
        );

        $limite = (int) ($datos['limite'] ?? 20);

        $ids = $this->obtenerIdsDescendientes(
            $espacio
        );

        if (empty($ids)) {
            return response()->json([
                'data' => [],
            ]);
        }

        $query = Espacio::query()
            ->whereIn(
                'id_espacio',
                $ids
            )
            ->where(
                'activo',
                true
            )
            ->with('contenedor');

        if ($buscar !== '') {
            $query->where(function ($subquery) use ($buscar) {
                $subquery
                    ->where(
                        'nombre',
                        'ilike',
                        "%{$buscar}%"
                    )
                    ->orWhere(
                        'direccion',
                        'ilike',
                        "%{$buscar}%"
                    )
                    ->orWhere(
                        'descripcion',
                        'ilike',
                        "%{$buscar}%"
                    );
            });
        }

        $espacios = $query
            ->orderBy('nombre')
            ->limit($limite)
            ->get();

        return response()->json([
            'data' => $espacios
                ->map(
                    fn (Espacio $item) =>
                    $item->paraSelector()
                )
                ->values(),
        ]);
    }

    private function validar(
        Request $request,
        ?Espacio $espacio = null
    ): array {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'id_espacio_contenedor' => [
                'nullable',
                'integer',
                Rule::exists(
                    'tbl_espacios',
                    'id_espacio'
                ),
            ],

            'direccion' => [
                'nullable',
                'string',
                'max:300',
            ],

            'indicaciones' => [
                'nullable',
                'string',
                'max:500',
            ],

            'latitud' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitud' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'capacidad' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'permite_actividades' => [
                'nullable',
                'boolean',
            ],

            'activo' => [
                'nullable',
                'boolean',
            ],

            'recursos' => [
                'nullable',
                'array',
            ],

            'recursos.*.seleccionado' => [
                'nullable',
                'boolean',
            ],

            'recursos.*.cantidad' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'recursos.*.observacion' => [
                'nullable',
                'string',
                'max:300',
            ],
        ], [
            'nombre.required' =>
                'Escribe el nombre del espacio.',

            'nombre.max' =>
                'El nombre no puede superar los 150 caracteres.',

            'descripcion.max' =>
                'La descripción no puede superar los 2000 caracteres.',

            'id_espacio_contenedor.exists' =>
                'El lugar seleccionado ya no está disponible.',

            'direccion.max' =>
                'La dirección no puede superar los 300 caracteres.',

            'indicaciones.max' =>
                'Las indicaciones no pueden superar los 500 caracteres.',

            'latitud.between' =>
                'La latitud debe estar entre -90 y 90.',

            'longitud.between' =>
                'La longitud debe estar entre -180 y 180.',

            'capacidad.min' =>
                'La capacidad debe ser de al menos una persona.',

            'recursos.*.cantidad.min' =>
                'La cantidad de cada recurso debe ser al menos 1.',
        ]);
    }

    private function prepararDatos(
        Request $request,
        array $datos
    ): array {
        $datos['nombre'] = $this->limpiarTexto(
            $datos['nombre']
        );

        $datos['descripcion'] = $this->limpiarOpcional(
            $datos['descripcion'] ?? null
        );

        $datos['direccion'] = $this->limpiarOpcional(
            $datos['direccion'] ?? null
        );

        $datos['indicaciones'] = $this->limpiarOpcional(
            $datos['indicaciones'] ?? null
        );

        $datos['id_espacio_contenedor'] =
            $datos['id_espacio_contenedor'] ?? null;

        $datos['latitud'] =
            $datos['latitud'] ?? null;

        $datos['longitud'] =
            $datos['longitud'] ?? null;

        $datos['capacidad'] =
            $datos['capacidad'] ?? null;

        $datos['permite_actividades'] =
            $request->boolean(
                'permite_actividades'
            );

        $datos['activo'] =
            $request->boolean(
                'activo'
            );

        unset(
            $datos['recursos']
        );

        return $datos;
    }

    private function sincronizarRecursos(
        Espacio $espacio,
        array $recursos
    ): void {
        $sincronizacion = [];

        foreach ($recursos as $idRecurso => $datos) {
            if (
                !isset($datos['seleccionado'])
                || !$datos['seleccionado']
            ) {
                continue;
            }

            $cantidad = max(
                1,
                (int) ($datos['cantidad'] ?? 1)
            );

            $observacion = $this->limpiarOpcional(
                $datos['observacion'] ?? null
            );

            $sincronizacion[$idRecurso] = [
                'cantidad' => $cantidad,
                'observacion' => $observacion,
            ];
        }

        $espacio->recursos()->sync(
            $sincronizacion
        );
    }

    private function validarJerarquia(
        Espacio $espacio,
        ?int $idContenedor
    ): void {
        if ($idContenedor === null) {
            return;
        }

        if (
            $idContenedor ===
            $espacio->id_espacio
        ) {
            abort(
                422,
                'Un espacio no puede estar dentro de sí mismo.'
            );
        }

        $descendientes = $this->obtenerIdsDescendientes(
            $espacio
        );

        if (
            in_array(
                $idContenedor,
                $descendientes,
                true
            )
        ) {
            abort(
                422,
                'No puedes mover un espacio dentro de uno de sus propios espacios internos.'
            );
        }
    }

    private function obtenerIdsDescendientes(
        Espacio $espacio
    ): array {
        $ids = [];

        $nivelActual = [
            (int) $espacio->id_espacio,
        ];

        while (!empty($nivelActual)) {
            $hijos = Espacio::query()
                ->whereIn(
                    'id_espacio_contenedor',
                    $nivelActual
                )
                ->pluck(
                    'id_espacio'
                )
                ->map(
                    fn ($id) => (int) $id
                )
                ->all();

            $siguienteNivel = [];

            foreach ($hijos as $idHijo) {
                if (
                    in_array(
                        $idHijo,
                        $ids,
                        true
                    )
                ) {
                    continue;
                }

                $ids[] = $idHijo;
                $siguienteNivel[] = $idHijo;
            }

            $nivelActual = $siguienteNivel;
        }

        return $ids;
    }

    private function limpiarTexto(
        string $valor
    ): string {
        return Str::squish(
            $valor
        );
    }

    private function limpiarOpcional(
        ?string $valor
    ): ?string {
        if ($valor === null) {
            return null;
        }

        $valor = Str::squish(
            $valor
        );

        return $valor === ''
            ? null
            : $valor;
    }
}