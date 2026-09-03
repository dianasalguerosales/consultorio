<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Diagnostico;

class DiagnosticosSeeder extends Seeder
{
    public function run()
    {
        $diagnosticos = [
            'Trastorno del lenguaje',
            'Déficit de atención',
            'Autismo',
            'Dislexia',
            'Trastorno de ansiedad infantil'
        ];

        foreach ($diagnosticos as $nombre) {
            Diagnostico::create(['nombre' => $nombre]);
        }
    }
}