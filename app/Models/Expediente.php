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
        'diagnostico_id',
        'modalidad_id',
        'estado_expediente_id',
        'codigo',
        'motivo_consulta',
        'fecha_inicio',
        'consentimiento',
        'observaciones',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function anamnesis()
    {
        return $this->belongsTo(Anamnesis::class);
    }

    public function diagnostico()
    {
        return $this->belongsTo(Diagnostico::class);
    }

    public function diagnosticos()
    {
        return $this->belongsToMany(Diagnostico::class, 'expediente_diagnostico')
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

    public function evaluaciones()
    {
        return $this->belongsToMany(Evaluacion::class, 'expediente_evaluacion')
            ->withTimestamps();
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'expediente_servicio')
            ->withTimestamps();
    }

    public function getNombreExpedienteAttribute()
    {
        return "{$this->codigo} - {$this->nombres} {$this->apellidos}";
    }

    public static function generarCodigoExpediente()
    {
        $anio = date('Y');

        $ultimo = self::where('codigo', 'like', 'KID-' . $anio . '%')
            ->orderBy('codigo', 'desc')
            ->first();

        $correlativo = $ultimo ? intval(substr($ultimo->codigo, 7)) + 1 : 1;

        return 'KID-' . $anio . str_pad($correlativo, 3, '0', STR_PAD_LEFT);
    }
}