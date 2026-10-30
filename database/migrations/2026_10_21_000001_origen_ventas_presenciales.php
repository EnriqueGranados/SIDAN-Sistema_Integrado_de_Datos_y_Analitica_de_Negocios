<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_ordenes', function (Blueprint $table) {
            $table->string('canal_venta', 20)->default('portal');
            $table->foreignId('creado_por')->nullable()->constrained('tbl_usuarios', 'id_usuario')->nullOnDelete();
        });
        Schema::table('tbl_abonos_transferencia', function (Blueprint $table) {
            $table->decimal('monto_reportado', 12, 2)->nullable();
        });
    }
    public function down(): void
    {
        // Revertir solamente en desarrollo, después de constatar ausencia
        // de movimientos de caja. No eliminar estos datos financieros en producción.
        throw new \RuntimeException('Reversión bloqueada: conservar el origen de las ventas y los montos reportados.');
    }
};
