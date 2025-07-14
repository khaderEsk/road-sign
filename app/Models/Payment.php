<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'customer_id', 'total',
        'paid', 'remaining', 'date',
        'payment_number', 'payment_image',
        'is_received'
    ];

    protected $casts = [
        'paid' => 'double',
        'remaining' => 'double',
        'total'=>'double'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
