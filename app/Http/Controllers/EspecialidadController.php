<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use Illuminate\Http\Request;

class EspecialidadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:especialidades,nombre',
            'activo' => 'boolean'
        ]);

        Especialidad::create($request->all());
        return redirect()->back()->with('success', 'Especialidad creada correctamente');
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $request->validate([
            'nombre' => 'required|string|unique:especialidades,nombre,' . $especialidad->id,
            'activo' => 'boolean'
        ]);

        $especialidad->update($request->all());
        return redirect()->back()->with('success', 'Especialidad actualizada correctamente');
    }

    public function destroy(Especialidad $especialidad)
    {
        $especialidad->delete();
        return redirect()->back()->with('success', 'Especialidad eliminada correctamente');
    }
}