<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_actividades', function (Blueprint $table) {
            $table->unsignedBigInteger('id_espacio')
                ->nullable()
                ->after('id_categoria');

            $table->string('ubicacion_externa', 300)
                ->nullable()
                ->after('id_espacio');

            $table->foreign('id_espacio')
                ->references('id_espacio')
                ->on('tbl_espacios')
                ->restrictOnDelete();

            $table->index('id_espacio');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_actividades', function (Blueprint $table) {
            $table->dropForeign(['id_espacio']);
            $table->dropIndex(['id_espacio']);
            $table->dropColumn([
                'id_espacio',
                'ubicacion_externa',
            ]);
        });
    }
};