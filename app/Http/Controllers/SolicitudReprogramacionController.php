<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\SolicitudReprogramacion;
use App\Notificaciones\Avisos;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SolicitudReprogramacionController extends Controller
{
    /**
     * El encargado pide mover una cita de uno de sus hijos.
     *
     * No propone fecha: la asigna quien la autoriza.
     */
    public function store(Request $request, Cita $cita)
    {
        $encargado = $request->user()->encargado;

        abort_unless($encargado, 403, 'Solo un encargado solicita reprogramaciones.');

        abort_unless(
            $cita->paciente?->encargado_id === $encargado->id,
            403,
            'Solo puede pedir la reprogramación de las citas de sus hijos.'
        );

        $datos = $request->validate(['motivo' => 'nullable|string|max:500']);

        $this->verificarAnticipacion($cita);
        $this->verificarQueNoHayaOtraPendiente($cita);

        SolicitudReprogramacion::create([
            'cita_id' => $cita->id,
            'solicitada_por' => $request->user()->id,
            'motivo' => $datos['motivo'] ?? null,
            'estado' => SolicitudReprogramacion::PENDIENTE,
        ]);

        // La cita queda marcada mientras se resuelve, así nadie la da por firme.
        $this->cambiarEstado($cita, 'Pendiente de reprogramación');

        $paciente = $cita->paciente?->nombre_completo ?? 'un paciente';
        $cuando = $cita->fecha->format('d/m/Y') . ' a las ' . substr($cita->hora_inicio, 0, 5);

        Avisos::aTodos(
            Avisos::quienesAutorizan(),
            'Solicitud de reprogramación',
            "{$encargado->nombre_completo} pidió mover la cita de {$paciente} del {$cuando}.",
            'event_repeat'
        );

        // El encargado también se lleva su acuse.
        Avisos::a(
            $request->user(),
            'Solicitud enviada',
            "Se solicitó la reprogramación de la cita de {$paciente} del {$cuando}. Le avisaremos cuando se resuelva.",
            'schedule_send'
        );

        return back()->with('success', 'Solicitud de reprogramación enviada.');
    }

    /** Coordinación o administración acepta —con fecha nueva— o rechaza. */
    public function update(Request $request, SolicitudReprogramacion $solicitud)
    {
        abort_unless(
            $solicitud->estado === SolicitudReprogramacion::PENDIENTE,
            422,
            'Esta solicitud ya fue resuelta.'
        );

        $datos = $request->validate([
            'decision' => 'required|in:aceptada,rechazada',
            'respuesta' => 'nullable|string|max:500',
            'fecha' => 'required_if:decision,aceptada|nullable|date',
            'hora_inicio' => 'required_if:decision,aceptada|nullable|date_format:H:i',
            'hora_fin' => 'required_if:decision,aceptada|nullable|date_format:H:i|after:hora_inicio',
        ]);

        $solicitud->load('cita.paciente', 'solicitante');

        return $datos['decision'] === 'aceptada'
            ? $this->aceptar($request, $solicitud, $datos)
            : $this->rechazar($request, $solicitud, $datos);
    }

    private function aceptar(Request $request, SolicitudReprogramacion $solicitud, array $datos)
    {
        $cita = $solicitud->cita;

        // Se edita la cita existente en lugar de crear otra: no hay dos citas,
        // hay una que cambió de horario. La fecha de la que se movió queda en
        // la solicitud.
        $solicitud->update([
            'estado' => SolicitudReprogramacion::ACEPTADA,
            'resuelta_por' => $request->user()->id,
            'resuelta_en' => now(),
            'respuesta' => $datos['respuesta'] ?? null,
            'fecha_original' => $cita->fecha->toDateString(),
            'hora_original' => $cita->hora_inicio,
        ]);

        $cita->update([
            'fecha' => $datos['fecha'],
            'hora_inicio' => Cita::normalizarHora($datos['hora_inicio']),
            'hora_fin' => Cita::normalizarHora($datos['hora_fin']),
            'estado_cita_id' => EstadoCita::where('nombre', 'Programada')->value('id'),
        ]);

        $cuando = $cita->fresh()->fecha->format('d/m/Y') . ' a las ' . substr($datos['hora_inicio'], 0, 5);

        Avisos::a(
            $solicitud->solicitante,
            'Reprogramación aprobada',
            "La cita de {$cita->paciente?->nombre_completo} quedó para el {$cuando}.",
            'event_available'
        );

        return back()->with('success', "Reprogramada para el {$cuando}.");
    }

    private function rechazar(Request $request, SolicitudReprogramacion $solicitud, array $datos)
    {
        // La cita vuelve a estar en pie, en su horario original.
        $this->cambiarEstado($solicitud->cita, 'Programada');

        $solicitud->update([
            'estado' => SolicitudReprogramacion::RECHAZADA,
            'resuelta_por' => $request->user()->id,
            'resuelta_en' => now(),
            'respuesta' => $datos['respuesta'] ?? null,
        ]);

        $motivo = $datos['respuesta'] ?? 'No se indicó un motivo.';

        Avisos::a(
            $solicitud->solicitante,
            'Reprogramación rechazada',
            "La cita de {$solicitud->cita->paciente?->nombre_completo} se mantiene en su horario. {$motivo}",
            'event_busy'
        );

        return back()->with('success', 'Solicitud rechazada.');
    }

    /** 30 horas de anticipación, contadas contra la hora de inicio. */
    private function verificarAnticipacion(Cita $cita): void
    {
        $inicio = Carbon::parse($cita->fecha->toDateString() . ' ' . Cita::normalizarHora($cita->hora_inicio));

        if (now()->diffInHours($inicio, false) < SolicitudReprogramacion::HORAS_MINIMAS) {
            throw ValidationException::withMessages([
                'motivo' => 'La reprogramación se pide con al menos ' . SolicitudReprogramacion::HORAS_MINIMAS . ' horas de anticipación.',
            ]);
        }
    }

    private function verificarQueNoHayaOtraPendiente(Cita $cita): void
    {
        if ($cita->solicitudesReprogramacion()->pendientes()->exists()) {
            throw ValidationException::withMessages([
                'motivo' => 'Ya hay una solicitud pendiente para esta cita.',
            ]);
        }
    }

    private function cambiarEstado(Cita $cita, string $nombre): void
    {
        if ($id = EstadoCita::where('nombre', $nombre)->value('id')) {
            $cita->update(['estado_cita_id' => $id]);
        }
    }
}
