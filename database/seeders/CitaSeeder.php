<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
        $paciente   = Paciente::first();
        $terapeuta  = Terapeuta::first();
        $servicio   = Servicio::first();
        $estado     = EstadoCita::where('nombre', 'Pendiente')->first();
        $modalidad  = Modalidad::where('nombre', 'Presencial')->first();
        $tipo       = TipoCita::where('nombre', 'Evaluación')->first();

        if ($paciente && $terapeuta && $servicio && $estado && $modalidad && $tipo) {
            Cita::updateOrCreate(
                [
                    'paciente_id' => $paciente->id,
                    'terapeuta_id' => $terapeuta->id,
                    'fecha' => '2026-09-06',
                    'hora_inicio' => '09:00:00',
                ],
                [
                    'servicio_id' => $servicio->id,
                    'estado_cita_id' => $estado->id,
                    'hora_fin' => '10:00:00',
                    'modalidad_id' => $modalidad->id,
                    'tipo_cita_id' => $tipo->id,
                    'precio_aplicado' => 250.00,
                ]
            );
        }
    }
}