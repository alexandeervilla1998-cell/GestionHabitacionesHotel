<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        $clientes = [
            [
                'nombre' => 'Juan Pérez',
                'correo' => 'juan.perez@example.com',
                'telefono' => '+52 55 1234 5678',
                'identificacion' => 'JUPA800101',
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'nombre' => 'Ana Martínez',
                'correo' => 'ana.martinez@example.com',
                'telefono' => '+52 55 8765 4321',
                'identificacion' => 'AMA850202',
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'nombre' => 'Carlos López',
                'correo' => 'carlos.lopez@example.com',
                'telefono' => '+52 55 1111 2222',
                'identificacion' => 'CALO900303',
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'nombre' => 'María García',
                'correo' => 'maria.garcia@example.com',
                'telefono' => '+52 55 3333 4444',
                'identificacion' => 'MAGA920404',
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'nombre' => 'Roberto Sánchez',
                'correo' => 'roberto.sanchez@example.com',
                'telefono' => '+52 55 5555 6666',
                'identificacion' => 'ROSA950505',
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now()
            ]
        ];

        foreach ($clientes as $cliente) {
            Cliente::create($cliente);
        }

        $this->command->info('5 clientes creados exitosamente');
    }
}
