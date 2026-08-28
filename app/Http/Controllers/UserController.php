<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Events\UserCreated;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::with('roles')->get()->map(function ($user) {
            return [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(), // ✅ solo nombres
            ];
        });

        $roles = Role::all();

        return Inertia::render('Usuarios', [
            'usuarios' => $usuarios,
            'roles'    => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'roles'    => 'array',
            'roles.*'  => 'exists:roles,name',
        ]);

        $user->update(['name' => $validated['name']]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->back()->with('success', 'Usuario actualizado correctamente');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles'    => 'array',
            'roles.*'  => 'exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        if (!empty($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        UserCreated::dispatch($user);

        return redirect()->route('usuarios')->with('success', 'Usuario creado correctamente');
    }


    public function destroy(User $user)
    {
        $user->syncRoles([]); 
        $user->delete();

        return redirect()->back()->with('success', 'Usuario eliminado correctamente');
    }
} 