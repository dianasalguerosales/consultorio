<?php

namespace App\Http\Controllers;

use App\Models\Administrativo;
use App\Models\Cargo;
use App\Models\Especialidad;
use App\Models\Genero;
use App\Personas\Personas;
use App\Personas\UsuariosDisponibles;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Salió de PersonasController, que tenía los tres CRUD de personas —
 * administrativos, terapeutas y encargados — calcados en un solo archivo.
 */
class AdministrativoController extends Controller
{
    public function index()
    {
        return Inertia::render('Personas/Administrativos', [
            'administrativos' => Administrativo::with(['cargo', 'especialidad', 'genero', 'user.roles'])->get(),
            'cargos' => Cargo::all(),
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
            'usuariosDisponibles' => UsuariosDisponibles::libres(),
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $persona = Administrativo::create($this->validar($request));

        // Nace con el rol que le toca por su tipo o su cargo.
        Personas::asignarRolInicial($persona->load('user', 'cargo'));

        return redirect()->route('personas.index')
            ->with('success', 'Administrativo creado correctamente.');
    }

    public function edit($id)
    {
        $administrativo = Administrativo::with('user')->findOrFail($id);

        return Inertia::render('Personas/AdministrativoEditar', [
            'administrativo' => $administrativo,
            'usuariosDisponibles' => UsuariosDisponibles::libresMas($administrativo->user),
            'cargos' => Cargo::all(),
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        Administrativo::findOrFail($id)->update($this->validar($request));

        return redirect()->route('personas.index')
            ->with('success', 'Administrativo actualizado correctamente.');
    }

    public function destroy($id)
    {
        Administrativo::findOrFail($id)->delete();

        return redirect()->route('personas.index')
            ->with('success', 'Administrativo eliminado correctamente.');
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
            'cargo_id' => 'nullable|exists:cargos,id',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'experiencia' => 'nullable|string',
            'certificaciones' => 'nullable|string',
            'cursos' => 'nullable|string',
        ]);
    }
}
