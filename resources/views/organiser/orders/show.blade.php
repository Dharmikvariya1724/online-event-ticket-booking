@extends('layouts.organiser')
@section('title', 'Order – ' . $booking->reference)
@section('page-title', 'Order Details')
@section('page-subtitle', $booking->reference)

@section('head')
<style>
  :root[data-theme='light'] .organiser-refund-history-card,
  :root[data-theme='light'] .organiser-refund-history-entry {
    background: #ffffff !important;
    border-color: var(--surface-border) !important;
    box-shadow: var(--light-shadow-sm);
  }

  :root[data-theme='light'] .organiser-refund-history-card,
  :root[data-theme='light'] .organiser-refund-history-card * {
    color: #0f172a !important;
  }
</style>
@endsection

@section('content')
@php
  $portalFeePercentage = ticketly_format_percentage(ticketly_setting('portal_fee_percentage', config('ticketly.portal_fee_percentage', 10)));
  $serviceFeePercentage = ticketly_format_percentage(ticketly_setting('service_fee_percentage', config('ticketly.service_fee_percentage', 5)));
  $promoDiscountLabel = 'Discount';
  if ($booking->promoCode) {
    $promoValue = $booking->promoCode->type === 'percentage'
      ? ticketly_format_percentage($booking->promoCode->value) . '%'
      : ticketly_money($booking->promoCode->value);
    $promoDiscountLabel .= ' (' . $booking->promoCode->code . ' - ' . $promoValue . ')';
  }
