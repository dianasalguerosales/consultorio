<?php

namespace App\Http\Controllers;

use App\Models\Evaluacion;
use App\Models\Expediente;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EvaluacionesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $puedeAplicar = $user->can('gestionar pacientes');

        // El récord sale del pivote expediente_evaluacion, que es lo único que
        // hoy registra una aplicación. Las respuestas, la interpretación y los
        // resultados llegan cuando existan sus tablas.
        $aplicadas = $this->conAlcanceDe(
            Expediente::with(['paciente', 'evaluaciones'])->has('evaluaciones'),
            $user
        )
            ->get()
            ->flatMap(fn(Expediente $exp) => $exp->evaluaciones->map(fn($ev) => [
                'id' => $exp->id . '-' . $ev->id,
                'expediente_id' => $exp->id,
                'codigo' => $exp->codigo,
                'paciente' => $exp->paciente?->nombre_completo
                    ?? trim("{$exp->nombres} {$exp->apellidos}"),
                'evaluacion' => $ev->nombre,
                'evaluacion_id' => $ev->id,
                'fecha' => optional($ev->pivot->created_at)->toDateString(),
            ]))
            ->sortByDesc('fecha')
            ->values();

        return Inertia::render('Evaluaciones', [
            'aplicadas' => $aplicadas,
            'evaluaciones' => Evaluacion::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'descripcion']),
            'puedeAplicar' => $puedeAplicar,

            // La lista de expedientes es para el formulario de aplicar, así que
            // no viaja a quien solo consulta: son todos los pacientes.
            'expedientes' => $puedeAplicar
                ? Expediente::with('paciente')->orderBy('codigo')->get()
                    ->map(fn(Expediente $e) => [
                        'id' => $e->id,
                        'codigo' => $e->codigo,
                        'paciente' => $e->paciente?->nombre_completo ?? trim("{$e->nombres} {$e->apellidos}"),
                    ])
                : [],
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'expediente_id' => 'required|integer|exists:expedientes,id',
            'evaluacion_id' => 'required|integer|exists:evaluaciones,id',
        ]);

        $expediente = Expediente::findOrFail($datos['expediente_id']);

        // syncWithoutDetaching y no attach: el pivote tiene unique en el par, y
        // aplicar dos veces la misma no debe tumbar la petición.
        $expediente->evaluaciones()->syncWithoutDetaching([$datos['evaluacion_id']]);

        return redirect()
            ->route('evaluaciones.index')
            ->with('success', 'Evaluación aplicada.');
    }

    /**
     * El encargado ve solo las evaluaciones de sus hijos. El resto del personal
     * ve todas: son los que tienen 'gestionar pacientes' o el rol pruebas.
     */
    private function conAlcanceDe($query, User $user)
    {
        if (! $user->hasRole('encargado')) {
            return $query;
        }

        $encargado = $user->encargado;

        // Sin ficha de encargado no hay hijos que mostrar, y sin este corte la
        // consulta traería los expedientes de todo el consultorio.
        if (! $encargado) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('paciente', fn($q) => $q->where('encargado_id', $encargado->id));
    }
}
