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
use Illuminate\View\View;

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

        DB::transaction(function () use ($user, $data, $request) {

            // Actualizar imagen de perfil si se proporciona una nueva
            if ($request->hasFile('imagen_perfil')) {

                // Eliminar imagen anterior si existe
                if ($user->imagen_perfil) {
                    Storage::disk('public')->delete($user->imagen_perfil);
                }

                // Guardar nueva imagen
                $user->imagen_perfil = $request->file('imagen_perfil')->store('perfiles', 'public');
            }

            // Actualizar información personal
            $personal = $user->informacion_personal;

            $personal->nombres = $data['nombres'];
            $personal->apellidos = $data['apellidos'];
            $personal->documento = $data['documento'] ?? null;
            $personal->telefono = $data['telefono'] ?? null;
            $personal->fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
            $personal->genero = $data['genero'] ?? null;
            $personal->ubicacion = $data['ubicacion'] ?? null;

            $personal->save();

            // Actualizar correo del usuario
            $user->correo = $data['email'];

            // Actualizar contraseña si se proporciona una nueva
            if (!empty($data['password'])) {
                $user->password_hash = Hash::make($data['password']);
            }

            $user->save();
        });

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated');
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
}