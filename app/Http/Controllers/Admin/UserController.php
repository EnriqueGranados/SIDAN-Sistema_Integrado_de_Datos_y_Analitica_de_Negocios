<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InformacionPersonal;
use App\Models\Rol;
use App\Models\User;
use App\Notifications\UsuarioCreadoNotification;
use App\Support\AccesoModulos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $users = User::with(['rol', 'informacion_personal'])
            ->where('eliminado', false)
            ->where('estado_activo', true)
            ->when($search, function ($query, $search) {
                $query->where(function ($consulta) use ($search) {
                    $consulta->whereHas('informacion_personal', function ($q) use ($search) {
                        $q->where('nombres', 'like', "%{$search}%")
                            ->orWhere('apellidos', 'like', "%{$search}%")
                            ->orWhere('documento', 'like', "%{$search}%");
                    })->orWhere('correo', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id_usuario')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.listado', compact('users', 'search'));
    }

    public function create()
    {
        $roles = $this->rolesAsignables();

        return view('admin.users.crear', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'documento' => [
                'nullable',
                'string',
                'regex:/^[0-9]{8}-[0-9]$/',
                'unique:tbl_informacion_personal,documento',
            ],
            'telefono' => [
                'nullable',
                'string',
                'regex:/^\+[1-9][0-9]{6,14}$/',
                'unique:tbl_informacion_personal,telefono',
            ],
            'fecha_nacimiento' => [
                'nullable',
                'date',
                'before_or_equal:' . now()->subYears(10)->format('Y-m-d'),
            ],
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
            'rol' => ['required', 'integer', 'exists:tbl_roles,id_rol'],
        ], [
            'documento.unique' => 'Este DUI ya está registrado.',
            'documento.regex' => 'Ingresa un número de DUI válido.',
            'telefono.regex' => 'Ingresa un número de teléfono válido.',
            'telefono.unique' => 'Este número de teléfono ya está registrado.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'fecha_nacimiento.before_or_equal' => 'Debes tener al menos 10 años para registrarte.',
        ]);

        $this->validarAsignacion($validated['rol']);

        $passwordTemporal = Str::random(20);

        try {
            $user = DB::transaction(function () use ($validated, $passwordTemporal) {
                $info = InformacionPersonal::create([
                    'nombres' => $validated['nombres'],
                    'apellidos' => $validated['apellidos'],
                    'documento' => $validated['documento'] ?? null,
                    'telefono' => $validated['telefono'] ?? null,
                    'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                    'genero' => $validated['genero'] ?? null,
                    'ubicacion' => $validated['ubicacion'] ?? null,
                ]);

                return User::create([
                    'id_informacion_personal' => $info->id_informacion_personal,
                    'id_rol' => $validated['rol'],
                    'correo' => $validated['email'],
                    'password_hash' => Hash::make($passwordTemporal),
                    'estado_activo' => true,
                    'eliminado' => false,
                    'must_change_password' => true,
                ]);
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->with('error', 'No fue posible crear el usuario.')
                ->withInput($request->except('password', 'password_confirmation'));
        }

        try {
            $user->notify(new UsuarioCreadoNotification($passwordTemporal));

            return redirect()
                ->route('admin.users.index')
                ->with(
                    'success',
                    'Usuario creado correctamente. Se enviaron sus credenciales al correo electrónico.'
                );
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('admin.users.index')
                ->with(
                    'warning',
                    'El usuario fue creado, pero no se pudo enviar el correo. No lo crees nuevamente; utilizá la opción para regenerar su contraseña.'
                );
        }
    }

    public function edit(User $user)
    {
        $this->autorizarGestion($user);

        $user->load('informacion_personal');
        $roles = $this->rolesAsignables();

        return view('admin.users.editar', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->autorizarGestion($user);

        $validated = $request->validate([
            'imagen_perfil' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
            'eliminar_imagen_perfil' => ['nullable', 'boolean'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'documento' => [
                'nullable',
                'string',
                'regex:/^[0-9]{8}-[0-9]$/',
                'unique:tbl_informacion_personal,documento,'
                    . $user->id_informacion_personal . ',id_informacion_personal',
            ],
            'telefono' => [
                'nullable',
                'string',
                'regex:/^\+[1-9][0-9]{6,14}$/',
                'unique:tbl_informacion_personal,telefono,'
                    . $user->id_informacion_personal . ',id_informacion_personal',
            ],
            'fecha_nacimiento' => [
                'nullable',
                'date',
                'before_or_equal:' . now()->subYears(10)->format('Y-m-d'),
            ],
            'genero' => ['nullable', 'in:M,F,O'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:150',
                'unique:tbl_usuarios,correo,' . $user->id_usuario . ',id_usuario',
            ],
            'rol' => ['required', 'integer', 'exists:tbl_roles,id_rol'],
        ]);

        $this->validarAsignacion($validated['rol']);

        $imagenAnterior = $user->imagen_perfil;
        $imagenNueva = null;
        $eliminarImagen = $request->boolean('eliminar_imagen_perfil');

        try {
            if (!$eliminarImagen && $request->hasFile('imagen_perfil')) {
                $imagenNueva = $request->file('imagen_perfil')
                    ->store('perfiles', 'public');
            }

            DB::transaction(function () use (
                $user,
                $validated,
                $eliminarImagen,
                $imagenNueva
            ) {
                $user->informacion_personal->update([
                    'nombres' => $validated['nombres'],
                    'apellidos' => $validated['apellidos'],
                    'documento' => $validated['documento'] ?? null,
                    'telefono' => $validated['telefono'] ?? null,
                    'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                    'genero' => $validated['genero'] ?? null,
                ]);

                $userData = [
                    'correo' => $validated['email'],
                    'id_rol' => $validated['rol'],
                ];

                if ($eliminarImagen) {
                    $userData['imagen_perfil'] = null;
                } elseif ($imagenNueva) {
                    $userData['imagen_perfil'] = $imagenNueva;
                }

                $user->update($userData);
            });
        } catch (\Throwable $exception) {
            if ($imagenNueva) {
                Storage::disk('public')->delete($imagenNueva);
            }

            report($exception);

            return back()
                ->with('error', 'No fue posible actualizar el usuario.')
                ->withInput();
        }

        if ($imagenAnterior && ($eliminarImagen || $imagenNueva !== null)) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function regenerarPassword(User $user)
    {
        $this->autorizarGestion($user);

        if ($user->eliminado) {
            return back()->with(
                'error',
                'No se puede generar una contraseña para un usuario eliminado.'
            );
        }

        $passwordTemporal = Str::random(20);

        try {
            DB::transaction(function () use ($user, $passwordTemporal) {
                $user->password_hash = Hash::make($passwordTemporal);
                $user->must_change_password = true;
                $user->remember_token = null;
                $user->save();
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'No fue posible generar la nueva contraseña.'
            );
        }

        try {
            $user->notify(new UsuarioCreadoNotification($passwordTemporal, true));

            return back()->with(
                'success',
                'Se generó una nueva contraseña temporal y se enviaron las credenciales al correo del usuario.'
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with(
                'warning',
                'La contraseña fue actualizada, pero no se pudo enviar el correo. Puedes intentar generar una nueva contraseña.'
            );
        }
    }

    public function toggleEstado(User $user)
    {
        $this->autorizarGestion($user);

        try {
            $nuevoEstado = !$user->estado_activo;
            $user->update(['estado_activo' => $nuevoEstado]);

            $mensaje = $nuevoEstado ? 'activado' : 'baneado';

            return response()->json([
                'success' => true,
                'message' => "Usuario {$mensaje} correctamente.",
            ]);
        } catch (\Exception $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible cambiar el estado del usuario.',
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        $this->autorizarGestion($user);

        try {
            $user->update([
                'eliminado' => true,
                'estado_activo' => false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado permanentemente del sistema.',
            ]);
        } catch (\Exception $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'No fue posible eliminar el usuario.',
            ], 500);
        }
    }

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
            ->orderByDesc('id_usuario')
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
            }),
        ]);
    }

    public function banned()
    {
        $users = User::with(['rol', 'informacion_personal'])
            ->where('eliminado', false)
            ->where('estado_activo', false)
            ->orderByDesc('id_usuario')
            ->paginate(10);

        return view('admin.users.banned', compact('users'));
    }

    public function restore(User $user)
    {
        $this->autorizarGestion($user);

        try {
            $user->update([
                'eliminado' => false,
                'estado_activo' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario reactivado correctamente y devuelto al listado principal.',
            ]);
        } catch (\Exception $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Error al reactivar el usuario.',
            ], 500);
        }
    }

    public function deleted()
    {
        $users = User::with(['rol', 'informacion_personal'])
            ->where('eliminado', true)
            ->orderByDesc('id_usuario')
            ->paginate(10);

        return view('admin.users.deleted', compact('users'));
    }

    private function rolesAsignables()
    {
        return Rol::orderBy('nombre')
            ->get()
            ->filter(
                fn (Rol $rol) => AccesoModulos::puedeAsignar(auth()->user(), $rol)
            )
            ->values();
    }

    private function validarAsignacion(int|string $idRol): void
    {
        $rol = Rol::findOrFail($idRol);

        if (!AccesoModulos::puedeAsignar(auth()->user(), $rol)) {
            throw ValidationException::withMessages([
                'rol' => 'No tenés autorización para asignar este rol.',
            ]);
        }
    }

    private function autorizarGestion(User $user): void
    {
        abort_unless(
            AccesoModulos::puedeGestionar(auth()->user(), $user),
            403,
            'No podés administrar esta cuenta. Para tus datos personales utilizá Mi perfil.'
        );
    }
}