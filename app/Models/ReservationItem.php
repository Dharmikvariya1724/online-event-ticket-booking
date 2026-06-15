<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationItem extends Model
{
    protected $fillable = [
        'reservation_id', 'ticket_tier_id',
        'quantity', 'unit_price', 'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function ticketTier()  { return $this->belongsTo(TicketTier::class); }
}