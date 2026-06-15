<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_secure' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
