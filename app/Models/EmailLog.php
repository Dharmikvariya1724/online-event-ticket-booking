<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'to',
        'subject',
        'status',
        'mailable',
        'context_type',
        'context_id',
        'error',
        'meta',
        'sent_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'sent_at' => 'datetime',
    ];

    public function context()
    {
        return $this->morphTo(null, 'context_type', 'context_id');
    }

    public static function logSent(string $to, string $subject, ?string $mailable = null, ?Model $context = null, array $meta = []): self
    {
        return self::create([
            'to' => $to,
            'subject' => $subject,
            'status' => 'sent',
            'mailable' => $mailable,
            'context_type' => $context?->getMorphClass(),
            'context_id' => $context?->getKey(),
            'meta' => $meta,
            'sent_at' => now(),
        ]);
    }

    public static function logFailed(string $to, string $subject, string $error, ?string $mailable = null, ?Model $context = null, array $meta = []): self
    {
        return self::create([
            'to' => $to,
            'subject' => $subject,
            'status' => 'failed',
            'mailable' => $mailable,
            'context_type' => $context?->getMorphClass(),
            'context_id' => $context?->getKey(),
            'error' => $error,
            'meta' => $meta,
        ]);
    }

    public static function logQueued(string $to, string $subject, ?string $mailable = null, ?Model $context = null, array $meta = []): self
    {
        return self::create([
            'to' => $to,
            'subject' => $subject,
            'status' => 'queued',
            'mailable' => $mailable,
            'context_type' => $context?->getMorphClass(),
            'context_id' => $context?->getKey(),
            'meta' => $meta,
        ]);
    }
}
