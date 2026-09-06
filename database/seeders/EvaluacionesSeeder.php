<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Evaluacion;

class EvaluacionesSeeder extends Seeder
{
    public function run(): void
    {
        $evaluaciones = [
            ['nombre' => 'FFPI-C', 'descripcion' => 'Inventario de Personalidad para niños.', 'activo' => 1],
            ['nombre' => 'Perfil Sensorial 2 Escolar', 'descripcion' => 'Evaluación sensorial en contexto escolar.', 'activo' => 1],
            ['nombre' => 'Perfil Sensorial 2 Niño', 'descripcion' => 'Evaluación sensorial en niños pequeños.', 'activo' => 1],
            ['nombre' => 'PHAI', 'descripcion' => 'Prueba de Habilidades de Aprendizaje Infantil.', 'activo' => 1],
            ['nombre' => 'Registro del Río', 'descripcion' => 'Instrumento de observación del desarrollo.', 'activo' => 1],
            ['nombre' => 'Escala de Desarrollo Infantil', 'descripcion' => 'Medición global del desarrollo infantil.', 'activo' => 1],
            ['nombre' => 'Prueba de Vocabulario', 'descripcion' => 'Evaluación del lenguaje expresivo y receptivo.', 'activo' => 1],
            ['nombre' => 'WISC-IV', 'descripcion' => 'Escala de Inteligencia de Wechsler para niños.', 'activo' => 1],
            ['nombre' => 'BASC-3', 'descripcion' => 'Sistema de evaluación de conducta infantil.', 'activo' => 1],
            ['nombre' => 'Conners 3', 'descripcion' => 'Evaluación de TDAH y problemas asociados.', 'activo' => 1],
        ];

        foreach ($evaluaciones as $e) {
            Evaluacion::updateOrCreate(
                ['nombre' => $e['nombre']],
                ['descripcion' => $e['descripcion'], 'activo' => $e['activo']]
            );
        }
    }
}