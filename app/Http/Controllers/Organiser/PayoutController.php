<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;

class PayoutController extends Controller
{

    private int $settlementDays;

    public function __construct()
    {
        $this->settlementDays = (int) config('ticketly.settlement_days', 7);
    }

    public function index(Request $request)
    {
        $organiser = $request->attributes->get('organiser');
        $canReceivePayouts = !empty($organiser->stripe_account_id) && (bool) $organiser->stripe_onboarding_complete;
        $paidStatuses = ['paid', 'partially_refunded'];
        $countStatuses = ['paid', 'partially_refunded', 'refunded'];
        $events    = Event::where('organiser_id', $organiser->id)
            ->orderBy('title')
            ->get(['id', 'title']);
        $eventIds = $events->pluck('id');

        $validated = $request->validate([
            'event_id'  => 'nullable|integer',
            'status'    => 'nullable|in:paid_out,pending',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
        ]);

        $selectedEventId = null;
        if (!empty($validated['event_id']) && $eventIds->contains((int) $validated['event_id'])) {
            $selectedEventId = (int) $validated['event_id'];
        }

        $query = Booking::query()
            ->join('events', 'events.id', '=', 'bookings.event_id')
            ->whereIn('bookings.event_id', $eventIds)
            ->whereIn('bookings.status', $countStatuses)
            ->when($selectedEventId, fn($q) => $q->where('bookings.event_id', $selectedEventId))
            ->when(!empty($validated['date_from']), function ($q) use ($validated) {
                $q->whereRaw(
                    'DATE(DATE_ADD(bookings.created_at, INTERVAL ' . $this->settlementDays . ' DAY)) >= ?',
                    [$validated['date_from']]
                );
            })
            ->when(!empty($validated['date_to']), function ($q) use ($validated) {
                $q->whereRaw(
                    'DATE(DATE_ADD(bookings.created_at, INTERVAL ' . $this->settlementDays . ' DAY)) <= ?',
                    [$validated['date_to']]
                );
            })
            ->when(($validated['status'] ?? null) === 'pending', function ($q) use ($canReceivePayouts) {
                if (!$canReceivePayouts) {
                    return;
                }

                $q->whereRaw(
                    'DATE(DATE_ADD(bookings.created_at, INTERVAL ' . $this->settlementDays . ' DAY)) > ?',
                    [now()->toDateString()]
                );
            })
            ->when(($validated['status'] ?? null) === 'paid_out', function ($q) use ($canReceivePayouts) {
                if (!$canReceivePayouts) {
                    $q->whereRaw('1 = 0');
                    return;
                }

                $q->whereRaw(
                    'DATE(DATE_ADD(bookings.created_at, INTERVAL ' . $this->settlementDays . ' DAY)) <= ?',
                    [now()->toDateString()]
                );
            })
            ->havingRaw("SUM(CASE WHEN bookings.status IN ('paid','partially_refunded') THEN bookings.total ELSE 0 END) > 0")
            ->selectRaw(
                "bookings.event_id,
                 events.title as event_title,
                 DATE(bookings.created_at) as source_date,
                 DATE(DATE_ADD(bookings.created_at, INTERVAL " . $this->settlementDays . " DAY)) as payout_date,
                 COUNT(bookings.id) as order_count,
                 SUM(CASE WHEN bookings.status IN ('paid','partially_refunded') THEN bookings.total ELSE 0 END) as amount"
            )
            ->groupBy('bookings.event_id', 'events.title', 'source_date', 'payout_date')
            ->orderByDesc('payout_date');

        $payouts = $query->paginate(20)->withQueryString();
        $payouts->getCollection()->transform(function ($row) use ($canReceivePayouts) {
            $isPaidOut = $canReceivePayouts && $row->payout_date <= now()->toDateString();
            $row->status = $isPaidOut ? 'paid_out' : 'pending';
            $row->status_label = $isPaidOut ? 'Paid Out' : 'Pending';
            $row->status_class = $isPaidOut ? 'badge--positive' : 'badge--warning';
            return $row;
        });

        $summary = [
            'paid_out_amount' => (float) $payouts->getCollection()
                ->where('status', 'paid_out')
                ->sum(fn($row) => (float) $row->amount),
            'pending_amount' => (float) $payouts->getCollection()
                ->where('status', 'pending')
                ->sum(fn($row) => (float) $row->amount),
        ];

        return view('organiser.payouts.index', [
            'organiser'       => $organiser,
            'events'          => $events,
            'payouts'         => $payouts,
            'selectedEventId' => $selectedEventId,
            'summary'         => $summary,
            'settlementDays'  => $this->settlementDays,
            'canReceivePayouts' => $canReceivePayouts,
        ]);
    }
}
