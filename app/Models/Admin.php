<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];
}
