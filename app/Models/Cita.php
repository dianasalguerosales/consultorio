<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cita extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'paciente_id',
        'terapeuta_id',
        'estado_cita_id',
        'modalidad_id',
        'tipo_cita_id',
        'servicio_id',
        'programa_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'precio_aplicado',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function estadoCita()
    {
        return $this->belongsTo(EstadoCita::class, 'estado_cita_id');
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }

    public function tipoCita()
    {
        return $this->belongsTo(TipoCita::class);
    }

    public function sesion()
    {
        return $this->hasOne(Sesion::class);
    }
}