@php
$lowestPrice  = $event->ticketTiers->where('is_active',true)->min('price');
$isFree       = $lowestPrice === null || $lowestPrice == 0;
$isSoldOut    = $event->ticketTiers->where('is_active',true)->sum('available_quantity') <= 0;
$isFeaturedCard = $featured ?? false;
@endphp

<article class="group bg-gray-900 border border-gray-800 rounded-[1.35rem] overflow-hidden hover:border-indigo-500/40 transition-all duration-300 hover:-translate-y-1 flex flex-col"
         role="article"
         aria-label="{{ $event->title }}">

  {{-- Banner --}}
  <a href="{{ route('events.show', $event->slug) }}" class="block relative overflow-hidden {{ $isFeaturedCard ? 'h-56' : 'h-48' }} flex-shrink-0"
     aria-label="View {{ $event->title }}" tabindex="0">
    @if($event->banner_url)
    <img src="{{ $event->banner_url }}" alt="Banner image for {{ $event->title }}"
         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
         loading="lazy">
    @else
    <div class="w-full h-full flex items-center justify-center" style="background:linear-gradient(135deg,#4f46e5,#7c3aed,#db2777)" aria-hidden="true">
      <span class="text-5xl">🎟</span>
    </div>
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/20 to-transparent" aria-hidden="true"></div>

    {{-- Category badge --}}
    <div class="absolute top-3 left-3" aria-label="Category: {{ $event->category }}">
      <span class="bg-black/60 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full">{{ $event->category }}</span>
    </div>

    {{-- Price badge --}}
    <div class="absolute top-3 right-3">
      @if($isSoldOut)
      <span class="bg-red-600 text-white text-xs font-extrabold px-2.5 py-1 rounded-full">SOLD OUT</span>
      @elseif($isFree)
      <span class="bg-emerald-600 text-white text-xs font-extrabold px-2.5 py-1 rounded-full">FREE</span>
      @else
      <span class="text-white text-xs font-extrabold px-2.5 py-1 rounded-full" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">From {{ ticketly_money($lowestPrice) }}</span>
      @endif
    </div>

    {{-- Date overlay --}}
    <div class="absolute bottom-3 left-3">
      <div class="bg-black/70 backdrop-blur-sm text-white text-xs font-semibold px-2.5 py-1 rounded-lg">
        <!-- <time datetime="{{ $event->starts_at->toISOString() }}">{{ $event->starts_at->format('d M Y · g:ia') }}</time> -->
        <time datetime="{{ $event->starts_at->toISOString() }}">{{ ticketly_format_date($event->starts_at) }}</time>
      </div>
    </div>
  </a>

  {{-- Content --}}
  <div class="p-5 flex-1 flex flex-col">
    <h3 class="font-extrabold text-white text-base mb-1.5 line-clamp-2 group-hover:text-indigo-300 transition-colors">
      <a href="{{ route('events.show', $event->slug) }}" class="focus:outline-none focus:underline">{{ $event->title }}</a>
    </h3>
    <p class="text-gray-500 text-sm mb-3.5 flex items-center gap-1.5">
      <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      {{ $event->venue_name }}, {{ $event->city }}
    </p>

    <div class="mt-auto">
      @if($isSoldOut)
      <span class="block text-center text-sm font-bold text-red-400 bg-red-900/20 border border-red-800/50 py-2.5 rounded-xl" role="status">Sold Out</span>
      @else
      <a href="{{ route('events.show', $event->slug) }}"
         class="block text-center text-sm font-bold text-white py-3 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500"
         style="background:linear-gradient(135deg,#6366f1,#8b5cf6)"
         aria-label="Book tickets for {{ $event->title }}">
        View Details
      </a>
      @endif
    </div>
  </div>
</article>
