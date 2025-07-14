<?php

namespace App\Models;

use App\ContractStatus;
use App\ContractType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'broker_id',
        'user_id',
        'customer_id',
        'start_date',
        'end_date',
        'type',
        'status',
        'done',
    ];

    protected $casts = [
        'type' => ContractType::class,
        'status' => ContractStatus::class,
    ];

    public function broker()
    {
        return $this->belongsTo(Broker::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
