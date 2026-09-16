<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'email',
        'password',
        'google_token',
        'status',
        'last_login_at',
    ];

    public function terapeuta()
    {
        return $this->hasOne(Terapeuta::class);
    }

    public function administrativo()
    {
        return $this->hasOne(Administrativo::class);
    }

    public function encargado()
    {
        return $this->hasOne(Encargado::class);
    }

    /**
     * El nombre con que se muestra al usuario. `users` solo guarda el correo:
     * la persona vive en terapeuta, encargado o administrativo, según el rol.
     * Sin ninguna de las tres queda el correo, que siempre está.
     */
    public function getNombreCompletoAttribute(): string
    {
        $persona = $this->terapeuta ?? $this->encargado ?? $this->administrativo;

        return $persona?->nombre_completo ?: $this->email;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];
}