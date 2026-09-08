<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Anamnesis extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Sin esto Eloquent pluraliza "Anamnesis" como "anamneses" y toda escritura
     * falla: la tabla real es "anamnesis".
     */
    protected $table = 'anamnesis';

    protected $fillable = [
        'observaciones',
    ];

    public function items()
    {
        return $this->hasMany(AnamnesisItem::class);
    }

    public function expediente()
    {
        return $this->hasOne(Expediente::class);
    }
}