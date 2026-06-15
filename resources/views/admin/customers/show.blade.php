@extends('layouts.admin')

@section('title', 'Customer Details')
@section('page-title', 'Customer')
@section('page-subtitle', $customer->email)

@section('content')
<div class="grid gap-6">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="text-lg font-bold text-white">{{ $customer->name ?? 'Guest Customer' }}</div>
      <div class="text-sm text-gray-400">{{ $customer->email }}</div>
      <div class="text-sm text-gray-500">{{ $customer->phone ?? 'No phone on file' }}</div>
      <div class="mt-2">
        @if($customer->is_suspended)
          <span class="badge badge--danger">Suspended</span>
        @else
          <span class="badge badge--positive">Active</span>
        @endif
      </div>
    </div>
    <div class="flex flex-wrap gap-2">
      @if($customer->is_suspended)
      <form method="POST" action="{{ route('admin.customers.activate', $customer->id) }}" data-confirm="Activate this customer account?">
        @csrf
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Activate</button>
      </form>
      @else
      <form method="POST" action="{{ route('admin.customers.suspend', $customer->id) }}" data-confirm="Suspend this customer account?">
        @csrf
        <button class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold">Suspend</button>
      </form>
      @endif
      <!-- <form method="POST" action="{{ route('admin.customers.destroy', $customer->id) }}">
        @csrf
        @method('DELETE')
        <button class="px-4 py-2 rounded-xl border border-gray-700 text-gray-300 text-sm">Delete</button>
      </form> -->
    </div>
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-white mb-4">Purchase History</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-gray-400 text-xs uppercase">
          <tr>
            <th class="text-left py-3">Reference</th>
            <th class="text-left py-3">Event</th>
            <th class="text-left py-3">Total</th>
            <th class="text-left py-3">Status</th>
            <th class="text-right py-3">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          @forelse($bookings as $booking)
          <tr>
            <td class="py-3 text-white font-semibold">{{ $booking->reference }}</td>
            <td class="py-3 text-gray-300">{{ $booking->event?->title }}</td>
            <td class="py-3 text-gray-300">{{ ticketly_money($booking->total) }}</td>
            <td class="py-3 text-gray-400">{{ ucfirst($booking->status) }}</td>
            <td class="py-3 text-gray-500 text-right">{{ ticketly_format_date($booking->created_at) }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="py-6 text-center text-gray-500">No purchases found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $bookings->links() }}</div>
  </div>
</div>
@endsection
