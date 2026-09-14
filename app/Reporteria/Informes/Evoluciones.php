<?php

namespace App\Reporteria\Informes;

use App\Models\Sesion;
use App\Models\Terapeuta;
use App\Reporteria\Informe;

class Evoluciones extends Informe
{
    public function clave(): string
    {
        return 'evoluciones';
    }

    public function nombre(): string
    {
        return 'Evoluciones';
    }

    public function descripcion(): string
    {
        return 'Las sesiones atendidas con la evolución que escribió el terapeuta.';
    }

    public function tablas(): string
    {
        return 'sesiones + citas + pacientes + terapeutas + servicios + estado_sesiones';
    }

    public function consulta()
    {
        return Sesion::query()
            ->with(['cita.paciente', 'cita.servicio', 'terapeuta', 'estadoSesion'])
            ->orderByDesc('created_at');
    }

    public function columnas(): array
    {
        return [
            'fecha' => ['etiqueta' => 'Fecha de la cita', 'valor' => fn(Sesion $s) => $this->fecha($s->cita?->fecha)],
            'paciente' => ['etiqueta' => 'Paciente', 'valor' => fn(Sesion $s) => $this->nombrePaciente($s->cita?->paciente)],
            'terapeuta' => ['etiqueta' => 'Terapeuta', 'valor' => fn(Sesion $s) => $s->terapeuta?->nombre_completo],
            'servicio' => ['etiqueta' => 'Servicio', 'valor' => fn(Sesion $s) => $s->cita?->servicio?->nombre],
            'estado' => ['etiqueta' => 'Estado de la sesión', 'valor' => fn(Sesion $s) => $s->estadoSesion?->nombre],
            'duracion' => ['etiqueta' => 'Duración (min)', 'valor' => fn(Sesion $s) => $s->duracion_minutos],
            'obs_clinicas' => ['etiqueta' => 'Evolución', 'valor' => fn(Sesion $s) => $s->observaciones_clinicas],
            'obs_generales' => ['etiqueta' => 'Observaciones públicas', 'valor' => fn(Sesion $s) => $s->observaciones_generales],
            'con_observacion' => ['etiqueta' => '¿Tiene observaciones?', 'valor' => fn(Sesion $s) => ($s->observaciones_clinicas || $s->observaciones_generales) ? 'Sí' : 'No'],
        ];
    }

    public function filtros(): array
    {
        return array_merge(
            $this->rangoFechasDe('cita', 'fecha', 'Fecha de la cita'),
            $this->filtroPaciente(
                fn($q, $v) => $q->whereHas('cita', fn($c) => $c->where('paciente_id', $v))
            ),
            [
                'terapeuta_id' => [
                'etiqueta' => 'Terapeuta',
                'tipo' => 'select',
                    'opciones' => $this->opcionesPersonas(Terapeuta::class),
                    'aplicar' => fn($q, $v) => $q->where('terapeuta_id', $v),
                ],
                'sin_observaciones' => [
                    'etiqueta' => 'Solo sesiones sin observaciones',
                    'tipo' => 'checkbox',
                    'aplicar' => fn($q, $v) => $q->whereNull('observaciones_clinicas')->whereNull('observaciones_generales'),
                ],
            ]
        );
    }
}
