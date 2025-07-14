<?php

namespace App\Models;

use App\ProductType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['template_id', 'price', 'type'];
    protected $casts = [
        'type' => ProductType::class,
    ];

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => floatval($value),
        );
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function roadSigns()
    {
        return $this->hasMany(RoadSign::class);
    }
}
