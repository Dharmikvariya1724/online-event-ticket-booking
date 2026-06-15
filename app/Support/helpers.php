<?php

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

if (! function_exists('ticketly_currency')) {
    function ticketly_currency(): string
    {
        return (string) config('ticketly.currency', 'GBP');
    }
}

if (! function_exists('ticketly_currency_symbol')) {
    function ticketly_currency_symbol(): string
    {
        return (string) config('ticketly.currency_symbol', '£');
    }
}

if (! function_exists('ticketly_money')) {
    function ticketly_money(float|int|string|null $amount): string
    {
        return ticketly_currency_symbol() . number_format((float) $amount, 2);
    }
}

if (! function_exists('ticketly_money_code')) {
    function ticketly_money_code(float|int|string|null $amount): string
    {
        return ticketly_currency() . ' ' . number_format((float) $amount, 2);
    }
}

if (! function_exists('ticketly_setting')) {
    function ticketly_setting(string $key, mixed $default = null): mixed
    {
        try {
            if (class_exists(\App\Models\SystemSetting::class)
                && \Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
                return \App\Models\SystemSetting::getValue($key, $default);
            }
        } catch (\Throwable $e) {
            // Database not available
        }
        return $default;
    }
}

if (! function_exists('ticketly_support_email')) {
    function ticketly_support_email(): string
    {
        return (string) config('ticketly.support_email', 'support@ticketly.com');
    }
}

if (! function_exists('ticketly_format_percentage')) {
    function ticketly_format_percentage(float|int|string|null $value): string
    {
        $number = (float) $value;

        if (fmod($number, 1.0) === 0.0) {
            return (string) (int) $number;
        }

        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.');
    }
}

if (! function_exists('ticketly_normalize_phone')) {
    function ticketly_normalize_phone(?string $phone, string $defaultCountryCode = '+91'): ?string
    {
        $phone = trim((string) $phone);
        if ($phone === '') {
            return null;
        }

        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        }

        $hasExplicitCountryCode = str_starts_with($phone, '+');
        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === '') {
            return null;
        }

        if (! $hasExplicitCountryCode) {
            $defaultDigits = preg_replace('/\D/', '', $defaultCountryCode);
            if ($defaultDigits !== '') {
                $digits = $defaultDigits . ltrim($digits, '0');
            }
        }

        return '+' . $digits;
    }
}

if (! function_exists('ticketly_phone_is_valid')) {
    function ticketly_phone_is_valid(?string $phone): bool
    {
        return is_string($phone) && preg_match('/^\+[1-9]\d{7,14}$/', $phone) === 1;
    }
}

if (! function_exists('ticketly_carbon')) {
    function ticketly_carbon(CarbonInterface|string|null $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Carbon::parse($value);
    }
}

if (! function_exists('ticketly_format_date')) {
    function ticketly_format_date(CarbonInterface|string|null $value, string $fallback = ''): string
    {
        $date = ticketly_carbon($value);

        return $date ? $date->format('l M j, Y') : $fallback;
    }
}

if (! function_exists('ticketly_format_time')) {
    function ticketly_format_time(CarbonInterface|string|null $value, string $fallback = ''): string
    {
        $date = ticketly_carbon($value);

        return $date ? $date->format('g:ia') : $fallback;
    }
}

if (! function_exists('ticketly_format_datetime')) {
    function ticketly_format_datetime(CarbonInterface|string|null $value, string $fallback = ''): string
    {
        $date = ticketly_carbon($value);

        return $date ? $date->format('l M j, Y g:ia') : $fallback;
    }
}

if (! function_exists('ticketly_format_compact_datetime')) {
    function ticketly_format_compact_datetime(CarbonInterface|string|null $value, string $fallback = ''): string
    {
        $date = ticketly_carbon($value);

        return $date ? $date->format('d M Y, g:i A') : $fallback;
    }
}
