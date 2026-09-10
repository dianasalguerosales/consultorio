<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Encargado;
use App\Models\RelacionPaciente;
use App\Models\Genero;
use App\Models\EstadoCivil;

class EncargadosSeeder extends Seeder
{
    public function run(): void
    {
        Encargado::updateOrCreate(
            ['user_id' => 3],
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Gómez',
                'fecha_nacimiento' => '1982-04-11',
                'dpi' => 1834726510101,
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
                'fecha_nacimiento' => '1986-09-27',
                'dpi' => 2495138760101,
                'telefono' => '555-6543',
                'correo' => 'ana@example.com',
                'relacion_paciente_id' => RelacionPaciente::where('nombre', 'Madre')->first()->id,
                'genero_id' => Genero::where('nombre', 'Femenino')->first()->id,
                'estado_civil_id' => EstadoCivil::where('nombre', 'Casado')->first()->id,
            ]
        );
    }
}