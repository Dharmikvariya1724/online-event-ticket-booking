@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-400">
                Showing <span class="font-semibold text-white">{{ $paginator->firstItem() }}</span>
                to <span class="font-semibold text-white">{{ $paginator->lastItem() }}</span>
                of <span class="font-semibold text-white">{{ $paginator->total() }}</span> results
            </p>
        </div>
        <div>
            <span class="relative z-0 inline-flex rounded-xl shadow-sm gap-1">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                <span class="px-3 py-2 text-sm text-gray-600 bg-gray-800 border border-gray-700 rounded-lg cursor-not-allowed">‹ Prev</span>
                @else
                <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-300 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 hover:text-white transition-all">‹ Prev</a>
                @endif

                {{-- Pages --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                    <span class="px-3 py-2 text-sm text-gray-500">{{ $element }}</span>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                        <span class="px-3 py-2 text-sm font-bold text-white bg-indigo-600 border border-indigo-600 rounded-lg">{{ $page }}</span>
                        @else
                        <a href="{{ $url }}" class="px-3 py-2 text-sm text-gray-300 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 hover:text-white transition-all">{{ $page }}</a>
                        @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-300 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700 hover:text-white transition-all">Next ›</a>
                @else
                <span class="px-3 py-2 text-sm text-gray-600 bg-gray-800 border border-gray-700 rounded-lg cursor-not-allowed">Next ›</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Mobile -->
    <div class="flex flex-1 justify-between sm:hidden gap-2">
        @if ($paginator->onFirstPage())
        <span class="px-4 py-2 text-sm text-gray-500 bg-gray-800 border border-gray-700 rounded-lg cursor-not-allowed">← Prev</span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}" class="px-4 py-2 text-sm text-gray-300 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700">← Prev</a>
        @endif
        <span class="px-4 py-2 text-sm text-gray-400">Page {{ $paginator->currentPage() }}</span>
        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="px-4 py-2 text-sm text-gray-300 bg-gray-800 border border-gray-700 rounded-lg hover:bg-gray-700">Next →</a>
        @else
        <span class="px-4 py-2 text-sm text-gray-500 bg-gray-800 border border-gray-700 rounded-lg cursor-not-allowed">Next →</span>
        @endif
    </div>
</nav>
@endif
