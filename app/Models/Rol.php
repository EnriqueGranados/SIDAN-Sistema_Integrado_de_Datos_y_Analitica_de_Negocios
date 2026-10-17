<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'tbl_roles';

    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre',
        'descripcion',
        'modulos',
    ];

    protected function casts(): array
    {
        return [
            'modulos' => 'array',
        ];
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_rol', 'id_rol');
    }

    public function esBase(): bool
    {
        return in_array(
            $this->nombre,
            ['superadmin', 'admin', 'usuario'],
            true
        );
    }
}