<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_espacios', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | Estado de validación
            |--------------------------------------------------------------------------
            |
            | Los espacios creados normalmente desde Administración quedan
            | validados.
            |
            | Los creados rápidamente desde una actividad o una sesión quedan
            | pendientes, pero siguen activos y pueden utilizarse inmediatamente.
            |
            */

            $table->string('estado_validacion', 20)
                ->default('validado')
                ->after('activo');

            /*
            |--------------------------------------------------------------------------
            | Origen
            |--------------------------------------------------------------------------
            |
            | administracion
            | actividad
            | sesion
            |
            */

            $table->string('origen_registro', 30)
                ->default('administracion')
                ->after('estado_validacion');

            /*
            |--------------------------------------------------------------------------
            | Auditoría
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('creado_por')
                ->nullable()
                ->after('origen_registro');

            $table->unsignedBigInteger('validado_por')
                ->nullable()
                ->after('creado_por');

            $table->timestamp('validado_en')
                ->nullable()
                ->after('validado_por');

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $table->foreign('creado_por')
                ->references('id_usuario')
                ->on('tbl_usuarios')
                ->nullOnDelete();

            $table->foreign('validado_por')
                ->references('id_usuario')
                ->on('tbl_usuarios')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Índices
            |--------------------------------------------------------------------------
            */

            $table->index(
                'estado_validacion',
                'idx_espacios_estado_validacion'
            );

            $table->index(
                'origen_registro',
                'idx_espacios_origen_registro'
            );

            $table->index(
                'creado_por',
                'idx_espacios_creado_por'
            );
        });
    }

    public function down(): void
    {
        Schema::table('tbl_espacios', function (Blueprint $table) {
            $table->dropForeign(['creado_por']);
            $table->dropForeign(['validado_por']);

            $table->dropIndex('idx_espacios_estado_validacion');
            $table->dropIndex('idx_espacios_origen_registro');
            $table->dropIndex('idx_espacios_creado_por');

            $table->dropColumn([
                'estado_validacion',
                'origen_registro',
                'creado_por',
                'validado_por',
                'validado_en',
            ]);
        });
    }
};