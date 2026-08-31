<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Terapeuta;
use App\Models\Encargado;
use App\Models\Administrativo;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['roles','terapeuta','encargado','administrativo'])->get()->map(function ($user) {
            return [
                'id'    => $user->id,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'terapeuta' => $user->terapeuta,
                'encargado' => $user->encargado,
                'administrativo' => $user->administrativo,
            ];
        });

        $roles = Role::all();

        return inertia('Usuarios', [
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
            'nombre'   => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'direccion'=> 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'genero'   => 'nullable|string|max:20',
            'especialidad' => 'nullable|string|max:255',
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

        // Crear registro en tabla correspondiente
        switch ($validated['tipo_usuario']) {
            case 'terapeuta':
                Terapeuta::create([
                    'user_id' => $user->id,
                    'nombre' => $validated['nombre'],
                    'correo' => $validated['email'],
                    'telefono' => $validated['telefono'],
                    'especialidad' => $validated['especialidad'],
                    'numero_colegiado' => $validated['numero_colegiado'],
                    'experiencia' => $validated['experiencia'],
                    'formacion' => $validated['formacion'],
                    'certificaciones' => $validated['certificaciones'],
                ]);
                break;

            case 'encargado':
                Encargado::create([
                    'user_id' => $user->id,
                    'nombre' => $validated['nombre'],
                    'correo' => $validated['email'],
                    'telefono' => $validated['telefono'],
                    'relacion' => $validated['relacion'],
                ]);
                break;

            case 'administrativo':
                Administrativo::create([
                    'user_id' => $user->id,
                    'nombre' => $validated['nombre'],
                    'correo' => $validated['email'],
                    'telefono' => $validated['telefono'],
                    'direccion' => $validated['direccion'],
                    'fecha_nacimiento' => $validated['fecha_nacimiento'],
                    'genero' => $validated['genero'],
                    'tipo' => $validated['tipo'],
                ]);
                break;
        }

        return redirect()->route('usuarios')->with('success', 'Usuario creado correctamente');
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