<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_observaciones_revision_actividad', function (Blueprint $table) {
            $table->bigIncrements('id_observacion');
            $table->unsignedBigInteger('id_revision');
            $table->string('seccion', 40);
            $table->string('referencia_tipo', 40)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->text('observacion');
            $table->timestamps();

            $table->foreign('id_revision')
                ->references('id_revision')
                ->on('tbl_revisiones_actividad')
                ->cascadeOnDelete();

            $table->index(['id_revision', 'seccion']);
            $table->index(['referencia_tipo', 'referencia_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_observaciones_revision_actividad');
    }
};