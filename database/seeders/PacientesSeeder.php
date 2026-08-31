<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Paciente;

class PacientesSeeder extends Seeder
{
    public function run(): void
    {
        Paciente::updateOrCreate(
            ['nombre' => 'Pedro Ramírez'],
            [
                'fecha_nacimiento' => '2010-09-01',
                'telefono' => '555-1111',
                'direccion' => 'Zona 10, Guatemala',
                'genero' => 'masculino',
                'expediente_id' => 'KID-2026001',
            ]
        );

        Paciente::updateOrCreate(
            ['nombre' => 'Lucía Fernández'],
            [
                'fecha_nacimiento' => '2012-05-12',
                'telefono' => '555-2222',
                'direccion' => 'Zona 15, Guatemala',
                'genero' => 'femenino',
                'expediente_id' => 'KID-2026002',
            ]
        );
    }
}