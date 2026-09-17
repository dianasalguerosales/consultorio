<?php

namespace App\Expedientes;

use App\Models\Cita;
use Illuminate\Support\Collection;

/**
 * Las terapias que un niño ya llevó, sacadas de sus citas.
 *
 * No es lo mismo que `expedientes.servicios`, que es lo que se le planificó al
 * ingresar: esto es el récord de lo que efectivamente pasó, y por eso se lee de
 * las citas y no del expediente.
 *
 * Solo cuentan las citas en estado "Atendida", que es el que se marca cuando ya
 * se escribió la sesión. Con todas las citas, una cancelada o una que nadie
 * atendió entraría al récord igual y dejaría de ser confiable.
 */
class TerapiasUsadas
{
    /**
     * Una consulta agregada para todos los pacientes de la pantalla, en vez de
     * una por expediente.
     *
     * Devuelve las filas agrupadas por `paciente_id`, cada una con el nombre de
     * la terapia, cuántas citas lleva y la fecha de la última.
     */
    public static function dePacientes(Collection $pacienteIds): Collection
    {
        if ($pacienteIds->isEmpty()) {
            return collect();
        }

        return Cita::query()
            ->whereIn('citas.paciente_id', $pacienteIds)
            ->whereNotNull('citas.servicio_id')
            ->whereHas('estadoCita', fn ($q) => $q->where('nombre', 'Atendida'))
            ->join('servicios', 'servicios.id', '=', 'citas.servicio_id')
            ->selectRaw('citas.paciente_id, servicios.nombre, COUNT(*) as citas, MAX(citas.fecha) as ultima')
            ->groupBy('citas.paciente_id', 'servicios.nombre')
            ->orderBy('servicios.nombre')
            ->get()
            ->groupBy('paciente_id');
    }

    /**
     * Le cuelga a cada expediente su récord, en `terapias_usadas`.
     *
     * Los expedientes llegan como modelos y salen a la vista tal cual, así que
     * el récord se agrega como atributo y no hace falta armar el arreglo a mano
     * en cada controlador.
     */
    public static function colgarEn(Collection $expedientes): void
    {
        $porPaciente = self::dePacientes(
            $expedientes->pluck('paciente_id')->filter()->unique()->values()
        );

        foreach ($expedientes as $expediente) {
            $filas = $porPaciente->get($expediente->paciente_id, collect());

            $expediente->setAttribute('terapias_usadas', $filas->map(fn ($fila) => [
                'nombre' => $fila->nombre,
                'citas' => (int) $fila->citas,
                'ultima' => $fila->ultima,
            ])->values());
        }
    }
}
