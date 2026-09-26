<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_promociones_items', function (Blueprint $table) {
            $table->foreignId('id_promocion')
                ->constrained('tbl_promociones', 'id_promocion')
                ->cascadeOnDelete();

            $table->foreignId('id_item_actividad')
                ->constrained('tbl_items_actividad', 'id_item_actividad')
                ->restrictOnDelete();

            $table->primary(
                ['id_promocion', 'id_item_actividad'],
                'pk_promociones_items'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_promociones_items');
    }
};