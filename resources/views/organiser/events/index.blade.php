@extends('layouts.organiser')
@section('title', 'My Events')
@section('page-title', 'My Events')
@section('page-subtitle', '')
@section('body-class', 'events-index-page')

@section('page-icon')
<div class="events-page-icon" aria-hidden="true">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <rect x="4" y="5" width="7" height="14" rx="1.8" stroke-width="1.9"></rect>
    <rect x="13" y="5" width="7" height="14" rx="1.8" stroke-width="1.9"></rect>
  </svg>
</div>
@endsection

@section('header-actions')
<a href="{{ route('organiser.events.create') }}" class="events-create-button">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
    <circle cx="12" cy="12" r="8.5" stroke-width="1.8"></circle>
    <path d="M12 8.5v7M8.5 12h7" stroke-linecap="round" stroke-width="1.8"></path>
  </svg>
  <span>Create Event</span>
</a>
@endsection

@section('head')
<style>
  .events-index-page {
    background-image: none !important;
  }

  .events-page-icon {
    width: 1.25rem;
    height: 1.25rem;
    color: var(--events-heading);
  }

  .events-page-icon svg {
    width: 100%;
    height: 100%;
  }

  .events-create-button {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    border-radius: 0.6rem;
    padding: 0.95rem 1.28rem;
    font-size: 0.9rem;
    font-weight: 600;
    line-height: 1;
    color: #ffffff;
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
  }

  .events-create-button svg {
    width: 1rem;
    height: 1rem;
  }

  .events-index-page .organiser-shell-header {
    border-color: var(--events-border) !important;
  }

  .events-index-page .organiser-shell-main {
    padding: 2rem 1.55rem 2.5rem;
  }

  .events-shell {
    display: grid;
    gap: 2rem;
    width: 100%;
    max-width: 91rem;
  }

  .events-shell.is-loading {
    opacity: 0.68;
    pointer-events: none;
    transition: opacity 0.18s ease;
  }

  .events-toolbar {
    display: grid;
    gap: 1.15rem;
  }

  .events-search-form {
    display: grid;
    gap: 1rem;
  }

  .events-search-box {
    position: relative;
    width: min(100%, 41.5rem);
  }

  .events-search-row {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    flex-wrap: wrap;
  }

  .events-search-box svg {
    position: absolute;
    left: 1rem;
    top: 50%;
    width: 1.25rem;
    height: 1.25rem;
    transform: translateY(-50%);
    color: var(--events-muted);
  }

  .events-search-input {
    width: 100%;
    height: 3.35rem;
    border-radius: 0.8rem;
    border: 1px solid var(--events-border);
    background: var(--events-surface);
    color: var(--events-heading);
    padding: 0 1rem 0 3.3rem;
    font-size: 0.96rem;
    outline: none;
  }

  .events-search-input::placeholder {
    color: var(--events-muted);
  }

  .events-search-input:focus,
  .events-search-submit:focus-visible,
  .events-tab-button:focus-visible,
  .events-clear-link:focus-visible,
  .events-ghost-action:focus-visible,
  .events-menu-toggle:focus-visible,
  .events-dropdown-link:focus-visible,
  .events-dropdown-btn:focus-visible {
    outline: 2px solid rgba(124, 58, 237, 0.35);
    outline-offset: 2px;
  }

  .events-search-submit {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 3.35rem;
    padding: 0.95rem 1.2rem;
    border: 0;
    border-radius: 0.8rem;
    background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
    color: #ffffff;
    font-size: 0.88rem;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
  }

  .events-filter-row {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    flex-wrap: wrap;
  }

  .events-tabs {
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
    border-radius: 0.75rem;
    background: var(--events-tab-strip);
    padding: 0.25rem;
  }

  .events-tab-button,
  .events-clear-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 3rem;
    padding: 0.75rem 1.12rem;
    border-radius: 0.6rem;
    border: 0;
    background: transparent;
    color: var(--events-muted);
    font-size: 0.96rem;
    font-weight: 600;
    line-height: 1;
  }

  .events-tab-button.is-active {
    background: var(--events-surface);
    color: var(--events-heading);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
  }

  .events-clear-link {
    border: 1px solid var(--events-border);
    background: var(--events-surface);
    text-decoration: none;
  }

  .events-list {
    display: grid;
    gap: 1.6rem;
  }

  .events-item {
    display: grid;
    grid-template-columns: 10.5rem minmax(0, 1fr) auto;
    align-items: center;
    gap: 1.6rem;
    padding: 1.55rem;
    border-radius: 1.15rem;
    border: 1px solid var(--events-border);
    background: var(--events-surface);
    box-shadow: var(--events-shadow);
    position: relative;
    cursor: pointer;
    transition: border-color 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
  }

  .events-item:hover {
    border-color: rgba(124, 58, 237, 0.32);
    transform: translateY(-1px);
  }

  .events-item:focus-visible {
    outline: 2px solid rgba(124, 58, 237, 0.35);
    outline-offset: 2px;
  }

  .events-item.is-menu-open {
    z-index: 35;
  }

  .events-item__media {
    width: 10.5rem;
    height: 7.35rem;
    overflow: hidden;
    border-radius: 0.1rem;
    background: linear-gradient(135deg, #4f46e5, #c026d3);
    flex-shrink: 0;
  }

  .events-item__media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
  }

  .events-item__body {
    min-width: 0;
  }

  .events-item__header {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 0.7rem;
  }

  .events-item__title {
    margin: 0;
    font-size: 1.08rem;
    line-height: 1.25;
    font-weight: 800;
    color: var(--events-heading);
    letter-spacing: -0.02em;
  }

  .events-item__badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 1.9rem;
    border-radius: 0.55rem;
    padding: 0.35rem 0.8rem;
    background: linear-gradient(135deg, #a855f7, #7c3aed);
    color: #ffffff;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: lowercase;
    line-height: 1;
  }

  .events-item__approval {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 1.9rem;
    border-radius: 999px;
    padding: 0.35rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: lowercase;
    line-height: 1;
    border: 1px solid transparent;
  }

  .events-item__approval--approved {
    background: rgba(16, 185, 129, 0.12);
    color: #10b981;
    border-color: rgba(16, 185, 129, 0.35);
  }

  .events-item__approval--rejected {
    background: rgba(239, 68, 68, 0.12);
    color: #ef4444;
    border-color: rgba(239, 68, 68, 0.35);
  }

  .events-item__approval--pending {
    background: rgba(245, 158, 11, 0.12);
    color: #f59e0b;
    border-color: rgba(245, 158, 11, 0.35);
  }

  .events-item__date {
    font-size: 0.92rem;
    color: var(--events-muted);
    margin-bottom: 1rem;
  }

  .events-item__stats {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
  }

  .events-stat {
    display: inline-flex;
    align-items: center;
    gap: 0.7rem;
    color: var(--events-muted);
    font-size: 0.88rem;
  }

  .events-stat__icon {
    width: 1.9rem;
    height: 1.9rem;
    border-radius: 999px;
    border: 1px solid var(--events-border);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--events-muted);
    flex-shrink: 0;
  }

  .events-stat__icon svg {
    width: 0.92rem;
    height: 0.92rem;
  }

  .events-item__actions {
    display: flex;
    align-items: flex-start;
    gap: 0.8rem;
    position: relative;
    align-self: start;
  }

  .events-ghost-action,
  .events-menu-toggle {
    width: 2.9rem;
    height: 2.9rem;
    border-radius: 999px;
    border: 1px solid var(--events-border);
    background: var(--events-surface);
    color: var(--events-muted);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
  }

  .events-ghost-action.is-disabled {
    opacity: 0.45;
    pointer-events: none;
    cursor: not-allowed;
  }

  .events-ghost-action svg,
  .events-menu-toggle svg {
    width: 1.15rem;
    height: 1.15rem;
  }

  .events-menu {
    position: absolute;
    top: 3.45rem;
    right: 0;
    min-width: 16.5rem;
    border-radius: 1rem;
    border: 1px solid var(--events-border);
    background: var(--events-surface);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.1);
    padding: 0.9rem;
    display: none;
    z-index: 20;
  }

  .events-menu.is-open {
    display: block;
  }

  .events-menu__group {
    display: grid;
    gap: 0.35rem;
  }

  .events-menu__divider {
    height: 1px;
    background: var(--events-divider);
    margin: 0.85rem 0;
  }

  .events-dropdown-link,
  .events-dropdown-btn {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 0;
    background: transparent;
    padding: 0.35rem 0;
    text-decoration: none;
    color: var(--events-heading);
    font-size: 0.92rem;
    text-align: left;
  }

  .events-dropdown-btn {
    cursor: pointer;
  }

  .events-dropdown-link[aria-disabled='true'] {
    opacity: 0.55;
    cursor: default;
    pointer-events: none;
  }

  .events-dropdown-link--danger,
  .events-dropdown-btn--danger {
    color: #ef4444;
  }

  .events-dropdown-icon {
    width: 2.15rem;
    height: 2.15rem;
    border-radius: 0.55rem;
    border: 1px solid var(--events-border);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 0.88rem;
    font-weight: 700;
    color: inherit;
  }

  .events-empty {
    border-radius: 1.15rem;
    border: 1px solid var(--events-border);
    background: var(--events-surface);
    padding: 4rem 1.5rem;
    text-align: center;
    color: var(--events-muted);
  }

  .events-empty h3 {
    margin: 0 0 0.45rem;
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--events-heading);
  }

  .events-empty p {
    margin: 0 0 1.2rem;
    font-size: 0.9rem;
  }

  .events-pagination {
    margin-top: 0.25rem;
  }

  @media (max-width: 991px) {
    .events-item {
      grid-template-columns: 8.5rem minmax(0, 1fr);
    }

    .events-item__actions {
      grid-column: 1 / -1;
      justify-self: end;
    }

    .events-item__media {
      width: 8.5rem;
      height: 6rem;
    }
  }

  @media (max-width: 767px) {
    .events-index-page .organiser-shell-main {
      padding: 1.2rem 1rem 2rem;
    }

    .events-shell {
      gap: 1.25rem;
    }

    .events-item {
      position: relative;
      grid-template-columns: 1fr;
      gap: 1rem;
      padding: 1rem;
    }

    .events-item__media {
      width: 100%;
      height: 11rem;
      border-radius: 0.75rem;
    }

    .events-item__actions {
      position: absolute;
      top: 1.75rem;
      right: 1.75rem;
      grid-column: auto;
      align-self: start;
      justify-self: auto;
      z-index: 25;
    }

    .events-menu {
      top: 3.45rem;
      bottom: auto;
      left: auto;
      right: 0;
      min-width: min(16.5rem, calc(100vw - 2.5rem));
      max-width: calc(100vw - 2.5rem);
    }

    .events-item__stats {
      gap: 0.9rem 1.2rem;
    }

    .events-filter-row {
      align-items: stretch;
    }

    .events-tabs {
      width: 100%;
      overflow-x: auto;
      padding-bottom: 0.35rem;
    }

    .events-clear-link {
      width: 100%;
    }
  }

  @media (max-width: 479px) {
    .events-search-row {
      align-items: stretch;
    }

    .events-search-box,
    .events-search-submit,
    .events-create-button {
      width: 100%;
    }

    .events-create-button {
      justify-content: center;
    }

    .events-tab-button,
    .events-clear-link {
      min-height: 2.75rem;
      padding: 0.7rem 0.95rem;
      font-size: 0.88rem;
    }

    .events-item__title {
      font-size: 1rem;
    }

    .events-item__stats {
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.75rem 1rem;
    }
  }

  :root[data-theme='light'] .events-index-page {
    background: #ffffff !important;
    --events-surface: #ffffff;
    --events-border: #d8dde7;
    --events-divider: #e5e7eb;
    --events-heading: #111827;
    --events-muted: #6b7280;
    --events-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    --events-tab-strip: #ececef;
  }

  :root[data-theme='dark'] .events-index-page {
    background: #060b14 !important;
    --events-surface: #101827;
    --events-border: #243043;
    --events-divider: #243043;
    --events-heading: #f8fafc;
    --events-muted: #94a3b8;
    --events-shadow: none;
    --events-tab-strip: #131d2b;
  }
