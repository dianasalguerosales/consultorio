<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'fecha_nacimiento',
        'expediente',
        'telefono',
        'direccion',
        'genero',
    ];


    public function terapeutas()
    {
        return $this->belongsToMany(Terapeuta::class, 'paciente_terapeuta');
    }

    public function encargados()
    {
        return $this->belongsToMany(Encargado::class, 'encargado_paciente');
    }
}
