<?php

namespace App\Http\Controllers;

use App\Models\Escolaridad;
use Illuminate\Http\Request;

class EscolaridadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:escolaridades,nombre',
            'activo' => 'boolean'
        ]);

        Escolaridad::create($request->all());
        return redirect()->back()->with('success', 'Escolaridad creada correctamente');
    }

    public function update(Request $request, Escolaridad $escolaridad)
    {
        $request->validate([
            'nombre' => 'required|string|unique:escolaridades,nombre,' . $escolaridad->id,
            'activo' => 'boolean'
        ]);

        $escolaridad->update($request->all());
        return redirect()->back()->with('success', 'Escolaridad actualizada correctamente');
    }

    public function destroy(Escolaridad $escolaridad)
    {
        $escolaridad->delete();
        return redirect()->back()->with('success', 'Escolaridad eliminada correctamente');
    }
}