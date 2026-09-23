<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public $timestamps = false;

    /*Indicar el nombre real de la tabla y la llave primaria */
    protected $table = 'usuarios';
    protected $primaryKey = 'id_usuario';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombres',
        'apellidos',
        'correo',
        'password_hash',
        'rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    /** Campos ocultos (para que no se expongan en JSON/arrays) */
    protected $hidden = [
        'password_hash',
    ];

    /** Laravel necesita saber cuál es el campo de contraseña */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /** Laravel usa 'email' por defecto para buscar, le decimos que use 'correo' */
  
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed', // <-- CAMBIO CRÍTICO: de 'password' a 'password_hash'
        ];
    }
}
