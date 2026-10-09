<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP INDEX IF EXISTS uq_medios_actividad_portada');

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX uq_medios_actividad_portada
            ON tbl_medios_actividad (id_actividad)
            WHERE es_portada = TRUE
              AND id_item_actividad IS NULL
              AND id_sesion IS NULL
        SQL);

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX uq_medios_item_portada
            ON tbl_medios_actividad (id_item_actividad)
            WHERE es_portada = TRUE
              AND id_item_actividad IS NOT NULL
        SQL);

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX uq_medios_sesion_portada
            ON tbl_medios_actividad (id_sesion)
            WHERE es_portada = TRUE
              AND id_sesion IS NOT NULL
        SQL);
    }

    public function down(): void
    {
        $hayConflictos = DB::table('tbl_medios_actividad')
            ->where('es_portada', true)
            ->groupBy('id_actividad')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hayConflictos) {
            throw new RuntimeException(
                'No se puede revertir esta migración mientras una actividad tenga más de una portada entre actividad, productos o sesiones.'
            );
        }

        DB::statement('DROP INDEX IF EXISTS uq_medios_sesion_portada');
        DB::statement('DROP INDEX IF EXISTS uq_medios_item_portada');
        DB::statement('DROP INDEX IF EXISTS uq_medios_actividad_portada');

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX uq_medios_actividad_portada
            ON tbl_medios_actividad (id_actividad)
            WHERE es_portada = TRUE
        SQL);
    }
};