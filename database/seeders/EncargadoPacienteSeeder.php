<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Encargado;
use App\Models\Genero;
use App\Models\RelacionPaciente;
use App\Models\EstadoCivil;

class EncargadoPacienteSeeder extends Seeder
{
    public function run(): void
    {
        Encargado::updateOrCreate(
            ['user_id' => 3],
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Gómez',
                'telefono' => '555-9876',
                'correo' => 'carlos@example.com',
                'relacion_paciente_id' => RelacionPaciente::where('nombre', 'Padre')->first()->id,
                'genero_id' => Genero::where('nombre', 'Masculino')->first()->id,
                'estado_civil_id' => EstadoCivil::where('nombre', 'Casado')->first()->id,
            ]
        );

        Encargado::updateOrCreate(
            ['user_id' => 4],
            [
                'nombres' => 'Ana',
                'apellidos' => 'Torres',
                'telefono' => '555-6543',
                'correo' => 'ana@example.com',
                'relacion_paciente_id' => RelacionPaciente::where('nombre', 'Madre')->first()->id,
                'genero_id' => Genero::where('nombre', 'Femenino')->first()->id,
                'estado_civil_id' => EstadoCivil::where('nombre', 'Casado')->first()->id,
            ]
        );
    }
}