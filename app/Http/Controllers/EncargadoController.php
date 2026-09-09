<?php

namespace App\Http\Controllers;
use App\Models\Encargado;
use Illuminate\Http\Request;

class EncargadoController extends Controller
{
    public function pacientes($id)
{
    $encargado = Encargado::with('pacientes')->findOrFail($id);

    return inertia('Personas/EncargadoPacientes', [
        'organigrama' => [
            'id' => $encargado->id,
            'nombre' => $encargado->nombres . ' ' . $encargado->apellidos,
            'cargo' => 'Encargado',
            'correo' => $encargado->correo,
            'rol' => 'encargado',
            'avatar' => $encargado->avatar_url ?? '/images/avatar.webp',
            'subalternos' => $encargado->pacientes->map(fn($p) => [
                'id' => $p->id,
                'nombre' => $p->nombres . ' ' . $p->apellidos,
                'cargo' => 'Paciente',
                'correo' => null,
                'rol' => 'paciente',
                'avatar' => '/images/avatar.webp',
                'subalternos' => [],
            ]),
        ]
    ]);
}

}
