<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoCita;

class EstadoCitaSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            'Pendiente',
            'Confirmada',
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