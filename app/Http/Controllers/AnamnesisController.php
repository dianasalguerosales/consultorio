<?php

namespace App\Http\Controllers;

use App\Models\Anamnesis;
use App\Models\Expediente;
use Illuminate\Http\Request;

class AnamnesisController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string',
            'items' => 'required|array',
            'items.*.criterio_id' => 'required|exists:criterios,id',
            'items.*.respuesta' => 'required|integer|in:1,2,3',
            'expediente_id' => 'required|exists:expedientes,id',
        ]);

        $anamnesis = Anamnesis::create([
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $anamnesis->items()->create([
                'criterio_id' => $item['criterio_id'],
                'respuesta'   => $item['respuesta'],
            ]);
        }

        $expediente = Expediente::findOrFail($validated['expediente_id']);
        $expediente->update([
            'anamnesis_id' => $anamnesis->id,
        ]);

        return redirect()->route('expedientes.show', $expediente->id)
                         ->with('success', 'Anamnesis registrada correctamente y vinculada al expediente.');
    }

    public function update(Request $request, Anamnesis $anamnesis)
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string',
            'items' => 'required|array',
            'items.*.criterio_id' => 'required|exists:criterios,id',
            'items.*.respuesta' => 'required|integer|in:1,2,3',
        ]);

        $anamnesis->update([
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        $anamnesis->items()->delete();
        foreach ($validated['items'] as $item) {
            $anamnesis->items()->create([
                'criterio_id' => $item['criterio_id'],
                'respuesta'   => $item['respuesta'],
            ]);
        }

        return redirect()->route('expedientes.show', $anamnesis->expediente->id)
                         ->with('success', 'Anamnesis actualizada correctamente.');
    }
}