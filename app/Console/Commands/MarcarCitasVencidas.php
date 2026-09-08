<?php

namespace App\Console\Commands;

use App\Models\Cita;
use App\Models\EstadoCita;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class MarcarCitasVencidas extends Command
{
    protected $signature = 'citas:marcar-vencidas';

    protected $description = 'Marca como Vencidas las citas cuya fecha ya pasó sin que el paciente confirmara ni fuera atendido';

    // Los estados que dejan la cita "en el aire". Confirmada, Atendida,
    // Cancelada y Reprogramada ya tienen desenlace, así que no vencen.
    private const ESTADOS_QUE_VENCEN = ['Pendiente', 'Programada'];

    public function handle(): int
    {
        $vencida = EstadoCita::where('nombre', 'Vencida')->value('id');

        if (! $vencida) {
            $this->error("Falta el estado 'Vencida' en estado_citas. Corré EstadoCitaSeeder.");

            return self::FAILURE;
        }

        $idsQueVencen = EstadoCita::whereIn('nombre', self::ESTADOS_QUE_VENCEN)->pluck('id');

        // El corte es ayer: una cita de hoy más tarde todavía puede atenderse.
        $marcadas = Cita::query()
            ->whereIn('estado_cita_id', $idsQueVencen)
            ->whereDate('fecha', '<', Carbon::today()->toDateString())
            // Una cita ya atendida no vence aunque su estado quedara atrasado.
            ->whereDoesntHave('sesion')
            ->update(['estado_cita_id' => $vencida]);

        $this->info("Citas marcadas como Vencidas: {$marcadas}.");

        return self::SUCCESS;
    }
}
