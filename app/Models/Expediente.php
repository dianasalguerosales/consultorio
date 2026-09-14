<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expediente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'paciente_id',
        'anamnesis_id',
        'modalidad_id',
        'estado_expediente_id',
        'codigo',
        'motivo_consulta',
        'fecha_inicio',
        'consentimiento',
        'observaciones',
    ];

    /**
     * Sin estos casts las fechas viajaban como "2026-09-09 15:12:24" y un
     * `<input type="date">` no reconoce ese formato: el campo salía en blanco
     * al editar aunque el dato estuviera guardado.
     */
    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
        'fecha_inicio' => 'date:Y-m-d',
        'consentimiento' => 'boolean',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function anamnesis()
    {
        return $this->belongsTo(Anamnesis::class);
    }

    public function diagnosticos()
    {
        return $this->belongsToMany(Diagnostico::class, 'expediente_diagnostico')
            ->withTimestamps();
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'expediente_servicios')
            ->withTimestamps();
    }

    public function evaluaciones()
    {
        return $this->belongsToMany(Evaluacion::class, 'expediente_evaluacion')
            ->withTimestamps();
    }

    public function modalidad()
    {
        return $this->belongsTo(Modalidad::class);
    }

    public function estado()
    {
        return $this->belongsTo(EstadoExpediente::class, 'estado_expediente_id');
    }

    public function getNombreExpedienteAttribute()
    {
        return "{$this->codigo} - {$this->nombres} {$this->apellidos}";
    }

    public static function generarCodigoExpediente()
    {
        $prefijo = 'KID-' . date('Y');

        // El corte se hace con el largo del prefijo, no con un 7 fijo:
        // 'KID-2026' son 8 caracteres, y cortar en 7 dejaba dentro un dígito
        // del año, así que cada expediente nuevo le agregaba un 6 al código
        // (KID-2026001 -> KID-20266002 -> KID-202666003...).
        //
        // Se calcula el máximo en PHP en lugar de ordenar por 'codigo' desc,
        // porque ese orden es alfabético y un código más largo se colaba como
        // si fuera el mayor.
        $correlativo = self::withTrashed()
            ->where('codigo', 'like', $prefijo . '%')
            ->pluck('codigo')
            ->map(fn($codigo) => (int) substr($codigo, strlen($prefijo)))
            ->max();

        return $prefijo . str_pad(($correlativo ?? 0) + 1, 3, '0', STR_PAD_LEFT);
    }
}