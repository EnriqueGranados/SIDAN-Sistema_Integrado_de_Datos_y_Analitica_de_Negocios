<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
        Run the migrations.
    */
    public function up(): void
    {
        Schema::create('tbl_etiquetas', function (Blueprint $table) {
            $table->id('id_etiqueta');
            $table->string('nombre', 80)->unique();
            $table->string('slug', 90)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /*
        Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('tbl_etiquetas');
    }
};