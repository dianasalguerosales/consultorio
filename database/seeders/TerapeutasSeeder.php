<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Terapeuta;

class TerapeutasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Terapeuta::updateOrCreate(
            ['user_id' => 3],
            [
                'nombre' => 'Juan Pérez',
                'especialidad' => 'Lenguaje',
                'fecha_nacimiento' => '1985-06-15',
                'telefono' => '555-1234',
                'correo' => 'juan@example.com',
                'numero_colegiado' => 'COL-12345',
                'experiencia' => '10 años en terapia de lenguaje',
                'formacion' => 'Licenciatura en Psicología',
                'certificaciones' => 'Certificación en Neurodesarrollo',
            ]
        );

        Terapeuta::updateOrCreate(
            ['user_id' => 4],
            [
                'nombre' => 'María López',
                'especialidad' => 'Coordinador',
                'fecha_nacimiento' => '1980-03-20',
                'telefono' => '555-5678',
                'correo' => 'maria@example.com',
            ]
        );
    }
}
