@extends('layouts.admin')

@section('title', 'Events')
@section('page-title', 'Events')
@section('page-subtitle', 'Approve, edit, and monitor events')

@section('content')
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
  <form method="GET" class="flex flex-col md:flex-row md:flex-wrap gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, city, venue"
      class="flex-1 min-w-[220px] bg-gray-800 border border-gray-700 rounded-xl px-4 py-2 text-sm text-white">
    <select name="organiser_id" class="min-w-[200px] bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Organisers</option>
      @foreach($organisers as $org)
        <option value="{{ $org->id }}" @selected(request('organiser_id')==$org->id)>{{ $org->name }}</option>
      @endforeach
    </select>
    <select name="approval_status" class="min-w-[170px] bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Approval</option>
      <option value="pending" @selected(request('approval_status')==='pending')>Pending</option>
      <option value="approved" @selected(request('approval_status')==='approved')>Approved</option>
      <option value="rejected" @selected(request('approval_status')==='rejected')>Rejected</option>
    </select>
    <select name="status" class="min-w-[150px] bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-sm text-white">
      <option value="">All Status</option>
      <option value="draft" @selected(request('status')==='draft')>Draft</option>
      <option value="published" @selected(request('status')==='published')>Published</option>
      <option value="cancelled" @selected(request('status')==='cancelled')>Cancelled</option>
    </select>
    <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold whitespace-nowrap">Search</button>
    <a href="{{ route('admin.events.index') }}" class="px-4 py-2 rounded-xl bg-gray-700 text-white text-sm font-semibold text-center whitespace-nowrap">Clear</a>
  </form>

  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-gray-400 text-xs uppercase">
        <tr>
          <th class="text-left py-3">Event</th>
          <th class="text-left py-3">Organiser</th>
          <th class="text-left py-3">Tickets Sold</th>
          <th class="text-left py-3">Approval</th>
          <th class="text-left py-3">Status</th>
          <th class="text-right py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        @forelse($events as $event)
        <tr>
          <td class="py-3">
            <div class="text-white font-semibold">{{ $event->title }}</div>
            <div class="text-xs text-gray-500">{{ ticketly_format_date($event->starts_at) }}</div>
          </td>
          <td class="py-3 text-gray-300">{{ $event->organiser?->name }}</td>
          <td class="py-3 text-gray-300">
            @php
              $sold = (int) ($event->sold_tickets ?? 0);
              $total = (int) ($event->total_capacity ?? 0);
            @endphp
            {{ number_format($sold) }}@if($total > 0) / {{ number_format($total) }}@endif
          </td>
          <td class="py-3">
            <span class="badge {{ $event->approval_status === 'approved' ? 'badge--positive' : ($event->approval_status === 'rejected' ? 'badge--danger' : 'badge--warning') }}">
              {{ ucfirst($event->approval_status ?? 'pending') }}
            </span>
          </td>
          <td class="py-3">
            @php($statusBadge = $event->status_badge)
            <span class="badge {{ $statusBadge['class'] }}">{{ $statusBadge['label'] }}</span>
          </td>
          <td class="py-3 text-right space-x-2">
            <a class="text-emerald-400 hover:text-emerald-300" href="{{ route('admin.events.show', $event->id) }}">View</a>
            <a class="text-sky-400 hover:text-sky-300" href="{{ route('admin.events.edit', $event->id) }}">Edit</a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="py-6 text-center text-gray-500">No events found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $events->links() }}</div>
</div>
@endsection
