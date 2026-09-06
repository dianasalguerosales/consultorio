<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Programa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'sesiones_por_mes',
        'precio_mensual',
        'activo',
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}