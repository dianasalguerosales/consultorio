<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EspecialidadController extends Controller
{
    public function index()
    {
        $especialidades = Especialidad::all();
        return Inertia::render('Parametros/Especialidades/Index', [
            'especialidades' => $especialidades
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        Especialidad::create($request->only('nombre'));

        return redirect()->route('parametros.especialidades.index')
                         ->with('success', 'Especialidad creada correctamente');
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $especialidad->update($request->only('nombre'));

        return redirect()->route('parametros.especialidades.index')
                         ->with('success', 'Especialidad actualizada correctamente');
    }

    public function destroy(Especialidad $especialidad)
    {
        $especialidad->delete();

        return redirect()->route('parametros.especialidades.index')
                         ->with('success', 'Especialidad eliminada correctamente');
    }
}