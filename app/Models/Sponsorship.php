<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sponsorship extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'photo',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        if (Str::startsWith($this->photo, ['http://', 'https://'])) {
            return $this->photo;
        }

        $normalizedPath = ltrim($this->photo, '/');

        if (Str::startsWith($normalizedPath, ['uploads/', 'storage/'])) {
            return asset($normalizedPath);
        }

        return asset('storage/' . $normalizedPath);
    }
}
