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
        Schema::create('tbl_formularios_actividad', function (Blueprint $table) {
            $table->id('id_formulario');

            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->cascadeOnDelete();

            $table->integer('version')->default(1);
            $table->string('nombre', 120)->default('Formulario principal');
            $table->string('estado', 20)->default('borrador');

            $table->foreignId('creado_por')
                ->constrained('usuarios', 'id_usuario')
                ->restrictOnDelete();

            $table->timestampTz('creado_en')->useCurrent();
            $table->timestampTz('publicado_en')->nullable();

            $table->unique(
                ['id_actividad', 'version'],
                'uq_formulario_actividad_version'
            );
        });

        DB::statement("
            ALTER TABLE tbl_formularios_actividad
            ADD CONSTRAINT chk_formularios_actividad_version
            CHECK (version > 0)
        ");

        DB::statement("
            ALTER TABLE tbl_formularios_actividad
            ADD CONSTRAINT chk_formularios_actividad_estado
            CHECK (estado IN ('borrador', 'publicado', 'retirado'))
        ");
    }

    /*
        Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('tbl_formularios_actividad');
    }
};