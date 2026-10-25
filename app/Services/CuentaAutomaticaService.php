<?php

namespace App\Services;

use App\Models\InformacionPersonal;
use App\Models\Rol;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CuentaAutomaticaService
{
    // Invocar dentro de la transacción de negocio que crea inscripción/orden.
    public function crear(string $nombre, string $correo, ?string $telefono): User
    {
        $correo = mb_strtolower(trim($correo));
        $nombre = trim(preg_replace('/\s+/u', ' ', $nombre));
        if (User::whereRaw('lower(correo) = ?', [$correo])->exists()) {
            throw ValidationException::withMessages([
                'correo' => 'Este correo ya tiene una cuenta. Iniciá sesión o recuperá tu contraseña.',
            ]);
        }
        $partes = preg_split('/\s+/u', $nombre, 2);
        $nombres = mb_substr($partes[0] ?? '', 0, 100);
        $apellidos = mb_substr($partes[1] ?? '', 0, 100);
        if (!$nombres || !$apellidos) {
            throw ValidationException::withMessages([
                'nombre' => 'Ingresá nombre y apellido para continuar.',
            ]);
        }
        $rol = Rol::where('nombre', 'usuario')->firstOrFail();
        $info = InformacionPersonal::create([
            'nombres' => $nombres,
            'apellidos' => $apellidos,
            'telefono' => filled($telefono) ? trim($telefono) : null,
        ]);
        // Nadie conoce esta contraseña; se establece mediante un enlace temporal.
        return User::create([
            'id_informacion_personal' => $info->id_informacion_personal,
            'id_rol' => $rol->id_rol,
            'correo' => $correo,
            'password_hash' => Hash::make(Str::random(64)),
            'must_change_password' => false,
            'estado_activo' => true,
            'eliminado' => false,
        ]);
    }

    // Llamar DESPUÉS del commit. No enviar enlaces dentro de la transacción.
    public function enviarEnlace(User $usuario): void
    {
        $token = Password::broker()->createToken($usuario);
        $usuario->notify(new SetPasswordNotification(route('password.reset', [
            'token' => $token,
            'email' => $usuario->correo,
        ])));
    }
}
