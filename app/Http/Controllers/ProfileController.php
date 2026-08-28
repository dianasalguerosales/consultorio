<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Terapeuta;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'roles',
            'terapeuta' => fn($q) => $q->withCount('pacientes'),
            'encargado' => fn($q) => $q->withCount('pacientes'),
        ]);

        return Inertia::render('Perfil', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'direccion' => $user->direccion,
                'fecha_nacimiento' => $user->fecha_nacimiento,
                'genero' => $user->genero,
                'nacionalidad' => $user->nacionalidad,
                'estado_civil' => $user->estado_civil,
                'avatar' => $user->avatar,
                'idioma' => $user->idioma,
                'notificaciones' => $user->notificaciones,
                'historial_accesos' => $user->historial_accesos,
                'ultima_sesion' => $user->ultima_sesion,
                'estado' => $user->estado,
                'created_at' => $user->created_at ? $user->created_at->format('d-m-Y') : null,
                'updated_at' => $user->updated_at ? $user->updated_at->format('d-m-Y') : null,
                'roles' => $user->getRoleNames(),
            ],
            'terapeuta' => $user->terapeuta ? [
                'nombre' => $user->terapeuta->nombre,
                'especialidad' => $user->terapeuta->especialidad,
                'fecha_nacimiento' => $user->terapeuta->fecha_nacimiento ? Carbon::parse($user->terapeuta->fecha_nacimiento)->format('d-m-Y') : null,
                'telefono' => $user->terapeuta->telefono,
                'correo' => $user->terapeuta->correo,
                'numero_colegiado' => $user->terapeuta->numero_colegiado,
                'experiencia' => $user->terapeuta->experiencia,
                'formacion' => $user->terapeuta->formacion,
                'certificaciones' => $user->terapeuta->certificaciones,
                'pacientes_count' => $user->terapeuta->pacientes_count,
            ] : null,
            'encargado' => $user->encargado ? [
                'nombre' => $user->encargado->nombre,
                'telefono' => $user->encargado->telefono,
                'correo' => $user->encargado->correo,
                'relacion' => $user->encargado->relacion,
                'pacientes_count' => $user->encargado->pacientes_count,
            ] : null,
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
