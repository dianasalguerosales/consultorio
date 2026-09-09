<?php

namespace App\Reporteria\Informes;

use App\Models\Administrativo;
use App\Models\Cita;
use App\Models\Terapeuta;
use App\Reporteria\Informe;

class ProfesionalesAgenda extends Informe
{
    public function clave(): string
    {
        return 'profesionales-agenda';
    }

    public function nombre(): string
    {
        return 'Profesionales y agenda';
    }

    public function descripcion(): string
    {
        return 'La carga de cada profesional, cita por cita. Incluye terapeutas y auxiliares.';
    }

    public function tablas(): string
    {
        return 'citas + terapeutas/administrativos + servicios + pacientes';
    }

    public function consulta()
    {
        return Cita::query()
            ->with(['atendidoPor', 'paciente', 'servicio', 'estadoCita', 'modalidad'])
            ->whereNotNull('atendido_por_id')
            ->orderBy('fecha')
            ->orderBy('hora_inicio');
    }

    public function columnas(): array
    {
        return [
            'profesional' => ['etiqueta' => 'Profesional', 'valor' => fn(Cita $c) => $this->atiende($c)],
            'rol' => ['etiqueta' => 'Rol', 'valor' => fn(Cita $c) => $c->atendido_por_type === Terapeuta::class ? 'Terapeuta' : 'Auxiliar'],
            'fecha' => ['etiqueta' => 'Fecha', 'valor' => fn(Cita $c) => $this->fecha($c->fecha)],
            'hora' => ['etiqueta' => 'Hora', 'valor' => fn(Cita $c) => substr($c->hora_inicio, 0, 5)],
            'paciente' => ['etiqueta' => 'Paciente atendido', 'valor' => fn(Cita $c) => $this->nombrePaciente($c->paciente)],
            'servicio' => ['etiqueta' => 'Servicio', 'valor' => fn(Cita $c) => $c->servicio?->nombre],
            'estado' => ['etiqueta' => 'Estado', 'valor' => fn(Cita $c) => $c->estadoCita?->nombre],
            'modalidad' => ['etiqueta' => 'Modalidad', 'valor' => fn(Cita $c) => $c->modalidad?->nombre],
        ];
    }

    public function filtros(): array
    {
        return array_merge(
            $this->rangoFechas('fecha', 'Fecha'),
            [
                'tipo_profesional' => [
                    'etiqueta' => 'Tipo de profesional',
                    'tipo' => 'select',
                    'opciones' => fn() => [
                        ['valor' => 'terapeuta', 'etiqueta' => 'Terapeutas'],
                        ['valor' => 'auxiliar', 'etiqueta' => 'Auxiliares'],
                    ],
                    'aplicar' => fn($q, $v) => $q->where(
                        'atendido_por_type',
                        $v === 'terapeuta' ? Terapeuta::class : Administrativo::class
                    ),
                ],
            ]
        );
    }
}
