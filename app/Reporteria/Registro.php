<?php

namespace App\Reporteria;

use App\Reporteria\Informes\CierreDeMes;
use App\Reporteria\Informes\CitasPacientes;
use App\Reporteria\Informes\EncargadosPacientes;
use App\Reporteria\Informes\ExpedienteClinico;
use App\Reporteria\Informes\PacientesDiagnosticos;
use App\Reporteria\Informes\PacientesProfesionales;
use App\Reporteria\Informes\PacientesServicios;
use App\Reporteria\Informes\ProfesionalesAgenda;
use App\Reporteria\Informes\Evoluciones;

/**
 * Todos los informes disponibles. Agregar uno es crear su clase en Informes/ y
 * sumarla a esta lista.
 *
 * Funciona como lista blanca: el controlador solo puede pedir informes que
 * estén acá, y de cada uno solo las columnas y filtros que la clase declara.
 */
class Registro
{
    // El orden es el que ve el usuario. Evoluciones y Cierre de mes van
    // primeros porque son los que se consultan a diario.
    private const INFORMES = [
        Evoluciones::class,
        CierreDeMes::class,
        PacientesDiagnosticos::class,
        PacientesProfesionales::class,
        PacientesServicios::class,
        CitasPacientes::class,
        ExpedienteClinico::class,
        EncargadosPacientes::class,
        ProfesionalesAgenda::class,
    ];

    /** @return Informe[] indexados por su clave */
    public static function todos(): array
    {
        $informes = [];

        foreach (self::INFORMES as $clase) {
            $informe = new $clase();
            $informes[$informe->clave()] = $informe;
        }

        return $informes;
    }

    public static function buscar(string $clave): ?Informe
    {
        return self::todos()[$clave] ?? null;
    }

    /** El catálogo que consume el frontend, en el orden de la lista. */
    public static function catalogo(): array
    {
        return array_values(array_map(
            fn(Informe $i) => $i->metadatos(),
            self::todos()
        ));
    }
}
