<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\EstadoSesion;
use App\Models\Sesion;
use App\Models\Terapeuta;
use Illuminate\Http\Request;

class SesionController extends Controller
{
    // Registra o corrige las observaciones de una cita atendida. Cita::sesion()
    // es hasOne, así que atender dos veces corrige en vez de duplicar.
    public function store(Request $request, Cita $cita)
    {
        $terapeuta = $this->terapeutaQueAtiende($request, $cita);

        // `observaciones_clinicas` es lo que el formulario rotula "Evolución".
        $datos = $request->validate([
            'observaciones_clinicas' => 'nullable|string',
            'observaciones_generales' => 'nullable|string',
            'duracion_minutos' => 'nullable|integer|min:1|max:600',
        ]);

        // El estado no se le pide al terapeuta: guardar es darla por Terminada.
        $terminada = EstadoSesion::where('nombre', 'Terminada')->value('id');

        Sesion::updateOrCreate(
            ['cita_id' => $cita->id],
            $datos + [
                'terapeuta_id' => $terapeuta->id,
                'estado_sesion_id' => $terminada,
            ]
        );

        // La cita pasa a Atendida para que el calendario lo refleje sin
        // consultar la sesión.
        if ($atendida = EstadoCita::where('nombre', 'Atendida')->value('id')) {
            $cita->update(['estado_cita_id' => $atendida]);
        }

        return redirect()
            ->route('agenda.index')
            ->with('success', 'Cita marcada como atendida.');
    }

    // Solo el terapeuta que atiende la cita. sesiones.terapeuta_id es FK a
    // terapeutas, así que un auxiliar no puede guardar aunque sí la atienda.
    private function terapeutaQueAtiende(Request $request, Cita $cita): Terapeuta
    {
        $terapeuta = $request->user()->terapeuta;

        abort_unless(
            $terapeuta,
            403,
            'Solo un terapeuta registra las observaciones de una cita.'
        );

        abort_unless(
            $cita->atendido_por_type === $terapeuta->getMorphClass()
                && (int) $cita->atendido_por_id === $terapeuta->id,
            403,
            'Solo puedes registrar observaciones de las citas que atiendes.'
        );

        return $terapeuta;
    }
}
