<?php

namespace App\Http\Controllers;

use App\Models\Encargado;
use App\Models\EstadoCivil;
use App\Models\Genero;
use App\Models\RelacionPaciente;
use App\Personas\Personas;
use App\Personas\UsuariosDisponibles;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * El CRUD salió de PersonasController, que tenía los tres CRUD de personas
 * calcados en un solo archivo.
 */
class EncargadoController extends Controller
{
    public function index()
    {
        return Inertia::render('Personas/Encargados', [
            'encargados' => Encargado::with(['relacionPaciente', 'genero', 'estadoCivil', 'user.roles'])->get(),
            'generos' => Genero::all(),
            'estadosCiviles' => EstadoCivil::all(),
            'relacionesPaciente' => RelacionPaciente::all(),
            'usuariosDisponibles' => UsuariosDisponibles::libres(),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $persona = Encargado::create($this->validar($request));

        // Nace con el rol que le toca por su tipo o su cargo.
        Personas::asignarRolInicial($persona->load('user'));

        return redirect()->route('personas.index')
            ->with('success', 'Encargado creado correctamente.');
    }

    public function edit($id)
    {
        $encargado = Encargado::with('user')->findOrFail($id);

        return Inertia::render('Personas/EncargadoEditar', [
            'encargado' => $encargado,
            'generos' => Genero::all(),
            'estadosCiviles' => EstadoCivil::all(),
            'relacionesPaciente' => RelacionPaciente::all(),
            'usuariosDisponibles' => UsuariosDisponibles::libresMas($encargado->user),
        ]);
    }

    public function update(Request $request, $id)
    {
        Encargado::findOrFail($id)->update($this->validar($request));

        return redirect()->route('personas.index')
            ->with('success', 'Encargado actualizado correctamente.');
    }

    public function destroy($id)
    {
        Encargado::findOrFail($id)->delete();

        return redirect()->route('personas.index')
            ->with('success', 'Encargado eliminado correctamente.');
    }

    /** Los pacientes a cargo, en forma de organigrama. */
    public function pacientes($id)
    {
        $encargado = Encargado::with(['genero', 'pacientes.genero'])->findOrFail($id);

        return Inertia::render('Personas/EncargadoPacientes', [
            'organigrama' => [
                'id' => $encargado->id,
                'nombre' => $encargado->nombre_completo,
                'cargo' => 'Encargado',
                'correo' => $encargado->correo,
                'rol' => 'encargado',
                'genero' => $encargado->genero?->nombre,
                'subalternos' => $encargado->pacientes->map(fn($p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre_completo,
                    'cargo' => 'Paciente',
                    'correo' => null,
                    'rol' => 'paciente',
                    'genero' => $p->genero?->nombre,
                    'subalternos' => [],
                ]),
            ],
        ]);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'fecha_nacimiento' => 'nullable|date',
            'dpi' => 'nullable|numeric',
            'telefono' => 'nullable|string|max:25',
            'correo' => 'nullable|email|max:255',
            'direccion' => 'nullable|string|max:255',
            'ocupacion' => 'nullable|string|max:255',
            'relacion_paciente_id' => 'nullable|exists:relaciones_paciente,id',
            'genero_id' => 'nullable|exists:generos,id',
            'estado_civil_id' => 'nullable|exists:estados_civiles,id',
        ]);
    }
}
