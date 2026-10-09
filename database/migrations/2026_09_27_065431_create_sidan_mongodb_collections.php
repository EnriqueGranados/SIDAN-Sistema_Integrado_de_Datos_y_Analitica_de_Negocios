<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    protected $connection = 'mongodb';

    public function up(): void
    {
        if (!Schema::hasCollection('system_config')) {
            Schema::create('system_config', function (Blueprint $collection) {
                $collection->unique('singleton_key');
            });
        }

        if (!Schema::hasCollection('activity_content')) {
            Schema::create('activity_content', function (Blueprint $collection) {
                $collection->unique('id_actividad_pg');
            });
        }

        if (!Schema::hasCollection('form_configs')) {
            Schema::create('form_configs', function (Blueprint $collection) {
                $collection->unique('id_formulario_pg');
            });
        }

        if (!Schema::hasCollection('registration_answers')) {
            Schema::create('registration_answers', function (Blueprint $collection) {
                $collection->index('id_inscripcion_pg');
                $collection->index('id_formulario_pg');
                $collection->index([
                    'id_inscripcion_pg' => 1,
                    'scope' => 1,
                    'id_detalle_inscripcion_pg' => 1,
                    'id_participante_pg' => 1,
                ]);
                $collection->index('created_at');
            });
        }

        if (!Schema::hasCollection('variant_attributes')) {
            Schema::create('variant_attributes', function (Blueprint $collection) {
                $collection->unique('id_variante_pg');
            });
        }

        if (!Schema::hasCollection('audit_events')) {
            Schema::create('audit_events', function (Blueprint $collection) {
                $collection->index('id_usuario_pg');
                $collection->index([
                    'entity' => 1,
                    'record_id' => 1,
                ]);
                $collection->index('action');
                $collection->index('timestamp');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('registration_answers');
        Schema::dropIfExists('form_configs');
        Schema::dropIfExists('activity_content');
        Schema::dropIfExists('system_config');
    }
};