<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Servicio::insert([
            ['nombre' => 'Terapia Individual'],
            ['nombre' => 'Terapia de Grupo'],
            ['nombre' => 'Evaluación Inicial'],
        ]);
    }
}
