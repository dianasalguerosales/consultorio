<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'auth' => function () {
                $user = auth()->user();

                return [
                    'user' => $user ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $user->getRoleNames()->toArray(),
                        'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
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
