<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sesion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sesiones';

    protected $fillable = [
        'cita_id',
        'evolucion',
        'observaciones_clinicas',
        'observaciones_generales',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }
}