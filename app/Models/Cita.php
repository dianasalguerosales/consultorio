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
        'atendido_por_type',
        'atendido_por_id',
        'estado_cita_id',
        'modalidad_id',
        'tipo_cita_id',
        'servicio_id',
        'programa_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'precio_aplicado',
        'google_event_id',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'precio_aplicado' => 'decimal:2',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /**
     * Quien atiende la cita: una Terapeuta o un Administrativo con cargo de
     * auxiliar (los auxiliares atienden solos en sucursal).
     */
    public function atendidoPor()
    {
        return $this->morphTo();
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

    public function solicitudesReprogramacion()
    {
        return $this->hasMany(SolicitudReprogramacion::class);
    }

    /** Citas cuya fecha cae dentro del rango que pide el calendario. */
    public function scopeEnRango($query, string $desde, string $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    /**
     * Citas de quien atiende, sea Terapeuta o Administrativo.
     * $persona es el modelo, no el id, para no confundir ids entre tablas.
     */
    public function scopeAtendidasPor($query, Model $persona)
    {
        return $query->where('atendido_por_type', $persona->getMorphClass())
                     ->where('atendido_por_id', $persona->getKey());
    }

    /**
     * Choque de horario para la misma persona en la misma fecha. Se ignoran las
     * citas canceladas y, al editar, la cita que se está guardando.
     */
    public function scopeSolapadas($query, string $atendidoPorType, int $atendidoPorId, string $fecha, string $horaInicio, string $horaFin, ?int $ignorarId = null)
    {
        return $query
            ->where('atendido_por_type', $atendidoPorType)
            ->where('atendido_por_id', $atendidoPorId)
            ->whereDate('fecha', $fecha)
            ->when($ignorarId, fn($q) => $q->where('id', '!=', $ignorarId))
            ->whereHas('estadoCita', fn($q) => $q->where('nombre', '!=', 'Cancelada'))
            // Dos rangos se traslapan si cada uno empieza antes de que el otro
            // termine. La comparación es de texto, así que ambos lados deben
            // venir en H:i:s: '10:00:00' > '10:00' daría true solo por largo.
            ->where('hora_inicio', '<', self::normalizarHora($horaFin))
            ->where('hora_fin', '>', self::normalizarHora($horaInicio));
    }

    /** Deja una hora en H:i:s, venga como '9:00', '09:00' o '09:00:00'. */
    public static function normalizarHora(string $hora): string
    {
        [$h, $m] = array_pad(explode(':', $hora), 2, '00');

        return sprintf('%02d:%02d:00', (int) $h, (int) $m);
    }
}