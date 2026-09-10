<?php

namespace Database\Seeders;

use App\Models\Administrativo;
use App\Models\Cargo;
use App\Models\Genero;
use Illuminate\Database\Seeder;

class AdministrativosSeeder extends Seeder
{
    public function run(): void
    {
        // El cargo se busca por nombre y no se deja en null: de él dependen el
        // avatar, la etiqueta de la ficha y quién cuelga de quién en el
        // organigrama.
        $definicion = [
            [
                'user_id' => 1, // admin@caine.com
                'nombres' => 'Administrador',
                'apellidos' => 'General',
                'fecha_nacimiento' => '1980-01-01',
                'dpi' => 1985043210101,
                'telefono' => '555-0001',
                'correo' => 'admin@caine.com',
                'cargo' => 'Administrador',
                'genero' => 'Masculino',
                'experiencia' => '10 años de gestión administrativa',
                'certificaciones' => 'Certificación en gestión hospitalaria',
                'cursos' => 'Curso de liderazgo organizacional',
            ],
            [
                'user_id' => 5,
                'nombres' => 'María',
                'apellidos' => 'López',
                'fecha_nacimiento' => '1990-03-15',
                'dpi' => 2447891230101,
                'telefono' => '555-0002',
                'correo' => 'maria@example.com',
                'cargo' => 'Coordinador',
                'genero' => 'Femenino',
                'experiencia' => '5 años en coordinación de programas',
                'certificaciones' => 'Diplomado en neurodesarrollo',
                'cursos' => 'Curso de gestión de proyectos',
            ],
            [
                'user_id' => 6,
                'nombres' => 'José',
                'apellidos' => 'Hernández',
                'fecha_nacimiento' => '1995-07-20',
                'dpi' => 3012567890101,
                'telefono' => '555-0003',
                'correo' => 'jose@example.com',
                'cargo' => 'Auxiliar',
                'genero' => 'Masculino',
                'experiencia' => '2 años de apoyo administrativo',
                'certificaciones' => null,
                'cursos' => 'Curso de asistencia administrativa',
            ],
        ];

        foreach ($definicion as $d) {
            Administrativo::updateOrCreate(
                ['user_id' => $d['user_id']],
                [
                    'nombres' => $d['nombres'],
                    'apellidos' => $d['apellidos'],
                    'fecha_nacimiento' => $d['fecha_nacimiento'],
                    'dpi' => $d['dpi'],
                    'telefono' => $d['telefono'],
                    'correo' => $d['correo'],
                    'cargo_id' => Cargo::where('nombre', $d['cargo'])->value('id'),
                    'genero_id' => Genero::where('nombre', $d['genero'])->value('id'),
                    'experiencia' => $d['experiencia'],
                    'certificaciones' => $d['certificaciones'],
                    'cursos' => $d['cursos'],
                ]
            );
        }
    }
}
