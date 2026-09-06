<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pago;
use App\Models\Paciente;
use App\Models\Cita;

class PagoSeeder extends Seeder
{
    public function run(): void
    {
        $paciente = Paciente::where('nombres', 'Pedro')
                            ->where('apellidos', 'Ramírez')
                            ->first();

        $cita = Cita::where('paciente_id', $paciente?->id)->first();

        if ($paciente) {
            Pago::updateOrCreate(
                [
                    'paciente_id' => $paciente->id,
                    'cita_id'     => $cita?->id,
                ],
                [
                    'programa_id' => null,
                    'monto'       => 250.00,
                    'metodo'      => 'Efectivo',
                    'estado'      => 'pagado',
                    'fecha'       => now()->toDateString(),
                ]
            );
        }
    }
}