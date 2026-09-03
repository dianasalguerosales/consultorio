<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Terapia;

class TerapiasSeeder extends Seeder
{
    public function run()
    {
        $terapias = [
            'Terapia de lenguaje',
            'Terapia ocupacional',
            'Terapia física',
            'Terapia psicológica',
            'Terapia conductual',
            'Terapia grupal',
        ];

        foreach ($terapias as $nombre) {
            Terapia::create(['nombre' => $nombre]);
        }
    }
}