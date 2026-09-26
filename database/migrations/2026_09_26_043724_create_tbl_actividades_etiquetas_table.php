<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
        Run the migrations.
    */
    public function up(): void
    {
        Schema::create('tbl_actividades_etiquetas', function (Blueprint $table) {
            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->cascadeOnDelete();

            $table->foreignId('id_etiqueta')
                ->constrained('tbl_etiquetas', 'id_etiqueta')
                ->cascadeOnDelete();

            $table->primary(
                ['id_actividad', 'id_etiqueta'],
                'pk_actividades_etiquetas'
            );
        });
    }

    /*
        Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('tbl_actividades_etiquetas');
    }
};