<?php

namespace App\Console\Commands;

use App\Mail\OrganiserDailySummary;
use App\Models\Organiser;
use App\Models\EmailLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Daily organiser sales summary.
 *
 * - Aggregates ticket quantities sold today per organiser.
 * - Sends a single summary email per organiser when tickets were sold.
 */
class SendOrganiserDailySummaries extends Command
{
    protected $signature   = 'organisers:daily-summary';
    protected $description = 'Send daily ticket sales summary emails to organisers';

    public function handle(): int
    {
        $start = now()->startOfDay();
        $end   = now()->endOfDay();
        $date  = $start->toDateString();

        $sales = DB::table('booking_items')
            ->join('bookings', 'booking_items.booking_id', '=', 'bookings.id')
            ->join('events', 'bookings.event_id', '=', 'events.id')
            ->where('bookings.status', 'paid')
            ->whereBetween('bookings.created_at', [$start, $end])
            ->select('events.organiser_id', DB::raw('SUM(booking_items.quantity) as tickets_sold'))
            ->groupBy('events.organiser_id')
            ->get()
            ->keyBy('organiser_id');

        $sent = 0;

        foreach ($sales as $organiserId => $row) {
            $ticketsSold = (int) ($row->tickets_sold ?? 0);
            if ($ticketsSold <= 0) {
                continue;
            }

            $organiser = Organiser::find($organiserId);
            if (!$organiser || empty($organiser->email)) {
                continue;
            }

            $detailsUrl = route('organiser.orders.index', [
                'status'    => 'paid',
                'date_from' => $date,
                'date_to'   => $date,
            ]);

            Mail::to($organiser->email)->queue(
                new OrganiserDailySummary($organiser, $ticketsSold, $date, $detailsUrl)
            );

            EmailLog::logQueued(
                $organiser->email,
                'Daily organiser summary',
                'organiser_daily_summary',
                $organiser,
                ['tickets_sold' => $ticketsSold, 'date' => $date, 'details_url' => $detailsUrl]
            );

            $sent++;
            $this->line("Sent daily summary to {$organiser->email} ({$ticketsSold} tickets)");
        }

        Log::info('[SendOrganiserDailySummaries] Sent ' . $sent . ' summary email(s).', [
            'date' => $date,
        ]);

        return Command::SUCCESS;
    }
}
