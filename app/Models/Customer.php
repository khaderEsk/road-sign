<?php

namespace App\Models;

use App\BookingType;
use App\Mail\CustomerVerificationEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class Customer extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;
    protected $guard_name = 'customer';

    protected $fillable = [
        'full_name',
        'company_name',
        'address',
        'number',
        'commercial_registration_number',
        'phone_number',
        'total_paid',
        'remaining',
        'type',
        'alt_phone_number',
        'belong_id',
        'password',
        'email',
        'otp_code',
        'otp_expires_at',
        'email_verified_at',
        'status',
        'img',
        'google_id',
        'fcm_token',
    ];
    protected $appends = [
        'total',
        'total_paid'
    ];
    protected $hidden = [
        'password',
        'otp_code',
    ];
    protected $casts = [
        'alt_phone_number' => 'array',
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];


    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
    public function ordersPrev()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function ordersNext()
    {
        return $this->hasMany(Order::class, 'customer_new_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'belong_id');
    }

    public function owner()
    {
        return $this->belongsTo(Customer::class, 'belong_id');
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class);
    }

    public function getTotalPaidAttribute()
    {
        return (float)$this->payments()->sum('paid');
    }
    public function getTotalAttribute()
    {
        return (float)$this->bookings()->where('type', BookingType::PERMANENT)->sum('total_price');
    }

    public function sendVerificationEmail()
    {
        $this->otp_code = rand(100000, 999999);
        $this->otp_expires_at = now()->addMinutes(10);
        $this->save();
        Mail::to($this->email)->send(new CustomerVerificationEmail([
            'name' => $this->name,
            'otp' => $this->otp_code,
            'company_name' => ''
        ]));
    }

    public function favorite()
    {
        return $this->hasMany(Favorite::class, 'customer_id');
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
