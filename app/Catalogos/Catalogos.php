<?php

namespace App\Catalogos;

use App\Models\Cargo;
use App\Models\Diagnostico;
use App\Models\Escolaridad;
use App\Models\Especialidad;
use App\Models\EstadoCita;
use App\Models\EstadoExpediente;
use App\Models\EstadoSesion;
use App\Models\Evaluacion;
use App\Models\Modalidad;
use App\Models\Programa;
use App\Models\Servicio;
use App\Models\TipoCita;

/**
 * Los catálogos de /parametros. Todos tienen `nombre` y `activo`, así que
 * comparten el mismo CRUD, la misma tabla y el mismo formulario.
 *
 * Agregar uno es sumar una entrada acá: no hay controlador, ruta ni página que
 * escribir. Y funciona como lista blanca: el CRUD solo atiende estas claves.
 *
 * `campos` es para los que llevan algo más que nombre y descripción, como
 * Programas con su cantidad de citas y su costo. Tipos: entero, moneda, texto.
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
        'diagnosticos' => [
            'modelo' => Diagnostico::class,
            'titulo' => 'Diagnóstico',
            'etiqueta' => 'Diagnósticos',
            'genero' => 'o',
            'conDescripcion' => true,
        ],
        'evaluaciones' => [
            'modelo' => Evaluacion::class,
            'titulo' => 'Evaluación',
            'etiqueta' => 'Evaluaciones',
            'genero' => 'a',
            'conDescripcion' => true,
        ],
        'programas' => [
            'modelo' => Programa::class,
            'titulo' => 'Programa',
            'etiqueta' => 'Programas',
            'genero' => 'o',
            'conDescripcion' => true,
            'campos' => [
                ['clave' => 'sesiones_por_mes', 'etiqueta' => 'Cantidad de citas', 'tipo' => 'entero'],
                ['clave' => 'precio_mensual', 'etiqueta' => 'Costo', 'tipo' => 'moneda'],
            ],
        ],
        'modalidades' => [
            'modelo' => Modalidad::class,
            'titulo' => 'Modalidad',
            'etiqueta' => 'Modalidades',
            'genero' => 'a',
            'conDescripcion' => false,
        ],
        'tipo-citas' => [
            'modelo' => TipoCita::class,
            'titulo' => 'Tipo de cita',
            'etiqueta' => 'Tipo de Cita',
            'genero' => 'o',
            'conDescripcion' => false,
        ],
        'estado-citas' => [
            'modelo' => EstadoCita::class,
            'titulo' => 'Estado de cita',
            'etiqueta' => 'Estado Citas',
            'genero' => 'o',
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
        'cargos' => [
            'modelo' => Cargo::class,
            'titulo' => 'Cargo',
            'etiqueta' => 'Cargos',
            'genero' => 'o',
            'conDescripcion' => false,
        ],
    ];

    public static function buscar(string $clave): ?array
    {
        $catalogo = self::CATALOGOS[$clave] ?? null;

        // `campos` se normaliza acá para que nadie tenga que declararlo vacío.
        return $catalogo ? [...$catalogo, 'campos' => $catalogo['campos'] ?? [], 'clave' => $clave] : null;
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
