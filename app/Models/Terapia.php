<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Terapia extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'descripcion'];

    public function expedientes()
    {
        return $this->belongsToMany(Expediente::class, 'expediente_terapia');
    }
}
