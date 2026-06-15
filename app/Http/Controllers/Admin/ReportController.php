<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use App\Models\Organiser;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : now()->subDays(29)->startOfDay();

        $to = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }
        $paidStatuses = ['paid', 'partially_refunded'];

        $ticketRows = BookingItem::query()
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->whereBetween('bookings.created_at', [$from, $to])
            ->selectRaw('DATE(bookings.created_at) as bucket_date, SUM(booking_items.quantity) as tickets')
            ->groupBy('bucket_date')
            ->orderBy('bucket_date')
            ->get()
            ->mapWithKeys(fn($row) => [$row->bucket_date => (int) $row->tickets]);

        $chartLabels = [];
        $chartTickets = [];
        $period = CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay());
        foreach ($period as $date) {
            $key = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');
            $chartTickets[] = (int) ($ticketRows[$key] ?? 0);
        }

        $revenueByEvent = Event::query()
            ->join('bookings', 'events.id', '=', 'bookings.event_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->whereBetween('bookings.created_at', [$from, $to])
            ->selectRaw('events.title as label, SUM(bookings.total) as value')
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        $revenueByOrganiser = Organiser::query()
            ->join('events', 'organisers.id', '=', 'events.organiser_id')
            ->join('bookings', 'events.id', '=', 'bookings.event_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->whereBetween('bookings.created_at', [$from, $to])
            ->selectRaw('organisers.name as label, SUM(bookings.total) as value')
            ->groupBy('organisers.id', 'organisers.name')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        $topEvents = Event::query()
            ->join('bookings', 'events.id', '=', 'bookings.event_id')
            ->join('booking_items', 'bookings.id', '=', 'booking_items.booking_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->whereBetween('bookings.created_at', [$from, $to])
            ->selectRaw('events.title as label, SUM(booking_items.quantity) as value')
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        $topOrganisers = Organiser::query()
            ->join('events', 'organisers.id', '=', 'events.organiser_id')
            ->join('bookings', 'events.id', '=', 'bookings.event_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->whereBetween('bookings.created_at', [$from, $to])
            ->selectRaw('organisers.name as label, SUM(bookings.total) as value')
            ->groupBy('organisers.id', 'organisers.name')
            ->orderByDesc('value')
            ->limit(10)
            ->get();

        return view('admin.reports.index', [
            'chartLabels' => $chartLabels,
            'chartTickets' => $chartTickets,
            'revenueByEvent' => $revenueByEvent,
            'revenueByOrganiser' => $revenueByOrganiser,
            'topEvents' => $topEvents,
            'topOrganisers' => $topOrganisers,
            'dateFrom' => $from->toDateString(),
            'dateTo' => $to->toDateString(),
        ]);
    }
}
