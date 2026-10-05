<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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

        // Reglas de validación para la contraseña actual y la nueva contraseña
        // Solo se exige si el usuario ya tiene una contraseña. Una cuenta de Google sin password_hash puede establecer una contraseña directamente
        $currentPasswordRules = ['nullable'];
        $passwordRules = ['nullable', 'confirmed', Password::defaults(),];

        if (filled($user->password_hash)) {
            // Usuario que ya tiene contraseña.
            // Si llena cualquiera de los 3 campos, los 3 son obligatorios
            $currentPasswordRules = ['nullable', 'required_with:password,password_confirmation', 'current_password',];

            $passwordRules = ['nullable', 'required_with:current_password,password_confirmation', 'confirmed', Password::defaults(),];

            $passwordConfirmationRules = ['nullable','required_with:current_password,password',];
        } else {
            // Usuario de Google sin contraseña.
            // Si llena nueva o confirmación, ambas son obligatorias
            $currentPasswordRules = ['nullable',];

            $passwordRules = ['nullable', 'required_with:password_confirmation', 'confirmed', Password::defaults(),];

            $passwordConfirmationRules = ['nullable', 'required_with:password',];
        }

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

            // Seguridad de la contraseña
            'current_password' => $currentPasswordRules,
            'password' => $passwordRules,
            'password_confirmation' => $passwordConfirmationRules,
        ];
    }

    // Validaciones adicionales después de las reglas principales.
    public function after(): array
    {
        return [
            function ($validator) {
                $user = $this->user();

                // Normalizamos ambos correos antes de compararlos.
                $correoActual = strtolower(trim($user->correo));
                $correoNuevo = strtolower(trim((string) $this->input('email')));

                // Solo nos interesa si realmente está intentando cambiarlo.
                $correoCambio = $correoActual !== $correoNuevo;

                // Tiene una cuenta Google vinculada y no tiene contraseña local.
                $esGoogleOnly =
                    $user->googleAccount()->exists() &&
                    is_null($user->password_hash);

                if ($correoCambio && $esGoogleOnly) {
                    $validator->errors()->add(
                        'email',
                        'Debes establecer una contraseña de SIDAN antes de cambiar tu correo electrónico.'
                    );
                }
            },
        ];
    }

    // Mensajes de error personalizados para la validación de contraseña
    public function messages(): array
    {
        return [
            'current_password.required_with' => 'La contraseña actual es obligatoria para cambiar la contraseña.',

            'current_password.current_password' => 'La contraseña actual no es correcta.',

            'password.required_with' => 'La nueva contraseña es obligatoria.',

            'password.confirmed' => 'La confirmación de la contraseña no coincide.',

            'password_confirmation.required_with' => 'Debes confirmar la nueva contraseña.',
        ];
    }
}