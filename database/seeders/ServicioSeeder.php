<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        Servicio::updateOrCreate(['nombre' => 'Terapia de Lenguaje']);
        Servicio::updateOrCreate(['nombre' => 'Terapia Ocupacional']);
        Servicio::updateOrCreate(['nombre' => 'Evaluación Psicológica']);
    }
}
