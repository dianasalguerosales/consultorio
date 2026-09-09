<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Encargado;
use App\Models\Escolaridad;
use App\Models\EstadoCivil;
use App\Models\Expediente;
use App\Models\Genero;
use App\Models\Paciente;
use App\Models\RelacionPaciente;
use App\Models\Especialidad;
use App\Models\Terapeuta;
use App\Models\User;

/**
 * Datos de prueba: 7 pacientes con toda su cadena armada — encargado (y su
 * usuario), expediente con código, y terapeutas asignados.
 *
 * Es idempotente: se puede correr varias veces sin duplicar nada, porque todo
 * usa updateOrCreate sobre una llave estable.
 */
class PacientesPruebaSeeder extends Seeder
{
    public function run(): void
    {
        $this->normalizarCodigosExistentes();

        $terapeutas = $this->crearTerapeutas();
        $encargados = $this->crearEncargados();

        $this->crearPacientes($terapeutas, $encargados);
        $this->completarDatosPrevios($terapeutas);
    }

    /**
     * Los dos pacientes que ya existían quedaron incompletos y hay usuarios sin
     * rol, lo que impide probar el alcance de la agenda. Se completan aquí.
     */
    private function completarDatosPrevios(array $terapeutas): void
    {
        // PacientesSeeder busca escolaridades 'Primaria' y 'Secundaria', que no
        // existen en el catálogo, así que Pedro y Lucía quedaron sin ella.
        $ajustes = [
            'Pedro' => ['escolaridad' => '3ro Primaria', 'terapeutas' => ['Lenguaje']],
            'Lucía' => ['escolaridad' => '1ro Básico', 'terapeutas' => ['Psicología', 'Lenguaje']],
        ];

        foreach ($ajustes as $nombres => $ajuste) {
            $paciente = Paciente::where('nombres', $nombres)->first();

            if (! $paciente) {
                continue;
            }

            if (! $paciente->escolaridad_id) {
                $paciente->update([
                    'escolaridad_id' => Escolaridad::where('nombre', $ajuste['escolaridad'])->value('id'),
                ]);
            }

            $ids = collect($ajuste['terapeutas'])->map(fn($e) => $terapeutas[$e]->id)->all();
            $paciente->terapeutas()->syncWithoutDetaching($ids);

            // El pivote encargado_paciente también estaba vacío para ellos.
            if ($paciente->encargado) {
                $paciente->encargado->pacientes()->syncWithoutDetaching([$paciente->id]);
            }
        }

        // Ana Torres es encargada de Lucía pero su usuario no tenía rol, así
        // que no podía entrar a ver la agenda de su hija.
        $ana = User::where('email', 'ana@example.com')->first();
        if ($ana && ! $ana->hasRole('encargado')) {
            $ana->assignRole('encargado');
        }

        // No había ningún auxiliar en el sistema, y sin uno no se puede probar
        // en pantalla que solo agenda para sí mismo. José Hernández ya existía
        // como administrativo sin cargo ni rol.
        $jose = User::where('email', 'jose@example.com')->first();
        if ($jose) {
            if (! $jose->hasRole('auxiliar')) {
                $jose->assignRole('auxiliar');
            }

            $jose->administrativo?->update([
                'cargo_id' => \App\Models\Cargo::where('nombre', 'Auxiliar')->value('id'),
                'superior_id' => 1,
            ]);
        }

        // Los otros dos administrativos tampoco tenían cargo asignado.
        $porCorreo = [
            'admin@caine.com' => 'Administrador',
            'maria@example.com' => 'Coordinador',
        ];

        foreach ($porCorreo as $correo => $cargo) {
            $user = User::where('email', $correo)->first();

            if ($user?->administrativo && ! $user->administrativo->cargo_id) {
                $user->administrativo->update([
                    'cargo_id' => \App\Models\Cargo::where('nombre', $cargo)->value('id'),
                ]);
            }

            if ($cargo === 'Coordinador') {
                $user->administrativo->update([
                    'superior_id' => 1,
                ]);
            }
                }
            }

