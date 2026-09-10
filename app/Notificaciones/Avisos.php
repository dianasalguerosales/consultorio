<?php

namespace App\Notificaciones;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Escribe en la tabla `notifications`, que es la que lee la campana del layout.
 *
 * No usa el sistema de notificaciones de Laravel a propósito: la tabla del
 * proyecto es propia (titulo, descripcion, icono, fecha, leida) y la campana ya
 * está construida sobre ella.
 */
class Avisos
{
    public static function a(User $user, string $titulo, string $descripcion, string $icono = 'notifications'): void
    {
        Notification::create([
            'user_id' => $user->id,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'icono' => $icono,
            'fecha' => now(),
            'leida' => false,
        ]);
    }

    /** El mismo aviso a varios usuarios, una fila por cada uno. */
    public static function aTodos(Collection $usuarios, string $titulo, string $descripcion, string $icono = 'notifications'): void
    {
        foreach ($usuarios as $user) {
            self::a($user, $titulo, $descripcion, $icono);
        }
    }

    /** Quienes resuelven las solicitudes: coordinación y administración. */
    public static function quienesAutorizan(): Collection
    {
        return User::role(['administrador', 'coordinador'])->get();
    }
}
