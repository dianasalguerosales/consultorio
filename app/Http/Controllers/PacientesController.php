<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Paciente;
use App\Models\Expediente;
use App\Models\Cita;
use App\Models\Encargado;

class PacientesController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::with([
            'expediente',
            'encargados',
            'citas.servicio',
            'citas.programa',
            'citas.estadoCita',
            'citas.terapeuta',
        ])->orderBy('apellidos')->get();

        $encargados = Encargado::all();

        return Inertia::render('Pacientes', [
            'pacientes' => $pacientes,
            'encargados' => $encargados,
        ]);
    }

    public function expediente(Paciente $paciente)
    {
        $expediente = $paciente->expediente;

        if (!$expediente) {
            abort(404, 'Expediente no encontrado');
        }

        return Inertia::render('Expedientes/Show', [
            'paciente' => $paciente,
            'expediente' => $expediente,
        ]);
    }

    public function historial(Paciente $paciente)
    {
        $citas = Cita::with(['terapeuta', 'servicio', 'programa', 'estadoCita'])
            ->where('paciente_id', $paciente->id)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get();

        return Inertia::render('Pacientes/Historial', [
            'paciente' => $paciente,
            'citas' => $citas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'genero' => 'nullable|string|max:20',
            'encargado_id' => 'nullable|exists:encargados,id',
        ]);

        $paciente = Paciente::create($validated);

        if ($request->filled('encargado_id')) {
            $paciente->encargados()->attach($request->encargado_id);
        }

        return redirect()->route('pacientes')
                         ->with('success', 'Paciente creado correctamente');
    }

    public function update(Request $request, Paciente $paciente)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'genero' => 'nullable|string|max:20',
            'encargado_id' => 'nullable|exists:encargados,id',
        ]);

        $paciente->update($validated);

        if ($request->filled('encargado_id')) {
            $paciente->encargados()->sync([$request->encargado_id]);
        }

        return redirect()->route('pacientes')
                         ->with('success', 'Paciente actualizado correctamente');
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route('pacientes')
                         ->with('success', 'Paciente eliminado correctamente');
    }
}