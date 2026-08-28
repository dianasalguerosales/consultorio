<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Terapeuta extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'especialidad',
        'fecha_nacimiento',
        'telefono',
        'correo',
        'numero_colegiado',
        'experiencia',
        'formacion',
        'certificaciones',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'paciente_terapeuta');
    }
}
