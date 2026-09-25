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
            'documento' => ['nullable', 'string', 'max:30', 'unique:personas,documento'],
            'telefono' => ['nullable', 'string', 'max:25'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'genero' => ['nullable', 'string', 'max:10'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:150',
                'unique:usuarios,correo',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
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
            ]);

            // Crear la cuenta de usuario
            return User::create([
                'id_informacion_personal' => $informacion_personal->id_informacion_personal,
                'id_rol' => $rol->id_rol,
                'correo' => $request->email,
                'password_hash' => Hash::make($request->password),
                'must_change_password' => false,
                'estado' => 'activo',
            ]);
        });

        event(new Registered($usuario));

        Auth::login($usuario);

        return redirect('/');
    }
}