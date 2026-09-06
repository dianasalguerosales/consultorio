<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PacienteTerapeuta extends Model
{
    use HasFactory;

    protected $table = 'paciente_terapeuta';

    protected $fillable = [
        'paciente_id',
        'terapeuta_id',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function terapeuta()
    {
        return $this->belongsTo(Terapeuta::class);
    }
}