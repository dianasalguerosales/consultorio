<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Programa;

class ProgramaController extends Controller
{
    public function index()
    {
        $programas = Programa::with(['servicios', 'especialidades'])->get();

        return Inertia::render('Programas/Index', [
            'programas' => $programas,
        ]);
    }
}