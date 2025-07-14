<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'model', 'type', 'size',
        'advertising_space', 'printing_space',
        'user_id', 'faces_number'
    ];

    protected $casts = [
        'advertising_space' => 'double', 'printing_space' => 'double',
        'faces_number'=>'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function roadsigns()
    {
        return $this->hasMany(RoadSign::class);
    }
}
