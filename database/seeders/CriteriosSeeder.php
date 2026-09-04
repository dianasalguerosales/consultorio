<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Criterio;

class CriteriosSeeder extends Seeder
{
    public function run(): void
    {
        $criterios = [
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor grueso', 'numero' => 1, 'descripcion' => 'Equilibrio y coordinación.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor grueso', 'numero' => 2, 'descripcion' => 'Corre y salta adecuadamente.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor grueso', 'numero' => 3, 'descripcion' => 'Sube y baja escaleras.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor grueso', 'numero' => 4, 'descripcion' => 'Lanza y atrapa objetos.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor grueso', 'numero' => 5, 'descripcion' => 'Postura corporal adecuada.'],

            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor fino', 'numero' => 1, 'descripcion' => 'Manejo del lápiz/tijera.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor fino', 'numero' => 2, 'descripcion' => 'Recorta y pega.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor fino', 'numero' => 3, 'descripcion' => 'Abrocha botones/cierre.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor fino', 'numero' => 4, 'descripcion' => 'Dibuja figuras reconocibles.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo motor fino', 'numero' => 5, 'descripcion' => 'Coordinación ojo-mano.'],

            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo del lenguaje', 'numero' => 1, 'descripcion' => 'Vocabulario adecuado para la edad.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo del lenguaje', 'numero' => 2, 'descripcion' => 'Forma frases completas.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo del lenguaje', 'numero' => 3, 'descripcion' => 'Comprende instrucciones.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo del lenguaje', 'numero' => 4, 'descripcion' => 'Narra experiencias.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo del lenguaje', 'numero' => 5, 'descripcion' => 'Pronunciación clara.'],

            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo cognitivo', 'numero' => 1, 'descripcion' => 'Reconoce colores/formas/números.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo cognitivo', 'numero' => 2, 'descripcion' => 'Comprende conceptos básicos.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo cognitivo', 'numero' => 3, 'descripcion' => 'Memoria adecuada.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo cognitivo', 'numero' => 4, 'descripcion' => 'Resuelve problemas simples.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo cognitivo', 'numero' => 5, 'descripcion' => 'Atención sostenida.'],

            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo social', 'numero' => 1, 'descripcion' => 'Juega con otros niños.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo social', 'numero' => 2, 'descripcion' => 'Comparte y coopera.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo social', 'numero' => 3, 'descripcion' => 'Respeta normas de juego.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo social', 'numero' => 4, 'descripcion' => 'Hace amigos con facilidad.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo social', 'numero' => 5, 'descripcion' => 'Resuelve conflictos adecuadamente.'],

            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo emocional', 'numero' => 1, 'descripcion' => 'Identifica emociones básicas.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo emocional', 'numero' => 2, 'descripcion' => 'Expresa afecto.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo emocional', 'numero' => 3, 'descripcion' => 'Regula sus emociones.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo emocional', 'numero' => 4, 'descripcion' => 'Muestra empatía.'],
            ['modulo' => 'Evaluación del Desarrollo Infantil', 'area' => 'Desarrollo emocional', 'numero' => 5, 'descripcion' => 'Autoestima adecuada para la edad.'],
        ];

        foreach ($criterios as $c) {
            Criterio::create($c);
        }
    }
}