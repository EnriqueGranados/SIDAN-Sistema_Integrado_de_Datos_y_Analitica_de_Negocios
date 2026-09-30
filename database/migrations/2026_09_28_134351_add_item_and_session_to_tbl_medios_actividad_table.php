<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_medios_actividad', function (Blueprint $table) {
            $table->bigInteger('id_item_actividad')->nullable();
            $table->bigInteger('id_sesion')->nullable();

            $table->foreign('id_item_actividad')
                ->references('id_item_actividad')
                ->on('tbl_items_actividad')
                ->restrictOnDelete();

            $table->foreign('id_sesion')
                ->references('id_sesion')
                ->on('tbl_sesiones_actividad')
                ->restrictOnDelete();

            $table->index(
                ['id_item_actividad', 'es_portada'],
                'idx_medios_item_portada'
            );

            $table->index(
                ['id_sesion', 'es_portada'],
                'idx_medios_sesion_portada'
            );
        });

        DB::statement("
            ALTER TABLE tbl_medios_actividad
            ADD CONSTRAINT chk_medio_un_solo_propietario
            CHECK (
                NOT (
                    id_item_actividad IS NOT NULL
                    AND id_sesion IS NOT NULL
                )
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE tbl_medios_actividad
            DROP CONSTRAINT IF EXISTS chk_medio_un_solo_propietario
        ");

        Schema::table('tbl_medios_actividad', function (Blueprint $table) {
            $table->dropIndex('idx_medios_item_portada');
            $table->dropIndex('idx_medios_sesion_portada');

            $table->dropForeign(['id_item_actividad']);
            $table->dropForeign(['id_sesion']);

            $table->dropColumn([
                'id_item_actividad',
                'id_sesion',
            ]);
        });
    }
};