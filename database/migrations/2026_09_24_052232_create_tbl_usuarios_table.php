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
        Schema::create('tbl_usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->foreignId('id_informacion_personal')->unique()->constrained('tbl_informacion_personal', 'id_informacion_personal')->cascadeOnDelete();
            $table->foreignId('id_rol')->constrained('tbl_roles', 'id_rol');
            $table->string('correo', 150)->unique();
            $table->string('password_hash', 255);
            $table->boolean('must_change_password')->default(false);
            $table->boolean('estado_activo')->default(true);
            $table->boolean('eliminado')->default(false);
            $table->rememberToken();
            $table->timestamps();
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
        Schema::dropIfExists('tbl_usuarios');
        Schema::dropIfExists('codigos_reset');
        Schema::dropIfExists('sessions');
    }
};