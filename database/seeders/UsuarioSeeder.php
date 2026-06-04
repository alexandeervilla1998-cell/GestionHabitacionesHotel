<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'nombre' => 'Carlos Rodríguez',
                'correo' => 'carlos.rodriguez@example.com',
                'password' => Hash::make('password123'),
                'rol' => 'admin',
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'nombre' => 'María González',
                'correo' => 'maria.gonzalez@example.com',
                'password' => Hash::make('password123'),
                'rol' => 'recepcionista',
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now()
            ],
            [
                'nombre' => 'Luis Sánchez',
                'correo' => 'luis.sanchez@example.com',
                'password' => Hash::make('password123'),
                'rol' => 'recepcionista',
                'activo' => true,
                'creado_en' => now(),
                'actualizado_en' => now()
            ]
        ];

        foreach ($usuarios as $usuario) {
            Usuario::create($usuario);
        }

        $this->command->info('3 usuarios creados exitosamente (1 admin, 2 recepcionistas)');
    }
}
