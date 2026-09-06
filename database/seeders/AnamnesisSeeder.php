<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Anamnesis;
use App\Models\AnamnesisItem;
use App\Models\Expediente;
use App\Models\Criterio;

class AnamnesisSeeder extends Seeder
{
    public function run(): void
    {
        $expediente = Expediente::first();

        if ($expediente) {
            $anamnesis = Anamnesis::updateOrCreate(
                ['expediente_id' => $expediente->id],
                ['observaciones' => 'Anamnesis inicial generada automáticamente']
            );

            $criterios = Criterio::take(3)->get();

            foreach ($criterios as $criterio) {
                AnamnesisItem::updateOrCreate(
                    [
                        'anamnesis_id' => $anamnesis->id,
                        'criterio_id' => $criterio->id,
                    ],
                    ['respuesta' => rand(0,1)] 
                );
            }
        }
    }
}