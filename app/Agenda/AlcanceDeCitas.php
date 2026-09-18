<?php

namespace App\Agenda;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Qué citas puede ver cada quien.
 *
 * Un permiso dice a qué pantalla se entra; esto dice cuáles filas se ven
 * adentro, que es otra pregunta. Vive aparte porque la responden dos pantallas
 * —la agenda y los pagos— y con la regla escrita dos veces bastaría con
 * corregir una sola para que la otra filtre de más o de menos.
 *
 * El orden de los bloques importa: quien además es administrador o coordinador
 * gana el alcance amplio, que es lo que hace útil tener más de un rol.
 *
 * Un rol que no cae en ningún bloque no ve nada, en vez de verlo todo por
 * omisión: si mañana aparece un rol nuevo, el error será que no ve lo suyo y no
 * que ve lo de todos.
 */
class AlcanceDeCitas
{
    public static function aplicar(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['administrador', 'coordinador'])) {
            return $query;
        }

        // El auxiliar atiende solo en sucursal: lo suyo son las citas que
        // atiende él, y de ahí salen también los cobros que le tocan.
        if ($user->hasRole('auxiliar')) {
            return $user->administrativo
                ? $query->atendidasPor($user->administrativo)
                : $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('terapeuta')) {
            return $user->terapeuta
                ? $query->atendidasPor($user->terapeuta)
                : $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('encargado')) {
            return $user->encargado
                ? $query->whereHas(
                    'paciente',
                    fn ($q) => $q->where('encargado_id', $user->encargado->id)
                )
                : $query->whereRaw('1 = 0');
        }

        // El rol de pruebas consulta todo el consultorio, sin tocar nada.
        if ($user->can('ver pagos') && $user->hasRole('pruebas')) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }
}
