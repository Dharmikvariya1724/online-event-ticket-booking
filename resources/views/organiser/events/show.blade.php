@extends('layouts.organiser')
@section('title', 'View Event')
@section('page-title', 'View Event')
@section('page-subtitle', $event->title)

@section('content')
@php
  $sameDayEvent = $event->starts_at->isSameDay($event->ends_at);
  $canPreview = $event->status === 'published' && $event->approval_status === 'approved';
  $showRejectedPublishState = !$event->isCancelled() && $event->approval_status === 'rejected';
@endphp
<div class="max-w-5xl space-y-6">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <a href="{{ route('organiser.events.index') }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-gray-400 hover:text-white transition-colors">
      <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-700 bg-transparent text-gray-400 transition-colors group-hover:border-gray-500">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6 9 12l6 6"></path>
        </svg>
      </span>
      <span>Back to events</span>
    </a>
    <div class="flex w-full flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center">
      @if($canPreview)
      <a href="{{ route('events.show', $event->slug) }}" target="_blank" class="text-xs font-semibold text-gray-400 border border-gray-700 px-3 py-2 rounded-lg hover:bg-gray-800 transition-colors">Preview ↗</a>
      @endif
      @if($showRejectedPublishState)
      <form action="{{ route('organiser.events.status', $event->id) }}" method="POST" class="w-full sm:w-auto">
        @csrf
        <input type="hidden" name="status" value="published">
        <button type="submit" disabled aria-disabled="true" class="w-full text-xs font-semibold text-red-400 border border-red-700/50 px-3 py-2 rounded-lg hover:bg-red-900/20 transition-colors sm:w-auto">Rejected</button>
      </form>
      @elseif(!$event->isCancelled() && $event->status !== 'published')
      <form action="{{ route('organiser.events.status', $event->id) }}" method="POST" class="w-full sm:w-auto">
        @csrf
        <input type="hidden" name="status" value="published">
        <button type="submit" class="w-full text-xs font-semibold text-emerald-400 border border-emerald-700/50 px-3 py-2 rounded-lg hover:bg-emerald-900/20 transition-colors sm:w-auto">Publish</button>
      </form>
      @endif
