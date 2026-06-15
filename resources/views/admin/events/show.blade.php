@extends('layouts.admin')

@section('title', 'Event Details')
@section('page-title', 'Event')
@section('page-subtitle', $event->title)

@section('head')
<style>
  .admin-event-sponsor-initials {
    color: #d1d5db !important;
  }

  .admin-event-sponsor-name {
    color: #ffffff !important;
  }

  .admin-event-description,
  .admin-event-description * {
    max-width: 100%;
    overflow-wrap: anywhere;
    word-break: break-word;
  }

  .admin-event-description {
    min-width: 0;
  }

  .admin-event-description img,
  .admin-event-description video,
  .admin-event-description iframe,
  .admin-event-description table,
  .admin-event-description pre,
  .admin-event-description code {
    max-width: 100%;
  }

  .admin-event-description table {
    display: block;
    overflow-x: auto;
    white-space: normal;
  }

  .admin-event-description pre {
    white-space: pre-wrap;
  }
</style>
@endsection

@section('content')
@php
  $refundPolicySource = (string) ($event->refund_policy ?? '');
  $refundPolicyHtml = trim(strip_tags(html_entity_decode($refundPolicySource))) !== ''
      ? $refundPolicySource
      : '<p>Not specified.</p>';
@endphp
<div class="grid gap-6">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div>
      <div class="text-lg font-bold text-white">{{ $event->title }}</div>
      <div class="text-sm text-gray-400">{{ $event->organiser?->name }} · {{ $event->organiser?->company_name }} · {{ ticketly_format_datetime($event->starts_at) }}</div>
      <div class="mt-2 flex flex-wrap gap-2">
        <span class="badge {{ $event->approval_status === 'approved' ? 'badge--positive' : ($event->approval_status === 'rejected' ? 'badge--danger' : 'badge--warning') }}">
          {{ ucfirst($event->approval_status ?? 'pending') }}
        </span>
        <span class="badge badge--neutral">{{ ucfirst($event->status) }}</span>
      </div>
      @if($event->rejection_reason)
        <div class="mt-2 text-xs text-rose-300">Rejection: {{ $event->rejection_reason }}</div>
      @endif
    </div>
    <div class="flex flex-wrap gap-2">
      <a class="px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-semibold" href="{{ route('admin.events.edit', $event->id) }}">Edit</a>
      @if($event->approval_status !== 'approved')
      <form method="POST" action="{{ route('admin.events.approve', $event->id) }}" data-confirm="Approve this event?">
        @csrf
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Approve</button>
      </form>
      @endif
      @if($event->approval_status !== 'rejected')
      <form method="POST" action="{{ route('admin.events.reject', $event->id) }}" data-confirm="Reject this event?">
        @csrf
        <input type="hidden" name="rejection_reason" value="Event did not meet platform requirements.">
        <button class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold">Reject</button>
      </form>
      @endif
      @if($event->status !== 'cancelled')
      <form method="POST" action="{{ route('admin.events.cancel', $event->id) }}" data-confirm="Cancel this event? This will notify attendees and process refunds.">
        @csrf
        <input type="hidden" name="cancellation_reason" value="Event cancelled by admin.">
        <button class="px-4 py-2 rounded-xl bg-amber-600 text-white text-sm font-semibold">Cancel Event</button>
      </form>
      @endif
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="text-xs uppercase tracking-wider text-gray-500">Tickets Sold</div>
      @php
        $sold = (int) ($event->sold_tickets ?? 0);
        $capacity = (int) ($event->total_capacity ?? 0);
      @endphp
      <div class="mt-2 text-2xl font-extrabold text-white">
        {{ number_format($sold) }}@if($capacity > 0) / {{ number_format($capacity) }}@endif
      </div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="text-xs uppercase tracking-wider text-gray-500">Revenue</div>
      <div class="mt-2 text-2xl font-extrabold text-white">{{ ticketly_money($event->total_revenue ?? 0) }}</div>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <div class="text-xs uppercase tracking-wider text-gray-500">Capacity</div>
      <div class="mt-2 text-2xl font-extrabold text-white">{{ number_format($event->total_capacity ?? 0) }}</div>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-white mb-4">Event Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Date & Time</div>
            <div class="mt-1 text-white">{{ ticketly_format_datetime($event->starts_at) }} - {{ ticketly_format_datetime($event->ends_at) }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Category</div>
            <div class="mt-1 text-white">{{ $event->category ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Venue</div>
            <div class="mt-1 text-white">{{ $event->venue_name ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Address</div>
            <div class="mt-1 text-white">{{ $event->venue_address ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">City</div>
            <div class="mt-1 text-white">{{ $event->city ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Country</div>
            <div class="mt-1 text-white">{{ $event->country ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Postcode</div>
            <div class="mt-1 text-white">{{ $event->postcode ?? 'N/A' }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Pricing</div>
            @php
              $minPrice = $event->ticketTiers->min('price') ?? 0;
              $maxPrice = $event->ticketTiers->max('price') ?? 0;
            @endphp
            <div class="mt-1 text-white">
              @if($maxPrice <= 0)
                Free
              @elseif($minPrice == $maxPrice)
                {{ ticketly_money($minPrice) }}
              @else
                {{ ticketly_money($minPrice) }} - {{ ticketly_money($maxPrice) }}
              @endif
            </div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Created</div>
            <div class="mt-1 text-white">{{ ticketly_format_datetime($event->created_at) }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Last Updated</div>
            <div class="mt-1 text-white">{{ ticketly_format_datetime($event->updated_at) }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Featured</div>
            <div class="mt-1 text-white">{{ $event->is_featured ? 'Yes' : 'No' }}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Status</div>
            <div class="mt-1 text-white">{{ ucfirst($event->status) }}</div>
          </div>
        </div>
      </div>

      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-white mb-4">Description</h2>
        @if($event->short_description)
          <p class="text-sm text-gray-300 mb-3">{{ $event->short_description }}</p>
        @endif
        @if($event->description)
          <div class="admin-event-description text-sm text-gray-300">{!! $event->description !!}</div>
        @else
          <p class="text-sm text-gray-300">No description provided.</p>
        @endif
      </div>

      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-white mb-4">Policies & Info</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Refund Policy</div>
            <div class="admin-event-description mt-1 text-sm text-gray-300">{!! $refundPolicyHtml !!}</div>
          </div>
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Parking Info</div>
            <div class="mt-1 text-gray-300 whitespace-pre-line">{{ $event->parking_info ?? 'Not specified.' }}</div>
          </div>
          @if($event->status === 'cancelled')
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Cancellation Reason</div>
            <div class="mt-1 text-rose-300 whitespace-pre-line">{{ $event->cancellation_reason ?? 'Not specified.' }}</div>
          </div>
          @endif
          @if($event->rejection_reason)
          <div>
            <div class="text-xs uppercase tracking-wider text-gray-500">Rejection Reason</div>
            <div class="mt-1 text-rose-300 whitespace-pre-line">{{ $event->rejection_reason }}</div>
          </div>
          @endif
        </div>
      </div>

      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-white mb-4">Performer Details</h2>

        @if(empty($event->performer_lineup))
          <p class="text-sm text-gray-400">No performer details added for this event.</p>
        @else
          <div class="divide-y divide-gray-800">
            @foreach($event->performer_lineup as $performer)
              <div class="grid grid-cols-1 gap-4 py-4 first:pt-0 last:pb-0 md:grid-cols-3 text-sm">
                <div>
                  <div class="text-xs uppercase tracking-wider text-gray-500">Performer Name</div>
                  <div class="mt-1 font-semibold text-white">{{ $performer['name'] ?? 'N/A' }}</div>
                </div>
                <div>
                  <div class="text-xs uppercase tracking-wider text-gray-500">Role / Band</div>
                  <div class="mt-1 text-gray-300">{{ $performer['role'] ?? 'N/A' }}</div>
                </div>
                <div>
                  <div class="text-xs uppercase tracking-wider text-gray-500">Time</div>
                  <div class="mt-1 text-gray-300">{{ $performer['time'] ?? 'N/A' }}</div>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <div class="space-y-6">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-white mb-4">Organiser</h2>
        <div class="text-sm text-white font-semibold">{{ $event->organiser?->name ?? 'N/A' }}</div>
        <div class="text-sm text-gray-300">{{ $event->organiser?->company_name ?? 'N/A' }}</div>
        <div class="text-sm text-gray-400">{{ $event->organiser?->email ?? 'N/A' }}</div>
        <div class="text-xs text-gray-500 mt-2">Stripe: {{ $event->organiser?->stripe_account_id ? 'Connected' : 'Not connected' }}</div>
      </div>

      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-white mb-4">Approval</h2>
        <div class="text-sm text-white">Status: {{ ucfirst($event->approval_status ?? 'pending') }}</div>
        <div class="text-xs text-gray-500 mt-2">Approved at: {{ ticketly_format_datetime($event->approved_at, '-') }}</div>
        <div class="text-xs text-gray-500">Rejected at: {{ ticketly_format_datetime($event->rejected_at, '-') }}</div>
      </div>

      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
        <h2 class="text-sm font-semibold text-white mb-4">Banner</h2>
        @if($event->banner_url)
          <img src="{{ $event->banner_url }}" alt="Event banner" class="w-full rounded-xl border border-gray-800">
        @else
          <div class="text-sm text-gray-500">No banner uploaded.</div>
        @endif
      </div>
    </div>
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-white mb-4">Ticket Tiers</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-gray-400 text-xs uppercase">
          <tr>
            <th class="text-left py-3">Tier</th>
            <th class="text-left py-3">Price</th>
            <th class="text-left py-3">Total</th>
            <th class="text-left py-3">Available</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          @foreach($event->ticketTiers as $tier)
          <tr>
            <td class="py-3 text-white font-semibold">{{ $tier->name }}</td>
            <td class="py-3 text-gray-300">{{ $tier->price > 0 ? ticketly_money($tier->price) : 'Free' }}</td>
            <td class="py-3 text-gray-400">{{ $tier->total_quantity }}</td>
            <td class="py-3 text-gray-400">{{ $tier->available_quantity }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-white mb-4">Sponsors</h2>

    @if($event->sponsorships->isEmpty())
      <p class="text-sm text-gray-400">No sponsors added for this event.</p>
    @else
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
        @foreach($event->sponsorships as $sponsorship)
          @php
            $initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sponsorship->name, 0, 2));
          @endphp
          <div class="rounded-2xl border border-gray-800 bg-gray-950/70 p-4">
            <div class="flex aspect-square items-center justify-center overflow-hidden rounded-2xl border border-gray-800 bg-gradient-to-br from-slate-800 to-slate-900">
              @if($sponsorship->photo_url)
                <img src="{{ asset('storage/app/public/sponsorships/'.basename($sponsorship->photo_url)) }}" alt="{{ $sponsorship->name }}" class="h-full w-full object-cover">
              @else
                <span class="admin-event-sponsor-initials text-lg font-extrabold tracking-[0.2em] text-gray-300">{{ $initials }}</span>
              @endif
            </div>
            @if($sponsorship->name)
              <p class="admin-event-sponsor-name mt-3 truncate text-center text-sm font-semibold text-white">{{ $sponsorship->name }}</p>
            @endif
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>
@endsection
