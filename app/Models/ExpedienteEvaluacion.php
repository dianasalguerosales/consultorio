<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpedienteEvaluacion extends Model
{
    use HasFactory;

    protected $table = 'expediente_evaluacion';

    protected $fillable = [
        'expediente_id',
        'evaluacion_id',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }
}