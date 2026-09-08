<?php

namespace Tests\Feature;

use App\Models\Anamnesis;
use App\Models\Criterio;
use App\Models\Diagnostico;
use App\Models\Expediente;
use App\Models\Paciente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndicadoresTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function usuarioCon(string $rol): User
    {
        $user = User::create([
            'email' => $rol . '@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($rol);

        return $user;
    }

    private function criterio(string $modulo, string $area, int $numero): Criterio
    {
        return Criterio::create([
            'modulo' => $modulo,
            'area' => $area,
            'numero' => $numero,
            'descripcion' => "Criterio $numero de $area",
        ]);
    }

    /**
     * Arma un expediente con su anamnesis y diagnósticos.
     *
     * @param array<int,int> $respuestas criterio_id => respuesta (1|2|3)
     * @param array<int,string> $diagnosticos nombres
     */
    private function expedienteCon(string $nombre, array $respuestas, array $diagnosticos): Expediente
    {
        $paciente = Paciente::create(['nombres' => $nombre, 'apellidos' => 'Prueba']);

        $anamnesis = Anamnesis::create(['observaciones' => null]);

        foreach ($respuestas as $criterioId => $respuesta) {
            $anamnesis->items()->create([
                'criterio_id' => $criterioId,
                'respuesta' => $respuesta,
            ]);
        }

        $expediente = Expediente::create([
            'paciente_id' => $paciente->id,
            'anamnesis_id' => $anamnesis->id,
            'codigo' => Expediente::generarCodigoExpediente(),
            'nombres' => $paciente->nombres,
            'apellidos' => $paciente->apellidos,
            'consentimiento' => 1,
            'fecha_inicio' => now(),
        ]);

        $ids = collect($diagnosticos)
            ->map(fn($n) => Diagnostico::firstOrCreate(['nombre' => $n], ['activo' => 1])->id)
            ->all();

        $expediente->diagnosticos()->sync($ids);

        return $expediente;
    }

    private function props(User $user, string $query = '')
    {
        return $this->actingAs($user)
            ->get('/indicadores' . $query)
            ->viewData('page')['props'];
    }

    /* ---------- Acceso ---------- */

    public function test_solo_administrador_y_coordinador_entran(): void
    {
        foreach (['administrador', 'coordinador'] as $rol) {
            $this->actingAs($this->usuarioCon($rol))->get('/indicadores')->assertOk();
        }

        foreach (['terapeuta', 'auxiliar', 'encargado', 'pruebas'] as $rol) {
            $this->actingAs($this->usuarioCon($rol))->get('/indicadores')->assertForbidden();
        }
    }

    /* ---------- Armado del grafo ---------- */

    public function test_amarra_el_diagnostico_con_el_area_deficiente(): void
    {
        $c1 = $this->criterio('Evaluación Cognitiva', 'Atención', 1);
        $c2 = $this->criterio('Evaluación Cognitiva', 'Memoria', 1);

        // Atención sale en Observación (1); Memoria, Adecuado (3).
        $this->expedienteCon('Ana', [$c1->id => 1, $c2->id => 3], ['TDAH']);

        $props = $this->props($this->usuarioCon('coordinador'));
        $nodos = collect($props['grafo']['nodos']);

        $this->assertCount(1, $nodos->where('tipo', 'diagnostico'));
        $this->assertSame('TDAH', $nodos->firstWhere('tipo', 'diagnostico')['etiqueta']);

        // Solo Atención debe aparecer: Memoria salió Adecuado.
        $areas = $nodos->where('tipo', 'area')->pluck('etiqueta');
        $this->assertCount(1, $areas);
        $this->assertSame('Atención', $areas->first());

        $this->assertCount(1, $props['grafo']['aristas']);
    }

    public function test_un_criterio_adecuado_no_genera_vinculo(): void
    {
        $c = $this->criterio('Evaluación Cognitiva', 'Atención', 1);
        $this->expedienteCon('Ana', [$c->id => 3], ['TDAH']);

        $props = $this->props($this->usuarioCon('coordinador'));

        $this->assertSame(0, $props['resumen']['vinculos']);
        $this->assertEmpty($props['grafo']['aristas']);
    }

    public function test_el_filtro_amplio_suma_lo_que_esta_en_desarrollo(): void
    {
        $c1 = $this->criterio('Evaluación Cognitiva', 'Atención', 1);
        $c2 = $this->criterio('Evaluación Cognitiva', 'Memoria', 1);

        $this->expedienteCon('Ana', [$c1->id => 1, $c2->id => 2], ['TDAH']);

        $coord = $this->usuarioCon('coordinador');

        $soloObservacion = $this->props($coord);
        $this->assertSame(1, $soloObservacion['resumen']['areas']);
        $this->assertSame('observacion', $soloObservacion['nivel']);

        $ambos = $this->props($coord, '?nivel=ambos');
        $this->assertSame(2, $ambos['resumen']['areas'], 'Memoria entra con el filtro amplio');
        $this->assertSame('ambos', $ambos['nivel']);
    }

    public function test_el_peso_cuenta_en_cuantos_expedientes_coinciden(): void
    {
        $c = $this->criterio('Evaluación Cognitiva', 'Atención', 1);

        $this->expedienteCon('Ana', [$c->id => 1], ['TDAH']);
        $this->expedienteCon('Beto', [$c->id => 1], ['TDAH']);
        $this->expedienteCon('Cira', [$c->id => 1], ['TEA']);

        $props = $this->props($this->usuarioCon('coordinador'));
        $aristas = collect($props['grafo']['aristas']);

        $nodos = collect($props['grafo']['nodos']);
        $tdah = $nodos->firstWhere('etiqueta', 'TDAH');
        $tea = $nodos->firstWhere('etiqueta', 'TEA');

        $this->assertSame(2, $aristas->firstWhere('origen', $tdah['id'])['peso']);
        $this->assertSame(1, $aristas->firstWhere('origen', $tea['id'])['peso']);

        // El nodo de área acumula los tres expedientes.
        $this->assertSame(3, $nodos->firstWhere('tipo', 'area')['expedientes']);
    }

    public function test_un_expediente_con_dos_diagnosticos_vincula_ambos(): void
    {
        $c = $this->criterio('Evaluación Socioemocional', 'Ansiedad y miedos', 1);

        $this->expedienteCon('Ana', [$c->id => 1], ['TDAH', 'Trastorno de Ansiedad Infantil']);

        $props = $this->props($this->usuarioCon('coordinador'));

        $this->assertSame(2, $props['resumen']['diagnosticos']);
        $this->assertSame(1, $props['resumen']['areas']);
        // Cada diagnóstico se liga al mismo área.
        $this->assertSame(2, $props['resumen']['vinculos']);
    }

    public function test_el_area_lista_los_criterios_que_fallaron(): void
    {
        $c1 = $this->criterio('Evaluación Cognitiva', 'Atención', 1);
        $c2 = $this->criterio('Evaluación Cognitiva', 'Atención', 2);
        $c3 = $this->criterio('Evaluación Cognitiva', 'Atención', 3);

        // Dos fallan, uno está adecuado.
        $this->expedienteCon('Ana', [$c1->id => 1, $c2->id => 1, $c3->id => 3], ['TDAH']);

        $props = $this->props($this->usuarioCon('coordinador'));
        $area = collect($props['grafo']['nodos'])->firstWhere('tipo', 'area');

        $this->assertCount(2, $area['criterios']);
        $this->assertSame('Evaluación Cognitiva', $area['modulo']);
        $this->assertSame('Ana Prueba', $area['criterios'][0]['pacientes'][0]['nombre']);
        $this->assertSame(1, $area['criterios'][0]['pacientes'][0]['respuesta']);
    }

    /* ---------- Integridad ---------- */

    public function test_ninguna_arista_apunta_a_un_nodo_inexistente(): void
    {
        $c1 = $this->criterio('Evaluación Cognitiva', 'Atención', 1);
        $c2 = $this->criterio('Evaluación Socioemocional', 'Conducta en casa', 1);

        $this->expedienteCon('Ana', [$c1->id => 1, $c2->id => 1], ['TDAH', 'Trastorno de Conducta']);
        $this->expedienteCon('Beto', [$c1->id => 1], ['TDAH']);

        $props = $this->props($this->usuarioCon('coordinador'));

        $ids = collect($props['grafo']['nodos'])->pluck('id');
        $nodosSueltos = collect($props['grafo']['nodos'])->reject(
            fn($n) => collect($props['grafo']['aristas'])
                ->contains(fn($a) => $a['origen'] === $n['id'] || $a['destino'] === $n['id'])
        );

        foreach ($props['grafo']['aristas'] as $arista) {
            $this->assertTrue($ids->contains($arista['origen']));
            $this->assertTrue($ids->contains($arista['destino']));
        }

        $this->assertCount(0, $ids->duplicates(), 'ids de nodo únicos');
        $this->assertCount(0, collect($props['grafo']['aristas'])->pluck('id')->duplicates());
        $this->assertCount(0, $nodosSueltos, 'ningún nodo queda sin conexión');
    }

    public function test_un_expediente_sin_diagnostico_no_entra(): void
    {
        $c = $this->criterio('Evaluación Cognitiva', 'Atención', 1);

        // Con área deficiente pero sin diagnóstico: no hay nada que amarrar.
        $this->expedienteCon('Ana', [$c->id => 1], []);

        $props = $this->props($this->usuarioCon('coordinador'));

        $this->assertSame(0, $props['resumen']['expedientes']);
        $this->assertEmpty($props['grafo']['nodos']);
    }

    public function test_sin_datos_la_vista_carga_vacia(): void
    {
        $props = $this->props($this->usuarioCon('administrador'));

        $this->assertEmpty($props['grafo']['nodos']);
        $this->assertEmpty($props['grafo']['aristas']);
        $this->assertSame(0, $props['resumen']['expedientes']);
    }
}
