<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoCivil;

class EstadoCivilSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Soltero', 'activo' => 1],
            ['nombre' => 'Casado', 'activo' => 1],
            ['nombre' => 'Divorciado', 'activo' => 1],
            ['nombre' => 'Viudo', 'activo' => 1],
            ['nombre' => 'Unión libre', 'activo' => 1],
        ];

        foreach ($estados as $estado) {
            EstadoCivil::updateOrCreate(
                ['nombre' => $estado['nombre']],
                ['activo' => $estado['activo']]
            );
        }
    }
}