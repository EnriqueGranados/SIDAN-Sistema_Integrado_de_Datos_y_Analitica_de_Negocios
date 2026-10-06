<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Notifications\ChangePasswordNotification;
use App\Notifications\SetPasswordNotification;
use Illuminate\Support\Facades\URL;

class ProfileController extends Controller
{
    // Mostrar perfil del usuario
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    // Actualizar el perfil del usuario
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $imagenAnterior = $user->imagen_perfil;
        $imagenNueva = null;

        // Si el usuario no quiere eliminar la imagen y ha subido una nueva, guardarla temporalmente.
        if (!$request->boolean('eliminar_imagen_perfil') && $request->hasFile('imagen_perfil')) 
        {
            $imagenNueva = $request->file('imagen_perfil')->store('perfiles', 'public');
        }

        try {
            DB::transaction(function () use ($user, $data, $request, $imagenNueva) 
            {
                // Actualizar imagen de perfil.
                if ($request->boolean('eliminar_imagen_perfil')) {
                    $user->imagen_perfil = null;

                } elseif ($imagenNueva) {
                    $user->imagen_perfil = $imagenNueva;
                }

                // Actualizar información personal.
                $personal = $user->informacion_personal;

                $personal->nombres = $data['nombres'];
                $personal->apellidos = $data['apellidos'];
                $personal->documento = $data['documento'] ?? null;
                $personal->telefono = $data['telefono'] ?? null;
                $personal->fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
                $personal->genero = $data['genero'] ?? null;
                $personal->ubicacion = $data['ubicacion'] ?? null;

                $personal->save();

                // Actualizar correo del usuario.
                $user->correo = $data['email'];

                $user->save();
            });

        } catch (\Throwable $e) {
            // Si ocurre un error durante la transacción, eliminar la nueva imagen si se guardó.

            if ($imagenNueva) {
                Storage::disk('public')->delete($imagenNueva);
            }

            throw $e;
        }

        // Eliminar la imagen anterior si no hubo error y si el usuario la eliminó o la reemplazó por una nueva.
        $imagenFueEliminada = $request->boolean('eliminar_imagen_perfil');
        $imagenFueReemplazada = !is_null($imagenNueva);

        if ($imagenAnterior && ($imagenFueEliminada || $imagenFueReemplazada)) 
        {
            Storage::disk('public')->delete($imagenAnterior);
        }

        return Redirect::route('profile.edit')->with('success', 'Perfil actualizado correctamente.');
    }

    // Enviar enlace para cambiar la contraseña del usuario autenticado.
    public function sendPasswordLink(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->eliminado || !$user->estado_activo) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Tu cuenta no se encuentra disponible.'
                );
        }

        $intent = is_null($user->password_hash)
            ? 'set'
            : 'change';

        // Generamos un token utilizando el mismo broker del sistema de recuperación de contraseña.
        $token = Password::broker()->createToken($user);

        // La intención queda protegida mediante una URL firmada.
        $url = URL::temporarySignedRoute(
            'profile.password.reset',
            now()->addMinutes(
                config('auth.passwords.users.expire')
            ),
            [
                'token' => $token,
                'email' => $user->correo,
                'intent' => $intent,
            ]
        );

        if ($intent === 'set') {
            $user->notify(
                new SetPasswordNotification($url)
            );
        } else {
            $user->notify(
                new ChangePasswordNotification($url)
            );
        }

        return Redirect::route('profile.edit')
            ->with(
                'success',
                $intent === 'set'
                    ? 'Te enviamos un enlace a tu correo electrónico para establecer tu contraseña de SIDAN.'
                    : 'Te enviamos un enlace a tu correo electrónico para cambiar tu contraseña.'
            );
    }

    // Eliminar la cuenta del usuario
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', ['password' => ['required', 'current_password'],]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    // Desvincular la cuenta de Google del usuario autenticado.
    public function unlinkGoogle(Request $request): RedirectResponse
    {
        $user = $request->user();

        // No hay ninguna cuenta de Google vinculada.
        if (!$user->googleAccount()->exists()) {
            return Redirect::route('profile.edit')->with('error', 'No tienes una cuenta de Google vinculada.');
        }

        // Nunca permitir que el usuario se quede sin método de acceso.
        if (is_null($user->password_hash)) {
            return Redirect::route('profile.edit')->with('error', 'Debes establecer una contraseña de SIDAN antes de desvincular tu cuenta de Google.');
        }

        $user->googleAccount()->delete();

        return Redirect::route('profile.edit')->with('success', 'Cuenta de Google desvinculada correctamente. Ahora puedes iniciar sesión usando tu correo y contraseña de SIDAN.');
    }
}