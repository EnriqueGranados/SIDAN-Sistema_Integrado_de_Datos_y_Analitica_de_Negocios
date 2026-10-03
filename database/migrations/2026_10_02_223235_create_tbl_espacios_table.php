<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_espacios', function (Blueprint $table) {
            $table->bigIncrements('id_espacio');

            $table->unsignedBigInteger('id_espacio_contenedor')->nullable();

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();

            $table->string('direccion', 300)->nullable();
            $table->string('indicaciones', 500)->nullable();

            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();

            $table->unsignedInteger('capacidad')->nullable();

            $table->boolean('permite_actividades')->default(true);
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->foreign('id_espacio_contenedor')
                ->references('id_espacio')
                ->on('tbl_espacios')
                ->nullOnDelete();

            $table->index('id_espacio_contenedor');
            $table->index('activo');
            $table->index('permite_actividades');
            $table->index('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_espacios');
    }
};