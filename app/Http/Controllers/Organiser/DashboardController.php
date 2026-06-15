<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $organiser = $request->attributes->get('organiser');
        $paidStatuses = ['paid', 'partially_refunded'];

        $eventIds = Event::where('organiser_id', $organiser->id)->pluck('id');

        // ── KPI: Revenue ───────────────────────────────────────────
        $totalRevenue = Booking::whereIn('event_id', $eventIds)
            ->whereIn('status', $paidStatuses)
            ->sum('total');

        $monthRevenue = Booking::whereIn('event_id', $eventIds)
            ->whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $lastMonthRevenue = Booking::whereIn('event_id', $eventIds)
            ->whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->sum('total');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : ($monthRevenue > 0 ? 100 : 0);

        // ── KPI: Tickets Sold ──────────────────────────────────────
        $totalTicketsSold = BookingItem::whereHas('booking', function ($q) use ($eventIds, $paidStatuses) {
            $q->whereIn('event_id', $eventIds)->whereIn('status', $paidStatuses);
        })->sum('quantity');

        $monthTickets = BookingItem::whereHas('booking', function ($q) use ($eventIds, $paidStatuses) {
            $q->whereIn('event_id', $eventIds)
              ->whereIn('status', $paidStatuses)
              ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        })->sum('quantity');

        $lastMonthTickets = BookingItem::whereHas('booking', function ($q) use ($eventIds, $paidStatuses) {
            $q->whereIn('event_id', $eventIds)
              ->whereIn('status', $paidStatuses)
              ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]);
        })->sum('quantity');

        $ticketGrowth = $lastMonthTickets > 0
            ? round((($monthTickets - $lastMonthTickets) / $lastMonthTickets) * 100, 1)
            : ($monthTickets > 0 ? 100 : 0);

        // ── KPI: Upcoming Events ───────────────────────────────────
        $upcomingEvents = Event::where('organiser_id', $organiser->id)
            ->where('status', 'published')
            ->where('starts_at', '>', now())
            ->count();

        $totalEvents = Event::where('organiser_id', $organiser->id)->count();

        $newEventsThisMonth = Event::where('organiser_id', $organiser->id)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        // ── KPI: Conversion Rate ───────────────────────────────────
        // Reservations that completed checkout / total reservations
        $totalReservations = Reservation::whereIn('event_id', $eventIds)->count();
        $completedReservations = Reservation::whereIn('event_id', $eventIds)
            ->where('status', 'completed')->count();
        $conversionRate = $totalReservations > 0
            ? round(($completedReservations / $totalReservations) * 100, 1)
            : 0;

        $monthReservations = Reservation::whereIn('event_id', $eventIds)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $monthCompletedReservations = Reservation::whereIn('event_id', $eventIds)
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $lastMonthReservations = Reservation::whereIn('event_id', $eventIds)
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();

        $lastMonthCompletedReservations = Reservation::whereIn('event_id', $eventIds)
            ->where('status', 'completed')
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();

        $monthConversionRate = $monthReservations > 0
            ? ($monthCompletedReservations / $monthReservations) * 100
            : 0;

        $lastMonthConversionRate = $lastMonthReservations > 0
            ? ($lastMonthCompletedReservations / $lastMonthReservations) * 100
            : 0;

        $conversionGrowth = $lastMonthReservations > 0
            ? round($monthConversionRate - $lastMonthConversionRate, 1)
            : ($monthReservations > 0 ? round($monthConversionRate, 1) : 0);

        // ── Revenue Chart (last 12 months) ────────────────────────
        $revenueChart = Booking::whereIn('event_id', $eventIds)
            ->whereIn('status', $paidStatuses)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Fill missing months
        $chartLabels  = [];
        $chartRevenue = [];
        $chartOrders  = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $row   = $revenueChart->firstWhere('month', $month);
            $chartLabels[]  = now()->subMonths($i)->format('M y');
            $chartRevenue[] = $row ? round((float)$row->revenue, 2) : 0;
            $chartOrders[]  = $row ? (int)$row->orders : 0;
        }

        // ── Upcoming Events List ──────────────────────────────────
        $upcomingEventsList = Event::where('organiser_id', $organiser->id)
            ->where('starts_at', '>', now())
            ->withCount(['bookings as total_bookings' => fn($q) => $q->whereIn('status', $paidStatuses)])
            ->withSum(['ticketTiers as total_tier_quantity' => fn($q) => $q->where('is_active', true)], 'total_quantity')
            ->withSum(['ticketTiers as available_tier_quantity' => fn($q) => $q->where('is_active', true)], 'available_quantity')
            ->withSum(['bookings as total_revenue' => fn($q) => $q->whereIn('status', $paidStatuses)], 'total')
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        // ── Recent Orders ─────────────────────────────────────────
        $recentBookings = Booking::with(['event'])
            ->whereIn('event_id', $eventIds)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // ── Top Events by Revenue ─────────────────────────────────
        $topEvents = Event::where('organiser_id', $organiser->id)
            ->withSum(['bookings as total_revenue' => fn($q) => $q->whereIn('status', $paidStatuses)], 'total')
            ->withCount(['bookings as total_bookings' => fn($q) => $q->whereIn('status', $paidStatuses)])
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        return view('organiser.dashboard', compact(
            'organiser',
            'totalRevenue', 'monthRevenue', 'lastMonthRevenue', 'revenueGrowth',
            'totalTicketsSold', 'monthTickets', 'ticketGrowth',
            'upcomingEvents', 'totalEvents', 'newEventsThisMonth',
            'conversionRate', 'totalReservations', 'completedReservations', 'conversionGrowth',
            'chartLabels', 'chartRevenue', 'chartOrders',
            'upcomingEventsList', 'recentBookings', 'topEvents'
        ));
    }
}
