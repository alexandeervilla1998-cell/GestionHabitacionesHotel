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
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10)->unique();
            $table->enum('tipo', ['individual', 'doble', 'suite', 'familiar']);
            $table->decimal('precio_por_noche', 10, 2);
            $table->enum('estado', ['disponible', 'ocupada', 'mantenimiento']);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->nullable();
            $table->timestamp('actualizado_en')->nullable();
            $table->string('imagen', 255)->nullable();
            
            $table->index('numero');
            $table->index('tipo');
            $table->index('estado');
            $table->index('activo');
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
