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
        'encargado_id',
        'terapeuta_id',
        'servicio_id',
        'programa_id',
        'estado_citas_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'modalidad',
        'precio_aplicado',
        'motivo_consulta',
        'observaciones',
        'confirmada_por_encargado_at',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function encargado()
    {
        return $this->belongsTo(Encargado::class);
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
        return $this->belongsTo(EstadoCita::class, 'estado_citas_id');
    }

    public function sesion()
    {
        return $this->hasOne(Sesion::class);
    }
}