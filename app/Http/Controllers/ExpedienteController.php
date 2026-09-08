<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Expediente;
use App\Models\Escolaridad;
use App\Models\Criterio;
use App\Models\Diagnostico;
use App\Models\Servicio;
use App\Models\Modalidad;
use App\Models\EstadoExpediente;
use App\Models\Evaluacion;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Expediente::with([
            'paciente.genero',
            'estado',
            'modalidad',
            'anamnesis',
            // El modal de anamnesis muestra cada criterio con su respuesta, así
            // que hacen falta los items y su criterio, no solo la cabecera.
            'anamnesis.items.criterio',
            'diagnosticos',
            'servicios',
            'evaluaciones'
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

        return Inertia::render('Expedientes', [
            'expedientes' => $expedientes,
            'escolaridadesList' => Escolaridad::all(),
            'diagnosticosList' => Diagnostico::all(),
            'serviciosList' => Servicio::all(),
            'modalidadesList' => Modalidad::where('activo', true)->get(),
            'estadoExpedientes' => EstadoExpediente::all(),
            'evaluacionesList' => Evaluacion::all(),
            'criteriosModulo1' => Criterio::where('modulo', 'Evaluación del Desarrollo Infantil')->get(),
            'criteriosModulo2' => Criterio::where('modulo', 'Evaluación Cognitiva')->get(),
            'criteriosModulo3' => Criterio::where('modulo', 'Evaluación Socioemocional')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $codigo = Expediente::generarCodigoExpediente();

        $validated = $request->validate([
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'estado_expediente_id' => 'required|integer|exists:estado_expedientes,id',
            'modalidad_id' => 'nullable|integer|exists:modalidades,id',
            'anamnesis_id' => 'nullable|integer|exists:anamnesis,id',
            'diagnosticos' => 'nullable|array',
            'diagnosticos.*' => 'integer|exists:diagnosticos,id',
            'servicios' => 'nullable|array',
            'servicios.*' => 'integer|exists:servicios,id',
            'evaluaciones' => 'nullable|array',
            'evaluaciones.*' => 'integer|exists:evaluaciones,id',
            'motivo_consulta' => 'nullable|string',
            'consentimiento' => 'boolean',
            'observaciones' => 'nullable|string',
        ]);

        $expediente = Expediente::create(array_merge($validated, [
            'codigo' => $codigo,
            'fecha_inicio' => now(),
        ]));

        $expediente->diagnosticos()->sync($request->diagnosticos ?? []);
        $expediente->servicios()->sync($request->servicios ?? []);
        $expediente->evaluaciones()->sync($request->evaluaciones ?? []);

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
            'anamnesis_id' => 'nullable|integer|exists:anamnesis,id',
            'diagnosticos' => 'nullable|array',
            'diagnosticos.*' => 'integer|exists:diagnosticos,id',
            'servicios' => 'nullable|array',
            'servicios.*' => 'integer|exists:servicios,id',
            'evaluaciones' => 'nullable|array',
            'evaluaciones.*' => 'integer|exists:evaluaciones,id',
            'motivo_consulta' => 'nullable|string',
            'consentimiento' => 'boolean',
            'observaciones' => 'nullable|string',
        ]);

        $expediente->update($validated);
        $expediente->diagnosticos()->sync($request->diagnosticos ?? []);
        $expediente->servicios()->sync($request->servicios ?? []);
        $expediente->evaluaciones()->sync($request->evaluaciones ?? []);

        return redirect()->route('expedientes')->with('success', 'Expediente actualizado correctamente');
    }
}