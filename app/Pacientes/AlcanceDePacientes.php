<?php

namespace App\Pacientes;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Qué pacientes puede ver cada quien.
 *
 * El auxiliar atiende solo en sucursal, así que ve y gestiona **los suyos**:
 * los que él registró, más los que atiende. Los dos, unidos, porque ninguno
 * de los dos alcanza solo — con las citas, un niño recién registrado no sería
 * suyo hasta agendarle algo; con `creado_por`, dejaría de ver a los que le
 * pasaron de otra sede.
 *
 * El encargado ve a sus hijos. El resto del personal ve a todos.
 *
 * Igual que `AlcanceDeCitas`, esto es lo de adentro de la pantalla, no la
 * puerta: el permiso dice si entra, esto dice cuántas filas ve. Y vale para
 * los listados **y para los selectores** de paciente: si un selector se
 * olvidara de llamarlo, ahí se filtraría un niño ajeno.
 */
class AlcanceDePacientes
{
    /**
     * El mismo criterio, para una consulta de expedientes.
     *
     * Un expediente es de quien es su paciente: asi la regla se escribe una
     * vez y no se va desincronizando pantalla por pantalla.
     */
    public static function enExpedientes(Builder $query, User $user): Builder
    {
        return $query->whereHas('paciente', fn (Builder $q) => self::aplicar($q, $user));
    }

    public static function aplicar(Builder $query, User $user): Builder
    {
        // Quien además coordina o administra gana el alcance amplio: es lo que
        // hace útil llevar más de un rol.
        if ($user->hasAnyRole(['administrador', 'coordinador', 'terapeuta', 'pruebas'])) {
            return $query;
        }

        if ($user->hasRole('auxiliar')) {
            return $query->where(
                fn (Builder $q) => $q
                    ->where('creado_por', $user->id)
                    ->orWhereHas('citas', fn (Builder $c) => $c->where('atiende_user_id', $user->id))
            );
        }

        if ($user->hasRole('encargado')) {
            return $user->encargado
                ? $query->where('encargado_id', $user->encargado->id)
                : $query->whereRaw('1 = 0');
        }

        // Un rol que no cae en ningún bloque no ve nada, en vez de verlo todo.
        return $query->whereRaw('1 = 0');
    }
}
