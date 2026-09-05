<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Expediente;
use App\Models\Paciente;
use App\Models\Diagnostico;
use App\Models\Terapia;
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
            'terapias',
            'evaluaciones',
            'escolaridad'
        ]);
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('codigo')) {
            $query->where('id', 'like', '%' . $request->codigo . '%');
        }

        if ($request->filled('paciente')) {
            $query->whereHas('paciente', function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->paciente . '%');
            });
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_apertura', $request->fecha_inicio);
        }

        $expedientes = $query->orderBy('fecha_apertura', 'desc')->get();

        return Inertia::render('Expedientes', [
            'expedientes' => $expedientes,
            'diagnosticosList' => Diagnostico::all(),
            'terapiasList' => Terapia::all(),
            'evaluacionesList' => Evaluacion::all(),
            'escolaridadesList' => Escolaridad::all(),
            'criteriosModulo1' => Criterio::where('modulo', 'Evaluación del Desarrollo Infantil')->get(),
            'criteriosModulo2' => Criterio::where('modulo', 'Evaluación Cognitiva')->get(),
            'criteriosModulo3' => Criterio::where('modulo', 'Evaluación Socioemocional')->get(),
        ]);
    }

    // 👉 Mostrar formulario para crear
    public function create(Paciente $paciente)
    {
        return Inertia::render('ExpedienteEditModal', [
            'pacienteId' => $paciente->id,
            'diagnosticosList' => Diagnostico::all(),
            'terapiasList' => Terapia::all(),
            'evaluacionesList' => Evaluacion::all(),
            'escolaridadesList' => Escolaridad::all(),
            'criteriosModulo1' => Criterio::where('modulo', 'Evaluación del Desarrollo Infantil')->get(),
            'criteriosModulo2' => Criterio::where('modulo', 'Evaluación Cognitiva')->get(),
            'criteriosModulo3' => Criterio::where('modulo', 'Evaluación Socioemocional')->get(),
        ]);
    }

    // 👉 Guardar nuevo expediente
    public function store(Request $request)
    {
        $codigo = $this->generarCodigoExpediente();

        $validated = $request->validate([
            'paciente_id' => 'nullable|integer|exists:pacientes,id',
            'nombre_pila' => 'nullable|string|max:100',
            'estado' => 'required|string',
            'modalidad' => 'nullable|string',
            'escolaridad_id' => 'nullable|integer|exists:escolaridades,id',
            'motivo_consulta' => 'nullable|string',
            'observaciones_administrativas' => 'nullable|string',
            'diagnosticos' => 'array',
            'diagnosticos.*' => 'integer|exists:diagnosticos,id',
            'terapias' => 'array',
            'terapias.*' => 'integer|exists:terapias,id',
            'evaluaciones' => 'array',
            'evaluaciones.*' => 'integer|exists:evaluaciones,id',
        ]);

        $expediente = Expediente::create(array_merge($validated, [
            'id' => $codigo,
            'fecha_apertura' => now(),
            'creado_por_usuario_id' => $request->user()->id,
        ]));

        $expediente->diagnosticos()->sync($request->diagnosticos ?? []);
        $expediente->terapias()->sync($request->terapias ?? []);
        $expediente->evaluaciones()->sync($request->evaluaciones ?? []);

        // Si tiene paciente, actualiza la relación
        if ($request->filled('paciente_id')) {
            $paciente = Paciente::find($request->paciente_id);
            $paciente?->update(['expediente_id' => $codigo]);
        }

        return redirect()->back()->with('success', 'Expediente creado correctamente');
    }

    // 👉 Mostrar formulario para editar
    public function edit(Expediente $expediente)
    {
        return Inertia::render('ExpedienteEditModal', [
            'expediente' => $expediente->load(['paciente', 'diagnosticos', 'terapias', 'evaluaciones', 'escolaridades']),
            'diagnosticosList' => Diagnostico::all(),
            'terapiasList' => Terapia::all(),
            'evaluacionesList' => Evaluacion::all(),
            'escolaridadesList' => Escolaridad::all(),
            'criteriosModulo1' => Criterio::where('modulo', 'Evaluación del Desarrollo Infantil')->get(),
            'criteriosModulo2' => Criterio::where('modulo', 'Evaluación Cognitiva')->get(),
            'criteriosModulo3' => Criterio::where('modulo', 'Evaluación Socioemocional')->get(),
        ]);
    }

    // 👉 Guardar cambios
    public function update(Request $request, Expediente $expediente)
    {
        $validated = $request->validate([
            'nombre_pila' => 'nullable|string|max:100',
            'estado' => 'required|string',
            'escolaridad_id' => 'nullable|integer|exists:escolaridades,id',
            'modalidad' => 'nullable|string',
            'observaciones_administrativas' => 'nullable|string',
            'diagnosticos' => 'array',
            'diagnosticos.*' => 'integer|exists:diagnosticos,id',
            'terapias' => 'array',
            'terapias.*' => 'integer|exists:terapias,id',
            'evaluaciones' => 'array',
            'evaluaciones.*' => 'integer|exists:evaluaciones,id',
        ]);

        $expediente->update($validated);

        $expediente->diagnosticos()->sync($request->diagnosticos ?? []);
        $expediente->terapias()->sync($request->terapias ?? []);
        $expediente->evaluaciones()->sync($request->evaluaciones ?? []);

        return redirect()->route('expedientes.index')->with('success', 'Expediente actualizado correctamente');
    }

    public function destroy(Expediente $expediente)
    {
        try {
            $expediente->delete();

            return redirect()->route('expedientes.index')
                ->with('success', 'Expediente eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('expedientes.index')
                ->with('error', 'No se pudo eliminar el expediente: ' . $e->getMessage());
        }
    }

    private function generarCodigoExpediente()
    {
        $anio = date('Y');

        $ultimo = Expediente::where('id', 'like', 'KID' . $anio . '%')
            ->orderBy('id', 'desc')
            ->first();

        $correlativo = $ultimo ? intval(substr($ultimo->id, 7)) + 1 : 1;

        return 'KID' . $anio . str_pad($correlativo, 3, '0', STR_PAD_LEFT);
    }
}
