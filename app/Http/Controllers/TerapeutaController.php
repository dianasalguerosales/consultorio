<?php

namespace App\Http\Controllers;

use App\Models\Especialidad;
use App\Models\Genero;
use App\Models\Terapeuta;
use App\Personas\Personas;
use App\Personas\UsuariosDisponibles;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * El CRUD salió de PersonasController, que tenía los tres CRUD de personas
 * calcados en un solo archivo.
 */
class TerapeutaController extends Controller
{
    public function index()
    {
        return Inertia::render('Personas/Terapeutas', [
            'terapeutas' => Terapeuta::with(['especialidad', 'genero', 'user.roles'])->get(),
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
            'usuariosDisponibles' => UsuariosDisponibles::libres(),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $persona = Terapeuta::create($this->validar($request));

        // Nace con el rol que le toca por su tipo o su cargo.
        Personas::asignarRolInicial($persona->load('user'));

        return redirect()->route('personas.index')
            ->with('success', 'Terapeuta creado correctamente.');
    }

    public function edit($id)
    {
        $terapeuta = Terapeuta::with('user')->findOrFail($id);

        return Inertia::render('Personas/TerapeutaEditar', [
            'terapeuta' => $terapeuta,
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
            'usuariosDisponibles' => UsuariosDisponibles::libresMas($terapeuta->user),
        ]);
    }

    public function update(Request $request, $id)
    {
        Terapeuta::findOrFail($id)->update($this->validar($request));

        return redirect()->route('personas.index')
            ->with('success', 'Terapeuta actualizado correctamente.');
    }

    public function destroy($id)
    {
        Terapeuta::findOrFail($id)->delete();

        return redirect()->route('personas.index')
            ->with('success', 'Terapeuta eliminado correctamente.');
    }

    /** Los pacientes del terapeuta, en forma de organigrama. */
    public function pacientes($id)
    {
        $terapeuta = Terapeuta::with('pacientes')->findOrFail($id);

        return Inertia::render('Personas/TerapeutaPacientes', [
            'organigrama' => [
                'id' => $terapeuta->id,
                'nombre' => $terapeuta->nombre_completo,
                'cargo' => 'Terapeuta',
                'correo' => $terapeuta->correo,
                'rol' => 'terapeuta',
                'subalternos' => $terapeuta->pacientes->map(fn($p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre_completo,
                    'cargo' => 'Paciente',
                    'correo' => null,
                    'rol' => 'paciente',
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
            'genero_id' => 'nullable|exists:generos,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'experiencia' => 'nullable|string',
            'certificaciones' => 'nullable|string',
            'cursos' => 'nullable|string',
        ]);
    }
}
