<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Expediente;
use App\Models\Paciente;
use App\Models\Diagnostico;
use App\Models\Servicio;
use App\Models\Evaluacion;
use App\Models\Escolaridad;
use App\Models\Criterio;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Expediente::with([
            'paciente',
            'diagnosticos',
            'servicios',
            'evaluaciones',
            'escolaridad',
            'estado'
        ]);

        if ($request->filled('estado_expediente_id')) {
            $query->where('estado_expediente_id', $request->estado_expediente_id);
        }

        if ($request->filled('codigo')) {
            $query->where('codigo', 'like', '%' . $request->codigo . '%');
        }

        if ($request->filled('paciente')) {
            $query->whereHas('paciente', function ($q) use ($request) {
                $q->where('nombres', 'like', '%' . $request->paciente . '%')
                  ->orWhere('apellidos', 'like', '%' . $request->paciente . '%');
            });
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_inicio', $request->fecha_inicio);
        }

        $expedientes = $query->orderBy('fecha_inicio', 'desc')->get();

        return Inertia::render('Expedientes/Index', [
            'expedientes' => $expedientes,
            'diagnosticosList' => Diagnostico::all(),
            'serviciosList' => Servicio::all(),
            'evaluacionesList' => Evaluacion::all(),
            'escolaridadesList' => Escolaridad::all(),
            'criteriosModulo1' => Criterio::where('modulo', 'Evaluación del Desarrollo Infantil')->get(),
            'criteriosModulo2' => Criterio::where('modulo', 'Evaluación Cognitiva')->get(),
            'criteriosModulo3' => Criterio::where('modulo', 'Evaluación Socioemocional')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $codigo = Expediente::generarCodigoExpediente();

        $validated = $request->validate([
            'paciente_id' => 'nullable|integer|exists:pacientes,id',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'estado_expediente_id' => 'required|integer|exists:estado_expedientes,id',
            'modalidad_id' => 'nullable|integer|exists:modalidades,id',
            'escolaridad_id' => 'nullable|integer|exists:escolaridades,id',
            'motivo_consulta' => 'nullable|string',
            'consentimiento' => 'boolean',
            'observaciones' => 'nullable|string',
            'diagnosticos' => 'array',
            'diagnosticos.*' => 'integer|exists:diagnosticos,id',
            'servicios' => 'array',
            'servicios.*' => 'integer|exists:servicios,id',
            'evaluaciones' => 'array',
            'evaluaciones.*' => 'integer|exists:evaluaciones,id',
        ]);

        $expediente = Expediente::create(array_merge($validated, [
            'codigo' => $codigo,
            'fecha_inicio' => now(),
        ]));

        $expediente->diagnosticos()->sync($request->diagnosticos ?? []);
        $expediente->servicios()->sync($request->servicios ?? []);
        $expediente->evaluaciones()->sync($request->evaluaciones ?? []);

        if ($request->filled('paciente_id')) {
            $paciente = Paciente::find($request->paciente_id);
            $paciente?->update(['expediente_id' => $expediente->id]);
        }

        return redirect()->back()->with('success', 'Expediente creado correctamente');
    }

    public function update(Request $request, Expediente $expediente)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'estado_expediente_id' => 'required|integer|exists:estado_expedientes,id',
            'modalidad_id' => 'nullable|integer|exists:modalidades,id',
            'escolaridad_id' => 'nullable|integer|exists:escolaridades,id',
            'motivo_consulta' => 'nullable|string',
            'consentimiento' => 'boolean',
            'observaciones' => 'nullable|string',
            'diagnosticos' => 'array',
            'diagnosticos.*' => 'integer|exists:diagnosticos,id',
            'servicios' => 'array',
            'servicios.*' => 'integer|exists:servicios,id',
            'evaluaciones' => 'array',
            'evaluaciones.*' => 'integer|exists:evaluaciones,id',
        ]);

        $expediente->update($validated);

        $expediente->diagnosticos()->sync($request->diagnosticos ?? []);
        $expediente->servicios()->sync($request->servicios ?? []);
        $expediente->evaluaciones()->sync($request->evaluaciones ?? []);

        return redirect()->route('expedientes.index')->with('success', 'Expediente actualizado correctamente');
    }
}