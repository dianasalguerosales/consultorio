<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Anamnesis;
use App\Models\Criterio;
use App\Models\Diagnostico;
use App\Models\Expediente;

/**
 * Anamnesis y diagnósticos de prueba para el grafo de indicadores.
 *
 * Las respuestas no son al azar: cada diagnóstico marca como deficientes las
 * áreas que clínicamente le corresponden, para que el grafo muestre patrones
 * reconocibles en vez de ruido. Con respuestas aleatorias todos los nodos
 * quedarían conectados con todos y la vista no diría nada.
 *
 * Escala de respuesta (la que usa el formulario de anamnesis):
 *   3 = Adecuado · 2 = En desarrollo · 1 = Observación (el punto deficiente)
 */
class AnamnesisPruebaSeeder extends Seeder
{
    /**
     * Áreas que cada diagnóstico suele afectar. La primera lista sale como
     * "Observación" (1) y la segunda como "En desarrollo" (2).
     */
    private const PERFILES = [
        'TEA' => [
            'observacion' => ['Desarrollo social', 'Desarrollo del lenguaje', 'Regulación emocional'],
            'enDesarrollo' => ['Desarrollo emocional', 'Conducta en la escuela'],
        ],
        'TDAH' => [
            'observacion' => ['Atención', 'Impulsividad y autocontrol', 'Funciones ejecutivas'],
            'enDesarrollo' => ['Conducta en la escuela', 'Velocidad de procesamiento'],
        ],
        'Dislexia' => [
            'observacion' => ['Percepción visual', 'Desarrollo del lenguaje', 'Memoria'],
            'enDesarrollo' => ['Velocidad de procesamiento', 'Autoestima y autoconcepto'],
        ],
        'Trastorno del Lenguaje' => [
            'observacion' => ['Desarrollo del lenguaje', 'Desarrollo social'],
            'enDesarrollo' => ['Autoestima y autoconcepto', 'Memoria'],
        ],
        'Trastorno de Ansiedad Infantil' => [
            'observacion' => ['Ansiedad y miedos', 'Regulación emocional'],
            'enDesarrollo' => ['Autoestima y autoconcepto', 'Conducta en la escuela', 'Atención'],
        ],
        'Trastorno de Conducta' => [
            'observacion' => ['Conducta en casa', 'Conducta en la escuela', 'Impulsividad y autocontrol'],
            'enDesarrollo' => ['Regulación emocional', 'Funciones ejecutivas'],
        ],
    ];

    /**
     * Qué diagnósticos recibe cada expediente, por nombre del paciente. Algunos
     * llevan dos, que es el caso que hace interesante al grafo.
     */
    private const POR_PACIENTE = [
        'Mateo' => ['TEA', 'Trastorno del Lenguaje'],
        'Camila' => ['TDAH'],
        'Sebastián' => ['Trastorno del Lenguaje'],
        'Emilia' => ['Dislexia', 'Trastorno de Ansiedad Infantil'],
        'Joaquín' => ['TDAH', 'Trastorno de Conducta'],
        'Isabella' => ['Trastorno de Ansiedad Infantil'],
        'Santiago' => ['TEA'],
        'Pedro' => ['Trastorno del Lenguaje'],
        'Lucía' => ['Dislexia'],
    ];

    public function run(): void
    {
        $criterios = Criterio::orderBy('id')->get();

        if ($criterios->isEmpty()) {
            return;
        }

        // Criterios agrupados por área, para traducir el perfil a respuestas.
        $porArea = $criterios->groupBy('area');

        $diagnosticos = Diagnostico::pluck('id', 'nombre');

        foreach (self::POR_PACIENTE as $nombrePaciente => $nombresDiagnostico) {
            $expediente = Expediente::whereHas(
                'paciente',
                fn($q) => $q->where('nombres', $nombrePaciente)
            )->first();

            if (! $expediente) {
                continue;
            }

            // Diagnósticos del expediente.
            $ids = collect($nombresDiagnostico)
                ->map(fn($n) => $diagnosticos[$n] ?? null)
                ->filter()
                ->all();

            $expediente->diagnosticos()->sync($ids);

            // Se juntan las áreas afectadas de todos sus diagnósticos.
            $observacion = [];
            $enDesarrollo = [];

            foreach ($nombresDiagnostico as $nombre) {
                $perfil = self::PERFILES[$nombre] ?? null;
                if (! $perfil) {
                    continue;
                }

                $observacion = array_merge($observacion, $perfil['observacion']);
                $enDesarrollo = array_merge($enDesarrollo, $perfil['enDesarrollo']);
            }

            $observacion = array_unique($observacion);
            // Un área marcada como Observación por un diagnóstico no se degrada
            // a En desarrollo por otro.
            $enDesarrollo = array_diff(array_unique($enDesarrollo), $observacion);

            $anamnesis = $expediente->anamnesis_id
                ? Anamnesis::find($expediente->anamnesis_id)
                : null;

            if (! $anamnesis) {
                $anamnesis = Anamnesis::create([
                    'observaciones' => 'Anamnesis de prueba para ' . $nombrePaciente . '.',
                ]);
                $expediente->update(['anamnesis_id' => $anamnesis->id]);
            }

            // Se reescriben los items para que correr de nuevo no duplique.
            $anamnesis->items()->forceDelete();

            foreach ($criterios as $criterio) {
                $respuesta = 3;

                if (in_array($criterio->area, $observacion, true)) {
                    // Dentro de un área afectada no todo sale mal: los criterios
                    // impares quedan en Observación y el resto en desarrollo.
                    $respuesta = $criterio->numero % 2 === 1 ? 1 : 2;
                } elseif (in_array($criterio->area, $enDesarrollo, true)) {
                    $respuesta = $criterio->numero % 2 === 1 ? 2 : 3;
                }

                $anamnesis->items()->create([
                    'criterio_id' => $criterio->id,
                    'respuesta' => $respuesta,
                ]);
            }
        }
    }
}