    /**
     * El generador de códigos tenía un desfase que iba metiendo un 6 por
     * expediente (KID-20266002 en lugar de KID-2026002). Se corrigen los que
     * quedaron mal para que el correlativo siga desde el número correcto.
     */
    private function normalizarCodigosExistentes(): void
    {
        $prefijo = 'KID-' . date('Y');

        foreach (Expediente::where('codigo', 'like', $prefijo . '%')->get() as $exp) {
            $sufijo = substr($exp->codigo, strlen($prefijo));

            // Un sufijo sano son 3 dígitos; más que eso viene del desfase.
            if (strlen($sufijo) <= 3) {
                continue;
            }

            $numero = (int) substr($sufijo, -3);
            $exp->update(['codigo' => $prefijo . str_pad($numero, 3, '0', STR_PAD_LEFT)]);
        }
    }

    /** @return array<string,Terapeuta> indexado por especialidad */
    private function crearTerapeutas(): array
    {
        $definicion = [
            [
                'email' => 'marisol.chavez@caine.com',
                'nombres' => 'Marisol',
                'apellidos' => 'Chávez Ordóñez',
                'especialidad' => 'Lenguaje',
                'genero' => 'Femenino',
                'telefono' => '5512-7788',
                'fecha_nacimiento' => '1988-03-12',
                'experiencia' => 'Licenciatura en Fonoaudiología, 9 años en terapia infantil',
                'certificaciones' => 'Certificación PROMPT nivel 1',
                'cursos' => 'Intervención en trastornos del habla',
                'superior_id' => 2,
            ],
            [
                'email' => 'diego.aguilar@caine.com',
                'nombres' => 'Diego',
                'apellidos' => 'Aguilar Ruano',
                'especialidad' => 'Terapia Ocupacional',
                'genero' => 'Masculino',
                'telefono' => '5533-2211',
                'fecha_nacimiento' => '1990-11-05',
                'experiencia' => 'Terapeuta ocupacional pediátrico, 7 años',
                'certificaciones' => 'Integración sensorial Ayres',
                'cursos' => 'Motricidad fina y grafomotricidad',
                'superior_id' => 2,
            ],
            [
                'email' => 'gabriela.morales@caine.com',
                'nombres' => 'Gabriela',
                'apellidos' => 'Morales Pinto',
                'especialidad' => 'Psicología',
                'genero' => 'Femenino',
                'telefono' => '5544-9900',
                'fecha_nacimiento' => '1985-07-23',
                'experiencia' => 'Psicóloga infantil, 12 años de práctica clínica',
                'certificaciones' => 'Evaluación WISC-V',
                'cursos' => 'Manejo conductual en el aula',
                'superior_id' => 2,
            ],
        ];

        $terapeutas = [];

        foreach ($definicion as $d) {
            $user = User::updateOrCreate(
                ['email' => $d['email']],
                ['password' => Hash::make('password'), 'status' => 'active']
            );

            if (! $user->hasRole('terapeuta')) {
                $user->assignRole('terapeuta');
            }

            $terapeutas[$d['especialidad']] = Terapeuta::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres' => $d['nombres'],
                    'apellidos' => $d['apellidos'],
                    'fecha_nacimiento' => $d['fecha_nacimiento'],
                    'telefono' => $d['telefono'],
                    'correo' => $d['email'],
                    'especialidad_id' => Especialidad::where('nombre', $d['especialidad'])->value('id'),
                    'genero_id' => Genero::where('nombre', $d['genero'])->value('id'),
                    'experiencia' => $d['experiencia'],
                    'certificaciones' => $d['certificaciones'],
                    'cursos' => $d['cursos'],
                    'superior_id' => $d['superior_id'],
                ]
            );
        }

        return $terapeutas;
    }

    /** @return array<string,Encargado> indexado por una clave corta */
    private function crearEncargados(): array
    {
        // Cinco encargados para siete pacientes: dos de ellos tienen dos hijos
        // en terapia, que es el caso que hay que poder probar.
        $definicion = [
            'rosales' => [
                'email' => 'silvia.rosales@example.com',
                'nombres' => 'Silvia',
                'apellidos' => 'Rosales de León',
                'relacion' => 'Madre',
                'genero' => 'Femenino',
                'estado_civil' => 'Casado',
                'telefono' => '4412-3344',
                'direccion' => '5a calle 12-34 zona 10, Guatemala',
                'ocupacion' => 'Contadora',
                'fecha_nacimiento' => '1987-02-18',
            ],
            'batres' => [
                'email' => 'rodrigo.batres@example.com',
                'nombres' => 'Rodrigo',
                'apellidos' => 'Batres Coronado',
                'relacion' => 'Padre',
                'genero' => 'Masculino',
                'estado_civil' => 'Casado',
                'telefono' => '4423-5566',
                'direccion' => '18 avenida 3-21 zona 15, Guatemala',
                'ocupacion' => 'Ingeniero civil',
                'fecha_nacimiento' => '1984-09-30',
            ],
            'quinonez' => [
                'email' => 'lucrecia.quinonez@example.com',
                'nombres' => 'Lucrecia',
                'apellidos' => 'Quiñónez Marroquín',
                'relacion' => 'Madre',
                'genero' => 'Femenino',
                'estado_civil' => 'Soltero',
                'telefono' => '4434-7788',
                'direccion' => 'Calzada Roosevelt 22-15 zona 11, Mixco',
                'ocupacion' => 'Docente',
                'fecha_nacimiento' => '1991-06-07',
            ],
            'castellanos' => [
                'email' => 'hugo.castellanos@example.com',
                'nombres' => 'Hugo',
                'apellidos' => 'Castellanos Arriaga',
                'relacion' => 'Tutor',
                'genero' => 'Masculino',
                'estado_civil' => 'Divorciado',
                'telefono' => '4445-9911',
                'direccion' => '7a avenida 8-90 zona 9, Guatemala',
                'ocupacion' => 'Comerciante',
                'fecha_nacimiento' => '1979-12-14',
            ],
            'melgar' => [
                'email' => 'andrea.melgar@example.com',
                'nombres' => 'Andrea',
                'apellidos' => 'Melgar Sandoval',
                'relacion' => 'Madre',
                'genero' => 'Femenino',
                'estado_civil' => 'Unión libre',
                'telefono' => '4456-2233',
                'direccion' => '2a calle 5-67 zona 14, Guatemala',
                'ocupacion' => 'Diseñadora gráfica',
                'fecha_nacimiento' => '1993-04-25',
            ],
        ];

        $encargados = [];

        foreach ($definicion as $clave => $d) {
            $user = User::updateOrCreate(
                ['email' => $d['email']],
                ['password' => Hash::make('password'), 'status' => 'active']
            );

            if (! $user->hasRole('encargado')) {
                $user->assignRole('encargado');
            }

            $encargados[$clave] = Encargado::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nombres' => $d['nombres'],
                    'apellidos' => $d['apellidos'],
                    'fecha_nacimiento' => $d['fecha_nacimiento'],
                    'telefono' => $d['telefono'],
                    'correo' => $d['email'],
                    'direccion' => $d['direccion'],
                    'ocupacion' => $d['ocupacion'],
                    'relacion_paciente_id' => RelacionPaciente::where('nombre', $d['relacion'])->value('id'),
                    'genero_id' => Genero::where('nombre', $d['genero'])->value('id'),
                    'estado_civil_id' => EstadoCivil::where('nombre', $d['estado_civil'])->value('id'),
                ]
            );
        }

        return $encargados;
    }

    private function crearPacientes(array $terapeutas, array $encargados): void
    {
        $definicion = [
            [
                'nombres' => 'Mateo',
                'apellidos' => 'Rosales de León',
                'genero' => 'Masculino',
                'escolaridad' => 'Kinder',
                'encargado' => 'rosales',
                'fecha_nacimiento' => '2020-05-14',
                'terapeutas' => ['Lenguaje'],
                'motivo' => 'Retraso en la adquisición del lenguaje expresivo',
                'estado_expediente' => 'Activo',
                'modalidad' => 'Presencial',
            ],
            [
                // Hermana de Mateo: mismo encargado.
                'nombres' => 'Camila',
                'apellidos' => 'Rosales de León',
                'genero' => 'Femenino',
                'escolaridad' => '2do Primaria',
                'encargado' => 'rosales',
                'fecha_nacimiento' => '2017-08-02',
                'terapeutas' => ['Psicología', 'Terapia Ocupacional'],
                'motivo' => 'Dificultades de atención y concentración en clase',
                'estado_expediente' => 'Activo',
                'modalidad' => 'Presencial',
            ],
            [
                'nombres' => 'Sebastián',
                'apellidos' => 'Batres Coronado',
                'genero' => 'Masculino',
                'escolaridad' => 'Prekinder',
                'encargado' => 'batres',
                'fecha_nacimiento' => '2021-11-19',
                'terapeutas' => ['Terapia Ocupacional'],
                'motivo' => 'Evaluación por hipotonía y motricidad fina',
                'estado_expediente' => 'Activo',
                'modalidad' => 'Presencial',
            ],
            [
                // Hermano de Sebastián: mismo encargado.
                'nombres' => 'Emilia',
                'apellidos' => 'Batres Coronado',
                'genero' => 'Femenino',
                'escolaridad' => '4to Primaria',
                'encargado' => 'batres',
                'fecha_nacimiento' => '2015-03-08',
                'terapeutas' => ['Lenguaje', 'Psicología'],
                'motivo' => 'Tartamudez y ansiedad al hablar en público',
                'estado_expediente' => 'Activo',
                'modalidad' => 'Hibrido',
            ],
            [
                'nombres' => 'Joaquín',
                'apellidos' => 'Quiñónez Marroquín',
                'genero' => 'Masculino',
                'escolaridad' => '1ro Primaria',
                'encargado' => 'quinonez',
                'fecha_nacimiento' => '2018-10-27',
                'terapeutas' => ['Psicología'],
                'motivo' => 'Referido por el colegio para evaluación conductual',
                'estado_expediente' => 'Pendiente',
                'modalidad' => 'Presencial',
            ],
            [
                'nombres' => 'Isabella',
                'apellidos' => 'Castellanos Arriaga',
                'genero' => 'Femenino',
                'escolaridad' => '6to Primaria',
                'encargado' => 'castellanos',
                'fecha_nacimiento' => '2013-01-16',
                'terapeutas' => ['Psicología', 'Lenguaje'],
                'motivo' => 'Seguimiento de proceso de adaptación escolar',
                'estado_expediente' => 'Activo',
                'modalidad' => 'Virtual',
            ],
            [
                'nombres' => 'Santiago',
                'apellidos' => 'Melgar Sandoval',
                'genero' => 'Masculino',
                'escolaridad' => 'Preparatoria',
                'encargado' => 'melgar',
                'fecha_nacimiento' => '2019-07-04',
                'terapeutas' => ['Lenguaje', 'Terapia Ocupacional'],
                'motivo' => 'Evaluación integral del desarrollo',
                'estado_expediente' => 'Activo',
                'modalidad' => 'Presencial',
            ],
        ];

        foreach ($definicion as $d) {
            $encargado = $encargados[$d['encargado']];

            $paciente = Paciente::updateOrCreate(
                ['nombres' => $d['nombres'], 'apellidos' => $d['apellidos']],
                [
                    'escolaridad_id' => Escolaridad::where('nombre', $d['escolaridad'])->value('id'),
                    'genero_id' => Genero::where('nombre', $d['genero'])->value('id'),
                    'encargado_id' => $encargado->id,
                ]
            );

            // Además de pacientes.encargado_id se llena el pivote, que estaba
            // vacío aunque Encargado::pacientes() lo usa.
            $encargado->pacientes()->syncWithoutDetaching([$paciente->id]);

            // Terapeutas asignados (tabla paciente_terapeuta).
            $ids = collect($d['terapeutas'])
                ->map(fn($esp) => $terapeutas[$esp]->id)
                ->all();
            $paciente->terapeutas()->syncWithoutDetaching($ids);

            $expediente = Expediente::where('paciente_id', $paciente->id)->first();

            if (! $expediente) {
                Expediente::create([
                    'paciente_id' => $paciente->id,
                    'codigo' => Expediente::generarCodigoExpediente(),
                    'nombres' => $paciente->nombres,
                    'apellidos' => $paciente->apellidos,
                    // pacientes no guarda fecha_nacimiento; el expediente sí.
                    'fecha_nacimiento' => $d['fecha_nacimiento'],
                    'estado_expediente_id' => \App\Models\EstadoExpediente::where('nombre', $d['estado_expediente'])->value('id'),
                    'modalidad_id' => \App\Models\Modalidad::where('nombre', $d['modalidad'])->value('id'),
                    'motivo_consulta' => $d['motivo'],
                    'fecha_inicio' => now()->subDays(rand(15, 180))->toDateString(),
                    'consentimiento' => 1,
                    'observaciones' => 'Expediente de prueba.',
                ]);
            }
        }
    }
}
