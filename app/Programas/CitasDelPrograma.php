<?php

namespace App\Programas;

use App\Models\AsignacionPrograma;
use App\Models\Cita;
use App\Models\EstadoCita;
use Illuminate\Support\Carbon;

/**
 * Las citas que le tocan a un niño por su programa.
 *
 * Recorre los días de la semana elegidos desde la fecha de inicio hasta juntar
 * la cantidad del paquete, y les reparte el precio para que sumen el costo
 * exacto.
 */
class CitasDelPrograma
{
    /** Tope de seguridad: dos años de calendario sin dar con las fechas. */
    private const MAX_DIAS = 730;

    /**
     * Fechas que le tocan, en orden.
     *
     * `$dias` va en formato ISO: 1 lunes ... 7 domingo.
     */
    public static function fechas(array $dias, string $desde, int $cantidad): array
    {
        if (empty($dias) || $cantidad < 1) {
            return [];
        }

        $fechas = [];
        $dia = Carbon::parse($desde)->startOfDay();
        $recorridos = 0;

        while (count($fechas) < $cantidad && $recorridos < self::MAX_DIAS) {
            if (in_array($dia->dayOfWeekIso, $dias, false)) {
                $fechas[] = $dia->toDateString();
            }

            $dia->addDay();
            $recorridos++;
        }

        return $fechas;
    }

    /**
     * Fechas que chocan con otra cita de la misma persona.
     *
     * Se revisan todas antes de crear ninguna: si se crearan solo las libres,
     * un paquete de 12 quedaría con 4 citas y su precio repartido entre esas
     * 4, que no es lo que se le vendió a nadie.
     */
    public static function choques(AsignacionPrograma $asignacion): array
    {
        return array_values(array_filter(
            self::fechasDe($asignacion),
            fn (string $fecha) => self::choca($asignacion, $fecha)
        ));
    }

    /** Crea las citas del programa, con el precio ya repartido. */
    public static function crear(AsignacionPrograma $asignacion): array
    {
        $fechas = self::fechasDe($asignacion);
        $precios = $asignacion->precioPorCita();
        $programada = EstadoCita::where('nombre', 'Programada')->value('id');

        $citas = [];

        foreach ($fechas as $i => $fecha) {
            $citas[] = Cita::create([
                'paciente_id' => $asignacion->paciente_id,
                'atendido_por_type' => $asignacion->atendido_por_type,
                'atendido_por_id' => $asignacion->atendido_por_id,
                'estado_cita_id' => $programada,
                'modalidad_id' => $asignacion->modalidad_id,
                'tipo_cita_id' => $asignacion->tipo_cita_id,
                'servicio_id' => $asignacion->servicio_id,
                'programa_id' => $asignacion->programa_id,
                'asignacion_programa_id' => $asignacion->id,
                'fecha' => $fecha,
                'hora_inicio' => Cita::normalizarHora($asignacion->hora_inicio),
                'hora_fin' => Cita::normalizarHora($asignacion->hora_fin),
                'precio_aplicado' => $precios[$i],
            ]);
        }

        return $citas;
    }

    private static function fechasDe(AsignacionPrograma $asignacion): array
    {
        return self::fechas(
            $asignacion->dias,
            $asignacion->fecha_inicio->toDateString(),
            $asignacion->cantidad_citas
        );
    }

    /** Sin terapeuta asignado no hay con quién chocar. */
    private static function choca(AsignacionPrograma $asignacion, string $fecha): bool
    {
        if (! $asignacion->atendido_por_id) {
            return false;
        }

        return Cita::solapadas(
            $asignacion->atendido_por_type,
            $asignacion->atendido_por_id,
            $fecha,
            $asignacion->hora_inicio,
            $asignacion->hora_fin
        )->exists();
    }
}
