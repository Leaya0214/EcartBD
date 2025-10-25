<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class PaymentAttempt extends Model
{
    use HasFactory;
    
    protected $guarded = [];

    /**
     * Store JSON payload/response as array
     */
    protected $casts = [
        'response' => 'array',
        'payload' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
