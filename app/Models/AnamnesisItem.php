<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnamnesisItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'anamnesis_id',
        'criterio_id',
        'respuesta',
    ];

    public function criterio()
    {
        return $this->belongsTo(Criterio::class);
    }

    public function anamnesis()
    {
        return $this->belongsTo(Anamnesis::class);
    }
}