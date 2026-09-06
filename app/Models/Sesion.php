<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sesion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sesiones';

    protected $fillable = [
        'cita_id',
        'terapeuta_id',
        'evolucion',
        'observaciones_clinicas',
        'observaciones_generales',
        'duracion_minutos',
        'estado_sesion_id',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class);
    }

    public function estadoSesion()
    {
        return $this->belongsTo(EstadoSesion::class, 'estado_sesion_id');
    }
}