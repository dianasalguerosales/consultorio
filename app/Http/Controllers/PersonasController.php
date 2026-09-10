<?php

namespace App\Http\Controllers;

use App\Models\Administrativo;
use App\Models\Cargo;
use App\Models\Encargado;
use App\Models\Especialidad;
use App\Models\EstadoCivil;
use App\Models\Genero;
use App\Models\RelacionPaciente;
use App\Models\Terapeuta;
use App\Personas\UsuariosDisponibles;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

/**
 * La pantalla de /personas, que muestra los tres tipos en pestañas.
 *
 * El CRUD de cada tipo vive en su propio controlador:
 * AdministrativoController, TerapeutaController y EncargadoController.
 */
class PersonasController extends Controller
{
    public function index()
    {
        return Inertia::render('Personas/Index', [
            'administrativos' => Administrativo::with(['cargo', 'especialidad', 'genero', 'user.roles'])->get(),
            'terapeutas' => Terapeuta::with(['especialidad', 'genero', 'user.roles'])->get(),
            'encargados' => Encargado::with(['relacionPaciente', 'genero', 'estadoCivil', 'user.roles'])->get(),
            'cargos' => Cargo::all(),
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
            'estadosCiviles' => EstadoCivil::all(),
            'relacionesPaciente' => RelacionPaciente::all(),
            'usuariosDisponibles' => UsuariosDisponibles::libres(),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }
}
