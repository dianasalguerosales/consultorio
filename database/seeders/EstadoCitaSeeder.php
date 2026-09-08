<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoCita;

class EstadoCitaSeeder extends Seeder
{
    public function run(): void
    {
        // La agenda los usa en su leyenda y en el resumen del día. 'Atendida'
        // la pone SesionController y 'Vencida' el comando citas:marcar-vencidas;
        // ninguna de las dos se elige a mano.
        $estados = [
            'Pendiente',
            'Programada',
            'Confirmada',
            'Reprogramada',
            'Cancelada',
            'Atendida',
            'Vencida',
        ];

        foreach ($estados as $nombre) {
            EstadoCita::updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => 1]
            );
        }
    }
}