<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_roles', function (Blueprint $table) {
            $table->json('modulos')->nullable();
        });

        $modulosAdmin = [
            'dashboard',
            'actividades',
            'revision_actividades',
            'categorias',
            'etiquetas',
            'recursos',
            'espacios',
            'inscripciones',
            'ordenes',
            'consolidado',
            'pagos',
            'usuarios',
            'welcome',
        ];

        DB::table('tbl_roles')
            ->where('nombre', 'admin')
            ->update([
                'modulos' => json_encode($modulosAdmin),
            ]);

        DB::table('tbl_roles')
            ->whereNull('modulos')
            ->update([
                'modulos' => json_encode([]),
            ]);
    }

    public function down(): void
    {
        Schema::table('tbl_roles', function (Blueprint $table) {
            $table->dropColumn('modulos');
        });
    }
};