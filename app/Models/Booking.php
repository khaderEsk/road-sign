<?php

namespace App\Models;

use App\BookingType;
use App\DiscountType;
use App\ProductType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'customer_id',
        'discount_type',
        'notes',
        'value',
        'type',
        'start_date',
        'end_date',
        'number',
        'product_type',
        'total_advertising_space',
        'total_printing_space',
        'total_price',
        'total_price_per_month',
        'total_price_befor_discount',
        'units',
        'status'

    ];
    protected $casts = [
        'type' => BookingType::class,
        'product_type' => ProductType::class,
        'discount_type' => DiscountType::class,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
    protected $appends = [
        'duration_of_days'
    ];

    public function getNotesWithoutAttribute(): string
    {
        $cleanNotes = strip_tags(str_ireplace(['<br>', '<br/>', '<br />', '</p>', '<p>'], ["\n", "\n", "\n", "\n", ''], $this->notes));
        return $this->title . ' - ' . $cleanNotes;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }


    public function roadsigns()
    {
        return $this->belongsToMany(RoadSign::class)
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
    public function getDurationOfDaysAttribute(): int
    {
        return Carbon::parse($this->start_date)->diffInDays(Carbon::parse($this->end_date)) + 1;
    }
}
