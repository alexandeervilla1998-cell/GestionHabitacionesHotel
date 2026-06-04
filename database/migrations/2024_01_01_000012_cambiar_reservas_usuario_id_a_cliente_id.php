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
        Schema::table('reservas', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['usuario_id']);
            
            // Drop index on usuario_id
            $table->dropIndex(['usuario_id']);
            
            // Rename column
            $table->renameColumn('usuario_id', 'cliente_id');
            
            // Add index on cliente_id
            $table->index('cliente_id');
        });
        
        // Add foreign key separately to ensure clientes table exists
        Schema::table('reservas', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['cliente_id']);
            
            // Drop index on cliente_id
            $table->dropIndex(['cliente_id']);
            
            // Rename column back
            $table->renameColumn('cliente_id', 'usuario_id');
            
            // Add index on usuario_id
            $table->index('usuario_id');
        });
        
        // Add foreign key to usuarios table separately
        Schema::table('reservas', function (Blueprint $table) {
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }
};
