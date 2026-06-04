<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsuarioSeeder::class,
            ClienteSeeder::class,
            HabitacionSeeder::class,
            ServicioSeeder::class,
        ]);

        $this->command->info('Todos los seeders han sido ejecutados exitosamente');
        $this->command->info('Datos de prueba creados:');
        $this->command->info('- 3 usuarios (1 admin, 2 recepcionistas)');
        $this->command->info('- 5 clientes');
        $this->command->info('- 8 habitaciones (2 individuales, 2 dobles, 2 suites, 2 familiares)');
        $this->command->info('- 10 servicios de hotel');
    }
}
