<?php

namespace App\Pagos;

use App\Models\AsignacionPrograma;
use App\Models\Cita;
use App\Models\Pago;
use Illuminate\Support\Collection;

/**
 * Cobrar un paquete completo de una sola vez.
 *
 * El cliente paga el programa, no sesión por sesión. Adentro el pago se reparte
 * entre las citas del paquete: así cada cita queda cobrada, y los totales, el
 * desglose por tipo de pago y el cierre de mes —que leen `pagos` por cita—
 * siguen cuadrando sin tener que entender qué es un paquete.
 *
 * No hace falta columna nueva en `pagos`: las citas ya apuntan a su paquete por
 * `citas.asignacion_programa_id`, y los pagos del paquete son los de esas citas.
 */
class PagoDePaquete
{
    /**
     * Las citas que se cobran.
     *
     * Una cancelada no se cobra: es la misma regla que usa el total "esperado"
     * de la vista de Pagos.
     */
    public static function cobrables(AsignacionPrograma $asignacion): Collection
    {
        return $asignacion->citas()
            ->with('pago')
            ->whereHas('estadoCita', fn($q) => $q->where('nombre', '!=', 'Cancelada'))
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();
    }

    /** Lo que le falta cobrar a una cita. */
    public static function faltaEn(Cita $cita): float
    {
        $falta = (float) $cita->precio_aplicado - (float) ($cita->pago?->monto ?? 0);

        return max(0, round($falta, 2));
    }

    /** Lo que cuesta el paquete descontando lo cancelado. */
    public static function esperado(AsignacionPrograma $asignacion): float
    {
        return round((float) self::cobrables($asignacion)->sum('precio_aplicado'), 2);
    }

    /** Lo que ya entró por este paquete. */
    public static function cobrado(AsignacionPrograma $asignacion): float
    {
        return round(
            (float) Pago::whereIn('cita_id', $asignacion->citas()->select('id'))->sum('monto'),
            2
        );
    }

    /**
     * Lo que falta cobrar, en dinero.
     *
     * Se calcula contra lo cobrado y no contando citas sin pago: después de un
     * abono todas las citas tienen pago, y por ahí el paquete se veía saldado
     * habiendo entrado la mitad.
     */
    public static function saldo(AsignacionPrograma $asignacion): float
    {
        return max(0, round(self::esperado($asignacion) - self::cobrado($asignacion), 2));
    }

    /** Las citas a las que todavía les falta algo. */
    public static function pendientes(AsignacionPrograma $asignacion): Collection
    {
        return self::cobrables($asignacion)->filter(fn(Cita $c) => self::faltaEn($c) > 0)->values();
    }

    /**
     * Cuánto le toca a cada cita de lo que se está pagando.
     *
     * Si se paga el saldo completo, cada cita recibe exactamente lo que le
     * falta: repartir parejo dejaría alguna un centavo corta —el reparto del
     * paquete no siempre da parejo— y esa cita quedaría como pago parcial
     * teniendo todo pagado.
     *
     * Un abono se reparte en proporción a lo que cada cita debe, trabajando en
     * centavos para que la suma dé exactamente el monto recibido.
     *
     * @return array<int, float> monto por cita, en el orden de $citas
     */
    public static function repartir(Collection $citas, float $monto): array
    {
        $faltan = $citas->map(fn(Cita $c) => (int) round(self::faltaEn($c) * 100))->all();
        $total = array_sum($faltan);
        $centavos = (int) round($monto * 100);

        if ($total <= 0) {
            return array_fill(0, count($faltan), 0.0);
        }

        if ($centavos >= $total) {
            return array_map(fn(int $c) => round($c / 100, 2), $faltan);
        }

        // Proporcional, y los centavos que sobran de la división se reparten de
        // a uno entre las primeras citas, igual que RepartoPrecio.
        $partes = array_map(fn(int $f) => intdiv($centavos * $f, $total), $faltan);
        $sobrantes = $centavos - array_sum($partes);

        foreach (array_keys($partes) as $i) {
            if ($sobrantes <= 0) {
                break;
            }

            if ($partes[$i] < $faltan[$i]) {
                $partes[$i]++;
                $sobrantes--;
            }
        }

        return array_map(fn(int $c) => round($c / 100, 2), $partes);
    }
}
