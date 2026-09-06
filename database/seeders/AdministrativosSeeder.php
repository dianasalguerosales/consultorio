<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administrativo;

class AdministrativosSeeder extends Seeder
{
    public function run(): void
    {
        Administrativo::updateOrCreate(
            ['user_id' => 1], // usuario admin@caine.com
            [
                'nombres' => 'Administrador',
                'apellidos' => 'General',
                'fecha_nacimiento' => '1980-01-01',
                'dpi' => null,
                'telefono' => '555-0001',
                'correo' => 'admin@caine.com',
                'cargo_id' => null, 
                'especialidad_id' => null, 
                'experiencia' => '10 años de gestión administrativa',
                'certificaciones' => 'Certificación en gestión hospitalaria',
                'cursos' => 'Curso de liderazgo organizacional',
            ]
        );

        Administrativo::updateOrCreate(
            ['user_id' => 5], // otro usuario creado en UsersSeeder
            [
                'nombres' => 'María',
                'apellidos' => 'López',
                'fecha_nacimiento' => '1990-03-15',
                'dpi' => null,
                'telefono' => '555-0002',
                'correo' => 'maria@example.com',
                'cargo_id' => null,
                'especialidad_id' => null,
                'experiencia' => '5 años en coordinación de programas',
                'certificaciones' => 'Diplomado en neurodesarrollo',
                'cursos' => 'Curso de gestión de proyectos',
            ]
        );

        Administrativo::updateOrCreate(
            ['user_id' => 6],
            [
                'nombres' => 'José',
                'apellidos' => 'Hernández',
                'fecha_nacimiento' => '1995-07-20',
                'dpi' => null,
                'telefono' => '555-0003',
                'correo' => 'jose@example.com',
                'cargo_id' => null,
                'especialidad_id' => null,
                'experiencia' => '2 años de apoyo administrativo',
                'certificaciones' => null,
                'cursos' => 'Curso de asistencia administrativa',
            ]
        );
    }
}