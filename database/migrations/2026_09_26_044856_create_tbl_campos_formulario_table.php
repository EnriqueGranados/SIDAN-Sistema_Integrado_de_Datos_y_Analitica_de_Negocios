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
        Schema::create('tbl_campos_formulario', function (Blueprint $table) {
            $table->id('id_campo');

            $table->foreignId('id_formulario')
                ->constrained('tbl_formularios_actividad', 'id_formulario')
                ->restrictOnDelete();

            $table->string('clave', 80);
            $table->string('nombre', 120);
            $table->string('tipo_dato', 30);

            $table->boolean('obligatorio')->default(false);
            $table->string('aplica_a', 25)->default('inscripcion');
            $table->smallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);

            $table->unique(
                ['id_formulario', 'clave'],
                'uq_campos_formulario_clave'
            );

            $table->index(['id_formulario', 'orden']);
        });

        DB::statement("
            ALTER TABLE tbl_campos_formulario
            ADD CONSTRAINT chk_campos_formulario_tipo_dato
            CHECK (tipo_dato IN (
                'texto',
                'numero',
                'fecha',
                'booleano',
                'opcion',
                'multiseleccion',
                'archivo'
            ))
        ");

        DB::statement("
            ALTER TABLE tbl_campos_formulario
            ADD CONSTRAINT chk_campos_formulario_aplica_a
            CHECK (aplica_a IN (
                'inscripcion',
                'detalle',
                'participante'
            ))
        ");

        DB::statement("
            ALTER TABLE tbl_campos_formulario
            ADD CONSTRAINT chk_campos_formulario_orden
            CHECK (orden >= 0)
        ");
    }

    /*
        Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('tbl_campos_formulario');
    }
};