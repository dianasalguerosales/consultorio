<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        if ($user) {
            $user->load(['terapeuta', 'encargado', 'administrativo']);
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                    'nombre' => $user->terapeuta
                        ? $user->terapeuta->nombres . ' ' . $user->terapeuta->apellidos
                        : ($user->encargado
                            ? $user->encargado->nombres . ' ' . $user->encargado->apellidos
                            : ($user->administrativo
                                ? $user->administrativo->nombres . ' ' . $user->administrativo->apellidos
                                : $user->name)),
                ] : null,
            ],

            // La campana vive en el layout, así que las notificaciones tienen
            // que viajar en todas las páginas, no solo en /notificaciones.
            'notificaciones' => $user
                ? Notification::where('user_id', $user->id)
                    ->latest('fecha')
                    ->take(20)
                    ->get(['id', 'titulo', 'descripcion', 'icono', 'fecha', 'leida'])
                : [],
        ]);
    }
}