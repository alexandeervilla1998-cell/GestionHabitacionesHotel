<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        // Migrar clientes de usuarios a tabla clientes
        $clientes = DB::table('usuarios')
            ->where('rol', 'cliente')
            ->get();

        foreach ($clientes as $cliente) {
            $nuevoClienteId = DB::table('clientes')->insertGetId([
                'nombre' => $cliente->nombre,
                'correo' => $cliente->correo,
                'telefono' => null,
                'direccion' => null,
                'identificacion' => null,
                'activo' => $cliente->activo,
                'creado_en' => $cliente->creado_en,
                'actualizado_en' => $cliente->actualizado_en,
            ]);

            // Actualizar reservas para usar el nuevo cliente_id
            DB::table('reservas')
                ->where('cliente_id', $cliente->id)
                ->update(['cliente_id' => $nuevoClienteId]);
        }

        // Eliminar usuarios con rol 'cliente'
        DB::table('usuarios')
            ->where('rol', 'cliente')
            ->delete();
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        // Esta migración no es reversible de manera segura
        // ya que los datos originales de usuarios se eliminaron
        // Se recomienda usar migrate:fresh en su lugar
        throw new \Exception('Esta migración no es reversible. Use migrate:fresh para restaurar el estado original.');
    }
};
