<?php

namespace App\Programas;

use App\Models\AsignacionPrograma;
use Illuminate\Support\Carbon;

/**
 * El siguiente paquete mensual de un niño.
 *
 * Renovar no agrega citas al paquete que ya corre: crea uno nuevo con las
 * mismas condiciones y su propio bloque de citas. Así cada mes se cobra por
 * separado y `citas_creadas / cantidad_citas` sigue queriendo decir algo.
 *
 * No se dispara solo. El coordinador aprieta "Generar paquete" cuando toca, que
 * es lo que pidió Diana: si se renovara en automático, un niño que dejó de
 * venir seguiría acumulando citas y cobros.
 */
class RenovacionPrograma
{
    /**
     * Desde cuándo arranca el paquete siguiente: el día después de la última
     * cita del actual. Si el paquete no llegó a crear citas, desde mañana —
     * nunca antes de hoy, que dejaría citas nuevas en el pasado.
     */
    public static function desdeCuando(AsignacionPrograma $asignacion): string
    {
        $ultima = $asignacion->citas()->max('fecha');

        $siguiente = $ultima
            ? Carbon::parse($ultima)->addDay()
            : Carbon::tomorrow();

        return $siguiente->max(Carbon::tomorrow())->toDateString();
    }

    /**
     * Lo que se le propone al coordinador, ya calculado.
     *
     * Se copian las condiciones del paquete que corre y no las del catálogo: el
     * precio y la cantidad pudieron negociarse para ese niño, y renovar no es
     * momento de deshacer eso. Igual queda todo editable en el formulario.
     */
    public static function propuesta(AsignacionPrograma $asignacion): array
    {
        return [
            'fecha_inicio' => self::desdeCuando($asignacion),
            'cantidad_citas' => $asignacion->cantidad_citas,
            'precio' => (float) $asignacion->precio,
            'dias' => $asignacion->dias,
            'hora_inicio' => substr($asignacion->hora_inicio, 0, 5),
            'hora_fin' => substr($asignacion->hora_fin, 0, 5),
        ];
    }

    /** El paquete nuevo, copiando del anterior lo que no cambia. */
    public static function crear(AsignacionPrograma $anterior, array $datos, int $usuarioId): AsignacionPrograma
    {
        return AsignacionPrograma::create([
            'paciente_id' => $anterior->paciente_id,
            'programa_id' => $anterior->programa_id,
            'servicio_id' => $anterior->servicio_id,
            'atendido_por_type' => $anterior->atendido_por_type,
            'atendido_por_id' => $anterior->atendido_por_id,
            'modalidad_id' => $anterior->modalidad_id,
            'tipo_cita_id' => $anterior->tipo_cita_id,

            'precio' => $datos['precio'],
            'cantidad_citas' => $datos['cantidad_citas'],
            'dias' => $datos['dias'],
            'hora_inicio' => $datos['hora_inicio'],
            'hora_fin' => $datos['hora_fin'],
            'fecha_inicio' => $datos['fecha_inicio'],

            'estado' => AsignacionPrograma::ACTIVO,
            'creado_por' => $usuarioId,
        ]);
    }
}
