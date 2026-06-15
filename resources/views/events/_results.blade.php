@if($events->isEmpty())
<div class="text-center py-20">
  <div class="text-5xl mb-4" aria-hidden="true">?</div>
  <h2 class="text-xl font-bold text-white mb-2">No events found</h2>
  <p class="text-gray-400 mb-5">Try adjusting your filters or search terms.</p>
  <a href="{{ route('events.index') }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-semibold">Clear all filters</a>
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
  @foreach($events as $event)
  @php
    $lowestPrice = $event->ticketTiers->where('is_active', true)->min('price');
    $isFree = $lowestPrice === null || $lowestPrice == 0;
  @endphp
  <article class="group bg-gray-900 border border-gray-800 rounded-[1.35rem] overflow-hidden hover:border-indigo-500/40 transition-all duration-300 hover:-translate-y-1 flex flex-col"
           aria-label="{{ $event->title }}">
    <a href="{{ route('events.show', $event->slug) }}" class="block relative h-48 overflow-hidden" aria-label="View {{ $event->title }}">
      
        <img src="{{ $event->banner_url ?? 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&h=400&fit=crop' }}" alt="Banner image for {{ $event->title }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
     
      <div class="absolute inset-0 bg-gradient-to-t from-gray-900/85 via-gray-900/15 to-transparent" aria-hidden="true"></div>

      @if($event->is_featured)
        <span class="absolute top-3 left-3 inline-flex items-center gap-1 rounded-full bg-amber-400 px-3 py-1 text-xs font-extrabold text-amber-950">
          <span aria-hidden="true">★</span>
          <span>Featured</span>
        </span>
      @endif

      <div class="absolute top-3 right-3">
        @if($isFree)
          <span class="bg-emerald-600 text-white text-xs font-extrabold px-2.5 py-1 rounded-full">Free</span>
        @else
          <span class="text-white text-xs font-extrabold px-2.5 py-1 rounded-full" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">From {{ ticketly_money($lowestPrice) }}</span>
        @endif
      </div>
    </a>

    <div class="p-5 flex-1 flex flex-col">
      <h3 class="font-extrabold text-white text-base mb-2 line-clamp-2 group-hover:text-indigo-300 transition-colors">
        <a href="{{ route('events.show', $event->slug) }}" class="focus:outline-none focus:underline">{{ $event->title }}</a>
      </h3>

      <p class="text-gray-400 text-sm mb-1.5">
        <time datetime="{{ $event->starts_at->toISOString() }}">{{ ticketly_format_date($event->starts_at) }}</time>
        <span> &middot; {{ ticketly_format_time($event->starts_at) }}</span>
      </p>

      <p class="text-gray-500 text-sm mb-4 line-clamp-1">{{ $event->venue_name }}, {{ $event->city }}</p>

      <div class="mt-auto">
        <a href="{{ route('events.show', $event->slug) }}"
           class="block text-center text-sm font-bold text-white py-3 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500"
           style="background:linear-gradient(135deg,#6366f1,#8b5cf6)"
           aria-label="Book tickets for {{ $event->title }}">
          View Details
        </a>
      </div>
    </div>
  </article>
  @endforeach
</div>
<div class="mt-8">{{ $events->links() }}</div>
@endif
