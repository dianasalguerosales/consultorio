<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoExpediente;

class EstadoExpedienteSeeder extends Seeder
{
    public function run(): void
    {
        $estados = ['Activo', 'Pendiente', 'Cerrado'];

        foreach ($estados as $nombre) {
            EstadoExpediente::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
