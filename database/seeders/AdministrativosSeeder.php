<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Administrativo;

class AdministrativosSeeder extends Seeder
{
    public function run(): void
    {
        Administrativo::updateOrCreate(
            ['user_id' => 1], // usuario admin@caine.com
            [
                'nombre' => 'Administrador General',
                'fecha_nacimiento' => '1980-01-01',
                'telefono' => '555-0001',
                'correo' => 'admin@caine.com',
                'direccion' => 'Oficina Central',
                'tipo' => 'administrador',
            ]
        );

        Administrativo::updateOrCreate(
            ['user_id' => 5], // otro usuario creado en UsersSeeder
            [
                'nombre' => 'María López',
                'fecha_nacimiento' => '1990-03-15',
                'telefono' => '555-0002',
                'correo' => 'maria@example.com',
                'direccion' => 'Zona 9, Guatemala',
                'tipo' => 'coordinador',
            ]
        );

        Administrativo::updateOrCreate(
            ['user_id' => 6],
            [
                'nombre' => 'José Hernández',
                'fecha_nacimiento' => '1995-07-20',
                'telefono' => '555-0003',
                'correo' => 'jose@example.com',
                'direccion' => 'Zona 1, Guatemala',
                'tipo' => 'auxiliar',
            ]
        );
    }
}

