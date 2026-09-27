<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'tbl_usuarios';

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'id_informacion_personal',
        'id_rol',
        'correo',
        'password_hash',
        'must_change_password',
        'estado_activo',
        'eliminado',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'must_change_password' => 'boolean'
        ];
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
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