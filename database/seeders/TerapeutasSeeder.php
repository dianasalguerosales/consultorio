<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Terapeuta;
use App\Models\User;
use App\Models\Especialidad;

class TerapeutasSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'juan@example.com')->first();
        $especialidad = Especialidad::where('nombre', 'Lenguaje')->first();

        if ($user && $especialidad) {
            Terapeuta::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres' => 'Juan',
                    'apellidos' => 'Pérez',
                    'especialidad_id' => $especialidad->id,
                    'dpi' => null,
                    'telefono' => '555-1234',
                    'correo' => 'juan@example.com',
                    'fecha_nacimiento' => '1985-06-15',
                    'experiencia' => 'Licenciatura en Fonoaudiología',
                    'certificaciones' => 'Certificado en intervención temprana',
                    'cursos' => 'Curso de terapia del lenguaje avanzado',
                ]
            );
        }
    }
}