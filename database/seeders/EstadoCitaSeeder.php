<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EstadoCita;

class EstadoCitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EstadoCita::insert([
            ['nombre' => 'Pendiente'],
            ['nombre' => 'Confirmada'],
            ['nombre' => 'Cancelada'],
        ]);
    }
}
