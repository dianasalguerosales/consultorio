<?php

namespace App\Agenda;

use App\Models\Administrativo;
use App\Models\Terapeuta;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Quién puede atender una cita: una Terapeuta, o un Administrativo con uno de
 * los cargos de CARGOS_QUE_ATIENDEN (los auxiliares atienden solos en sucursal;
 * al administrador también se le agenda).
 *
 * Vive aparte porque lo usan la agenda y la ocupación. Antes cada una tenía su
 * propia versión de la misma consulta.
 */
class QuienAtiende
{
    /**
     * Los cargos administrativos a los que se les puede agendar. El `tipo` que
     * viaja en la cita sigue siendo 'auxiliar' para todos: es la clave del
     * morph a Administrativo, no el puesto de la persona. El puesto va en `rol`.
     */
    public const CARGOS_QUE_ATIENDEN = ['Auxiliar', 'Administrador'];

    /** Se valida contra esta lista, no contra cualquier clase que llegue del formulario. */
    public const TIPOS = [
        'terapeuta' => Terapeuta::class,
        'auxiliar' => Administrativo::class,
    ];

    public static function tipos(): array
    {
        return array_keys(self::TIPOS);
    }

    public static function clase(string $tipo): ?string
    {
        return self::TIPOS[$tipo] ?? null;
    }

    /** De clase Eloquent a la clave que usa la vista. */
    public static function tipoDe(?string $clase): ?string
    {
        if (! $clase) {
            return null;
        }

        return array_search($clase, self::TIPOS, true) ?: null;
    }

    /**
     * Todo el personal que atiende, en una sola lista.
     *
     * `clave` es "tipo:id" porque el terapeuta 1 y el auxiliar 1 son personas
     * distintas. Trae más llaves de las que usa cada pantalla: la ocupación lee
     * `clave`, `nombre_completo` y `rol`; el modal de cita, `tipo` e `id`.
     */
    public static function todos(): Collection
    {
        $terapeutas = Terapeuta::query()
            ->with('especialidad:id,nombre')
            ->orderBy('nombres')
            ->get()
            ->map(fn(Terapeuta $t) => self::comoFila('terapeuta', $t->id, $t->nombre_completo, 'Terapeuta', $t->especialidad?->nombre));

        // El personal administrativo al que se le agenda, por su cargo.
        $administrativos = Administrativo::query()
            ->with(['especialidad:id,nombre', 'cargo:id,nombre'])
            ->whereHas('cargo', fn($q) => $q->whereIn('nombre', self::CARGOS_QUE_ATIENDEN))
            ->orderBy('nombres')
            ->get()
            ->map(fn(Administrativo $a) => self::comoFila(
                'auxiliar',
                $a->id,
                $a->nombre_completo,
                $a->cargo?->nombre ?? 'Auxiliar',
                $a->especialidad?->nombre
            ));

        return $terapeutas->concat($administrativos)->values();
    }

    /** Cómo atiende el usuario logueado, o null si no atiende citas. */
    public static function de(User $user): ?array
    {
        if ($user->administrativo) {
            return self::comoFila(
                'auxiliar',
                $user->administrativo->id,
                $user->administrativo->nombre_completo,
                $user->administrativo->cargo?->nombre ?? 'Auxiliar',
                $user->administrativo->especialidad?->nombre
            );
        }

        if ($user->terapeuta) {
            return self::comoFila('terapeuta', $user->terapeuta->id, $user->terapeuta->nombre_completo, 'Terapeuta', $user->terapeuta->especialidad?->nombre);
        }

        return null;
    }

    /**
     * Si a esta persona se le pueden agendar citas.
     *
     * `de()` devuelve ficha para cualquier administrativo, tenga el cargo que
     * tenga; esto responde la otra pregunta: si aparece o no en `todos()`.
     */
    public static function atiendeCitas(User $user): bool
    {
        if ($user->terapeuta) {
            return true;
        }

        return (bool) $user->administrativo?->cargo
            && in_array($user->administrativo->cargo->nombre, self::CARGOS_QUE_ATIENDEN, true);
    }

    private static function comoFila(string $tipo, int $id, ?string $nombre, string $rol, ?string $especialidad = null): array
    {
        return [
            'clave' => "{$tipo}:{$id}",
            'tipo' => $tipo,
            'id' => $id,
            'nombre_completo' => $nombre,
            'rol' => $rol,
            // La especialidad sale de la persona: al elegirla, la pantalla la
            // muestra debajo del nombre en vez de pedir que se escoja aparte.
            'especialidad' => $especialidad,
        ];
    }
}