</style>
@endsection

@section('content')
@php
  $statusLabels = [
    'all' => 'All (' . ($eventCounts['all'] ?? 0) . ')',
    'published' => 'Published (' . ($eventCounts['published'] ?? 0) . ')',
    'draft' => 'Drafts (' . ($eventCounts['draft'] ?? 0) . ')',
    'past' => 'Past (' . ($eventCounts['past'] ?? 0) . ')',
  ];
@endphp

<div class="events-shell" id="events-shell" data-events-root>
  <section class="events-toolbar">
    <form method="GET" class="events-search-form" data-events-filter-form>
      <div class="events-search-row">
        <div class="events-search-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="m20 20-3.75-3.75M17 10.5A6.5 6.5 0 1 1 4 10.5a6.5 6.5 0 0 1 13 0Z"></path>
          </svg>
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Search events..." class="events-search-input">
          @if(($activeTab ?? 'all') !== 'all')
          <input type="hidden" name="tab" value="{{ $activeTab }}">
          @endif
        </div>
        <button type="submit" class="events-search-submit">Search</button>
      </div>
    </form>

    <div class="events-filter-row">
      <div class="events-tabs">
        @foreach($statusLabels as $value => $label)
        <form method="GET" data-events-filter-form>
          @if(request()->filled('search'))
          <input type="hidden" name="search" value="{{ request('search') }}">
          @endif
          @if($value !== 'all')
          <input type="hidden" name="tab" value="{{ $value }}">
          @endif
          <button type="submit" class="events-tab-button {{ ($activeTab ?? 'all') === $value ? 'is-active' : '' }}">{{ $label }}</button>
        </form>
        @endforeach
      </div>

      @if(request()->hasAny(['search','tab']))
      <a href="{{ route('organiser.events.index') }}" class="events-clear-link">Clear Filter</a>
      @endif
    </div>
  </section>

  <section class="events-list">
    @forelse($events as $event)
    @php
      $sold = (int) ($event->sold_tickets ?? 0);
      $total = (int) ($event->total_capacity ?? 0);
      $left = max($total - $sold, 0);
      $badgeLabel = strtolower($event->status_badge['label'] ?? $event->status);
      $approvalStatus = strtolower($event->approval_status ?? 'pending');
      $approvalClass = [
        'approved' => 'events-item__approval--approved',
        'rejected' => 'events-item__approval--rejected',
        'pending' => 'events-item__approval--pending',
      ][$approvalStatus] ?? 'events-item__approval--pending';
      $menuId = 'event-menu-' . $event->id;
      $statusToggleLabel = $approvalStatus === 'rejected' ? 'Rejected' : ($event->status === 'published' ? 'Move to Draft' : 'Publish');
      $statusToggleValue = $event->status === 'published' ? 'draft' : 'published';
      $statusToggleIcon = $approvalStatus === 'rejected' ? 'R' : ($event->status === 'published' ? 'D' : 'P');
      $statusToggleDisabled = $approvalStatus === 'rejected';
    @endphp
    <article class="events-item" data-event-href="{{ route('organiser.events.show', $event->id) }}" role="link" tabindex="0" aria-label="View {{ $event->title }}">
      <div class="events-item__media">
        @if($event->banner_url)
        <img src="{{ $event->banner_url }}" alt="{{ $event->title }}">
        @endif
      </div>

      <div class="events-item__body">
        <div class="events-item__header">
          <h3 class="events-item__title">{{ $event->title }}</h3>
          <span class="events-item__badge">{{ $badgeLabel }}</span>
          <span class="events-item__approval {{ $approvalClass }}">{{ $approvalStatus }}</span>
        </div>
       <div class="events-item__date">{{ ticketly_format_date($event->starts_at) }}</div> 

        <div class="events-item__stats">
          <span class="events-stat">
            <span class="events-stat__icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 8.5V7a2 2 0 1 1 4 0v1.5M13 8.5V7a2 2 0 1 1 4 0v1.5M5.75 10.25h12.5v6.5H5.75z"></path></svg>
            </span>
            <span>sold</span>
          </span>
          <span class="events-stat">
            <span class="events-stat__icon">S</span>
            <span>{{ number_format($sold) }} sold</span>
          </span>
          <span class="events-stat">
            <span class="events-stat__icon">L</span>
            <span>{{ number_format($left) }} left</span>
          </span>
          <span class="events-stat">
            <span class="events-stat__icon">T</span>
            <span>{{ number_format($total) }} total</span>
          </span>
        </div>
      </div>

      <div class="events-item__actions" data-menu-root>
        <a href="{{ route('organiser.events.show', $event->id) }}" class="events-ghost-action" aria-label="View event details">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M2.75 12s3.5-6.25 9.25-6.25S21.25 12 21.25 12 17.75 18.25 12 18.25 2.75 12 2.75 12Zm9.25 2.75A2.75 2.75 0 1 0 12 9.25a2.75 2.75 0 0 0 0 5.5Z"></path></svg>
        </a>

        <button type="button" class="events-menu-toggle" aria-expanded="false" aria-controls="{{ $menuId }}" onclick="toggleEventMenu(this)">
          <svg viewBox="0 0 24 24" fill="currentColor"><circle cx="5" cy="12" r="1.7"></circle><circle cx="12" cy="12" r="1.7"></circle><circle cx="19" cy="12" r="1.7"></circle></svg>
        </button>

        <div id="{{ $menuId }}" class="events-menu" role="menu">
          <div class="events-menu__group">
            <a href="{{ route('organiser.events.show', $event->id) }}" class="events-dropdown-link" role="menuitem">
              <span class="events-dropdown-icon">V</span>
              <span>View Event</span>
            </a>
            <a href="{{ route('organiser.events.edit', $event->id) }}" class="events-dropdown-link" role="menuitem">
              <span class="events-dropdown-icon">E</span>
              <span>Edit Event</span>
            </a>
            <a href="{{ route('organiser.tiers.index', $event->id) }}" class="events-dropdown-link" role="menuitem">
              <span class="events-dropdown-icon">T</span>
              <span>Ticket Tiers</span>
            </a>
            <a href="{{ route('organiser.sponsorships.index', $event->id) }}" class="events-dropdown-link" role="menuitem">
              <span class="events-dropdown-icon">S</span>
              <span>Manage Sponsorship</span>
            </a>
            <!-- <a href="#" class="events-dropdown-link" role="menuitem" aria-disabled="true">
              <span class="events-dropdown-icon">D</span>
              <span>Duplicate</span>
            </a> -->
            @if(!$event->isCancelled())
            <form action="{{ route('organiser.events.status', $event->id) }}" method="POST">
              @csrf
              <input type="hidden" name="status" value="{{ $statusToggleValue }}">
              <button type="submit" class="events-dropdown-btn{{ $statusToggleDisabled ? ' events-dropdown-btn--danger' : '' }}" role="menuitem" {{ $statusToggleDisabled ? 'disabled aria-disabled=true' : '' }}>
                <span class="events-dropdown-icon">{{ $statusToggleIcon }}</span>
                <span>{{ $statusToggleLabel }}</span>
              </button>
            </form>
            @endif
          </div>

          <div class="events-menu__divider"></div>

          <div class="events-menu__group">
            @if(!$event->isCancelled())
              <button type="button"
                      class="events-dropdown-btn events-dropdown-btn--danger"
                      role="menuitem"
                      data-cancel-event-trigger
                      data-cancel-action="{{ route('organiser.events.status', $event->id) }}">
                <span class="events-dropdown-icon">C</span>
                <span>Cancel Event</span>
              </button>
            @endif
            <form action="{{ route('organiser.events.destroy', $event->id) }}" method="POST" data-confirm="Delete this event?">
              @csrf
              @method('DELETE')
              <button type="submit" class="events-dropdown-btn events-dropdown-btn--danger" role="menuitem">
                <span class="events-dropdown-icon">X</span>
                <span>Delete Event</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </article>
    @empty
    <div class="events-empty">
      <h3>No events yet</h3>
      <p>Create your first event and start selling tickets.</p>
      <a href="{{ route('organiser.events.create') }}" class="events-create-button">Create Event</a>
    </div>
    @endforelse
  </section>

  <div class="events-pagination" data-events-pagination>{{ $events->links() }}</div>
