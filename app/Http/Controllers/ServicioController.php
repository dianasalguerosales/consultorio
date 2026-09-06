<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Servicio;

class ServicioController extends Controller
{
    public function index()
    {
        $servicios = Servicio::orderBy('nombre')->paginate(10);
        return Inertia::render('Parametros/Servicios/Index', [
            'servicios' => $servicios
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        Servicio::create($request->only('nombre', 'activo'));

        return redirect()->route('parametros.servicios.index')
                         ->with('success', 'Servicio creado correctamente');
    }

    public function update(Request $request, Servicio $servicio)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        $servicio->update($request->only('nombre', 'activo'));

        return redirect()->route('parametros.servicios.index')
                         ->with('success', 'Servicio actualizado correctamente');
    }

    public function destroy(Servicio $servicio)
    {
        $servicio->delete();

        return redirect()->route('parametros.servicios.index')
                         ->with('success', 'Servicio eliminado correctamente');
    }
}