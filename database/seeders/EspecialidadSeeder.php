<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Especialidad;

class EspecialidadSeeder extends Seeder
{
    public function run(): void
    {
        $especialidades = [
            'Neurología',
            'Psicología',
            'Terapia Ocupacional',
            'Fisioterapia',
            'Lenguaje',
            'Educación Especial',
            'Psiquiatría Infantil',
        ];

        foreach ($especialidades as $nombre) {
            Especialidad::updateOrCreate(
                ['nombre' => $nombre],
                ['nombre' => $nombre]
            );
        }
    }
}