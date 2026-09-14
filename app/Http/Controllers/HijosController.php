<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HijosController extends Controller
{
    public function index(Request $request)
    {
        $encargado = $request->user()->encargado;

        // Sin fila en encargados no hay a quién filtrar, y sin el abort la
        // consulta traería los pacientes de todo el consultorio.
        abort_unless($encargado, 403, 'Su usuario no está asociado a un encargado.');

        $hijos = Paciente::with([
            'genero',
            'escolaridad',

            // Del expediente el encargado ve una sola pestaña: terapeuta,
            // terapias y objetivos. Lo demás ni se carga, así no viaja a su
            // navegador. Las terapias son `servicios`; el terapeuta cuelga del
            // paciente, no del expediente.
            'expediente.servicios',
            'expediente.paciente.terapeutas.especialidad',
            'expediente.paciente.objetivos.servicio',

            'citas' => fn($q) => $q->orderBy('fecha')->orderBy('hora_inicio'),
            'citas.servicio',
            'citas.estadoCita',
            'citas.modalidad',
            'citas.atendidoPor',

            // Al encargado solo le tocan las observaciones generales: las
            // clínicas ni siquiera se cargan, así no viajan a su navegador.
            'citas.sesion' => fn($q) => $q->select('id', 'cita_id', 'observaciones_generales'),
        ])
            ->where('encargado_id', $encargado->id)
            ->orderBy('nombres')
            ->get();

        return Inertia::render('Hijos', ['hijos' => $hijos]);
    }
}
