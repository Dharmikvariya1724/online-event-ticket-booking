<!DOCTYPE html>
<html lang="en" data-theme-default="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Organiser') - Ticketly</title>
@include('partials.protected-history-guard-head', ['guardNamespace' => 'organiser', 'guardState' => session('organiser_auth_state'), 'fallbackRedirect' => route('organiser.login')])
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cdefs%3E%3ClinearGradient id='g' x1='8' y1='8' x2='56' y2='56' gradientUnits='userSpaceOnUse'%3E%3Cstop stop-color='%236366f1'/%3E%3Cstop offset='1' stop-color='%23a855f7'/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect x='8' y='10' width='48' height='44' rx='12' fill='url(%23g)'/%3E%3Cpath d='M22 24h20a4 4 0 0 0 4 4v8a4 4 0 0 0-4 4H22a4 4 0 0 0-4-4v-8a4 4 0 0 0 4-4Z' fill='white' fill-opacity='.96'/%3E%3Cpath d='M32 24v16' stroke='url(%23g)' stroke-width='3' stroke-linecap='round'/%3E%3C/svg%3E">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@include('partials.theme-system-head')
@include('partials.global-ticket-loader-head')
<style>
  @media (max-width: 767px) {
    #sidebar {
      position: fixed;
      inset: 0 auto 0 0;
      z-index: 50;
      width: min(18rem, 82vw);
      min-width: 0 !important;
      transform: translateX(-100%);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    }

    #sidebar.sidebar-open {
      transform: translateX(0);
    }

    #sidebar-backdrop {
      position: fixed;
      inset: 0;
      z-index: 40;
      background: rgba(15, 23, 42, 0.42);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }

    #sidebar-backdrop.sidebar-open {
      opacity: 1;
      pointer-events: auto;
    }
  }

  .sidebar-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.72rem 0.82rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 550;
    transition: all 0.2s ease;
  }
  .sidebar-link.active {
    background: rgba(79, 70, 229, 0.2);
    color: #a5b4fc;
    border: 1px solid rgba(99, 102, 241, 0.3);
  }
  .sidebar-link .icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
  }
  .kpi-card {
    border: 1px solid;
    border-radius: 1.15rem;
    padding: 1.35rem;
    transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
  }
  .kpi-card:hover {
    border-color: var(--surface-border-2);
    transform: translateY(-2px);
  }
  .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.38rem;
    min-height: 1.8rem;
    padding: 0.34rem 0.78rem;
    border-radius: 9999px;
    font-size: 0.74rem;
    font-weight: 700;
    line-height: 1;
    letter-spacing: 0;
    white-space: nowrap;
    border: 1px solid #d1d5db;
    background: #f3f4f6;
    color: #374151;
  }
  .badge__dot {
    width: 0.4rem;
    height: 0.4rem;
    border-radius: 9999px;
    flex-shrink: 0;
    background: currentColor;
    opacity: 0.85;
  }
  .badge--positive {
    color: #166534;
    background: #dcfce7;
    border-color: #86efac;
  }
  .badge--danger {
    color: #991b1b;
    background: #fef2f2;
    border-color: #fecaca;
  }
  .badge--accent {
    color: #1d4ed8;
    background: #eff6ff;
    border-color: #bfdbfe;
  }
  .badge--neutral {
    color: #374151;
    background: #f3f4f6;
    border-color: #d1d5db;
  }
  .badge--warning {
    color: #92400e;
    background: #fffbeb;
    border-color: #fde68a;
  }
  .line-clamp-1 { display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden; }
  .line-clamp-2 { display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }

  :root[data-theme='light'] #sidebar.organiser-shell-sidebar {
    background: #F3F4F6 !important;
  }
</style>
@yield('head')
</head>
<body class="bg-gray-950 text-gray-100 @yield('body-class')">
@include('partials.global-ticket-loader')

