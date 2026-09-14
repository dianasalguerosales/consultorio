<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombres',
        'apellidos',
        'escolaridad_id',
        'genero_id',
        'encargado_id',
    ];

    public function terapeutas()
    {
        return $this->belongsToMany(Terapeuta::class, 'paciente_terapeuta')
                    ->withTimestamps();
    }

    public function expediente()
    {
        return $this->hasOne(Expediente::class);
    }

    /** Los objetivos terapéuticos, agrupados por área al mostrarlos. */
    public function objetivos()
    {
        return $this->hasMany(ObjetivoTerapeutico::class)->orderBy('area')->orderBy('id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    public function escolaridad()
    {
        return $this->belongsTo(Escolaridad::class);
    }

    public function genero()
    {
        return $this->belongsTo(Genero::class);
    }

    public function encargado()
    {
        return $this->belongsTo(Encargado::class);
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}