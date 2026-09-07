<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EstadoSesion;

class EstadoSesionSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['nombre' => 'Terminada', 'activo' => true],
            ['nombre' => 'Pendiente de observaciones', 'activo' => true],
            ['nombre' => 'Pendiente de planificación', 'activo' => true],
        ];

        foreach ($estados as $estado) {
            EstadoSesion::updateOrCreate(
                ['nombre' => $estado['nombre']],
                ['activo' => $estado['activo']]
            );
        }
    }
}