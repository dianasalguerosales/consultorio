<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Administrativo;
use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\Modalidad;
use App\Models\Paciente;
use App\Models\Programa;
use App\Models\Servicio;
use App\Models\Terapeuta;
use App\Models\TipoCita;

/**
 * Citas de prueba repartidas entre todo el personal que atiende, con duraciones
 * y densidad variadas, para poder evaluar la vista de ocupación: sin variedad
 * el heatmap sale plano y la comparación de horas no compara nada.
 *
 * Cubre la semana pasada, la actual y la siguiente. Es idempotente y no genera
 * choques de horario: por persona y día el cursor avanza con cada cita.
 */
class CitasPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = Paciente::orderBy('id')->get();
        $terapeutas = Terapeuta::orderBy('id')->get();

        if ($pacientes->isEmpty() || $terapeutas->isEmpty()) {
            return;
        }

        $auxiliares = Administrativo::whereHas('cargo', fn($q) => $q->where('nombre', 'Auxiliar'))
            ->orderBy('id')
            ->get();

        // Quien atiende: terapeutas y auxiliares juntos.
        $personal = $terapeutas
            ->map(fn(Terapeuta $t) => ['modelo' => $t, 'carga' => null])
            ->concat($auxiliares->map(fn(Administrativo $a) => ['modelo' => $a, 'carga' => null]))
            ->values();

        $servicios = Servicio::orderBy('id')->get();
        $tipos = TipoCita::orderBy('id')->get();
        $programas = Programa::orderBy('id')->get();
        $modalidades = Modalidad::where('activo', 1)->orderBy('id')->get();

        $estados = EstadoCita::whereIn('nombre', ['Pendiente', 'Programada', 'Confirmada', 'Reprogramada'])
            ->orderBy('id')
            ->get();

        if ($servicios->isEmpty() || $tipos->isEmpty() || $estados->isEmpty()) {
            return;
        }

        // Cuántas citas hace cada persona por día, de lunes a viernes. Se fija a
        // mano en vez de aleatorio para que el heatmap muestre un patrón legible
        // y estable entre corridas: hay gente saturada, gente media y días libres.
        $densidad = [
            [3, 4, 2, 4, 3],   // primera persona: la más cargada
            [2, 0, 3, 1, 2],   // con un día libre en medio
            [1, 2, 2, 0, 3],
            [0, 1, 1, 2, 1],   // carga baja
            [2, 1, 0, 1, 0],   // el auxiliar, si existe
        ];

        // Duraciones en minutos: quien lleva citas largas debe notarse en la
        // tabla de horas aunque tenga menos citas que otro.
        $duraciones = [
            [60, 90, 60, 90, 60],
            [45, 45, 60, 45, 45],
            [30, 45, 30, 30, 45],
            [90, 90, 60, 90, 90],
            [60, 30, 45, 60, 30],
        ];

        // Semana pasada, actual y siguiente.
        foreach ([-1, 0, 1] as $desplazamiento) {
            $lunes = Carbon::now()->startOfWeek()->addWeeks($desplazamiento);

            foreach ($personal as $indice => $quien) {
                $filaDensidad = $densidad[$indice % count($densidad)];
                $filaDuracion = $duraciones[$indice % count($duraciones)];

                foreach (range(0, 4) as $dia) {
                    $cuantas = $filaDensidad[$dia];

                    // La semana pasada y la siguiente van un poco más flojas,
                    // así la actual no se ve idéntica a las vecinas.
                    if ($desplazamiento !== 0 && $cuantas > 0) {
                        $cuantas = max(1, $cuantas - 1);
                    }

                    if ($cuantas === 0) {
                        continue;
                    }

                    $fecha = $lunes->copy()->addDays($dia);
                    $minutos = $filaDuracion[$dia];

                    // El cursor arranca a las 8, salta el almuerzo y avanza con
                    // cada cita, así nunca se encima nada.
                    $cursor = $fecha->copy()->setTime(8, 0);

                    for ($n = 0; $n < $cuantas; $n++) {
                        if ($cursor->hour === 12) {
                            $cursor->setTime(14, 0);
                        }

                        $inicio = $cursor->copy();
                        $fin = $cursor->copy()->addMinutes($minutos);

                        // El seeder respeta la misma regla que el controlador:
                        // nadie queda con dos citas encimadas. Si el hueco ya
                        // está tomado se corre al siguiente.
                        $choca = Cita::solapadas(
                            $quien['modelo']->getMorphClass(),
                            $quien['modelo']->id,
                            $fecha->toDateString(),
                            $inicio->format('H:i'),
                            $fin->format('H:i')
                        )->exists();

                        if ($choca) {
                            $cursor = $fin->addMinutes(15);
                            continue;
                        }

                        // Se reparten los pacientes de forma estable.
                        $paciente = $pacientes[($indice * 3 + $dia * 2 + $n) % $pacientes->count()];
                        $servicio = $servicios[($indice + $n) % $servicios->count()];
                        $tipo = $tipos[($dia + $n) % $tipos->count()];
                        $estado = $estados[($indice + $dia + $n) % $estados->count()];
                        $modalidad = $modalidades->isEmpty() ? null : $modalidades[($indice + $dia) % $modalidades->count()];
                        $programa = $programas->isEmpty() ? null : $programas[$n % $programas->count()];

                        Cita::updateOrCreate(
                            [
                                'atendido_por_type' => $quien['modelo']->getMorphClass(),
                                'atendido_por_id' => $quien['modelo']->id,
                                'fecha' => $fecha->toDateString(),
                                'hora_inicio' => $inicio->format('H:i:s'),
                            ],
                            [
                                'paciente_id' => $paciente->id,
                                'hora_fin' => $fin->format('H:i:s'),
                                'servicio_id' => $servicio->id,
                                'tipo_cita_id' => $tipo->id,
                                'estado_cita_id' => $estado->id,
                                'modalidad_id' => $modalidad?->id,
                                'programa_id' => $programa?->id,
                                'precio_aplicado' => 250.00,
                            ]
                        );

                        // 15 minutos de holgura entre una cita y la siguiente.
                        $cursor = $fin->addMinutes(15);
                    }
                }
            }
        }
    }
}
