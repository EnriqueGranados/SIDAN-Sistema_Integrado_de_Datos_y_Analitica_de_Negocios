<?php

namespace Database\Seeders;

use App\Models\InformacionPersonal;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = Rol::where('nombre', 'superadmin')->first();
        $admin = Rol::where('nombre', 'admin')->first();

        $personaSuperadmin = InformacionPersonal::firstOrCreate(
            ['documento' => '00000000-0'],
            [
                'nombres' => 'Super',
                'apellidos' => 'Administrador',
                'telefono' => null,
                'fecha_nacimiento' => null,
                'genero' => null,
            ]
        );

        User::firstOrCreate(
            ['correo' => 'superadmin@gema.test'],
            [
                'id_informacion_personal' => $personaSuperadmin->id_informacion_personal,
                'id_rol' => $superadmin->id_rol,
                'password_hash' => Hash::make('Password123!'),
                'estado' => 'activo',
                'must_change_password' => true,
            ]
        );

        $personaAdmin = InformacionPersonal::firstOrCreate(
            ['documento' => '00000000-1'],
            [
                'nombres' => 'Administrador',
                'apellidos' => 'General',
                'telefono' => null,
                'fecha_nacimiento' => null,
                'genero' => null,
            ]
        );

        User::firstOrCreate(
            ['correo' => 'admin@gema.test'],
            [
                'id_informacion_personal' => $personaAdmin->id_informacion_personal,
                'id_rol' => $admin->id_rol,
                'password_hash' => Hash::make('Password123!'),
                'estado' => 'activo',
                'must_change_password' => true,
            ]
        );
    }
}