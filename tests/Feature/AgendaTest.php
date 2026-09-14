<?php

namespace Tests\Feature;

use App\Models\Administrativo;
use App\Models\Cita;
use App\Models\Encargado;
use App\Models\EstadoCita;
use App\Models\Paciente;
use App\Models\Terapeuta;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgendaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        EstadoCita::create(['nombre' => 'Programada', 'activo' => 1]);
        EstadoCita::create(['nombre' => 'Cancelada', 'activo' => 1]);
    }

    /** Crea un usuario con el rol dado y su registro de persona asociado. */
    private function usuarioCon(string $rol): User
    {
        $user = User::create([
            'email' => $rol . '@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($rol);

        return $user;
    }

    private function terapeutaPara(User $user): Terapeuta
    {
        return Terapeuta::create([
            'user_id' => $user->id,
            'nombres' => 'Tera',
            'apellidos' => 'Peuta',
        ]);
    }

    private function pacienteDe(?Encargado $encargado = null): Paciente
    {
        return Paciente::create([
            'nombres' => 'Pac',
            'apellidos' => 'Iente',
            'encargado_id' => $encargado?->id,
        ]);
    }

    /* ---------- Acceso a la vista ---------- */

    public function test_un_usuario_sin_rol_no_entra_a_la_agenda(): void
    {
        $user = User::create(['email' => 'nadie@test.com', 'password' => bcrypt('password')]);

        $this->actingAs($user)->get('/agenda')->assertForbidden();
    }

    public function test_los_cinco_roles_con_ver_agenda_entran(): void
    {
        foreach (['administrador', 'coordinador', 'auxiliar', 'terapeuta', 'encargado'] as $rol) {
            $user = $this->usuarioCon($rol);

            $this->actingAs($user)->get('/agenda')->assertOk();
        }
    }

    /* ---------- Quién puede crear ---------- */

    public function test_la_terapeuta_no_puede_crear_citas(): void
    {
        $user = $this->usuarioCon('terapeuta');
        $this->terapeutaPara($user);

        $this->actingAs($user)
            ->post('/agenda', $this->datosDeCita())
            ->assertForbidden();

        $this->assertSame(0, Cita::count());
    }

    public function test_el_encargado_no_puede_crear_citas(): void
    {
        $user = $this->usuarioCon('encargado');

        $this->actingAs($user)
            ->post('/agenda', $this->datosDeCita())
            ->assertForbidden();

        $this->assertSame(0, Cita::count());
    }

    public function test_el_coordinador_si_puede_crear_citas(): void
    {
        $user = $this->usuarioCon('coordinador');

        $this->actingAs($user)
            ->post('/agenda', $this->datosDeCita())
            ->assertRedirect('/agenda');

        $this->assertSame(1, Cita::count());
    }

    public function test_el_auxiliar_puede_crear_una_cita_que_el_mismo_atiende(): void
    {
        $user = $this->usuarioCon('auxiliar');
        $auxiliar = Administrativo::create([
            'user_id' => $user->id,
            'nombres' => 'Aux',
            'apellidos' => 'Iliar',
        ]);

        $this->actingAs($user)
            ->post('/agenda', $this->datosDeCita([
                'atiende_tipo' => 'auxiliar',
                'atiende_id' => $auxiliar->id,
            ]))
            ->assertRedirect('/agenda');

        $cita = Cita::first();
        $this->assertSame(Administrativo::class, $cita->atendido_por_type);
        $this->assertSame($auxiliar->id, $cita->atendido_por_id);
    }

    public function test_el_auxiliar_no_puede_agendarle_a_otra_persona(): void
    {
        $user = $this->usuarioCon('auxiliar');
        Administrativo::create([
            'user_id' => $user->id,
            'nombres' => 'Aux',
            'apellidos' => 'Iliar',
        ]);

        $teraUser = User::create(['email' => 'ajena@test.com', 'password' => bcrypt('x')]);
        $ajena = $this->terapeutaPara($teraUser);

        // Manipular el formulario para agendarle a una terapeuta debe fallar.
        $this->actingAs($user)
            ->post('/agenda', $this->datosDeCita([
                'atiende_tipo' => 'terapeuta',
                'atiende_id' => $ajena->id,
            ]))
            ->assertSessionHasErrors('atiende_id');

        $this->assertSame(0, Cita::count());
    }

    public function test_al_auxiliar_solo_se_le_ofrece_a_si_mismo_en_el_catalogo(): void
    {
        $user = $this->usuarioCon('auxiliar');
        $auxiliar = Administrativo::create([
            'user_id' => $user->id,
            'nombres' => 'Aux',
            'apellidos' => 'Iliar',
        ]);

        // Hay otra persona que atiende, pero no debe aparecer en su lista.
        $teraUser = User::create(['email' => 'otra@test.com', 'password' => bcrypt('x')]);
        $this->terapeutaPara($teraUser);

        $props = $this->actingAs($user)->get('/agenda')->viewData('page')['props'];

        $this->assertTrue($props['permisos']['soloParaSiMismo']);
        $this->assertCount(1, $props['catalogos']['atienden']);
        $this->assertSame($auxiliar->id, $props['catalogos']['atienden'][0]['id']);
        $this->assertSame('auxiliar', $props['catalogos']['atienden'][0]['tipo']);
    }

    public function test_el_coordinador_no_esta_limitado_a_si_mismo(): void
    {
        $user = $this->usuarioCon('coordinador');

        $props = $this->actingAs($user)->get('/agenda')->viewData('page')['props'];

        $this->assertFalse($props['permisos']['soloParaSiMismo']);
    }

    public function test_un_auxiliar_que_tambien_es_coordinador_agenda_para_cualquiera(): void
    {
        $user = $this->usuarioCon('auxiliar');
        $user->assignRole('coordinador');
        Administrativo::create([
            'user_id' => $user->id,
            'nombres' => 'Aux',
            'apellidos' => 'Iliar',
        ]);

        $teraUser = User::create(['email' => 'ajena@test.com', 'password' => bcrypt('x')]);
        $ajena = $this->terapeutaPara($teraUser);

        // El rol más amplio manda.
        $this->actingAs($user)
            ->post('/agenda', $this->datosDeCita([
                'atiende_tipo' => 'terapeuta',
                'atiende_id' => $ajena->id,
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Cita::count());
    }

    /* ---------- Alcance de lo que cada rol ve ---------- */

    public function test_la_terapeuta_solo_ve_las_citas_que_atiende(): void
    {
        $user = $this->usuarioCon('terapeuta');
        $suya = $this->terapeutaPara($user);

        $otraUser = User::create(['email' => 'otra@test.com', 'password' => bcrypt('x')]);
        $otra = $this->terapeutaPara($otraUser);

        $paciente = $this->pacienteDe();
        $estado = EstadoCita::first();

        $this->crearCita($paciente, $suya, $estado, '09:00');
        $this->crearCita($paciente, $otra, $estado, '11:00');

        $this->assertSame(2, Cita::count());

        $respuesta = $this->actingAs($user)->get('/agenda');
        $citas = $respuesta->viewData('page')['props']['citas'];

        $this->assertCount(1, $citas);
        $this->assertSame('09:00', $citas[0]['extendedProps']['horaInicio']);
    }

    public function test_el_encargado_solo_ve_las_citas_de_sus_pacientes(): void
    {
        $user = $this->usuarioCon('encargado');
        $encargado = Encargado::create([
            'user_id' => $user->id,
            'nombres' => 'Enc',
            'apellidos' => 'Argado',
        ]);

        $miHijo = $this->pacienteDe($encargado);
        $ajeno = $this->pacienteDe();

        $teraUser = User::create(['email' => 't@test.com', 'password' => bcrypt('x')]);
        $tera = $this->terapeutaPara($teraUser);
        $estado = EstadoCita::first();

        $this->crearCita($miHijo, $tera, $estado, '09:00');
        $this->crearCita($ajeno, $tera, $estado, '11:00');

        $respuesta = $this->actingAs($user)->get('/agenda');
        $citas = $respuesta->viewData('page')['props']['citas'];

        $this->assertCount(1, $citas);
        $this->assertSame($miHijo->id, $citas[0]['extendedProps']['pacienteId']);
    }

    public function test_el_coordinador_ve_todas_las_citas(): void
    {
        $user = $this->usuarioCon('coordinador');

        $teraUser = User::create(['email' => 't@test.com', 'password' => bcrypt('x')]);
        $tera = $this->terapeutaPara($teraUser);
        $estado = EstadoCita::first();

        $this->crearCita($this->pacienteDe(), $tera, $estado, '09:00');
        $this->crearCita($this->pacienteDe(), $tera, $estado, '11:00');

        $respuesta = $this->actingAs($user)->get('/agenda');

        $this->assertCount(2, $respuesta->viewData('page')['props']['citas']);
    }

    /* ---------- Catálogos solo para quien agenda ---------- */

    public function test_los_catalogos_no_se_envian_a_quien_no_puede_agendar(): void
    {
        $user = $this->usuarioCon('terapeuta');
        $this->terapeutaPara($user);

        $props = $this->actingAs($user)->get('/agenda')->viewData('page')['props'];

        $this->assertNull($props['catalogos']);
        $this->assertFalse($props['permisos']['agendar']);
    }

    public function test_los_catalogos_si_se_envian_a_quien_agenda(): void
    {
        $user = $this->usuarioCon('coordinador');

        $props = $this->actingAs($user)->get('/agenda')->viewData('page')['props'];

        $this->assertNotNull($props['catalogos']);
        $this->assertTrue($props['permisos']['agendar']);
        $this->assertTrue($props['permisos']['verOcupacion']);
    }

    /* ---------- Solapamiento ---------- */

    public function test_no_se_puede_encimar_una_cita_de_la_misma_persona(): void
    {
        $user = $this->usuarioCon('coordinador');

        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
        ]))->assertRedirect('/agenda');

        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'hora_inicio' => '09:30',
            'hora_fin' => '10:30',
        ]))->assertSessionHasErrors('hora_inicio');

        $this->assertSame(1, Cita::count());
    }

    public function test_una_cita_pegada_a_otra_si_se_permite(): void
    {
        $user = $this->usuarioCon('coordinador');

        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
        ]))->assertRedirect('/agenda');

        // Empieza justo cuando termina la anterior: no es un choque.
        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'hora_inicio' => '10:00',
            'hora_fin' => '11:00',
        ]))->assertSessionHasNoErrors();

        $this->assertSame(2, Cita::count());
    }

    public function test_dos_personas_distintas_si_pueden_tener_la_misma_hora(): void
    {
        $user = $this->usuarioCon('coordinador');

        $unaUser = User::create(['email' => 'una@test.com', 'password' => bcrypt('x')]);
        $una = $this->terapeutaPara($unaUser);
        $otraUser = User::create(['email' => 'otra@test.com', 'password' => bcrypt('x')]);
        $otra = $this->terapeutaPara($otraUser);

        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'atiende_id' => $una->id,
        ]))->assertRedirect('/agenda');

        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'atiende_id' => $otra->id,
        ]))->assertSessionHasNoErrors();

        $this->assertSame(2, Cita::count());
    }

    public function test_una_cita_cancelada_no_bloquea_el_horario(): void
    {
        $user = $this->usuarioCon('coordinador');
        $cancelada = EstadoCita::where('nombre', 'Cancelada')->first();

        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'estado_cita_id' => $cancelada->id,
        ]))->assertRedirect('/agenda');

        $this->actingAs($user)->post('/agenda', $this->datosDeCita())
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Cita::count());
    }

    public function test_editar_una_cita_sin_moverla_no_choca_consigo_misma(): void
    {
        $user = $this->usuarioCon('coordinador');

        $this->actingAs($user)->post('/agenda', $this->datosDeCita())->assertRedirect('/agenda');
        $cita = Cita::first();

        $this->actingAs($user)
            ->put("/agenda/{$cita->id}", $this->datosDeCita(['precio_aplicado' => 999]))
            ->assertSessionHasNoErrors();

        $this->assertEquals(999, Cita::first()->precio_aplicado);
    }

    /* ---------- Validación ---------- */

    public function test_la_hora_fin_debe_ser_posterior_a_la_hora_inicio(): void
    {
        $user = $this->usuarioCon('coordinador');

        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'hora_inicio' => '11:00',
            'hora_fin' => '10:00',
        ]))->assertSessionHasErrors('hora_fin');

        $this->assertSame(0, Cita::count());
    }

    public function test_no_se_acepta_un_tipo_de_persona_desconocido(): void
    {
        $user = $this->usuarioCon('coordinador');

        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'atiende_tipo' => 'paciente',
        ]))->assertSessionHasErrors('atiende_tipo');

        $this->assertSame(0, Cita::count());
    }

    public function test_no_se_acepta_una_persona_que_no_existe(): void
    {
        $user = $this->usuarioCon('coordinador');

        $this->actingAs($user)->post('/agenda', $this->datosDeCita([
            'atiende_id' => 9999,
        ]))->assertSessionHasErrors('atiende_id');

        $this->assertSame(0, Cita::count());
    }

    /* ---------- Ocupación de personal ---------- */

    public function test_solo_administrador_y_coordinador_ven_la_ocupacion(): void
    {
        $permitidos = ['administrador', 'coordinador'];
        $negados = ['terapeuta', 'auxiliar', 'encargado'];

        foreach ($permitidos as $rol) {
            $this->actingAs($this->usuarioCon($rol))->get('/indicadores/ocupacion')->assertOk();
        }

        foreach ($negados as $rol) {
            $this->actingAs($this->usuarioCon($rol))->get('/indicadores/ocupacion')->assertForbidden();
        }
    }

    public function test_la_matriz_cuenta_las_citas_de_cada_dia(): void
    {
        $coord = $this->usuarioCon('coordinador');

        $teraUser = User::create(['email' => 't@test.com', 'password' => bcrypt('x')]);
        $tera = $this->terapeutaPara($teraUser);
        $paciente = $this->pacienteDe();
        $estado = EstadoCita::first();

        // Dos citas el lunes y una el miércoles de la semana actual.
        $lunes = now()->startOfWeek();
        $this->crearCitaEn($paciente, $tera, $estado, $lunes->toDateString(), '09:00', '10:00');
        $this->crearCitaEn($paciente, $tera, $estado, $lunes->toDateString(), '11:00', '12:00');
        $this->crearCitaEn($paciente, $tera, $estado, $lunes->copy()->addDays(2)->toDateString(), '09:00', '10:30');

        $props = $this->actingAs($coord)->get('/indicadores/ocupacion')->viewData('page')['props'];

        $fila = collect($props['personal'])->firstWhere('clave', 'terapeuta:' . $tera->id);

        $this->assertSame(2, $fila['porDia'][0]['citas'], 'lunes');
        $this->assertSame(0, $fila['porDia'][1]['citas'], 'martes');
        $this->assertSame(1, $fila['porDia'][2]['citas'], 'miércoles');

        $this->assertSame(3, $fila['totales']['citas']);
        // 60 + 60 + 90 minutos
        $this->assertSame(210, $fila['totales']['minutos']);
        $this->assertSame(70, $fila['totales']['promedioMinutos']);
        $this->assertSame(90, $fila['totales']['maxMinutos']);
        $this->assertSame(2, $fila['totales']['diasConCitas']);
    }

    public function test_una_cita_cancelada_no_ocupa_agenda(): void
    {
        $coord = $this->usuarioCon('coordinador');

        $teraUser = User::create(['email' => 't@test.com', 'password' => bcrypt('x')]);
        $tera = $this->terapeutaPara($teraUser);
        $paciente = $this->pacienteDe();

        $lunes = now()->startOfWeek()->toDateString();
        $this->crearCitaEn($paciente, $tera, EstadoCita::first(), $lunes, '09:00', '10:00');
        $this->crearCitaEn(
            $paciente, $tera,
            EstadoCita::where('nombre', 'Cancelada')->first(),
            $lunes, '11:00', '12:00'
        );

        $props = $this->actingAs($coord)->get('/indicadores/ocupacion')->viewData('page')['props'];
        $fila = collect($props['personal'])->firstWhere('clave', 'terapeuta:' . $tera->id);

        $this->assertSame(1, $fila['totales']['citas'], 'la cancelada no cuenta');
        $this->assertSame(60, $fila['totales']['minutos']);
    }

    public function test_la_ocupacion_incluye_a_los_auxiliares(): void
    {
        $coord = $this->usuarioCon('coordinador');

        $auxUser = User::create(['email' => 'aux@test.com', 'password' => bcrypt('x')]);
        $cargo = \App\Models\Cargo::create(['nombre' => 'Auxiliar', 'activo' => 1]);
        $auxiliar = Administrativo::create([
            'user_id' => $auxUser->id,
            'nombres' => 'Aux',
            'apellidos' => 'Iliar',
            'cargo_id' => $cargo->id,
        ]);

        // Un administrativo con otro cargo no debe aparecer.
        $otroCargo = \App\Models\Cargo::create(['nombre' => 'Coordinador', 'activo' => 1]);
        $otroUser = User::create(['email' => 'otro@test.com', 'password' => bcrypt('x')]);
        Administrativo::create([
            'user_id' => $otroUser->id,
            'nombres' => 'Otro',
            'apellidos' => 'Cargo',
            'cargo_id' => $otroCargo->id,
        ]);

        $props = $this->actingAs($coord)->get('/indicadores/ocupacion')->viewData('page')['props'];
        $claves = collect($props['personal'])->pluck('clave');

        $this->assertTrue($claves->contains('auxiliar:' . $auxiliar->id));
        $this->assertCount(1, $claves->filter(fn($c) => str_starts_with($c, 'auxiliar:')));
    }

    public function test_la_semana_se_puede_navegar(): void
    {
        $coord = $this->usuarioCon('coordinador');

        $props = $this->actingAs($coord)->get('/indicadores/ocupacion')->viewData('page')['props'];
        $this->assertTrue($props['semana']['esActual']);

        $anterior = $props['semana']['anterior'];
        $props2 = $this->actingAs($coord)
            ->get('/indicadores/ocupacion?semana=' . $anterior)
            ->viewData('page')['props'];

        $this->assertFalse($props2['semana']['esActual']);
        $this->assertSame($anterior, $props2['semana']['desde']);
        $this->assertCount(7, $props2['dias']);
    }

    public function test_el_desglose_trae_las_citas_de_la_semana(): void
    {
        $coord = $this->usuarioCon('coordinador');

        $teraUser = User::create(['email' => 't@test.com', 'password' => bcrypt('x')]);
        $tera = $this->terapeutaPara($teraUser);
        $paciente = $this->pacienteDe();

        $lunes = now()->startOfWeek()->toDateString();
        $this->crearCitaEn($paciente, $tera, EstadoCita::first(), $lunes, '09:00', '10:30');

        $props = $this->actingAs($coord)->get('/indicadores/ocupacion')->viewData('page')['props'];
        $fila = collect($props['personal'])->firstWhere('clave', 'terapeuta:' . $tera->id);

        $this->assertCount(1, $fila['citas']);
        $cita = $fila['citas'][0];

        $this->assertSame('09:00', $cita['horaInicio']);
        $this->assertSame('10:30', $cita['horaFin']);
        $this->assertSame(90, $cita['minutos']);
        $this->assertSame($paciente->nombre_completo, $cita['paciente']);
    }

    /* ---------- Helpers ---------- */

    private function datosDeCita(array $reemplazos = []): array
    {
        $paciente = Paciente::first() ?? $this->pacienteDe();

        $terapeuta = Terapeuta::first();
        if (! $terapeuta) {
            $u = User::create(['email' => 'base@test.com', 'password' => bcrypt('x')]);
            $terapeuta = $this->terapeutaPara($u);
        }

        return array_merge([
            'paciente_id' => $paciente->id,
            'atiende_tipo' => 'terapeuta',
            'atiende_id' => $terapeuta->id,
            'estado_cita_id' => EstadoCita::first()->id,
            'fecha' => '2026-10-05',
            'hora_inicio' => '09:00',
            'hora_fin' => '10:00',
        ], $reemplazos);
    }

    private function crearCita(Paciente $paciente, Terapeuta $tera, EstadoCita $estado, string $hora): Cita
    {
        return Cita::create([
            'paciente_id' => $paciente->id,
            'atendido_por_type' => $tera->getMorphClass(),
            'atendido_por_id' => $tera->id,
            'estado_cita_id' => $estado->id,
            'fecha' => now()->toDateString(),
            'hora_inicio' => $hora . ':00',
            'hora_fin' => (((int) substr($hora, 0, 2)) + 1) . ':00:00',
        ]);
    }

    /** Igual que crearCita pero con fecha y rango horario explícitos. */
    private function crearCitaEn(
        Paciente $paciente,
        Terapeuta $tera,
        EstadoCita $estado,
        string $fecha,
        string $inicio,
        string $fin
    ): Cita {
        return Cita::create([
            'paciente_id' => $paciente->id,
            'atendido_por_type' => $tera->getMorphClass(),
            'atendido_por_id' => $tera->id,
            'estado_cita_id' => $estado->id,
            'fecha' => $fecha,
            'hora_inicio' => $inicio . ':00',
            'hora_fin' => $fin . ':00',
        ]);
    }
}
