<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inscripciones de participantes.
        Schema::create('tbl_inscripciones', function (Blueprint $table) {
            $table->id('id_inscripcion');

            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->restrictOnDelete();

            $table->foreignId('id_usuario')
                ->nullable()
                ->constrained('tbl_usuarios', 'id_usuario')
                ->nullOnDelete();

            $table->string('nombre_participante', 200);
            $table->string('correo_participante', 150);
            $table->string('telefono_participante', 25)->nullable();

            $table->string('estado', 30);
            $table->timestampTz('fecha_confirmacion')->nullable();
            $table->timestampTz('expira_en')->nullable();
            $table->timestampsTz();

            $table->index(['id_actividad', 'estado']);
        });

        DB::statement("
            ALTER TABLE tbl_inscripciones
            ADD CONSTRAINT chk_inscripciones_estado
            CHECK (estado IN (
                'confirmada',
                'confirmada_prueba',
                'pendiente_pago',
                'cancelada',
                'vencida',
                'revision_cupo'
            ))
        ");

        // No permitir dos inscripciones activas con el mismo correo.
        DB::statement("
            CREATE UNIQUE INDEX uq_inscripcion_correo_activo
            ON tbl_inscripciones (
                id_actividad,
                lower(correo_participante)
            )
            WHERE estado IN (
                'confirmada',
                'confirmada_prueba',
                'pendiente_pago',
                'revision_cupo'
            )
        ");

        // Tampoco permitir duplicados por usuario registrado.
        DB::statement("
            CREATE UNIQUE INDEX uq_inscripcion_usuario_activo
            ON tbl_inscripciones (id_actividad, id_usuario)
            WHERE id_usuario IS NOT NULL
            AND estado IN (
                'confirmada',
                'confirmada_prueba',
                'pendiente_pago',
                'revision_cupo'
            )
        ");

        // Órdenes comerciales.
        Schema::create('tbl_ordenes', function (Blueprint $table) {
            $table->id('id_orden');

            $table->foreignId('id_actividad')
                ->constrained('tbl_actividades', 'id_actividad')
                ->restrictOnDelete();

            $table->foreignId('id_inscripcion')
                ->nullable()
                ->unique()
                ->constrained('tbl_inscripciones', 'id_inscripcion')
                ->restrictOnDelete();

            $table->foreignId('id_usuario')
                ->nullable()
                ->constrained('tbl_usuarios', 'id_usuario')
                ->nullOnDelete();

            $table->string('correo_contacto', 150);
            $table->char('moneda', 3)->default('USD');

            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            $table->string('estado', 30)->default('pendiente_pago');
            $table->timestampTz('expira_en')->nullable();
            $table->timestampsTz();
        });

        DB::statement("
            ALTER TABLE tbl_ordenes
            ADD CONSTRAINT chk_ordenes_montos
            CHECK (
                subtotal >= 0
                AND descuento_total >= 0
                AND total >= 0
            )
        ");

        // Conceptos que forman una orden.
        Schema::create('tbl_detalles_orden', function (Blueprint $table) {
            $table->id('id_detalle_orden');

            $table->foreignId('id_orden')
                ->constrained('tbl_ordenes', 'id_orden')
                ->cascadeOnDelete();

            $table->string('tipo_concepto', 30);
            $table->string('nombre_concepto', 200);
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestampsTz();
        });

        // Movimientos de pago.
        Schema::create('tbl_pagos', function (Blueprint $table) {
            $table->id('id_pago');

            $table->foreignId('id_orden')
                ->unique()
                ->constrained('tbl_ordenes', 'id_orden')
                ->restrictOnDelete();

            $table->string('proveedor', 30)->default('wompi');
            $table->decimal('monto', 12, 2);
            $table->string('estado', 30)->default('pendiente');
            $table->timestampTz('fecha_confirmacion')->nullable();
            $table->timestampsTz();
        });

        // Cada intento de generar y pagar un enlace de Wompi.
        Schema::create('tbl_intentos_pago_wompi', function (Blueprint $table) {
            $table->id('id_intento');

            $table->foreignId('id_pago')
                ->constrained('tbl_pagos', 'id_pago')
                ->restrictOnDelete();

            $table->string('referencia_comercio', 120)->unique();
            $table->unsignedBigInteger('id_enlace_wompi')
                ->nullable()->unique();

            $table->string('id_transaccion_wompi', 100)
                ->nullable()->unique();

            $table->decimal('monto_solicitado', 12, 2);
            $table->text('url_enlace')->nullable();
            $table->boolean('es_productiva')->nullable();
            $table->string('estado', 30)->default('creando');
            $table->timestampTz('expira_en')->nullable();
            $table->timestampsTz();

            $table->index(['id_pago', 'estado']);
        });

        // Recibos internos, no comprobantes fiscales.
        Schema::create('tbl_comprobantes', function (Blueprint $table) {
            $table->id('id_comprobante');

            $table->foreignId('id_pago')
                ->unique()
                ->constrained('tbl_pagos', 'id_pago')
                ->restrictOnDelete();

            $table->string('numero', 70)->unique();
            $table->string('tipo', 30)->default('recibo_interno');
            $table->decimal('monto', 12, 2);
            $table->timestampTz('fecha_emision');
            $table->boolean('es_productiva')->default(false);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_comprobantes');
        Schema::dropIfExists('tbl_intentos_pago_wompi');
        Schema::dropIfExists('tbl_pagos');
        Schema::dropIfExists('tbl_detalles_orden');
        Schema::dropIfExists('tbl_ordenes');
        Schema::dropIfExists('tbl_inscripciones');
    }
};
