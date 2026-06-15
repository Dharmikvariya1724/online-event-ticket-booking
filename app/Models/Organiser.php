<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Organiser extends Model implements Authenticatable
{
    use HasFactory, AuthenticatableTrait;

    protected $fillable = [
        'name', 'company_name', 'email', 'password',
        'phone', 'website', 'bio', 'logo',
        'is_approved', 'approved_at', 'approved_by_admin_id',
        'is_suspended', 'suspended_at',
        'rejected_at', 'rejection_reason', 'rejected_by_admin_id',
        'password_reset_token', 'password_reset_token_expires_at',
        'last_active_at',
        'stripe_account_id', 'stripe_onboarding_complete',
    ];

    protected $hidden = ['password', 'remember_token', 'password_reset_token'];

    protected $casts = [
        'is_approved'                   => 'boolean',
        'approved_at'                   => 'datetime',
        'is_suspended'                  => 'boolean',
        'suspended_at'                  => 'datetime',
        'rejected_at'                   => 'datetime',
        'password_reset_token_expires_at' => 'datetime',
        'last_active_at'                => 'datetime',
        'stripe_onboarding_complete'    => 'boolean',
    ];

    public function payouts()
    {
        return $this->hasMany(Payout::class, 'user_id');
    }

    // ── Relationships ─────────────────────────────────────────────
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function activeEvents()
    {
        return $this->hasMany(Event::class)->where('status', 'published')->where('starts_at', '>', now());
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isApproved(): bool
    {
        return (bool) $this->is_approved;
    }

    public function isSuspended(): bool
    {
        return (bool) $this->is_suspended;
    }

    public function hasActiveEvents(): bool
    {
        return $this->activeEvents()->exists();
    }

    public function generatePasswordResetToken(): string
    {
        $token = Str::random(64);
        $this->update([
            'password_reset_token'            => Hash::make($token),
            'password_reset_token_expires_at' => now()->addHours(24),
        ]);
        return $token;
    }

    public function isResetTokenValid(string $token): bool
    {
        if (!$this->password_reset_token) return false;
        if ($this->password_reset_token_expires_at?->isPast()) return false;
        return Hash::check($token, $this->password_reset_token);
    }

    public function clearResetToken(): void
    {
        $this->update([
            'password_reset_token'            => null,
            'password_reset_token_expires_at' => null,
        ]);
    }

    public function touchActivity(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    // ── Logo URL ──────────────────────────────────────────────────
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo) {
            return null;
        }

        if (Str::startsWith($this->logo, ['http://', 'https://'])) {
            return $this->logo;
        }

        if (Str::startsWith($this->logo, 'uploads/')) {
            return asset($this->logo);
        }

        $publicCandidate = 'uploads/organisers/' . basename($this->logo);
        if (file_exists(base_path($publicCandidate)) || file_exists(public_path($publicCandidate))) {
            return asset($publicCandidate);
        }

        return \Illuminate\Support\Facades\Storage::url($this->logo);
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->company_name);
        return strtoupper(implode('', array_map(fn($w) => substr($w, 0, 1), array_slice($words, 0, 2))));
    }
}
