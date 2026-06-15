@extends('layouts.app')
@section('title', 'Browse Events')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 max-[375px]:px-3 sm:px-6 lg:px-8">
  <div class="mb-8">
    <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-2">Browse Events</h1>
    <p id="results-count" class="text-gray-400">{{ $events->total() }} event{{ $events->total() !== 1 ? 's' : '' }} found</p>
  </div>

  <form id="events-filter-form" method="GET" action="{{ route('events.index') }}">
    <div class="flex flex-col gap-8 lg:flex-row">
      <aside class="w-full lg:w-56 lg:flex-shrink-0" aria-label="Event filters">
        <button id="mobile-filters-toggle"
                type="button"
                class="mb-3 inline-flex w-full items-center justify-between rounded-xl border border-gray-700 bg-gray-900 px-4 py-3 text-sm font-semibold text-white lg:hidden"
                aria-controls="mobile-filters-panel"
                aria-expanded="false">
          <span>Filters</span>
          <span id="mobile-filters-toggle-icon" class="text-gray-400">+</span>
        </button>

        <div id="mobile-filters-panel" class="hidden rounded-2xl border border-gray-800 bg-gray-900 p-5 space-y-6 max-[375px]:space-y-5 max-[375px]:p-4 lg:block">
          <div>
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">Category</h3>
            <div class="space-y-2" role="group" aria-label="Filter by category">
              <label class="flex w-full cursor-pointer items-center gap-2.5">
                <input type="radio" name="category" value="" {{ !request('category') ? 'checked' : '' }} class="text-indigo-600">
                <span class="min-w-0 flex-1 truncate text-sm leading-5 text-gray-300">All Categories</span>
              </label>
              @foreach($categories as $cat => $count)
              <label class="flex w-full cursor-pointer items-center gap-2.5" title="{{ $cat }} ({{ $count }})">
                <input type="radio" name="category" value="{{ $cat }}" {{ request('category') === $cat ? 'checked' : '' }} class="text-indigo-600">
                <span class="min-w-0 flex-1 truncate text-sm leading-5 text-gray-300">{{ $cat }} <span class="text-gray-600">({{ $count }})</span></span>
              </label>
              @endforeach
            </div>
          </div>

          <div>
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">City</h3>
            <select name="city" class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 max-[375px]:text-[13px]" aria-label="Filter by city">
              <option value="">All Cities</option>
              @foreach($cities as $city => $count)
              <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }} ({{ $count }})</option>
              @endforeach
            </select>
          </div>

          <div>
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">Date Range</h3>
            <div class="grid gap-2">
              <div class="grid gap-1">
                <label for="date-from" class="text-xs font-semibold uppercase tracking-wide text-gray-500">From</label>
                <input id="date-from" type="date" name="date_from" value="{{ request('date_from') }}"
                       class="block w-full max-w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 max-[375px]:text-[13px]"
                       aria-label="Date from">
              </div>
              <div class="grid gap-1">
                <label for="date-to" class="text-xs font-semibold uppercase tracking-wide text-gray-500">To</label>
                <input id="date-to" type="date" name="date_to" value="{{ request('date_to') }}"
                       class="block w-full max-w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 max-[375px]:text-[13px]"
                       aria-label="Date to">
              </div>
            </div>
          </div>

          <div>
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-gray-400">Price Range</h3>
            <div class="grid grid-cols-2 gap-2 max-[375px]:grid-cols-1">
              <input type="number" min="0" step="0.01" name="price_min" value="{{ request('price_min') }}"
                     placeholder="Min"
                     class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 max-[375px]:text-[13px]"
                     aria-label="Minimum price">
              <input type="number" min="0" step="0.01" name="price_max" value="{{ request('price_max') }}"
                     placeholder="Max"
                     class="w-full rounded-lg border border-gray-700 bg-gray-800 px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 max-[375px]:text-[13px]"
                     aria-label="Maximum price">
            </div>
          </div>

          <a id="clear-filters-link"
             href="{{ route('events.index') }}"
             class="block text-center text-xs font-semibold text-gray-400 hover:text-white transition-colors {{ request()->hasAny(['category','city','date','date_from','date_to','search','price','price_min','price_max']) ? '' : 'hidden' }}">
            Clear filters
          </a>
        </div>
      </aside>

      <main id="main-content" class="min-w-0 flex-1">
        <div class="mb-6" role="search">
          <label for="events-search" class="sr-only">Search events</label>
          <div class="flex gap-2">
            <input id="events-search" type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search by event name or description..."
                   class="flex-1 rounded-xl border border-gray-700 bg-gray-900 px-4 py-3 text-sm text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 max-[375px]:px-3 max-[375px]:py-2.5 max-[375px]:text-[13px]">
          </div>
        </div>

        <div id="events-results">
          @include('events._results', ['events' => $events])
        </div>
      </main>
    </div>
  </form>
