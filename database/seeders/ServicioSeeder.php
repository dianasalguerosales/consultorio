<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['nombre' => 'Terapia de Lenguaje', 'descripcion' => 'Intervención en dificultades del habla y comunicación.', 'activo' => 1],
            ['nombre' => 'Terapia Ocupacional', 'descripcion' => 'Apoyo en habilidades motoras finas y actividades de la vida diaria.', 'activo' => 1],
            ['nombre' => 'Evaluación Psicológica', 'descripcion' => 'Valoración cognitiva y emocional del paciente.', 'activo' => 1],
            ['nombre' => 'Psicología Infantil', 'descripcion' => 'Atención psicológica especializada en niños y adolescentes.', 'activo' => 1],
            ['nombre' => 'Evaluación Cognitiva', 'descripcion' => 'Pruebas para medir memoria, atención y razonamiento.', 'activo' => 1],
        ];

        foreach ($servicios as $s) {
            Servicio::updateOrCreate(
                ['nombre' => $s['nombre']],
                ['descripcion' => $s['descripcion'], 'activo' => $s['activo']]
            );
        }
    }
}