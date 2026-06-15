@extends('layouts.organiser')
@section('title', 'Payouts')
@section('page-title', 'Payouts')
@section('page-subtitle', 'Estimated settlement same day for all payments')

@section('content')

<div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-6">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <div>
      <div class="text-xs text-gray-500 mb-1">Stripe Connect</div>
      <div class="text-sm text-gray-300">
        {{ $organiser->stripe_account_id ? 'Connected' : 'Not Connected' }}
        @if($organiser->stripe_account_id)
          <span class="text-gray-500 ml-2">({{ $organiser->stripe_onboarding_complete ? 'Onboarding complete' : 'Onboarding incomplete' }})</span>
        @endif
      </div>
    </div>
    @if(!$organiser->stripe_account_id || !$organiser->stripe_onboarding_complete)
      <a href="{{ route('organiser.stripe.connect') }}"
         class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-indigo-700">
        Connect with Stripe
      </a>
    @endif
  </div>
  <div class="mt-3 text-xs text-gray-500">
    Account ID: {{ $organiser->stripe_account_id ?? '—' }}
  </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <div class="text-xs text-gray-500 mb-1">
      {{ $canReceivePayouts ? 'Paid Out' : 'Paid Out (available after Stripe setup)' }}
    </div>
    <div class="text-2xl font-extrabold text-white">{{ ticketly_money_code($summary['paid_out_amount']) }}</div>
  </div>
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <div class="text-xs text-gray-500 mb-1">Pending</div>
    <div class="text-2xl font-extrabold text-white">{{ ticketly_money_code($summary['pending_amount']) }}</div>
  </div>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-5">
  <select name="event_id" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <option value="">All Events</option>
    @foreach($events as $event)
    <option value="{{ $event->id }}" {{ (string) $selectedEventId === (string) $event->id ? 'selected' : '' }}>{{ Str::limit($event->title, 40) }}</option>
    @endforeach
  </select>

  <select name="status" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <option value="">All Statuses</option>
    @if($canReceivePayouts)
    <option value="paid_out" {{ request('status') === 'paid_out' ? 'selected' : '' }}>Paid Out</option>
    @endif
    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
  </select>

  <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
  <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">

  <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl">Search</button>

  @if(request()->hasAny(['event_id','status','date_from','date_to']))
  <a href="{{ route('organiser.payouts.index') }}" class="bg-gray-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-gray-600">Clear</a>
  @endif
</form>

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="bg-gray-800/60">
        <tr>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Payout Date</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Event</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
          <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Orders</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        @forelse($payouts as $payout)
        <tr class="hover:bg-gray-800/30 transition-colors">
          <td class="px-4 py-3 text-sm text-gray-200">{{ ticketly_format_date($payout->payout_date) }}</td>
          <td class="px-4 py-3 text-sm font-bold text-white">{{ ticketly_money_code((float) $payout->amount) }}</td>
          <td class="px-4 py-3 text-sm text-gray-300">{{ Str::limit($payout->event_title, 45) }}</td>
          <td class="px-4 py-3">
            <span class="badge {{ $payout->status_class }}">
              {{ $payout->status_label }}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('organiser.orders.index', ['event_id' => $payout->event_id, 'date_from' => $payout->source_date, 'date_to' => $payout->source_date]) }}"
               class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">
              {{ number_format((int) $payout->order_count) }} {{ (int) $payout->order_count === 1 ? 'order' : 'orders' }}
            </a>
          </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center py-12 text-gray-500 text-sm">No payout records found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $payouts->links() }}</div>
@endsection
