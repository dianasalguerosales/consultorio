<?php

namespace App\Reporteria\Informes;

use App\Models\Expediente;
use App\Models\Servicio;
use App\Reporteria\Informe;

class PacientesServicios extends Informe
{
    public function clave(): string
    {
        return 'pacientes-servicios';
    }

    public function nombre(): string
    {
        return 'Pacientes y servicios';
    }

    public function descripcion(): string
    {
        return 'Los servicios contratados en cada expediente.';
    }

    public function tablas(): string
    {
        return 'expedientes + expediente_servicios + servicios + pacientes';
    }

    public function consulta()
    {
        return Expediente::query()
            ->with(['paciente', 'servicios', 'modalidad', 'diagnosticos'])
            ->has('servicios');
    }

    public function expandir(): ?callable
    {
        return fn(Expediente $e) => $e->servicios
            ->map(fn($s) => ['expediente' => $e, 'servicio' => $s]);
    }

    public function columnas(): array
    {
        return [
            'codigo' => ['etiqueta' => 'Expediente', 'valor' => fn($f) => $f['expediente']->codigo],
            'paciente' => ['etiqueta' => 'Paciente', 'valor' => fn($f) => $this->nombrePaciente($f['expediente']->paciente)],
            'servicio' => ['etiqueta' => 'Servicio', 'valor' => fn($f) => $f['servicio']->nombre],
            'modalidad' => ['etiqueta' => 'Modalidad', 'valor' => fn($f) => $f['expediente']->modalidad?->nombre],
            'diagnosticos' => ['etiqueta' => 'Diagnósticos', 'valor' => fn($f) => $f['expediente']->diagnosticos->pluck('nombre')->implode(' / ')],
        ];
    }

    public function filtros(): array
    {
        return array_merge(
            $this->rangoFechas('fecha_inicio', 'Apertura'),
            $this->filtroPaciente(fn($q, $v) => $q->where('paciente_id', $v)),
            [
                'servicio_id' => [
                    'etiqueta' => 'Servicio',
                    'tipo' => 'select',
                    'opciones' => $this->opciones(Servicio::class),
                    'aplicar' => fn($q, $v) => $q->whereHas('servicios', fn($s) => $s->where('servicios.id', $v)),
                ],
            ]
        );
    }
}
