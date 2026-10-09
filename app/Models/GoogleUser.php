<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleUser extends Model
{
    protected $table = 'tbl_google_usuarios';

    protected $primaryKey = 'id_google_usuario';

    protected $fillable = [
        'id_usuario',
        'google_id',
        'correo_google',
    ];

    //Usuario de SIDAN al que pertenece esta cuenta de Google.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}