@if(!$event->isCancelled() && $event->status === 'published' && $event->approval_status !== 'rejected')
      <form action="{{ route('organiser.events.status', $event->id) }}" method="POST" class="w-full sm:w-auto">
        @csrf
        <input type="hidden" name="status" value="draft">
        <button type="submit" class="w-full text-xs font-semibold text-yellow-400 border border-yellow-700/50 px-3 py-2 rounded-lg hover:bg-yellow-900/20 transition-colors sm:w-auto">Move to Draft</button>
      </form>
      @endif
      <a href="{{ route('organiser.events.edit', $event->id) }}" class="inline-flex w-full items-center justify-center text-xs font-semibold text-indigo-400 border border-indigo-500/30 px-3 py-2 rounded-lg hover:bg-indigo-600/10 transition-colors sm:w-auto">Edit Event</a>
      <a href="{{ route('organiser.tiers.index', $event->id) }}" class="inline-flex w-full items-center justify-center text-xs font-semibold text-indigo-400 border border-indigo-500/30 px-3 py-2 rounded-lg hover:bg-indigo-600/10 transition-colors sm:w-auto">Ticket Tiers</a>
        <a href="{{ route('organiser.sponsorships.index', $event->id) }}" class="inline-flex w-full items-center justify-center text-xs font-semibold text-indigo-400 border border-indigo-500/30 px-3 py-2 rounded-lg hover:bg-indigo-600/10 transition-colors sm:w-auto">Manage Sponsorship</a>
      
      
      
      @if(!$event->isCancelled())
      <button type="button" onclick="document.getElementById('cancel-modal').classList.remove('hidden')" class="w-full text-xs font-semibold text-red-400 border border-red-700/50 px-3 py-2 rounded-lg hover:bg-red-900/20 transition-colors sm:w-auto">Cancel Event</button>
      @endif
      <form action="{{ route('organiser.events.destroy', $event->id) }}" method="POST" class="w-full sm:w-auto" data-confirm="Delete this event?">
        @csrf
        @method('DELETE')
        <button type="submit" class="w-full text-xs font-semibold text-red-400 border border-red-700/50 px-3 py-2 rounded-lg hover:bg-red-900/20 transition-colors sm:w-auto">Delete Event</button>
      </form>
      
    </div>
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="h-48 bg-gray-800 sm:h-64">
      @if($event->banner_url)
      <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
      @else
      <div class="w-full h-full" style="background:linear-gradient(135deg,#4f46e5,#7c3aed,#ec4899)"></div>
      @endif
    </div>
    <div class="p-4 sm:p-6 lg:p-7">
      <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <h1 class="text-2xl font-extrabold text-white">{{ $event->title }}</h1>
            <span class="badge {{ $event->status_badge['class'] }}">
              {{ $event->status_badge['label'] }}
            </span>
          </div>
          @if($event->short_description)
          <p class="text-sm text-gray-400 max-w-3xl">{{ $event->short_description }}</p>
          @endif
        </div>
        <div class="w-full">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="rounded-2xl bg-indigo-500/10 border border-indigo-500/20 px-4 py-4">
              <div class="text-[11px] font-medium text-gray-400 mb-1">Tickets sold</div>
              <div class="text-2xl font-extrabold text-white leading-none">{{ number_format($event->sold_tickets ?? 0) }}</div>
              <div class="text-xs text-gray-500 mt-2">Confirmed paid bookings</div>
            </div>
            <div class="rounded-2xl bg-indigo-500/10 border border-indigo-500/20 px-4 py-4">
              <div class="text-[11px] font-medium text-gray-400 mb-1">Tickets left</div>
              <div class="text-2xl font-extrabold text-white leading-none">{{ number_format(max(($event->total_capacity ?? 0) - ($event->sold_tickets ?? 0), 0)) }}</div>
              <div class="text-xs text-gray-500 mt-2">Remaining from {{ number_format($event->total_capacity ?? 0) }} total</div>
            </div>
            <div class="rounded-2xl bg-indigo-500/10 border border-indigo-500/20 px-4 py-4">
              <div class="text-[11px] font-medium text-gray-400 mb-1">Revenue</div>
              <div class="text-2xl font-extrabold text-indigo-400 leading-none">{{ ticketly_currency_symbol() }}{{ number_format($event->total_revenue ?? 0, 0) }}</div>
              <div class="text-xs text-gray-500 mt-2">Total paid revenue generated</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-gray-800/50 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-bold text-white mb-4">Event Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div>
                <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">Category</div>
                <div class="text-white">{{ $event->category }}</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">Organiser</div>
                <div class="text-white">{{ $event->organiser->name ?? $event->organiser->company_name ?? 'N/A' }}</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">Start Date</div>
                <div class="text-white">{{ ticketly_format_date($event->starts_at) }}</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">Start Time</div>
                <div class="text-white">{{ ticketly_format_time($event->starts_at) }}</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">End Date</div>
                <div class="text-white">{{ $sameDayEvent ? 'Same day' : ticketly_format_date($event->ends_at) }}</div>
              </div>
              
              <div>
                <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">End Time</div>
                <div class="text-white">{{ ticketly_format_time($event->ends_at) }}</div>
              </div>
              <div>
                <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">Venue</div>
                <div class="text-white">{{ $event->venue_name }}</div>
              </div>
              <div class="sm:col-span-2">
                <div class="text-gray-500 text-xs uppercase tracking-wide mb-1">Address</div>
                <div class="text-white">{{ $event->venue_address }}, {{ $event->city }}@if($event->postcode), {{ $event->postcode }}@endif</div>
              </div>
            </div>
          </div>

          @if($event->description)
          <div class="bg-gray-800/50 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-bold text-white mb-4">Description</h2>
            <div class="prose prose-invert max-w-none text-sm text-gray-300">{!! $event->description !!}</div>
          </div>
          @endif

          @if(!empty($event->performer_lineup))
          <div class="bg-gray-800/50 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-bold text-white mb-4">Performer Lineup</h2>
            <div class="space-y-3">
              @foreach($event->performer_lineup as $performer)
              <div class="flex flex-wrap items-center justify-between gap-3 border border-gray-800 rounded-xl px-4 py-3">
                <div>
                  <div class="text-sm font-semibold text-white">{{ $performer['name'] ?? '' }}</div>
                  @if(!empty($performer['role']))
                  <div class="text-xs text-gray-500">{{ $performer['role'] }}</div>
                  @endif
                </div>
                @if(!empty($performer['time']))
                <div class="text-xs font-semibold text-indigo-400">{{ $performer['time'] }}</div>
                @endif
              </div>
              @endforeach
            </div>
          </div>
          @endif
        </div>

        <div class="space-y-6">
          <div class="bg-gray-800/50 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-bold text-white mb-4">Ticket Tiers</h2>
            @if($event->ticketTiers->isNotEmpty())
            <div class="space-y-3">
              @foreach($event->ticketTiers as $tier)
              <div class="border border-gray-800 rounded-xl p-4">
                <div class="flex items-start justify-between gap-3 mb-2">
                  <div class="text-sm font-semibold text-white">{{ $tier->name }}</div>
                  <div class="text-sm font-bold text-indigo-400">{{ $tier->price == 0 ? 'Free' : ticketly_money($tier->price) }}</div>
                </div>
                @if($tier->description)
                <div class="text-xs text-gray-500 mb-3">{{ $tier->description }}</div>
                @endif
                <div class="flex items-center justify-between text-xs text-gray-500">
                  @php
                    $tierSold = (int) ($tier->paid_sold_quantity ?? 0);
                    $tierLeft = max(((int) $tier->total_quantity) - $tierSold, 0);
                  @endphp
                  <span>{{ number_format($tierSold) }} sold</span>
                  <span>{{ number_format($tierLeft) }} left</span>
                </div>
              </div>
              @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">No ticket tiers added yet.</p>
            @endif
          </div>

          @if($event->parking_info)
          <div class="bg-gray-800/50 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-bold text-white mb-4">Parking / Transport</h2>
            <p class="text-sm text-gray-300 whitespace-pre-line">{{ $event->parking_info }}</p>
          </div>
          @endif

          @if($event->refund_policy)
          <div class="bg-gray-800/50 border border-gray-800 rounded-2xl p-5">
            <h2 class="text-base font-bold text-white mb-4">Refund Policy</h2>
            <div class="prose prose-invert max-w-none text-sm text-gray-300">{!! $event->refund_policy !!}</div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@if(!$event->isCancelled())
