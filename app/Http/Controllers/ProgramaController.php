<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Programa;

class ProgramaController extends Controller
{
    public function index()
    {
        $programas = Programa::with(['servicios', 'especialidades'])
            ->orderBy('nombre')
            ->paginate(10);

        return Inertia::render('Programas', [
            'programas' => $programas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'servicios' => 'array',
            'servicios.*' => 'integer|exists:servicios,id',
            'especialidades' => 'array',
            'especialidades.*' => 'integer|exists:especialidades,id',
        ]);

        $programa = Programa::create($validated);
        $programa->servicios()->sync($request->servicios ?? []);
        $programa->especialidades()->sync($request->especialidades ?? []);

        return redirect()->route('programas')
                         ->with('success', 'Programa creado correctamente');
    }

    public function update(Request $request, Programa $programa)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'servicios' => 'array',
            'servicios.*' => 'integer|exists:servicios,id',
            'especialidades' => 'array',
            'especialidades.*' => 'integer|exists:especialidades,id',
        ]);

        $programa->update($validated);
        $programa->servicios()->sync($request->servicios ?? []);
        $programa->especialidades()->sync($request->especialidades ?? []);

        return redirect()->route('programas')
                         ->with('success', 'Programa actualizado correctamente');
    }

    public function destroy(Programa $programa)
    {
        $programa->delete();

        return redirect()->route('programas')
                         ->with('success', 'Programa eliminado correctamente');
    }
}