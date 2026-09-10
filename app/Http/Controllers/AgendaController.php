<?php

namespace App\Http\Controllers;

use App\Agenda\QuienAtiende;
use App\Models\SolicitudReprogramacion;
use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Modalidad;
use App\Models\Paciente;
use App\Models\Programa;
use App\Models\Servicio;
use App\Models\TipoCita;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // FullCalendar manda el rango visible; sin él se asume el mes actual.
        $desde = $request->date('desde') ?? Carbon::now()->startOfMonth();
        $hasta = $request->date('hasta') ?? Carbon::now()->endOfMonth();

        $citas = $this->conAlcanceDe(
            Cita::query()->with([
                'paciente.genero',
                'atendidoPor',
                'estadoCita',
                'servicio',
                'modalidad',
                'tipoCita',
                'sesion',
                // Para no ofrecer dos veces la reprogramación de la misma cita.
                'solicitudesReprogramacion',
            ]),
            $user
        )
            ->enRango($desde->toDateString(), $hasta->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        return Inertia::render('Agenda', [
            'citas' => $citas->map(fn(Cita $cita) => $this->comoEvento($cita))->values(),

            'rango' => [
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
            ],

            // Quién puede hacer qué; la vista oculta lo que no aplique.
            'permisos' => [
                'agendar' => $user->can('agendar citas'),
                'verOcupacion' => $user->can('ver ocupacion personal'),
                // El auxiliar agenda solo para sí mismo, así que el selector
                // de "Atiende" le queda fijo.
                'soloParaSiMismo' => $this->soloAgendaParaSiMismo($user),
                'yoAtiendo' => QuienAtiende::de($user),
                'atender' => $user->hasRole('terapeuta'),
                // El encargado pide mover las citas de sus hijos.
                'solicitarReprogramacion' => (bool) $user->encargado,
                'resolverReprogramacion' => $user->hasAnyRole(['administrador', 'coordinador']),
            ],

            // La regla de anticipación se manda armada: el frontend no debe
            // tener su propia copia del número.
            'horasMinimasReprogramacion' => SolicitudReprogramacion::HORAS_MINIMAS,

            // Las que esperan respuesta, para quien las autoriza.
            'solicitudes' => $user->hasAnyRole(['administrador', 'coordinador'])
                ? $this->solicitudesPendientes()
                : [],

            // Catálogos solo para quien realmente puede crear citas.
            'catalogos' => $user->can('agendar citas')
                ? $this->catalogos($user)
                : null,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $this->verificarSolapamiento($datos);

        $cita = Cita::create($datos);

        return redirect()
            ->route('agenda.index')
            ->with('success', "Cita creada para el {$cita->fecha->format('d/m/Y')}.");
    }

    public function update(Request $request, Cita $cita)
    {
        $this->verificarPuedeGestionar($request->user(), $cita);

        $datos = $this->validar($request);

        $this->verificarSolapamiento($datos, $cita->id);

        $cita->update($datos);

        return redirect()
            ->route('agenda.index')
            ->with('success', 'Cita actualizada.');
    }

    public function destroy(Request $request, Cita $cita)
    {
        $this->verificarPuedeGestionar($request->user(), $cita);

        $cita->delete();

        return redirect()
            ->route('agenda.index')
            ->with('success', 'Cita eliminada.');
    }
    /* ---------- Alcance por rol ---------- */

    /**
     * Limita las citas visibles según el rol:
     * - Administrador, coordinador 
     * - El auxiliar ve solo las que el atiende
     * - La terapeuta ve solo las que atiende
     * - El encargado ve solo las de sus pacientes
     * Cualquier otro rol no ve nada, en vez de verlo todo por omisión.
     */
    private function conAlcanceDe(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['administrador', 'coordinador'])) {
            return $query;
        }

        // Va después del bloque de arriba a propósito: si además fuera
        // coordinador, gana el alcance amplio.
        if ($user->hasRole('auxiliar')) {
            return $user->administrativo
                ? $query->atendidasPor($user->administrativo)
                : $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('terapeuta')) {
            return $user->terapeuta
                ? $query->atendidasPor($user->terapeuta)
                : $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('encargado')) {
            return $user->encargado
                ? $query->whereHas(
                    'paciente',
                    fn($q) => $q->where('encargado_id', $user->encargado->id)
                )
                : $query->whereRaw('1 = 0');
        }

        return $query->whereRaw('1 = 0');
    }

    /* ---------- Quién puede agendar para quién ---------- */

    /**
     * El auxiliar atiende solo en sucursal, así que únicamente puede agendar
     * citas que él mismo atiende. Administrador y coordinador agendan para
     * cualquiera; si alguien tiene los dos roles, manda el más amplio.
     */
    private function soloAgendaParaSiMismo(User $user): bool
    {
        return $user->hasRole('auxiliar')
            && ! $user->hasAnyRole(['administrador', 'coordinador']);
    }

    // El auxiliar gestiona solo las citas que él atiende. validar() revisa a
    // quién se le agenda; esto, que la cita que toca sea suya — sin ello podía
    // editar o borrar una ajena mandando su id.
    private function verificarPuedeGestionar(User $user, Cita $cita): void
    {
        if (! $this->soloAgendaParaSiMismo($user)) {
            return;
        }

        $propio = QuienAtiende::de($user);

        abort_unless(
            $propio
                && $cita->atendido_por_type === QuienAtiende::clase($propio['tipo'])
                && (int) $cita->atendido_por_id === $propio['id'],
            403,
            'Solo puede modificar las citas que usted mismo atiende.'
        );
    }

    /**
     * El par (tipo, id) con el que este usuario aparece como quien atiende, o
     * null si no atiende citas (por ejemplo un coordinador sin ficha).
     */
    /* ---------- Validación ---------- */

    private function validar(Request $request): array
    {
        $validado = $request->validate([
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'atiende_tipo' => ['required', Rule::in(QuienAtiende::tipos())],
            'atiende_id' => ['required', 'integer'],
            'estado_cita_id' => ['required', 'integer', 'exists:estado_citas,id'],
            'modalidad_id' => ['nullable', 'integer', 'exists:modalidades,id'],
            'tipo_cita_id' => ['nullable', 'integer', 'exists:tipo_citas,id'],
            'servicio_id' => ['nullable', 'integer', 'exists:servicios,id'],
            'programa_id' => ['nullable', 'integer', 'exists:programas,id'],
            'fecha' => ['required', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'precio_aplicado' => ['nullable', 'numeric', 'min:0'],
        ]);

        $claseAtiende = QuienAtiende::clase($validado['atiende_tipo']);

        // exists: no sirve aquí porque la tabla depende del tipo elegido.
        if (! $claseAtiende::whereKey($validado['atiende_id'])->exists()) {
            throw ValidationException::withMessages([
                'atiende_id' => 'La persona seleccionada no existe.',
            ]);
        }

        // El auxiliar no puede agendarle a otra persona, aunque manipule el
        // formulario: se valida en el servidor, no solo en la vista.
        if ($this->soloAgendaParaSiMismo($request->user())) {
            $propio = QuienAtiende::de($request->user());

            $esElMismo = $propio
                && $validado['atiende_tipo'] === $propio['tipo']
                && (int) $validado['atiende_id'] === $propio['id'];

            if (! $esElMismo) {
                throw ValidationException::withMessages([
                    'atiende_id' => 'Solo puede agendar citas que usted mismo atiende.',
                ]);
            }
        }

        return [
            'paciente_id' => $validado['paciente_id'],
            'atendido_por_type' => $claseAtiende,
            'atendido_por_id' => $validado['atiende_id'],
            'estado_cita_id' => $validado['estado_cita_id'],
            'modalidad_id' => $validado['modalidad_id'] ?? null,
            'tipo_cita_id' => $validado['tipo_cita_id'] ?? null,
            'servicio_id' => $validado['servicio_id'] ?? null,
            'programa_id' => $validado['programa_id'] ?? null,
            'fecha' => $validado['fecha'],
            'hora_inicio' => $validado['hora_inicio'],
            'hora_fin' => $validado['hora_fin'],
            'precio_aplicado' => $validado['precio_aplicado'] ?? null,
        ];
    }

    /** Nadie puede quedar con dos citas encimadas en el mismo horario. */
    private function verificarSolapamiento(array $datos, ?int $ignorarId = null): void
    {
        $choque = Cita::solapadas(
            $datos['atendido_por_type'],
            $datos['atendido_por_id'],
            $datos['fecha'],
            $datos['hora_inicio'],
            $datos['hora_fin'],
            $ignorarId
        )->with('paciente')->first();

        if (! $choque) {
            return;
        }

        throw ValidationException::withMessages([
            'hora_inicio' => sprintf(
                'Ya hay una cita de %s a %s con %s.',
                substr($choque->hora_inicio, 0, 5),
                substr((string) $choque->hora_fin, 0, 5),
                $choque->paciente?->nombre_completo ?? 'otro paciente'
            ),
        ]);
    }

    /* ---------- Armado de datos para la vista ---------- */

    /** Una cita en el formato de evento que espera FullCalendar. */
    private function comoEvento(Cita $cita): array
    {
        $atiende = $cita->atendidoPor;

        return [
            'id' => $cita->id,
            'title' => $cita->servicio?->nombre ?? $cita->tipoCita?->nombre ?? 'Cita',
            'start' => $cita->fecha->toDateString() . 'T' . $cita->hora_inicio,
            'end' => $cita->hora_fin
                ? $cita->fecha->toDateString() . 'T' . $cita->hora_fin
                : null,
            'extendedProps' => [
                'pacienteId' => $cita->paciente_id,
                'paciente' => $cita->paciente?->nombre_completo,
                'pacienteGenero' => $cita->paciente?->genero?->nombre,
                'atiende' => $atiende?->nombre_completo,
                'atiendeTipo' => QuienAtiende::tipoDe($cita->atendido_por_type),
                'atiendeId' => $cita->atendido_por_id,
                'estado' => $cita->estadoCita?->nombre,
                'estadoId' => $cita->estado_cita_id,
                'servicioId' => $cita->servicio_id,
                'modalidad' => $cita->modalidad?->nombre,
                'modalidadId' => $cita->modalidad_id,
                'tipoCitaId' => $cita->tipo_cita_id,
                'programaId' => $cita->programa_id,
                'precio' => $cita->precio_aplicado,
                'horaInicio' => substr($cita->hora_inicio, 0, 5),
                'horaFin' => $cita->hora_fin ? substr($cita->hora_fin, 0, 5) : null,

                // null = aún sin atender. Sirve para abrir el modal con lo escrito.
                'sesion' => $cita->sesion ? [
                    'evolucion' => $cita->sesion->evolucion,
                    'observacionesClinicas' => $cita->sesion->observaciones_clinicas,
                    'observacionesGenerales' => $cita->sesion->observaciones_generales,
                    'duracionMinutos' => $cita->sesion->duracion_minutos,
                ] : null,

                // Una solicitud pendiente bloquea pedir otra sobre la misma cita.
                'tieneSolicitud' => $cita->solicitudesReprogramacion
                    ->where('estado', SolicitudReprogramacion::PENDIENTE)
                    ->isNotEmpty(),
            ],
        ];
    }

    /** Solicitudes de reprogramación sin resolver, para el panel de la agenda. */
    private function solicitudesPendientes(): array
    {
        return SolicitudReprogramacion::with(['cita.paciente', 'cita.atendidoPor', 'cita.servicio', 'solicitante'])
            ->pendientes()
            ->latest()
            ->get()
            ->map(fn(SolicitudReprogramacion $s) => [
                'id' => $s->id,
                'motivo' => $s->motivo,
                'solicitadaEl' => $s->created_at?->format('d/m/Y H:i'),
                'paciente' => $s->cita?->paciente?->nombre_completo,
                'atiende' => $s->cita?->atendidoPor?->nombre_completo,
                'servicio' => $s->cita?->servicio?->nombre,
                'fecha' => $s->cita?->fecha?->format('d/m/Y'),
                'hora' => substr((string) $s->cita?->hora_inicio, 0, 5)
                    . ' - ' . substr((string) $s->cita?->hora_fin, 0, 5),
            ])
            ->all();
    }

    private function catalogos(User $user): array
    {
        // Al auxiliar solo se le ofrece a sí mismo como quien atiende.
        $propio = QuienAtiende::de($user);

        $atienden = $this->soloAgendaParaSiMismo($user)
            ? collect($propio ? [$propio] : [])
            : QuienAtiende::todos();

        return [
            'pacientes' => Paciente::query()
                ->with('genero')
                ->orderBy('nombres')
                ->get()
                ->map(fn(Paciente $p) => [
                    'id' => $p->id,
                    'nombre_completo' => $p->nombre_completo,
                    'genero' => $p->genero?->nombre,
                ]),

            // Terapeutas y auxiliares en una sola lista: el modal necesita
            // el par (tipo, id) para armar la relación polimórfica.
            'atienden' => $atienden->values(),

            // Se marcan las que pone el sistema para que el formulario las
            // muestre deshabilitadas. Sacarlas dejaría el selector en blanco al
            // editar una cita que ya esté en uno de esos estados.
            'estados' => EstadoCita::where('activo', 1)
                ->orderBy('id')
                ->get(['id', 'nombre'])
                ->map(fn(EstadoCita $estado) => [
                    'id' => $estado->id,
                    'nombre' => $estado->nombre,
                    'delSistema' => in_array($estado->nombre, ['Atendida', 'Vencida'], true),
                ]),
            'modalidades' => Modalidad::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'tiposCita' => TipoCita::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'servicios' => Servicio::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'programas' => Programa::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre', 'precio_mensual']),
        ];
    }
}
