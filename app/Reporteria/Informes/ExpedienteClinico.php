<?php

namespace App\Reporteria\Informes;

use App\Models\EstadoExpediente;
use App\Models\Expediente;
use App\Reporteria\Informe;

class ExpedienteClinico extends Informe
{
    public function clave(): string
    {
        return 'expediente-clinico';
    }

    public function nombre(): string
    {
        return 'Expediente clínico';
    }

    public function descripcion(): string
    {
        return 'Un renglón por expediente, con su estado y sus diagnósticos.';
    }

    public function tablas(): string
    {
        return 'expedientes + pacientes + estado_expedientes + modalidades + diagnosticos';
    }

    public function consulta()
    {
        return Expediente::query()
            ->with(['paciente.genero', 'paciente.escolaridad', 'estado', 'modalidad', 'diagnosticos', 'anamnesis'])
            ->orderBy('codigo');
    }

    public function columnas(): array
    {
        return [
            'codigo' => ['etiqueta' => 'Expediente', 'valor' => fn(Expediente $e) => $e->codigo],
            'paciente' => ['etiqueta' => 'Paciente', 'valor' => fn(Expediente $e) => $this->nombrePaciente($e->paciente)],
            'genero' => ['etiqueta' => 'Género', 'valor' => fn(Expediente $e) => $e->paciente?->genero?->nombre],
            'escolaridad' => ['etiqueta' => 'Escolaridad', 'valor' => fn(Expediente $e) => $e->paciente?->escolaridad?->nombre],
            'fecha_inicio' => ['etiqueta' => 'Fecha de ingreso', 'valor' => fn(Expediente $e) => $this->fecha($e->fecha_inicio)],
            'estado' => ['etiqueta' => 'Estado', 'valor' => fn(Expediente $e) => $e->estado?->nombre],
            'modalidad' => ['etiqueta' => 'Modalidad', 'valor' => fn(Expediente $e) => $e->modalidad?->nombre],
            'diagnosticos' => ['etiqueta' => 'Diagnósticos', 'valor' => fn(Expediente $e) => $e->diagnosticos->pluck('nombre')->implode(' / ')],
            'motivo' => ['etiqueta' => 'Motivo de consulta', 'valor' => fn(Expediente $e) => $e->motivo_consulta],
            'anamnesis' => ['etiqueta' => 'Tiene anamnesis', 'valor' => fn(Expediente $e) => $e->anamnesis ? 'Sí' : 'No'],
            'consentimiento' => ['etiqueta' => 'Consentimiento', 'valor' => fn(Expediente $e) => $e->consentimiento ? 'Firmado' : 'Pendiente'],
        ];
    }

    public function filtros(): array
    {
        return array_merge(
            $this->rangoFechas('fecha_inicio', 'Ingreso'),
            [
                'estado_expediente_id' => [
                    'etiqueta' => 'Estado',
                    'tipo' => 'select',
                    'opciones' => $this->opciones(EstadoExpediente::class),
                    'aplicar' => fn($q, $v) => $q->where('estado_expediente_id', $v),
                ],
            ]
        );
    }
}
