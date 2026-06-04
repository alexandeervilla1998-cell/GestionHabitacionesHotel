<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropIndex(['activo']);
            $table->dropColumn('activo');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropIndex(['activo']);
            $table->dropColumn('activo');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->boolean('activo')->default(true);
            $table->index('activo');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->boolean('activo')->default(true);
            $table->index('activo');
        });
    }
};
