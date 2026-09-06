<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagos';

    protected $fillable = [
        'paciente_id',
        'cita_id',
        'programa_id',
        'monto',
        'metodo',
        'estado',
        'fecha',
    ];

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
}