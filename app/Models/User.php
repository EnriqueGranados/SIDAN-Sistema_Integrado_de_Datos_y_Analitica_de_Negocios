<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements CanResetPasswordContract
{
    use Notifiable, CanResetPasswordTrait;

    protected $table = 'tbl_usuarios';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_informacion_personal',
        'id_rol',
        'correo',
        'password_hash',
        'must_change_password',
        'google_id',
        'estado_activo',
        'eliminado',
        'imagen_perfil',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'must_change_password' => 'boolean',
            'estado_activo' => 'boolean',
            'eliminado' => 'boolean',
        ];
    }

    // Relación uno a uno con la cuenta de Google del usuario.
    public function googleAccount(): HasOne
    {
        return $this->hasOne(GoogleUser::class, 'id_usuario', 'id_usuario');
    }

    // Devuelve la contraseña del usuario para la autenticación.
    public function getAuthPassword(): ?string
    {
        return $this->password_hash;
    }

    // Devuelve el correo electrónico del usuario para la recuperación de contraseña.
    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    // Devuelve la dirección de correo electrónico del usuario para las notificaciones.
    public function routeNotificationForMail($notification): string
    {
        return $this->correo;
    }

    // Envía la notificación de restablecimiento de contraseña al usuario.
    public function sendPasswordResetNotification($token): void
    {
        $url = route('password.reset', [
            'token' => $token,
            'email' => $this->correo,
        ]);

        $this->notify(
            new ResetPasswordNotification($url)
        );
    }

    public function informacion_personal()
    {
        return $this->belongsTo(InformacionPersonal::class, 'id_informacion_personal', 'id_informacion_personal');
    }

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }
}