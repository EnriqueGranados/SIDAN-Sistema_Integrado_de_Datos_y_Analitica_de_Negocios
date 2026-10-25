<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_avisos_orden', function (Blueprint $t) {
            $t->bigIncrements('id_aviso');
            $t->unsignedBigInteger('id_orden')->index();
            $t->string('evento', 36);
            $t->string('referencia', 60)->default('');
            $t->timestampTz('enviado_en')->nullable();
            $t->timestampsTz();
            $t->unique(['id_orden', 'evento', 'referencia'], 'avisos_orden_unicos');
        });
    }
    public function down(): void { Schema::dropIfExists('tbl_avisos_orden'); }
};
