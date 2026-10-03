<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('tbl_recursos_actividad');

        Schema::create('tbl_recursos_actividad', function (Blueprint $table) {
            $table->bigIncrements('id_recurso_actividad');

            $table->unsignedBigInteger('id_actividad');
            $table->unsignedBigInteger('id_sesion')->nullable();
            $table->unsignedBigInteger('id_recurso');

            $table->unsignedInteger('cantidad')->default(1);
            $table->string('observacion', 300)->nullable();

            $table->timestamps();

            $table->foreign('id_actividad')
                ->references('id_actividad')
                ->on('tbl_actividades')
                ->restrictOnDelete();

            $table->foreign('id_sesion')
                ->references('id_sesion')
                ->on('tbl_sesiones_actividad')
                ->cascadeOnDelete();

            $table->foreign('id_recurso')
                ->references('id_recurso')
                ->on('tbl_recursos')
                ->restrictOnDelete();

            $table->unique(
                ['id_actividad', 'id_sesion', 'id_recurso'],
                'uq_recurso_actividad_sesion'
            );

            $table->index('id_actividad');
            $table->index('id_sesion');
            $table->index('id_recurso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_recursos_actividad');

        Schema::create('tbl_recursos_actividad', function (Blueprint $table) {
            $table->id('id_recurso');

            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->restrictOnDelete();

            $table->foreignId('id_sesion')
                ->nullable()
                ->constrained('tbl_sesiones_actividad', 'id_sesion')
                ->restrictOnDelete();

            $table->string('tipo', 50);
            $table->string('nombre', 120);
            $table->integer('capacidad')->nullable();
            $table->string('estado', 20)->default('activo');
            $table->string('observacion', 300)->nullable();

            $table->index(
                ['id_actividad', 'estado'],
                'idx_recursos_actividad_estado'
            );

            $table->index(
                'id_sesion',
                'idx_recursos_actividad_sesion'
            );
        });
    }
};