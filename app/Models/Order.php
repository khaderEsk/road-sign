<?php

namespace App\Models;

use App\OrderType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'road_sign_id', 'customer_id','booking_id',
        'type', 'action_date', 'notes', 'status','customer_new_id',
        'booking_new_id','order_execution_date'
    ];

    protected $casts = [
        'type' => OrderType::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }
    public function customerNew()
    {
        return $this->belongsTo(Customer::class,'customer_new_id');
    }

    public function roadSign()
    {
        return $this->belongsTo(RoadSign::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
