<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponsableActividad extends Model
{
    use HasFactory;

    protected $table = 'tbl_responsables_actividad';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'id_actividad',
        'id_usuario',
        'rol_en_actividad',
    ];

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}