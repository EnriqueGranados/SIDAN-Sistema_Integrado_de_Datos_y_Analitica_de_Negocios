<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class RolController extends Controller
{
    // Mostrar el listado de roles junto con la cantidad de usuarios asignados
    public function index()
    {
        $roles = Rol::withCount('usuarios')
            ->orderBy('id_rol', 'desc')
            ->paginate(10);

        return view('admin.roles.listado', compact('roles'));
    }

    // Mostrar el formulario para crear un nuevo rol
    public function create()
    {
        return view('admin.roles.crear');
    }

    // Guardar un nuevo rol en el sistema
    public function store(Request $request)
    {
        // Normalizar el nombre antes de realizar la validación
        $request->merge([
            'nombre' => strtolower(trim($request->nombre))
        ]);

        // Validar los datos enviados y evitar nombres de roles duplicados
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tbl_roles', 'nombre')
            ],
            'descripcion' => [
                'required',
                'string'
            ]
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.string' => 'El nombre del rol debe ser un texto válido.',
            'nombre.max' => 'El nombre del rol no puede superar los 255 caracteres.',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
            'descripcion.required' => 'La descripción del rol es obligatoria.',
            'descripcion.string' => 'La descripción debe ser un texto válido.'
        ]);

        try {
            Rol::create($validated);

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'El rol fue creado correctamente.');
        } catch (QueryException $e) {

            // Registrar el error de base de datos para facilitar su revisión
            Log::error('Error al crear rol', [
                'error' => $e->getMessage()
            ]);

            // Controlar posibles duplicados detectados directamente por PostgreSQL
            if (($e->errorInfo[0] ?? null) === '23505') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'nombre' => 'Ya existe un rol con ese nombre.'
                    ]);
            }

            return back()
                ->withInput()
                ->with('error', 'No se pudo crear el rol. Inténtalo nuevamente.');
        }
    }

    // Mostrar el formulario para editar un rol existente
    public function edit(Rol $rol)
    {
        return view('admin.roles.editar', compact('rol'));
    }

    // Actualizar la información de un rol existente
    public function update(Request $request, Rol $rol)
    {
        // Normalizar el nombre antes de realizar la validación
        $request->merge([
            'nombre' => strtolower(trim($request->nombre))
        ]);

        // Validar los datos ignorando al mismo rol en la regla de nombre único
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tbl_roles', 'nombre')
                    ->ignore($rol->id_rol, 'id_rol')
            ],
            'descripcion' => [
                'required',
                'string'
            ]
        ], [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.string' => 'El nombre del rol debe ser un texto válido.',
            'nombre.max' => 'El nombre del rol no puede superar los 255 caracteres.',
            'nombre.unique' => 'Ya existe otro rol con ese nombre.',
            'descripcion.required' => 'La descripción del rol es obligatoria.',
            'descripcion.string' => 'La descripción debe ser un texto válido.'
        ]);

        try {
            $rol->update($validated);

            return redirect()
                ->route('admin.roles.index')
                ->with('success', 'El rol fue actualizado correctamente.');
        } catch (QueryException $e) {

            // Registrar el error incluyendo el rol que se intentaba actualizar
            Log::error('Error al actualizar rol', [
                'rol' => $rol->id_rol,
                'error' => $e->getMessage()
            ]);

            // Controlar posibles duplicados detectados directamente por PostgreSQL
            if (($e->errorInfo[0] ?? null) === '23505') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'nombre' => 'Ya existe otro rol con ese nombre.'
                    ]);
            }

            return back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el rol. Inténtalo nuevamente.');
        }
    }

    // Eliminar un rol del sistema
    public function destroy(Rol $rol)
    {
        // Evitar eliminar roles que todavía tengan usuarios asignados
        if ($rol->usuarios()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        // Proteger los roles base necesarios para el funcionamiento del sistema
        if (in_array($rol->nombre, ['superadmin', 'admin', 'usuario'])) {
            return back()->with('error', 'No se pueden eliminar los roles base del sistema.');
        }

        try {
            $rol->delete();

            return redirect()->route('admin.roles.index')->with('success', 'Rol eliminado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el rol.');
        }
    }
}