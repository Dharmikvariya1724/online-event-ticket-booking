<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'name', 'description', 'price',
        'total_quantity', 'available_quantity',
        'min_per_order', 'max_per_order',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'price'              => 'decimal:2',
        'is_active'          => 'boolean',
        'total_quantity'     => 'integer',
        'available_quantity' => 'integer',
    ];

    public function event()        { return $this->belongsTo(Event::class); }
    public function bookingItems() { return $this->hasMany(BookingItem::class); }
    public function reservationItems() { return $this->hasMany(ReservationItem::class); }

    public function getSoldQuantityAttribute(): int
    {
        return $this->total_quantity - $this->available_quantity;
    }

    public function isSoldOut(): bool
    {
        return $this->available_quantity <= 0;
    }
}