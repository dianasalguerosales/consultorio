<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoCivil extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estados_civiles';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }
}