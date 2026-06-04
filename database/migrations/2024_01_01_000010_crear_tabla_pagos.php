<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'tarjeta_crédito', 'tarjeta_débito', 'transferencia']);
            $table->enum('estado_pago', ['pendiente', 'completado', 'fallido', 'reembolsado']);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->nullable();
            
            $table->index('factura_id');
            $table->index('metodo_pago');
            $table->index('estado_pago');
            $table->index('activo');
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
