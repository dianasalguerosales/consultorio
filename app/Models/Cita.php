<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'encargado_id',
        'terapeuta_id',
        'servicio_id',
        'programa_id',
        'estado_cita_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'modalidad',
        'precio_aplicado',
        'motivo_consulta',
        'observaciones',
        'confirmada_por_encargado_at',
    ];

    public function paciente() { return $this->belongsTo(Paciente::class); }
    public function encargado() { return $this->belongsTo(Encargado::class); }
    public function terapeuta() { return $this->belongsTo(Terapeuta::class); }
    public function servicio() { return $this->belongsTo(Servicio::class); }
    public function programa() { return $this->belongsTo(Programa::class); }
    public function estadoCita() { return $this->belongsTo(EstadoCita::class); }
}