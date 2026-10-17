<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_abonos_transferencia', function (Blueprint $table) {
            $table->id('id_abono');
            $table->foreignId('id_pago')
                ->constrained('tbl_pagos', 'id_pago')
                ->cascadeOnDelete();
            $table->string('comprobante_path');
            $table->decimal('monto_recibido', 12, 2)->nullable();
            $table->string('estado', 30)->default('pendiente');
            $table->text('observacion_revision')->nullable();
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->timestampTz('revisado_en')->nullable();
            $table->timestampsTz();
            $table->index(['id_pago', 'estado']);
        });

        // No se permiten dos comprobantes pendientes para una misma transferencia.
        DB::statement("CREATE UNIQUE INDEX uq_abono_transferencia_pendiente
            ON tbl_abonos_transferencia (id_pago) WHERE estado = 'pendiente'");

        // Mantener los comprobantes anteriores a esta migración.
        DB::table('tbl_pagos')
            ->where('proveedor', 'transferencia')
            ->whereNotNull('comprobante_path')
            ->orderBy('id_pago')
            ->chunkById(100, function ($pagos) {
                foreach ($pagos as $pago) {
                    DB::table('tbl_abonos_transferencia')->insert([
                        'id_pago' => $pago->id_pago,
                        'comprobante_path' => $pago->comprobante_path,
                        'monto_recibido' => $pago->estado === 'confirmado' ? $pago->monto : null,
                        'estado' => match ($pago->estado) {
                            'confirmado' => 'confirmado',
                            'rechazado' => 'rechazado',
                            default => 'pendiente',
                        },
                        'observacion_revision' => $pago->observacion_revision,
                        'revisado_por' => $pago->revisado_por,
                        'revisado_en' => $pago->revisado_en,
                        'created_at' => $pago->created_at ?? now(),
                        'updated_at' => $pago->updated_at ?? now(),
                    ]);
                }
            }, 'id_pago');
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_abonos_transferencia');
    }
};
