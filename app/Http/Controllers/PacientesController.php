<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PacientesController extends Controller
{
    public function index()
    {
        $pacientes = Paciente::all();

        return Inertia::render('Pacientes', [
            'pacientes' => $pacientes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'expediente' => 'required|string|unique:pacientes,expediente',
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'genero' => 'nullable|string|max:20',
        ]);

        Paciente::create($validated);

        return redirect()->back()->with('success', 'Paciente registrado correctamente');
    }

    public function update(Request $request, Paciente $paciente)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'expediente' => 'required|string|unique:pacientes,expediente,' . $paciente->id,
            'fecha_nacimiento' => 'nullable|date',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'genero' => 'nullable|string|max:20',
        ]);

        $paciente->update($validated);

        return redirect()->back()->with('success', 'Paciente actualizado correctamente');
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->back()->with('success', 'Paciente eliminado correctamente');
    }
}
