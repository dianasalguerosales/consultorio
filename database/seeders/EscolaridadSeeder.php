<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Escolaridad;

class EscolaridadSeeder extends Seeder
{
    public function run(): void
    {
        $escolaridades = [
            'Prekinder',
            'Kinder',
            'Preparatoria',
            '1ro Primaria',
            '2do Primaria',
            '3ro Primaria',
            '4to Primaria',
            '5to Primaria',
            '6to Primaria',
            '1ro Básico',
            '2do Básico',
            '3ro Básico',
            'No aplica',
        ];

        foreach ($escolaridades as $nombre) {
            Escolaridad::updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => 1]
            );
        }
    }
}