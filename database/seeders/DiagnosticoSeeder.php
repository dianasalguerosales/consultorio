<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Diagnostico;

class DiagnosticoSeeder extends Seeder
{
    public function run(): void
    {
        Diagnostico::updateOrCreate(['nombre' => 'Trastorno del Lenguaje'], ['descripcion' => 'Dificultades en la comunicación verbal.']);
        Diagnostico::updateOrCreate(['nombre' => 'TEA'], ['descripcion' => 'Trastorno del Espectro Autista.']);
        Diagnostico::updateOrCreate(['nombre' => 'TDAH'], ['descripcion' => 'Trastorno por Déficit de Atención e Hiperactividad.']);
    }
}