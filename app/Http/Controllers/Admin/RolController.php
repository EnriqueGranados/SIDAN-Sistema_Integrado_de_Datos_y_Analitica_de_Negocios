<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Support\AccesoModulos;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RolController extends Controller
{
    public function index()
    {
        $roles = Rol::withCount('usuarios')
            ->orderByDesc('id_rol')
            ->paginate(10);

        return view('admin.roles.listado', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.crear', [
            'modulosDisponibles' => AccesoModulos::asignables(),
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        try {
            DB::transaction(function () use ($datos) {
                Rol::create($datos);
            });

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Rol y accesos creados correctamente.');
        } catch (QueryException $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'nombre' => ($exception->errorInfo[0] ?? null) === '23505'
                        ? 'Ya existe un rol con ese nombre.'
                        : 'No fue posible guardar el rol.',
                ]);
        }
    }

    public function edit(Rol $rol)
    {
        return view('admin.roles.editar', [
            'rol' => $rol,
            'modulosDisponibles' => AccesoModulos::asignables(),
        ]);
    }

    public function update(Request $request, Rol $rol)
    {
        $datos = $this->validar($request, $rol);

        try {
            DB::transaction(function () use ($rol, $datos) {
                $rol->update($datos);
            });

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Rol y accesos actualizados correctamente.');
        } catch (QueryException $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'nombre' => ($exception->errorInfo[0] ?? null) === '23505'
                        ? 'Ya existe otro rol con ese nombre.'
                        : 'No fue posible actualizar el rol.',
                ]);
        }
    }

    public function destroy(Rol $rol)
    {
        if ($rol->esBase()) {
            return back()->with(
                'error',
                'No se pueden eliminar los roles base del sistema.'
            );
        }

        if ($rol->usuarios()->exists()) {
            return back()->with(
                'error',
                'No se puede eliminar el rol porque tiene usuarios asignados.'
            );
        }

        try {
            $rol->delete();

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'Rol eliminado correctamente.');
        } catch (QueryException $exception) {
            report($exception);

            return back()->with('error', 'No fue posible eliminar el rol.');
        }
    }

    private function validar(Request $request, ?Rol $rol = null): array
    {
        $nombre = $request->input('nombre');

        if (is_string($nombre)) {
            $request->merge([
                'nombre' => mb_strtolower(trim($nombre)),
            ]);
        }

        $reglaUnica = Rule::unique('tbl_roles', 'nombre');

        if ($rol) {
            $reglaUnica->ignore($rol->id_rol, 'id_rol');
        }

        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255', $reglaUnica],
            'descripcion' => ['required', 'string', 'max:2000'],
            'modulos' => ['sometimes', 'array'],
            'modulos.*' => [
                'required',
                'string',
                'distinct',
                Rule::in(array_keys(AccesoModulos::asignables())),
            ],
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'modulos.array' => 'Los módulos seleccionados no son válidos.',
            'modulos.*.in' => 'Uno de los módulos seleccionados no está permitido.',
        ]);

        $nombresReservados = ['superadmin', 'admin', 'usuario'];

        if ($rol?->esBase() && $datos['nombre'] !== $rol->nombre) {
            throw ValidationException::withMessages([
                'nombre' => 'No se puede cambiar el nombre de un rol base.',
            ]);
        }

        if (
            !$rol?->esBase()
            && in_array($datos['nombre'], $nombresReservados, true)
        ) {
            throw ValidationException::withMessages([
                'nombre' => 'Ese nombre está reservado para un rol base.',
            ]);
        }

        $datos['modulos'] = array_values($datos['modulos'] ?? []);

        if (in_array($rol?->nombre, ['superadmin', 'usuario'], true)) {
            $datos['modulos'] = [];
        }

        return $datos;
    }
}