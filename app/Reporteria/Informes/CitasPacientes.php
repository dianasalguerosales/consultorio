<?php

namespace App\Reporteria\Informes;

use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Servicio;
use App\Reporteria\Informe;

class CitasPacientes extends Informe
{
    public function clave(): string
    {
        return 'citas-pacientes';
    }

    public function nombre(): string
    {
        return 'Citas y pacientes';
    }

    public function descripcion(): string
    {
        return 'Detalle de cada cita agendada.';
    }

    public function tablas(): string
    {
        return 'citas + pacientes + terapeutas/administrativos + servicios + estado_citas';
    }

    public function consulta()
    {
        return Cita::query()
            ->with(['paciente', 'atendidoPor', 'servicio', 'estadoCita', 'modalidad', 'tipoCita', 'programa'])
            ->orderBy('fecha')
            ->orderBy('hora_inicio');
    }

    public function columnas(): array
    {
        return [
            'fecha' => ['etiqueta' => 'Fecha', 'valor' => fn(Cita $c) => $this->fecha($c->fecha)],
            'hora' => ['etiqueta' => 'Hora', 'valor' => fn(Cita $c) => substr($c->hora_inicio, 0, 5) . ' - ' . substr((string) $c->hora_fin, 0, 5)],
            'paciente' => ['etiqueta' => 'Paciente', 'valor' => fn(Cita $c) => $this->nombrePaciente($c->paciente)],
            'atiende' => ['etiqueta' => 'Atiende', 'valor' => fn(Cita $c) => $this->atiende($c)],
            'servicio' => ['etiqueta' => 'Servicio', 'valor' => fn(Cita $c) => $c->servicio?->nombre],
            'programa' => ['etiqueta' => 'Programa', 'valor' => fn(Cita $c) => $c->programa?->nombre],
            'estado' => ['etiqueta' => 'Estado', 'valor' => fn(Cita $c) => $c->estadoCita?->nombre],
            'modalidad' => ['etiqueta' => 'Modalidad', 'valor' => fn(Cita $c) => $c->modalidad?->nombre],
            'tipo' => ['etiqueta' => 'Tipo de cita', 'valor' => fn(Cita $c) => $c->tipoCita?->nombre],
            'precio' => ['etiqueta' => 'Precio aplicado', 'valor' => fn(Cita $c) => $c->precio_aplicado],
        ];
    }

    public function filtros(): array
    {
        return array_merge(
            $this->rangoFechas('fecha', 'Fecha'),
            $this->filtroPaciente(fn($q, $v) => $q->where('paciente_id', $v)),
            [
                'estado_cita_id' => [
                    'etiqueta' => 'Estado',
                    'tipo' => 'select',
                    'opciones' => $this->opciones(EstadoCita::class),
                    'aplicar' => fn($q, $v) => $q->where('estado_cita_id', $v),
                ],
                'servicio_id' => [
                    'etiqueta' => 'Servicio',
                    'tipo' => 'select',
                    'opciones' => $this->opciones(Servicio::class),
                    'aplicar' => fn($q, $v) => $q->where('servicio_id', $v),
                ],
            ]
        );
    }
}
