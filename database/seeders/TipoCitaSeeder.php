<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoCita;

class TipoCitaSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Evaluación inicial', 'activo' => 1],
            ['nombre' => 'Terapia de lenguaje', 'activo' => 1],
            ['nombre' => 'Terapia ocupacional', 'activo' => 1],
            ['nombre' => 'Terapia física', 'activo' => 1],
            ['nombre' => 'Seguimiento', 'activo' => 1],
            ['nombre' => 'Control médico', 'activo' => 1],
        ];

        foreach ($tipos as $tipo) {
            TipoCita::updateOrCreate(
                ['nombre' => $tipo['nombre']],
                ['activo' => $tipo['activo']]
            );
        }
    }
}