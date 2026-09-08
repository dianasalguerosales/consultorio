<?php

namespace App\Http\Controllers;

use App\Models\Administrativo;
use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Modalidad;
use App\Models\Paciente;
use App\Models\Programa;
use App\Models\Servicio;
use App\Models\Terapeuta;
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
    /**
     * Tipos que pueden atender una cita. Se valida contra esta lista en vez de
     * aceptar cualquier clase que llegue del formulario.
     */
    private const TIPOS_ATIENDEN = [
        'terapeuta' => Terapeuta::class,
        'auxiliar' => Administrativo::class,
    ];

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
                'yoAtiendo' => $this->comoAtiende($user),
            ],

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
        $datos = $this->validar($request);

        $this->verificarSolapamiento($datos, $cita->id);

        $cita->update($datos);

        return redirect()
            ->route('agenda.index')
            ->with('success', 'Cita actualizada.');
    }

    public function destroy(Cita $cita)
    {
        $cita->delete();

        return redirect()
            ->route('agenda.index')
            ->with('success', 'Cita eliminada.');
    }

    /**
     * Ocupación semanal del personal que atiende: cuántas citas tiene cada
     * terapeuta o auxiliar en cada día de la semana, más una comparación de
     * horas para detectar quién carga citas más largas.
     */
    public function ocupacion(Request $request)
    {
        $semana = ($request->date('semana') ?? Carbon::now())->startOfWeek();
        $finSemana = $semana->copy()->endOfWeek();

        $personal = $this->personalQueAtiende();

        $citas = Cita::query()
            ->with(['paciente', 'servicio', 'estadoCita', 'modalidad', 'atendidoPor'])
            ->enRango($semana->toDateString(), $finSemana->toDateString())
            // Una cita cancelada no ocupa la agenda de nadie.
            ->whereHas('estadoCita', fn($q) => $q->where('nombre', '!=', 'Cancelada'))
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $dias = collect(range(0, 6))->map(function (int $i) use ($semana) {
            $dia = $semana->copy()->addDays($i);

            return [
                'fecha' => $dia->toDateString(),
                'etiqueta' => ucfirst($dia->locale('es')->isoFormat('ddd')),
                'numero' => $dia->day,
                'esFinDeSemana' => $dia->isWeekend(),
            ];
        });

        // Las citas se agrupan por "tipo:id" para no confundir el id 1 de
        // terapeutas con el id 1 de administrativos.
        $porPersona = $citas->groupBy(fn(Cita $c) => $this->claveDelTipo($c->atendido_por_type) . ':' . $c->atendido_por_id);

        return Inertia::render('Ocupacion', [
            'semana' => [
                'desde' => $semana->toDateString(),
                'hasta' => $finSemana->toDateString(),
                'etiqueta' => $this->etiquetaDeSemana($semana, $finSemana),
                'anterior' => $semana->copy()->subWeek()->toDateString(),
                'siguiente' => $semana->copy()->addWeek()->toDateString(),
                'esActual' => $semana->isSameWeek(Carbon::now()),
            ],

            'dias' => $dias,

            'personal' => $personal->map(function (array $persona) use ($porPersona, $dias) {
                $suyas = $porPersona->get($persona['clave'], collect());

                return [
                    ...$persona,

                    // Una celda por día: lo que pinta el heatmap.
                    'porDia' => $dias->map(function (array $dia) use ($suyas) {
                        $delDia = $suyas->filter(fn(Cita $c) => $c->fecha->toDateString() === $dia['fecha']);

                        return [
                            'fecha' => $dia['fecha'],
                            'citas' => $delDia->count(),
                            'minutos' => $delDia->sum(fn(Cita $c) => $this->minutosDe($c)),
                        ];
                    }),

                    'totales' => $this->totalesDe($suyas),

                    // El desglose se arma en el cliente al hacer clic, sin
                    // otra vuelta al servidor: son pocas citas por semana.
                    'citas' => $suyas->map(fn(Cita $c) => [
                        'id' => $c->id,
                        'fecha' => $c->fecha->toDateString(),
                        'horaInicio' => substr($c->hora_inicio, 0, 5),
                        'horaFin' => $c->hora_fin ? substr($c->hora_fin, 0, 5) : null,
                        'minutos' => $this->minutosDe($c),
                        'paciente' => $c->paciente?->nombre_completo,
                        'servicio' => $c->servicio?->nombre,
                        'estado' => $c->estadoCita?->nombre,
                        'modalidad' => $c->modalidad?->nombre,
                    ])->values(),
                ];
            })->values(),
        ]);
    }

    /** Terapeutas y auxiliares: los dos tipos que pueden atender una cita. */
    private function personalQueAtiende()
    {
        $terapeutas = Terapeuta::query()
            ->orderBy('nombres')
            ->get()
            ->map(fn(Terapeuta $t) => [
                'clave' => 'terapeuta:' . $t->id,
                'tipo' => 'terapeuta',
                'id' => $t->id,
                'nombre_completo' => $t->nombre_completo,
                'rol' => 'Terapeuta',
            ]);

        $auxiliares = Administrativo::query()
            ->whereHas('cargo', fn($q) => $q->where('nombre', 'Auxiliar'))
            ->orderBy('nombres')
            ->get()
            ->map(fn(Administrativo $a) => [
                'clave' => 'auxiliar:' . $a->id,
                'tipo' => 'auxiliar',
                'id' => $a->id,
                'nombre_completo' => $a->nombre_completo,
                'rol' => 'Auxiliar',
            ]);

        return $terapeutas->concat($auxiliares);
    }

    /** Duración en minutos; sin hora_fin se asume una hora. */
    private function minutosDe(Cita $cita): int
    {
        if (! $cita->hora_fin) {
            return 60;
        }

        $inicio = Carbon::createFromFormat('H:i:s', Cita::normalizarHora($cita->hora_inicio));
        $fin = Carbon::createFromFormat('H:i:s', Cita::normalizarHora($cita->hora_fin));

        return max(0, $inicio->diffInMinutes($fin));
    }

    /** Resumen de la semana para la tabla comparativa de horas. */
    private function totalesDe($citas): array
    {
        $minutos = $citas->map(fn(Cita $c) => $this->minutosDe($c));

        return [
            'citas' => $citas->count(),
            'minutos' => (int) $minutos->sum(),
            // Lo que revela quién carga citas largas frente a quién tiene
            // muchas cortas.
            'promedioMinutos' => $citas->isEmpty() ? 0 : (int) round($minutos->avg()),
            'maxMinutos' => (int) ($minutos->max() ?? 0),
            'diasConCitas' => $citas->pluck('fecha')->map(fn($f) => $f->toDateString())->unique()->count(),
        ];
    }

    private function etiquetaDeSemana(Carbon $desde, Carbon $hasta): string
    {
        $mesIgual = $desde->isSameMonth($hasta);

        return $mesIgual
            ? $desde->day . ' – ' . $hasta->day . ' ' . ucfirst($hasta->locale('es')->isoFormat('MMMM YYYY'))
            : $desde->day . ' ' . ucfirst($desde->locale('es')->isoFormat('MMM'))
              . ' – ' . $hasta->day . ' ' . ucfirst($hasta->locale('es')->isoFormat('MMM YYYY'));
    }

    /* ---------- Alcance por rol ---------- */

    /**
     * Limita las citas visibles según el rol:
     * - administrador, coordinador y auxiliar ven todas
     * - la terapeuta ve solo las que atiende
     * - el encargado ve solo las de sus pacientes
     * Cualquier otro rol no ve nada, en vez de verlo todo por omisión.
     */
    private function conAlcanceDe(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['administrador', 'coordinador', 'auxiliar'])) {
            return $query;
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

    /**
     * El par (tipo, id) con el que este usuario aparece como quien atiende, o
     * null si no atiende citas (por ejemplo un coordinador sin ficha).
     */
    private function comoAtiende(User $user): ?array
    {
        if ($user->administrativo) {
            return [
                'tipo' => 'auxiliar',
                'id' => $user->administrativo->id,
                'nombre_completo' => $user->administrativo->nombre_completo,
            ];
        }

        if ($user->terapeuta) {
            return [
                'tipo' => 'terapeuta',
                'id' => $user->terapeuta->id,
                'nombre_completo' => $user->terapeuta->nombre_completo,
            ];
        }

        return null;
    }

    /* ---------- Validación ---------- */

    private function validar(Request $request): array
    {
        $validado = $request->validate([
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'atiende_tipo' => ['required', Rule::in(array_keys(self::TIPOS_ATIENDEN))],
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

        $claseAtiende = self::TIPOS_ATIENDEN[$validado['atiende_tipo']];

        // exists: no sirve aquí porque la tabla depende del tipo elegido.
        if (! $claseAtiende::whereKey($validado['atiende_id'])->exists()) {
            throw ValidationException::withMessages([
                'atiende_id' => 'La persona seleccionada no existe.',
            ]);
        }

        // El auxiliar no puede agendarle a otra persona, aunque manipule el
        // formulario: se valida en el servidor, no solo en la vista.
        if ($this->soloAgendaParaSiMismo($request->user())) {
            $propio = $this->comoAtiende($request->user());

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
                'atiendeTipo' => $this->claveDelTipo($cita->atendido_por_type),
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
            ],
        ];
    }

    /** Invierte TIPOS_ATIENDEN: de clase Eloquent a la clave que usa la vista. */
    private function claveDelTipo(?string $clase): ?string
    {
        if (! $clase) {
            return null;
        }

        return array_search($clase, self::TIPOS_ATIENDEN, true) ?: null;
    }

    private function catalogos(User $user): array
    {
        // Al auxiliar solo se le ofrece a sí mismo como quien atiende.
        if ($this->soloAgendaParaSiMismo($user)) {
            $propio = $this->comoAtiende($user);
            $atienden = collect($propio ? [$propio] : []);
        } else {
            // Los auxiliares se identifican por su cargo en administrativos.
            $auxiliares = Administrativo::query()
                ->whereHas('cargo', fn($q) => $q->where('nombre', 'Auxiliar'))
                ->orderBy('nombres')
                ->get()
                ->map(fn(Administrativo $a) => [
                    'id' => $a->id,
                    'tipo' => 'auxiliar',
                    'nombre_completo' => $a->nombre_completo,
                ]);

            $terapeutas = Terapeuta::query()
                ->orderBy('nombres')
                ->get()
                ->map(fn(Terapeuta $t) => [
                    'id' => $t->id,
                    'tipo' => 'terapeuta',
                    'nombre_completo' => $t->nombre_completo,
                ]);

            $atienden = $terapeutas->concat($auxiliares);
        }

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

            'estados' => EstadoCita::where('activo', 1)->orderBy('id')->get(['id', 'nombre']),
            'modalidades' => Modalidad::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'tiposCita' => TipoCita::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'servicios' => Servicio::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre']),
            'programas' => Programa::where('activo', 1)->orderBy('nombre')->get(['id', 'nombre', 'precio_mensual']),
        ];
    }
}
