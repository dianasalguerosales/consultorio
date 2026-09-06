<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResultadoEvaluacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'resultado_evaluaciones';

    protected $fillable = [
        'evaluacion_id',
        'descripcion',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }
}