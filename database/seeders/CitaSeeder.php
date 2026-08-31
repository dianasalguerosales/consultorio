<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cita;

class CitaSeeder extends Seeder
{
    public function run()
    {
        Cita::create([
            'paciente_id' => 1,
            'terapeuta_id' => 1,
            'servicio_id' => 1,
            'estado_cita_id' => 1,
            'fecha' => '2026-08-29',
            'hora_inicio' => '09:00:00',
            'hora_fin' => '10:00:00',
            'modalidad' => 'Presencial',
            'observaciones' => 'Primera consulta de evaluación',
        ]);
    }
}
