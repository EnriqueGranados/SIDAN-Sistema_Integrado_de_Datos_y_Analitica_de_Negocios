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
        Schema::create('tbl_informacion_personal', function (Blueprint $table) {
            $table->id('id_informacion_personal');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('documento', 30)->unique()->nullable();
            $table->string('telefono', 25)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 10)->nullable();
            $table->timestamps();
        });
    }

    /*
        Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('tbl_informacion_personal');
    }
};
