<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpedienteDiagnostico extends Model
{
    use HasFactory;

    protected $table = 'expediente_diagnostico';

    protected $fillable = [
        'expediente_id',
        'diagnostico_id',
    ];

    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }

    public function diagnostico()
    {
        return $this->belongsTo(Diagnostico::class);
    }
}