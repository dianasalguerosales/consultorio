<?php

namespace App\Http\Controllers;

use App\Models\AsignacionPrograma;
use App\Models\Cita;
use App\Models\Pago;
use App\Pagos\PagoDePaquete;
use App\Pagos\Quincena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Cobro de las sesiones ya recibidas.
 *
 * Salen todas las citas del período, con el estado en que está cada una: un
 * programa se cobra por adelantado, así que esperar a que la sesión se dé
 * dejaría fuera lo que hay que cobrar. Las que nadie ha pagado no tienen fila
 * en `pagos`; se muestran como pendientes.
 */
class PagoController extends Controller
{
    public function index(Request $request)
    {
        // El rango de sesión es la base: acota qué citas se traen. Los filtros
        // de pago recortan sobre eso, no lo amplían.
        $rango = Quincena::desdeFiltro($request->query('desde'), $request->query('hasta'));

        $filtros = $this->filtrosDePago($request);

        $citas = Cita::query()
            ->enRango($rango->inicio(), $rango->fin())
            ->with(['paciente:id,nombres,apellidos,genero', 'servicio:id,nombre', 'atendidoPor', 'estadoCita:id,nombre'])
            ->when(
                $filtros['pago_desde'] || $filtros['pago_hasta'] || $filtros['metodo'],
                // Van en un solo whereHas para que las tres condiciones caigan
                // sobre el mismo pago y no sobre pagos distintos de la cita.
                fn ($q) => $q->whereHas('pago', fn ($p) => $p
                    ->when($filtros['pago_desde'], fn ($p) => $p->whereDate('fecha', '>=', $filtros['pago_desde']))
                    ->when($filtros['pago_hasta'], fn ($p) => $p->whereDate('fecha', '<=', $filtros['pago_hasta']))
                    ->when($filtros['metodo'], fn ($p) => $p->where('metodo', $filtros['metodo'])))
            )
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        // El nombre de quien registró vive en terapeuta/administrativo/encargado,
        // no en `users`: por eso se cargan las tres junto al usuario.
        // El nombre de quien registró o autorizó vive en terapeuta /
        // administrativo / encargado, no en `users`; y los roles hacen falta
        // para saber si el pago necesitaba autorización.
        $personas = ['terapeuta', 'administrativo', 'encargado'];

        $pagos = Pago::whereIn('cita_id', $citas->pluck('id'))
            ->with([
                'registradoPor' => fn ($u) => $u->with($personas)->with('roles:id,name'),
                'autorizadoPor' => fn ($u) => $u->with($personas),
            ])
            ->get()
            ->keyBy('cita_id');

        $filas = $citas->map(fn (Cita $cita) => $this->fila($cita, $pagos->get($cita->id)));

        return Inertia::render('Pagos', [
            'filas' => $filas,
            // Los paquetes con saldo: el cliente paga el programa completo, no
            // sesión por sesión.
            'paquetes' => $this->paquetesPorCobrar($citas),
            'rango' => $rango->paraLaVista(),
            'filtros' => $filtros,
            'metodos' => Pago::METODOS,
            'totales' => $this->totales($filas),
            'puedeRegistrar' => $request->user()->hasAnyRole(['administrador', 'coordinador', 'auxiliar']),
            // Autoriza quien no necesita que lo autoricen.
            'puedeAutorizar' => $request->user()->hasAnyRole(Pago::ROLES_SIN_AUTORIZACION),
        ]);
    }

    /**
     * Filtros que miran la fila de `pagos`: cuando se usa alguno solo quedan
     * las citas ya cobradas, porque una cita pendiente no tiene ni fecha de
     * pago ni método con qué compararse.
     *
     * El método se valida contra Pago::METODOS: uno inventado en la URL dejaría
     * la tabla vacía sin explicar por qué.
     */
    private function filtrosDePago(Request $request): array
    {
        $metodo = $request->query('metodo');

        return [
            'pago_desde' => $request->query('pago_desde') ?: null,
            'pago_hasta' => $request->query('pago_hasta') ?: null,
            'metodo' => in_array($metodo, Pago::METODOS, true) ? $metodo : null,
        ];
    }

