<?php

namespace App\Http\Controllers;

use App\Catalogos\Catalogos;
use Inertia\Inertia;

class ParametroController extends Controller
{
    public function index()
    {
        // Las pestañas y sus filas salen del registro, así que agregar un
        // catálogo no toca este archivo.
        return Inertia::render('Parametros/Index', [
            'catalogos' => Catalogos::paraLaVista(),
        ]);
    }
}
