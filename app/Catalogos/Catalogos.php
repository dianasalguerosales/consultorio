<?php

namespace App\Catalogos;

use App\Models\Escolaridad;
use App\Models\Especialidad;
use App\Models\EstadoExpediente;
use App\Models\EstadoSesion;
use App\Models\Modalidad;
use App\Models\Servicio;

/**
 * Los catálogos de /parametros. Todos tienen `nombre` y `activo`, así que
 * comparten el mismo CRUD, la misma tabla y el mismo formulario.
 *
 * Agregar uno es sumar una entrada acá: no hay controlador, ruta ni página que
 * escribir. Y funciona como lista blanca: el CRUD solo atiende estas claves.
 */
class Catalogos
{
    private const CATALOGOS = [
        'servicios' => [
            'modelo' => Servicio::class,
            'titulo' => 'Servicio',
            'etiqueta' => 'Servicios',
            'genero' => 'o',
            'conDescripcion' => true,
        ],
        'especialidades' => [
            'modelo' => Especialidad::class,
            'titulo' => 'Especialidad',
            'etiqueta' => 'Especialidades',
            'genero' => 'a',
            'conDescripcion' => false,
        ],
        'escolaridades' => [
            'modelo' => Escolaridad::class,
            'titulo' => 'Escolaridad',
            'etiqueta' => 'Escolaridades',
            'genero' => 'a',
            'conDescripcion' => false,
        ],
        'estado-expedientes' => [
            'modelo' => EstadoExpediente::class,
            'titulo' => 'Estado de expediente',
            'etiqueta' => 'Estado Expedientes',
            'genero' => 'o',
            'conDescripcion' => false,
        ],
        'estado-sesiones' => [
            'modelo' => EstadoSesion::class,
            'titulo' => 'Estado de sesión',
            'etiqueta' => 'Estado Sesiones',
            'genero' => 'o',
            'conDescripcion' => false,
        ],
        'modalidades' => [
            'modelo' => Modalidad::class,
            'titulo' => 'Modalidad',
            'etiqueta' => 'Modalidades',
            'genero' => 'a',
            'conDescripcion' => false,
        ],
    ];

    public static function buscar(string $clave): ?array
    {
        $catalogo = self::CATALOGOS[$clave] ?? null;

        return $catalogo ? [...$catalogo, 'clave' => $clave] : null;
    }

    /** Metadatos + filas de cada catálogo, para la pantalla de parámetros. */
    public static function paraLaVista(): array
    {
        return array_map(
            fn(string $clave) => [
                ...self::buscar($clave),
                'modelo' => null, // La clase no le sirve al frontend.
                'items' => self::buscar($clave)['modelo']::orderBy('nombre')->get(),
            ],
            array_keys(self::CATALOGOS)
        );
    }

    /** La tabla sale del modelo, para no repetirla en el registro. */
    public static function tabla(array $catalogo): string
    {
        return (new $catalogo['modelo']())->getTable();
    }
}
