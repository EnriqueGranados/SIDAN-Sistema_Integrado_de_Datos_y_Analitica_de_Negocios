<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_recursos', function (Blueprint $table) {
            $table->bigIncrements('id_recurso');

            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();

            $table->string('categoria', 80)->nullable();
            $table->string('unidad_medida', 40)->default('unidad');

            $table->boolean('es_movil')->default(true);
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique('nombre');
            $table->index('categoria');
            $table->index('activo');
            $table->index('es_movil');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_recursos');
    }
};