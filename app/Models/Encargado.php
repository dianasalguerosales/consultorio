<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encargado extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'dpi',
        'telefono',
        'correo',
        'direccion',
        'ocupacion',
        'relacion_paciente_id',
        'genero_id',
        'estado_civil_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'encargado_paciente')
                    ->withTimestamps();
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function relacionPaciente()
    {
        return $this->belongsTo(RelacionPaciente::class);
    }

    public function genero()
    {
        return $this->belongsTo(Genero::class);
    }

    public function estadoCivil()
    {
        return $this->belongsTo(EstadoCivil::class);
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}