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
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with(['rol', 'informacion_personal'])
            ->when($search, function ($query, $search) {
                $query->whereHas('informacion_personal', function ($q) use ($search) {
                    $q->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('documento', 'like', "%{$search}%");
                })->orWhere('correo', 'like', "%{$search}%");
            })
            ->orderBy('id_usuario', 'desc')
            ->paginate(10);

        return view('admin.users.listado', compact('users', 'search'));
    }

    // Muestra el formulario de creación (antes create)
    public function create()
    {
        $roles = Rol::all();
        return view('admin.users.crear', compact('roles'));
    }

    // Guarda el nuevo usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => 'required|email|unique:tbl_usuarios,correo',
            'documento' => 'required|string|unique:tbl_informacion_personal,documento',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|in:M,F,O',
            'id_rol' => 'required|exists:tbl_roles,id_rol',
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

    // Muestra el formulario de edición (antes edit)
    public function edit(User $user)
    {
        $user->load('informacion_personal');
        $roles = Rol::all();
        return view('admin.users.editar', compact('user', 'roles'));
    }

    // Actualiza el usuario
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo' => 'required|email|unique:tbl_usuarios,correo,' . $user->id_usuario . ',id_usuario',
            'documento' => 'required|string|unique:tbl_informacion_personal,documento,' . $user->id_informacion_personal . ',id_informacion_personal',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable|in:M,F,O',
            'id_rol' => 'required|exists:tbl_roles,id_rol',
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

    // Alterna el estado del usuario
    public function toggleEstado(User $user)
    {
        try {
            // Invierte el valor booleano (true -> false, false -> true)
            $user->update([
                'estado_activo' => !$user->estado_activo
            ]);

            $nuevoEstado = $user->estado_activo ? 'activado' : 'desactivado';
            return back()->with('success', "Usuario {$nuevoEstado} correctamente.");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al cambiar el estado del usuario.');
        }
    }
    public function search(Request $request)
{
    $search = $request->input('q', '');

    $users = User::with(['rol', 'informacion_personal'])
        ->when($search, function ($query, $search) {

            $query->where(function ($q) use ($search) {

                $q->where('correo', 'like', "%{$search}%")
                    ->orWhereHas('informacion_personal', function ($q) use ($search) {

                        $q->where('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%")
                            ->orWhere('documento', 'like', "%{$search}%")
                            ->orWhere('telefono', 'like', "%{$search}%");

                    })
                    ->orWhereHas('rol', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%");
                    });

            });

        })
        ->orderBy('id_usuario', 'desc')
        ->get();

    return response()->json([
        'users' => $users->map(function ($user) {

            return [
                'id' => $user->id_usuario,
                'nombres' => $user->informacion_personal->nombres ?? '',
                'apellidos' => $user->informacion_personal->apellidos ?? '',
                'documento' => $user->informacion_personal->documento ?? '',
                'correo' => $user->correo,
                'rol_nombre' => $user->rol->nombre ?? '',
                'estado_activo' => $user->estado_activo,
                'edit_url' => route('admin.users.edit', $user),
                'toggle_url' => route('admin.users.toggle', $user),
            ];

        })
    ]);
}
}