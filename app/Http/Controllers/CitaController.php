<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;

class CitaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'paciente_id' => 'required|exists:pacientes,id',
            'terapeuta_id' => 'required|exists:terapeutas,id',
            'servicio_id' => 'required|exists:servicios,id',
            'estado_cita_id' => 'required|exists:estado_citas,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'modalidad' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        Cita::create($validated);

        return redirect()->back()->with('success', 'Cita creada correctamente');
    }
}
