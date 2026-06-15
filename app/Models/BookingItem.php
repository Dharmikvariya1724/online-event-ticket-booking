<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id', 'ticket_tier_id',
        'quantity', 'unit_price', 'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function booking()    { return $this->belongsTo(Booking::class); }
    public function ticketTier() { return $this->belongsTo(TicketTier::class); }
}