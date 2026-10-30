<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'tbl_promociones';
    protected $primaryKey = 'id_promocion';
    public $timestamps = false;

    protected $fillable = [
        'id_actividad',
        'codigo',
        'nombre',
        'tipo_descuento',
        'valor',
        'vigente_desde',
        'vigente_hasta',
        'limite_usos',
        'limite_por_persona',
        'monto_minimo',
        'activo',
        'creado_por',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'vigente_desde' => 'datetime',
            'vigente_hasta' => 'datetime',
            'limite_usos' => 'integer',
            'limite_por_persona' => 'integer',
            'monto_minimo' => 'decimal:2',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'id_actividad', 'id_actividad');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por', 'id_usuario');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            ItemActividad::class,
            'tbl_promociones_items',
            'id_promocion',
            'id_item_actividad'
        );
    }

    public function scopePublicables(Builder $query): Builder
    {
        $ahora = now();

        return $query
            ->where('activo', true)
            ->where(function (Builder $subquery) use ($ahora) {
                $subquery
                    ->whereNull('vigente_hasta')
                    ->orWhere('vigente_hasta', '>=', $ahora);
            });
    }

    public function estaVigente(): bool
    {
        $ahora = now();

        return $this->activo
            && (!$this->vigente_desde || !$ahora->lt($this->vigente_desde))
            && (!$this->vigente_hasta || !$ahora->gt($this->vigente_hasta));
    }

    public function esProxima(): bool
    {
        return $this->activo
            && $this->vigente_desde
            && now()->lt($this->vigente_desde);
    }

    public function textoDescuento(): string
    {
        if ($this->tipo_descuento === 'porcentaje') {
            return rtrim(rtrim(number_format((float) $this->valor, 2), '0'), '.') . '%';
        }

        return '$' . number_format((float) $this->valor, 2);
    }
}
