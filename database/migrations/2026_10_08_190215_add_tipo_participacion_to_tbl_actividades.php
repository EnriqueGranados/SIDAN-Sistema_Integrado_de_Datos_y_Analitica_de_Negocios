<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_actividades', function (Blueprint $table) {
            $table->string('tipo_participacion', 30)
                ->default('registro_gratuito')
                ->after('prioridad');

            $table->decimal('precio_inscripcion', 12, 2)
                ->nullable()
                ->after('cupo_total');
        });

        DB::statement(<<<'SQL'
            UPDATE tbl_actividades AS a
            SET tipo_participacion = CASE
                WHEN a.habilita_inscripcion = TRUE THEN 'registro_gratuito'
                WHEN EXISTS (
                    SELECT 1
                    FROM tbl_items_actividad AS i
                    WHERE i.id_actividad = a.id_actividad
                      AND i.activo = TRUE
                ) THEN 'venta_directa'
                ELSE 'informativa'
            END
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_tipo_participacion
            CHECK (
                tipo_participacion IN (
                    'informativa',
                    'registro_gratuito',
                    'registro_pago',
                    'venta_directa'
                )
            )
        SQL);
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE tbl_actividades DROP CONSTRAINT IF EXISTS chk_actividades_tipo_participacion'
        );

        Schema::table('tbl_actividades', function (Blueprint $table) {
            $table->dropColumn([
                'precio_inscripcion',
                'tipo_participacion',
            ]);
        });
    }
};
