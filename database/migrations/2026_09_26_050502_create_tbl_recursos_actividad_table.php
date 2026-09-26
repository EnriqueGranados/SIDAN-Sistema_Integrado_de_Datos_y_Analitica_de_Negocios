<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        DB::statement("
            ALTER TABLE tbl_recursos_actividad
            ADD CONSTRAINT chk_recursos_actividad_capacidad
            CHECK (
                capacidad IS NULL
                OR capacidad >= 0
            )
        ");

        DB::statement("
            ALTER TABLE tbl_recursos_actividad
            ADD CONSTRAINT chk_recursos_actividad_estado
            CHECK (
                estado IN (
                    'activo',
                    'inactivo',
                    'fuera_servicio'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_recursos_actividad');
    }
};