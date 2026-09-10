<?php

namespace App\Http\Controllers;

use App\Agenda\QuienAtiende;
use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

/**
 * Ocupación semanal del personal que atiende: cuántas citas tiene cada
 * terapeuta o auxiliar en cada día, más una comparación de horas para detectar
 * quién carga citas más largas.
 *
 * Salió de AgendaController: comparten el concepto de quién atiende, pero nada
 * más. Ver contexto/agenda-decisiones-arquitectura.md.
 */
class OcupacionController extends Controller
{
    public function index(Request $request)
    {
        $semana = ($request->date('semana') ?? Carbon::now())->startOfWeek();
        $finSemana = $semana->copy()->endOfWeek();

        $personal = QuienAtiende::todos();

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

        // Se agrupa por "tipo:id" para no confundir el id 1 de terapeutas con
        // el id 1 de administrativos.
        $porPersona = $citas->groupBy(
            fn(Cita $c) => QuienAtiende::tipoDe($c->atendido_por_type) . ':' . $c->atendido_por_id
        );

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

                    // El desglose se arma en el cliente al hacer clic, sin otra
                    // vuelta al servidor: son pocas citas por semana.
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
}
