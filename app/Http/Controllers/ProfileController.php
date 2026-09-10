<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'administrativo.cargo', 'administrativo.especialidad', 'administrativo.genero',
            'terapeuta.especialidad', 'terapeuta.genero',
            'encargado.relacionPaciente', 'encargado.genero', 'encargado.estadoCivil',
        ]);

        // Un usuario es a lo más una de las tres cosas. Se manda la persona y
        // su tipo, y la vista arma la misma ficha para cualquiera de ellos.
        [$tipo, $persona] = match (true) {
            (bool) $user->administrativo => ['administrativo', $user->administrativo],
            (bool) $user->terapeuta => ['terapeuta', $user->terapeuta],
            (bool) $user->encargado => ['encargado', $user->encargado],
            default => [null, null],
        };

        if ($persona) {
            $persona->setRelation('user', $user->only(['id', 'email']));
        }

        return Inertia::render('Perfil', [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'tipo' => $tipo,
            'persona' => $persona,
            // Cuántos pacientes tiene a cargo, cuando aplica.
            'pacientes' => $user->terapeuta?->pacientes()->count()
                ?? $user->encargado?->pacientes()->count(),
        ]);
    }

    public function configuracion(Request $request)
    {
        return Inertia::render('Configuracion', [
            'user' => $request->user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'La contraseña actual no es correcta.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}