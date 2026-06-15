@extends('layouts.organiser')
@section('title', 'Promo Codes')
@section('page-title', 'Promo Codes')
@section('page-subtitle', 'Manage discount codes for your events')

@section('content')
<div class="flex items-center justify-between mb-6">
  <div></div>
  <a href="{{ route('organiser.promos.create') }}" class="flex items-center gap-2 text-sm font-bold text-white px-4 py-2.5 rounded-xl" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">+ Create Promo Code</a>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="bg-gray-800/60">
        <tr>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Code</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Discount</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Event</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Uses</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Expires</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
          <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-800">
        @forelse($promos as $promo)
        <tr class="hover:bg-gray-300/20 {{ $promo->trashed() ? 'opacity-50' : '' }}">
          <td class="px-4 py-3">
            <span class="font-mono font-extrabold text-sm {{ $promo->trashed() ? 'text-gray-500 line-through' : 'text-indigo-400' }}">{{ $promo->code }}</span>
          </td>
          <td class="px-4 py-3 text-sm text-white font-semibold">
            {{ $promo->type === 'percentage' ? $promo->value . '%' : ticketly_money_code($promo->value) }}
            @if($promo->max_discount)<span class="text-xs text-gray-500"> (max {{ ticketly_money($promo->max_discount) }})</span>@endif
          </td>
          <td class="px-4 py-3 text-sm text-gray-300">
            {{ $promo->event ? Str::limit($promo->event->title, 30) : 'All events' }}
          </td>
          <td class="px-4 py-3 text-sm text-gray-300">
            {{ $promo->used_count }}{{ $promo->max_uses ? '/' . $promo->max_uses : '' }}
          </td>
          <td class="px-4 py-3 text-xs text-gray-400">
            {{ $promo->expires_at ? ticketly_format_date($promo->expires_at) : '-' }}
          </td>
          <td class="px-4 py-3">
            @php
              if ($promo->trashed()) {
                  $statusLabel = 'Deleted';
                  $statusClass = 'badge--danger';
              } elseif (!$promo->is_active) {
                  $statusLabel = 'Inactive';
                  $statusClass = 'badge--warning';
              } elseif (!$promo->isValid()) {
                  $statusLabel = 'Expired';
                  $statusClass = 'badge--danger';
              } else {
                  $statusLabel = 'Active';
                  $statusClass = 'badge--positive';
              }
            @endphp
            <span class="badge {{ $statusClass }}">
              {{ $statusLabel }}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-2 flex-wrap">
              <a href="{{ route('organiser.promos.show', $promo->id) }}" class="text-xs font-semibold text-blue-300 border border-blue-600/40 px-2.5 py-1.5 rounded-lg hover:bg-blue-900/20 transition-colors">View</a>

              @if(!$promo->trashed())
              <a href="{{ route('organiser.promos.edit', $promo->id) }}" class="text-xs font-semibold text-blue-300 border border-blue-600/40 px-2.5 py-1.5 rounded-lg hover:bg-blue-900/20 transition-colors">Edit</a>

              <form action="{{ $promo->is_active ? route('organiser.promos.deactivate', $promo->id) : route('organiser.promos.activate', $promo->id) }}" method="POST" class="inline" data-confirm="{{ $promo->is_active ? 'Deactivate this promo code?' : 'Activate this promo code?' }}">
                @csrf
                <button type="submit"
                        class="text-xs font-semibold {{ $promo->is_active ? 'text-amber-300 border-amber-600/40 hover:bg-amber-900/20' : 'text-emerald-300 border-emerald-600/40 hover:bg-emerald-900/20' }} border px-2.5 py-1.5 rounded-lg transition-colors">
                  {{ $promo->is_active ? 'Deactivate' : 'Activate' }}
                </button>
              </form>
              <form action="{{ route('organiser.promos.destroy', $promo->id) }}" method="POST" class="inline" data-confirm="{{ $promo->used_count > 0 ? 'This promo has already been used, so it will be soft-deleted only. Continue?' : ($promo->trashed() ? 'Permanently delete this promo code?' : 'Delete this promo code?') }}" data-confirm-ok="Delete">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="text-xs font-semibold text-red-300 border border-red-700/50 px-2.5 py-1.5 rounded-lg hover:bg-red-900/20 transition-colors">Delete</button>
              </form>
              @endif

              
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center py-12 text-gray-500 text-sm">No promo codes yet</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="mt-4">{{ $promos->links() }}</div>
@endsection
