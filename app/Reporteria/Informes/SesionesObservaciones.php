<?php

namespace App\Reporteria\Informes;

use App\Models\Sesion;
use App\Models\Terapeuta;
use App\Reporteria\Informe;

class SesionesObservaciones extends Informe
{
    public function clave(): string
    {
        return 'sesiones-observaciones';
    }

    public function nombre(): string
    {
        return 'Sesiones y observaciones';
    }

    public function descripcion(): string
    {
        return 'Las sesiones atendidas con lo que escribió el terapeuta.';
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
            'evolucion' => ['etiqueta' => 'Evolución', 'valor' => fn(Sesion $s) => $s->evolucion],
            'obs_clinicas' => ['etiqueta' => 'Observaciones clínicas', 'valor' => fn(Sesion $s) => $s->observaciones_clinicas],
            'obs_generales' => ['etiqueta' => 'Observaciones generales', 'valor' => fn(Sesion $s) => $s->observaciones_generales],
            'con_observacion' => ['etiqueta' => '¿Tiene observaciones?', 'valor' => fn(Sesion $s) => ($s->observaciones_clinicas || $s->observaciones_generales) ? 'Sí' : 'No'],
        ];
    }

    public function filtros(): array
    {
        return [
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
        ];
    }
}
