<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', // Código KID-2026001
        'nombre_pila',
        'fecha_apertura',
        'estado',
        'motivo_consulta',
        'modalidad',
        'diagnostico_id',
        'escolaridad_id',
        'anamnesis_id',
        'paciente_id', // opcional
        'creado_por_usuario_id',
        'observaciones_administrativas',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'creado_por_usuario_id');
    }

    public function diagnostico()
    {
        return $this->belongsTo(Diagnostico::class);
    }

    public function escolaridad()
    {
        return $this->belongsTo(Escolaridad::class);
    }

    public function anamnesis()
    {
        return $this->belongsTo(Anamnesis::class);
    }
}