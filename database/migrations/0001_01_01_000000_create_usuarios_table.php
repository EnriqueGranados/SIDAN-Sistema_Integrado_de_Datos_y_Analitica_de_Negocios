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
        // Tabla de usuarios.
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('correo', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('documento', 30)->unique()->nullable();
            $table->string('telefono', 25)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->string('estado', 20)->default('activo');
            $table->string('genero', 10)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->string('rol', 20)->default('usuario'); 
            $table->timestampTz('creado_en')->useCurrent();
        });

        // Tabla para la recuperación de contraseñas.
        Schema::create('codigos_reset', function (Blueprint $table) {
            $table->id('id_codigo');
            $table->string('correo', 100);
            $table->string('codigo', 8);
            $table->date('expiracion');
            $table->boolean('usado')->default(false);
        });

        // Tabla para manejo de sesiones.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /*
        Reverse the migrations.
    */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('codigos_reset');
        Schema::dropIfExists('sessions');
    }
};