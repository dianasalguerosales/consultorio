<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Especialidad;
use Inertia\Inertia;

class ProgramaController extends Controller
{
    public function index()
    {
        return Inertia::render('Programas/Index', [
            'servicios' => Servicio::all(),
            'especialidades' => Especialidad::all(),
        ]);
    }
}