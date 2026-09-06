<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expediente;
use App\Models\Paciente;

class ExpedienteSeeder extends Seeder
{
    public function run(): void
    {
        $pacientes = Paciente::all();

        foreach ($pacientes as $paciente) {
            $codigo = Expediente::generarCodigoExpediente();

            Expediente::updateOrCreate(
                ['paciente_id' => $paciente->id],
                [
                    'codigo' => $codigo,
                    'nombres' => $paciente->nombres,
                    'apellidos' => $paciente->apellidos,
                    'fecha_nacimiento' => $paciente->fecha_nacimiento,
                    'estado_expediente_id' => 1,
                    'motivo_consulta' => 'Consulta inicial',
                    'fecha_inicio' => now(),
                    'consentimiento' => 1,
                    'observaciones' => 'Expediente inicial generado automáticamente',
                ]
            );
        }
    }
}