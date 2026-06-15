<?php

namespace App\Support;

use App\Models\Admin;
use Illuminate\Support\Str;

class AdminAuth
{
    private const SESSION_KEY = 'admin_id';
    private const AUTH_STATE_KEY = 'admin_auth_state';
    private const INACTIVITY_MINS = 90;

    public static function user(): ?Admin
    {
        $id = session(self::SESSION_KEY);
        $lastActive = session('admin_last_active');

        if (!$id) {
            return null;
        }

        if ($lastActive && (now()->timestamp - $lastActive) > (self::INACTIVITY_MINS * 60)) {
            self::logout();
            return null;
        }

        $admin = Admin::find($id);
        if (!$admin) {
            self::logout();
            return null;
        }

        if (!session(self::AUTH_STATE_KEY)) {
            self::issueHistoryState();
        }

        session(['admin_last_active' => now()->timestamp]);
        $admin->update(['last_active_at' => now()]);

        return $admin;
    }

    public static function login(Admin $admin): void
    {
        session([
            self::SESSION_KEY => $admin->id,
            self::AUTH_STATE_KEY => (string) Str::uuid(),
            'admin_last_active' => now()->timestamp,
        ]);
        $admin->update(['last_active_at' => now()]);
    }

    public static function issueHistoryState(): string
    {
        $state = (string) Str::uuid();
        session([self::AUTH_STATE_KEY => $state]);

        return $state;
    }

    public static function logout(): void
    {
        session()->forget([self::SESSION_KEY, self::AUTH_STATE_KEY, 'admin_last_active']);
    }
}
