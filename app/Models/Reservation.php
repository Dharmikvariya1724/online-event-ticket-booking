<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'token', 'event_id', 'session_id',
        'customer_email', 'customer_name', 'customer_phone',
        'promo_code_id', 'discount_amount',
        'subtotal', 'portal_fee', 'service_fee', 'total',
        'stripe_payment_intent_id',
        'expires_at', 'status',
    ];

    protected $casts = [
        'expires_at'      => 'datetime',
        'subtotal'        => 'decimal:2',
        'portal_fee'      => 'decimal:2',
        'service_fee'     => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'           => 'decimal:2',
    ];

    public function event()     { return $this->belongsTo(Event::class); }
    public function items()     { return $this->hasMany(ReservationItem::class); }
    public function promoCode() { return $this->belongsTo(PromoCode::class); }

    public function isActive(): bool
    {
        return $this->status === 'pending' && $this->expires_at?->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at?->isPast() ?? true;
    }

    public function secondsRemaining(): int
    {
        if ($this->isExpired()) return 0;
        return max(0, (int) now()->diffInSeconds($this->expires_at, false));
    }
}
