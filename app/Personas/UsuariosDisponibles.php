<?php

namespace App\Personas;

use App\Models\Administrativo;
use App\Models\Encargado;
use App\Models\Terapeuta;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Usuarios que todavía no están amarrados a ninguna persona.
 *
 * Estaba repetido en cinco métodos de PersonasController. Se cuentan los
 * borrados (withTrashed) porque su user_id sigue ocupado.
 */
class UsuariosDisponibles
{
    public static function libres(): Collection
    {
        $usados = collect()
            ->merge(Encargado::withTrashed()->whereNotNull('user_id')->pluck('user_id'))
            ->merge(Terapeuta::withTrashed()->whereNotNull('user_id')->pluck('user_id'))
            ->merge(Administrativo::withTrashed()->whereNotNull('user_id')->pluck('user_id'))
            ->unique()
            ->values();

        return User::whereNotIn('id', $usados)->orderBy('email')->get(['id', 'email']);
    }

    /**
     * Los libres más el que ya tiene la persona que se está editando: si no,
     * su propio usuario no aparecería en el selector.
     */
    public static function libresMas(?User $propio): Collection
    {
        $libres = self::libres();

        if (! $propio) {
            return $libres;
        }

        return $libres->push($propio->only(['id', 'email']))->unique('id')->values();
    }
}
