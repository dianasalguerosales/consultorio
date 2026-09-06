<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncargadoPaciente extends Model
{
    use HasFactory;

    protected $table = 'encargado_paciente';

    protected $fillable = [
        'encargado_id',
        'paciente_id',
    ];

    public function encargado()
    {
        return $this->belongsTo(Encargado::class);
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}