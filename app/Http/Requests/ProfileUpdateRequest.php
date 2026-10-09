<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Reglas de validación para la actualización del perfil
    public function rules(): array
    {
        $user = $this->user();
        $personal = $user->informacion_personal;

        // Regla para documento
        // Ignoramos el documento del usuario actual para que pueda guardar el formulario sin que la validación unique lo rechace
        $documentoRule = Rule::unique($personal->getTable(),'documento')->ignore($personal->getKey(), $personal->getKeyName());

        // Regla para teléfono
        // Ignoramos el teléfono del usuario actual para que pueda guardar el formulario sin que la validación unique lo rechace
        $telefonoRule = Rule::unique($personal->getTable(), 'telefono')->ignore($personal->getKey(), $personal->getKeyName());

        // Regla para correo
        // Ignoramos el correo del usuario actual para que pueda guardar el formulario sin que la validación unique lo rechace
        $correoRule = Rule::unique($user->getTable(), 'correo')->ignore($user->getKey(), $user->getKeyName());

        return [
            // Imagen de perfil
            'imagen_perfil' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120',],

            'eliminar_imagen_perfil' => ['nullable', 'boolean',],

            // Información personal
            'nombres' => ['required', 'string', 'max:100',],
            'apellidos' => ['required', 'string', 'max:100',],
            'documento' => ['nullable', 'string', 'regex:/^[0-9]{8}-[0-9]$/', $documentoRule,],
            'telefono' => ['nullable', 'string', 'regex:/^\+[1-9][0-9]{6,14}$/', $telefonoRule,],
            'fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d'),],
            'genero' => ['nullable', 'string', 'max:10',],
            'ubicacion' => ['nullable', 'string', 'max:255',],

            // Cuenta de usuario
            'email' => ['required', 'string', 'lowercase', 'email', 'max:150', $correoRule,],
        ];
    }

    // Validaciones adicionales después de las reglas principales.
    public function after(): array
    {
        return [
            function ($validator) {
                $user = $this->user();

                $correoActual = strtolower(trim($user->correo));
                $correoNuevo = strtolower(
                    trim((string) $this->input('email'))
                );

                $correoCambio = $correoActual !== $correoNuevo;

                if (
                    $correoCambio &&
                    $user->googleAccount()->exists()
                ) {
                    $validator->errors()->add(
                        'email',
                        'Debes desvincular tu cuenta de Google antes de cambiar tu correo electrónico.'
                    );
                }
            },
        ];
    }
}