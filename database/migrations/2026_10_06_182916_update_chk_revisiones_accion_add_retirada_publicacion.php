<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE tbl_revisiones_actividad
            DROP CONSTRAINT IF EXISTS chk_revisiones_accion
        ");

        DB::statement("
            ALTER TABLE tbl_revisiones_actividad
            ADD CONSTRAINT chk_revisiones_accion
            CHECK (
                accion IN (
                    'enviada_revision',
                    'cambios_solicitados',
                    'aprobada',
                    'rechazada',
                    'publicada',
                    'retirada',
                    'retirada_publicacion',
                    'reabierta'
                )
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE tbl_revisiones_actividad
            DROP CONSTRAINT IF EXISTS chk_revisiones_accion
        ");

        DB::statement("
            ALTER TABLE tbl_revisiones_actividad
            ADD CONSTRAINT chk_revisiones_accion
            CHECK (
                accion IN (
                    'enviada_revision',
                    'cambios_solicitados',
                    'aprobada',
                    'rechazada',
                    'publicada',
                    'retirada',
                    'reabierta'
                )
            )
        ");
    }
};