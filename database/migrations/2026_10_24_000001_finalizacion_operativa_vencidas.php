<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_ordenes', function (Blueprint $t) {
            $t->timestampTz('finalizada_en')->nullable();
            $t->unsignedBigInteger('finalizada_por')->nullable();
            $t->string('motivo_finalizacion', 500)->nullable();
        });
    }
    public function down(): void
    {
        // No borrar datos de cierre operativo de órdenes.
        throw new RuntimeException('No se revierte automáticamente el historial de finalización.');
    }
};
