<?php

namespace App\Programas;

use InvalidArgumentException;

/**
 * Reparte el costo de un programa entre sus citas.
 *
 * La regla es que la suma de las citas dé exactamente el costo del paquete: si
 * cada cita se redondeara por su cuenta, Q800 entre 22 daría Q799.92 y el
 * cierre quincenal arrastraría el descuadre.
 */
class RepartoPrecio
{
    /**
     * Montos que suman exactamente $total, uno por cita.
     *
     * Trabaja en centavos —los flotantes no representan 0.01 con exactitud— y
     * los centavos que sobran de la división se reparten de a uno entre las
     * primeras citas, en vez de cargarlos todos a la última.
     */
    public static function en(float $total, int $partes): array
    {
        if ($partes < 1) {
            throw new InvalidArgumentException('Un programa necesita al menos una cita.');
        }

        $centavos = (int) round($total * 100);
        $base = intdiv($centavos, $partes);
        $sobrantes = $centavos - $base * $partes;

        return array_map(
            fn (int $i) => round(($base + ($i < $sobrantes ? 1 : 0)) / 100, 2),
            range(0, $partes - 1)
        );
    }
}
