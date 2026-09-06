<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expediente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre_pila',
        'fecha_apertura',
        'estado',
        'motivo_consulta',
        'modalidad',
        'escolaridad_id',
        'anamnesis_id',
        'paciente_id',
        'creado_por_usuario_id',
        'observaciones_administrativas',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'creado_por_usuario_id');
    }

    public function diagnosticos()
    {
        return $this->belongsToMany(Diagnostico::class, 'expediente_diagnostico')
                    ->withTimestamps();
    }

    public function terapias()
    {
        return $this->belongsToMany(Terapia::class, 'expediente_terapia')
                    ->withTimestamps();
    }

    public function evaluaciones()
    {
        return $this->belongsToMany(Evaluacion::class, 'expediente_evaluacion')
                    ->withTimestamps();
    }

    public function escolaridad()
    {
        return $this->belongsTo(Escolaridad::class);
    }

    public function anamnesis()
    {
        return $this->belongsTo(Anamnesis::class);
    }

    protected static function booted()
    {
        static::deleting(function ($expediente) {
            $expediente->diagnosticos()->detach();
            $expediente->terapias()->detach();
            $expediente->evaluaciones()->detach();
        });
    }
}
