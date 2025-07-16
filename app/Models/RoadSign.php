<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class RoadSign extends Model
{
    use HasFactory;
    protected $fillable = [
        'template_id',
        'city_id',
        'region_id',
        'place',
        'directions',
        'panels_number',
        'faces_number',
        'latitudeX',
        'longitudeY',
        'img'

    ];

    protected function facesNumber(): Attribute
    {
        return Attribute::make(
            get: fn($value) => floatval($value),
        );
    }
    protected function number(): Attribute
    {
        return Attribute::make(
            get: fn($value) => floatval($value),
        );
    }
    protected function printingMeters(): Attribute
    {
        return Attribute::make(
            get: fn($value) => floatval($value),
        );
    }
    protected function advertisingMeters(): Attribute
    {
        return Attribute::make(
            get: fn($value) => floatval($value),
        );
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class)
            ->withPivot([
                'booking_faces',
                'start_date',
                'end_date',
                'total_faces_price',
                'face_price',
                'number_of_reserved_panels',
                'days_of_reservation',
                'units'
            ]);
    }

    public function favorite()
    {
        return $this->hasMany(Favorite::class, 'road_id');
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
