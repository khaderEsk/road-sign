<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broker extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;
    protected $fillable = [
        'full_name',
        'number',
        'discount',
        'email',
        'password',
        'wallet'
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    protected $hidden = [
        'password',
    ];


    public function customer(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function transformation(): HasMany
    {
        return $this->hasMany(Transformation::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
