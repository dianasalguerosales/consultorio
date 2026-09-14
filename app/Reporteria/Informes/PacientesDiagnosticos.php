<?php

namespace App\Reporteria\Informes;

use App\Models\Diagnostico;
use App\Models\Expediente;
use App\Reporteria\Informe;

class PacientesDiagnosticos extends Informe
{
    public function clave(): string
    {
        return 'pacientes-diagnosticos';
    }

    public function nombre(): string
    {
        return 'Pacientes y diagnósticos';
    }

    public function descripcion(): string
    {
        return 'Cada diagnóstico registrado en un expediente, con los datos del paciente.';
    }

    public function tablas(): string
    {
        return 'expedientes + expediente_diagnostico + diagnosticos + pacientes';
    }

    public function consulta()
    {
        return Expediente::query()
            ->with(['paciente.genero', 'diagnosticos', 'estado'])
            ->has('diagnosticos');
    }

    // Una fila por par expediente-diagnóstico, no por expediente.
    public function expandir(): ?callable
    {
        return fn(Expediente $e) => $e->diagnosticos
            ->map(fn($d) => ['expediente' => $e, 'diagnostico' => $d]);
    }

    public function columnas(): array
    {
        return [
            'codigo' => ['etiqueta' => 'Expediente', 'valor' => fn($f) => $f['expediente']->codigo],
            'paciente' => ['etiqueta' => 'Paciente', 'valor' => fn($f) => $this->nombrePaciente($f['expediente']->paciente)],
            'genero' => ['etiqueta' => 'Género', 'valor' => fn($f) => $f['expediente']->paciente?->genero?->nombre],
            'nacimiento' => ['etiqueta' => 'Fecha de nacimiento', 'valor' => fn($f) => $this->fecha($f['expediente']->fecha_nacimiento)],
            'diagnostico' => ['etiqueta' => 'Diagnóstico', 'valor' => fn($f) => $f['diagnostico']->nombre],
            'fecha_diagnostico' => ['etiqueta' => 'Fecha de registro', 'valor' => fn($f) => $this->fecha($f['diagnostico']->pivot->created_at)],
            'estado' => ['etiqueta' => 'Estado del expediente', 'valor' => fn($f) => $f['expediente']->estado?->nombre],
        ];
    }

    public function filtros(): array
    {
        return array_merge(
            $this->rangoFechas('fecha_inicio', 'Apertura'),
            $this->filtroPaciente(fn($q, $v) => $q->where('paciente_id', $v)),
            [
                'diagnostico_id' => [
                    'etiqueta' => 'Diagnóstico',
                    'tipo' => 'select',
                    'opciones' => $this->opciones(Diagnostico::class),
                    'aplicar' => fn($q, $v) => $q->whereHas('diagnosticos', fn($d) => $d->where('diagnosticos.id', $v)),
                ],
            ]
        );
    }
}
