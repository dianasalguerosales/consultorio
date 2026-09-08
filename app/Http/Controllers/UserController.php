<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Terapeuta;
use App\Models\Encargado;
use App\Models\Administrativo;
use App\Models\Genero;
use App\Models\Especialidad;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with(['terapeuta', 'encargado.genero', 'administrativo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($user) {
            return [
                'id' => $user->id,
                'email' => $user->email,
                'status' => $user->status,
                'last_login_at' => $user->last_login_at,
                'roles' => $user->getRoleNames()->toArray(),
                'terapeuta' => $user->terapeuta ? [
                    'id' => $user->terapeuta->id,
                    'nombre_completo' => $user->terapeuta->nombre_completo,
                ] : null,
                'encargado' => $user->encargado ? [
                    'id' => $user->encargado->id,
                    'nombre_completo' => $user->encargado->nombre_completo,
                    'genero' => $user->encargado->genero?->nombre,
                ] : null,
                'administrativo' => $user->administrativo ? [
                    'id' => $user->administrativo->id,
                    'nombre_completo' => $user->administrativo->nombre_completo,
                ] : null,
            ];
            });

        $roles = Role::all();

        return Inertia::render('Usuarios', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'generos' => Genero::where('activo', 1)->orderBy('nombre')->get(),
            'especialidades' => Especialidad::orderBy('nombre')->get(),
            'cargos' => Cargo::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'status' => 'active',
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()
            ->route('usuarios')
            ->with('success', 'Usuario creado correctamente');
    }

    public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'email' => 'required|email|unique:users,email,' . $user->id,
        'status' => 'required|in:active,inactive',
        'roles' => 'array',
        'roles.*' => 'exists:roles,name',
    ]);

    $user->update([
        'email' => $validated['email'],
        'status' => $validated['status'],
    ]);

    if (!empty($validated['roles'])) {
        $user->syncRoles($validated['roles']);
    }

    // 🔑 redirige igual que en store
    return redirect()->route('usuarios')->with('success', 'Usuario actualizado correctamente');
}


    public function destroy(User $user)
    {
        $user->syncRoles([]);
        $user->delete();

        return redirect()->back()->with('success', 'Usuario eliminado correctamente');
    }
}