<div class="flex h-screen overflow-hidden">
  <div id="sidebar-backdrop" onclick="closeSidebar()" aria-hidden="true"></div>

  <!-- â”€â”€ SIDEBAR â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
  <aside id="sidebar" class="organiser-shell-sidebar w-64 flex-shrink-0 bg-gray-900 border-r border-gray-800 flex flex-col h-full overflow-y-auto transition-all duration-300" style="min-width:256px">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-5 border-b border-gray-800">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#6366f1,#8b5cf6,#ec4899)">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
      </div>
      <div>
        <div class="text-sm font-extrabold text-white">Ticketly</div>
        <div class="text-xs text-indigo-400 font-medium">Organiser Portal</div>
      </div>
      <button type="button" onclick="closeSidebar()" class="md:hidden ml-auto inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-700 text-gray-400 hover:bg-gray-800 hover:text-white" aria-label="Close sidebar">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>



    <!-- Nav -->
    <nav class="flex-1 px-3 py-4 space-y-1">
      <div class="text-xs font-semibold text-gray-600 uppercase tracking-wider px-3 mb-2">Main</div>

      <a href="{{ route('organiser.dashboard') }}"
         class="sidebar-link {{ request()->routeIs('organiser.dashboard') ? 'active' : '' }}">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
      </a>

      <a href="{{ route('organiser.analytics.index') }}"
         class="sidebar-link {{ request()->routeIs('organiser.analytics.*') ? 'active' : '' }}">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M4 19h16"/></svg>
        Analytics
      </a>

      <a href="{{ route('organiser.events.index') }}"
         class="sidebar-link {{ request()->routeIs('organiser.events.*') || request()->routeIs('organiser.sponsorships.*') ? 'active' : '' }}">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Events
      </a>

      <a href="{{ route('organiser.orders.index') }}"
         class="sidebar-link {{ request()->routeIs('organiser.orders.*') ? 'active' : '' }}">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Orders
      </a>

      <a href="{{ route('organiser.scan.index') }}"
         class="sidebar-link {{ request()->routeIs('organiser.scan.*') ? 'active' : '' }}">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2M18 3h2a1 1 0 011 1v2M18 21h2a1 1 0 001-1v-2M9 12h6m-6 0a3 3 0 016 0m-6 0a3 3 0 006 0"/></svg>
        Scan Ticket
      </a>

      <a href="{{ route('organiser.payouts.index') }}"
         class="sidebar-link {{ request()->routeIs('organiser.payouts.*') ? 'active' : '' }}">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .672-3 1.5S10.343 11 12 11s3 .672 3 1.5S13.657 14 12 14m0-6V7m0 7v1m-7-3a9 9 0 1018 0 9 9 0 10-18 0z"/></svg>
        Payouts
      </a>

      <a href="{{ route('organiser.promos.index') }}"
         class="sidebar-link {{ request()->routeIs('organiser.promos.*') ? 'active' : '' }}">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        Promo Codes
      </a>

      <div class="text-xs font-semibold text-gray-600 uppercase tracking-wider px-3 mt-4 mb-2">Account</div>

      <a href="{{ route('organiser.profile.show') }}"
         class="sidebar-link {{ request()->routeIs('organiser.profile.*') ? 'active' : '' }}">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        Profile
      </a>

      <a href="{{ route('home') }}" target="_blank"
         class="sidebar-link">
        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        View Public Site
      </a>
    </nav>

    <!-- Logout -->
    <div class="px-4 py-4 border-t border-gray-800">
@php $organiser = request()->attributes->get('organiser'); @endphp

@if($organiser)
<div class="flex items-center gap-3">

    {{-- Logo / Avatar --}}
    @if($organiser->logo_url)
        <div class="w-10 h-10 rounded-full overflow-hidden bg-gray-800 flex-shrink-0">
            <img src="{{ $organiser->logo_url }}" class="w-full h-full object-cover">
        </div>
    @else
        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"
            style="background:linear-gradient(135deg,#6366f1,#7c3aed)">
            {{ $organiser->initials }}
        </div>
    @endif

    {{-- Organiser Info --}}
    <div class="flex-1 min-w-0">
        <div class="text-sm font-semibold text-white truncate">
            {{ $organiser->name }}
        </div>
        <div class="text-xs text-gray-400 truncate">
            {{ $organiser->email }}
        </div>
    </div>

    {{-- Logout Button --}}
    <form action="{{ route('organiser.logout') }}" method="POST" data-logout-guard data-logout-namespace="organiser" data-logout-state="{{ session('organiser_auth_state') }}" data-logout-redirect="{{ route('organiser.login') }}">
        @csrf
        <button type="submit"
            class="p-2 rounded-lg text-gray-400 hover:text-red-400 hover:bg-gray-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
        </button>
    </form>

