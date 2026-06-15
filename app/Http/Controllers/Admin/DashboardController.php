<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Customer;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DashboardController extends Controller
{
    public function index()
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $paidStatuses = ['paid', 'partially_refunded'];

        $totalCustomers = Customer::count();
        $totalOrganisers = Organiser::count();
        $totalEvents = Event::whereBetween('created_at', [$todayStart, $todayEnd])->count();

        $totalTicketsSold = BookingItem::query()
            ->join('bookings', 'bookings.id', '=', 'booking_items.booking_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->whereBetween('bookings.created_at', [$todayStart, $todayEnd])
            ->sum('booking_items.quantity');

        $totalRevenue = Booking::whereIn('status', $paidStatuses)
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('total');
        $totalPayouts = Payout::sum('amount');

        $recentEvents = Event::with('organiser')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $recentBookings = Booking::with(['event', 'customer'])
            ->whereIn('status', $paidStatuses)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'totalCustomers' => $totalCustomers,
            'totalOrganisers' => $totalOrganisers,
            'totalEvents' => $totalEvents,
            'totalTicketsSold' => (int) $totalTicketsSold,
            'totalRevenue' => (float) $totalRevenue,
            'totalPayouts' => (float) $totalPayouts,
            'recentEvents' => $recentEvents,
            'recentBookings' => $recentBookings,
        ]);
    }

    public function runScheduledTasks(Request $request)
    {
        // Run the scheduled commands manually
        Artisan::call('tickets:expire-reservations');
        Artisan::call('tickets:dispatch-reminders');
        Artisan::call('organisers:daily-summary');

        // Process queued notification jobs (InfinityFree-safe manual trigger)
        Artisan::call('queue:work', [
            '--queue' => config('notifications.queue', 'default') . ',default',
            '--stop-when-empty' => true,
            '--max-jobs' => 50,
            '--max-time' => 20,
        ]);

        return back()->with('success', 'Scheduled tasks have been executed successfully.');
    }
}
