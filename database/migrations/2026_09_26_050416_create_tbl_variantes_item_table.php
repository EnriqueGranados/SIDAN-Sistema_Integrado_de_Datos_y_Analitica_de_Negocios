<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_variantes_item', function (Blueprint $table) {
            $table->id('id_variante_item');

            $table->foreignId('id_item_actividad')
                ->constrained('tbl_items_actividad', 'id_item_actividad')
                ->restrictOnDelete();

            $table->string('sku', 100)->nullable();
            $table->string('nombre_variante', 180);

            $table->decimal('precio', 12, 2)->nullable();
            $table->decimal('costo_referencia', 12, 2)->nullable();

            $table->integer('stock_total')->nullable();

            $table->smallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);

            $table->unique('sku', 'uq_variantes_item_sku');

            $table->index(
                ['id_item_actividad', 'activo', 'orden'],
                'idx_variantes_item_catalogo'
            );
        });

        DB::statement("
            ALTER TABLE tbl_variantes_item
            ADD CONSTRAINT chk_variantes_item_precio
            CHECK (
                precio IS NULL
                OR precio >= 0
            )
        ");

        DB::statement("
            ALTER TABLE tbl_variantes_item
            ADD CONSTRAINT chk_variantes_item_costo
            CHECK (
                costo_referencia IS NULL
                OR costo_referencia >= 0
            )
        ");

        DB::statement("
            ALTER TABLE tbl_variantes_item
            ADD CONSTRAINT chk_variantes_item_stock
            CHECK (
                stock_total IS NULL
                OR stock_total >= 0
            )
        ");

        DB::statement("
            ALTER TABLE tbl_variantes_item
            ADD CONSTRAINT chk_variantes_item_orden
            CHECK (orden >= 0)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_variantes_item');
    }
};