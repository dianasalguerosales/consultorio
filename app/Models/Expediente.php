<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expediente extends Model
{
    protected $fillable = [
        'id',
        'paciente_id',
        'fecha_apertura',
        'estado',
        'creado_por_usuario_id',
        'observaciones_administrativas',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    public function paciente()
    {
        return $this->hasOne(Paciente::class, 'expediente_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'creado_por_usuario_id');
    }
}