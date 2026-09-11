<?php

namespace App\Models;

use App\Programas\RepartoPrecio;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * El programa de un niño: el paquete que lleva, con quién, qué días y a qué
 * precio. Sus citas cuelgan de acá por `citas.asignacion_programa_id`.
 */
class AsignacionPrograma extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'asignaciones_programa';

    public const ACTIVO = 'activo';

    public const FINALIZADO = 'finalizado';

    public const CANCELADO = 'cancelado';

    protected $fillable = [
        'paciente_id',
        'programa_id',
        'servicio_id',
        'atendido_por_type',
        'atendido_por_id',
        'modalidad_id',
        'tipo_cita_id',
        'precio',
        'cantidad_citas',
        'dias',
        'hora_inicio',
        'hora_fin',
        'fecha_inicio',
        'estado',
        'creado_por',
    ];

    protected $casts = [
        'dias' => 'array',
        'fecha_inicio' => 'date:Y-m-d',
        'precio' => 'decimal:2',
    ];

    /* ---------- Relaciones ---------- */

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /** El paquete del catálogo. */
    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function atendidoPor()
    {
        return $this->morphTo();
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }

    public function tipoCita()
    {
        return $this->belongsTo(TipoCita::class, 'tipo_cita_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'asignacion_programa_id');
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    /* ---------- Precio ---------- */

    /** Lo que cuesta cada cita, ya cuadrado para que la suma dé el precio. */
    public function precioPorCita(): array
    {
        return RepartoPrecio::en((float) $this->precio, $this->cantidad_citas);
    }
}
