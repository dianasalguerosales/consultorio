<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ObjetivoTerapeutico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'objetivos_terapeuticos';

    /**
     * De tres a cuatro objetivos por servicio y por niño, según la regla del
     * consultorio. El mínimo no se puede exigir al guardar el primero, así que
     * se usa para avisar en pantalla; el máximo sí se valida.
     */
    public const MINIMO_POR_SERVICIO = 3;

    public const MAXIMO_POR_SERVICIO = 4;

    protected $fillable = [
        'paciente_id',
        'servicio_id',
        'descripcion',
        'terapeuta_id',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    /** El servicio es el área en la que se plantea el objetivo. */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class);
    }

    /** Cuántos objetivos tiene ya ese niño en ese servicio. */
    public static function cuantosEn(int $pacienteId, int $servicioId): int
    {
        return self::where('paciente_id', $pacienteId)
            ->where('servicio_id', $servicioId)
            ->count();
    }
}
