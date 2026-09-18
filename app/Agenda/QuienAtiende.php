<?php

namespace App\Agenda;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Quién puede atender una cita: un usuario con un rol que atiende.
 *
 * Antes se resolvía por la tabla donde estuviera la ficha de la persona —una
 * Terapeuta, o un Administrativo con cierto cargo—, y la cita guardaba además
 * de qué tabla se trataba. Eso obligaba a que un administrador que también
 * atiende tuviera una ficha de terapeuta duplicada.
 *
 * Ahora manda el rol: quien administra y atiende lleva los dos roles y aparece
 * una sola vez, con su ficha única. La cita apunta al usuario.
 *
 * Vive aparte porque lo usan la agenda, la ocupación y los programas.
 */
class QuienAtiende
{
    /**
     * Los roles a cuyos usuarios se les puede agendar.
     *
     * Al administrador no se le agenda por ser administrador: si atiende, lleva
     * además el rol de terapeuta. Es la regla que hace útil tener varios roles.
     */
    public const ROLES_QUE_ATIENDEN = ['terapeuta', 'auxiliar'];

    /**
     * Todo el personal que atiende, en una sola lista.
     *
     * `clave` es el id del usuario. Antes era "tipo:id" porque el terapeuta 1 y
     * el auxiliar 1 eran personas distintas; con el usuario de por medio el id
     * ya es único y esa ambigüedad desapareció.
     */
    public static function todos(): Collection
    {
        return User::query()
            ->role(self::ROLES_QUE_ATIENDEN)
            ->with(['terapeuta.especialidad:id,nombre', 'administrativo.especialidad:id,nombre', 'administrativo.cargo:id,nombre'])
            ->get()
            ->map(fn (User $u) => self::comoFila($u))
            ->sortBy('nombre_completo')
            ->values();
    }

    /** Cómo atiende el usuario logueado, o null si no atiende citas. */
    public static function de(User $user): ?array
    {
        return self::atiendeCitas($user) ? self::comoFila($user) : null;
    }

    /** Si a este usuario se le pueden agendar citas. */
    public static function atiendeCitas(User $user): bool
    {
        return $user->hasAnyRole(self::ROLES_QUE_ATIENDEN);
    }

    private static function comoFila(User $user): array
    {
        $persona = $user->terapeuta ?? $user->administrativo;

        return [
            // Se conservan las tres llaves: `clave` la usan la ocupación y los
            // filtros; `id` el modal de cita. Ahora las tres son el usuario.
            'clave' => (string) $user->id,
            'id' => $user->id,
            'nombre_completo' => $user->nombre_completo,
            'rol' => self::rolVisible($user),
            // La especialidad sale de la ficha: al elegir a la persona, la
            // pantalla la muestra debajo del nombre en vez de pedirla aparte.
            'especialidad' => $persona?->especialidad?->nombre,
        ];
    }

    /** El puesto que se le enseña al usuario, no la lista entera de sus roles. */
    private static function rolVisible(User $user): string
    {
        if ($user->hasRole('terapeuta')) {
            return 'Terapeuta';
        }

        return $user->administrativo?->cargo?->nombre ?? 'Auxiliar';
    }
}
