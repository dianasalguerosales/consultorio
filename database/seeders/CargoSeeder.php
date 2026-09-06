<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo;

class CargoSeeder extends Seeder
{
    public function run(): void
    {
        $cargos = [
            ['nombre' => 'Administrador', 'activo' => 1],
            ['nombre' => 'Coordinador', 'activo' => 1],
            ['nombre' => 'Terapeuta', 'activo' => 1],
            ['nombre' => 'Auxiliar', 'activo' => 1],
            ['nombre' => 'Encargado', 'activo' => 1],
        ];

        foreach ($cargos as $cargo) {
            Cargo::updateOrCreate(
                ['nombre' => $cargo['nombre']],
                ['activo' => $cargo['activo']]
            );
        }
    }
}