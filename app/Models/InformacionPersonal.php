<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformacionPersonal extends Model
{
    protected $table = 'tbl_informacion_personal';

    protected $primaryKey = 'id_informacion_personal';

    protected $fillable = [
        'nombres',
        'apellidos',
        'documento',
        'telefono',
        'fecha_nacimiento',
        'genero',
    ];

    public function usuario()
    {
        return $this->hasOne(User::class, 'id_informacion_personal', 'id_informacion_personal');
    }
}