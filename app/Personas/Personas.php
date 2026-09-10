<?php

namespace App\Personas;

use App\Models\Administrativo;
use App\Models\Encargado;
use App\Models\Terapeuta;
use Illuminate\Database\Eloquent\Model;

/**
 * Los tres tipos de persona. Funciona como lista blanca: las rutas que reciben
 * un `{tipo}` solo atienden estas claves.
 */
class Personas
{
    private const TIPOS = [
        'administrativo' => Administrativo::class,
        'terapeuta' => Terapeuta::class,
        'encargado' => Encargado::class,
    ];

    public static function modelo(string $tipo): ?string
    {
        return self::TIPOS[$tipo] ?? null;
    }

    public static function tipos(): array
    {
        return array_keys(self::TIPOS);
    }

    /**
     * El rol con el que nace una persona.
     *
     * Los cargos de `cargos` se llaman igual que los roles, así que el del
     * administrativo sale de su cargo: Administrador → administrador,
     * Coordinador → coordinador, Auxiliar → auxiliar, Pruebas → pruebas.
     * Sin cargo no hay rol que asignar.
     */
    public static function rolPorDefecto(Model $persona): ?string
    {
        return match (true) {
            $persona instanceof Administrativo => self::rolDelCargo($persona),
            $persona instanceof Terapeuta => 'terapeuta',
            $persona instanceof Encargado => 'encargado',
            default => null,
        };
    }

    /** Le da su rol inicial al usuario de la persona, si ya tiene uno ligado. */
    public static function asignarRolInicial(Model $persona): void
    {
        $rol = self::rolPorDefecto($persona);
        $user = $persona->user;

        // Sin usuario no hay a quién asignarle el rol: se le pone después,
        // cuando se le ligue uno desde el formulario de la persona.
        //
        // Y solo si el usuario no tiene ninguno: este rol es el de nacimiento,
        // no debe pisar lo que el administrador haya ajustado a mano.
        if (! $rol || ! $user || $user->roles()->exists()) {
            return;
        }

        $user->assignRole($rol);
    }

    private static function rolDelCargo(Administrativo $persona): ?string
    {
        $cargo = $persona->cargo?->nombre;

        return $cargo ? strtolower($cargo) : null;
    }
}
