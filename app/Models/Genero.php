<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genero extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'generos';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }

    public function encargados()
    {
        return $this->hasMany(Encargado::class);
    }
}