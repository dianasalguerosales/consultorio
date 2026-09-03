<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Evaluacion;

class EvaluacionesSeeder extends Seeder
{
    public function run()
    {
        $evaluaciones = [
            'FFPI-C',
            'Perfil Sensorial 2 Escolar',
            'Perfil Sensorial 2 Niño',
            'PHAI',
            'Registro del Río',
            'Escala de Desarrollo Infantil',
            'Prueba de Vocabulario',
            'WISC-IV',
            'BASC-3',
            'Conners 3'
        ];

        foreach ($evaluaciones as $nombre) {
            Evaluacion::create(['nombre' => $nombre]);
        }
    }
}
