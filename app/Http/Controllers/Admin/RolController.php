<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    // 1. Listado de roles
    public function index()
    {
        $roles = Rol::withCount('usuarios')
            ->orderBy('id_rol', 'desc')
            ->paginate(10);

        return view('admin.roles.listado', compact('roles'));
    }

    // 2. Formulario de creación
    public function create()
    {
        return view('admin.roles.crear');
    }

    // 3. Guardar nuevo rol
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:tbl_roles,nombre',
            'descripcion' => 'required|string|max:255',
        ]);

        try {
            Rol::create([
                'nombre' => strtolower($validated['nombre']),
                'descripcion' => $validated['descripcion'],
            ]);

            return redirect()->route('admin.roles.index')->with('success', 'Rol creado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear el rol: ' . $e->getMessage())->withInput();
        }
    }

    // 4. Formulario de edición
    public function edit(Rol $rol)
    {
        return view('admin.roles.editar', compact('rol'));
    }

    // 5. Actualizar rol
    public function update(Request $request, Rol $rol)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:tbl_roles,nombre,' . $rol->id_rol . ',id_rol',
            'descripcion' => 'required|string|max:255',
        ]);

        try {
            $rol->update([
                'nombre' => strtolower($validated['nombre']),
                'descripcion' => $validated['descripcion'],
            ]);

            return redirect()->route('admin.roles.index')->with('success', 'Rol actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el rol: ' . $e->getMessage())->withInput();
        }
    }

    // 6. Eliminar rol
    public function destroy(Rol $rol)
    {
        // Verificar si hay usuarios con este rol
        if ($rol->usuarios()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el rol porque tiene usuarios asignados.');
        }

        // Evitar eliminar roles críticos del sistema (opcional pero recomendado)
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