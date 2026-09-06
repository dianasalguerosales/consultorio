<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paciente;
use App\Models\Genero;
use App\Models\Escolaridad;
use App\Models\Encargado;

class PacientesSeeder extends Seeder
{
    public function run(): void
    {
        $masculino = Genero::where('nombre', 'Masculino')->first();
        $femenino  = Genero::where('nombre', 'Femenino')->first();
        $primaria  = Escolaridad::where('nombre', 'Primaria')->first();
        $secundaria = Escolaridad::where('nombre', 'Secundaria')->first();

        $carlos = Encargado::where('correo', 'carlos@example.com')->first();
        $ana    = Encargado::where('correo', 'ana@example.com')->first();

        Paciente::updateOrCreate(
            ['nombres' => 'Pedro', 'apellidos' => 'Ramírez'],
            [
                'escolaridad_id' => $primaria?->id,
                'genero_id'      => $masculino?->id,
                'encargado_id'   => $carlos?->id,
            ]
        );

        Paciente::updateOrCreate(
            ['nombres' => 'Lucía', 'apellidos' => 'Fernández'],
            [
                'escolaridad_id' => $secundaria?->id,
                'genero_id'      => $femenino?->id,
                'encargado_id'   => $ana?->id,
            ]
        );
    }
}