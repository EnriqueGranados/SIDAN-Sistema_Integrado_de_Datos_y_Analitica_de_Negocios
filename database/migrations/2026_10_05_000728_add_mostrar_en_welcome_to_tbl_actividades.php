<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_actividades', function (Blueprint $table) {
            $table->boolean('mostrar_en_welcome')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('tbl_actividades', function (Blueprint $table) {
            $table->dropColumn('mostrar_en_welcome');
        });
    }
};