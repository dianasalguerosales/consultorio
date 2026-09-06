<?php

namespace App\Http\Controllers;

use App\Models\Genero;
use Inertia\Inertia;

class GeneroController extends Controller
{
    public function index()
    {
        $generos = Genero::where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Generos/Index', [
            'generos' => $generos,
        ]);
    }

    public function list()
    {
        return response()->json(
            Genero::where('activo', 1)->orderBy('nombre')->get()
        );
    }
}