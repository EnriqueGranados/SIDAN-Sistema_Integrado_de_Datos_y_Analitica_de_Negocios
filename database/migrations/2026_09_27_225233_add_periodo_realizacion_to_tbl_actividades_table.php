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
            $table->timestampTz('realizacion_desde')->nullable();
            $table->timestampTz('realizacion_hasta')->nullable();
        });

        DB::statement("
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_realizacion_fechas
            CHECK (
                realizacion_hasta IS NULL
                OR realizacion_desde IS NULL
                OR realizacion_hasta >= realizacion_desde
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE tbl_actividades
            DROP CONSTRAINT IF EXISTS chk_actividades_realizacion_fechas
        ");

        Schema::table('tbl_actividades', function (Blueprint $table) {
            $table->dropColumn([
                'realizacion_desde',
                'realizacion_hasta',
            ]);
        });
    }
};