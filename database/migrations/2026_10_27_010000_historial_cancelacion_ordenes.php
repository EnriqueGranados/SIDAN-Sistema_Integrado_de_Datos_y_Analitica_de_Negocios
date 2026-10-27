<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_ordenes', function (Blueprint $table) {
            $table->timestampTz('cancelada_en')->nullable();
            $table->unsignedBigInteger('cancelada_por')->nullable();
            $table->string('motivo_cancelacion', 300)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_ordenes', function (Blueprint $table) {
            $table->dropColumn(['cancelada_en', 'cancelada_por', 'motivo_cancelacion']);
        });
    }
};
