<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Administrativo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre',
        'fecha_nacimiento',
        'telefono',
        'correo',
        'direccion',
        'tipo', // administrador, coordinador, auxiliar
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pacientes()
    {
        return $this->belongsToMany(Paciente::class, 'administrativo_paciente')->withTimestamps();
    }
}
