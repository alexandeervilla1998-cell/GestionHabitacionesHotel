<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            [
                'nombre' => 'Desayuno Buffet',
                'precio' => 15.99,
                'activo' => true
            ],
            [
                'nombre' => 'Almuerzo Ejecutivo',
                'precio' => 22.50,
                'activo' => true
            ],
            [
                'nombre' => 'Cena Romántica',
                'precio' => 45.00,
                'activo' => true
            ],
            [
                'nombre' => 'Servicio de Habitación',
                'precio' => 8.50,
                'activo' => true
            ],
            [
                'nombre' => 'Lavandería',
                'precio' => 12.99,
                'activo' => true
            ],
            [
                'nombre' => 'Spa - Masaje Relajante',
                'precio' => 65.00,
                'activo' => true
            ],
            [
                'nombre' => 'Gimnasio',
                'precio' => 10.00,
                'activo' => true
            ],
            [
                'nombre' => 'Parking Valet',
                'precio' => 18.00,
                'activo' => true
            ],
            [
                'nombre' => 'Transporte Aeropuerto',
                'precio' => 35.00,
                'activo' => true
            ],
            [
                'nombre' => 'Tour Guiado Ciudad',
                'precio' => 28.50,
                'activo' => true
            ]
        ];

        foreach ($servicios as $servicio) {
            Servicio::create($servicio);
        }

        $this->command->info('10 servicios creados exitosamente');
    }
}
