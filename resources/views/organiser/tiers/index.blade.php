@extends('layouts.organiser')
@section('title', 'Ticket Tiers')
@section('page-title', 'Ticket Tiers')
@section('page-subtitle', $event->title)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
  <a href="{{ route('organiser.events.show', $event->id) }}" class="group inline-flex items-center gap-2 text-sm font-semibold text-gray-400 hover:text-white transition-colors">
      <span class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-700 bg-transparent text-gray-400 transition-colors group-hover:border-gray-500">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 6 9 12l6 6"></path>
        </svg>
      </span>
      <span>Back to event</span>
    </a>
  <a href="{{ route('organiser.tiers.create', $event->id) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white sm:w-auto" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">+ Add Tier</a>
</div>

@if($tiers->isEmpty())
<div class="bg-gray-900 border border-gray-800 rounded-2xl p-10 text-center sm:p-16">
  <div class="text-5xl mb-4">🎟</div>
  <h3 class="text-lg font-bold text-white mb-2">No ticket tiers yet</h3>
  <p class="text-gray-400 text-sm mb-5">Add at least one ticket tier so attendees can purchase tickets.</p>
  <a href="{{ route('organiser.tiers.create', $event->id) }}" class="inline-flex items-center gap-2 text-sm font-bold text-white px-5 py-3 rounded-xl" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">Add First Tier</a>
</div>
@else
<div class="space-y-4">
  @foreach($tiers as $tier)
  <div class="bg-gray-900 border {{ $tier->is_active ? 'border-gray-800' : 'border-gray-800 opacity-60' }} rounded-2xl p-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3 mb-1">
          <h3 class="font-bold text-white">{{ $tier->name }}</h3>
          @if(!$tier->is_active)
          <span class="badge badge--neutral">Inactive</span>
          @elseif($tier->isSoldOut())
          <span class="badge badge--danger">Sold Out</span>
          @else
          <span class="badge badge--positive">Active</span>
          @endif
        </div>
        @if($tier->description)<p class="text-gray-400 text-sm mb-3">{{ $tier->description }}</p>@endif
        <div class="flex flex-wrap gap-5 text-sm">
          <div><span class="text-gray-500">Price</span><br><span class="text-indigo-400 font-extrabold text-base">{{ $tier->price == 0 ? 'Free' : ticketly_money($tier->price) }}</span></div>
          <div><span class="text-gray-500">Total</span><br><span class="text-white font-bold">{{ number_format($tier->total_quantity) }}</span></div>
          <div><span class="text-gray-500">Sold</span><br><span class="text-pink-400 font-bold">{{ number_format($tier->sold_quantity) }}</span></div>
          <div><span class="text-gray-500">Available</span><br><span class="text-emerald-400 font-bold">{{ number_format($tier->available_quantity) }}</span></div>
          <div><span class="text-gray-500">Per order</span><br><span class="text-white">{{ $tier->min_per_order }}–{{ $tier->max_per_order }}</span></div>
        </div>
        {{-- Progress bar --}}
        <div class="mt-4">
          <div class="flex justify-between text-xs text-gray-500 mb-1">
            <span>{{ $tier->sold_quantity }} sold</span>
            <span>{{ $tier->total_quantity - $tier->available_quantity > 0 ? round(($tier->sold_quantity / $tier->total_quantity) * 100) : 0 }}%</span>
          </div>
          <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
            @php $pct = $tier->total_quantity > 0 ? ($tier->sold_quantity / $tier->total_quantity) * 100 : 0; @endphp
            <div class="h-full rounded-full transition-all" style="width:{{ $pct }}%;background:linear-gradient(90deg,#6366f1,#ec4899)"></div>
          </div>
        </div>
      </div>
      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
        <a href="{{ route('organiser.tiers.edit', [$event->id, $tier->id]) }}" class="inline-flex items-center justify-center text-xs font-semibold text-indigo-400 border border-indigo-500/30 px-3 py-2 rounded-lg hover:bg-indigo-600/10 transition-colors">Edit</a>
        @if($tier->sold_quantity === 0)
        <form action="{{ route('organiser.tiers.destroy', [$event->id, $tier->id]) }}" method="POST" class="w-full sm:w-auto" data-confirm="Delete this tier?">
          @csrf @method('DELETE')
          <button type="submit" class="w-full text-xs font-semibold text-red-400 border border-red-700/50 px-3 py-2 rounded-lg hover:bg-red-900/20 transition-colors sm:w-auto">Delete</button>
        </form>
        @endif
      </div>
    </div>
  </div>
  @endforeach
</div>
@endif
@endsection