<div id="cancel-modal" class="hidden fixed inset-0 z-50 bg-gray-950/90 flex items-center justify-center px-4">
  <div class="bg-gray-900 border border-red-800/60 rounded-2xl p-8 max-w-md w-full">
    <h3 class="text-lg font-extrabold text-white mb-2">Cancel This Event?</h3>
    <p class="text-gray-400 text-sm mb-5">All ticket holders will be notified by email and SMS. All paid bookings will be marked for refund.</p>
    <form action="{{ route('organiser.events.status', $event->id) }}" method="POST">
      @csrf
      <input type="hidden" name="status" value="cancelled">
      <div class="mb-4">
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Reason for Cancellation *</label>
        <textarea name="cancellation_reason" rows="4" required minlength="10" maxlength="1000"
                  class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Please explain why this event is being cancelled..."></textarea>
      </div>
      <div class="flex gap-3">
        <button type="submit" class="flex-1 bg-red-700 hover:bg-red-600 text-white font-bold py-3 rounded-xl text-sm transition-colors" style="color:#ffffff !important;">Confirm Cancellation</button>
        <button type="button" onclick="document.getElementById('cancel-modal').classList.add('hidden')" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 rounded-xl text-sm transition-colors">Go Back</button>
      </div>
    </form>
  </div>
</div>
@endif
@endsection
