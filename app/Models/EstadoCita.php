<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoCita extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estado_citas';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function citas()
    {
        return $this->hasMany(Cita::class, 'estado_citas_id');
    }
}