<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RelacionPaciente;

class RelacionesPacienteSeeder extends Seeder
{
    public function run(): void
    {
        $relaciones = ['Padre', 'Madre', 'Tutor', 'Hermano', 'Otro'];

        foreach ($relaciones as $nombre) {
            RelacionPaciente::updateOrCreate(['nombre' => $nombre]);
        }
    }
}
