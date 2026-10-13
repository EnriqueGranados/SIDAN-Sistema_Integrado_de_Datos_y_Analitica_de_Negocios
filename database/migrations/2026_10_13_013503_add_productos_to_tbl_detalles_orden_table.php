<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_detalles_orden', function (Blueprint $table) {
            $table->foreignId('id_item_actividad')
                ->nullable()
                ->constrained(
                    'tbl_items_actividad',
                    'id_item_actividad'
                )
                ->restrictOnDelete();

            $table->foreignId('id_variante_item')
                ->nullable()
                ->constrained(
                    'tbl_variantes_item',
                    'id_variante_item'
                )
                ->restrictOnDelete();

            $table->jsonb('configuracion')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_detalles_orden', function (Blueprint $table) {
            $table->dropConstrainedForeignId(
                'id_variante_item'
            );

            $table->dropConstrainedForeignId(
                'id_item_actividad'
            );

            $table->dropColumn(
                'configuracion'
            );
        });
    }
};