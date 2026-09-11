<?php

namespace App\Http\Controllers;

use App\Agenda\QuienAtiende;
use App\Models\AsignacionPrograma;
use App\Models\Modalidad;
use App\Models\Paciente;
use App\Models\Programa;
use App\Models\Servicio;
use App\Models\TipoCita;
use App\Programas\CitasDelPrograma;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Los programas que lleva cada niño.
 *
 * Se asignan desde la ficha del paciente y desde acá se ven todos juntos.
 * Al guardarlo, las citas del paquete quedan en el calendario.
 */
class AsignacionProgramaController extends Controller
{
    public function index()
    {
        $asignaciones = AsignacionPrograma::query()
            ->with(['paciente:id,nombres,apellidos,genero', 'programa:id,nombre', 'servicio:id,nombre', 'atendidoPor'])
            ->withCount('citas')
            ->orderByDesc('fecha_inicio')
            ->get()
            ->map(fn (AsignacionPrograma $a) => $this->fila($a));

        return Inertia::render('Programas', [
            'asignaciones' => $asignaciones,
            'catalogos' => $this->catalogos(),
        ]);
    }

    /** Le asigna un programa a un niño y le deja sus citas en el calendario. */
    public function store(Request $request, Paciente $paciente)
    {
        $datos = $request->validate([
            'programa_id' => 'required|exists:programas,id',
            'servicio_id' => 'nullable|exists:servicios,id',
            'modalidad_id' => 'nullable|exists:modalidades,id',
            'tipo_cita_id' => 'nullable|exists:tipo_citas,id',
            'atiende' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'cantidad_citas' => 'required|integer|min:1|max:200',
            'dias' => 'required|array|min:1',
            'dias.*' => 'integer|between:1,7',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'fecha_inicio' => 'required|date',
        ]);

        [$tipo, $id] = $this->quienAtiende($datos['atiende'] ?? null);

        // Las citas y la asignación entran juntas: si hay un choque, la
        // excepción revierte todo y no queda un programa a medias.
        $citas = DB::transaction(function () use ($datos, $paciente, $tipo, $id, $request) {
            $asignacion = AsignacionPrograma::create([
                ...Arr::except($datos, 'atiende'),
                'paciente_id' => $paciente->id,
                'atendido_por_type' => $tipo,
                'atendido_por_id' => $id,
                'estado' => AsignacionPrograma::ACTIVO,
                'creado_por' => $request->user()->id,
            ]);

            if ($choques = CitasDelPrograma::choques($asignacion)) {
                throw ValidationException::withMessages([
                    'dias' => 'Estas fechas chocan con otra cita de la misma persona: '
                        . implode(', ', $choques)
                        . '. Cambie el horario, los días o quien atiende.',
                ]);
            }

            return CitasDelPrograma::crear($asignacion);
        });

        $aviso = 'Programa asignado. Quedaron ' . count($citas) . ' citas en el calendario.';

        return back()->with('success', $aviso);
    }

    /** Cancela el programa y retira del calendario sus citas que no se han dado. */
    public function destroy(AsignacionPrograma $asignacion)
    {
        $borradas = DB::transaction(function () use ($asignacion) {
            // Solo las que no se han dado: una cita ya atendida es historia y
            // además tiene un pago colgando.
            $retiradas = $asignacion->citas()
                ->whereDate('fecha', '>=', now()->toDateString())
                ->whereDoesntHave('sesion')
                ->delete();

            $asignacion->update(['estado' => AsignacionPrograma::CANCELADO]);
            $asignacion->delete();

            return $retiradas;
        });

        return back()->with('success', "Programa cancelado. Se retiraron {$borradas} citas pendientes.");
    }

    /* ---------- Armado ---------- */

    private function fila(AsignacionPrograma $a): array
    {
        return [
            'id' => $a->id,
            'paciente' => trim("{$a->paciente?->nombres} {$a->paciente?->apellidos}"),
            'genero' => $a->paciente?->genero,
            'programa' => $a->programa?->nombre ?? 'Sin programa',
            'servicio' => $a->servicio?->nombre ?? 'Sin servicio',
            'atiende' => $a->atendidoPor?->nombre_completo ?? 'Sin asignar',
            'precio' => (float) $a->precio,
            'cantidad_citas' => $a->cantidad_citas,
            'citas_creadas' => $a->citas_count,
            'dias' => $a->dias,
            'hora' => substr($a->hora_inicio, 0, 5) . ' a ' . substr($a->hora_fin, 0, 5),
            'fecha_inicio' => $a->fecha_inicio->toDateString(),
            'estado' => $a->estado,
        ];
    }

    /** Los catálogos del formulario, que son los mismos de una cita. */
    public function catalogos(): array
    {
        return [
            'programas' => Programa::where('activo', 1)->orderBy('nombre')
                ->get(['id', 'nombre', 'sesiones_por_mes', 'precio_mensual']),
            'servicios' => Servicio::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'modalidades' => Modalidad::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'tiposCita' => TipoCita::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'quienAtiende' => QuienAtiende::todos(),
        ];
    }

    /** "terapeuta:3" llega del formulario y se valida contra la lista blanca. */
    private function quienAtiende(?string $clave): array
    {
        if (! $clave) {
            return [null, null];
        }

        [$tipo, $id] = array_pad(explode(':', $clave), 2, null);

        $clase = QuienAtiende::clase($tipo);

        abort_unless($clase && $id, 422, 'No se reconoce quién atiende.');

        return [$clase, (int) $id];
    }
}