</div>

<div id="cancel-modal" class="hidden fixed inset-0 z-50 bg-gray-950/90 flex items-center justify-center px-4">
  <div class="bg-gray-900 border border-red-800/60 rounded-2xl p-8 max-w-md w-full">
    <h3 class="text-lg font-extrabold text-white mb-2">Cancel This Event?</h3>
    <p class="text-gray-400 text-sm mb-5">All ticket holders will be notified by email and SMS. All paid bookings will be marked for refund.</p>
    <form id="cancel-modal-form" action="" method="POST">
      @csrf
      <input type="hidden" name="status" value="cancelled">
      <div class="mb-4">
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Reason for Cancellation *</label>
        <textarea name="cancellation_reason" rows="4" required minlength="10" maxlength="1000"
                  class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-red-500"
                  placeholder="Please explain why this event is being cancelled..."></textarea>
      </div>
      <div class="flex gap-3">
        <button type="submit" class="flex-1 bg-red-700 hover:bg-red-600 text-white font-bold py-3 rounded-xl text-sm transition-colors" style="color:#ffffff !important;">Confirm Cancellation</button>
        <button type="button" data-cancel-modal-close class="flex-1 bg-gray-700 hover:bg-gray-600 text-white font-semibold py-3 rounded-xl text-sm transition-colors">Go Back</button>
      </div>
    </form>
  </div>
