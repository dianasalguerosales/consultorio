<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anamnesis extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'antecedentes_familiares',
        'antecedentes_medicos',
        'observaciones',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function expediente()
    {
        return $this->hasOne(Expediente::class);
    }
}