</div>

<script>
(() => {
  const form = document.getElementById('events-filter-form');
  const resultsEl = document.getElementById('events-results');
  const countEl = document.getElementById('results-count');
  const searchInput = document.getElementById('events-search');
  const clearFiltersLink = document.getElementById('clear-filters-link');
  const mobileFiltersToggle = document.getElementById('mobile-filters-toggle');
  const mobileFiltersPanel = document.getElementById('mobile-filters-panel');
  const mobileFiltersToggleIcon = document.getElementById('mobile-filters-toggle-icon');

  if (!form || !resultsEl) return;

  const dateFromInput = form.querySelector('input[name="date_from"]');
  const dateToInput = form.querySelector('input[name="date_to"]');

  let debounceTimer = null;
  let activeController = null;

  const getQueryString = () => {
    const formData = new FormData(form);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
      if (String(value).trim() !== '') {
        params.append(key, value);
      }
    }
    return params.toString();
  };

  const syncClearFiltersVisibility = () => {
    if (!clearFiltersLink) return;
    clearFiltersLink.classList.toggle('hidden', getQueryString() === '');
  };

  const syncMobileFiltersToggle = () => {
    if (!mobileFiltersToggle || !mobileFiltersPanel || !mobileFiltersToggleIcon) return;
    const expanded = !mobileFiltersPanel.classList.contains('hidden');
    mobileFiltersToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    mobileFiltersToggleIcon.textContent = expanded ? '−' : '+';
  };

  const syncDateRangeBounds = () => {
    if (!dateFromInput || !dateToInput) return;
    dateToInput.min = dateFromInput.value || '';
    dateFromInput.max = dateToInput.value || '';
  };

  const fetchAndRender = async (url, pushState = true) => {
    try {
      if (activeController) activeController.abort();
      activeController = new AbortController();
      resultsEl.classList.add('opacity-60', 'pointer-events-none');

      const response = await fetch(url, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: activeController.signal,
      });

      const html = await response.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const nextResults = doc.getElementById('events-results');
      const nextCount = doc.getElementById('results-count');

      if (nextResults) {
        resultsEl.innerHTML = nextResults.innerHTML;
      }
      if (nextCount && countEl) {
        countEl.textContent = nextCount.textContent;
      }
      if (pushState) {
        window.history.pushState({}, '', url);
      }
    } catch (error) {
      if (error.name !== 'AbortError') {
        console.error('Failed to update events list:', error);
      }
    } finally {
      resultsEl.classList.remove('opacity-60', 'pointer-events-none');
    }
  };

  const submitLive = (pushState = true) => {
    const qs = getQueryString();
    const url = qs ? `${form.action}?${qs}` : form.action;
    syncClearFiltersVisibility();
    fetchAndRender(url, pushState);
  };

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    submitLive(true);
  });

  form.querySelectorAll('select, input[type="radio"], input[type="date"], input[type="number"]').forEach((el) => {
    el.addEventListener('change', () => {
      syncDateRangeBounds();
      syncClearFiltersVisibility();
      submitLive(true);
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      syncClearFiltersVisibility();
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => submitLive(true), 350);
    });
  }

  if (mobileFiltersToggle && mobileFiltersPanel) {
    mobileFiltersToggle.addEventListener('click', () => {
      mobileFiltersPanel.classList.toggle('hidden');
      syncMobileFiltersToggle();
    });
  }

  resultsEl.addEventListener('click', (e) => {
    const link = e.target.closest('a[href]');
    if (!link) return;

    const url = new URL(link.href, window.location.origin);
    const indexPath = new URL(form.action, window.location.origin).pathname;
    if (url.pathname !== indexPath || !url.searchParams.has('page')) return;

    e.preventDefault();
    fetchAndRender(url.toString(), true);
  });

  window.addEventListener('popstate', () => {
    syncDateRangeBounds();
    syncClearFiltersVisibility();
    fetchAndRender(window.location.href, false);
  });

  syncDateRangeBounds();
  syncClearFiltersVisibility();
  syncMobileFiltersToggle();
})();
</script>
@endsection
