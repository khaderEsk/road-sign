<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'commercial_registration_number',
        'address',
        'description',
        'about_us',
        'contract_note',
        'latitudeX',
        'longitudeY',
        'img',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
