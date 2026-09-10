<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Pago;
use App\Pagos\Quincena;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Cobro de las sesiones ya recibidas.
 *
 * Solo se cobra lo que se dio: la lista sale de las citas que tienen sesión
 * registrada. Las que nadie ha pagado no tienen fila en `pagos`, se muestran
 * como pendientes.
 */
class PagoController extends Controller
{
    public function index(Request $request)
    {
        $rango = Quincena::desdeFiltro($request->query('desde'), $request->query('hasta'));

        $citas = Cita::query()
            ->has('sesion')
            ->enRango($rango->inicio(), $rango->fin())
            ->with(['paciente:id,nombres,apellidos,genero', 'servicio:id,nombre', 'atendidoPor'])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        $pagos = Pago::whereIn('cita_id', $citas->pluck('id'))->get()->keyBy('cita_id');

        $filas = $citas->map(fn (Cita $cita) => $this->fila($cita, $pagos->get($cita->id)));

        return Inertia::render('Pagos', [
            'filas' => $filas,
            'rango' => $rango->paraLaVista(),
            'metodos' => Pago::METODOS,
            'totales' => $this->totales($filas),
            'puedeRegistrar' => $request->user()->hasAnyRole(['administrador', 'coordinador', 'auxiliar']),
        ]);
    }

    /** Registra o corrige el pago de una cita. */
    public function store(Request $request, Cita $cita)
    {
        abort_unless($cita->sesion, 422, 'La cita todavía no tiene sesión registrada.');

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
            ]
        );

        return back()->with('success', 'Pago registrado.');
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
            'estado' => $pago?->estado ?? Pago::PENDIENTE,
            'monto' => $pago ? (float) $pago->monto : null,
            'metodo' => $pago?->metodo,
            'numero_autorizacion' => $pago?->numero_autorizacion,
            'fecha_pago' => $pago?->fecha?->toDateString(),
        ];
    }

    /** Lo que se espera cobrar en el período, contra lo que ya entró. */
    private function totales($filas): array
    {
        $esperado = $filas->sum('precio');
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
