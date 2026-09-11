<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Paciente;
use App\Models\Expediente;
use App\Models\Cita;
use App\Models\Encargado;
use App\Models\Genero;
use App\Models\Escolaridad;

class PacientesController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::with([
            'expediente',
            // El modal de Expediente se abre también desde aquí, así que necesita las
            // mismas relaciones que carga ExpedienteController::index.
            'expediente.estado',
            'expediente.modalidad',
            'expediente.anamnesis.items.criterio',
            'expediente.diagnosticos',
            'expediente.servicios',
            'expediente.evaluaciones',
            'expediente.paciente.escolaridad',
            'encargado',
            'genero',
            'escolaridad',
            // El modal de Historial lista las citas en orden cronológico.
            'citas' => fn($q) => $q->orderBy('fecha')->orderBy('hora_inicio'),
            'citas.servicio',
            'citas.programa',
            'citas.estadoCita',
            'citas.modalidad',
            'citas.sesion',
            'citas.atendidoPor',
        ])->orderBy('apellidos')->get();

        return Inertia::render('Pacientes', [
            'pacientes' => $pacientes,
            'encargados' => Encargado::all(),
            'generos' => Genero::orderBy('nombre')->get(),
            'escolaridades' => Escolaridad::orderBy('nombre')->get(),
            // Para el modal de programa, que se abre desde la ficha.
            'catalogosPrograma' => app(AsignacionProgramaController::class)->catalogos(),
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
        $citas = Cita::with(['atendidoPor', 'servicio', 'programa', 'estadoCita', 'modalidad', 'sesion'])
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
            'escolaridad_id' => 'nullable|exists:escolaridades,id',
            'genero_id' => 'nullable|exists:generos,id',
            'encargado_id' => 'nullable|exists:encargados,id',
        ]);

        Paciente::create($validated);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente creado correctamente');
    }

    public function update(Request $request, Paciente $paciente)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'escolaridad_id' => 'nullable|exists:escolaridades,id',
            'genero_id' => 'nullable|exists:generos,id',
            'encargado_id' => 'nullable|exists:encargados,id',
        ]);

        $paciente->update($validated);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente actualizado correctamente');
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente actualizado correctamente');
    }
}
