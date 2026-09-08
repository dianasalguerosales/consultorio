<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndicadoresController extends Controller
{
    /**
     * Grafo que amarra cada diagnóstico con las áreas que salieron deficientes
     * en la anamnesis de los expedientes que lo tienen.
     *
     * Escala de la anamnesis: 3 = Adecuado · 2 = En desarrollo · 1 = Observación.
     * "Deficiente" es 1; con el filtro amplio entra también el 2.
     */
    public function index(Request $request)
    {
        // 'observacion' cuenta solo lo que salió en Observación; 'ambos' suma
        // también lo que está En desarrollo.
        $nivel = $request->input('nivel') === 'ambos' ? 'ambos' : 'observacion';
        $respuestas = $nivel === 'ambos' ? [1, 2] : [1];

        $expedientes = Expediente::query()
            ->with([
                'paciente',
                'diagnosticos',
                'anamnesis.items' => fn($q) => $q->whereIn('respuesta', $respuestas),
                'anamnesis.items.criterio',
            ])
            ->whereHas('diagnosticos')
            ->whereHas('anamnesis')
            ->get();

        // Se recorre expediente por expediente: cada uno aporta un vínculo entre
        // cada diagnóstico suyo y cada área deficiente suya.
        $diagnosticos = [];   // nombre => ['expedientes' => [...]]
        $areas = [];          // nombre => ['modulo' => ..., 'expedientes' => [...]]
        $vinculos = [];       // "dx|area" => ['expedientes' => [...]]
        $criteriosPorArea = []; // area => [criterioId => ['descripcion','pacientes'=>[]]]

        foreach ($expedientes as $expediente) {
            $itemsDeficientes = $expediente->anamnesis?->items ?? collect();

            // Áreas deficientes de este expediente, sin repetir.
            $areasDelExpediente = $itemsDeficientes
                ->filter(fn($item) => $item->criterio)
                ->groupBy(fn($item) => $item->criterio->area);

            if ($areasDelExpediente->isEmpty() || $expediente->diagnosticos->isEmpty()) {
                continue;
            }

            $paciente = $expediente->paciente?->nombre_completo ?? $expediente->nombres;

            foreach ($expediente->diagnosticos as $diagnostico) {
                $diagnosticos[$diagnostico->nombre] ??= ['expedientes' => []];
                $diagnosticos[$diagnostico->nombre]['expedientes'][] = $paciente;

                foreach ($areasDelExpediente as $area => $items) {
                    $areas[$area] ??= [
                        'modulo' => $items->first()->criterio->modulo,
                        'expedientes' => [],
                    ];
                    $areas[$area]['expedientes'][] = $paciente;

                    $clave = $diagnostico->nombre . '|' . $area;
                    $vinculos[$clave] ??= ['expedientes' => []];
                    $vinculos[$clave]['expedientes'][] = $paciente;
                }
            }

            // Criterios concretos que fallaron, para el panel de detalle.
            foreach ($itemsDeficientes as $item) {
                if (! $item->criterio) {
                    continue;
                }

                $area = $item->criterio->area;
                $criteriosPorArea[$area][$item->criterio->id] ??= [
                    'descripcion' => $item->criterio->descripcion,
                    'numero' => $item->criterio->numero,
                    'pacientes' => [],
                ];
                $criteriosPorArea[$area][$item->criterio->id]['pacientes'][] = [
                    'nombre' => $paciente,
                    'respuesta' => (int) $item->respuesta,
                ];
            }
        }

        // Ids estables y seguros: los nombres de área traen acentos y espacios.
        $idArea = [];
        foreach (array_keys($areas) as $i => $nombre) {
            $idArea[$nombre] = 'area-' . $i;
        }

        $idDiagnostico = [];
        foreach (array_keys($diagnosticos) as $i => $nombre) {
            $idDiagnostico[$nombre] = 'dx-' . $i;
        }

        $nodos = [];

        foreach ($diagnosticos as $nombre => $datos) {
            $pacientes = array_values(array_unique($datos['expedientes']));

            $nodos[] = [
                'id' => $idDiagnostico[$nombre],
                'tipo' => 'diagnostico',
                'etiqueta' => $nombre,
                'expedientes' => count($pacientes),
                'pacientes' => $pacientes,
            ];
        }

        foreach ($areas as $nombre => $datos) {
            $pacientes = array_values(array_unique($datos['expedientes']));

            $nodos[] = [
                'id' => $idArea[$nombre],
                'tipo' => 'area',
                'etiqueta' => $nombre,
                'modulo' => $datos['modulo'],
                'expedientes' => count($pacientes),
                'pacientes' => $pacientes,
                'criterios' => collect($criteriosPorArea[$nombre] ?? [])
                    ->map(fn($c) => [
                        'numero' => $c['numero'],
                        'descripcion' => $c['descripcion'],
                        // Un mismo criterio puede fallarle a varios pacientes.
                        'pacientes' => collect($c['pacientes'])
                            ->unique(fn($p) => $p['nombre'] . '|' . $p['respuesta'])
                            ->sortBy('nombre')
                            ->values(),
                    ])
                    ->sortBy('numero')
                    ->values(),
            ];
        }

        $aristas = [];

        foreach ($vinculos as $clave => $datos) {
            [$nombreDx, $nombreArea] = explode('|', $clave, 2);
            $pacientes = array_values(array_unique($datos['expedientes']));

            $aristas[] = [
                'id' => 'e-' . $idDiagnostico[$nombreDx] . '-' . $idArea[$nombreArea],
                'origen' => $idDiagnostico[$nombreDx],
                'destino' => $idArea[$nombreArea],
                // El peso es en cuántos expedientes coinciden: engrosa la línea.
                'peso' => count($pacientes),
                'pacientes' => $pacientes,
            ];
        }

        return Inertia::render('Indicadores', [
            'grafo' => [
                'nodos' => $nodos,
                'aristas' => $aristas,
            ],

            'nivel' => $nivel,

            'resumen' => [
                'expedientes' => $expedientes->count(),
                'diagnosticos' => count($diagnosticos),
                'areas' => count($areas),
                'vinculos' => count($vinculos),
            ],
        ]);
    }
}
