<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Criterio;

class Criterios3Seeder extends Seeder
{
    public function run(): void
    {
        $criterios = [
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Regulación emocional', 'numero' => 1, 'descripcion' => 'Identifica sus emociones.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Regulación emocional', 'numero' => 2, 'descripcion' => 'Expresa emociones adecuadamente.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Regulación emocional', 'numero' => 3, 'descripcion' => 'Se calma tras momentos de alteración.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Regulación emocional', 'numero' => 4, 'descripcion' => 'Tolera la frustración.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Regulación emocional', 'numero' => 5, 'descripcion' => 'Pide ayuda cuando la necesita.'],

            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Autoestima y autoconcepto', 'numero' => 1, 'descripcion' => 'Se reconoce capaz.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Autoestima y autoconcepto', 'numero' => 2, 'descripcion' => 'Acepta sus errores.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Autoestima y autoconcepto', 'numero' => 3, 'descripcion' => 'Se valora positivamente.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Autoestima y autoconcepto', 'numero' => 4, 'descripcion' => 'Confía en sus habilidades.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Autoestima y autoconcepto', 'numero' => 5, 'descripcion' => 'Se compara negativamente con otros.'],

            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Ansiedad y miedos', 'numero' => 1, 'descripcion' => 'Presenta miedos específicos.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Ansiedad y miedos', 'numero' => 2, 'descripcion' => 'Ansiedad anticipatoria.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Ansiedad y miedos', 'numero' => 3, 'descripcion' => 'Síntomas físicos de ansiedad (dolor de estómago/cabeza).'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Ansiedad y miedos', 'numero' => 4, 'descripcion' => 'Evita situaciones por miedo.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Ansiedad y miedos', 'numero' => 5, 'descripcion' => 'Pesadillas frecuentes.'],

            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en casa', 'numero' => 1, 'descripcion' => 'Sigue normas y límites.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en casa', 'numero' => 2, 'descripcion' => 'Respeta la autoridad.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en casa', 'numero' => 3, 'descripcion' => 'Presenta rabietas/berrinches.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en casa', 'numero' => 4, 'descripcion' => 'Agresividad física o verbal.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en casa', 'numero' => 5, 'descripcion' => 'Oposicionismo.'],

            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en la escuela', 'numero' => 1, 'descripcion' => 'Sigue instrucciones del docente.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en la escuela', 'numero' => 2, 'descripcion' => 'Respeta a sus compañeros.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en la escuela', 'numero' => 3, 'descripcion' => 'Mantiene conducta adecuada en grupo.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en la escuela', 'numero' => 4, 'descripcion' => 'Presenta conflictos frecuentes.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Conducta en la escuela', 'numero' => 5, 'descripcion' => 'Es excluido/a por pares.'],

            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Impulsividad y autocontrol', 'numero' => 1, 'descripcion' => 'Actúa sin pensar.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Impulsividad y autocontrol', 'numero' => 2, 'descripcion' => 'Interrumpe constantemente.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Impulsividad y autocontrol', 'numero' => 3, 'descripcion' => 'Dificultad para esperar turnos.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Impulsividad y autocontrol', 'numero' => 4, 'descripcion' => 'Reacciones desproporcionadas.'],
            ['modulo' => 'Evaluación Socioemocional', 'area' => 'Impulsividad y autocontrol', 'numero' => 5, 'descripcion' => 'Se arrepiente de sus acciones.'],
        ];

        foreach ($criterios as $c) {
            Criterio::updateOrCreate(
                [
                    'modulo' => $c['modulo'],
                    'area' => $c['area'],
                    'numero' => $c['numero'],
                ],
                ['descripcion' => $c['descripcion']]
            );
        }
    }
}
