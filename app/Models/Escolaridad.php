<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Escolaridad extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'escolaridades';

    protected $fillable = [
        'grado',
    ];

    public function expedientes()
    {
        return $this->hasMany(Expediente::class);
    }
}