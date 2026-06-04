<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Habitacion;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $habitaciones = [
            [
                'numero' => '101',
                'tipo' => 'individual',
                'precio_por_noche' => 89.99,
                'estado' => 'disponible',
                'activo' => true,
                'imagen' => 'habitaciones/individual_101.jpg',
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'numero' => '102',
                'tipo' => 'individual',
                'precio_por_noche' => 89.99,
                'estado' => 'disponible',
                'activo' => true,
                'imagen' => 'habitaciones/individual_102.jpg',
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'numero' => '201',
                'tipo' => 'doble',
                'precio_por_noche' => 129.99,
                'estado' => 'disponible',
                'activo' => true,
                'imagen' => 'habitaciones/doble_201.jpg',
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'numero' => '202',
                'tipo' => 'doble',
                'precio_por_noche' => 129.99,
                'estado' => 'disponible',
                'activo' => true,
                'imagen' => 'habitaciones/doble_202.jpg',
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'numero' => '301',
                'tipo' => 'suite',
                'precio_por_noche' => 249.99,
                'estado' => 'disponible',
                'activo' => true,
                'imagen' => 'habitaciones/suite_301.jpg',
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'numero' => '302',
                'tipo' => 'suite',
                'precio_por_noche' => 249.99,
                'estado' => 'mantenimiento',
                'activo' => true,
                'imagen' => 'habitaciones/suite_302.jpg',
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'numero' => '401',
                'tipo' => 'familiar',
                'precio_por_noche' => 189.99,
                'estado' => 'disponible',
                'activo' => true,
                'imagen' => 'habitaciones/familiar_401.jpg',
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'numero' => '402',
                'tipo' => 'familiar',
                'precio_por_noche' => 189.99,
                'estado' => 'disponible',
                'activo' => true,
                'imagen' => 'habitaciones/familiar_402.jpg',
                'creado_en' => now(),
                'actualizado_en' => now()
            ]
        ];

        foreach ($habitaciones as $habitacion) {
            Habitacion::create($habitacion);
        }

        $this->command->info('8 habitaciones creadas exitosamente');
    }
}
