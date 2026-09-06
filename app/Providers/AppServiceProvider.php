<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Inertia::share([
            'auth' => function () {
                $user = auth()->user();

                if ($user) {
                    $user->load(['terapeuta', 'encargado', 'administrativo']);
                }

                return [
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
                                    : null)),
                    ] : null,
                ];
            },
            'notificaciones' => function () {
                if (auth()->check()) {
                    return Notification::where('user_id', auth()->id())
                        ->latest()
                        ->take(20)
                        ->get();
                }
                return [];
            },
        ]);
    }
}
