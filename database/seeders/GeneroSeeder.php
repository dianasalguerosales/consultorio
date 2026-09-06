<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Genero;

class GeneroSeeder extends Seeder
{
    public function run(): void
    {
        $generos = [
            ['nombre' => 'Masculino', 'activo' => 1],
            ['nombre' => 'Femenino', 'activo' => 1],
            ['nombre' => 'Otro', 'activo' => 1],
            ['nombre' => 'No especificado', 'activo' => 1],
        ];

        foreach ($generos as $genero) {
            Genero::updateOrCreate(
                ['nombre' => $genero['nombre']],
                ['activo' => $genero['activo']]
            );
        }
    }
}