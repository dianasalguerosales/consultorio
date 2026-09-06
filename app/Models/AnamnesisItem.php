<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnamnesisItem extends Model
{
    use HasFactory, SoftDeletes;

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