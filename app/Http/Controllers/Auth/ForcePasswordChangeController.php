<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChangeController extends Controller
{
    public function create(Request $request)
    {
        if (!$request->user()->must_change_password) {
            return redirect()->route('welcome');
        }

        return view('auth.force-password-change');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->must_change_password) {
            return redirect()->route('welcome');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                Password::defaults(),
            ],
        ], [
            'current_password.required' => 'Ingresa tu contraseña temporal.',
            'password.required' => 'Ingresa una nueva contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.different' => 'La nueva contraseña debe ser diferente de la temporal.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            return back()->withErrors([
                'current_password' => 'La contraseña temporal es incorrecta.',
            ]);
        }

        $user->password_hash = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->remember_token = \Illuminate\Support\Str::random(60);
        $user->save();

        $request->session()->regenerate();

        return redirect()
            ->route('welcome')
            ->with('success', 'Tu contraseña se actualizó correctamente. Bienvenido a SIDAN.');
    }
}