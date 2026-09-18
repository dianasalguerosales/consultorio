<?php

namespace App\Http\Controllers;

use App\Models\ObjetivoTerapeutico;
use App\Models\Paciente;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Los objetivos terapéuticos de cada niño, agrupados por servicio.
 *
 * El servicio hace de área: se plantean de tres a cuatro objetivos por terapia.
 * Viven fuera del expediente porque el expediente se llena una vez al ingreso y
 * los objetivos se revisan durante todo el tratamiento.
 */
class ObjetivoTerapeuticoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $puedeGestionar = $user->can('gestionar evaluaciones');

        $objetivos = $this->conAlcanceDe(
            ObjetivoTerapeutico::with(['paciente.expediente:id,paciente_id,codigo', 'servicio', 'terapeuta']),
            $user
        )->get();

        // Una fila por par niño-servicio, con sus objetivos adentro: es como se
        // leen y como se asignan.
        $filas = $objetivos
            ->groupBy(fn(ObjetivoTerapeutico $o) => $o->paciente_id . '-' . $o->servicio_id)
            ->map(function ($grupo) {
                $primero = $grupo->first();

                return [
                    'clave' => $primero->paciente_id . '-' . $primero->servicio_id,
                    'paciente_id' => $primero->paciente_id,
                    'paciente' => $primero->paciente?->nombre_completo,
                    'codigo' => $primero->paciente?->expediente?->codigo,
                    'servicio_id' => $primero->servicio_id,
                    'servicio' => $primero->servicio?->nombre,
                    'terapeuta' => $primero->terapeuta?->nombre_completo,
                    'objetivos' => $grupo->map(fn(ObjetivoTerapeutico $o) => [
                        'id' => $o->id,
                        'descripcion' => $o->descripcion,
                    ])->values(),
                ];
            })
            ->sortBy([['paciente', 'asc'], ['servicio', 'asc']])
            ->values();

        return Inertia::render('Objetivos', [
            'filas' => $filas,
            'puedeGestionar' => $puedeGestionar,
            'minimo' => ObjetivoTerapeutico::MINIMO_POR_SERVICIO,
            'maximo' => ObjetivoTerapeutico::MAXIMO_POR_SERVICIO,

            // Los catálogos del formulario no viajan a quien solo consulta: son
            // todos los pacientes del consultorio.
            'pacientes' => $puedeGestionar
                ? Paciente::with('expediente:id,paciente_id,codigo')->orderBy('apellidos')->get()
                    ->map(fn(Paciente $p) => [
                        'id' => $p->id,
                        'nombre' => $p->nombre_completo,
                        'codigo' => $p->expediente?->codigo,
                    ])
                : [],

            'servicios' => $puedeGestionar
                ? Servicio::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre'])
                : [],
        ]);
    }

    /**
     * Guarda los objetivos de un niño en un servicio, de una sola vez.
     *
     * Se reemplaza el grupo completo en vez de ir uno por uno: en pantalla se
     * editan las tres o cuatro líneas juntas, y así lo que queda guardado es
     * exactamente lo que se ve.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'servicio_id' => 'required|integer|exists:servicios,id',
            'objetivos' => 'required|array|min:1|max:' . ObjetivoTerapeutico::MAXIMO_POR_SERVICIO,
            'objetivos.*' => 'required|string|max:2000',
        ], [
            'objetivos.max' => 'Son como máximo ' . ObjetivoTerapeutico::MAXIMO_POR_SERVICIO
                . ' objetivos por terapia.',
            'objetivos.required' => 'Escriba al menos un objetivo.',
        ]);

        $paciente = Paciente::findOrFail($datos['paciente_id']);
        $terapeutaId = $request->user()->terapeuta?->id;

        ObjetivoTerapeutico::where('paciente_id', $paciente->id)
            ->where('servicio_id', $datos['servicio_id'])
            ->delete();

        foreach ($datos['objetivos'] as $descripcion) {
            $paciente->objetivos()->create([
                'servicio_id' => $datos['servicio_id'],
                'descripcion' => trim($descripcion),
                'terapeuta_id' => $terapeutaId,
            ]);
        }

        return redirect()
            ->route('objetivos.index')
            ->with('success', 'Objetivos guardados.');
    }

    /** Borra el grupo entero de un niño en un servicio. */
    public function destroy(Request $request)
    {
        $datos = $request->validate([
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'servicio_id' => 'required|integer|exists:servicios,id',
        ]);

        $borrados = ObjetivoTerapeutico::where('paciente_id', $datos['paciente_id'])
            ->where('servicio_id', $datos['servicio_id'])
            ->delete();

        if (! $borrados) {
            throw ValidationException::withMessages([
                'paciente_id' => 'Ese niño no tiene objetivos en esa terapia.',
            ]);
        }

        return redirect()
            ->route('objetivos.index')
            ->with('success', 'Objetivos eliminados.');
    }

    /**
     * El encargado ve solo los objetivos de sus hijos. El resto del personal ve
     * todos — el mismo corte que usa el récord de Evaluaciones.
     */
    private function conAlcanceDe($query, User $user)
    {
        if (! $user->hasRole('encargado')) {
            return $query;
        }

        $encargado = $user->encargado;

        if (! $encargado) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('paciente', fn($q) => $q->where('encargado_id', $encargado->id));
    }
}
