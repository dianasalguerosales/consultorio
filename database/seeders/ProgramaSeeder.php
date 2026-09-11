<?php

namespace Database\Seeders;

use App\Models\Programa;
use Illuminate\Database\Seeder;

/**
 * Los programas que se venden. La cantidad de citas es la que trae el paquete;
 * el costo se divide entre ellas para sacar el precio de cada cita.
 *
 * Los costos quedan vacíos a propósito: los llena la coordinación desde
 * Parámetros. No se inventan acá.
 */
class ProgramaSeeder extends Seeder
{
    public function run(): void
    {
        $programas = [
            [
                'nombre' => '4 sesiones',
                'descripcion' => 'Cuatro citas al mes.',
                'sesiones_por_mes' => 4,
            ],
            [
                'nombre' => '8 sesiones',
                'descripcion' => 'Ocho citas al mes.',
                'sesiones_por_mes' => 8,
            ],
            [
                'nombre' => '12 sesiones',
                'descripcion' => 'Doce citas al mes.',
                'sesiones_por_mes' => 12,
            ],
            [
                'nombre' => 'Caine Kids',
                'descripcion' => 'Escuela de lunes a viernes, de 9:15 a 12:15. Se paga por mes.',
                'sesiones_por_mes' => 20,
            ],
        ];

        foreach ($programas as $p) {
            Programa::updateOrCreate(
                ['nombre' => $p['nombre']],
                [
                    'descripcion' => $p['descripcion'],
                    'sesiones_por_mes' => $p['sesiones_por_mes'],
                    'activo' => 1,
                ]
            );
        }
    }
}
