<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'ticket_uuid', 'event_id', 'reservation_id', 'customer_id', 'promo_code_id',
        'customer_name', 'customer_email', 'customer_phone',
        'subtotal', 'discount_amount', 'portal_fee', 'service_fee', 'total', 'currency',
        'stripe_session_id', 'stripe_payment_intent_id', 'stripe_charge_id',
        'status', 'refund_amount', 'refunded_at', 'refund_reason', 'is_used',
        'confirmation_sent_at', 'reminders_sent', 'scanned_at', 'scanned_quantity',
    ];

    protected $casts = [
        'subtotal'             => 'decimal:2',
        'discount_amount'      => 'decimal:2',
        'portal_fee'           => 'decimal:2',
        'service_fee'          => 'decimal:2',
        'total'                => 'decimal:2',
        'refund_amount'        => 'decimal:2',
        'refunded_at'          => 'datetime',
        'is_used'              => 'boolean',
        'confirmation_sent_at' => 'datetime',
        'reminders_sent'       => 'array',
        'scanned_at'           => 'datetime',
        'scanned_quantity'     => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($b) {
            $b->reference = $b->reference ?? strtoupper('TKT-' . Str::random(8));
            $b->ticket_uuid = $b->ticket_uuid ?? (string) Str::uuid();
            $b->currency  = $b->currency ?? ticketly_currency();
        });
    }

    public function event()      { return $this->belongsTo(Event::class); }
    public function items()      { return $this->hasMany(BookingItem::class); }
   public function refundTransactions() {
    return $this->hasMany(BookingRefund::class)
        ->orderBy('refunded_at', 'asc')
        ->orderBy('id', 'asc');
    }
    public function reservation(){ return $this->belongsTo(Reservation::class); }
    public function promoCode()  { return $this->belongsTo(PromoCode::class); }
    public function customer()   { return $this->belongsTo(Customer::class); }

    public function isPaid(): bool     { return $this->status === 'paid'; }
    public function isRefunded(): bool { return in_array($this->status, ['refunded', 'partially_refunded']); }
    public function isFullyRefunded(): bool { return $this->status === 'refunded'; }
    public function isPartiallyRefunded(): bool { return $this->status === 'partially_refunded'; }
    public function isUsed(): bool { return (bool) ($this->is_used || $this->scanned_at !== null || $this->scannedQuantity() > 0); }

    public function ticketQuantity(): int
    {
        $this->loadMissing('items');

        return max(1, (int) $this->items->sum('quantity'));
    }

    public function scannedQuantity(): int
    {
        return max(0, (int) ($this->scanned_quantity ?? 0));
    }

    public function validationCounts(): array
    {
        $ticketQuantity = $this->ticketQuantity();
        $rawValidatedQuantity = $this->isUsed() ? $ticketQuantity : $this->scannedQuantity();

        return [
            'ticket_quantity' => $ticketQuantity,
            'validated_quantity' => min($rawValidatedQuantity, $ticketQuantity),
            'remaining_quantity' => max(0, $ticketQuantity - $rawValidatedQuantity),
        ];
    }

    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'paid'               => ['label' => 'Paid',     'class' => 'badge--positive'],
            'refunded'           => ['label' => 'Refunded', 'class' => 'badge--accent'],
            'partially_refunded' => ['label' => 'Part. Refund', 'class' => 'badge--accent'],
            'cancelled'          => ['label' => 'Cancelled','class' => 'badge--danger'],
            'failed'             => ['label' => 'Failed',   'class' => 'badge--neutral'],
            default              => ['label' => ucfirst($this->status), 'class' => 'badge--neutral'],
        };
    }
}
