<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|unique:servicios,nombre',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        Servicio::create($request->all());
        return redirect()->back()->with('success', 'Servicio creado correctamente');
    }

    public function update(Request $request, Servicio $servicio)
    {
        $request->validate([
            'nombre' => 'required|string|unique:servicios,nombre,' . $servicio->id,
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $servicio->update($request->all());
        return redirect()->back()->with('success', 'Servicio actualizado correctamente');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();
        return redirect()->back()->with('success', 'Servicio eliminado correctamente');
    }
}