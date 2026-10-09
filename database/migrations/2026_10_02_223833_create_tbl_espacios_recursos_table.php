<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_espacios_recursos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_espacio');
            $table->unsignedBigInteger('id_recurso');

            $table->unsignedInteger('cantidad')->default(1);
            $table->string('observacion', 300)->nullable();

            $table->timestamps();

            $table->primary([
                'id_espacio',
                'id_recurso',
            ]);

            $table->foreign('id_espacio')
                ->references('id_espacio')
                ->on('tbl_espacios')
                ->cascadeOnDelete();

            $table->foreign('id_recurso')
                ->references('id_recurso')
                ->on('tbl_recursos')
                ->restrictOnDelete();

            $table->index('id_recurso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_espacios_recursos');
    }
};