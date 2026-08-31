<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Terapeuta;

class TerapeutasSeeder extends Seeder
{
    public function run(): void
    {
        Terapeuta::updateOrCreate(
            ['user_id' => 2],
            [
                'nombre' => 'Juan Pérez',
                'especialidad' => 'Lenguaje',
                'numero_colegiado' => 'COL-12345',
                'telefono' => '555-1234',
                'correo' => 'juan@example.com',
                'fecha_nacimiento' => '1985-06-15',
                'formacion' => 'Licenciatura en Fonoaudiología',
                'certificaciones' => 'Certificado en intervención temprana',
            ]
        );
    }
}