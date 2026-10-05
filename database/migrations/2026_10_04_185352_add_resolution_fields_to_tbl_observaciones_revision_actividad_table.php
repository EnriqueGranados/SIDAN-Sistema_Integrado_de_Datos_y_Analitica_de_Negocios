<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_observaciones_revision_actividad', function (Blueprint $table) {
            $table->boolean('resuelta')->default(false);
            $table->unsignedBigInteger('resuelta_por')->nullable();
            $table->unsignedBigInteger('id_revision_resuelta')->nullable();
            $table->timestamp('resuelta_en')->nullable();

            $table->foreign('resuelta_por')
                ->references('id_usuario')
                ->on('tbl_usuarios')
                ->nullOnDelete();

            $table->foreign('id_revision_resuelta')
                ->references('id_revision')
                ->on('tbl_revisiones_actividad')
                ->nullOnDelete();

            $table->index(['resuelta', 'id_revision']);
        });
    }

    public function down(): void
    {
        Schema::table('tbl_observaciones_revision_actividad', function (Blueprint $table) {
            $table->dropForeign(['resuelta_por']);
            $table->dropForeign(['id_revision_resuelta']);
            $table->dropIndex(['resuelta', 'id_revision']);

            $table->dropColumn([
                'resuelta',
                'resuelta_por',
                'id_revision_resuelta',
                'resuelta_en',
            ]);
        });
    }
};