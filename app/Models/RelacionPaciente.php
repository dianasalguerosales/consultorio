<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RelacionPaciente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'relaciones_paciente';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function encargados()
    {
        return $this->hasMany(Encargado::class);
    }
}