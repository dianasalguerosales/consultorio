<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Terapeuta;
use App\Models\Encargado;
use App\Models\Administrativo;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['terapeuta', 'encargado', 'administrativo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray(),
                    'terapeuta' => $user->terapeuta ? [
                        'id' => $user->terapeuta->id,
                        'nombre_completo' => $user->terapeuta->nombre_completo,
                    ] : null,
                    'encargado' => $user->encargado ? [
                        'id' => $user->encargado->id,
                        'nombre_completo' => $user->encargado->nombre_completo,
                    ] : null,
                    'administrativo' => $user->administrativo ? [
                        'id' => $user->administrativo->id,
                        'nombre_completo' => $user->administrativo->nombre_completo,
                    ] : null,
                ];
            });

        $roles = Role::all();

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles'    => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles'    => 'array',
            'roles.*'  => 'exists:roles,name',
            'tipo_usuario' => 'required|string|in:administrativo,terapeuta,encargado',
            'nombres'  => 'required|string|max:255',
            'apellidos'=> 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion'=> 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'genero'   => 'nullable|string|max:20',
            'especialidad_id' => 'nullable|exists:especialidades,id',
            'numero_colegiado' => 'nullable|string|max:50',
            'experiencia' => 'nullable|string|max:255',
            'formacion' => 'nullable|string|max:255',
            'certificaciones' => 'nullable|string|max:255',
            'relacion' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        switch ($validated['tipo_usuario']) {
            case 'terapeuta':
                Terapeuta::create([
                    'user_id' => $user->id,
                    'nombres' => $validated['nombres'],
                    'apellidos' => $validated['apellidos'],
                    'correo' => $validated['email'],
                    'telefono' => $validated['telefono'],
                    'especialidad_id' => $validated['especialidad_id'],
                    'numero_colegiado' => $validated['numero_colegiado'],
                    'experiencia' => $validated['experiencia'],
                    'formacion' => $validated['formacion'],
                    'certificaciones' => $validated['certificaciones'],
                ]);
                break;

            case 'encargado':
                Encargado::create([
                    'user_id' => $user->id,
                    'nombres' => $validated['nombres'],
                    'apellidos' => $validated['apellidos'],
                    'correo' => $validated['email'],
                    'telefono' => $validated['telefono'],
                    'relacion' => $validated['relacion'],
                ]);
                break;

            case 'administrativo':
                Administrativo::create([
                    'user_id' => $user->id,
                    'nombres' => $validated['nombres'],
                    'apellidos' => $validated['apellidos'],
                    'correo' => $validated['email'],
                    'telefono' => $validated['telefono'],
                    'direccion' => $validated['direccion'],
                    'fecha_nacimiento' => $validated['fecha_nacimiento'],
                    'genero' => $validated['genero'],
                    'tipo' => $validated['tipo'],
                ]);
                break;
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles'    => 'array',
            'roles.*'  => 'exists:roles,name',
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->back()->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy(User $user)
    {
        $user->syncRoles([]);
        $user->delete();

        return redirect()->back()->with('success', 'Usuario eliminado correctamente');
    }
}