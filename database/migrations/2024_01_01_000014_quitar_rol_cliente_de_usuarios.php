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
        Schema::table('usuarios', function (Blueprint $table) {
            // MySQL doesn't support modifying enum directly, so we need to change the column type
            $table->enum('rol', ['admin', 'recepcionista'])->change();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->enum('rol', ['admin', 'cliente', 'recepcionista'])->change();
        });
    }
};