@endphp
<div class="max-w-2xl">

  <div class="flex items-center gap-3 mb-5">
    <a href="{{ route('organiser.orders.index') }}" class="text-sm text-gray-400 hover:text-white">← All Orders</a>
    <span class="badge {{ $booking->status_badge['class'] }}">
      {{ $booking->status_badge['label'] }}
    </span>
  </div>

  {{-- Customer + Event --}}
  <div class="organiser-refund-history-card bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
    <div class="grid grid-cols-2 gap-4">
      @foreach([
        ['Reference', $booking->reference],
        ['Event', $booking->event->title],
        ['Customer', $booking->customer_name],
        ['Email', $booking->customer_email],
        ['Phone', $booking->customer_phone ?? '—'],
        ['Date', ticketly_format_datetime($booking->created_at)],
      ] as [$label, $value])
      <div>
        <div class="text-xs text-gray-500 mb-0.5">{{ $label }}</div>
        <div class="text-sm font-semibold text-white">{{ $value }}</div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Items --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden mb-4">
    <div class="px-5 py-3 border-b border-gray-800"><h3 class="font-bold text-white text-sm">Tickets</h3></div>
    <div class="p-5 space-y-2">
      @foreach($booking->items as $item)
      <div class="flex justify-between text-sm">
        <span class="text-gray-300">{{ $item->ticketTier->name }} × {{ $item->quantity }}</span>
        <span class="font-bold text-white">{{ ticketly_money($item->subtotal) }}</span>
      </div>
      @endforeach
      <div class="border-t border-gray-800 pt-3 space-y-1.5 mt-3">
        <div class="flex justify-between text-sm text-gray-400"><span>Subtotal</span><span>{{ ticketly_money($booking->subtotal) }}</span></div>
        <div class="flex justify-between text-sm text-gray-400"><span>Portal Fee ({{ $portalFeePercentage }}%)</span><span>{{ ticketly_money($booking->portal_fee ?? 0) }}</span></div>
        <div class="flex justify-between text-sm text-gray-400"><span>Service Fee ({{ $serviceFeePercentage }}%)</span><span>{{ ticketly_money($booking->service_fee) }}</span></div>
        @if($booking->discount_amount > 0)
        <div class="flex justify-between text-sm text-emerald-400"><span>{{ $promoDiscountLabel }}</span><span>-{{ ticketly_money($booking->discount_amount) }}</span></div>
        @endif
        <div class="flex justify-between font-extrabold text-white text-base pt-2 border-t border-gray-700"><span>Total</span><span class="text-indigo-400">{{ ticketly_money($booking->total) }}</span></div>
      </div>
    </div>
  </div>

  @php
    $refundTotal = round((float) ($booking->refund_amount ?? 0), 2);
    $refundTransactions = $booking->refundTransactions;
    if ($refundTransactions->isEmpty() && ($refundTotal > 0 || $booking->refunded_at || $booking->refund_reason)) {
      $fallbackOriginalTotal = $booking->isPartiallyRefunded()
        ? round($refundTotal + (float) $booking->total, 2)
        : max(round((float) $booking->total, 2), $refundTotal);
      $fallbackRemainingTotal = $booking->isFullyRefunded()
        ? 0.0
        : max(round($fallbackOriginalTotal - $refundTotal, 2), 0.0);

      $refundTransactions = collect([
        (object) [
          'original_total' => $fallbackOriginalTotal,
          'refunded_amount' => $refundTotal,
          'remaining_total' => $fallbackRemainingTotal,
          'refunded_at' => $booking->refunded_at,
          'reason' => $booking->refund_reason,
        ],
      ]);
    }
    $hasRefund = $refundTransactions->isNotEmpty();
    $remainingTotal = $hasRefund
      ? max((float) $refundTransactions->first()->remaining_total, 0)
      : max((float) $booking->total, 0);
    $refundStatus = $booking->isFullyRefunded()
      ? 'Full refund'
      : ($booking->isPartiallyRefunded() ? 'Partial refund' : ucfirst($booking->status));
  @endphp
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 mb-4">
    <h3 class="font-bold text-white text-sm mb-4">Refund History</h3>
    @if($hasRefund)
      <div class="space-y-2 text-sm">
        <div class="flex justify-between text-gray-300">
          <span>Status</span>
          <span>{{ $refundStatus }}</span>
        </div>
        <div class="flex justify-between text-gray-300">
          <span>Total Refunded</span>
          <span>{{ ticketly_money($refundTotal) }}</span>
        </div>
        <div class="flex justify-between text-gray-300">
          <span>Current Remaining</span>
          <span>{{ ticketly_money($remainingTotal) }}</span>
        </div>
      </div>

      <div class="mt-4 space-y-3">
        @foreach($refundTransactions as $refund)
        <div class="organiser-refund-history-entry rounded-xl border border-gray-800 bg-gray-950/60 p-4">
          <div class="flex items-center justify-between gap-3 text-xs uppercase tracking-wide text-gray-500">
            <span>Refund {{ $loop->iteration }}</span>
            <span>{{ $refund->refunded_at ? ticketly_format_datetime($refund->refunded_at) : 'Timestamp unavailable' }}</span>
          </div>
          <div class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between text-gray-300">
              <span>Original Total</span>
              <span>{{ ticketly_money($refund->original_total) }}</span>
            </div>
            <div class="flex justify-between text-gray-300">
              <span>Refunded Amount</span>
              <span>{{ ticketly_money($refund->refunded_amount) }}</span>
            </div>
            <div class="flex justify-between text-gray-300">
              <span>Remaining Amount</span>
              <span>{{ ticketly_money($refund->remaining_total) }}</span>
            </div>
          </div>
          @if($refund->reason)
          <div class="pt-3 text-xs text-gray-400">
            <span class="block uppercase mb-1 text-[11px] tracking-wide">Reason</span>
            <span class="text-gray-300">{{ $refund->reason }}</span>
          </div>
          @endif
        </div>
        @endforeach
      </div>
    @else
      <p class="text-sm text-gray-400">No refunds recorded for this order.</p>
    @endif
  </div>

  {{-- Refund 
  @if($booking->isPaid())
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h3 class="font-bold text-white text-sm mb-4">Issue Refund</h3>
    @if($errors->has('refund'))<div class="bg-red-900/40 border border-red-700/50 rounded-xl p-3 mb-4 text-red-300 text-sm">{{ $errors->first('refund') }}</div>@endif
    <form action="{{ route('organiser.orders.refund', $booking->id) }}" method="POST" class="space-y-4">
      @csrf
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Refund Amount ({{ ticketly_currency_symbol() }})</label>
          <input type="number" name="refund_amount" step="0.01" min="0.01" max="{{ $booking->total }}"
                 value="{{ old('refund_amount', $booking->total) }}"
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <p class="text-xs text-gray-600 mt-1">Max: {{ ticketly_money($booking->total) }}</p>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Reason *</label>
          <input type="text" name="refund_reason" required maxlength="500"
                 class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Event cancelled, etc.">
        </div>
      </div>
      <button type="submit" onclick="return confirm('Process this refund via Stripe?')"
              class="bg-red-700 hover:bg-red-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-colors">Process Refund</button>
    </form>
  </div>
  @elseif($booking->isRefunded())
  <div class="bg-emerald-900/30 border border-emerald-700/50 rounded-2xl p-5">
    <p class="text-emerald-300 text-sm">✅ Refund of {{ ticketly_money($booking->refund_amount) }} processed on {{ ticketly_format_datetime($booking->refunded_at) }}</p>
    @if($booking->refund_reason)<p class="text-emerald-200/60 text-xs mt-1">Reason: {{ $booking->refund_reason }}</p>@endif
  </div>
  @endif --}}
</div>
@endsection
