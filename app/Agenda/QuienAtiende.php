<?php

namespace App\Agenda;

use App\Models\Administrativo;
use App\Models\Terapeuta;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Quién puede atender una cita: una Terapeuta, o un Administrativo con cargo de
 * auxiliar (los auxiliares atienden solos en sucursal).
 *
 * Vive aparte porque lo usan la agenda y la ocupación. Antes cada una tenía su
 * propia versión de la misma consulta.
 */
class QuienAtiende
{
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
            ->orderBy('nombres')
            ->get()
            ->map(fn(Terapeuta $t) => self::comoFila('terapeuta', $t->id, $t->nombre_completo, 'Terapeuta'));

        // Los auxiliares se identifican por su cargo en administrativos.
        $auxiliares = Administrativo::query()
            ->whereHas('cargo', fn($q) => $q->where('nombre', 'Auxiliar'))
            ->orderBy('nombres')
            ->get()
            ->map(fn(Administrativo $a) => self::comoFila('auxiliar', $a->id, $a->nombre_completo, 'Auxiliar'));

        return $terapeutas->concat($auxiliares)->values();
    }

    /** Cómo atiende el usuario logueado, o null si no atiende citas. */
    public static function de(User $user): ?array
    {
        if ($user->administrativo) {
            return self::comoFila('auxiliar', $user->administrativo->id, $user->administrativo->nombre_completo, 'Auxiliar');
        }

        if ($user->terapeuta) {
            return self::comoFila('terapeuta', $user->terapeuta->id, $user->terapeuta->nombre_completo, 'Terapeuta');
        }

        return null;
    }

    private static function comoFila(string $tipo, int $id, ?string $nombre, string $rol): array
    {
        return [
            'clave' => "{$tipo}:{$id}",
            'tipo' => $tipo,
            'id' => $id,
            'nombre_completo' => $nombre,
            'rol' => $rol,
        ];
    }
}
