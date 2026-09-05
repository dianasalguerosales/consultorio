<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anamnesis extends Model
{
    use HasFactory;

    protected $table = 'anamnesis';

    protected $fillable = ['expediente_id', 'observaciones'];
    
    public function items()
    {
        return $this->hasMany(AnamnesisItem::class);
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class, 'id', 'anamnesis_id');
    }
}