</div>
@endsection

@section('scripts')
<script>
  let eventsFetchController = null;

  async function refreshEventsListing(url, pushState = true) {
    const root = document.querySelector('[data-events-root]');
    if (!root || !url) return;

    if (eventsFetchController) {
      eventsFetchController.abort();
    }

    eventsFetchController = new AbortController();
    root.classList.add('is-loading');

    try {
      const response = await fetch(url, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        signal: eventsFetchController.signal
      });

      if (!response.ok) {
        throw new Error('Failed to refresh events');
      }

      const html = await response.text();
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const nextRoot = doc.querySelector('[data-events-root]');

      if (!nextRoot) {
        throw new Error('Updated events markup not found');
      }

      root.innerHTML = nextRoot.innerHTML;

      if (pushState) {
        window.history.pushState({}, '', url);
      }
    } catch (error) {
      if (error.name !== 'AbortError') {
        window.location.href = url;
      }
    } finally {
      root.classList.remove('is-loading');
      eventsFetchController = null;
    }
  }

  function buildEventsUrl(form) {
    const action = form.getAttribute('action') || window.location.pathname;
    const url = new URL(action, window.location.origin);
    const formData = new FormData(form);

    for (const [key, value] of formData.entries()) {
      if (typeof value === 'string' && value !== '') {
        url.searchParams.set(key, value);
      }
    }

    return url.toString();
  }

  function closeAllEventMenus() {
    document.querySelectorAll('.events-menu.is-open').forEach(function (openMenu) {
      openMenu.classList.remove('is-open');

      var menuRoot = openMenu.closest('[data-menu-root]');
      var toggle = menuRoot ? menuRoot.querySelector('.events-menu-toggle') : null;
      var eventItem = openMenu.closest('.events-item');

      if (toggle) {
        toggle.setAttribute('aria-expanded', 'false');
      }

      if (eventItem) {
        eventItem.classList.remove('is-menu-open');
      }
    });
  }

  document.addEventListener('submit', function (event) {
    const form = event.target.closest('[data-events-filter-form]');
    if (!form) return;

    event.preventDefault();
    refreshEventsListing(buildEventsUrl(form));
  });

  document.addEventListener('keydown', function (event) {
    const input = event.target.closest('.events-search-input');
    if (!input || event.key !== 'Enter') return;

    const form = input.form;
    if (!form || !form.matches('[data-events-filter-form]')) return;

    event.preventDefault();
    refreshEventsListing(buildEventsUrl(form));
  });

  document.addEventListener('click', function (event) {
    var eventCard = event.target.closest('[data-event-href]');
    if (eventCard) {
      if (event.target.closest('a, button, form, input, select, textarea, label, [data-menu-root]')) {
        return;
      }

      window.location.href = eventCard.getAttribute('data-event-href');
      return;
    }

    const paginationLink = event.target.closest('[data-events-pagination] a');
    if (!paginationLink) return;

    event.preventDefault();
    refreshEventsListing(paginationLink.href);
  });

  document.addEventListener('keydown', function (event) {
    var eventCard = event.target.closest('[data-event-href]');
    if (!eventCard) return;

    if (event.target.closest('a, button, form, input, select, textarea, label, [data-menu-root]')) {
      return;
    }

    if (event.key !== 'Enter' && event.key !== ' ') return;

    event.preventDefault();
    window.location.href = eventCard.getAttribute('data-event-href');
  });

  window.addEventListener('popstate', function () {
    refreshEventsListing(window.location.href, false);
  });

  function toggleEventMenu(button) {
    var root = button.closest('[data-menu-root]');
    if (!root) return;

    var menu = root.querySelector('.events-menu');
    var eventItem = button.closest('.events-item');
    var isOpen = menu.classList.contains('is-open');

    closeAllEventMenus();

    if (!isOpen) {
      menu.classList.add('is-open');
      button.setAttribute('aria-expanded', 'true');
      if (eventItem) {
        eventItem.classList.add('is-menu-open');
      }
    }
  }

  function closeCancelModal() {
    var modal = document.getElementById('cancel-modal');
    var form = document.getElementById('cancel-modal-form');
    if (!modal || !form) return;

    modal.classList.add('hidden');
    form.reset();
    form.setAttribute('action', '');
  }

  document.addEventListener('click', function (event) {
    var cancelTrigger = event.target.closest('[data-cancel-event-trigger]');
    if (cancelTrigger) {
      var modal = document.getElementById('cancel-modal');
      var form = document.getElementById('cancel-modal-form');
      var textarea = form ? form.querySelector('textarea[name="cancellation_reason"]') : null;

      if (!modal || !form || !textarea) return;

      closeAllEventMenus();

      form.setAttribute('action', cancelTrigger.getAttribute('data-cancel-action'));
      modal.classList.remove('hidden');

      window.setTimeout(function () {
        textarea.focus();
      }, 0);

      return;
    }

    if (event.target.closest('[data-cancel-modal-close]')) {
      closeCancelModal();
      return;
    }

    if (event.target.closest('[data-menu-root]')) return;
    closeAllEventMenus();
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') return;

    closeAllEventMenus();
    closeCancelModal();
  });
</script>
@endsection
