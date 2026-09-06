<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EspecialidadController extends Controller
{
    public function index()
    {
        $especialidades = Especialidad::orderBy('nombre')->get();
        return Inertia::render('Parametros/Especialidades', [
            'especialidades' => $especialidades
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        Especialidad::create($request->only('nombre', 'activo'));

        return redirect()->route('parametros.especialidades')
                         ->with('success', 'Especialidad creada correctamente');
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        $especialidad->update($request->only('nombre', 'activo'));

        return redirect()->route('parametros.especialidades')
                         ->with('success', 'Especialidad actualizada correctamente');
    }

    public function destroy(Especialidad $especialidad)
    {
        $especialidad->delete();

        return redirect()->route('parametros.especialidades')
                         ->with('success', 'Especialidad eliminada correctamente');
    }
}