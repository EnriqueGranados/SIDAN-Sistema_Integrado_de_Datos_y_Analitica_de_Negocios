<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_google_usuarios', function (Blueprint $table) {
            $table->id('id_google_usuario');
            $table->foreignId('id_usuario')->unique()->constrained('tbl_usuarios', 'id_usuario')->cascadeOnDelete();
            $table->string('google_id')->unique();
            $table->string('correo_google', 150);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_google_usuarios');
    }
};