<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRefund extends Model
{
    protected $fillable = [
        'booking_id',
        'stripe_refund_id',
        'original_total',
        'refunded_amount',
        'remaining_total',
        'currency',
        'reason',
        'refunded_at',
    ];

    protected $casts = [
        'original_total' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'remaining_total' => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
