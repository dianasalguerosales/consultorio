<?php

namespace App\Http\Controllers;

use App\Models\Administrativo;
use Inertia\Inertia;

class SubalternosController extends Controller
{
    public function show(Administrativo $administrativo)
    {
        $administrativo->load([
            'subordinados.subordinados',
            'subordinados.terapeutas',
            'terapeutas',
        ]);

        return Inertia::render('Personas/Subalternos', [
            'organigrama' => $this->nodo($administrativo),
        ]);
    }


    private function nodo(Administrativo $persona): array
    {
        $subordinados = $persona->subordinados->map(fn($sub) => $this->nodo($sub));

        $terapeutas = $persona->terapeutas->map(fn($t) => [
            'id' => $t->id,
            'nombre' => $t->nombre_completo,
            'cargo' => 'Terapeuta',
            'correo' => $t->correo,
            'rol' => 'terapeuta',
            'subalternos' => [],
        ]);

        return [
            'id' => $persona->id,
            'nombre' => $persona->nombre_completo,
            'cargo' => $persona->cargo?->nombre,
            'correo' => $persona->correo,
            'rol' => strtolower($persona->cargo?->nombre ?? ''),
            'subalternos' => collect()
                ->merge($subordinados)
                ->merge($terapeutas)
                ->values()
                ->toArray(),
        ];
    }
}