<?php

namespace App\Reporteria;

use App\Models\Cita;
use App\Models\Paciente;
use Illuminate\Support\Carbon;

/**
 * Base de todos los informes. Cada uno vive en su propio archivo bajo
 * Informes/ y solo escribe lo suyo; acá quedan el contrato y las ayudas que
 * comparten.
 */
abstract class Informe
{
    /** Identificador que viaja en la URL. */
    abstract public function clave(): string;

    abstract public function nombre(): string;

    abstract public function descripcion(): string;

    /** Las tablas que cruza, para que el usuario sepa qué está mirando. */
    abstract public function tablas(): string;

    /** Consulta base con sus joins ya resueltos. */
    abstract public function consulta();

    /** ['clave' => ['etiqueta' => ..., 'valor' => fn($registro) => ...]] */
    abstract public function columnas(): array;

    /** ['clave' => ['etiqueta','tipo','opciones'?,'aplicar' => fn($q,$v)]] */
    public function filtros(): array
    {
        return [];
    }

    /**
     * Convierte un registro en varias filas cuando hace falta: un expediente
     * con tres diagnósticos son tres renglones. null = una fila por registro.
     */
    public function expandir(): ?callable
    {
        return null;
    }

    /* ---------- Ayudas compartidas ---------- */

    /** Quien atiende una cita puede ser Terapeuta o Administrativo auxiliar. */
    protected function atiende(?Cita $cita): ?string
    {
        return $cita?->atendidoPor?->nombre_completo;
    }

    protected function nombrePaciente(?Paciente $p): ?string
    {
        return $p?->nombre_completo;
    }

    /** dd/mm/yyyy, el formato que ve el usuario en toda la aplicación. */
    protected function fecha($valor): ?string
    {
        if (! $valor) {
            return null;
        }

        return $valor instanceof \DateTimeInterface
            ? $valor->format('d/m/Y')
            : Carbon::parse($valor)->format('d/m/Y');
    }

    /** Par de filtros desde/hasta sobre una columna de fecha. */
    protected function rangoFechas(string $columna, string $etiqueta): array
    {
        return [
            'desde' => [
                'etiqueta' => "$etiqueta desde",
                'tipo' => 'date',
                'aplicar' => fn($q, $v) => $q->whereDate($columna, '>=', $v),
            ],
            'hasta' => [
                'etiqueta' => "$etiqueta hasta",
                'tipo' => 'date',
                'aplicar' => fn($q, $v) => $q->whereDate($columna, '<=', $v),
            ],
        ];
    }

    /** Igual que rangoFechas, pero la fecha vive en una relación. */
    protected function rangoFechasDe(string $relacion, string $columna, string $etiqueta): array
    {
        $enRelacion = fn(string $operador) => fn($q, $v) => $q->whereHas(
            $relacion,
            fn($r) => $r->whereDate($columna, $operador, $v)
        );

        return [
            'desde' => ['etiqueta' => "$etiqueta desde", 'tipo' => 'date', 'aplicar' => $enRelacion('>=')],
            'hasta' => ['etiqueta' => "$etiqueta hasta", 'tipo' => 'date', 'aplicar' => $enRelacion('<=')],
        ];
    }

    /**
     * Filtro por paciente. Cada informe pasa cómo llegar al paciente desde su
     * propia consulta: unos lo tienen en una columna, otros a través de la cita
     * o del expediente.
     */
    protected function filtroPaciente(callable $aplicar): array
    {
        return [
            'paciente_id' => [
                'etiqueta' => 'Paciente',
                'tipo' => 'select',
                'opciones' => $this->opcionesPersonas(Paciente::class),
                'aplicar' => $aplicar,
            ],
        ];
    }

    /** Opciones de un catálogo con columna `nombre`. */
    protected function opciones(string $modelo): callable
    {
        return fn() => $modelo::orderBy('nombre')->pluck('nombre', 'id')
            ->map(fn($nombre, $id) => ['valor' => $id, 'etiqueta' => $nombre])
            ->values()->all();
    }

    /** Opciones de personas, que se ordenan y etiquetan por nombre completo. */
    protected function opcionesPersonas(string $modelo): callable
    {
        return fn() => $modelo::orderBy('apellidos')->get()
            ->map(fn($p) => ['valor' => $p->id, 'etiqueta' => $p->nombre_completo])
            ->all();
    }

    /** Metadatos para el frontend: sin closures, que no viajan en JSON. */
    public function metadatos(): array
    {
        return [
            'clave' => $this->clave(),
            'nombre' => $this->nombre(),
            'descripcion' => $this->descripcion(),
            'tablas' => $this->tablas(),
            'columnas' => collect($this->columnas())
                ->map(fn(array $c, string $k) => ['clave' => $k, 'etiqueta' => $c['etiqueta']])
                ->values(),
            'filtros' => collect($this->filtros())
                ->map(fn(array $f, string $k) => [
                    'clave' => $k,
                    'etiqueta' => $f['etiqueta'],
                    'tipo' => $f['tipo'],
                    'opciones' => isset($f['opciones']) ? ($f['opciones'])() : null,
                ])
                ->values(),
        ];
    }
}
