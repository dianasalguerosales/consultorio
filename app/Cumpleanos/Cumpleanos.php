<?php

namespace App\Cumpleanos;

use App\Models\Administrativo;
use App\Models\Encargado;
use App\Models\Expediente;
use App\Models\Terapeuta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Quién cumple años, entre pacientes y personal.
 *
 * El filtro se hace en PHP y no en la consulta porque sacar el mes y el día de
 * una fecha se escribe distinto en SQLite y en MySQL, y son pocas filas.
 */
class Cumpleanos
{
    /**
     * Ayer, hoy y mañana.
     *
     * Ayer también cuenta: si nadie abrió el sistema ese día, el saludo se
     * pierde sin que nadie se entere. Mañana sirve para preparar con un día de
     * anticipación.
     */
    public static function deLosTresDias(): array
    {
        $personas = self::todas();

        return [
            'ayer' => self::losDe($personas, Carbon::yesterday()),
            'hoy' => self::losDe($personas, Carbon::today()),
            'manana' => self::losDe($personas, Carbon::tomorrow()),
        ];
    }

    /** Si la persona cumple años el día dado. */
    public static function esCumpleanos(?string $nacimiento, ?Carbon $dia = null): bool
    {
        if (! $nacimiento) {
            return false;
        }

        return Carbon::parse($nacimiento)->format('m-d') === ($dia ?? Carbon::today())->format('m-d');
    }

    /* ---------- Fuentes ---------- */

    private static function todas(): Collection
    {
        return self::pacientes()
            ->concat(self::personal(Terapeuta::class, 'Terapeuta'))
            ->concat(self::personal(Administrativo::class, 'Personal'))
            ->concat(self::personal(Encargado::class, 'Encargado'));
    }

    /** La fecha de nacimiento del niño vive en su expediente, no en `pacientes`. */
    private static function pacientes(): Collection
    {
        return Expediente::query()
            ->whereNotNull('fecha_nacimiento')
            ->with('paciente:id,nombres,apellidos,genero_id')
            ->get()
            ->map(fn (Expediente $e) => [
                'nombre' => trim("{$e->nombres} {$e->apellidos}"),
                'tipo' => 'Paciente',
                'nacimiento' => $e->fecha_nacimiento,
                'genero' => $e->paciente?->genero_id,
                'paciente_id' => $e->paciente_id,
            ]);
    }

    private static function personal(string $modelo, string $tipo): Collection
    {
        return $modelo::query()
            ->whereNotNull('fecha_nacimiento')
            ->get()
            ->map(fn ($p) => [
                'nombre' => $p->nombre_completo,
                'tipo' => $tipo,
                'nacimiento' => $p->fecha_nacimiento,
                'genero' => $p->genero_id ?? null,
                'paciente_id' => null,
            ]);
    }

    /* ---------- Filtro ---------- */

    private static function losDe(Collection $personas, Carbon $dia): array
    {
        return $personas
            ->filter(fn (array $p) => self::esCumpleanos((string) $p['nacimiento'], $dia))
            ->map(fn (array $p) => [
                ...$p,
                'nacimiento' => Carbon::parse($p['nacimiento'])->toDateString(),
                'edad' => Carbon::parse($p['nacimiento'])->diffInYears($dia),
            ])
            ->sortBy('nombre')
            ->values()
            ->all();
    }
}
