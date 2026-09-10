<?php

namespace App\Pagos;

use Illuminate\Support\Carbon;

/**
 * El cierre de pagos se hace cada 15 días: del 1 al 15 y del 16 al fin de mes.
 *
 * Vive aparte porque el rango se calcula en la vista de Pagos, en los totales
 * y —cuando exista— en el informe de cobros.
 */
class Quincena
{
    public function __construct(
        public readonly Carbon $desde,
        public readonly Carbon $hasta,
    ) {}

    /** La quincena donde cae la fecha dada; por omisión, hoy. */
    public static function de(?Carbon $fecha = null): self
    {
        $fecha = $fecha ?? Carbon::today();

        return $fecha->day <= 15
            ? new self($fecha->copy()->startOfMonth(), $fecha->copy()->startOfMonth()->addDays(14))
            : new self($fecha->copy()->startOfMonth()->addDays(15), $fecha->copy()->endOfMonth());
    }

    /**
     * Rango pedido por el usuario, con la quincena actual como respaldo.
     *
     * Si vienen invertidas se ordenan en lugar de devolver una tabla vacía.
     */
    public static function desdeFiltro(?string $desde, ?string $hasta): self
    {
        if (! $desde && ! $hasta) {
            return self::de();
        }

        $actual = self::de();
        $inicio = $desde ? Carbon::parse($desde) : $actual->desde;
        $fin = $hasta ? Carbon::parse($hasta) : $actual->hasta;

        return $inicio->lte($fin) ? new self($inicio, $fin) : new self($fin, $inicio);
    }

    public function inicio(): string
    {
        return $this->desde->toDateString();
    }

    public function fin(): string
    {
        return $this->hasta->toDateString();
    }

    public function paraLaVista(): array
    {
        return ['desde' => $this->inicio(), 'hasta' => $this->fin()];
    }
}
