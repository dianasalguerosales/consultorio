<?php

namespace App\Http\Controllers;

use App\Cumpleanos\Cumpleanos;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            // Hoy y mañana, para poder preparar con un día de anticipación.
            'cumpleanos' => Cumpleanos::hoyYManana(),
        ]);
    }
}
