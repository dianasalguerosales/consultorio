<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpedienteServicio extends Model
{
    use HasFactory;

    protected $table = 'expediente_servicios';

    protected $fillable = [
        'expediente_id',
        'servicio_id',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}