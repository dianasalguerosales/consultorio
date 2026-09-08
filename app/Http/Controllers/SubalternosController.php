<?php

namespace App\Http\Controllers;

use App\Models\Administrativo;
use Inertia\Inertia;

class SubalternosController extends Controller
{
    // La jerarquía sale de administrativos.cargo_id: no hay tabla de jefaturas
    // ni la necesita. Cada cargo cuelga del que tiene encima.
    private const BAJO = [
        'Administrador' => ['Coordinador'],
        'Coordinador' => ['Auxiliar'],
    ];

    public function show(Administrativo $administrativo)
    {
        return Inertia::render('Personas/Subalternos', [
            'organigrama' => $this->nodo($administrativo),
        ]);
    }

    // Arma el subárbol de una persona. Recursivo porque el organigrama crece
    // hacia abajo y cada nivel se resuelve igual que el anterior.
    private function nodo(Administrativo $persona): array
    {
        $cargo = $persona->cargo?->nombre;

        return [
            'id' => $persona->id,
            'nombre' => $persona->nombre_completo,
            'cargo' => $cargo,
            'correo' => $persona->correo,
            'rol' => strtolower($cargo ?? ''),
            'subalternos' => $this->subalternosDe($cargo),
        ];
    }

    private function subalternosDe(?string $cargo): array
    {
        $cargos = self::BAJO[$cargo] ?? [];

        if (! $cargos) {
            return [];
        }

        return Administrativo::with('cargo')
            ->whereHas('cargo', fn($q) => $q->whereIn('nombre', $cargos))
            ->orderBy('apellidos')
            ->get()
            ->map(fn(Administrativo $a) => $this->nodo($a))
            ->all();
    }
}
