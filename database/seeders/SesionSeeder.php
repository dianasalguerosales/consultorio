<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sesion;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\EstadoSesion;
use App\Models\Terapeuta;

class SesionSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar paciente por nombre
        $pacientePedro = Paciente::where('nombres', 'Pedro')
                                 ->where('apellidos', 'Ramírez')
                                 ->first();

        // Buscar cita asociada al paciente
        $citaPedro = Cita::where('paciente_id', $pacientePedro?->id)->first();

        // Buscar terapeuta y estado de sesión
        $terapeuta = Terapeuta::where('correo', 'juan@example.com')->first();
        $estadoActiva = EstadoSesion::where('nombre', 'Activa')->first();

        if ($citaPedro && $terapeuta && $estadoActiva) {
            Sesion::updateOrCreate(
                ['cita_id' => $citaPedro->id, 'terapeuta_id' => $terapeuta->id],
                [
                    'evolucion' => 'Sesión inicial de lenguaje con avances en pronunciación.',
                    'observaciones_clinicas' => 'Paciente atento y colaborador.',
                    'observaciones_generales' => 'Se recomienda reforzar ejercicios en casa.',
                    'duracion_minutos' => 45,
                    'estado_sesion_id' => $estadoActiva->id,
                ]
            );
        }
    }
}