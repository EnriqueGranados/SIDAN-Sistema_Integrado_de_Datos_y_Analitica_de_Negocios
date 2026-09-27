<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /*
        Run the migrations.
    */
    public function up(): void
    {
        Schema::create('tbl_medios_actividad', function (Blueprint $table) {
            $table->id('id_medio');

            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->cascadeOnDelete();

            $table->string('tipo', 20)->default('imagen');
            $table->string('url', 500);
            $table->string('texto_alternativo', 255)->nullable();

            $table->boolean('es_portada')->default(false);
            $table->smallInteger('orden')->default(0);

            $table->timestamps();

            $table->index(['id_actividad', 'es_portada']);
            $table->index(['id_actividad', 'orden']);
        });

        DB::statement("
            CREATE UNIQUE INDEX uq_medios_actividad_portada
            ON tbl_medios_actividad (id_actividad)
            WHERE es_portada = TRUE
        ");
    }

    /*
        Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('tbl_medios_actividad');
    }
};