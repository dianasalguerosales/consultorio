<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criterio extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'modulo',
        'area',
        'numero',
        'descripcion',
    ];

    public function items()
    {
        return $this->hasMany(AnamnesisItem::class);
    }
}