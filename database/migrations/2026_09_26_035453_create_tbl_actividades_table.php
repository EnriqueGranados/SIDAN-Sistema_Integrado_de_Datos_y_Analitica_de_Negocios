<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_actividades', function (Blueprint $table) {
            $table->id('id_actividad');

            $table->foreignId('id_categoria')
                ->nullable()
                ->constrained('tbl_categorias', 'id_categoria')
                ->restrictOnDelete();

            $table->string('nombre', 180);
            $table->string('slug', 200)->unique();
            $table->string('resumen', 300)->nullable();
            $table->text('descripcion')->nullable();

            $table->string('estado_publicacion', 25)->default('borrador');
            $table->string('estado_operativo', 20)->default('normal');
            $table->string('visibilidad', 20)->default('publica');

            $table->integer('revision_actual')->default(1);

            $table->boolean('destacada')->default(false);
            $table->smallInteger('prioridad')->default(0);

            $table->boolean('habilita_inscripcion')->default(true);
            $table->boolean('requiere_cuenta')->default(false);
            $table->boolean('permite_lista_espera')->default(false);

            $table->integer('cupo_total')->nullable();

            $table->timestampTz('inscripcion_desde')->nullable();
            $table->timestampTz('inscripcion_hasta')->nullable();
            $table->timestampTz('visible_desde')->nullable();
            $table->timestampTz('visible_hasta')->nullable();

            $table->foreignId('creado_por')
                ->constrained('tbl_usuarios', 'id_usuario')
                ->restrictOnDelete();

            $table->foreignId('actualizado_por')
                ->nullable()
                ->constrained('tbl_usuarios', 'id_usuario')
                ->restrictOnDelete();

            $table->timestamps();
            $table->softDeletes('eliminado_en');

            $table->index([
                'estado_publicacion',
                'visibilidad',
                'visible_desde',
                'visible_hasta'
            ], 'idx_actividades_publicacion');

            $table->index([
                'estado_publicacion',
                'actualizado_por'
            ], 'idx_actividades_revision');
        });

        DB::statement("
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_prioridad
            CHECK (prioridad BETWEEN 0 AND 100)
        ");

        DB::statement("
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_cupo_total
            CHECK (
                cupo_total IS NULL
                OR cupo_total >= 0
            )
        ");

        DB::statement("
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_visibilidad
            CHECK (
                visibilidad IN (
                    'publica',
                    'no_listada',
                    'interna'
                )
            )
        ");

        DB::statement("
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_estado_publicacion
            CHECK (
                estado_publicacion IN (
                    'borrador',
                    'pendiente_revision',
                    'cambios_solicitados',
                    'aprobada',
                    'rechazada',
                    'publicada',
                    'retirada'
                )
            )
        ");

        DB::statement("
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_estado_operativo
            CHECK (
                estado_operativo IN (
                    'normal',
                    'suspendida',
                    'cancelada'
                )
            )
        ");

        DB::statement("
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_revision_actual
            CHECK (revision_actual >= 1)
        ");

        DB::statement("
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_inscripcion_fechas
            CHECK (
                inscripcion_hasta IS NULL
                OR inscripcion_desde IS NULL
                OR inscripcion_hasta >= inscripcion_desde
            )
        ");

        DB::statement("
            ALTER TABLE tbl_actividades
            ADD CONSTRAINT chk_actividades_visibilidad_fechas
            CHECK (
                visible_hasta IS NULL
                OR visible_desde IS NULL
                OR visible_hasta >= visible_desde
            )
        ");

        DB::statement("
            CREATE INDEX idx_actividades_destacadas
            ON tbl_actividades (prioridad DESC)
            WHERE destacada = TRUE
                AND estado_publicacion = 'publicada'
                AND eliminado_en IS NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_actividades');
    }
};