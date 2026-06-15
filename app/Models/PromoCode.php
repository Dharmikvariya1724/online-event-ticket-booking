<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PromoCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organiser_id', 'event_id', 'code', 'type', 'value',
        'max_discount', 'max_uses', 'used_count', 'is_active', 'expires_at',
    ];

    protected $casts = [
        'value'       => 'decimal:2',
        'max_discount'=> 'decimal:2',
        'is_active'   => 'boolean',
        'expires_at'  => 'datetime',
    ];

    public function organiser() { return $this->belongsTo(Organiser::class); }
    public function event()     { return $this->belongsTo(Event::class); }
    public function bookings()  { return $this->hasMany(Booking::class); }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        $amount = max(0.0, round($amount, 2));

        if ($this->type === 'percentage') {
            $discount = round($amount * ($this->value / 100), 2);
            if ($this->max_discount) {
                $discount = min($discount, (float) $this->max_discount);
            }
            return min($discount, $amount);
        }
        // Fixed amount
        return min((float) $this->value, $amount);
    }

    public function isApplicableToEvent(Event $event): bool
    {
        if ((int) $this->organiser_id !== (int) $event->organiser_id) {
            return false;
        }

        if ($this->event_id) {
            return (int) $this->event_id === (int) $event->id;
        }

        return true;
    }

    public static function codeExistsForOrganiser(int $organiserId, string $code, ?int $ignoreId = null): bool
    {
        $query = static::withTrashed()
            ->where('organiser_id', $organiserId)
            ->whereRaw('LOWER(code) = ?', [Str::lower(trim($code))]);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public static function findForOrganiser(int $organiserId, string $code, bool $withTrashed = false): ?self
    {
        $query = $withTrashed ? static::withTrashed() : static::query();

        return $query
            ->where('organiser_id', $organiserId)
            ->whereRaw('LOWER(code) = ?', [Str::lower(trim($code))])
            ->latest('id')
            ->first();
    }

    public static function activeExistsForOtherOrganisers(int $organiserId, string $code): bool
    {
        return static::query()
            ->where('organiser_id', '!=', $organiserId)
            ->whereRaw('LOWER(code) = ?', [Str::lower(trim($code))])
            ->where('is_active', true)
            ->exists();
    }

    public static function resolveForEvent(?Event $event, ?string $code): array
    {
        $normalizedCode = trim((string) $code);

        if (!$event || !$event->organiser_id || $normalizedCode === '') {
            return ['promo' => null, 'message' => null];
        }

        $promo = static::findForOrganiser((int) $event->organiser_id, $normalizedCode, true);

        if ($promo) {
            if (!$promo->isApplicableToEvent($event)) {
                return ['promo' => null, 'message' => 'This promo code is not valid for this event.'];
            }

            if ($promo->trashed() || !$promo->isValid()) {
                return ['promo' => null, 'message' => null];
            }

            return ['promo' => $promo, 'message' => null];
        }

        if (static::activeExistsForOtherOrganisers((int) $event->organiser_id, $normalizedCode)) {
            return ['promo' => null, 'message' => 'This promo code is not valid for this event.'];
        }

        return ['promo' => null, 'message' => null];
    }
}
