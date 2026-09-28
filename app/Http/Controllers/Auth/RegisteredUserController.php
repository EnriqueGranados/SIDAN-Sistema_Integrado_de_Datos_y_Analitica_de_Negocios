<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\InformacionPersonal;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
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

        $usuario = DB::transaction(function () use ($request) {

            // Obtener el rol normal de usuario
            $rol = Rol::where('nombre', 'usuario')->firstOrFail();

            // Crear la persona
            $informacion_personal = InformacionPersonal::create([
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'documento' => $request->documento,
                'telefono' => $request->telefono,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'ubicacion' => $request->ubicacion,
            ]);

            // Crear la cuenta de usuario
            return User::create([
                'id_informacion_personal' => $informacion_personal->id_informacion_personal,
                'id_rol' => $rol->id_rol,
                'correo' => $request->email,
                'password_hash' => Hash::make($request->password),
                'must_change_password' => false,
                'estado_activo' => true,
                'eliminado' => false,
            ]);
        });

        event(new Registered($usuario));

        Auth::login($usuario);

        return redirect('/');
    }
}