<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Programa;

class ProgramaSeeder extends Seeder
{
    public function run(): void
    {
        $programas = [
            ['nombre' => 'Diario', 'descripcion' => 'Sesiones todos los días', 'sesiones_por_mes' => 30, 'precio_mensual' => 800.00, 'activo' => 1],
            ['nombre' => 'Mensual', 'descripcion' => 'Plan mensual estándar', 'sesiones_por_mes' => 8, 'precio_mensual' => 250.00, 'activo' => 1],
        ];

        foreach ($programas as $p) {
            Programa::updateOrCreate(
                ['nombre' => $p['nombre']],
                [
                    'descripcion' => $p['descripcion'],
                    'sesiones_por_mes' => $p['sesiones_por_mes'],
                    'precio_mensual' => $p['precio_mensual'],
                    'activo' => $p['activo'],
                ]
            );
        }
    }
}