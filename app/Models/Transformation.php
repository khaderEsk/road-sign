<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transformation extends Model
{
    use HasFactory;
    protected $fillable = [
        'broker_id',
        'value',
        'type',
        'img'
    ];

    public function broker()
    {
        return $this->belongsTo(Broker::class);
    }
}