    /**
     * Cobra un paquete completo y reparte el monto entre sus citas.
     *
     * Es lo que hace el mostrador: el cliente paga el programa. Adentro queda
     * un pago por cita, con el mismo método, fecha y documento, para que la
     * tabla y el cierre de mes no tengan que saber qué es un paquete.
     */
    public function pagarPaquete(Request $request, AsignacionPrograma $asignacion)
    {
        $pendientes = PagoDePaquete::pendientes($asignacion);

        if ($pendientes->isEmpty()) {
            throw ValidationException::withMessages([
                'monto' => 'Este paquete ya no tiene citas por cobrar.',
            ]);
        }

        $saldo = PagoDePaquete::saldo($asignacion);

        $datos = $request->validate([
            // No se cobra de más: el tope es lo que falta del paquete.
            'monto' => "required|numeric|min:0.01|max:{$saldo}",
            'metodo' => 'required|in:' . implode(',', Pago::METODOS),
            'numero_autorizacion' => 'nullable|string|max:60',
            'fecha' => 'required|date',
        ], [
            'monto.max' => "Del paquete solo falta cobrar Q" . number_format($saldo, 2) . '.',
        ]);

        $montos = PagoDePaquete::repartir($pendientes, (float) $datos['monto']);

        // O quedan cobradas todas las citas del paquete o ninguna: un cobro a
        // medias dejaría al cliente pagando un programa incompleto.
        DB::transaction(function () use ($pendientes, $montos, $datos, $asignacion, $request) {
            foreach ($pendientes as $i => $cita) {
                if ($montos[$i] <= 0) {
                    continue;
                }

                // Un segundo abono completa lo que la cita ya tenía, no lo pisa.
                $acumulado = round((float) ($cita->pago?->monto ?? 0) + $montos[$i], 2);

                Pago::updateOrCreate(
                    ['cita_id' => $cita->id],
                    [
                        'paciente_id' => $asignacion->paciente_id,
                        'programa_id' => $asignacion->programa_id,
                        'monto' => $acumulado,
                        'metodo' => $datos['metodo'],
                        'numero_autorizacion' => $datos['numero_autorizacion'] ?? null,
                        'fecha' => $datos['fecha'],
                        'estado' => Pago::estadoSegun($acumulado, (float) $cita->precio_aplicado),
                        'registrado_por' => $request->user()->id,

                        // Cambió el monto: el visto bueno anterior ya no aplica.
                        'autorizado_por' => null,
                        'autorizado_en' => null,
                    ]
                );
            }
        });

        return back()->with(
            'success',
            'Paquete cobrado: Q' . number_format((float) $datos['monto'], 2)
                . ' repartidos entre ' . count(array_filter($montos, fn($m) => $m > 0)) . ' citas.'
        );
    }

    /**
     * Autoriza de una vez todos los cobros del paquete.
     *
     * Si no, un paquete de veinte citas que registró un auxiliar pediría veinte
     * autorizaciones para respaldar un solo cobro de mostrador.
     */
    public function autorizarPaquete(Request $request, AsignacionPrograma $asignacion)
    {
        $pagos = Pago::whereIn('cita_id', $asignacion->citas()->select('id'))
            ->whereNull('autorizado_por')
            ->with('registradoPor.roles')
            ->get()
            ->filter(fn(Pago $p) => $p->requiereAutorizacion());

        abort_unless($pagos->isNotEmpty(), 422, 'Este paquete no tiene cobros esperando autorización.');

        Pago::whereIn('id', $pagos->pluck('id'))->update([
            'autorizado_por' => $request->user()->id,
            'autorizado_en' => now(),
        ]);

        return back()->with('success', "Se autorizaron {$pagos->count()} cobros del paquete.");
    }

    /** Deshace el cobro completo de un paquete. */
    public function anularPaquete(AsignacionPrograma $asignacion)
    {
        $anulados = Pago::whereIn('cita_id', $asignacion->citas()->select('id'))->delete();

        abort_unless($anulados, 422, 'Este paquete no tiene cobros que anular.');

        return back()->with('success', "Se anularon {$anulados} cobros. El paquete vuelve a quedar pendiente.");
    }

