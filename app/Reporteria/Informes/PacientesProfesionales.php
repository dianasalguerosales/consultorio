<?php

namespace App\Reporteria\Informes;

use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Reporteria\Informe;

class PacientesProfesionales extends Informe
{
    public function clave(): string
    {
        return 'pacientes-profesionales';
    }

    public function nombre(): string
    {
        return 'Pacientes y profesionales';
    }

    public function descripcion(): string
    {
        return 'Qué terapeuta tiene asignado cada paciente.';
    }

    public function tablas(): string
    {
        return 'pacientes + paciente_terapeuta + terapeutas + especialidades';
    }

    public function consulta()
    {
        return Paciente::query()
            ->with(['genero', 'escolaridad', 'terapeutas.especialidad'])
            ->has('terapeutas');
    }

    public function expandir(): ?callable
    {
        return fn(Paciente $p) => $p->terapeutas
            ->map(fn($t) => ['paciente' => $p, 'terapeuta' => $t]);
    }

    public function columnas(): array
    {
        return [
            'paciente' => ['etiqueta' => 'Paciente', 'valor' => fn($f) => $this->nombrePaciente($f['paciente'])],
            'genero' => ['etiqueta' => 'Género', 'valor' => fn($f) => $f['paciente']->genero?->nombre],
            'escolaridad' => ['etiqueta' => 'Escolaridad', 'valor' => fn($f) => $f['paciente']->escolaridad?->nombre],
            'terapeuta' => ['etiqueta' => 'Terapeuta', 'valor' => fn($f) => $f['terapeuta']->nombre_completo],
            'especialidad' => ['etiqueta' => 'Especialidad', 'valor' => fn($f) => $f['terapeuta']->especialidad?->nombre],
            'correo' => ['etiqueta' => 'Correo del terapeuta', 'valor' => fn($f) => $f['terapeuta']->correo],
        ];
    }

    public function filtros(): array
    {
        return array_merge(
            // La consulta es de pacientes, asi que aqui la fecha es la de alta.
            $this->rangoFechas('created_at', 'Registro'),
            $this->filtroPaciente(fn($q, $v) => $q->where('pacientes.id', $v)),
            [
                'terapeuta_id' => [
                    'etiqueta' => 'Terapeuta',
                    'tipo' => 'select',
                    'opciones' => $this->opcionesPersonas(Terapeuta::class),
                    'aplicar' => fn($q, $v) => $q->whereHas('terapeutas', fn($t) => $t->where('terapeutas.id', $v)),
                ],
            ]
        );
    }
}
