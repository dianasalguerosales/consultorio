<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Models\Servicio;
use App\Models\EstadoCita;
use App\Models\Modalidad;
use App\Models\TipoCita;

class CitaSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = Paciente::orderBy('id')->get();
        $terapeutas = Terapeuta::orderBy('id')->get();

        if ($pacientes->isEmpty() || $terapeutas->isEmpty()) {
            return;
        }

        $servicios = Servicio::orderBy('id')->get();
        $modalidad = Modalidad::where('nombre', 'Presencial')->first();

        // Antes se buscaba el tipo 'Evaluación', que no existe en el catálogo
        // (es 'Evaluación inicial'), así que el seeder nunca creaba nada.
        $tipos = TipoCita::orderBy('id')->get();

        $estados = EstadoCita::whereIn('nombre', ['Pendiente', 'Programada', 'Confirmada'])
            ->orderBy('id')
            ->get();

        if ($servicios->isEmpty() || $tipos->isEmpty() || $estados->isEmpty()) {
            return;
        }

        // Una sola cita de referencia, en una fecha fija y fuera de las semanas
        // que llena CitasPruebaSeeder. El volumen de citas para probar la agenda
        // y la ocupación lo genera ese otro seeder; tener dos fuentes creando
        // citas para la misma persona y semana producía horarios encimados.
        $paciente = $pacientes->first();
        $terapeuta = $terapeutas->first();

        Cita::updateOrCreate(
            [
                'atendido_por_type' => $terapeuta->getMorphClass(),
                'atendido_por_id' => $terapeuta->id,
                'fecha' => Carbon::now()->startOfWeek()->subWeeks(4)->toDateString(),
                'hora_inicio' => '09:00:00',
            ],
            [
                'paciente_id' => $paciente->id,
                'servicio_id' => $servicios->first()->id,
                'estado_cita_id' => $estados->first()->id,
                'hora_fin' => '10:00:00',
                'modalidad_id' => $modalidad?->id,
                'tipo_cita_id' => $tipos->first()->id,
                'precio_aplicado' => 250.00,
            ]
        );
    }
}
