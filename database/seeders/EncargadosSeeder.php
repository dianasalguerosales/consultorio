<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Encargado;

class EncargadosSeeder extends Seeder
{
    public function run(): void
    {
        Encargado::updateOrCreate(
            ['user_id' => 3],
            [
                'nombre' => 'Carlos Gómez',
                'telefono' => '555-9876',
                'correo' => 'carlos@example.com',
                'relacion' => 'Padre',
            ]
        );

        Encargado::updateOrCreate(
            ['user_id' => 4],
            [
                'nombre' => 'Ana Torres',
                'telefono' => '555-6543',
                'correo' => 'ana@example.com',
                'relacion' => 'Madre',
            ]
        );
    }
}