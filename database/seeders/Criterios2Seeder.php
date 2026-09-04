<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Criterio;

class Criterios2Seeder extends Seeder
{
    public function run(): void
    {
        $criterios = [
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Atención', 'numero' => 1, 'descripcion' => 'Atención sostenida en tareas largas.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Atención', 'numero' => 2, 'descripcion' => 'Atención selectiva (filtra distractores).'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Atención', 'numero' => 3, 'descripcion' => 'Atención dividida.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Atención', 'numero' => 4, 'descripcion' => 'Retoma tareas tras interrupciones.'],

            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Memoria', 'numero' => 1, 'descripcion' => 'Memoria a corto plazo.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Memoria', 'numero' => 2, 'descripcion' => 'Memoria de trabajo.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Memoria', 'numero' => 3, 'descripcion' => 'Memoria a largo plazo.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Memoria', 'numero' => 4, 'descripcion' => 'Recuerda instrucciones múltiples.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Memoria', 'numero' => 5, 'descripcion' => 'Aprende canciones/poemas con facilidad.'],

            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Funciones ejecutivas', 'numero' => 1, 'descripcion' => 'Planifica antes de actuar.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Funciones ejecutivas', 'numero' => 2, 'descripcion' => 'Organiza sus tareas.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Funciones ejecutivas', 'numero' => 3, 'descripcion' => 'Controla impulsos.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Funciones ejecutivas', 'numero' => 4, 'descripcion' => 'Flexibilidad ante cambios.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Funciones ejecutivas', 'numero' => 5, 'descripcion' => 'Monitorea sus propios errores.'],

            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Razonamiento y resolución de problemas', 'numero' => 1, 'descripcion' => 'Comprende causa y efecto.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Razonamiento y resolución de problemas', 'numero' => 2, 'descripcion' => 'Identifica semejanzas y diferencias.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Razonamiento y resolución de problemas', 'numero' => 3, 'descripcion' => 'Resuelve problemas cotidianos.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Razonamiento y resolución de problemas', 'numero' => 4, 'descripcion' => 'Pensamiento abstracto (según edad).'],

            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Percepción visual', 'numero' => 1, 'descripcion' => 'Discrimina formas y tamaños.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Percepción visual', 'numero' => 2, 'descripcion' => 'Reconoce figuras en fondos complejos.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Percepción visual', 'numero' => 3, 'descripcion' => 'Reproduce patrones visuales.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Percepción visual', 'numero' => 4, 'descripcion' => 'Orientación espacial.'],

            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Velocidad de procesamiento', 'numero' => 1, 'descripcion' => 'Completa tareas en tiempo adecuado.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Velocidad de procesamiento', 'numero' => 2, 'descripcion' => 'Responde con agilidad.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Velocidad de procesamiento', 'numero' => 3, 'descripcion' => 'Procesa información auditiva con rapidez.'],
            ['modulo' => 'Evaluación Cognitiva', 'area' => 'Velocidad de procesamiento', 'numero' => 4, 'descripcion' => 'Copia texto con velocidad adecuada.'],
        ];

        foreach ($criterios as $c) {
            Criterio::create($c);
        }
    }
}