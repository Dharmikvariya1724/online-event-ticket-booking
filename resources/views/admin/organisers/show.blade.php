@extends('layouts.admin')

@section('title', 'Organiser Details')
@section('page-title', 'Organiser')
@section('page-subtitle', $organiser->name)

@section('content')
<div class="grid gap-6">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div>
      <div class="text-lg font-bold text-white">{{ $organiser->name }} <span class="text-gray-500">·</span> {{ $organiser->company_name }}</div>
      <div class="text-sm text-gray-400">{{ $organiser->email }}</div>
      <div class="text-sm text-gray-500">{{ $organiser->phone ?? 'No phone provided' }}</div>
      <div class="mt-2 flex flex-wrap gap-2">
        @if($organiser->is_approved)
          <span class="badge badge--positive">Approved</span>
        @elseif($organiser->rejected_at)
          <span class="badge badge--danger">Rejected</span>
        @else
          <span class="badge badge--warning">Pending</span>
        @endif
        @if($organiser->is_suspended)
          <span class="badge badge--danger">Suspended</span>
        @else
          <span class="badge badge--positive">Active</span>
        @endif
      </div>
      @if($organiser->rejection_reason)
        <div class="mt-2 text-xs text-rose-300">Rejection: {{ $organiser->rejection_reason }}</div>
      @endif
      <div class="mt-3 text-xs text-gray-500">
        Stripe: {{ $organiser->stripe_account_id ? 'Connected' : 'Not connected' }} ·
        Onboarding: {{ $organiser->stripe_onboarding_complete ? 'Complete' : 'Incomplete' }}
      </div>
    </div>
    <div class="flex flex-wrap gap-2">
      @if(!$organiser->is_approved)
      <form method="POST" action="{{ route('admin.organisers.approve', $organiser->id) }}" data-confirm="Approve this organiser?">
        @csrf
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Approve</button>
      </form>
      @endif
      @if(!$organiser->is_approved && !$organiser->rejected_at)
      <form method="POST" action="{{ route('admin.organisers.reject', $organiser->id) }}" data-confirm="Reject this organiser?">
        @csrf
        <input type="hidden" name="rejection_reason" value="Your organiser account was not approved by our team.">
        <button class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold">Reject</button>
      </form>
      @endif
      @if($organiser->is_suspended)
      <form method="POST" action="{{ route('admin.organisers.activate', $organiser->id) }}" data-confirm="Activate this organiser account?">
        @csrf
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Activate</button>
      </form>
      @else
      <form method="POST" action="{{ route('admin.organisers.suspend', $organiser->id) }}" data-confirm="Suspend this organiser account?">
        @csrf
        <button class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-semibold">Suspend</button>
      </form>
      @endif
    </div>
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <div class="text-sm text-gray-400 uppercase tracking-wider">Revenue</div>
    <div class="mt-2 text-2xl font-extrabold text-white">{{ ticketly_money($revenue) }}</div>
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-white mb-4">Events</h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-gray-400 text-xs uppercase">
          <tr>
            <th class="text-left py-3">Event</th>
            <th class="text-left py-3">Status</th>
            <th class="text-left py-3">Approval</th>
            <th class="text-right py-3">Date</th>
            <th class="text-right py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
          @forelse($events as $event)
          <tr>
            <td class="py-3 text-white font-semibold">{{ $event->title }}</td>
            <td class="py-3 text-gray-400">{{ ucfirst($event->status) }}</td>
            <td class="py-3">
              <span class="badge {{ $event->approval_status === 'approved' ? 'badge--positive' : ($event->approval_status === 'rejected' ? 'badge--danger' : 'badge--warning') }}">
                {{ ucfirst($event->approval_status ?? 'pending') }}
              </span>
            </td>
            <td class="py-3 text-gray-500 text-right">{{ ticketly_format_date($event->starts_at) }}</td>
            <td class="py-3 text-right">
              <a class="text-emerald-400 hover:text-emerald-300" href="{{ route('admin.events.show', $event->id) }}">View</a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="py-6 text-center text-gray-500">No events found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
