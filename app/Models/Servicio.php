<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servicio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        // Con qué color se pinta esta terapia en la agenda, en #RRGGBB.
        'color',
        'activo',
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}