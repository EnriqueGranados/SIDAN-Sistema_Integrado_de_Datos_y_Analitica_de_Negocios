<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rol;
use App\Models\InformacionPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // 1. Muestra la lista (antes index)
    public function index()
    {
        $users = User::with(['rol', 'informacion_personal'])
            ->orderBy('id_usuario', 'desc')
            ->paginate(10);
            
        return view('admin.users.listado', compact('users'));
    }

    // 2. Muestra el formulario de creación (antes create)
    public function create()
    {
        $roles = Rol::all();
        return view('admin.users.crear', compact('roles'));
    }

    // 3. Guarda el nuevo usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => 'required|email|unique:usuarios,correo',
            'documento' => 'required|string|unique:informacion_personal,documento',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|in:M,F,O',
            'id_rol' => 'required|exists:roles,id_rol',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        DB::beginTransaction();
        try {
            $info = InformacionPersonal::create([
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'],
                'documento' => $validated['documento'],
                'telefono' => $validated['telefono'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'genero' => $validated['genero'],
            ]);

            User::create([
                'id_informacion_personal' => $info->id_informacion_personal,
                'id_rol' => $validated['id_rol'],
                'correo' => $validated['correo'],
                'password_hash' => Hash::make($validated['password']),
                'estado' => 'activo',
                'must_change_password' => false,
            ]);

            DB::commit();
            return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el usuario: ' . $e->getMessage())->withInput();
        }
    }

    // 4. Muestra el formulario de edición (antes edit)
    public function edit(User $user)
    {
        $user->load('informacion_personal');
        $roles = Rol::all();
        return view('admin.users.editar', compact('user', 'roles'));
    }

    // 5. Actualiza el usuario
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => 'required|email|unique:usuarios,correo,' . $user->id_usuario . ',id_usuario',
            'documento' => 'required|string|unique:informacion_personal,documento,' . $user->id_informacion_personal . ',id_informacion_personal',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|in:M,F,O',
            'id_rol' => 'required|exists:roles,id_rol',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        DB::beginTransaction();
        try {
            $user->informacion_personal->update([
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'],
                'documento' => $validated['documento'],
                'telefono' => $validated['telefono'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'],
                'genero' => $validated['genero'],
            ]);

            $userData = [
                'correo' => $validated['correo'],
                'id_rol' => $validated['id_rol'],
            ];

            if (!empty($validated['password'])) {
                $userData['password_hash'] = Hash::make($validated['password']);
            }

            $user->update($userData);

            DB::commit();
            return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar: ' . $e->getMessage())->withInput();
        }
    }

    // 6. Elimina el usuario
    public function destroy(User $user)
    {
        DB::beginTransaction();
        try {
            $user->informacion_personal()->delete();
            $user->delete();
            DB::commit();
            return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el usuario.');
        }
    }
}