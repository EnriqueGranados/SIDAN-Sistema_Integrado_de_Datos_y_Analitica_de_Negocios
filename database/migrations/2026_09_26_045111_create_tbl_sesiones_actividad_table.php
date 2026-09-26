<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
        Run the migrations.
    */
    public function up(): void
    {
        Schema::create('tbl_sesiones_actividad', function (Blueprint $table) {
            $table->id('id_sesion');

            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->restrictOnDelete();

            $table->string('nombre', 150);
            $table->timestampTz('fecha_inicio');
            $table->timestampTz('fecha_fin')->nullable();

            $table->string('ubicacion', 250)->nullable();
            $table->text('enlace_acceso')->nullable();

            $table->integer('cupo')->nullable();

            $table->boolean('requiere_reserva')->default(false);
            $table->boolean('obligatoria')->default(false);

            $table->smallInteger('orden')->default(0);
            $table->string('estado', 20)->default('programada');

            $table->index(
                ['id_actividad', 'fecha_inicio'],
                'idx_sesiones_actividad_fecha'
            );
        });

        DB::statement("
            ALTER TABLE tbl_sesiones_actividad
            ADD CONSTRAINT chk_sesiones_actividad_fechas
            CHECK (
                fecha_fin IS NULL
                OR fecha_fin >= fecha_inicio
            )
        ");

        DB::statement("
            ALTER TABLE tbl_sesiones_actividad
            ADD CONSTRAINT chk_sesiones_actividad_cupo
            CHECK (
                cupo IS NULL
                OR cupo >= 0
            )
        ");

        DB::statement("
            ALTER TABLE tbl_sesiones_actividad
            ADD CONSTRAINT chk_sesiones_actividad_orden
            CHECK (orden >= 0)
        ");

        DB::statement("
            ALTER TABLE tbl_sesiones_actividad
            ADD CONSTRAINT chk_sesiones_actividad_estado
            CHECK (
                estado IN (
                    'programada',
                    'cancelada',
                    'finalizada'
                )
            )
        ");
    }

    /*
        Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('tbl_sesiones_actividad');
    }
};