</div>
@endif
</div>
  </aside>

  <!-- â”€â”€ MAIN CONTENT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
  <div class="flex-1 flex flex-col overflow-hidden">

    <!-- Top Bar -->
    <header class="organiser-shell-header bg-gray-900/80 backdrop-blur border-b border-gray-800 px-7 py-4 flex items-center justify-between flex-shrink-0">
      <div class="flex items-center gap-3">
        <!-- Mobile menu toggle -->
        <button onclick="toggleSidebar()" class="md:hidden p-2 rounded-lg hover:bg-gray-800 text-gray-400">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        @hasSection('page-icon')
        <div class="hidden md:flex items-center justify-center flex-shrink-0">
          @yield('page-icon')
        </div>
        @endif
        <div>
          <h1 class="text-base font-bold text-white">@yield('page-title', 'Dashboard')</h1>
          <p class="text-xs text-gray-500">@yield('page-subtitle', ticketly_format_date(now()))</p>
        </div>
      </div>

      <div class="flex items-center gap-3">

          <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle color theme">
            <span data-theme-label>Dark</span>
          </button>
      

        <!-- Session timer indicator -->
        <!-- <div class="hidden sm:flex items-center gap-1.5 text-xs text-gray-500 bg-gray-800 border border-gray-700 rounded-xl px-3 py-1.5">
          <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"/></svg>
          Session active
        </div> -->
        <!-- Create Event shortcut -->
        <a href="{{ route('organiser.events.create') }}" class="hidden sm:flex items-center gap-2 text-xs font-semibold text-white px-4 py-2 rounded-xl" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          New Event
        </a>

      </div>
    </header>

    @php($hideDefaultAlerts = trim($__env->yieldContent('hide-default-alerts')) !== '')

    <!-- Flash Messages -->
    @if(!$hideDefaultAlerts && session('success'))
    <div class="mx-6 mt-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
      <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center text-emerald-600">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
      </span>
      <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(!$hideDefaultAlerts && session('info'))
    <div class="mx-6 mt-4 flex items-center gap-3 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
      <svg class="h-4 w-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <span class="font-medium">{{ session('info') }}</span>
    </div>
    @endif
    @if(!$hideDefaultAlerts && $errors->any())
    <div class="mx-6 mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
      @foreach($errors->all() as $error)
      <div class="flex items-start gap-2">
        <span class="pt-0.5 text-rose-500">&bull;</span>
        <span>{{ $error }}</span>
      </div>
      @endforeach
    </div>
    @endif

    <!-- Page Content -->
    <main class="organiser-shell-main flex-1 overflow-y-auto p-6 lg:p-7 xl:p-8">
      @yield('content')
    </main>
  </div>
</div>

<div id="organiser-confirm-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/60" data-confirm-backdrop></div>
  <div class="relative w-full max-w-md rounded-2xl border border-gray-800 bg-gray-900 p-6 shadow-2xl">
    <div class="flex items-start gap-3">
      <div class="mt-1 inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-500/15 text-amber-300">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-7.1 12.3A2 2 0 005.1 19h13.8a2 2 0 001.73-2.84l-7.1-12.3a2 2 0 00-3.46 0z"/></svg>
      </div>
      <div class="flex-1">
        <h3 id="organiser-confirm-title" class="text-base font-semibold text-white">Confirm action</h3>
        <p id="organiser-confirm-message" class="mt-2 text-sm text-gray-400">Are you sure you want to continue?</p>
      </div>
    </div>
    <div class="mt-6 flex items-center justify-end gap-3">
      <button type="button" id="organiser-confirm-cancel" class="px-4 py-2 rounded-xl border border-gray-700 text-gray-300 text-sm">Cancel</button>
      <button type="button" id="organiser-confirm-ok" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-semibold">Confirm</button>
    </div>
  </div>
</div>

@include('partials.theme-system-script')
@include('partials.password-toggle-script')
@include('partials.protected-history-guard-script', ['guardNamespace' => 'organiser', 'guardState' => session('organiser_auth_state'), 'fallbackRedirect' => route('organiser.login')])
<script>
  function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebar-backdrop');
    if (!sidebar || !backdrop) return;
    sidebar.classList.toggle('sidebar-open');
    backdrop.classList.toggle('sidebar-open');
  }

  function closeSidebar() {
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebar-backdrop');
    if (!sidebar || !backdrop) return;
    sidebar.classList.remove('sidebar-open');
    backdrop.classList.remove('sidebar-open');
  }

  (function () {
    var modal = document.getElementById('organiser-confirm-modal');
    var titleEl = document.getElementById('organiser-confirm-title');
    var messageEl = document.getElementById('organiser-confirm-message');
    var okBtn = document.getElementById('organiser-confirm-ok');
    var cancelBtn = document.getElementById('organiser-confirm-cancel');
    var backdrop = modal ? modal.querySelector('[data-confirm-backdrop]') : null;
    var pendingForm = null;

    if (!modal || !okBtn || !cancelBtn || !backdrop) return;

    function openModal(form) {
      pendingForm = form;
      titleEl.textContent = form.getAttribute('data-confirm-title') || 'Confirm action';
      messageEl.textContent = form.getAttribute('data-confirm') || 'Are you sure you want to continue?';
      okBtn.textContent = form.getAttribute('data-confirm-ok') || 'Confirm';
      cancelBtn.textContent = form.getAttribute('data-confirm-cancel') || 'Cancel';
      modal.classList.remove('hidden');
    }

    function closeModal() {
      modal.classList.add('hidden');
      pendingForm = null;
    }

    okBtn.addEventListener('click', function () {
      if (!pendingForm) return closeModal();
      var form = pendingForm;
      closeModal();
      form.submit();
    });

    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closeModal();
    });

    document.addEventListener('submit', function (event) {
      var form = event.target.closest('form[data-confirm]');
      if (!form) return;
      event.preventDefault();
      openModal(form);
    });
  })();
</script>
@yield('scripts')
@include('partials.date-input-display')
@include('partials.global-ticket-loader-script')
</body>
</html>
