@extends('layouts.admin')

@section('title', 'Orders')
@section('page-title', 'Orders')
@section('page-subtitle', 'Ticket purchases and refunds')

@section('content')
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
  <form method="GET" class="flex flex-col md:flex-row md:flex-wrap gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reference or customer"
      class="flex-1 min-w-[220px] bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    <!-- <input type="text" name="customer_email" value="{{ request('customer_email') }}" placeholder="Customer email"
      class="min-w-[200px] bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white"> -->
    <select name="event_id" class="min-w-[200px] bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Events</option>
      @foreach($events as $event)
        <option value="{{ $event->id }}" @selected(request('event_id')==$event->id)>{{ $event->title }}</option>
      @endforeach
    </select>
    <select name="organiser_id" class="min-w-[200px] bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Organisers</option>
      @foreach($organisers as $org)
        <option value="{{ $org->id }}" @selected(request('organiser_id')==$org->id)>{{ $org->name }}</option>
      @endforeach
    </select>
    <select name="status" class="min-w-[160px] bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Status</option>
      <option value="paid" @selected(request('status')==='paid')>Paid</option>
      <option value="refunded" @selected(request('status')==='refunded')>Refunded</option>
      <option value="partially_refunded" @selected(request('status')==='partially_refunded')>Partial</option>
      <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
      <option value="failed" @selected(request('status')==='failed')>Failed</option>
    </select>
    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold whitespace-nowrap">Search</button>
    <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded-xl bg-gray-700 text-white text-sm font-semibold text-center whitespace-nowrap">Clear</a>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-gray-400 text-xs uppercase">
        <tr>
          <th class="text-left py-3">Reference</th>
          <th class="text-left py-3">Customer</th>
          <th class="text-left py-3">Event</th>
          <th class="text-left py-3">Total</th>
          <th class="text-left py-3">Status</th>
          <th class="text-right py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        @forelse($orders as $order)
        @php($ticketCount = max(1, (int) ($order->total_tickets ?? 0)))
        <tr>
          <td class="py-3 text-white font-semibold">{{ $order->reference }}</td>
          <td class="py-3 text-gray-300">{{ $order->customer_name }}</td>
          <td class="py-3 text-gray-400">{{ $order->event?->title }}</td>
          <td class="py-3">
            <div class="text-gray-300">{{ ticketly_money($order->total) }}</div>
            <div class="text-xs text-gray-500">{{ $ticketCount }} {{ $ticketCount === 1 ? 'ticket' : 'tickets' }}</div>
          </td>
          <td class="py-3 text-gray-400">{{ $order->status_badge['label'] }}</td>
          <td class="py-3 text-right">
            <a href="{{ route('admin.orders.show', $order->id) }}" class="text-emerald-400 hover:text-emerald-300">View</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="py-6 text-center text-gray-500">No orders found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection
