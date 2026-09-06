<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Especialidad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'especialidades';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function administrativos()
    {
        return $this->hasMany(Administrativo::class);
    }

    public function terapeutas()
    {
        return $this->hasMany(Terapeuta::class);
    }
}