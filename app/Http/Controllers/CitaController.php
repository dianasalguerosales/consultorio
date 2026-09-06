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
            'programa_id' => 'nullable|exists:programas,id',
            'estado_cita_id' => 'required|exists:estado_citas,id',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'modalidad_id' => 'nullable|exists:modalidades,id',
            'precio_aplicado' => 'nullable|numeric',
            'motivo_consulta' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ]);

        $existe = Cita::where('terapeuta_id', $validated['terapeuta_id'])
            ->where('fecha', $validated['fecha'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('hora_inicio', [$validated['hora_inicio'], $validated['hora_fin']])
                  ->orWhereBetween('hora_fin', [$validated['hora_inicio'], $validated['hora_fin']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('hora_inicio', '<=', $validated['hora_inicio'])
                         ->where('hora_fin', '>=', $validated['hora_fin']);
                  });
            })
            ->exists();

        if ($existe) {
            return redirect()->back()->withErrors([
                'hora_inicio' => 'El terapeuta ya tiene una cita en ese horario.',
            ]);
        }

        Cita::create($validated);

        return redirect()->route('citas')
                         ->with('success', 'Cita creada correctamente');
    }
}