    /** Registra o corrige el pago de una cita. */
    public function store(Request $request, Cita $cita)
    {
        $datos = $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo' => 'required|in:' . implode(',', Pago::METODOS),
            'numero_autorizacion' => 'nullable|string|max:60',
            'fecha' => 'required|date',
        ]);

        Pago::updateOrCreate(
            ['cita_id' => $cita->id],
            [
                'paciente_id' => $cita->paciente_id,
                'programa_id' => $cita->programa_id,
                'monto' => $datos['monto'],
                'metodo' => $datos['metodo'],
                'numero_autorizacion' => $datos['numero_autorizacion'] ?? null,
                'fecha' => $datos['fecha'],
                'estado' => Pago::estadoSegun((float) $datos['monto'], (float) $cita->precio_aplicado),
                'registrado_por' => $request->user()->id,

                // Corregir un pago tira abajo el visto bueno anterior: se
                // autorizó un monto, no la fila. Si vuelve a hacer falta, hay
                // que autorizarlo de nuevo.
                'autorizado_por' => null,
                'autorizado_en' => null,
            ]
        );

        return back()->with('success', 'Pago registrado.');
    }

    /**
     * Da el visto bueno a un cobro que registró un auxiliar.
     *
     * No se toca el monto ni el estado del pago: lo único que cambia es quién
     * respalda el cobro.
     */
    public function autorizar(Request $request, Pago $pago)
    {
        abort_unless(
            $pago->requiereAutorizacion(),
            422,
            'Este pago no necesita autorización: no lo registró un auxiliar.'
        );

        $pago->update([
            'autorizado_por' => $request->user()->id,
            'autorizado_en' => now(),
        ]);

        return back()->with('success', 'Pago autorizado.');
    }

    /** Deshace un cobro mal ingresado: la cita vuelve a pendiente de pago. */
    public function destroy(Pago $pago)
    {
        $pago->delete();

        return back()->with('success', 'El pago se anuló y la cita quedó pendiente.');
    }

    /* ---------- Armado de la tabla ---------- */

    private function fila(Cita $cita, ?Pago $pago): array
    {
        return [
            'cita_id' => $cita->id,
            'pago_id' => $pago?->id,
            'fecha' => $cita->fecha->toDateString(),
            'hora' => substr($cita->hora_inicio, 0, 5),
            'paciente' => trim("{$cita->paciente?->nombres} {$cita->paciente?->apellidos}"),
            'genero' => $cita->paciente?->genero,
            'servicio' => $cita->servicio?->nombre ?? 'Sin servicio',
            'atiende' => $cita->atendidoPor?->nombre_completo ?? 'Sin asignar',
            'precio' => (float) $cita->precio_aplicado,
            'estado_cita' => $cita->estadoCita?->nombre ?? 'Sin estado',
            'cancelada' => $cita->estadoCita?->nombre === 'Cancelada',
            'estado' => $pago?->estado ?? Pago::PENDIENTE,
            'monto' => $pago ? (float) $pago->monto : null,
            'metodo' => $pago?->metodo,
            'numero_autorizacion' => $pago?->numero_autorizacion,
            'fecha_pago' => $pago?->fecha?->toDateString(),
            'registro' => $pago?->registradoPor?->nombre_completo,
            // null = el pago no necesita que nadie lo autorice.
            'requiere_autorizacion' => (bool) $pago?->requiereAutorizacion(),
            'autoriza' => $pago?->autorizadoPor?->nombre_completo,
            'autorizado_en' => $pago?->autorizado_en?->format('d/m/Y H:i'),
        ];
    }

    /**
     * Los paquetes que tocan las citas del período, con su estado de cobro.
     *
     * Se sacan de las citas que ya están en pantalla y no de una consulta
     * aparte: el panel tiene que hablar del mismo período que la tabla.
     */
    private function paquetesPorCobrar($citas): array
    {
        $ids = $citas->pluck('asignacion_programa_id')->filter()->unique();

        if ($ids->isEmpty()) {
            return [];
        }

        return AsignacionPrograma::with(['paciente:id,nombres,apellidos,genero', 'programa:id,nombre', 'servicio:id,nombre'])
            ->whereIn('id', $ids)
            ->get()
            ->map(function (AsignacionPrograma $a) {
                $pendientes = PagoDePaquete::pendientes($a);

                return [
                    'id' => $a->id,
                    'paciente' => $a->paciente?->nombre_completo,
                    'genero' => $a->paciente?->genero,
                    'programa' => $a->programa?->nombre ?? 'Sin programa',
                    'servicio' => $a->servicio?->nombre ?? 'Sin servicio',
                    'precio' => (float) $a->precio,
                    'citas' => $a->citas()->count(),
                    'por_cobrar' => $pendientes->count(),
                    'esperado' => PagoDePaquete::esperado($a),
                    'cobrado' => PagoDePaquete::cobrado($a),
                    'saldo' => PagoDePaquete::saldo($a),
                    // Cuánto le tocaría a cada cita si se paga el saldo completo.
                    'por_cita' => $a->cantidad_citas > 0
                        ? round((float) $a->precio / $a->cantidad_citas, 2)
                        : 0,
                ];
            })
            ->sortBy('paciente')
            ->values()
            ->all();
    }

    /** Lo que se espera cobrar en el período, contra lo que ya entró. */
    private function totales($filas): array
    {
        // Una cita cancelada se sigue viendo, pero no se espera cobrarla.
        $esperado = $filas->where('cancelada', false)->sum('precio');
        $cobrado = $filas->sum(fn ($f) => $f['monto'] ?? 0);

        return [
            'sesiones' => $filas->count(),
            'esperado' => round($esperado, 2),
            'cobrado' => round($cobrado, 2),
            'saldo' => round($esperado - $cobrado, 2),
            'pendientes' => $filas->where('estado', Pago::PENDIENTE)->count(),
        ];
    }
}
