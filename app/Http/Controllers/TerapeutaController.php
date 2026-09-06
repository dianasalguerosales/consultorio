<?php

namespace App\Http\Controllers;

use App\Models\Terapeuta;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TerapeutaController extends Controller
{
    public function index()
    {
        $terapeutas = Terapeuta::withCount('pacientes')->with('user')->get();

        return Inertia::render('Terapeutas/Index', [
            'terapeutas' => $terapeutas,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'required|email|unique:terapeutas,correo',
            'numero_colegiado' => 'nullable|string|max:50',
            'especialidad' => 'nullable|string|max:255',
            'formacion' => 'nullable|string',
            'certificaciones' => 'nullable|string',
        ]);

        Terapeuta::create($request->all());

        return redirect()->route('terapeutas.index')->with('success', 'Terapeuta creado correctamente.');
    }

    public function update(Request $request, Terapeuta $terapeuta)
    {
        $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'fecha_nacimiento' => 'required|date',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'required|email|unique:terapeutas,correo,' . $terapeuta->id,
            'numero_colegiado' => 'nullable|string|max:50',
            'especialidad' => 'nullable|string|max:255',
            'formacion' => 'nullable|string',
            'certificaciones' => 'nullable|string',
        ]);

        $terapeuta->update($request->all());

        return redirect()->route('terapeutas.index')->with('success', 'Terapeuta actualizado correctamente.');
    }

    public function destroy(Terapeuta $terapeuta)
    {
        $terapeuta->delete();

        return redirect()->route('terapeutas.index')->with('success', 'Terapeuta eliminado correctamente.');
    }

    public function perfil(Terapeuta $terapeuta)
    {
        return Inertia::render('Terapeutas/Perfil', [
            'terapeuta' => $terapeuta->load('user', 'pacientes'),
        ]);
    }

    public function pacientes(Terapeuta $terapeuta)
    {
        return Inertia::render('Terapeutas/Pacientes', [
            'pacientes' => $terapeuta->pacientes,
        ]);
    }
}