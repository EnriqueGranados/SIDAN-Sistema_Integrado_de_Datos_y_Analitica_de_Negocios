<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_items_actividad', function (Blueprint $table) {
            $table->id('id_item_actividad');

            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->restrictOnDelete();

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('tipo', 30)->default('servicio');

            $table->decimal('precio', 12, 2)->default(0);
            $table->decimal('costo_referencia', 12, 2)->default(0);

            $table->integer('stock_total')->nullable();

            $table->timestampTz('venta_desde')->nullable();
            $table->timestampTz('venta_hasta')->nullable();

            $table->integer('min_por_inscripcion')->default(1);
            $table->integer('max_por_inscripcion')->nullable();

            $table->boolean('requiere_participante')->default(false);
            $table->smallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);

            $table->timestampTz('creado_en')->useCurrent();

            $table->index(
                ['id_actividad', 'activo', 'orden'],
                'idx_items_actividad_catalogo'
            );
        });

        DB::statement("
            ALTER TABLE tbl_items_actividad
            ADD CONSTRAINT chk_items_actividad_tipo
            CHECK (
                tipo IN (
                    'producto',
                    'servicio',
                    'acceso',
                    'donacion',
                    'reserva',
                    'otro'
                )
            )
        ");

        DB::statement("
            ALTER TABLE tbl_items_actividad
            ADD CONSTRAINT chk_items_actividad_precio
            CHECK (precio >= 0)
        ");

        DB::statement("
            ALTER TABLE tbl_items_actividad
            ADD CONSTRAINT chk_items_actividad_costo
            CHECK (costo_referencia >= 0)
        ");

        DB::statement("
            ALTER TABLE tbl_items_actividad
            ADD CONSTRAINT chk_items_actividad_stock
            CHECK (
                stock_total IS NULL
                OR stock_total >= 0
            )
        ");

        DB::statement("
            ALTER TABLE tbl_items_actividad
            ADD CONSTRAINT chk_items_actividad_fechas_venta
            CHECK (
                venta_hasta IS NULL
                OR venta_desde IS NULL
                OR venta_hasta >= venta_desde
            )
        ");

        DB::statement("
            ALTER TABLE tbl_items_actividad
            ADD CONSTRAINT chk_items_actividad_cantidades
            CHECK (
                min_por_inscripcion >= 1
                AND (
                    max_por_inscripcion IS NULL
                    OR max_por_inscripcion >= min_por_inscripcion
                )
            )
        ");

        DB::statement("
            ALTER TABLE tbl_items_actividad
            ADD CONSTRAINT chk_items_actividad_orden
            CHECK (orden >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_items_actividad');
    }
};