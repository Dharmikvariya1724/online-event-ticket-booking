@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Platform overview')

@section('content')
<div class="grid gap-6">
  <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <!-- <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="text-xs uppercase tracking-wider text-gray-500">Total Customers</div>
      <div class="mt-2 text-2xl font-extrabold text-white">{{ number_format($totalCustomers) }}</div>
    </div> -->
    <!-- <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="text-xs uppercase tracking-wider text-gray-500">Total Organisers</div>
      <div class="mt-2 text-2xl font-extrabold text-white">{{ number_format($totalOrganisers) }}</div>
    </div> -->
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="text-xs uppercase tracking-wider text-gray-500">Today Events</div>
      <div class="mt-2 text-2xl font-extrabold text-white">{{ number_format($totalEvents) }}</div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="text-xs uppercase tracking-wider text-gray-500">Today Tickets Sold</div>
      <div class="mt-2 text-2xl font-extrabold text-white">{{ number_format($totalTicketsSold) }}</div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="text-xs uppercase tracking-wider text-gray-500">Today Revenue</div>
      <div class="mt-2 text-2xl font-extrabold text-white">{{ ticketly_money($totalRevenue) }}</div>
    </div>
    <!-- <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="text-xs uppercase tracking-wider text-gray-500">Payouts</div>
      <div class="mt-2 text-2xl font-extrabold text-white">{{ ticketly_money($totalPayouts) }}</div>
    </div> -->
  </section>

  <!-- <section class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-white mb-4">System Tasks</h2>
    <p class="text-xs text-gray-500 mb-4">You can manually trigger scheduled tasks here.</p> 
    <form method="POST" action="{{ route('admin.dashboard.run-tasks') }}" class="inline">
      @csrf
      <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
        Run Scheduled Tasks
      </button>
    </form>
  </section> -->

  <section class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-white">Recent Events</h2>
        <a class="text-xs text-emerald-400 hover:text-emerald-300" href="{{ route('admin.events.index') }}">View all</a>
      </div>
      <div class="mt-4 space-y-3">
        @forelse($recentEvents as $event)
        <a href="{{ route('admin.events.show', $event->id) }}" class="flex items-center justify-between gap-3 border border-gray-800 rounded-xl p-3 hover:border-gray-700 transition">
          <div>
            <div class="text-sm font-semibold text-white">{{ $event->title }}</div>
            <div class="text-xs text-gray-500">{{ $event->organiser?->name ?? 'Organiser' }} ·  {{ ticketly_format_date($event->starts_at) }}</div>
          </div>
          <span class="badge {{ $event->approval_status === 'approved' ? 'badge--positive' : ($event->approval_status === 'rejected' ? 'badge--danger' : 'badge--warning') }}">
            {{ ucfirst($event->approval_status ?? 'pending') }}
          </span>
        </a>
        @empty
        <div class="text-sm text-gray-500">No recent events.</div>
        @endforelse
      </div>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-semibold text-white">Recent Ticket Purchases</h2>
        <a class="text-xs text-emerald-400 hover:text-emerald-300" href="{{ route('admin.orders.index') }}">View all</a>
      </div>
      <div class="mt-4 space-y-3">
        @forelse($recentBookings as $booking)
        @if($booking)
        <a href="{{ route('admin.orders.show', $booking->id) }}" class="flex items-center justify-between gap-3 border border-gray-800 rounded-xl p-3 hover:border-gray-700 transition">
        @else
        <div class="flex items-center justify-between gap-3 border border-gray-800 rounded-xl p-3">
        @endif
          <div>
            <div class="text-sm font-semibold text-white">{{ $booking->reference }}</div>
            <div class="text-xs text-gray-500">{{ $booking->customer_name }} · {{ $booking->event?->title }}</div>
          </div>
          <div class="text-right">
            <div class="text-sm font-semibold text-white">{{ ticketly_money($booking->total) }}</div>
            <div class="text-xs text-gray-500">{{ $booking->status_badge['label'] }}</div>
          </div>
        @if($booking)
        </a>
        @else
        </div>
        @endif
        @empty
        <div class="text-sm text-gray-500">No recent purchases.</div>
        @endforelse
      </div>
    </div>
  </section>
</div>
@endsection
