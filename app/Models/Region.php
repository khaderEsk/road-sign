<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'city_id', 'is_active'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
