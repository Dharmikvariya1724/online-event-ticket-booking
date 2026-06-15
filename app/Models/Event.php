<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'Music',
        'Nightlife',
        'Comedy',
        'Sports',
        'Arts',
        'Food & Drink',
        'Wellness',
        'Business',
    ];

    protected $fillable = [
        'organiser_id', 'title', 'slug', 'short_description', 'description',
        'banner', 'banner_image', 'category', 'starts_at', 'ends_at', 'ticket_validation_starts_at', 'ticket_validation_ends_at',
        'venue_name', 'venue_address', 'city', 'country', 'postcode',
        'parking_info', 'performer_lineup', 'refund_policy',
        'status', 'cancelled_at', 'cancellation_reason',
        'is_featured', 'total_capacity',
        'approval_status', 'approved_at', 'rejected_at', 'rejection_reason',
        'approved_by_admin_id', 'rejected_by_admin_id',
    ];

    protected $casts = [
        'starts_at'        => 'datetime',
        'ends_at'          => 'datetime',
        'ticket_validation_starts_at' => 'datetime',
        'ticket_validation_ends_at' => 'datetime',
        'cancelled_at'     => 'datetime',
        'approved_at'      => 'datetime',
        'rejected_at'      => 'datetime',
        'is_featured'      => 'boolean',
        'performer_lineup' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($e) {
            if (!$e->slug) {
                $e->slug = static::uniqueSlug($e->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function organiser()
    {
        return $this->belongsTo(Organiser::class);
    }

    public function ticketTiers()
    {
        return $this->hasMany(TicketTier::class)->orderBy('sort_order');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingItems()
    {
        return $this->hasManyThrough(BookingItem::class, Booking::class, 'event_id', 'booking_id', 'id', 'id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class);
    }

    public function sponsorships()
    {
        return $this->hasMany(Sponsorship::class)->orderBy('name');
    }

    public function getBannerUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl($this->banner, 'uploads/events');
    }

    public function getAiImageUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl($this->banner_image, 'ai_image');
    }

    public function getBannerImageUrlAttribute(): ?string
    {
        return $this->resolveMediaUrl($this->banner_image, 'ai_image');
    }

    public function getLowestPriceAttribute(): float
    {
        $min = $this->ticketTiers()->where('is_active', true)->min('price');

        return $min ?? 0;
    }

    public function getHighestPriceAttribute(): float
    {
        $max = $this->ticketTiers()->where('is_active', true)->max('price');

        return $max ?? 0;
    }

    public function getFormattedDateAttribute(): string
    {
        return ticketly_format_date($this->starts_at);
    }

    public function getFormattedTimeAttribute(): string
    {
        return ticketly_format_time($this->starts_at) . ' - ' . ticketly_format_time($this->ends_at);
    }

    public function ticketValidationStartsAt(): ?CarbonInterface
    {
        if ($this->ticket_validation_starts_at) {
            return $this->ticket_validation_starts_at;
        }

        return $this->starts_at?->copy()->subHours(2);
    }

    public function ticketValidationEndsAt(): ?CarbonInterface
    {
        if ($this->ticket_validation_ends_at) {
            return $this->ticket_validation_ends_at;
        }

        return $this->ends_at;
    }

    public function ticketValidationStatus(?CarbonInterface $moment = null): string
    {
        $moment = $moment ?? now();

        if ($this->isCancelled()) {
            return 'cancelled';
        }

        $validationStartsAt = $this->ticketValidationStartsAt();
        $validationEndsAt = $this->ticketValidationEndsAt();

        if (!$validationStartsAt || !$validationEndsAt) {
            return 'closed';
        }

        if ($moment->lt($validationStartsAt)) {
            return 'before_window';
        }

        if ($moment->gt($validationEndsAt)) {
            return 'after_window';
        }

        return 'open';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'published' => ['label' => 'Published', 'class' => 'badge--positive'],
            'draft' => ['label' => 'Draft', 'class' => 'badge--warning'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'badge--danger'],
            default => ['label' => ucfirst($this->status), 'class' => 'badge--neutral'],
        };
    }

    private function resolveMediaUrl(?string $path, string $fallbackDirectory): ?string
    {
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, [$fallbackDirectory . '/', 'uploads/'])) {
            return asset($path);
        }

        $publicCandidate = $fallbackDirectory . '/' . basename($path);
        if (file_exists(base_path($publicCandidate)) || file_exists(public_path($publicCandidate))) {
            return asset($publicCandidate);
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    }
}
