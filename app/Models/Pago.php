<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagos';

    /* ---------- Estados ---------- */

    /** Una cita sin fila en `pagos` está pendiente; no se guardan vacíos. */
    public const PENDIENTE = 'pendiente';

    public const PARCIAL = 'parcial';

    public const PAGADO = 'pagado';

    /**
     * Formas de pago que se reciben en el consultorio.
     *
     * Es una lista fija a propósito: no cambia seguido y así el filtro y el
     * informe no dependen de que alguien escriba "efectivo" o "Efectivo".
     */
    public const METODOS = [
        'Efectivo',
        'Transferencia',
        'Tarjeta de crédito',
        'Tarjeta de débito',
        'Cheque',
        'Depósito',
    ];

    protected $fillable = [
        'paciente_id',
        'cita_id',
        'programa_id',
        'monto',
        'metodo',
        'numero_autorizacion',
        'estado',
        'fecha',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'monto' => 'decimal:2',
    ];

    /* ---------- Relaciones ---------- */

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /* ---------- Consultas ---------- */

    public function scopeEnRango($query, string $desde, string $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    /** Pagado solo si cubre el precio de la cita; si no, queda parcial. */
    public static function estadoSegun(float $monto, ?float $precio): string
    {
        if ($monto <= 0) {
            return self::PENDIENTE;
        }

        return $precio && $monto + 0.001 < $precio ? self::PARCIAL : self::PAGADO;
    }
}
