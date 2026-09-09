<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Models\Administrativo;
use App\Models\Encargado;
use App\Models\Terapeuta;
use App\Models\Cargo;
use App\Models\Especialidad;
use App\Models\Genero;
use App\Models\EstadoCivil;
use App\Models\RelacionPaciente;
use App\Models\User;

use Inertia\Inertia;

class PersonasController extends Controller
{
    // Usuarios que todavía no están amarrados a ninguna persona. El usuario ya
    // asignado a alguien NO sale acá: cada modal agrega el suyo a la lista.
    private function usuariosLibres()
    {
        $usados = collect()
            ->merge(Encargado::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->merge(Terapeuta::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->merge(Administrativo::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->unique()
            ->values();

        return User::whereNotIn('id', $usados)->orderBy('email')->get(['id', 'email']);
    }

    public function index()
    {
        $usados = collect()
            ->merge(Encargado::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->merge(Terapeuta::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->merge(Administrativo::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->unique()
            ->values();

        $usuariosDisponibles = User::whereNotIn('id', $usados)->get();

        return Inertia::render('Personas/Index', [
            'administrativos' => Administrativo::with(['cargo', 'especialidad', 'genero'])->get(),
            'terapeutas' => Terapeuta::with(['especialidad', 'genero'])->get(),
            'encargados' => Encargado::with(['relacionPaciente', 'genero', 'estadoCivil'])->get(),
            'cargos' => Cargo::all(),
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
            'estadosCiviles' => EstadoCivil::all(),
            'relacionesPaciente' => RelacionPaciente::all(),
            'usuariosDisponibles' => $usuariosDisponibles,
        ]);
    }

    public function administrativos()
    {
        return Inertia::render('Personas/Administrativos', [
            'administrativos' => Administrativo::with(['cargo', 'especialidad', 'genero', 'user:id,email'])->get(),
            'cargos' => Cargo::all(),
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
            'usuariosDisponibles' => $this->usuariosLibres(),
        ]);
    }

    public function terapeutas()
    {
        return Inertia::render('Personas/Terapeutas', [
            'terapeutas' => Terapeuta::with(['especialidad', 'genero', 'user:id,email'])->get(),
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
            'usuariosDisponibles' => $this->usuariosLibres(),
        ]);
    }

    public function encargados()
    {
        return Inertia::render('Personas/Encargados', [
            'encargados' => Encargado::with(['relacionPaciente', 'genero', 'estadoCivil', 'user:id,email'])->get(),
            'generos' => Genero::all(),
            'estadosCiviles' => EstadoCivil::all(),
            'relacionesPaciente' => RelacionPaciente::all(),
            'usuariosDisponibles' => $this->usuariosLibres(),
        ]);
    }

    public function updateAdministrativo(Request $request, $id)
    {
        $administrativo = Administrativo::findOrFail($id);

        $data = $request->validate([
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

        $administrativo->update($data);

        return redirect()->route('personas.index')
            ->with('success', 'Administrativo actualizado correctamente.');
    }

    public function updateTerapeuta(Request $request, $id)
    {
        $terapeuta = Terapeuta::findOrFail($id);

        $data = $request->validate([
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

        $terapeuta->update($data);

        return redirect()->route('personas.index')
            ->with('success', 'Terapeuta actualizado correctamente.');
    }

    public function updateEncargado(Request $request, $id)
    {
        $encargado = Encargado::findOrFail($id);

        $data = $request->validate([
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

        $encargado->update($data);

        return redirect()->route('personas.index')
            ->with('success', 'Encargado actualizado correctamente.');
    }

    public function destroyAdministrativo($id)
    {
        $administrativo = Administrativo::findOrFail($id);
        $administrativo->delete();

        return redirect()->route('personas.index')
            ->with('success', 'Administrativo eliminado correctamente.');
    }

    public function destroyTerapeuta($id)
    {
        $terapeuta = Terapeuta::findOrFail($id);
        $terapeuta->delete();

        return redirect()->route('personas.index')
            ->with('success', 'Terapeuta eliminado correctamente.');
    }

    public function destroyEncargado($id)
    {
        $encargado = Encargado::findOrFail($id);
        $encargado->delete();

        return redirect()->route('personas.index')
            ->with('success', 'Encargado eliminado correctamente.');
    }

    public function storeAdministrativo(Request $request)
    {
        $data = $request->validate([
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

        Administrativo::create($data);

        return redirect()->route('personas.index')
            ->with('success', 'Administrativo creado correctamente.');
    }

    public function storeTerapeuta(Request $request)
    {
        $data = $request->validate([
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

        Terapeuta::create($data);

        return redirect()->route('personas.index')
            ->with('success', 'Terapeuta creado correctamente.');
    }

    public function storeEncargado(Request $request)
    {
        $data = $request->validate([
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

        Encargado::create($data);

        return redirect()->route('personas.index')
            ->with('success', 'Encargado creado correctamente.');
    }

    public function editAdministrativo($id)
    {
        // Cargar administrativo con su relación user
        $administrativo = Administrativo::with('user')->findOrFail($id);

        // IDs ya usados
        $usados = Encargado::withTrashed()->whereNotNull('user_id')->pluck('user_id')
            ->merge(Terapeuta::withTrashed()->whereNotNull('user_id')->pluck('user_id'))
            ->merge(Administrativo::withTrashed()->whereNotNull('user_id')->pluck('user_id'))
            ->unique()
            ->values();

        // Usuarios libres
        $usuariosLibres = User::whereNotIn('id', $usados)->get();

        // Usuario actual
        if ($administrativo->user) {
            $usuariosLibres->push($administrativo->user);
            $usuariosLibres = $usuariosLibres->unique('id')->values();
        }

        return Inertia::render('Personas/AdministrativoEditar', [
            'administrativo' => $administrativo,
            'usuariosDisponibles' => $usuariosLibres,
            'cargos' => Cargo::all(),
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
        ]);
    }


    public function editEncargado($id)
    {
        $encargado = Encargado::with('user')->findOrFail($id);

        $usados = collect()
            ->merge(Encargado::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->merge(Terapeuta::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->merge(Administrativo::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->unique()
            ->values();

        $usuariosDisponibles = User::whereNotIn('id', $usados)->get();

        if ($encargado->user) {
            $usuariosDisponibles->push($encargado->user);
            $usuariosDisponibles = $usuariosDisponibles->unique('id');
        }

        return Inertia::render('Personas/EncargadoEditar', [
            'encargado' => $encargado,
            'generos' => Genero::all(),
            'estadosCiviles' => EstadoCivil::all(),
            'relacionesPaciente' => RelacionPaciente::all(),
            'usuariosDisponibles' => $usuariosDisponibles,
        ]);
    }

    public function editTerapeuta($id)
    {
        $terapeuta = Terapeuta::with('user')->findOrFail($id);

        $usados = collect()
            ->merge(Encargado::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->merge(Terapeuta::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->merge(Administrativo::withTrashed()->whereNotNull('user_id')->pluck('user_id')->toArray())
            ->unique()
            ->values();

        $usuariosDisponibles = User::whereNotIn('id', $usados)->get();

        if ($terapeuta->user) {
            $usuariosDisponibles->push($terapeuta->user);
            $usuariosDisponibles = $usuariosDisponibles->unique('id');
        }

        return Inertia::render('Personas/TerapeutaEditar', [
            'terapeuta' => $terapeuta,
            'especialidades' => Especialidad::all(),
            'generos' => Genero::all(),
            'usuariosDisponibles' => $usuariosDisponibles,
        ]);
    }
}
