<?php

namespace App\Http\Controllers\Organiser;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Refund;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $organiser = $request->attributes->get('organiser');
        $eventIds  = Event::where('organiser_id', $organiser->id)->pluck('id');

        $query = Booking::with(['event'])
            ->withSum('items as total_tickets', 'quantity')
            ->whereIn('event_id', $eventIds);

        // ── Filters ────────────────────────────────────────────────
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_email', 'like', "%{$s}%");
            });
        }

        // ── CSV Export ─────────────────────────────────────────────
        if ($request->filled('export') && $request->export === 'csv') {
            return $this->exportCsv($query->get());
        }

        $orders = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $events = Event::where('organiser_id', $organiser->id)->orderBy('title')->get();

        // Summary stats
        $paidStatuses = ['paid', 'partially_refunded'];
        $stats = [
            'total'   => Booking::whereIn('event_id', $eventIds)->whereIn('status', $paidStatuses)->count(),
            'revenue' => Booking::whereIn('event_id', $eventIds)->whereIn('status', $paidStatuses)->sum('total'),
        ];

        return view('organiser.orders.index', compact('organiser', 'orders', 'events', 'stats'));
    }

    public function show(Request $request, int $id)
    {
        $organiser = $request->attributes->get('organiser');
        $eventIds  = Event::where('organiser_id', $organiser->id)->pluck('id');

        $booking   = Booking::with([
            'event',
            'items.ticketTier',
            'promoCode' => fn ($query) => $query->withTrashed(),
            'refundTransactions',
        ])
            ->whereIn('event_id', $eventIds)
            ->findOrFail($id);

        return view('organiser.orders.show', compact('organiser', 'booking'));
    }

    // public function refund(Request $request, int $id)
    // {
    //     $organiser = $request->attributes->get('organiser');
    //     $eventIds  = Event::where('organiser_id', $organiser->id)->pluck('id');
    //     $booking   = Booking::whereIn('event_id', $eventIds)->findOrFail($id);

    //     if (!$booking->isPaid()) {
    //         return back()->withErrors(['refund' => 'Only paid bookings can be refunded.']);
    //     }

    //     $request->validate([
    //         'refund_amount' => 'required|numeric|min:0.01|max:' . $booking->total,
    //         'refund_reason' => 'required|string|max:500',
    //     ]);

    //     try {
    //         Stripe::setApiKey(config('services.stripe.secret'));

    //         $amountPence = (int) round($request->refund_amount * 100);

    //         $refundData = ['amount' => $amountPence];
    //         if ($booking->stripe_payment_intent_id) {
    //             $refundData['payment_intent'] = $booking->stripe_payment_intent_id;
    //         } elseif ($booking->stripe_charge_id) {
    //             $refundData['charge'] = $booking->stripe_charge_id;
    //         } else {
    //             return back()->withErrors(['refund' => 'No Stripe payment reference found on this booking.']);
    //         }

    //         Refund::create($refundData);

    //         $isFullRefund  = $request->refund_amount >= $booking->total;
    //         $booking->update([
    //             'status'        => $isFullRefund ? 'refunded' : 'partially_refunded',
    //             'refund_amount' => $request->refund_amount,
    //             'refunded_at'   => now(),
    //             'refund_reason' => $request->refund_reason,
    //         ]);

    //         dispatch(new \App\Jobs\SendRefundConfirmation($booking, (float) $request->refund_amount));

    //         Log::info('[OrderController] Refund issued: ' . $booking->reference . ' ' . ticketly_money($request->refund_amount));

    //         return back()->with('success', 'Refund of ' . ticketly_money($request->refund_amount) . ' processed successfully.');

    //     } catch (\Exception $e) {
    //         Log::error('[OrderController] Refund failed: ' . $e->getMessage());
    //         return back()->withErrors(['refund' => 'Stripe refund failed: ' . $e->getMessage()]);
    //     }
    // }

    private function exportCsv($bookings)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders-' . now()->format('Y-m-d-His') . '.csv"',
        ];

        $callback = function () use ($bookings) {
            $handle = fopen('php://output', 'w');
                fputcsv($handle, [
                    'Order ID', 'Reference', 'Customer Name', 'Customer Email',
                    'Event', 'Status', 'Subtotal', 'Discount', 'Portal Fee', 'Service Fee',
                    'Total', 'Date', 'Refund Amount',
                ]);
            foreach ($bookings as $b) {
                fputcsv($handle, [
                    $b->id,
                    $b->reference,
                    $b->customer_name,
                    $b->customer_email,
                    $b->event->title ?? '',
                    $b->status,
                    $b->subtotal,
                    $b->discount_amount,
                    $b->portal_fee,
                    $b->service_fee,
                    $b->total,
                    ticketly_format_date($b->created_at),
                    $b->refund_amount ?? '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
