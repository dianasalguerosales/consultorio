<?php

namespace App\Reporteria\Informes;

use App\Models\Encargado;
use App\Models\Paciente;
use App\Models\RelacionPaciente;
use App\Reporteria\Informe;

class EncargadosPacientes extends Informe
{
    public function clave(): string
    {
        return 'encargados-pacientes';
    }

    public function nombre(): string
    {
        return 'Encargados y pacientes';
    }

    public function descripcion(): string
    {
        return 'Directorio de contacto: qué encargado responde por cada paciente.';
    }

    public function tablas(): string
    {
        return 'encargados + pacientes + relaciones_paciente + generos';
    }

    public function consulta()
    {
        return Encargado::query()
            ->with(['relacionPaciente', 'genero', 'user'])
            ->orderBy('apellidos');
    }

    // Un encargado sin hijos igual aparece, con la columna en blanco.
    public function expandir(): ?callable
    {
        return function (Encargado $e) {
            $hijos = Paciente::with('genero')->where('encargado_id', $e->id)->get();

            return $hijos->isEmpty()
                ? collect([['encargado' => $e, 'paciente' => null]])
                : $hijos->map(fn($p) => ['encargado' => $e, 'paciente' => $p]);
        };
    }

    public function columnas(): array
    {
        return [
            'encargado' => ['etiqueta' => 'Encargado', 'valor' => fn($f) => $f['encargado']->nombre_completo],
            'parentesco' => ['etiqueta' => 'Parentesco', 'valor' => fn($f) => $f['encargado']->relacionPaciente?->nombre],
            'telefono' => ['etiqueta' => 'Teléfono', 'valor' => fn($f) => $f['encargado']->telefono],
            'correo' => ['etiqueta' => 'Correo', 'valor' => fn($f) => $f['encargado']->correo],
            'direccion' => ['etiqueta' => 'Dirección', 'valor' => fn($f) => $f['encargado']->direccion],
            'ocupacion' => ['etiqueta' => 'Ocupación', 'valor' => fn($f) => $f['encargado']->ocupacion],
            'paciente' => ['etiqueta' => 'Paciente', 'valor' => fn($f) => $this->nombrePaciente($f['paciente'])],
            'usuario' => ['etiqueta' => 'Usuario del sistema', 'valor' => fn($f) => $f['encargado']->user?->email],
        ];
    }

    public function filtros(): array
    {
        return [
            'relacion_paciente_id' => [
                'etiqueta' => 'Parentesco',
                'tipo' => 'select',
                'opciones' => $this->opciones(RelacionPaciente::class),
                'aplicar' => fn($q, $v) => $q->where('relacion_paciente_id', $v),
            ],
        ];
    }
}
