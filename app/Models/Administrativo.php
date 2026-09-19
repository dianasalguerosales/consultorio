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

    protected $appends = ['nombre_completo'];

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

    /**
     * Las citas que atiende, a traves de su usuario: la cita apunta al usuario
     * y no a esta ficha, para que el rol sea lo que decide quien atiende.
     */
    public function citas()
    {
        return $this->hasMany(Cita::class, 'atiende_user_id', 'user_id');
    }

    public function superior()
    {
        return $this->belongsTo(Administrativo::class, 'superior_id');
    }

    public function subordinados()
    {
        return $this->hasMany(Administrativo::class, 'superior_id');
    }

    public function terapeutas()
    {
        return $this->hasMany(Terapeuta::class, 'superior_id');
    }

    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellidos}";
    }
}