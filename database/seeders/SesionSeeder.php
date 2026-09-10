<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\EstadoCita;
use App\Models\EstadoSesion;
use App\Models\Sesion;
use App\Models\Terapeuta;
use Illuminate\Database\Seeder;

/**
 * Sesiones atendidas, que son la base del módulo de Pagos: solo se cobra lo
 * que ya se dio.
 *
 * Toma citas pasadas atendidas por una terapeuta —la FK sesiones.terapeuta_id
 * apunta a `terapeutas`, así que las de un auxiliar no sirven— les registra la
 * sesión y las pasa a Atendida.
 */
class SesionSeeder extends Seeder
{
    private const OBSERVACIONES = [
        [
            'evolucion' => 'Avance sostenido en la articulación de fonemas /r/ y /s/.',
            'clinicas' => 'Buena disposición. Mantiene la atención los 45 minutos completos.',
            'generales' => 'La madre reporta que practica los ejercicios en casa.',
        ],
        [
            'evolucion' => 'Mejora en el agarre del lápiz; todavía cuesta la presión.',
            'clinicas' => 'Fatiga motriz hacia el final de la sesión.',
            'generales' => 'Llegó puntual, acompañado por el padre.',
        ],
        [
            'evolucion' => 'Tolera texturas que antes rechazaba.',
            'clinicas' => 'Respuesta sensorial más regulada que en sesiones previas.',
            'generales' => 'Se recomienda continuar con la dieta sensorial en casa.',
        ],
        [
            'evolucion' => 'Amplió el vocabulario expresivo con 12 palabras nuevas.',
            'clinicas' => 'Inicia interacción de forma espontánea, algo que no hacía.',
            'generales' => 'Sesión completa sin interrupciones.',
        ],
        [
            'evolucion' => 'Sigue instrucciones de dos pasos sin apoyo visual.',
            'clinicas' => 'Se distrae con ruidos del pasillo; conviene cerrar la puerta.',
            'generales' => 'Asistió con 10 minutos de retraso.',
        ],
        [
            'evolucion' => 'Consolidó la secuencia de vestido autónomo.',
            'clinicas' => 'Frustración baja ante el error, mejor que el mes pasado.',
            'generales' => 'La encargada pidió pautas para reforzar en casa.',
        ],
        [
            'evolucion' => 'Trabajo en regulación emocional con apoyo de pictogramas.',
            'clinicas' => 'Dos episodios de desregulación, ambos superados solo.',
            'generales' => 'Se entregó material de apoyo para la semana.',
        ],
        [
            'evolucion' => 'Primera evaluación de seguimiento trimestral.',
            'clinicas' => 'Se observan avances en las tres áreas trabajadas.',
            'generales' => 'Se agendó reunión de devolución con los padres.',
        ],
    ];

    public function run(): void
    {
        $terminada = EstadoSesion::where('nombre', 'Terminada')->value('id');
        $atendida = EstadoCita::where('nombre', 'Atendida')->value('id');

        if (! $terminada || ! $atendida) {
            $this->command?->warn('Faltan los catálogos de estado. Corré EstadoSesionSeeder y EstadoCitaSeeder.');

            return;
        }

        // Solo las que atiende una terapeuta y ya pasaron. Se toma una por
        // dia para que queden repartidas entre quincenas y el filtro de la
        // vista de Pagos se pueda probar de verdad.
        $citas = Cita::query()
            ->where('atendido_por_type', Terapeuta::class)
            ->whereDate('fecha', '<', now()->toDateString())
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get()
            ->groupBy(fn ($cita) => $cita->fecha->toDateString())
            ->map->first()
            ->values();

        foreach ($citas as $i => $cita) {
            $obs = self::OBSERVACIONES[$i % count(self::OBSERVACIONES)];

            Sesion::updateOrCreate(
                ['cita_id' => $cita->id],
                [
                    'terapeuta_id' => $cita->atendido_por_id,
                    'evolucion' => $obs['evolucion'],
                    'observaciones_clinicas' => $obs['clinicas'],
                    'observaciones_generales' => $obs['generales'],
                    'duracion_minutos' => 45,
                    'estado_sesion_id' => $terminada,
                ]
            );

            $cita->update(['estado_cita_id' => $atendida]);
        }

        $this->command?->info("Sesiones registradas: {$citas->count()}");
    }
}
