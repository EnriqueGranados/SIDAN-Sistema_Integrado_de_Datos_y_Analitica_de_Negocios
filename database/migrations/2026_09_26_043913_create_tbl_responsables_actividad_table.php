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
        Schema::create('tbl_responsables_actividad', function (Blueprint $table) {
            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->cascadeOnDelete();

            $table->foreignId('id_usuario')
                ->constrained('usuarios', 'id_usuario')
                ->restrictOnDelete();

            $table->string('rol_en_actividad', 50)->default('responsable');
            $table->timestamps();

            $table->primary(
                ['id_actividad', 'id_usuario'],
                'pk_responsables_actividad'
            );

            $table->index('id_usuario');
        });
    }

    /*
        Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('tbl_responsables_actividad');
    }
};