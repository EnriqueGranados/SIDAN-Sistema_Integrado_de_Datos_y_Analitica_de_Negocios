
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Guardar el costo de cada artículo al crear la orden.
        Schema::table('tbl_detalles_orden', function (Blueprint $table) {
            $table->decimal('costo_unitario_historico', 12, 2)
                ->nullable();
        });

        // 2. Obtener el costo vigente del producto o variante.
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION sidan_copiar_costo_historico()
            RETURNS TRIGGER
            LANGUAGE plpgsql
            AS $$
            DECLARE
                costo_actual NUMERIC(12, 2);
            BEGIN
                -- Los detalles sin artículo no tienen costo del catálogo.
                IF NEW.id_item_actividad IS NULL THEN
                    NEW.costo_unitario_historico := NULL;
                    RETURN NEW;
                END IF;

                -- Primero usar el costo de la variante cuando exista.
                -- En caso contrario, usar el costo del artículo.
                SELECT COALESCE(
                    v.costo_referencia,
                    i.costo_referencia
                )
                INTO costo_actual
                FROM tbl_items_actividad AS i
                LEFT JOIN tbl_variantes_item AS v
                    ON v.id_variante_item = NEW.id_variante_item
                    AND v.id_item_actividad = i.id_item_actividad
                WHERE i.id_item_actividad = NEW.id_item_actividad;

                -- Congelar el costo en el detalle de la compra.
                NEW.costo_unitario_historico := costo_actual;

                RETURN NEW;
            END;
            $$;
        SQL);

        // 3. Ejecutar automáticamente antes de insertar cada detalle.
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_sidan_copiar_costo_historico
            BEFORE INSERT ON tbl_detalles_orden
            FOR EACH ROW
            EXECUTE FUNCTION sidan_copiar_costo_historico();
        SQL);
    }

    public function down(): void
    {
        // Eliminar trigger y función al revertir la migración.
        DB::unprepared(
            'DROP TRIGGER IF EXISTS trg_sidan_copiar_costo_historico ON tbl_detalles_orden;'
        );

        DB::unprepared(
            'DROP FUNCTION IF EXISTS sidan_copiar_costo_historico();'
        );

        Schema::table('tbl_detalles_orden', function (Blueprint $table) {
            $table->dropColumn('costo_unitario_historico');
        });
    }
};
