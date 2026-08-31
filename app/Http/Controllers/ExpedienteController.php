<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Expediente;
use App\Models\Paciente;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Expediente::with('paciente');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('codigo')) {
            $query->where('id', 'like', '%'.$request->codigo.'%');
        }

        if ($request->filled('paciente')) {
            $query->whereHas('paciente', function ($q) use ($request) {
                $q->where('nombre', 'like', '%'.$request->paciente.'%');
            });
        }

        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_apertura', $request->fecha_inicio);
        }

        $expedientes = $query->orderBy('fecha_apertura', 'desc')->get();

        return Inertia::render('Expediente', [
            'expedientes' => $expedientes,
        ]);
    }

    public function store(Request $request, Paciente $paciente)
    {
        $codigo = $this->generarCodigoExpediente();

        $expediente = Expediente::create([
            'id' => $codigo,
            'paciente_id' => $paciente->id,
            'fecha_apertura' => now(),
            'estado' => 'activo',
            'creado_por_usuario_id' => $request->user()->id,
            'observaciones_administrativas' => 'Expediente inicial',
        ]);

        $paciente->update([
            'expediente_id' => $codigo,
        ]);

        return redirect()->back()->with('success', 'Expediente creado correctamente');
    }

    private function generarCodigoExpediente()
    {
        $anio = date('Y');

        $ultimo = Expediente::where('id', 'like', 'KID-'.$anio.'%')
            ->orderBy('id', 'desc')
            ->first();

        $correlativo = $ultimo ? intval(substr($ultimo->id, 7)) + 1 : 1;

        return 'KID'.$anio.str_pad($correlativo, 3, '0', STR_PAD_LEFT);
    }
}