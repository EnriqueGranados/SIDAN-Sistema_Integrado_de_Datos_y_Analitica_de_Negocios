<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_pagos', function (Blueprint $table) {
            $table->string('comprobante_path')->nullable();
            $table->unsignedBigInteger('revisado_por')->nullable();
            $table->timestampTz('revisado_en')->nullable();
            $table->text('observacion_revision')->nullable();
        });

        Schema::table('tbl_detalles_orden', function (Blueprint $table) {
            $table->decimal('descuento', 12, 2)->default(0);
            $table->unsignedInteger('unidades_descuento')->default(0);
        });

        Schema::create('tbl_usos_promociones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_promocion')
                ->constrained('tbl_promociones', 'id_promocion')
                ->restrictOnDelete();

            $table->foreignId('id_orden')
                ->unique()
                ->constrained('tbl_ordenes', 'id_orden')
                ->cascadeOnDelete();

            $table->foreignId('id_usuario')
                ->constrained('tbl_usuarios', 'id_usuario')
                ->restrictOnDelete();

            $table->unsignedInteger('unidades');
            $table->string('codigo', 60);
            $table->decimal('descuento', 12, 2);
            $table->timestampsTz();

            $table->index(['id_promocion', 'id_usuario']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_usos_promociones');

        Schema::table('tbl_detalles_orden', function (Blueprint $table) {
            $table->dropColumn(['descuento', 'unidades_descuento']);
        });

        Schema::table('tbl_pagos', function (Blueprint $table) {
            $table->dropColumn([
                'comprobante_path',
                'revisado_por',
                'revisado_en',
                'observacion_revision',
            ]);
        });
    }
};