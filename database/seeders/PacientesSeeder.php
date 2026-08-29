<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Paciente;

class PacientesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Paciente::updateOrCreate(
            ['expediente_id' => 'KID-261001'],
            [
                'nombre' => 'Pedro Ramírez',
                'fecha_nacimiento' => '2010-09-01',
                'telefono' => '555-4444',
                'direccion' => 'Zona 11, Guatemala',
                'genero' => 'Masculino',
            ]
        );

        Paciente::updateOrCreate(
            ['expediente_id' => 'KID-262001'],
            [
                'nombre' => 'Lucía Fernández',
                'fecha_nacimiento' => '2012-05-12',
                'telefono' => '555-5555',
                'direccion' => 'Zona 16, Guatemala',
                'genero' => 'Femenino',
            ]
        );
    }
}
