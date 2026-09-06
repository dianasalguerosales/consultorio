<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'evaluaciones';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    public function expedientes()
    {
        return $this->belongsToMany(Expediente::class, 'expediente_evaluacion')
                    ->withTimestamps();
    }

    public function resultados()
    {
        return $this->hasMany(ResultadoEvaluacion::class);
    }
}