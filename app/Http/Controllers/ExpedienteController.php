<?php

namespace App\Http\Controllers;

use App\Models\Expediente;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpedienteController extends Controller
{
    public function index()
    {
        $expedientes = Expediente::with('paciente')->get();

        return Inertia::render('Expedientes/Index', [
            'expedientes' => $expedientes,
        ]);
    }

    public function store(Request $request, Paciente $paciente)
    {
        $codigo = $this->generarCodigoExpediente($paciente->genero);

        \App\Models\Expediente::create([
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

    private function generarCodigoExpediente($genero)
    {
        $anio = date('y');
        $generoCodigo = $genero === 'Masculino' ? '1' : '2';

        $ultimo = Expediente::where('id', 'like', $anio.$generoCodigo.'%')
            ->orderBy('id', 'desc')
            ->first();

        $correlativo = $ultimo ? intval(substr($ultimo->id, 2)) + 1 : 1;

        return intval($anio.$generoCodigo.str_pad($correlativo, 3, '0', STR_PAD_LEFT));
    }
}