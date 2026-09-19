<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Expedientes\TerapiasUsadas;
use App\Pacientes\AlcanceDePacientes;
use App\Models\Paciente;
use App\Models\Expediente;
use App\Models\Cita;
use App\Models\Encargado;
use App\Models\Genero;
use App\Models\Escolaridad;

class PacientesController extends Controller
{
    public function index(Request $request)
    {
        // El auxiliar ve solo los suyos; el resto del personal, a todos.
        $pacientes = AlcanceDePacientes::aplicar(Paciente::query(), $request->user())
            ->with([
            'expediente',
            // El modal de Expediente se abre también desde aquí, así que necesita las
            // mismas relaciones que carga ExpedienteController::index.
            'expediente.estado',
            'expediente.modalidad',
            'expediente.anamnesis.items.criterio',
            'expediente.diagnosticos',
            'expediente.servicios',
            'expediente.evaluaciones',
            'expediente.paciente.escolaridad',
            'encargado',
            'genero',
            'escolaridad',
            // El modal de Historial lista las citas en orden cronológico.
            'citas' => fn($q) => $q->orderBy('fecha')->orderBy('hora_inicio'),
            'citas.servicio',
            'citas.programa',
            'citas.estadoCita',
            'citas.modalidad',
            'citas.sesion',
            'citas.atendidoPor',

        ])->orderBy('apellidos')->get();

        // El modal de Expediente se abre desde aquí, y su pestaña de atención
        // terapéutica muestra el récord de terapias.
        TerapiasUsadas::colgarEn($pacientes->pluck('expediente')->filter());

        return Inertia::render('Pacientes', [
            'pacientes' => $pacientes,
            'encargados' => Encargado::all(),
            'generos' => Genero::orderBy('nombre')->get(),
            'escolaridades' => Escolaridad::orderBy('nombre')->get(),
            // Para el modal de programa, que se abre desde la ficha.
            'catalogosPrograma' => app(AsignacionProgramaController::class)->catalogos(),
        ]);
    }

    public function expediente(Paciente $paciente)
    {
        $expediente = $paciente->expediente;

        if (!$expediente) {
            abort(404, 'Expediente no encontrado');
        }

        return Inertia::render('Expedientes/Show', [
            'paciente' => $paciente,
            'expediente' => $expediente,
        ]);
    }

    public function historial(Paciente $paciente)
    {
        $citas = Cita::with(['atendidoPor', 'servicio', 'programa', 'estadoCita', 'modalidad', 'sesion'])
            ->where('paciente_id', $paciente->id)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get();

        return Inertia::render('Pacientes/Historial', [
            'paciente' => $paciente,
            'citas' => $citas,
        ]);
    }

    /**
     * Lo que el terapeuta escribió en cada sesión, a página completa.
     *
     * La ruta ya existía apuntando a un método que no estaba. Va aparte del
     * modal de Historial porque ahí la evolución cae dentro de una celda de
     * tabla, y lo que se escribe son párrafos largos que quedan ilegibles.
     */
    public function observaciones(Paciente $paciente)
    {
        $citas = Cita::with(['atendidoPor', 'servicio', 'estadoCita', 'sesion'])
            ->where('paciente_id', $paciente->id)
            // Solo las atendidas: una cita sin sesión no tiene nada escrito.
            ->whereHas('sesion')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'desc')
            ->get();

        return Inertia::render('Pacientes/Observaciones', [
            'paciente' => [
                'id' => $paciente->id,
                'nombre_completo' => $paciente->nombre_completo,
                'expediente' => $paciente->expediente?->codigo,
            ],

            'sesiones' => $citas->map(fn(Cita $cita) => [
                'id' => $cita->id,
                'fecha' => $cita->fecha->toDateString(),
                'hora' => substr($cita->hora_inicio, 0, 5),
                'servicio' => $cita->servicio?->nombre,
                'atiende' => $cita->atendidoPor?->nombre_completo,
                'estado' => $cita->estadoCita?->nombre,
                'duracion' => $cita->sesion?->duracion_minutos,
                'evolucion' => $cita->sesion?->observaciones_clinicas,
                'observaciones' => $cita->sesion?->observaciones_generales,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'escolaridad_id' => 'nullable|exists:escolaridades,id',
            'genero_id' => 'nullable|exists:generos,id',
            'encargado_id' => 'nullable|exists:encargados,id',
        ]);

        // Quién lo registró: es la mitad de lo que hace "suyo" a un paciente,
        // y sin esto un auxiliar perdería de vista al que acaba de dar de alta.
        Paciente::create([...$validated, 'creado_por' => $request->user()->id]);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente creado correctamente');
    }

    public function update(Request $request, Paciente $paciente)
    {
        $validated = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'escolaridad_id' => 'nullable|exists:escolaridades,id',
            'genero_id' => 'nullable|exists:generos,id',
            'encargado_id' => 'nullable|exists:encargados,id',
        ]);

        $paciente->update($validated);

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente actualizado correctamente');
    }

    public function destroy(Paciente $paciente)
    {
        $paciente->delete();

        return redirect()->route('pacientes.index')
            ->with('success', 'Paciente actualizado correctamente');
    }
}
