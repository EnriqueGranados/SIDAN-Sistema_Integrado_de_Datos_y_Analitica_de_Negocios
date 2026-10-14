<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_items_actividad', function (Blueprint $table) {
            $table->string('alcance_sesiones', 20)
                ->default('actividad')
                ->after('venta_hasta');

            $table->index(
                ['id_actividad', 'alcance_sesiones'],
                'idx_items_actividad_alcance_sesiones'
            );
        });

        DB::statement("
            ALTER TABLE tbl_items_actividad
            ADD CONSTRAINT chk_items_actividad_alcance_sesiones
            CHECK (
                alcance_sesiones IN (
                    'actividad',
                    'sesiones'
                )
            )
        ");

        Schema::create('tbl_items_sesiones', function (Blueprint $table) {
            $table->foreignId('id_item_actividad')
                ->constrained('tbl_items_actividad', 'id_item_actividad')
                ->cascadeOnDelete();

            $table->foreignId('id_sesion')
                ->constrained('tbl_sesiones_actividad', 'id_sesion')
                ->cascadeOnDelete();

            $table->primary(
                ['id_item_actividad', 'id_sesion'],
                'pk_items_sesiones'
            );

            $table->index(
                ['id_sesion', 'id_item_actividad'],
                'idx_items_sesiones_sesion'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_items_sesiones');

        DB::statement("
            ALTER TABLE tbl_items_actividad
            DROP CONSTRAINT IF EXISTS chk_items_actividad_alcance_sesiones
        ");

        Schema::table('tbl_items_actividad', function (Blueprint $table) {
            $table->dropIndex('idx_items_actividad_alcance_sesiones');
            $table->dropColumn('alcance_sesiones');
        });
    }
};
