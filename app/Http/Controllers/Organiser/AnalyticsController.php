<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $organiser = $request->attributes->get('organiser');
        $events    = Event::where('organiser_id', $organiser->id)
            ->orderBy('title')
            ->get(['id', 'title']);
        $eventIds = $events->pluck('id');
        $paidStatuses = ['paid', 'partially_refunded'];

        $validated = $request->validate([
            'event_id'  => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $selectedEventId = null;
        if (!empty($validated['event_id']) && $eventIds->contains((int) $validated['event_id'])) {
            $selectedEventId = (int) $validated['event_id'];
        }

        $from = !empty($validated['date_from'])
            ? Carbon::parse($validated['date_from'])->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = !empty($validated['date_to'])
            ? Carbon::parse($validated['date_to'])->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $revenueRows = Booking::query()
            ->whereIn('event_id', $eventIds)
            ->whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$from, $to])
            ->when($selectedEventId, fn($q) => $q->where('event_id', $selectedEventId))
            ->selectRaw('DATE(created_at) as bucket_date, SUM(total) as revenue')
            ->groupBy('bucket_date')
            ->orderBy('bucket_date')
            ->get()
            ->mapWithKeys(fn($row) => [$row->bucket_date => round((float) $row->revenue, 2)]);

        $ticketRows = BookingItem::query()
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->whereIn('bookings.event_id', $eventIds)
            ->whereIn('bookings.status', $paidStatuses)
            ->whereBetween('bookings.created_at', [$from, $to])
            ->when($selectedEventId, fn($q) => $q->where('bookings.event_id', $selectedEventId))
            ->selectRaw('DATE(bookings.created_at) as bucket_date, SUM(booking_items.quantity) as tickets')
            ->groupBy('bucket_date')
            ->orderBy('bucket_date')
            ->get()
            ->mapWithKeys(fn($row) => [$row->bucket_date => (int) $row->tickets]);

        $chartLabels  = [];
        $chartRevenue = [];
        $chartTickets = [];

        $period = CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay());
        foreach ($period as $date) {
            $key           = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');
            $chartRevenue[] = (float) ($revenueRows[$key] ?? 0);
            $chartTickets[] = (int) ($ticketRows[$key] ?? 0);
        }

        $totalRevenue = array_sum($chartRevenue);
        $totalTickets = array_sum($chartTickets);
        $selectedEventTitle = $selectedEventId
            ? ($events->firstWhere('id', $selectedEventId)?->title ?? 'Selected event')
            : 'All events';

        return view('organiser.analytics.index', [
            'organiser'          => $organiser,
            'events'             => $events,
            'selectedEventId'    => $selectedEventId,
            'selectedEventTitle' => $selectedEventTitle,
            'dateFrom'           => $from->toDateString(),
            'dateTo'             => $to->toDateString(),
            'chartLabels'        => $chartLabels,
            'chartRevenue'       => $chartRevenue,
            'chartTickets'       => $chartTickets,
            'totalRevenue'       => $totalRevenue,
            'totalTickets'       => $totalTickets,
        ]);
    }
}
