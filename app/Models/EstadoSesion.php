<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoSesion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estado_sesiones';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function sesiones()
    {
        return $this->hasMany(Sesion::class);
    }
}