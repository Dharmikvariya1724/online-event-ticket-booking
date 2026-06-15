@if($events->count())
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($events as $event)
    <a href="{{ route('events.show', $event->slug) }}" class="group bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden hover:border-indigo-500/50 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/10 flex flex-col">
        <!-- Banner -->
        <div class="relative h-48 bg-gradient-to-br from-indigo-700 to-purple-800 overflow-hidden flex-shrink-0">
            
            <img src="{{ $event->banner_url ?? 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&h=400&fit=crop' }}" alt="{{ $event->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            

            <!-- Badges -->
            <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                @if($event->is_featured)
                <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full w-fit">⭐ Featured</span>
                @endif
                @if($event->total_available < 50 && $event->total_available > 0)
                <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full w-fit">🔥 {{ $event->total_available }} left</span>
                @elseif($event->total_available == 0)
                <span class="bg-gray-600 text-white text-xs font-bold px-2 py-0.5 rounded-full w-fit">Sold Out</span>
                @endif
            </div>

            <!-- Date pill top right -->
            <div class="absolute top-3 right-3 max-w-[10rem] bg-black/60 backdrop-blur text-white text-[11px] font-bold px-2.5 py-1.5 rounded-lg text-center leading-4">
                {{ ticketly_format_date($event->starts_at) }}
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 flex flex-col flex-1">
            <span class="inline-block text-xs font-semibold text-indigo-400 uppercase tracking-wide mb-1">{{ $event->category }}</span>
            <h3 class="font-bold text-white text-sm leading-tight mb-2 line-clamp-2 group-hover:text-indigo-300 transition-colors flex-1">{{ $event->title }}</h3>

            <div class="space-y-1.5 text-xs text-gray-400 mb-4">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $event->formatted_date }}
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ $event->venue_name }}, {{ $event->city }}
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-gray-800">
                <div>
                    <span class="text-xs text-gray-500">From</span>
                    <span class="text-indigo-400 font-extrabold text-lg ml-1">{{ ticketly_money($event->lowest_price) }}</span>
                </div>
                <span class="text-xs font-semibold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1.5 rounded-lg group-hover:bg-indigo-500/20 transition-colors">Get Tickets →</span>
            </div>
        </div>
    </a>
    @endforeach
</div>
@else
<div class="flex flex-col items-center justify-center py-24 text-center">
    <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mb-4">
        <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </div>
    <h3 class="text-xl font-bold text-white mb-2">No events found</h3>
    <p class="text-gray-500 mb-6 max-w-xs">Try adjusting your search terms or filters to find what you're looking for.</p>
    <button onclick="document.getElementById('clear-filters').click()" class="text-sm text-indigo-400 hover:text-indigo-300 font-semibold border border-indigo-500/30 px-4 py-2 rounded-lg hover:bg-indigo-500/10 transition-all">
        Clear all filters
    </button>
</div>
@endif
