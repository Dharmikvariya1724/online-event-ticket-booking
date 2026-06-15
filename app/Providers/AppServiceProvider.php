<?php

namespace App\Providers;

use App\Console\Commands\DispatchEventReminders;
use App\Console\Commands\ExpireReservations;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
    }

    public function boot(): void
    {
        try {
            if (Schema::hasTable('system_settings')) {
            $settings = SystemSetting::allCached();

            if (array_key_exists('service_fee_percentage', $settings)) {
                config(['ticketly.service_fee_percentage' => $settings['service_fee_percentage']]);
            }
            if (array_key_exists('portal_fee_percentage', $settings)) {
                config(['ticketly.portal_fee_percentage' => $settings['portal_fee_percentage']]);
            }
            if (array_key_exists('settlement_days', $settings)) {
                config(['ticketly.settlement_days' => $settings['settlement_days']]);
            }
            if (array_key_exists('currency', $settings)) {
                config(['ticketly.currency' => $settings['currency']]);
            }
            if (array_key_exists('currency_symbol', $settings)) {
                config(['ticketly.currency_symbol' => $settings['currency_symbol']]);
            }
            if (array_key_exists('support_email', $settings)) {
                config(['ticketly.support_email' => $settings['support_email']]);
            }
            if (array_key_exists('mail_from_address', $settings)) {
                config(['mail.from.address' => $settings['mail_from_address']]);
                config(['notifications.from_address' => $settings['mail_from_address']]);
            }
            if (array_key_exists('mail_from_name', $settings)) {
                config(['mail.from.name' => $settings['mail_from_name']]);
                config(['notifications.from_name' => $settings['mail_from_name']]);
            }
            $stripeKey = is_string($settings['stripe_key'] ?? null) ? trim($settings['stripe_key']) : null;
            $stripeSecret = is_string($settings['stripe_secret'] ?? null) ? trim($settings['stripe_secret']) : null;
            $hasStripePair = ($stripeKey !== null && $stripeKey !== '')
                && ($stripeSecret !== null && $stripeSecret !== '');

            if ($hasStripePair) {
                $keyMode = str_starts_with($stripeKey, 'pk_live_') ? 'live' : (str_starts_with($stripeKey, 'pk_test_') ? 'test' : null);
                $secretMode = str_starts_with($stripeSecret, 'sk_live_') ? 'live' : (str_starts_with($stripeSecret, 'sk_test_') ? 'test' : null);

                if ($keyMode !== null && $secretMode !== null && $keyMode === $secretMode) {
                    config(['services.stripe.key' => $stripeKey]);
                    config(['services.stripe.secret' => $stripeSecret]);
                } else {
                    logger()->warning('[Config] Ignoring Stripe settings from system_settings due to invalid/mismatched key pair.');
                }
            }

            $stripeWebhookSecret = is_string($settings['stripe_webhook_secret'] ?? null) ? trim($settings['stripe_webhook_secret']) : null;
            if ($stripeWebhookSecret !== null && $stripeWebhookSecret !== '') {
                config(['services.stripe.webhook_secret' => $stripeWebhookSecret]);
            }
        }
        } catch (\Throwable $e) {
            // Database not available during initial setup or cache clear
            logger()->warning('[Config] Could not load system_settings: ' . $e->getMessage());
        }

        // Scheduled Jobs
        Schedule::command(ExpireReservations::class)
            ->everyMinute()
            ->withoutOverlapping()
            ->name('expire-reservations');

        // Reminder dispatcher - runs every 30 minutes
        // Reminder windows configured in config/notifications.php
        Schedule::command(DispatchEventReminders::class)
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->name('dispatch-event-reminders');
    }
}
