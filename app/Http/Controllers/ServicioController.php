<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::all();
        return Inertia::render('Programas/Index', [
            'servicios' => $servicios
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        Servicio::create($request->only('nombre'));

        return redirect()->route('programas.index')->with('success', 'Servicio creado correctamente');
    }

    public function update(Request $request, Servicio $servicio)
    {
        $request->validate([
            'nombre' => 'required|string|max:255'
        ]);

        $servicio->update($request->only('nombre'));

        return redirect()->route('programas.index')->with('success', 'Servicio actualizado correctamente');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('programas.index')->with('success', 'Servicio eliminado correctamente');
    }
}
