<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Escolaridad extends Model
{
    use HasFactory;
    protected $table = 'escolaridades';

    protected $fillable = ['grado'];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class);
    }
}