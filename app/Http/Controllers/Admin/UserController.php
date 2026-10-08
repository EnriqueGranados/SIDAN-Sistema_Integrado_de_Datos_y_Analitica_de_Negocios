<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rol;
use App\Models\InformacionPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class UserController extends Controller
{

    // Muestra solo usuarios activos y no eliminados
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with(['rol', 'informacion_personal'])
            ->where('eliminado', false)  
            ->where('estado_activo', true)   
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
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'documento' => ['nullable', 'string', 'regex:/^[0-9]{8}-[0-9]$/', 'unique:tbl_informacion_personal,documento'],
            'telefono' => ['nullable', 'string', 'regex:/^\+[1-9][0-9]{6,14}$/', 'unique:tbl_informacion_personal,telefono'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(10)->format('Y-m-d')],
            'genero' => ['nullable', 'string', 'max:10'],
            'ubicacion' => ['nullable', 'string', 'max:150'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:150',
                'unique:tbl_usuarios,correo',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],

            'rol' => 'required|exists:tbl_roles,id_rol',
        ],
        [
            'documento.unique' => 'Este DUI ya está registrado.',
            'documento.regex' => 'Ingresa un número de DUI válido.',

            'telefono.regex' => 'Ingresa un número de teléfono válido.',
            'telefono.unique' => 'Este número de teléfono ya está registrado.',

            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',

            'fecha_nacimiento.before_or_equal' => 'Debes tener al menos 10 años para registrarte.',
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
                'ubicacion' => $validated['ubicacion'],
            ]);

            User::create([
                'id_informacion_personal' => $info->id_informacion_personal,
                'id_rol' => $validated['rol'],
                'correo' => $validated['email'],
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
            // Imagen de perfil.
            'imagen_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120',],
            'eliminar_imagen_perfil' => ['nullable', 'boolean',],

            // Información personal.
            'nombres' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'documento' => ['nullable', 'string', 'regex:/^[0-9]{8}-[0-9]$/', 'unique:tbl_informacion_personal,documento,' . $user->id_informacion_personal . ',id_informacion_personal'],
            'telefono' => ['nullable', 'string', 'regex:/^\+[1-9][0-9]{6,14}$/', 'unique:tbl_informacion_personal,telefono,' . $user->id_informacion_personal . ',id_informacion_personal'],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(10)->format('Y-m-d')],
            'genero' => 'nullable|in:M,F,O',
            
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:150',
                'unique:tbl_usuarios,correo,' . $user->id_usuario . ',id_usuario',
            ],

            'password' => [
                'nullable',
                'confirmed',
                Rules\Password::defaults(),
            ],

            'rol' => 'required|exists:tbl_roles,id_rol',
        ]);

        // Guardar referencia a la imagen actual.
        $imagenAnterior = $user->imagen_perfil;
        $imagenNueva = null;

        // Determinar si se solicitó eliminar la imagen.
        $eliminarImagen = $request->boolean('eliminar_imagen_perfil');

        try {
            // Subir imagen solamente si no se solicitó eliminarla.
            if (!$eliminarImagen && $request->hasFile('imagen_perfil')) {
                $imagenNueva = $request->file('imagen_perfil')->store('perfiles', 'public');
            }

            // Actualizar datos dentro de una transacción.
            DB::transaction(function () use (
                $user,
                $validated,
                $eliminarImagen,
                $imagenNueva
            ) {
                // Actualizar información personal.
                $user->informacion_personal->update([
                    'nombres' => $validated['nombres'],
                    'apellidos' => $validated['apellidos'],
                    'documento' => $validated['documento'] ?? null,
                    'telefono' => $validated['telefono'] ?? null,
                    'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                    'genero' => $validated['genero'] ?? null,
                ]);

                // Datos de la cuenta.
                $userData = [
                    'correo' => $validated['email'],
                    'id_rol' => $validated['rol'],
                ];

                // Actualizar contraseña solamente si se proporcionó.
                if (!empty($validated['password'])) {
                    $userData['password_hash'] = Hash::make(
                        $validated['password']
                    );
                }

                // Actualizar imagen de perfil.
                if ($eliminarImagen) {
                    $userData['imagen_perfil'] = null;
                } elseif ($imagenNueva) {
                    $userData['imagen_perfil'] = $imagenNueva;
                }

                // Guardar cambios del usuario.
                $user->update($userData);
            });

        } catch (\Throwable $e) {
            // Si falla la actualización, eliminar la imagen recién subida.
            if ($imagenNueva) {
                Storage::disk('public')->delete($imagenNueva);
            }

            report($e);

            return back()->with('error', 'No fue posible actualizar el usuario.')->withInput();
        }

        // Eliminar imagen anterior solo después de guardar correctamente.
        $imagenFueReemplazada = !is_null($imagenNueva);

        if ($imagenAnterior && ($eliminarImagen || $imagenFueReemplazada)) 
        {
            Storage::disk('public')->delete($imagenAnterior);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    // Alterna el estado y devuelve JSON para Alpine.js
    public function toggleEstado(User $user)
    {
        try {
            $nuevoEstado = !$user->estado_activo;
            $user->update(['estado_activo' => $nuevoEstado]);

            $mensaje = $nuevoEstado ? 'activado' : 'baneado';
            return response()->json(['success' => true, 'message' => "Usuario {$mensaje} correctamente."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error.'], 500);
        }
    }

    // Eliminar (Borrado lógico completo)
    public function destroy(User $user)
    {
        try {
            $user->update([
                'eliminado' => true,
                'estado_activo' => false,
            ]);

            return response()->json(['success' => true, 'message' => 'Usuario eliminado permanentemente del sistema.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error.'], 500);
        }
    }

    // Búsqueda en tiempo real 
    public function search(Request $request)
    {
        $search = $request->input('q', '');

        $users = User::with(['rol', 'informacion_personal'])
            ->where('eliminado', false)
            ->where('estado_activo', true)  
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
                    'ban_url' => route('admin.users.destroy', $user), 
                ];
            })
        ]);
    }
    // Bloqueados
    public function banned()
    {
        $users = User::with(['rol', 'informacion_personal'])
            ->where('eliminado', false)         
            ->where('estado_activo', false)     
            ->orderBy('id_usuario', 'desc')
            ->paginate(10);

        return view('admin.users.banned', compact('users'));
    }

    // Restaurar usuario (funciona para baneados y eliminados)
    public function restore($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->update([
                'eliminado' => false,
                'estado_activo' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario reactivado correctamente y devuelto al listado principal.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar el usuario.'
            ], 500);
        }
    }

    // Ver usuarios eliminados (solo superadmin)
    public function deleted()
    {
        $users = User::with(['rol', 'informacion_personal'])
            ->where('eliminado', true)
            ->orderBy('id_usuario', 'desc')
            ->paginate(10);

        return view('admin.users.deleted', compact('users'));
    }
}