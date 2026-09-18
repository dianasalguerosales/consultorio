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
            $expediente = Expediente::firstOrNew(['paciente_id' => $paciente->id]);

            // El código se genera una sola vez. Antes iba dentro del
            // updateOrCreate, así que cada corrida renumeraba los expedientes
            // que ya existían y el correlativo se corría hacia adelante.
            $expediente->codigo ??= Expediente::generarCodigoExpediente();

            // Lo mismo con la fecha de nacimiento: si el expediente ya la
            // tiene, se respeta. La fecha vive en `expedientes` y en ningún
            // otro lado — `pacientes` no tiene esa columna, y por leerla de ahí
            // los expedientes sembrados nacían sin fecha.
            $expediente->fecha_nacimiento ??= now()
                ->subYears(rand(4, 12))
                ->subDays(rand(0, 364))
                ->toDateString();

            // Cuándo abrió el caso: tampoco se pisa en cada corrida.
            $expediente->fecha_inicio ??= now();

            $expediente->fill([
                'nombres' => $paciente->nombres,
                'apellidos' => $paciente->apellidos,
                'estado_expediente_id' => 1,
                'motivo_consulta' => 'Consulta inicial',
                'consentimiento' => 1,
                'observaciones' => 'Expediente inicial generado automáticamente',
            ])->save();
        }
    }
}
