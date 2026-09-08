<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Terapeuta extends Model
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
        'especialidad_id',
        'genero_id',
        'experiencia',
        'certificaciones',
        'cursos',
    ];

    /**
     * nombre_completo viaja en el JSON para que las vistas que reciben esta
     * relación sin pasar por un mapeo del controlador (por ejemplo
     * cita.atendido_por) puedan mostrar el nombre directamente.
     */
    protected $appends = ['nombre_completo'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'paciente_terapeuta')
                    ->withTimestamps();
    }

    public function citas()
    {
        return $this->morphMany(Cita::class, 'atendido_por');
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