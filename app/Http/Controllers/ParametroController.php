<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Servicio;
use App\Models\Especialidad;
use App\Models\Escolaridad;
use App\Models\EstadoExpediente;
use App\Models\EstadoSesion;
use App\Models\Modalidad;

class ParametroController extends Controller
{
    public function index()
    {
        return Inertia::render('Parametros/Index', [
            'servicios' => Servicio::orderBy('nombre')->get(),
            'especialidades' => Especialidad::orderBy('nombre')->get(),
            'escolaridades' => Escolaridad::orderBy('nombre')->get(),
            'estadoExpedientes' => EstadoExpediente::orderBy('nombre')->get(),
            'estadoSesiones' => EstadoSesion::orderBy('nombre')->get(),
            'modalidades' => Modalidad::orderBy('nombre')->get(),
        ]);
    }
}