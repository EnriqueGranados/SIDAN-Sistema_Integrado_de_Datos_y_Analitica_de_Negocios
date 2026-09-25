<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserSeederTest extends Seeder
{
    public function run(): void
    {
        $idRolUsuario = 3;

        $usersData = [
            [
                'nombres' => 'María',
                'apellidos' => 'González Pérez',
                'correo' => 'maria.gonzalez@sidan.com',
                'documento' => '01234570',
                'telefono' => '74234567',
                'fecha_nacimiento' => '1995-03-15',
                'genero' => 'F',
                'password' => 'password123',
            ],
            [
                'nombres' => 'Juan',
                'apellidos' => 'Ramírez Torres',
                'correo' => 'juan.ramirez@sidan.com',
                'documento' => '01234571',
                'telefono' => '75234567',
                'fecha_nacimiento' => '1990-07-22',
                'genero' => 'M',
                'password' => 'password123',
            ],
            [
                'nombres' => 'Pedro',
                'apellidos' => 'Sánchez López',
                'correo' => 'pedro.sanchez@sidan.com',
                'documento' => '01234572',
                'telefono' => '76234567',
                'fecha_nacimiento' => '1988-11-08',
                'genero' => 'M',
                'password' => 'password123',
            ],
            [
                'nombres' => 'Laura',
                'apellidos' => 'Díaz Martínez',
                'correo' => 'laura.diaz@sidan.com',
                'documento' => '01234573',
                'telefono' => '77234567',
                'fecha_nacimiento' => '1992-05-30',
                'genero' => 'F',
                'password' => 'password123',
            ],
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Vega Ruiz',
                'correo' => 'carlos.vega@sidan.com',
                'documento' => '01234574',
                'telefono' => '78234567',
                'fecha_nacimiento' => '1985-09-12',
                'genero' => 'M',
                'password' => 'password123',
            ],
        ];

        foreach ($usersData as $userData) {
            // 1. Crear información personal (Agregamos 'id_informacion_personal' como segundo parámetro)
            $infoPersonalId = DB::table('informacion_personal')->insertGetId([
                'nombres' => $userData['nombres'],
                'apellidos' => $userData['apellidos'],
                'documento' => $userData['documento'],
                'telefono' => $userData['telefono'],
                'fecha_nacimiento' => $userData['fecha_nacimiento'],
                'genero' => $userData['genero'],
                'created_at' => now(),
                'updated_at' => now(),
            ], 'id_informacion_personal'); // <--- AQUÍ ESTÁ LA CORRECCIÓN

            // 2. Crear usuario con rol ID 3 (usuario)
            DB::table('usuarios')->insert([
                'id_informacion_personal' => $infoPersonalId,
                'id_rol' => $idRolUsuario,
                'correo' => $userData['correo'],
                'password_hash' => Hash::make($userData['password']),
                'must_change_password' => false,
                'estado' => 'activo',
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ Se crearon 5 usuarios con rol de usuario normal correctamente.');
    }
}