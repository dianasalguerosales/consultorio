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
        ])->get();

        $encargados = Encargado::all();

        return Inertia::render('Pacientes', [
            'pacientes' => $pacientes,
            'encargados' => $encargados,
        ]);
    }

    public function expediente(Paciente $paciente)
    {
        $expediente = Expediente::where('paciente_id', $paciente->id)->firstOrFail();

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
            'nombre' => 'required|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'genero' => 'nullable|string|max:20',
            'encargado_id' => 'nullable|exists:encargados,id',
        ]);

        $paciente = Paciente::create($validated);

        // Nuevo formato de código: KIDYYYYXXX
        $codigo = $this->generarCodigoExpediente();
        Expediente::create([
            'id' => $codigo,
            'paciente_id' => $paciente->id,
            'fecha_apertura' => now(),
            'estado' => 'activo',
            'creado_por_usuario_id' => $request->user()->id,
            'observaciones_administrativas' => 'Expediente inicial',
        ]);

        $paciente->update([
            'expediente_id' => $codigo,
        ]);

        if ($request->filled('encargado_id')) {
            $paciente->encargados()->attach($request->encargado_id);
        }

        return redirect()->back()->with('success', 'Paciente y expediente creados correctamente');
    }

    public function update(Request $request, Paciente $paciente)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
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

        return redirect()->back()->with('success', 'Paciente actualizado correctamente');
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->back()->with('success', 'Paciente eliminado correctamente');
    }

    /**
     * Genera código de expediente en formato KIDYYYYXXX
     */
    private function generarCodigoExpediente()
    {
        $anio = date('Y');

        $ultimo = Expediente::where('id', 'like', 'KID-'.$anio.'%')
            ->orderBy('id', 'desc')
            ->first();

        $correlativo = $ultimo ? intval(substr($ultimo->id, 7)) + 1 : 1;

        return 'KID'.$anio.str_pad($correlativo, 3, '0', STR_PAD_LEFT);
    }
}