@extends('layouts.organiser')
@section('title', 'Orders')
@section('page-title', 'Orders')
@section('page-subtitle', number_format($stats['total']) . ' paid orders · ' . ticketly_money($stats['revenue']) . ' total revenue')

@section('content')

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
  <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference, name or email..."
         class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 flex-1 min-w-[200px]">
  <select name="event_id" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <option value="">All Events</option>
    @foreach($events as $event)
    <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>{{ Str::limit($event->title, 40) }}</option>
    @endforeach
  </select>
  <select name="status" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
    <option value="">All Statuses</option>
    @foreach(['paid'=>'Paid','refunded'=>'Refunded','cancelled'=>'Cancelled','failed'=>'Failed'] as $v => $l)
    <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
    @endforeach
  </select>
  <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
  <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
  <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl">Search</button>
  @if(request()->hasAny(['search','event_id','status','date_from','date_to']))
  <a href="{{ route('organiser.orders.index') }}" class="bg-gray-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-gray-600">Clear</a>
  @endif
  <a href="{{ request()->fullUrlWithQuery(['export'=>'csv']) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl flex items-center gap-2">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
    Export CSV
  </a>
</form>

{{-- Table --}}
<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="bg-gray-800/60">
        <tr>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Reference</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Customer</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Event</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Date</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
          <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Amount</th>
          <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        @forelse($orders as $order)
        @php
          $ticketCount = max(1, (int) ($order->total_tickets ?? 0));
          $badgeVariant = match($order->status) {
            'paid' => 'badge--positive',
            'refunded', 'partially_refunded' => 'badge--accent',
            'cancelled', 'failed' => 'badge--danger',
            default => 'badge--neutral',
          };
        @endphp
        <tr class="hover:bg-gray-800/30 transition-colors">
          <td class="px-4 py-3">
            <span class="text-xs font-mono font-bold text-indigo-400">{{ $order->reference }}</span>
          </td>
          <td class="px-4 py-3">
            <div class="text-sm font-medium text-white">{{ $order->customer_name }}</div>
            <div class="text-xs text-gray-500">{{ $order->customer_email }}</div>
          </td>
          <td class="px-4 py-3 text-sm text-gray-300 max-w-[180px]">
            <div class="truncate">{{ $order->event->title ?? '—' }}</div>
          </td>
          <td class="px-4 py-3 text-xs text-gray-400">{{ ticketly_format_date($order->created_at) }}</td>
          <td class="px-4 py-3">
            <span class="badge {{ $badgeVariant }}">
              {{ $order->status_badge['label'] }}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <div class="font-bold text-white text-sm">{{ ticketly_money($order->total) }}</div>
            <div class="text-xs text-gray-500">{{ $ticketCount }} {{ $ticketCount === 1 ? 'ticket' : 'tickets' }}</div>
          </td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('organiser.orders.show', $order->id) }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">View</a>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-12 text-gray-500 text-sm">No orders found</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $orders->links() }}</div>
@endsection
