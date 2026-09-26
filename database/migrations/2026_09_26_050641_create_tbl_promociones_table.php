<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_promociones', function (Blueprint $table) {
            $table->id('id_promocion');

            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->restrictOnDelete();

            $table->string('codigo', 60)->nullable();
            $table->string('nombre', 140);

            $table->string('tipo_descuento', 15);
            $table->decimal('valor', 12, 2);

            $table->timestampTz('vigente_desde')->nullable();
            $table->timestampTz('vigente_hasta')->nullable();

            $table->integer('limite_usos')->nullable();
            $table->integer('limite_por_persona')->nullable();

            $table->decimal('monto_minimo', 12, 2)->nullable();

            $table->boolean('activo')->default(true);

            $table->foreignId('creado_por')
                ->constrained('usuarios', 'id_usuario')
                ->restrictOnDelete();

            $table->timestampTz('creado_en')->useCurrent();
        });

        DB::statement("
            CREATE UNIQUE INDEX uq_promociones_codigo_actividad
            ON tbl_promociones (id_actividad, LOWER(codigo))
            WHERE codigo IS NOT NULL
        ");

        DB::statement("
            ALTER TABLE tbl_promociones
            ADD CONSTRAINT chk_promociones_tipo_descuento
            CHECK (
                tipo_descuento IN (
                    'porcentaje',
                    'monto'
                )
            )
        ");

        DB::statement("
            ALTER TABLE tbl_promociones
            ADD CONSTRAINT chk_promociones_valor
            CHECK (
                valor > 0
                AND (
                    tipo_descuento <> 'porcentaje'
                    OR valor <= 100
                )
            )
        ");

        DB::statement("
            ALTER TABLE tbl_promociones
            ADD CONSTRAINT chk_promociones_fechas
            CHECK (
                vigente_hasta IS NULL
                OR vigente_desde IS NULL
                OR vigente_hasta >= vigente_desde
            )
        ");

        DB::statement("
            ALTER TABLE tbl_promociones
            ADD CONSTRAINT chk_promociones_limite_usos
            CHECK (
                limite_usos IS NULL
                OR limite_usos > 0
            )
        ");

        DB::statement("
            ALTER TABLE tbl_promociones
            ADD CONSTRAINT chk_promociones_limite_persona
            CHECK (
                limite_por_persona IS NULL
                OR limite_por_persona > 0
            )
        ");

        DB::statement("
            ALTER TABLE tbl_promociones
            ADD CONSTRAINT chk_promociones_monto_minimo
            CHECK (
                monto_minimo IS NULL
                OR monto_minimo >= 0
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_promociones');
    }
};