<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;




class SendUserCreatedNotification
{
    public function handle(UserCreated $event)
    {
        Notification::create([
            'user_id' => auth()->id() ?? 1, // Asigna el ID del usuario autenticado o 1 si no hay usuario autenticado
            'titulo' => 'Usuario creado',
            'descripcion' => "Se ha agregado el usuario {$event->user->name}.",
            'icono' => 'person_add',
            'fecha' => now()
        ]);
        Log::info('Listener ejecutado', [
    'user_id' => auth()->id(),
    'nuevo_usuario' => $event->user->id,
]);
    }
}

    