<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Diagnostico;

class DiagnosticoSeeder extends Seeder
{
    public function run(): void
    {
        $diagnosticos = [
            ['nombre' => 'Trastorno del Lenguaje', 'descripcion' => 'Dificultades en la comunicación verbal.'],
            ['nombre' => 'TEA', 'descripcion' => 'Trastorno del Espectro Autista.'],
            ['nombre' => 'TDAH', 'descripcion' => 'Trastorno por Déficit de Atención e Hiperactividad.'],
            ['nombre' => 'Dislexia', 'descripcion' => 'Dificultades específicas en la lectura.'],
            ['nombre' => 'Trastorno de Ansiedad Infantil', 'descripcion' => 'Ansiedad excesiva que afecta el desarrollo.'],
            ['nombre' => 'Trastorno de Conducta', 'descripcion' => 'Patrones persistentes de comportamiento disruptivo.'],
        ];

        foreach ($diagnosticos as $d) {
            Diagnostico::updateOrCreate(
                ['nombre' => $d['nombre']],
                ['descripcion' => $d['descripcion']]
            );
        }
    }
}