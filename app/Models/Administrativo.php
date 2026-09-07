<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Administrativo extends Model
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
        'genero_id',
        'cargo_id',
        'especialidad_id',
        'experiencia',
        'certificaciones',
        'cursos',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function genero()
    {
        return $this->belongsTo(Genero::class);
    }


    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}