<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_welcome_espacios', function (Blueprint $table) {
            $table->bigIncrements('id_espacio_welcome');
            $table->string('seccion', 30);
            $table->string('nombre', 100);
            $table->smallInteger('orden');
            $table->unsignedBigInteger('id_actividad')->nullable();
            $table->timestamps();

            $table->unique(['seccion', 'orden']);
            $table->foreign('id_actividad')->references('id_actividad')->on('tbl_actividades')->onUpdate('cascade')->onDelete('set null');
        });

        DB::table('tbl_welcome_espacios')->insert([
            ['seccion' => 'hero', 'nombre' => 'Hero principal', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['seccion' => 'hero', 'nombre' => 'Hero secundario 1', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['seccion' => 'hero', 'nombre' => 'Hero secundario 2', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['seccion' => 'explorar', 'nombre' => 'Actividad 1', 'orden' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['seccion' => 'explorar', 'nombre' => 'Actividad 2', 'orden' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['seccion' => 'explorar', 'nombre' => 'Actividad 3', 'orden' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['seccion' => 'explorar', 'nombre' => 'Actividad 4', 'orden' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['seccion' => 'explorar', 'nombre' => 'Actividad 5', 'orden' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['seccion' => 'explorar', 'nombre' => 'Actividad 6', 'orden' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_welcome_espacios');
    }
};