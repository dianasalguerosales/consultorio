<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SendUserCreatedNotification
{
    public function handle(UserCreated $event)
    {
        $nombre = $event->user->terapeuta
            ? $event->user->terapeuta->nombres . ' ' . $event->user->terapeuta->apellidos
            : ($event->user->encargado
                ? $event->user->encargado->nombres . ' ' . $event->user->encargado->apellidos
                : ($event->user->administrativo
                    ? $event->user->administrativo->nombres . ' ' . $event->user->administrativo->apellidos
                    : $event->user->name));

        Notification::create([
            'user_id' => auth()->id() ?? 1,
            'titulo' => 'Usuario creado',
            'descripcion' => "Se ha agregado el usuario {$nombre}.",
            'icono' => 'person_add',
            'fecha' => now(),
            'leida' => false,
        ]);

        Log::info('Listener ejecutado', [
            'user_id' => auth()->id(),
            'nuevo_usuario' => $event->user->id,
        ]);
    }
}