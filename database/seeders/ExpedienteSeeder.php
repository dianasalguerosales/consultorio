<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expediente;
use App\Models\Paciente;

class ExpedienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pacientes = Paciente::all();

        foreach ($pacientes as $paciente) {
            $codigo = app(\App\Http\Controllers\ExpedienteController::class)
                ->generarCodigoExpediente($paciente->genero);

            Expediente::create([
                'id' => $codigo,
                'paciente_id' => $paciente->id,
                'fecha_apertura' => now(),
                'estado' => 'activo',
                'creado_por_usuario_id' => 1, // admin por defecto
                'observaciones_administrativas' => 'Expediente inicial',
            ]);
        }
    }
}
