<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoCita;

class EstadoCitaSeeder extends Seeder
{
    public function run(): void
    {
        // La agenda distingue estos cinco estados en su leyenda y en el
        // resumen del día.
        $estados = [
            'Pendiente',
            'Programada',
            'Confirmada',
            'Reprogramada',
            'Cancelada',
        ];

        foreach ($estados as $nombre) {
            EstadoCita::updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => 1]
            );
        }
    }
}