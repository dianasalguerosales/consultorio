<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitudReprogramacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes_reprogramacion';

    /**
     * Horas mínimas de anticipación para pedir una reprogramación: las
     * profesionales necesitan prepararse. No hay máximo.
     *
     * Vive acá y no en el controlador porque la usan tres lugares: la
     * validación, la agenda que decide si dibuja el botón, y el mensaje de
     * error. Un solo número.
     */
    public const HORAS_MINIMAS = 24;

    public const PENDIENTE = 'pendiente';
    public const ACEPTADA = 'aceptada';
    public const RECHAZADA = 'rechazada';

    protected $fillable = [
        'cita_id',
        'solicitada_por',
        'motivo',
        'estado',
        'resuelta_por',
        'resuelta_en',
        'respuesta',
        'fecha_original',
        'hora_original',
    ];

    protected $casts = [
        'resuelta_en' => 'datetime',
        'fecha_original' => 'date:Y-m-d',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitada_por');
    }

    public function resolutor()
    {
        return $this->belongsTo(User::class, 'resuelta_por');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::PENDIENTE);
    }
}
