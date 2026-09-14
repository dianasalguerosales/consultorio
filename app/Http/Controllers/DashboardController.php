<?php

namespace App\Http\Controllers;

use App\Agenda\QuienAtiende;
use App\Cumpleanos\Cumpleanos;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tareas = $this->tareasDelDia($user);

        return Inertia::render('Dashboard', [
            // Ayer, hoy y mañana: ayer es para el que se saltó un saludo, y
            // mañana para poder preparar con un día de anticipación.
            'cumpleanos' => Cumpleanos::deLosTresDias(),

            // Lo que le toca atender hoy a quien está viendo la pantalla.
            'tareasDelDia' => $tareas,

            // El panel es de quien atiende citas. A un coordinador, que nunca
            // tiene, no se le deja una lista vacía todos los días — salvo que
            // alguna cita vieja siga a su nombre.
            'yoAtiendo' => QuienAtiende::atiendeCitas($user) || $tareas
                ? QuienAtiende::de($user)
                : null,
        ]);
    }

    /**
     * Las citas de hoy de quien está logueado.
     *
     * Son las que tiene asignadas él, no las del consultorio: la agenda
     * completa ya está en /agenda. Quien no atiende citas —un coordinador sin
     * ficha de terapeuta o auxiliar— no tiene tareas que listar.
     */
    private function tareasDelDia($user): array
    {
        $quien = QuienAtiende::de($user);

        if (! $quien) {
            return [];
        }

        $clase = QuienAtiende::clase($quien['tipo']);
        $persona = $clase::find($quien['id']);

        if (! $persona) {
            return [];
        }

        return Cita::query()
            ->atendidasPor($persona)
            ->whereDate('fecha', Carbon::today())
            ->with([
                'paciente:id,nombres,apellidos,genero',
                // Para avisar si el niño cumple años hoy.
                'paciente.expediente:id,paciente_id,fecha_nacimiento',
                'servicio:id,nombre',
                'estadoCita:id,nombre',
                'sesion',
            ])
            ->orderBy('hora_inicio')
            ->get()
            ->map(fn(Cita $cita) => [
                'id' => $cita->id,
                'hora' => substr($cita->hora_inicio, 0, 5),
                'hora_fin' => $cita->hora_fin ? substr($cita->hora_fin, 0, 5) : null,
                'paciente' => $cita->paciente?->nombre_completo,
                'genero' => $cita->paciente?->genero,
                'servicio' => $cita->servicio?->nombre ?? 'Sin terapia',
                'estado' => $cita->estadoCita?->nombre,
                // Atendida = ya tiene la sesión escrita; es lo que marca que la
                // tarea está hecha.
                'atendida' => (bool) $cita->sesion,
                'cumpleanos' => Cumpleanos::esCumpleanos(
                    $cita->paciente?->expediente?->fecha_nacimiento,
                    $cita->fecha
                ),
            ])
            ->all();
    }
}
