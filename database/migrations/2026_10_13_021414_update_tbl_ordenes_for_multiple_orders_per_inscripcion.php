<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Una inscripción ahora puede tener varias órdenes
        |--------------------------------------------------------------------------
        |
        | Antes:
        | inscripción 1 --- 1 orden
        |
        | Ahora:
        | inscripción 1 --- N órdenes
        |
        | Ejemplo:
        | - orden de inscripción
        | - orden de camiseta
        | - orden de productos posterior
        |
        */

        DB::statement('
            ALTER TABLE tbl_ordenes
            DROP CONSTRAINT IF EXISTS tbl_ordenes_id_inscripcion_unique
        ');

        DB::statement('
            DROP INDEX IF EXISTS tbl_ordenes_id_inscripcion_unique
        ');

        Schema::table('tbl_ordenes', function (Blueprint $table) {
            $table->string('tipo_orden', 30)
                ->default('inscripcion');

            $table->index(
                'id_inscripcion',
                'idx_ordenes_id_inscripcion'
            );

            $table->index(
                [
                    'id_inscripcion',
                    'tipo_orden',
                    'estado',
                ],
                'idx_ordenes_inscripcion_tipo_estado'
            );
        });

        DB::statement("
            ALTER TABLE tbl_ordenes
            ADD CONSTRAINT chk_ordenes_tipo_orden
            CHECK (
                tipo_orden IN (
                    'inscripcion',
                    'productos'
                )
            )
        ");
    }

    public function down(): void
    {
        DB::statement('
            ALTER TABLE tbl_ordenes
            DROP CONSTRAINT IF EXISTS chk_ordenes_tipo_orden
        ');

        Schema::table('tbl_ordenes', function (Blueprint $table) {
            $table->dropIndex(
                'idx_ordenes_inscripcion_tipo_estado'
            );

            $table->dropIndex(
                'idx_ordenes_id_inscripcion'
            );

            $table->dropColumn(
                'tipo_orden'
            );
        });

        Schema::table('tbl_ordenes', function (Blueprint $table) {
            $table->unique(
                'id_inscripcion'
            );
        });
    }
};