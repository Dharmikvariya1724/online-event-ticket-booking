<?php

namespace App\Console\Commands;

use App\Jobs\SendEventReminder;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Run by scheduler at regular intervals.
 * Dispatches reminders for bookings where:
 *   - status = 'paid'
 *   - reminder not already sent for this window
 *   - event is within the reminder window
 *
 * Uses a JSON column 'reminders_sent' on bookings table to track which
 * windows have already been dispatched (to prevent duplicates).
 */
class DispatchEventReminders extends Command
{
    protected $signature   = 'tickets:dispatch-reminders';
    protected $description = 'Dispatch event reminder notifications (48h, 24h, event-day)';

    public function handle(): int
    {
        $windows = config('notifications.reminder_windows', []);
        $total   = 0;
        $now     = now();

        foreach ($windows as $window) {
            $label      = $window['label'];
            $hoursBefore = (int) ($window['hours_before'] ?? 0);
            $atHour      = $window['at_hour'] ?? null;

            // Calculate the target window
            if ($label === 'event_day') {
                // Event-day reminder: event starts today, and current hour >= 8am
                $windowStart = $now->copy()->startOfDay()->addHours($atHour ?? 8);
                $windowEnd   = $windowStart->copy()->addHour();

                // Start is inclusive, end is exclusive.
                if ($now->lt($windowStart) || $now->gte($windowEnd)) {
                    continue; // Not within the event-day dispatch window
                }

                // Day-of reminder should include all today's events.
                $eventAfter  = $now->copy()->startOfDay();
                $eventBefore = $now->copy()->endOfDay();
            } else {
                // X-hours-before reminder: event starts in the next hour relative to window
                $eventAfter  = $now->copy()->addHours($hoursBefore);
                $eventBefore = $eventAfter->copy()->addHour();
            }

            $bookings = Booking::with('event')
                ->where('status', 'paid')
                ->whereHas('event', function ($q) use ($eventAfter, $eventBefore) {
                    $q->whereBetween('starts_at', [$eventAfter, $eventBefore]);
                })
                ->get()
                ->filter(function ($booking) use ($label) {
                    $sent = $booking->reminders_sent ?? [];
                    if (is_string($sent)) {
                        $sent = json_decode($sent, true) ?: [];
                    }
                    if (!is_array($sent)) {
                        $sent = [];
                    }

                    return !in_array($label, $sent, true);
                });

            foreach ($bookings as $booking) {
                SendEventReminder::dispatch($booking, $label);

                // Mark reminder as sent
                $sent = $booking->reminders_sent ?? [];
                if (is_string($sent)) {
                    $sent = json_decode($sent, true) ?: [];
                }
                if (!is_array($sent)) {
                    $sent = [];
                }
                $sent[] = $label;
                $booking->update(['reminders_sent' => array_values(array_unique($sent))]);

                $total++;
                $this->line("Queued {$label} reminder for {$booking->reference}");
            }
        }

        $this->info("Dispatched {$total} reminder(s).");
        Log::info('[DispatchEventReminders] Dispatched ' . $total . ' reminder(s).');

        return Command::SUCCESS;
    }
}
