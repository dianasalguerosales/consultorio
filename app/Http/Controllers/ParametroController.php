<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Servicio;
use App\Models\Especialidad;
use App\Models\Escolaridad;

class ParametroController extends Controller
{
    public function index()
    {
        return Inertia::render('Parametros/Index', [
            'servicios' => Servicio::all(),
            'especialidades' => Especialidad::all(),
            'escolaridades' => Escolaridad::all(),
        ]);
    }
}
