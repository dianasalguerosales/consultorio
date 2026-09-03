<?php

namespace App\Http\Controllers;

use App\Models\Anamnesis;
use App\Models\Expediente;
use App\Models\AnamnesisItem;
use Illuminate\Http\Request;

class AnamnesisController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string',
            'items' => 'required|array',
            'items.*.area' => 'required|string',
            'items.*.criterio' => 'required|string',
            'items.*.respuesta' => 'required|integer|in:1,2,3',
            'expediente_id' => 'required|exists:expedientes,id',
        ]);

        $anamnesis = Anamnesis::create([
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            $anamnesis->items()->create($item);
        }

        $expediente = Expediente::find($validated['expediente_id']);
            $expediente->update([
                'anamnesis_id' => $anamnesis->id,
            ]);

        return redirect()->route('expedientes.show', $validated['expediente_id'])
                         ->with('success', 'Anamnesis registrada correctamente y vinculada al expediente.');
    }

    public function update(Request $request, Anamnesis $anamnesis)
    {
        $validated = $request->validate([
            'observaciones' => 'nullable|string',
            'items' => 'required|array',
            'items.*.area' => 'required|string',
            'items.*.criterio' => 'required|string',
            'items.*.respuesta' => 'required|integer|in:1,2,3',
        ]);

        $anamnesis->update([
            'observaciones' => $validated['observaciones'] ?? null,
        ]);

        $anamnesis->items()->delete();
        foreach ($validated['items'] as $item) {
            $anamnesis->items()->create($item);
        }

        return redirect()->route('expedientes.show', $anamnesis->expediente_id)
                         ->with('success', 'Anamnesis actualizada correctamente.');
    }
}