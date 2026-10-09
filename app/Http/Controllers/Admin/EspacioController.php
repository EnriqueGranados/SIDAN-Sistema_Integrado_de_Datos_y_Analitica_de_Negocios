<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Espacio;
use App\Models\Recurso;
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
                            $contenedor->where('nombre', 'ilike', "%{$buscar}%");
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

        $datos = $this->prepararDatos($request, $datos);

        DB::transaction(function () use ($request, $datos) {
            $espacio = Espacio::create($datos);

            $this->sincronizarRecursos(
                $espacio,
                $request->input('recursos', [])
            );
        });

        return redirect()
            ->route('admin.espacios.index')
            ->with('success', 'El espacio se creó correctamente.');
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

        $idsNoDisponibles = $this->obtenerIdsDescendientes($espacio);

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
            ->with('success', 'El espacio se actualizó correctamente.');
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
            ->with('success', 'El espacio se eliminó correctamente.');
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
            $request->boolean('permite_actividades');

        $datos['activo'] =
            $request->boolean('activo');

        unset($datos['recursos']);

        return $datos;
    }

    private function sincronizarRecursos(
        Espacio $espacio,
        array $recursos
    ): void {
        $sincronizacion = [];

        foreach ($recursos as $idRecurso => $datos) {
            if (
                !isset($datos['seleccionado']) ||
                !$datos['seleccionado']
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

        if ($idContenedor === $espacio->id_espacio) {
            abort(
                422,
                'Un espacio no puede estar dentro de sí mismo.'
            );
        }

        $descendientes = $this->obtenerIdsDescendientes(
            $espacio
        );

        if (in_array(
            $idContenedor,
            $descendientes,
            true
        )) {
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
        $pendientes = [
            $espacio->id_espacio,
        ];

        while (!empty($pendientes)) {
            $idActual = array_shift(
                $pendientes
            );

            $hijos = Espacio::query()
                ->where(
                    'id_espacio_contenedor',
                    $idActual
                )
                ->pluck('id_espacio')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($hijos as $idHijo) {
                if (in_array(
                    $idHijo,
                    $ids,
                    true
                )) {
                    continue;
                }

                $ids[] = $idHijo;
                $pendientes[] = $idHijo;
            }
        }

        return $ids;
    }

    private function limpiarTexto(
        string $valor
    ): string {
        return Str::squish($valor);
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