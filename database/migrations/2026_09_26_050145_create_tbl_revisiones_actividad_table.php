<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_revisiones_actividad', function (Blueprint $table) {
            $table->id('id_revision');

            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->restrictOnDelete();

            $table->integer('numero_revision');

            $table->foreignId('id_usuario')
                ->constrained('tbl_usuarios', 'id_usuario')
                ->restrictOnDelete();

            $table->string('accion', 30);
            $table->text('observacion')->nullable();

            $table->timestampTz('creado_en')->useCurrent();

            $table->index(
                ['id_actividad', 'numero_revision', 'creado_en'],
                'idx_revisiones_actividad_historial'
            );

            $table->index(
                ['accion', 'creado_en'],
                'idx_revisiones_actividad_accion'
            );
        });

        DB::statement("
            ALTER TABLE tbl_revisiones_actividad
            ADD CONSTRAINT chk_revisiones_numero
            CHECK (numero_revision >= 1)
        ");

        DB::statement("
            ALTER TABLE tbl_revisiones_actividad
            ADD CONSTRAINT chk_revisiones_accion
            CHECK (
                accion IN (
                    'enviada_revision',
                    'cambios_solicitados',
                    'aprobada',
                    'rechazada',
                    'publicada',
                    'retirada',
                    'reabierta'
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_revisiones_actividad');
    }
};