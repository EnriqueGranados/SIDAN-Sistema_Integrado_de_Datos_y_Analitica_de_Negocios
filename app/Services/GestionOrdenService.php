<?php

namespace App\Services;

use App\Models\Actividad;
use App\Models\Orden;
use App\Models\Pago;
use App\Models\Promocion;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GestionOrdenService
{
    public function error(string $mensaje, string $campo = 'orden'): never
    {
        throw ValidationException::withMessages([$campo => $mensaje]);
    }

    public function centavos(mixed $valor): int
    {
        return (int) round((float) $valor * 100);
    }

    public function dinero(int $centavos): string
    {
        return number_format($centavos / 100, 2, '.', '');
    }

    public function ejecutar(Orden $referencia, Closure $accion): mixed
    {
        return DB::transaction(function () use ($referencia, $accion) {
            Actividad::whereKey($referencia->id_actividad)
                ->lockForUpdate()
                ->firstOrFail();

            $orden = Orden::whereKey($referencia->id_orden)
                ->lockForUpdate()
                ->firstOrFail();

            $orden->load(['actividad', 'inscripcion', 'detalles', 'pago.intentos']);

            return $accion($orden);
        }, 3);
    }

    public function validarPendiente(Orden $orden): void
    {
        if (
            $orden->estado !== 'pendiente_pago'
            || ($orden->expira_en && !$orden->expira_en->isFuture())
        ) {
            $this->error('Esta orden ya no está disponible para realizar esta operación.');
        }

        if ($orden->tipo_orden === 'inscripcion') {
            if (!$orden->inscripcion || $orden->inscripcion->estado !== 'pendiente_pago') {
                $this->error('La inscripción ya no está pendiente de pago.');
            }
        } elseif ($orden->tipo_orden === 'productos') {
            if (
                $orden->actividad->requiereInscripcionGeneral()
                && (
                    !$orden->inscripcion
                    || !in_array(
                        $orden->inscripcion->estado,
                        ['confirmada', 'confirmada_prueba'],
                        true
                    )
                )
            ) {
                $this->error('Necesitás una inscripción confirmada para comprar estos productos.');
            }
        } else {
            $this->error('El tipo de orden no es válido.');
        }
    }

    public function validarSinPago(Orden $orden): void
    {
        $this->validarPendiente($orden);

        if ($orden->pago()->exists()) {
            $this->error(
                'Esta orden ya inició un pago. No se puede cambiar su importe ni su método.'
            );
        }
    }

    public function admiteCodigo(Orden $orden): bool
    {
        if ($orden->tipo_orden !== 'productos' || !$orden->id_usuario) {
            return false;
        }

        $ids = $orden->detalles
            ->pluck('id_item_actividad')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!$ids) {
            return false;
        }

        return Promocion::query()
            ->where('id_actividad', $orden->id_actividad)
            ->where('activo', true)
            ->whereNotNull('codigo')
            ->where('codigo', '<>', '')
            ->where(function ($query) use ($ids) {
                $query->whereDoesntHave('items')
                    ->orWhereHas('items', function ($items) use ($ids) {
                        $items->whereIn(
                            'tbl_items_actividad.id_item_actividad',
                            $ids
                        );
                    });
            })
            ->exists();
    }

    public function aplicarCodigo(Orden $orden, string $codigo): void
    {
        $this->validarSinPago($orden);

        if ($orden->tipo_orden !== 'productos' || !$orden->id_usuario) {
            $this->error('Esta orden no admite códigos de productos.', 'codigo');
        }

        $promo = Promocion::query()
            ->where('id_actividad', $orden->id_actividad)
            ->whereRaw('LOWER(codigo) = ?', [mb_strtolower(trim($codigo))])
            ->lockForUpdate()
            ->first();

        if (!$promo) {
            $this->error('El código no existe para esta actividad.', 'codigo');
        }

        if (!$promo->activo) {
            $this->error('El código está desactivado.', 'codigo');
        }

        if ($promo->vigente_desde && now()->lt($promo->vigente_desde)) {
            $this->error('El código todavía no está vigente.', 'codigo');
        }

        if ($promo->vigente_hasta && now()->gt($promo->vigente_hasta)) {
            $this->error('El código promocional ya expiró.', 'codigo');
        }

        $ids = $promo->items()
            ->pluck('tbl_items_actividad.id_item_actividad')
            ->map(fn($id) => (int) $id)
            ->all();

        $elegibles = $orden->detalles
            ->filter(function ($detalle) use ($ids) {
                return $detalle->id_item_actividad
                    && (!$ids || in_array((int) $detalle->id_item_actividad, $ids, true))
                    && $this->centavos($detalle->precio_unitario) > 0;
            })
            ->sortByDesc(fn($detalle) => $this->centavos($detalle->precio_unitario));

        if ($elegibles->isEmpty()) {
            $this->error('El código no aplica a ninguno de los productos de esta orden.', 'codigo');
        }

        $subtotal = $orden->detalles->sum(
            fn($detalle) => $this->centavos($detalle->subtotal)
        );

        if ($subtotal < $this->centavos($promo->monto_minimo ?? 0)) {
            $this->error(
                'El código requiere una compra mínima de $'
                . number_format((float) $promo->monto_minimo, 2) . '.',
                'codigo'
            );
        }

        $usos = DB::table('tbl_usos_promociones as uso')
            ->join('tbl_ordenes as orden', 'orden.id_orden', '=', 'uso.id_orden')
            ->where('uso.id_promocion', $promo->id_promocion)
            ->where('uso.id_orden', '<>', $orden->id_orden)
            ->where(function ($query) {
                $query->whereIn('orden.estado', [
                    'pagada',
                    'pagada_prueba',
                    'revision_cupo',
                ])->orWhere(function ($pendiente) {
                    $pendiente->where('orden.estado', 'pendiente_pago')
                        ->where(function ($vigente) {
                            $vigente->whereNull('orden.expira_en')
                                ->orWhere('orden.expira_en', '>', now());
                        });
                })->orWhereExists(function ($intentos) {
                    $intentos->selectRaw('1')
                        ->from('tbl_pagos as pago')
                        ->join(
                            'tbl_intentos_pago_wompi as intento',
                            'intento.id_pago',
                            '=',
                            'pago.id_pago'
                        )
                        ->whereColumn('pago.id_orden', 'orden.id_orden');
                });
            });

        $global = (int) (clone $usos)->sum('uso.unidades');
        $personal = (int) (clone $usos)
            ->where('uso.id_usuario', $orden->id_usuario)
            ->sum('uso.unidades');

        $disponiblesGlobales = $promo->limite_usos === null
            ? PHP_INT_MAX
            : max(0, $promo->limite_usos - $global);

        $disponiblesPersona = $promo->limite_por_persona === null
            ? PHP_INT_MAX
            : max(0, $promo->limite_por_persona - $personal);

        if ($disponiblesGlobales < 1) {
            $this->error('El código agotó sus usos disponibles.', 'codigo');
        }

        if ($disponiblesPersona < 1) {
            $this->error(
                'Ya utilizaste o reservaste el máximo de usos de este código.',
                'codigo'
            );
        }

        $restantes = min($disponiblesGlobales, $disponiblesPersona);
        $descuentoTotal = 0;
        $unidadesTotal = 0;

        $orden->detalles()->update([
            'descuento' => 0,
            'unidades_descuento' => 0,
        ]);

        foreach ($elegibles as $detalle) {
            if ($restantes < 1) {
                break;
            }

            $precio = $this->centavos($detalle->precio_unitario);

            $descuentoUnidad = $promo->tipo_descuento === 'porcentaje'
                ? intdiv($precio * $this->centavos($promo->valor) + 5000, 10000)
                : min($precio, $this->centavos($promo->valor));

            $descuentoUnidad = min($precio, $descuentoUnidad);

            if ($descuentoUnidad < 1) {
                continue;
            }

            $unidades = min((int) $detalle->cantidad, $restantes);
            $descuento = $descuentoUnidad * $unidades;

            $detalle->update([
                'descuento' => $this->dinero($descuento),
                'unidades_descuento' => $unidades,
            ]);

            $restantes -= $unidades;
            $unidadesTotal += $unidades;
            $descuentoTotal += $descuento;
        }

        if ($descuentoTotal < 1) {
            $this->error('El código no genera un descuento para esta compra.', 'codigo');
        }

        DB::table('tbl_usos_promociones')->updateOrInsert(
            ['id_orden' => $orden->id_orden],
            [
                'id_promocion' => $promo->id_promocion,
                'id_usuario' => $orden->id_usuario,
                'codigo' => $promo->codigo,
                'unidades' => $unidadesTotal,
                'descuento' => $this->dinero($descuentoTotal),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $orden->update([
            'subtotal' => $this->dinero($subtotal),
            'descuento_total' => $this->dinero($descuentoTotal),
            'total' => $this->dinero($subtotal - $descuentoTotal),
        ]);
    }

    public function registrarManual(
        Orden $orden,
        string $metodo,
        ?string $comprobante
    ): void {
        $this->validarSinPago($orden);

        if ($this->centavos($orden->total) <= 0) {
            $this->error('Esta orden no requiere pago. Usá el botón de confirmar.');
        }

        Pago::create([
            'id_orden' => $orden->id_orden,
            'proveedor' => $metodo,
            'monto' => $orden->total,
            'estado' => 'pendiente',
            'comprobante_path' => $comprobante,
        ]);

        $orden->update(['expira_en' => null]);

        if ($orden->tipo_orden === 'inscripcion') {
            $orden->inscripcion->update(['expira_en' => null]);
        }
    }

    public function confirmar(Orden $orden, Pago $pago, ?int $administrador): void
    {
        $this->validarPendiente($orden);

        if ($this->centavos($pago->monto) !== $this->centavos($orden->total)) {
            $this->error('El monto del pago no coincide con el total de la orden.');
        }

        if ($pago->estado !== 'pendiente') {
            $this->error('Este pago ya fue procesado.');
        }

        $pago->update([
            'estado' => 'confirmado',
            'fecha_confirmacion' => now(),
            'revisado_por' => $administrador,
            'revisado_en' => now(),
        ]);

        $orden->update(['estado' => 'pagada']);

        if ($orden->tipo_orden === 'inscripcion') {
            $orden->inscripcion->update([
                'estado' => 'confirmada',
                'fecha_confirmacion' => now(),
                'expira_en' => null,
            ]);
        }

        DB::table('tbl_comprobantes')->insert([
            'id_pago' => $pago->id_pago,
            'numero' => 'SIDAN-REC-' . $pago->id_pago,
            'tipo' => 'recibo_interno',
            'monto' => $pago->monto,
            'fecha_emision' => now(),
            'es_productiva' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function puedeCancelar(Orden $orden): bool
    {
        if (!in_array($orden->estado, ['pendiente_pago', 'vencida'], true)) {
            return false;
        }

        $pago = $orden->pago;

        // El efectivo queda reservado y solo administración puede decidir su cierre.
        // Tampoco borrar pagos en efectivo rechazados: conservar el historial.
        if ($pago?->proveedor === 'efectivo') {
            return false;
        }

        return !$pago || (
            in_array($pago->estado, ['pendiente', 'rechazado'], true)
            && !$pago->intentos()->exists()
            && !DB::table('tbl_comprobantes')
                ->where('id_pago', $pago->id_pago)
                ->exists()
        );
    }

    public function cancelar(Orden $orden): ?string
    {
        if (!$this->puedeCancelar($orden)) {
            $this->error(
                'No podés cancelar esta orden: el efectivo debe revisarlo administración o existe un pago que necesita revisión.'
            );
        }

        $archivo = $orden->pago?->comprobante_path;

        if ($orden->tipo_orden === 'inscripcion' && $orden->inscripcion) {
            if (
                !in_array(
                    $orden->inscripcion->estado,
                    ['pendiente_pago', 'vencida', 'cancelada'],
                    true
                )
            ) {
                $this->error('La inscripción asociada ya no se puede cancelar.');
            }

            $orden->inscripcion->update([
                'estado' => 'cancelada',
                'expira_en' => now(),
            ]);
        }

        $orden->pago?->delete();
        $orden->delete();

        return $archivo;
    }
}
