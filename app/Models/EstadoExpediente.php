<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoExpediente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'estado_expedientes';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class);
    }
}