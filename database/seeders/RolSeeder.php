<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        Rol::firstOrCreate(
            ['nombre' => 'superadmin'],
            ['descripcion' => 'Administrador principal del sistema']
        );

        Rol::firstOrCreate(
            ['nombre' => 'admin'],
            ['descripcion' => 'Administrador del sistema']
        );

        Rol::firstOrCreate(
            ['nombre' => 'usuario'],
            ['descripcion' => 'Usuario normal del sistema']
        );
    }
}