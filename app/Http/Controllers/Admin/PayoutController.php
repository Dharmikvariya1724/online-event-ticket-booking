<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Organiser;
use App\Models\Payout;
use App\Services\ServiceFeeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Payout as StripePayout;
use Stripe\Stripe;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $settlementDays = (int) config('ticketly.settlement_days', 0);
        $cutoff = now()->subDays($settlementDays)->endOfDay();
        $paidStatuses = ['paid', 'partially_refunded'];

        $eligible = Booking::query()
            ->join('events', 'events.id', '=', 'bookings.event_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->where('bookings.created_at', '<=', $cutoff)
            ->selectRaw('events.organiser_id, SUM(bookings.subtotal - bookings.discount_amount) as eligible_amount')
            ->groupBy('events.organiser_id')
            ->get()
            ->keyBy('organiser_id');

        $pending = Booking::query()
            ->join('events', 'events.id', '=', 'bookings.event_id')
            ->whereIn('bookings.status', $paidStatuses)
            ->where('bookings.created_at', '>', $cutoff)
            ->selectRaw('events.organiser_id, SUM(bookings.subtotal - bookings.discount_amount) as pending_amount')
            ->groupBy('events.organiser_id')
            ->get()
            ->keyBy('organiser_id');

        $paidOut = Payout::query()
            ->selectRaw('user_id, SUM(amount) as paid_amount')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $organisers = Organiser::orderBy('company_name')->get()->map(function ($org) use ($eligible, $pending, $paidOut) {
            $eligibleAmount = (float) ($eligible[$org->id]->eligible_amount ?? 0);
            $pendingAmount = (float) ($pending[$org->id]->pending_amount ?? 0);
            $paidAmount = (float) ($paidOut[$org->id]->paid_amount ?? 0);
            $availableAmount = max(0.0, $eligibleAmount - $paidAmount);

            $org->eligible_amount = $eligibleAmount;
            $org->pending_amount = $pendingAmount;
            $org->paid_amount = $paidAmount;
            $org->available_amount = $availableAmount;
            return $org;
        });

        $payouts = Payout::with('organiser')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payouts.index', compact('organisers', 'payouts', 'settlementDays'));
    }

    public function trigger(Request $request, int $organiserId)
    {
        $organiser = Organiser::findOrFail($organiserId);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = (float) $validated['amount'];
        $currency = strtolower(ticketly_currency());

        $settlementDays = (int) config('ticketly.settlement_days', 0);
        $cutoff = now()->subDays($settlementDays)->endOfDay();
        $eligible = Booking::query()
            ->join('events', 'events.id', '=', 'bookings.event_id')
            ->where('events.organiser_id', $organiser->id)
            ->whereIn('bookings.status', ['paid', 'partially_refunded'])
            ->where('bookings.created_at', '<=', $cutoff)
            ->sum(DB::raw('bookings.subtotal - bookings.discount_amount'));

        $paidAmount = Payout::where('user_id', $organiser->id)->sum('amount');
        $availableAmount = max(0.0, (float) $eligible - (float) $paidAmount);

        if ($amount > $availableAmount) {
            return back()->withErrors(['payout' => 'Requested amount exceeds available balance.']);
        }

        if ($organiser->stripe_account_id) {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $stripePayout = StripePayout::create([
                    'amount' => ServiceFeeCalculator::toPence($amount),
                    'currency' => $currency,
                ], ['stripe_account' => $organiser->stripe_account_id]);

                Payout::create([
                    'user_id' => $organiser->id,
                    'stripe_payout_id' => $stripePayout->id,
                    'amount' => $amount,
                    'currency' => strtoupper($currency),
                    'status' => $stripePayout->status ?? 'pending',
                ]);

                return back()->with('success', 'Stripe payout initiated successfully.');
            } catch (\Exception $e) {
                return back()->withErrors(['payout' => 'Stripe payout failed: ' . $e->getMessage()]);
            }
        }

        Payout::create([
            'user_id' => $organiser->id,
            'stripe_payout_id' => 'manual-' . now()->format('YmdHis'),
            'amount' => $amount,
            'currency' => strtoupper($currency),
            'status' => 'manual',
        ]);

        return back()->with('success', 'Manual payout recorded.');
    }
}
